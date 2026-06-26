@extends('layouts.app')
@section('title', __('pages.leave.cuti.create_title'))
@section('subtitle', __('pages.leave.cuti.create_subtitle'))
@section('content')
    @include('leaves.partials.create-form', ['category' => 'cuti'])
@endsection
