<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="font-sans antialiased bg-white">
        <div class="min-h-screen">
            {{ $slot }}
        </div>

        @fluxScripts
    </body>
</html>
