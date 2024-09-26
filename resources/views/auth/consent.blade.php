<x-app-layout>
    <form class="consent-form" method="POST" action="">
        @csrf
        <input type="hidden" id="action" name="action" value="consent" />

        <h2>Consent</h2>

        <p>The client {{ $client_name ?? null }} at <a href="{{ $client_id }}">{{ $client_id }}</a> would like to
            request your authorization to:</p>

        <!-- Scopes -->
        <div class="input-container">
            @foreach ($scopes as $scope)
            <label for="{{$scope}}" class="checkbox-container">
                <input class="checkbox-input" id="{{$scope}}" type="checkbox" name="scopes[]" value="{{$scope}}" checked autocomplete="off">
                <span class="inline-input-label">{{ __('scopes.' . $scope) }}</span>
            </label>
            @endforeach
        </div>

        <!-- Token duration -->
        <div class="input-container">
            The client will have access for 7 days
            <div class="checkbox-container">
                <input class="checkbox-input" id="" type="checkbox" name="refresh" value="true" autocomplete="off">
                <span class="inline-input-label">Allow persistent access? </span>
                <span class="inline-input-label" style="font-size: 0.75rem">This will allow the client to continue to
                    request access, even after the initial expiry of the access tokens, for potentially unlimited time,
                    but if after 30 days they do not use this access, they will lose it.</span>
            </div>
        </div>

        <p>You will be redirected to: <a href="{{ $redirect_uri }}">{{ $redirect_uri }}</a></p>

        <hr />
        <div style="width:fit-content; margin: 0 auto">
        <button type="submit">
            Accept
        </button>
        <a href="{{ route('homepage') }}" class="button">
            Deny
        </a>
        </div>
    </form>
</x-app-layout>