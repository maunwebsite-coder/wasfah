@extends('layouts.app')

@section('title', __('dessert_tips.meta.title'))

@section('content')
    @include('pages.partials.tips-page', ['namespace' => 'dessert_tips'])
@endsection

