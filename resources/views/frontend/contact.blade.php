@extends('layouts.master')
@section('title','Contact Us')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Contact Us' , 'description' => '' , 'bread' => 'Contact Us' ])

<section id="pricing" class="pricing-area pt-120 pb-60 fix p-relative">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6">
                <div class="form card p-4">
                <div class="section-title center-align mb-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h2>
                        Get In Touch
                    </h2>
                    <p class="my-3">Reach out with your questions, feedback or any assistance you require.</p>
                </div>
                    <form action="#" method="post" role="form" class="contactForm">
                        <div class="form-group mb-3">
                            <input type="text" name="name" class="form-control rounded-pill p-3" id="name"
                                placeholder="Your Name" data-rule="minlen:4" data-msg="Please enter at least 4 chars">
                            <div class="validation"></div>
                        </div>
                        <div class="form-group mb-3">
                            <input type="email" class="form-control rounded-pill p-3" name="email" id="email"
                                placeholder="Your Email" data-rule="email" data-msg="Please enter a valid email">
                            <div class="validation"></div>
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" class="form-control rounded-pill p-3" name="subject" id="subject"
                                placeholder="Subject" data-rule="minlen:4"
                                data-msg="Please enter at least 8 chars of subject">
                            <div class="validation"></div>
                        </div>
                        <div class="form-group mb-3">
                            <textarea class="form-control  p-3" style="border-radius: 15px;" name="message" rows="5"
                                data-rule="required" data-msg="Please write something for us"
                                placeholder="Message"></textarea>
                            <div class="validation"></div>
                        </div>


                        <div class="text-center"><button type="submit" class="btn btn-primary rounded-pill px-4"
                                title="Send Message">Send Message</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@include('frontend.component.clients')

@endsection
