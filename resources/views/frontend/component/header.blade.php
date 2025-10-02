<!-- header -->
<header class="header-area header-three">
    <div id="header-sticky" class="menu-area">
        <div class="container">
            <div class="second-menu">
                <div class="row align-items-center">
                    <div class="col-xl-2 col-lg-2">
                        <div class="logo">
                            <a href="{{route('home')}}"><img src="{{asset('assets/img/logo/logo-1.png')}}" alt="logo"></a>
                        </div>
                    </div>
                    <div class="col-xl-7 col-lg-7">

                        <div class="main-menu text-right text-xl-right">
                            <nav id="mobile-menu">
                                <ul>
                                    <li><a class="@routeis('home') site-text @endrouteis" href="{{route('home')}}">Home</a></li>
                                    <li><a class="@routeis('about') site-text @endrouteis" href="{{route('about')}}">About Us</a></li>
                                    <li><a class="@routeis('services') site-text @endrouteis" href="{{route('services')}}">Services</a></li>
                                    <li class="has-sub">
                                        <a href="#">Product</a>
                                        <ul>
                                            <li> <a href="#">Payment Gateway</a></li>
                                            <li> <a href="#">Invoices</a></li>
                                            <li> <a href="{{ route('all-product')}}">Our Products</a></li>
                                        </ul>
                                    </li>
                                    <li><a class="@routeis('pricing') site-text @endrouteis" href="{{route('pricing')}}">Pricing</a></li>
                                    <li><a class="@routeis('faqs') site-text @endrouteis" href="{{route('faqs')}}">Faqs</a></li>
                                    <li><a class="@routeis('contact') site-text @endrouteis" href="{{route('contact')}}">Contact Us</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 d-none d-lg-block mt-25 mb-25 text-right text-xl-right">
                        <div class="login">
                            <ul>
                                <li>
                                    <div class="second-header-btn">
                                        <a href="{{route('login')}}" class="btn">Sign In</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mobile-menu"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- header-end -->
