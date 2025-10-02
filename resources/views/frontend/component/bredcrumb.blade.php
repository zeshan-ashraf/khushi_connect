<section class="breadcrumb-area d-flex align-items-center p-relative fix"
    style="background-image:url(img/bg/bdrc-bg.jpg); min-height: 390px !important;">
    <div class="slider-circal"></div>
    <div class="slider-circal-2"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xl-12 col-lg-12">
                <div class="breadcrumb-wrap text-left">
                    <div class="breadcrumb-title">
                        <h2>{{$title}}</h2>
                        @if($description != '')
                        <p>
                            {{$description}}
                        </p>
                        @endif
                        <div class="breadcrumb-wrap">

                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{$bread}}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
