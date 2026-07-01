<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\KnowledgeAssetController;
use App\Http\Controllers\OutreachDraftController;
use App\Http\Controllers\OutreachQueueController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\ReviewQueueController;
use App\Http\Controllers\DiscoveryResultController;
use App\Http\Controllers\ProspectContactController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignMemberController;
use App\Http\Controllers\CampaignMemberSendingController;
use App\Http\Controllers\SendingQueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('prospects', ProspectController::class)->only(['index','create','store','show']);
Route::post('prospects/{prospect}/crawl', [ProspectController::class, 'crawl'])->name('prospects.crawl');
Route::post('prospects/{prospect}/analyze', [ProspectController::class, 'analyze'])->name('prospects.analyze');
Route::post('prospects/{prospect}/proof-match', [ProspectController::class, 'matchProof'])->name('prospects.proof-match');
Route::post('prospects/{prospect}/generate-draft', [ProspectController::class, 'generateDraft'])->name('prospects.generate-draft');
Route::post('prospects/{prospect}/quality-latest-draft', [ProspectController::class, 'qualityLatestDraft'])->name('prospects.quality-latest-draft');

Route::post('/prospects/{prospect}/contacts', [ProspectContactController::class, 'store'])
    ->name('prospects.contacts.store');

Route::get('/prospect-contacts/{prospectContact}/edit', [ProspectContactController::class, 'edit'])
    ->name('prospect-contacts.edit');

Route::put('/prospect-contacts/{prospectContact}', [ProspectContactController::class, 'update'])
    ->name('prospect-contacts.update');

Route::post('/prospect-contacts/{prospectContact}/make-primary', [ProspectContactController::class, 'makePrimary'])
    ->name('prospect-contacts.make-primary');

Route::delete('/prospect-contacts/{prospectContact}', [ProspectContactController::class, 'destroy'])
    ->name('prospect-contacts.destroy');

Route::post('/prospect-contacts/{prospectContact}/drafts', [OutreachDraftController::class, 'createForContact'])
    ->name('prospect-contacts.drafts.store');
	
Route::get('/outreach-queue', [OutreachQueueController::class, 'index'])
    ->name('outreach-queue.index');

Route::get('/knowledge-assets', [KnowledgeAssetController::class, 'index'])
    ->name('knowledge-assets.index');

Route::get('/knowledge-assets/create', [KnowledgeAssetController::class, 'create'])
    ->name('knowledge-assets.create');

Route::post('/knowledge-assets', [KnowledgeAssetController::class, 'store'])
    ->name('knowledge-assets.store');

Route::post('/knowledge-assets/seed', [KnowledgeAssetController::class, 'seed'])
    ->name('knowledge-assets.seed');

Route::get('/knowledge-assets/{knowledgeAsset}/edit', [KnowledgeAssetController::class, 'edit'])
    ->name('knowledge-assets.edit');

Route::put('/knowledge-assets/{knowledgeAsset}', [KnowledgeAssetController::class, 'update'])
    ->name('knowledge-assets.update');

Route::patch('/knowledge-assets/{knowledgeAsset}/toggle', [KnowledgeAssetController::class, 'toggle'])
    ->name('knowledge-assets.toggle');

Route::get('outreach-drafts', [OutreachDraftController::class, 'index'])->name('outreach-drafts.index');
Route::get('/outreach-drafts/{outreachDraft}/edit', [OutreachDraftController::class, 'edit'])
    ->name('outreach-drafts.edit');

Route::put('/outreach-drafts/{outreachDraft}', [OutreachDraftController::class, 'update'])
    ->name('outreach-drafts.update');
Route::get('outreach-drafts/{outreachDraft}', [OutreachDraftController::class, 'show'])->name('outreach-drafts.show');
Route::post('/outreach-drafts/{outreachDraft}/improve-ai', [OutreachDraftController::class, 'improveWithAi'])
    ->name('outreach-drafts.improve-ai');

Route::post('/outreach-drafts/{outreachDraft}/quality-check-ai', [OutreachDraftController::class, 'qualityCheckWithAi'])
    ->name('outreach-drafts.quality-check-ai');

