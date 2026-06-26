@extends('layouts.app')
@section('title', __('pages.leave.izin.create_title'))
@section('subtitle', __('pages.leave.izin.create_subtitle'))
@section('content')
    @include('leaves.partials.create-form', ['category' => 'izin'])
@endsection
