@extends('layouts.app')
@section('title', 'Dashboard · AI Sales Agent')
@section('content')
<h1>Dashboard</h1>
<div class="grid cards">
  <div class="card"><strong>{{ $prospectsCount }}</strong><span>Prospects</span></div>
  <div class="card"><strong>{{ $campaignsCount }}</strong><span>Discovery Campaigns</span></div>
  <div class="card"><strong>{{ $draftsCount }}</strong><span>Drafts</span></div>
  <div class="card"><strong>{{ $reviewCount }}</strong><span>Ready for Review</span></div>
</div>
<div class="actions"><a class="btn" href="{{ route('discovery.create') }}">New Discovery Campaign</a><a class="btn secondary" href="{{ route('prospects.create') }}">Add Prospect</a></div>
<h2>Recent Prospects</h2>
@include('prospects._table', ['prospects' => $recentProspects])
@endsection
