@extends('layouts.master')
@section('title','About Us')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'About Us' , 'description' => 'Our company Khushipay, which specializes in financial technology, is enthusiastic about payments and has embarked on a mission to transform the daily financial practices of Pakistanis. We are glad to say that, despite the many hard-won victories we have made in support of the nation transition to financial inclusion and digitization, our path is far from over.' , 'bread' => 'About Us'  ])

<section class="about-area about-p pt-120 pb-120 p-relative fix">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-12 mb-5">
                <h2>Vision Statement</h2>
                <h4 class="text-secondary fst-italic">“In our ideal future, trade will be unrestricted. At Khushipay, our goal is to be the driving force
                    behind the development of a smooth, safe, and cutting-edge digital payment ecosystem. Our goal is to
                    establish a world where companies of all sizes may easily transact, expand, and prosper with the
                    help of our state-of-the-art payment gateway services. With a focus on client success, global
                    connectivity, and financial technology innovation, our goal is to transform the electronic
                    transaction landscape and make the world market more open, effective, and integrated.”</h4>

            </div>
            <div class="col-md-8 wow fadeInUp pt-4 pt-lg-0 order-2 order-lg-1">
                <h4>Message from the Chairman</h4>
                <p> Dear valued customers, partners, and stakeholders, </p>
                <p>I am delighted to welcome you to <b>Khushipay</b>, best payment solution provider in Pakistan. At
                    <b>Khushipay</b>, we are committed to revolutionizing the way Pakistanis make payments, empowering
                    businesses to grow, and contributing to the country's digital transformation.</p>
                <p><b>Our mission is simple:</b> to provide secure, convenient and innovative payment solutions that
                    meet the evolving needs of our customers. We achieve this through our cutting-edge technology,
                    robust infrastructure, and customer-centric approach.</p>
                <p>As we continue to navigate the rapidly changing payments landscape, we remain focused on:
                    <ol>
                        <li>Enhancing payment security and compliance</li>
                        <li>Expanding our merchant network and partnerships</li>
                        <li>Developing user-friendly payment solutions</li>
                        <li>Fostering financial inclusion and digital literacy</li>
                    </ol>
                </p>
                <p>I would like to express my gratitude to our customers for trusting <b>Khushipay</b> with their payment
                    needs. Your loyalty and feedback drive us to innovate and improve.</p>
                <p>To our partners, we appreciate your collaboration and shared commitment to Pakistan's digital
                    payments ecosystem. At <b>Khushipay</b>, we are proud to be part of Pakistan's growth story. Together,
                    let's shape the future of payments.</p>
                <p>Thank you for choosing <b>Khushipay</b>.
                    <br><br><i>Sincerely,</i>
                    <br><br><b>Muhammad Ashraf</b>
                    <p>Chairman, <b>Khushipay</b></p>
            </div>
            <div class="col-lg-4 wow fadeInUp order-1 order-lg-2">
                <img src="{{asset('assets/img/theme/ceo.jpg')}}" class="img-fluid">

            </div>

        </div>
    </div>
</section>
@endsection
