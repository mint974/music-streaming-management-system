<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Music Streaming System')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
