<?php

namespace App\Http\Requests;

use App\Models\User;
use Auth;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use RateLimiter;
use Str;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'password' => ['required', 'string']
        ];
    }

    /**
     * Attempt to authenticate
     */
    public function authenticate() : void
    {
        //If there's no user, we make the default user
        if (User::count() == 0) {
            User::create([
                'name' => 'cogi234',
                'url' => route('homepage'),
                'email' => 'colinbougie@gmail.com',
                'password' => '2644151',
                'admin' => true
            ]);
        }

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only(['name', 'password']), $this->boolean('remember'))) {
            //If we failed, we hit the rate limiter and throw an error
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'name' => trans('auth.failed')
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited
     */
    public function ensureIsNotRateLimited() : void 
    {
        //If it's not rate limited, we return
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }
        //Otherwise
        //Send a lockout event
        event(new Lockout($this));
        //Calculate how many seconds of lockout are left
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'name' => trans('auth.throttle', [
                'seconds' => $seconds
            ])
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('name') . '|' . $this->ip()));
    }
}
