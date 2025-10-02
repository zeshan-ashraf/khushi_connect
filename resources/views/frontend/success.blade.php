@extends('layouts.master')
@section('title','Pricing')
@section('content')


<section style="margin-top:200px;">
    <div class="container mt-5">
        <div class="row mb-5">
            <div class="col-md-12 text-center my-3">
                <img src="{{asset('assets/image/success.png')}}" alt="">
                    <h1>Thank you.</h1>
                    <p class="lead w-lg-50 mx-auto">Your order has been placed successfully.</p>
                    <p>As Result of this transaction an amount of Rs. <span class="text-primary"><b>{{$transaction->amount}}</b></span> has been debited from your mobile account against the Order Id: <span class="text-primary"><b>{{$transaction->txn_ref_no }}</b></span></p>
                    <a class="btn btn-primary btn-lg" href="{{route('all-product')}}">Continue Shoping</a>
            </div>
        </div>
    </div>
</section>

@include('frontend.component.grow-business')
@include('frontend.component.payments')
@endsection