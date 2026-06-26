@php $category = $category ?? 'cuti'; @endphp
@extends('layouts.app')
@section('title', __("pages.leave.{$category}.create_title"))
@section('subtitle', __("pages.leave.{$category}.create_subtitle"))
@section('content')
    @include('leaves.partials.create-form', ['category' => $category])
@endsection
