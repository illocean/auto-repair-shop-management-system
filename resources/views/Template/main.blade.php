<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'NO. 3. Auto Repair Shop')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="body-layout">
    @include('Template.header')
    <main id="main-content" class="main-content">
        <div class="container-main">
            @yield('content')
        </div>
    </main>
    @include('Template.footer')
</body>

</html>