@extends('layouts.master')
@section('title','Login')
@section('content')
<section id="home" class="slider-area fix p-relative">
    <div class="slider-circal"></div>
    <div class="slider-circal-2"></div>
    <div class="slider-active" style="background: #00173c;">
        <div class="single-slider slider-bg d-flex slider-bg-three align-items-center" style="min-height: 100vh !important;">
            <div class="container">
                <div class="row justify-content-center align-items-center">

                    <div class="col-md-6">
                        <div class="slider-content s-slider-content mt-20">
                        <div class="form card p-4">
                            <img src="{{asset('assets/img/logo/logo-1.png')}}" width="200" class="m-auto py-4">
                    <form action="{{ route('login') }}" method="post">
                        @csrf
                        <div class="form-group mb-3">
                            <input type="email" class="form-control rounded-pill p-3" name="email" id="email"
                                placeholder="Your Email" value="{{old('email')}}">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div class="form-group mb-3">
                            <input type="password" class="form-control rounded-pill p-3" name="password" id="subject"
                                placeholder="password">
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>


                        <div class="text-center"><button type="submit" class="btn btn-primary rounded-pill px-4"
                                title="Send Message">Login</button></div>
                    </form>
                </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>


</section>
@endsection

