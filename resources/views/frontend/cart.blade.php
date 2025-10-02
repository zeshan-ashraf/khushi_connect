@extends('layouts.master')
@section('title','Pricing')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Cart' , 'description' => '' , 'bread' => 'Cart' ])
<style>
    .section-bg {
        background: #fff;
    }

    #pricing {
        padding: 80px 0px 0;
    }

    #team {
        padding: 0;
    }

    h4 {
        font-size: 30px;
        font-weight: 600;
        margin-right: 56px;
        margin-left: 0px !important;
        color: #42499b;
        margin-bottom: 5px;
    }

</style>
<section id="pricing" class="wow fadeInUp section-bg mt-5">
    <div class="container">

    </div>
</section>
<section id="team" class="">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="form contact-form p-4">

                    <form action="{{route('checkout')}}" method="post" role="form">
						@csrf
						<input type="hidden" name="client_email" value="testing@khushipay.com">
						<input type="hidden" name="amount" value="{{$item->price}}">
                        <div class="row">
                            <div class="col-md-12 mt-5">
                                <table class="table table-bordered mb-4">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="10%">Image</th>
                                            <th scope="col">Product Name</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><img src="{{asset($item->image)}}" style="width:150px;height:150px">
                                            </td>
                                            <td>{{$item->name}}</td>
                                            <td>{{$item->price}}</td>
                                            <td>1</td>
                                            <td>{{$item->price}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-3 m-auto">
                                <h4>Billing Detail</h4>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" name="first_name" class="form-control rounded-pill py-4 px-3"
                                        id="first_name" placeholder="Enter Name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" name="phone" class="form-control rounded-pill py-4 px-3"
                                        id="phone" placeholder="Enter Phone Number">
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="form-group">
                                    <input type="text" name="cnic" class="form-control rounded-pill py-4 px-3"
                                        id="cnic" placeholder="Enter last six digits cnic">
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="form-group">
                                    <input type="email" class="form-control rounded-pill py-4 px-3" name="email"
                                        id="email" placeholder="Enter Email">
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="form-group">
                                    <input type="text" class="form-control rounded-pill py-4 px-3" name="address"
                                        id="address" placeholder="Enter Address">
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div id="payment" class="woocommerce-checkout-payment">
                                    <h3>Payment Methods</h3>
                                    <ul class="wc_payment_methods payment_methods methods list-unstyled">
                                        <li class="wc_payment_method payment_method_jazzcash">
                                            <span class="custom-checkbox">
                                                <input id="payment_method_jazzcash" type="radio" class="input-radio"
                                                    name="payment_method" value="jazzcash" checked="checked"
                                                    data-order_button_text="">
                                                <label for="payment_method_jazzcash">
                                                    <img width="90" src="{{asset('image/jazzcash.jfif')}}"
                                                        alt="JazzCash Debit/Credit Card"> <span
                                                        class="checkmark"></span>
                                                </label>
                                            </span>
                                        </li>
                                        <li class="wc_payment_method payment_method_easypaisa">
                                            <span class="custom-checkbox">
                                                <input id="payment_method_easypaisa" type="radio" class="input-radio"
                                                    name="payment_method" value="easypaisa" data-order_button_text="">
                                                <label for="payment_method_easypaisa">
                                                    <img width="105" src="{{asset('image/easypaisa.jpg')}}"
                                                        alt="Easypaisa"> <span class="checkmark"></span>
                                                </label>
                                            </span>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!--<div class="col-md-12">-->
                            <!--    <div class="g-recaptcha" data-sitekey="6LeBvYMqAAAAAIFHOx6N46a45RDrx2aEYCzqj_LA"></div>-->
                            <!--</div>-->
                            
                            <div class="col-md-12">
                                <div class="text-center"><button type="submit" class="btn btn-primary rounded-pill px-4"
                                        title="Send Message">Rs. {{$item->price}} - Checkout</button></div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@include('frontend.component.grow-business')
@include('frontend.component.payments')
@endsection