@extends('layouts.app')
@section('title', __('pages.leave.lembur.history_title'))
@section('subtitle', __('pages.leave.lembur.history_subtitle'))
@section('content')
    @include('leaves.partials.history-table', ['leaves' => $leaves, 'category' => 'lembur'])
@endsection
