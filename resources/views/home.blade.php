<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Singapore Hawker Centre Closing Dates | Cleaning Schedule</title>
    <meta name="description" content="Check which Singapore hawker centres are closing for cleaning or maintenance. Updated daily with official NEA data for the next 15 days.">
    <meta name="keywords" content="singapore hawker centre, hawker center closing, cleaning schedule, NEA, hawker centre maintenance">
    <link rel="canonical" href="{{ url('/') }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Singapore Hawker Centre Closing Dates">
    <meta property="og:description" content="Check which Singapore hawker centres are closing for cleaning or maintenance. Updated daily with official NEA data.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url('/icons/icon-512x512.png') }}">
    <meta property="og:locale" content="en_SG">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Singapore Hawker Centre Closing Dates">
    <meta name="twitter:description" content="Check which Singapore hawker centres are closing for cleaning or maintenance.">
    <meta name="twitter:image" content="{{ url('/icons/icon-512x512.png') }}">

    <!-- PWA -->
    <meta name="theme-color" content="#f9fafb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">

        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Singapore Hawker Centre Closures</h1>
            <p class="text-gray-600 mt-2">Hawker centres closing for cleaning or maintenance in the next 15 days</p>
        </header>

        @if($closures->isEmpty())
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-500">No hawker centers are scheduled to close in the next 15 days.</p>
                <p class="text-gray-400 text-sm mt-2">Run <code class="bg-gray-100 px-1 rounded">php artisan hawker:sync</code> to fetch the latest data.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($closures as $closure)
                    <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $closure->type === 'cleaning' ? 'border-blue-500' : 'border-orange-500' }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="font-semibold text-gray-900">{{ $closure->hawkerCenter->name }}</h2>
                                @if($closure->hawkerCenter->address)
                                    <p class="text-gray-500 text-sm mt-1">{{ $closure->hawkerCenter->address }}</p>
                                @endif
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $closure->type === 'cleaning' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $closure->type === 'cleaning' ? 'Cleaning' : 'Other Works' }}
                            </span>
                        </div>
                        <div class="mt-3 text-sm text-gray-600">
                            <span class="font-medium">Closed:</span>
                            {{ $closure->start_date->format('j M Y') }} - {{ $closure->end_date->format('j M Y') }}
                            @php
                                $daysUntil = now()->startOfDay()->diffInDays($closure->start_date, false);
                            @endphp
                            @if($daysUntil > 0)
                                <span class="text-gray-400">(in {{ $daysUntil }} {{ Str::plural('day', $daysUntil) }})</span>
                            @elseif($daysUntil === 0)
                                <span class="text-red-500 font-medium">(starts today)</span>
                            @else
                                <span class="text-red-500 font-medium">(ongoing)</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <footer class="mt-8 text-center text-gray-400 text-sm">
            <p>Data from <a href="https://data.gov.sg" class="underline hover:text-gray-600">data.gov.sg</a></p>
        </footer>
    </div>
</body>
</html>
