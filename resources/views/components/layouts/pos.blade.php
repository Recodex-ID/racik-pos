<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="font-sans antialiased bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 h-full">
        <div class="min-h-full flex flex-col">
            {{ $slot }}
        </div>

        <!-- Flux UI Scripts -->
        @fluxScripts
    </body>
</html>
