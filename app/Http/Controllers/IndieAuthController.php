<?php

namespace App\Http\Controllers;

use App\Models\AuthorizationCode;
use App\Models\Token;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class IndieAuthController extends Controller
{
    public function metadata(): JsonResponse
    {
        return response()->json([
            'issuer' => route('homepage'),
            'authorization_endpoint' => route('indieauth'),
            'token_endpoint' => route('indieauth.token'),
            'introspection_endpoint' => route('indieauth.introspection'),
            'scopes_supported' => [ //TODO add scopes, probably starting with micropub
                'test'
            ],
            'service_documentation' => 'https://indieauth.spec.indieweb.org/',
            'code_challenge_methods_supported' => [ //TODO support plaintext maybe?
                'S256'
            ],
            'authorization_response_iss_parameter_supported' => true,
            'userinfo_endpoint' => route('indieauth.userinfo')
        ]);
    }

    public function authenticate(Request $request)
    {
        // Request data
        $validator = Validator::make($request->query(), [
            'response_type' => [
                'required',
                Rule::in(['code', 'id'])
            ],
            'client_id' => [
                'required',
                'url'
            ],
            'redirect_uri' => [
                'required',
                'url'
            ],
            'state' => [
                'required'
            ],
            'code_challenge' => [
                'required'
            ],
            'code_challenge_method' => [
                'required',
                Rule::in('S256')
            ],
            'me' => [
                'nullable',
                'url'
            ]
        ]);
        //If there's something wrong with the request, we display the error
        if ($validator->fails()) {
            return response(View::make('auth.badrequest', ['errors' => $validator->errors()->getMessages()]), 400);
        }
        $request_data = $validator->validated();
        $request_data['scopes'] = explode(' ', $request->query('scope') ?? '');

        //Client information discovery
        $client_data = $this->getClientData($request_data['client_id']);

        //Validate redirect uri
        $parsed_client_id = parse_url($request_data['client_id']);
        $parsed_redirect_url = parse_url($request_data['redirect_uri']);
        //If the scheme, the host or the port doesn't match AND the uri isn't in the list provided by the client, we display an error
        if (
            (
                $parsed_client_id['scheme'] != $parsed_redirect_url['scheme']
                || $parsed_client_id['host'] != $parsed_redirect_url['host']
                || (isset($parsed_client_id['port']) && isset($parsed_redirect_url['port']) && $parsed_client_id['port'] != $parsed_redirect_url['port'])
            )
            && is_array($client_data['redirect_uris'])
            && in_array($request_data['redirect_uri'], $client_data['redirect_uris'], true)
        ) {
            return response(View::make('auth.badrequest', ['errors' => [`The provided redirect uri is invalid.`]]), 400);
        }

        //If we're not authenticated, we go to login
        if (!Auth::check()) {
            redirect()->setIntendedUrl($request->fullUrl());
            return redirect()->route('login');
        }

        //All input has been validated
        $request->session()->flash('request_data', $request_data);

        //Send data to the consent form
        $consent_data = [
            'scopes' => $request_data['scopes'],
            'client_id' => $request_data['client_id'],
            'redirect_uri' => $request_data['redirect_uri']
        ];

        if ($client_data != null && $client_data != []) {
            $consent_data['client_name'] = $client_data['client_name'] ?? null;
            $consent_data['logo_uri'] = $client_data['logo_uri'] ?? null;
        }

        return view('auth.consent', $consent_data);
    }

    public function getToken(Request $request)
    {
        //This is to handle the consent form
        if ($request->input('action') === 'consent')
            return $this->createAuthentication($request);

        if ($request->input('grant_type') == 'authorization_code') {
            //We grant a token from an authorization code
            //Validate request
            $validator = Validator::make($request->all(), [
                'code' => [
                    'required',
                    'string'
                ],
                'client_id' => [
                    'required',
                    'url'
                ],
                'redirect_uri' => [
                    'required',
                    'url'
                ],
                'code_verifier' => [
                    'required',
                    'string'
                ]
            ]);
            //If there's something wrong with the request, we display the error
            if ($validator->fails()) {
                return response(View::make('auth.badrequest', ['errors' => $validator->errors()->getMessages()]), 400);
            }
            $request_data = $validator->validated();

            $authorization = AuthorizationCode::find($request_data['code']);

            if ($authorization == null) {
                //There's no corresponding authorization for this code
                return response('invalid_request', status: 400);
            }
            if ($authorization->used) {
                //The code was already used so we delete it and abort
                $authorization->delete();
                return response('invalid_request', status: 400);
            }


            $code_verification = $this->base64url_encode(pack('H*', hash('sha256', $request_data['code_verifier'])));
            //If the client_id or the redirect_uri don't match, or if the code verifier isn't right, we return a bad request
            if (
                $request_data['client_id'] != $authorization->client_id
                || $request_data['redirect_uri'] != $authorization->redirect_uri
                || ($code_verification != $authorization->code_challenge && $authorization->code_challenge_method = 'S256')
            ) {
                return response('invalid_token', 401);
            }

            $scopes = explode(' ', $authorization->scopes);

            $response = ['me' => route('homepage')];

            $non_token_scope_count = 0;
            if (in_array('profile', $scopes, true)) {
                $non_token_scope_count++;
                $profile = [
                    'name' => $authorization->user->name,
                    'url' => $authorization->user->url,
                    'photo' => $authorization->user->image
                ];
                if (in_array('email', $scopes, true)) {
                    $non_token_scope_count++;
                    $profile['email'] = $authorization->user->email;
                }
                $response['profile'] = $profile;
            }
            if (sizeof($scopes) > $non_token_scope_count) {
                //We want a token
                $token = Token::create([
                    'code' => Str::random(128),
                    'expiration' => now()->addDays(7),
                    'scopes' => $authorization->scopes,
                    'client_id' => $authorization->client_id,
                    'user_id' => $authorization->user_id,
                    'type' => 'access'
                ]);

                $response['access_token'] = $token->code;
                $response['token_type'] = 'Bearer';
                $response['scope'] = $token->scopes;
                $response['expires_in'] = (int) now()->diffInSeconds($token->expiration);

                if ($authorization->refresh) {
                    //We want a refresh token
                    $refresh_token = Token::create([
                        'code' => Str::random(128),
                        'expiration' => now()->addDays(30),
                        'scopes' => $authorization->scopes,
                        'client_id' => $authorization->client_id,
                        'user_id' => $authorization->user_id,
                        'type' => 'refresh'
                    ]);
                    $response['access_token'] = $refresh_token->code;
                }
            }

            return response(json_encode($response));
        } else if ($request->input('grant_type') == 'refresh_token') {
            //We grant a token from a refresh token
            //Validate request
            $validator = Validator::make($request->all(), [
                'refresh_token' => [
                    'required',
                    'string'
                ],
                'client_id' => [
                    'required',
                    'url'
                ],
                'scope' => [
                    'nullable',
                    'string'
                ]
            ]);
            //If there's something wrong with the request, we display the error
            if ($validator->fails()) {
                return response(View::make('auth.badrequest', ['errors' => $validator->errors()->getMessages()]), 400);
            }
            $request_data = $validator->validated();

            $refresh_token = Token::where('type', 'refresh')->find($request_data['refresh_token']);
            if ($refresh_token == null) {
                //There's no corresponding refresh token for this code
                return response('invalid_token', status: 401);
            }
            //If scope is not specified, it's the full scope of the refresh token
            if ($request_data['scope'] == null)
                $request_data['scope'] = $refresh_token->scopes;

            $requested_scopes = array_intersect(explode(' ', $refresh_token->scopes), explode(' ', $request_data['scope']));

            //We want an access token
            $access_token = Token::create([
                'code' => Str::random(128),
                'expiration' => now()->addDays(7),
                'scopes' => implode(' ', $requested_scopes),
                'client_id' => $refresh_token->client_id,
                'user_id' => $refresh_token->user_id,
                'type' => 'access'
            ]);
            //We want a refresh token
            $new_refresh_token = Token::create([
                'code' => Str::random(128),
                'expiration' => now()->addDays(30),
                'scopes' => $refresh_token->scopes,
                'client_id' => $refresh_token->client_id,
                'user_id' => $refresh_token->user_id,
                'type' => 'refresh'
            ]);

            $response = [
                'me' => $refresh_token->user->url,
                'access_token' => $access_token->code,
                'refresh_token' => $new_refresh_token->code,
                'token_type' => 'Bearer',
                'scope' => implode(' ', $requested_scopes)
            ];

            if (in_array('profile', $requested_scopes, true)) {
                $profile = [
                    'name' => $refresh_token->user->name,
                    'url' => $refresh_token->user->url,
                    'photo' => $refresh_token->user->image
                ];
                if (in_array('email', $requested_scopes, true)) {
                    $profile['email'] = $refresh_token->user->email;
                }
                $response['profile'] = $profile;
            }

            //We delete the old refresh token, because we give a new one
            $refresh_token->delete();

            return response(json_encode($response));
        }

    }

    private function createAuthentication(Request $request)
    {
        $original_request_data = $request->session()->get('request_data');

        $accepted_scopes = array_intersect($original_request_data['scopes'], $request->input('scopes'));

        $auth_code = AuthorizationCode::create([
            'code' => Str::random(40),
            'expiration' => now()->addMinutes(10),
            'scopes' => implode(' ', $accepted_scopes),
            'user_id' => Auth::user()->id(),
            'client_id' => $original_request_data['client_id'],
            'redirect_uri' => $original_request_data['redirect_uri'],
            'code_challenge' => $original_request_data['code_challenge'],
            'code_challenge_method' => $original_request_data['code_challenge_method'],
            'refresh' => $request->input('refresh') ?? false
        ]);

        $url = url($original_request_data['redirect_uri']) . '?' . http_build_query([
            'code' => $auth_code->code(),
            'state' => $original_request_data['state'],
            'iss' => route('homepage')
        ]);

        return redirect()->away($url);
    }

    private function getClientData($client_id): array
    {
        $response = Http::get($client_id);
        if (str_starts_with($response->header('Content-Type'), 'application/json')) { //TODO: Add mime type detection if the header doesn't tell
            //If the client information is Json, we take the relevant data
            $response_data = $response->json();
            if ($response_data['client_id'] == null)
                $response_data['client_id'] = $client_id;

            $validator = Validator::make($response_data, [
                'client_id' => [
                    'required',
                    Rule::in([$client_id])
                ],
                'client_uri' => [
                    'required',
                    'url'
                ],
                'client_name' => [
                    'nullable',
                    'string'
                ],
                'logo_uri' => [
                    'nullable',
                    'url'
                ],
                'redirect_uris' => [
                    'nullable',
                    'array'
                ],
                'redirect_uris.*' => [
                    'nullable',
                    'url'
                ]
            ]);
            return $validator->validated();
        }
        return [];
    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
