@extends('layouts.master')
@section('title','Pricing')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Pricing' , 'description' => '' , 'bread' => 'Pricing' ])

<section id="pricing" class="pricing-area pt-120 pb-60 fix p-relative">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-12">
                <div class="section-title center-align mb-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h2>
                    Pricing
                    </h2>
                    <p class="my-3">It's simple. Sell more, pay less. <br> Your rates are automatically adjusted as your business grows</p>
                </div>

            </div>
            <div class="col-lg-4 col-md-6">
                <div class="pricing-box pricing-box2 mb-60">
                    <div class="pricing-head">
                        <h3>Standard Plan</h3>
                        <p>Plan designed for Startups,
                            Small and Medium Enterprises</p>
                        <hr>
                    </div>

                    <div class="pricing-body mt-20 mb-15 text-left">
                        <ul class="mb-15">
                            <li>Payments Links</li>
                            <li>Subscriptions</li>
                            <li>Built-in Dashboards</li>
                            <li>Secure Payments</li>
                        </ul>
                        <hr>
                    </div>
                    <div class="price-count">
                        <h2>6000Rs <sub>/ Month</sub></h2>
                    </div>

                    <div class="pricing-btn">
                        <a href="mailto:info@khishipay.com" class="btn ss-btn">Get Started </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="pricing-box pricing-box2 mb-60">
                    <div class="pricing-head">
                        <h3>Enterprise</h3>
                        <p>Do you process large monthly volumes or have a unique business model? Get in touch with us to
                            discuss your pricing.</p>
                        <hr>
                    </div>
                    <div class="pricing-btn">
                        <a href="mailto:info@khishipay.com" class="btn ss-btn">Get Started </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@include('frontend.component.grow-business')

@include('frontend.component.payments')

@endsection
