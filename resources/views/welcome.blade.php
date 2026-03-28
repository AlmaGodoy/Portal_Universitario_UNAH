<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Portal Universitario</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /* Estilos base de Tailwind (mantenidos para que no se rompa el diseño) */
                @layer theme {
                    :root {
                        --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                        --color-white: #fff;
                        --color-black: #000;
                    }
                }
            </style>
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">

        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
                <div class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">

                    <h1 class="mb-1 font-medium text-xl">Bienvenido al Portal</h1>
                    <p class="mb-6 text-[#706f6c] dark:text-[#A1A09A]">
                        Selecciona el tipo de acceso para continuar con tus trámites académicos.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-900 transition-all text-center">
                            <h2 class="font-bold">Estudiantes</h2>
                            <a href="{{ route('login') }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded">Iniciar Sesión</a>
                        </div>

                        <div class="p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-900 transition-all text-center">
                            <h2 class="font-bold">Empleados</h2>
                            <a href="{{ route('login') }}" class="mt-4 inline-block bg-gray-800 text-white px-4 py-2 rounded">Iniciar Sesión</a>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </body>
</html>