Route::get('review-queue', [ReviewQueueController::class, 'index'])->name('review.index');
Route::post('review-queue/prospects/{prospect}/approve', [ReviewQueueController::class, 'approve'])->name('review.approve');
Route::post('review-queue/prospects/{prospect}/reject', [ReviewQueueController::class, 'reject'])->name('review.reject');
Route::post('review-queue/prospects/{prospect}/mark-not-fit', [ReviewQueueController::class, 'markNotFit'])->name('review.mark-not-fit');

Route::get('discovery', [DiscoveryController::class, 'index'])->name('discovery.index');
Route::get('discovery/create', [DiscoveryController::class, 'create'])->name('discovery.create');
Route::post('discovery', [DiscoveryController::class, 'store'])->name('discovery.store');
Route::get('discovery/{campaign}', [DiscoveryController::class, 'show'])->name('discovery.show');
Route::post('discovery/{campaign}/run', [DiscoveryController::class, 'run'])->name('discovery.run');
Route::post('discovery/{campaign}/convert-strong', [DiscoveryController::class, 'convertStrong'])->name('discovery.convert-strong');
Route::post('discovery-results/{result}/convert', [DiscoveryController::class, 'convert'])->name('discovery-results.convert');
Route::get('/discovery-results/{discoveryResult}', [DiscoveryResultController::class, 'show'])
    ->name('discovery-results.show');
Route::post('/discovery-results/{discoveryResult}/reject', [DiscoveryResultController::class, 'reject'])
    ->name('discovery-results.reject');
	
Route::resource('campaigns', CampaignController::class);

Route::post('/campaigns/{campaign}/members', [CampaignMemberController::class, 'store'])
    ->name('campaigns.members.store');

Route::put('/campaign-members/{campaignMember}', [CampaignMemberController::class, 'update'])
    ->name('campaign-members.update');

Route::post('/campaign-members/{campaignMember}/draft', [CampaignMemberController::class, 'createDraft'])
    ->name('campaign-members.draft.store');

Route::delete('/campaign-members/{campaignMember}', [CampaignMemberController::class, 'destroy'])
    ->name('campaign-members.destroy');

Route::post('/campaign-members/{campaignMember}/mark-sent', [CampaignMemberController::class, 'markSent'])
    ->name('campaign-members.mark-sent');

Route::post('/campaign-members/{campaignMember}/mark-follow-up-1-sent', [CampaignMemberController::class, 'markFollowUp1Sent'])
    ->name('campaign-members.mark-follow-up-1-sent');

Route::post('/campaign-members/{campaignMember}/mark-follow-up-2-sent', [CampaignMemberController::class, 'markFollowUp2Sent'])
    ->name('campaign-members.mark-follow-up-2-sent');

Route::post('/campaign-members/{campaignMember}/mark-replied', [CampaignMemberController::class, 'markReplied'])
    ->name('campaign-members.mark-replied');

Route::post('/campaign-members/{campaignMember}/mark-not-interested', [CampaignMemberController::class, 'markNotInterested'])
    ->name('campaign-members.mark-not-interested');

Route::post('/campaign-members/{campaignMember}/mark-bounced', [CampaignMemberController::class, 'markBounced'])
    ->name('campaign-members.mark-bounced');
	
Route::get('/campaign-members/{campaignMember}/sending-control', [CampaignMemberSendingController::class, 'edit'])
    ->name('campaign-members.sending-control');

Route::post('/campaign-members/{campaignMember}/approve-sending', [CampaignMemberSendingController::class, 'approve'])
    ->name('campaign-members.approve-sending');

Route::post('/campaign-members/{campaignMember}/pause-sequence', [CampaignMemberSendingController::class, 'pause'])
    ->name('campaign-members.pause-sequence');

Route::post('/campaign-members/{campaignMember}/resume-sequence', [CampaignMemberSendingController::class, 'resume'])
    ->name('campaign-members.resume-sequence');

Route::post('/campaign-members/{campaignMember}/stop-sequence', [CampaignMemberSendingController::class, 'stop'])
    ->name('campaign-members.stop-sequence');

Route::get('/sending-queue', [SendingQueueController::class, 'index'])
    ->name('sending-queue.index');

Route::post('/outbound-email-jobs/{outboundEmailJob}/cancel', [SendingQueueController::class, 'cancel'])
    ->name('outbound-email-jobs.cancel');