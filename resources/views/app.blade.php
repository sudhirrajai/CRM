<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- App favicon -->
        <link rel="shortcut icon" href="/assets/images/favicon.png">

        <!-- Theme Config Js -->
        <script src="/assets/js/config.js"></script>

        <!-- Vendor css (Packaged in assets) -->
        <link href="/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        <!-- Vendor js (Packaged in assets) -->
        <script src="/assets/js/vendor.min.js"></script>

        <!-- Apex Chart js -->
        <script src="/assets/vendor/apexcharts/apexcharts.min.js"></script>

        <!-- App js (Packaged in assets) -->
        <script src="/assets/js/app.js"></script>

        <!-- Dashboard Sales js -->
        <script src="/assets/js/pages/dashboard-sales.js"></script>

        <!-- Iconify-icon -->
        <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    </body>
</html>
