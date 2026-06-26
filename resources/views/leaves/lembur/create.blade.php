@extends('layouts.app')
@section('title', __('pages.leave.lembur.create_title'))
@section('subtitle', __('pages.leave.lembur.create_subtitle'))
@section('content')
    @include('leaves.partials.create-form', ['category' => 'lembur'])
@endsection
