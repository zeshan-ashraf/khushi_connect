@extends('layouts.master')
@section('title','Services')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Services' , 'description' => '' , 'bread' => 'Services'  ])

@include('frontend.component.services')

@include('frontend.component.who-we-are')

@include('frontend.component.clients')

@endsection
