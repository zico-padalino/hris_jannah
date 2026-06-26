@extends('layouts.app')
@section('title', __('pages.leave.cuti.history_title'))
@section('subtitle', __('pages.leave.cuti.history_subtitle'))
@section('content')
    @include('leaves.partials.history-table', ['leaves' => $leaves, 'category' => 'cuti'])
@endsection
