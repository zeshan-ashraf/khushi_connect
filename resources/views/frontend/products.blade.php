@extends('layouts.master')
@section('title','Pricing')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Our Products' , 'description' => '' , 'bread' => 'Our Products' ])
<style>
    #why-us .why-us-content .features h4 {
    font-size: 24px;
    font-weight: 600;
    margin-right: 56px;
    margin-left: 0px !important;
    color: #42499b;
    margin-bottom: 5px;
}
#why-us .why-us-content .features {
    margin: 0 0 107px 0 !important;
}
#pricing .card{
    height: 492px;
    padding:10px 0;
}
#pricing .card .list-group{
    margin-bottom: 0px;
    width: 100%;
    align-items: center;
}

.breadcrumb-area  {
    min-height: 390px !important;
}
</style>
<!--==========================
    Intro Section
  ============================-->


<section id="pricing" class="wow fadeInUp section-bg mt-5">

    <div class="container">

        <header class="section-header">
            <h3 class="mb-5">Our Products</h3>
        </header>

        <div class="row flex-items-xs-middle flex-items-xs-center">
            @foreach($list as $item)
            <div class="col-xs-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <!--<h3><i class="fa  fa-check-circle"></i></h3>-->
                        <img src="{{$item->image}}" style="width:390px;height:250px">
                    </div>
                    <div class="card-block">
                        <h4 class="card-title">
                            {{$item->name}}
                        </h4>
                        <ul class="list-group">
                            <h4 class="mb-3"> Rs. {{$item->price}} </h4>
                            <a href="{{route('save-product',$item->id)}}" class="btn">Buy Product</a>
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</section><!-- #pricing -->

@include('frontend.component.grow-business')
@include('frontend.component.payments')
@endsection