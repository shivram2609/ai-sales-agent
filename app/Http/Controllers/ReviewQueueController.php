<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Services\Review\ReviewQueueService;
use Illuminate\Http\Request;

class ReviewQueueController extends Controller
{
    public function index(ReviewQueueService $service) { return view('review.index', ['prospects' => $service->queue()]); }
    public function approve(Request $request, Prospect $prospect, ReviewQueueService $service) { $service->approve($prospect, $request->input('notes')); return back()->with('success', 'Prospect approved.'); }
    public function reject(Request $request, Prospect $prospect, ReviewQueueService $service) { $service->reject($prospect, $request->input('notes')); return back()->with('success', 'Prospect rejected.'); }
    public function markNotFit(Request $request, Prospect $prospect, ReviewQueueService $service) { $service->markNotFit($prospect, $request->input('notes')); return back()->with('success', 'Prospect marked not fit.'); }
}
