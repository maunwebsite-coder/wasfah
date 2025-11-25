@extends('layouts.app')

@section('title', __('baking_tips.meta.title'))

@section('content')
    @include('pages.partials.tips-page', ['namespace' => 'baking_tips'])
@endsection

