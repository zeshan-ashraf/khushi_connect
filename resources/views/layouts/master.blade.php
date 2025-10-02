<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Khushipay - @yield('title')</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/img/favicon.jpg')}}">
        <!-- Place favicon.ico in the root directory -->

		<!-- CSS here -->
        <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/animate.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/magnific-popup.css')}}">
        <link rel="stylesheet" href="{{asset('assets/fontawesome/css/all.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets/font-awesome/css/font-awesome.min.css')}}">

        <link rel="stylesheet" href="{{asset('assets/css/dripicons.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/slick.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/meanmenu.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/default.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
        <link rel="stylesheet" href="{{asset('assets/css/responsive.css')}}">

        @yield('css')
    </head>
    <body>

    @if(url()->current() != route('login'))
        @include('frontend.component.header')
    @endif
        <!-- main-area -->
        <main>

            @yield('content')

        </main>
        <!-- main-area-end -->
@if(url()->current() != route('login'))
        @include('frontend.component.footer')
@endif
        <!-- JS here -->
        <script src="{{asset('assets/js/vendor/modernizr-3.5.0.min.js')}}"></script>
        <script src="{{asset('assets/js/vendor/jquery-3.6.0.min.js')}}"></script>
        <script src="{{asset('assets/js/popper.min.js')}}"></script>
        <script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('assets/js/slick.min.js')}}"></script>
        <script src="{{asset('assets/js/ajax-form.js')}}"></script>
        <script src="{{asset('assets/js/paroller.js')}}"></script>
        <script src="{{asset('assets/js/wow.min.js')}}"></script>
        <script src="{{asset('assets/js/js_isotope.pkgd.min.js')}}"></script>
        <script src="{{asset('assets/js/imagesloaded.min.js')}}"></script>
        <script src="{{asset('assets/js/parallax.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.waypoints.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.counterup.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.scrollUp.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.meanmenu.min.js')}}"></script>
        <script src="{{asset('assets/js/parallax-scroll.js')}}"></script>
        <script src="{{asset('assets/js/jquery.magnific-popup.min.js')}}"></script>
        <script src="{{asset('assets/js/element-in-view.js')}}"></script>
        <script src="{{asset('assets/js/main.js')}}"></script>
        <script src="{{asset('assets/js/contactform.js')}}"></script>

        @yield('js')
    </body>
</html>
