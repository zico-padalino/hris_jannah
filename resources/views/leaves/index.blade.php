@php $category = $category ?? 'cuti'; @endphp
@extends('layouts.app')
@section('title', __("pages.leave.{$category}.history_title"))
@section('subtitle', __("pages.leave.{$category}.history_subtitle"))
@section('content')
    @include('leaves.partials.history-table', ['leaves' => $leaves, 'category' => $category])
@endsection
