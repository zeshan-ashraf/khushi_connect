@extends('layouts.master')
@section('title','Home')
@section('content')

<!-- slider-area -->
<section id="home" class="slider-area fix p-relative">
    <div class="slider-circal"></div>
    <div class="slider-circal-2"></div>
    <div class="slider-active slider-bg-video" style="background: #00173c;">
        <div class="single-slider slider-bg d-flex slider-bg-three align-items-center"
            style="background-image: url(img/slider/slider_bg.png); background-size: cover;">
            <div class="container">
                <div class="row justify-content-center align-items-center">

                    <div class="col-lg-8 col-md-8">
                        <div class="slider-content s-slider-content mt-20">
                            <h2 data-animation="fadeInUp" data-delay=".4s">Provides Best Payment Solutions</h2>
                            <p data-animation="fadeInUp" data-delay=".6s">It’s time to go cashless. We offer a
                                comprehensive payment solution that lets your business manage cash flows, present
                                digital invoices and receive digital payments through a fast, easy and reliable
                                one-window interface. Your clients can opt payment methods of their choice which include
                                Digital Wallets (Jazzcash, Easypaisa), digital bank transfers, debit/credit cards etc.
                                Get Registered for Free.</p>

                            <div class="slider-btn mt-30">
                                <a href="#" class="btn ss-btn" data-animation="fadeInLeft"
                                    data-delay=".4s">Open an Account</a>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 p-relative">
                    </div>

                </div>
            </div>
            <!-- video -->
            <video id="my-video" class="video2" muted loop autoplay>
                <source src="{{asset('assets/img/slider/slider-vedio.mp4')}}" type="video/mp4">
                <source src="{{asset('assets/img/slider/slider-vedio.ogv')}}" type="video/ogg">
                <source src="{{asset('assets/img/slider/slider-vedio.webm')}}" type="video/webm">
            </video><!-- /video -->
        </div>

    </div>


</section>
<!-- slider-area-end -->

<!-- service-area -->
<section class="service-details-two p-relative">
    <div class="container">
        <div class="row">

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="services-box07 mb-30">

                    <div class="sr-contner">
                        <div class="icon">
                            <img src="{{asset('assets/img/icon/sve-icon4.png')}}" alt="icon01">
                        </div>
                        <div class="text">
                            <h5>Payment Solution</h5>
                            <p>This is the secure connection through which transactions are submitted to the banking
                                network for authorization, settlement and reporting.</p>
                        </div>
                    </div>


                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="services-box07 mb-30">
                    <div class="sr-contner">
                        <div class="icon">
                            <img src="{{asset('assets/img/icon/sve-icon5.png')}}" alt="icon01">
                        </div>
                        <div class="text">
                            <h5>Growth Business</h5>
                            <p>Sell online without a website. Share Payment Links with customer via SMS, email or
                                WhatsApp and get paid instantly.</p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="services-box07 mb-30">
                    <div class="sr-contner">
                        <div class="icon">
                            <img src="{{asset('assets/img/icon/sve-icon6.png')}}" alt="icon01">
                        </div>
                        <div class="text">
                            <h5>Connect People</h5>
                            <p>Our secure payment gateway is used to connect people by providing them businesses in the
                                world.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

@include('frontend.component.who-we-are')

@include('frontend.component.clients')

@include('frontend.component.grow-business')

@include('frontend.component.services')

@include('frontend.component.payments')

@endsection
