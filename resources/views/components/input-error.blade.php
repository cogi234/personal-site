@props(['messages'])

@if ($messages)
    <ul class="input-errors">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif