@extends('layouts.app')
@section('title','Review Queue')
@section('content')
<h1>Review Queue</h1>
@forelse($prospects as $prospect)
<div class="panel">
  <h2><a href="{{ route('prospects.show', $prospect) }}">{{ $prospect->company_name ?: $prospect->domain }}</a></h2>
  <p>Fit: {{ $prospect->fit_score }} · Status: {{ $prospect->status }} · {{ $prospect->category_guess }}</p>
  @php($draft=$prospect->drafts->first()) @if($draft)<pre>{{ $draft->body }}</pre>@endif
  <div class="actions wrap">
    <form method="post" action="{{ route('review.approve', $prospect) }}">@csrf<input name="notes" placeholder="Notes"><button class="btn">Approve</button></form>
    <form method="post" action="{{ route('review.reject', $prospect) }}">@csrf<input name="notes" placeholder="Notes"><button class="btn danger">Reject</button></form>
    <form method="post" action="{{ route('review.mark-not-fit', $prospect) }}">@csrf<input name="notes" placeholder="Notes"><button class="btn secondary">Not Fit</button></form>
  </div>
</div>
@empty <p>No prospects ready for review.</p> @endforelse
@endsection
