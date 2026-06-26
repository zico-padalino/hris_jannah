@extends('layouts.app')
@section('title', __('pages.leave.izin.history_title'))
@section('subtitle', __('pages.leave.izin.history_subtitle'))
@section('content')
    @include('leaves.partials.history-table', ['leaves' => $leaves, 'category' => 'izin'])
@endsection
