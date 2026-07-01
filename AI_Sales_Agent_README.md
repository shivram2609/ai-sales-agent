# AI Sales Agent - Developer README

**Project:** AI Sales Agent / Zestminds Outreach OS  
**Owner:** Zestminds Technologies  
**Status date:** 2026-06-25  
**Stack:** Laravel, MySQL, Blade, Bootstrap, SerpAPI, OpenAI Responses API  
**Core rule:** AI prepares. Human reviews. Human sends manually. No auto-send.

---

## 1. Project Purpose

The AI Sales Agent is an internal outreach operating system for Zestminds Technologies. It helps the team discover relevant leads, inspect and filter them, convert good leads into prospects, generate outreach drafts, improve drafts with AI, and keep human review mandatory before any outreach is sent.

This is not a spam-sending system. It is a research, review, drafting, and decision-support tool.

---

## 2. Current Architecture

```text
Discovery campaign
  -> Smart query builder
  -> SerpAPI search
  -> Filtering service
  -> Save strong or possible candidates only
  -> Human reviews details
  -> Convert to prospect or reject

Prospect
  -> Crawl homepage
  -> Analyze fit
  -> Match proof assets
  -> Generate outreach draft
  -> AI quality check
  -> AI improve or rewrite
  -> Human reviews
  -> Human copies and sends manually
```

### Local environment

```text
Project root: D:\ai-sales-agent
PHP command: .\php83.bat
App URL: http://127.0.0.1:8000
```

### Common commands

```powershell
cd D:\ai-sales-agent
.\php83.bat artisan serve
.\php83.bat artisan migrate
.\php83.bat artisan optimize:clear
npm install
npm run build
```

### Queue worker

Discovery campaigns are processed in the background using Laravel database queues.

```powershell
.\php83.bat artisan queue:work --timeout=600 --tries=1
```

---

## 3. Environment Variables

The `.env` file should include:

```env
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=660

SERPAPI_API_KEY=your_serpapi_key_here

OPENAI_API_KEY=your_openai_key_here
OPENAI_MODEL=gpt-5.4-mini
OPENAI_TIMEOUT=45
OPENAI_DRAFT_PROMPT_VERSION=v1
```

`config/services.php` should include:

```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-5.4-mini'),
    'timeout' => (int) env('OPENAI_TIMEOUT', 45),
    'draft_prompt_version' => env('OPENAI_DRAFT_PROMPT_VERSION', 'v1'),
],
```

---

## 4. Completed Modules

### 4.1 Dashboard Module

**Purpose:** Give a quick operating view of discovery, prospects, drafts, and knowledge assets.

**Main files:**

```text
app\Http\Controllers\DashboardController.php
resources\views\dashboard\index.blade.php
routes\web.php
resources\css\app.css
```

**Dashboard shows:**

- Total prospects
- Approved prospects
- Review-ready prospects
- Discovery leads
- Strong leads
- Possible leads
- Manual rejects
- Running campaigns
- Draft count
- AI quality checked drafts
- Knowledge assets count
- Recent prospects
- Recent discovery campaigns
- Recent drafts
- Strong discovery candidates
- Quick workflow guidance

---

### 4.2 Discovery Queue Module

**Purpose:** Prevent browser timeouts by running SerpAPI discovery in the background.

**Main files:**

```text
app\Jobs\RunDiscoveryCampaignJob.php
app\Http\Controllers\DiscoveryController.php
.env
routes\web.php
```

**Flow:**

```text
Click Run Campaign
  -> campaign status becomes queued
  -> job is dispatched
  -> queue worker sets status running
  -> DiscoveryService runs campaign
  -> status becomes completed or failed
```

**Important commands:**

```powershell
.\php83.bat artisan queue:table
.\php83.bat artisan queue:failed-table
.\php83.bat artisan migrate
.\php83.bat artisan queue:work --timeout=600 --tries=1
```

---

### 4.3 Discovery Filtering Module

**Purpose:** Improve lead quality and avoid saving garbage results.

**Main files:**

```text
app\Services\Discovery\DiscoveryQueryBuilderService.php
app\Services\Discovery\SerpApiService.php
app\Services\Discovery\DiscoveryFilterService.php
app\Services\Discovery\DiscoveryService.php
app\Models\DiscoveryResult.php
```

**Current behavior:**

- Smart queries are capped.
- SerpAPI calls use timeout and retry.
- Auto-rejected results are skipped and not saved.
- Duplicate domains are skipped.
- Only `strong_candidate` and `possible_candidate` results are saved.

**Rejected automatically:**

```text
instagram.com
facebook.com
linkedin.com
reddit.com
quora.com
clutch.co
designrush.com
upwork.com
fiverr.com
land-book.com
landbook.com
awwwards.com
behance.net
dribbble.com
webflow.io
webflow.com
vercel.app
netlify.app
pages.dev
blogs
articles
listicles
directories
templates
job pages
career pages
```

---

### 4.4 Discovery Result Details, Convert, and Reject Module

**Purpose:** Let the user inspect each discovery lead before converting it.

**Main files:**

```text
app\Http\Controllers\DiscoveryResultController.php
resources\views\discovery-results\show.blade.php
resources\views\discovery\show.blade.php
routes\web.php
resources\css\app.css
```

**Actions:**

```text
Details
Open Website
Convert to Prospect
Reject Lead
Open Prospect if already converted
```

**Manual reject behavior:**

- Does not delete the result.
- Sets `filter_status = manual_rejected`.
- Sets `relevance_score = 0`.
- Stores manual rejection reason in `reason`.

---

### 4.5 Prospects Module

**Purpose:** Manage prospect records and run pipeline actions.

**Main files:**

```text
app\Http\Controllers\ProspectController.php
resources\views\prospects\index.blade.php
resources\views\prospects\create.blade.php
resources\views\prospects\show.blade.php
```

**Pipeline actions:**

```text
Crawl Homepage
Analyze
Match Proof
Generate Draft
Quality Check Latest Draft
Approve or Review
```

**Important cleanup completed:**

Old code referenced a missing `qualityChecks` relation on `OutreachDraft`. That relation is no longer used.

Current source of truth:

```text
Latest AI quality result: outreach_drafts columns
AI review history and debug: ai_logs table
```

`ProspectController@show` should load drafts like this:

```php
$prospect->load([
    'pages' => fn ($query) => $query->latest(),
    'analyses' => fn ($query) => $query->latest(),
    'drafts' => fn ($query) => $query->latest(),
]);
```

Do not eager load:

```php
with('qualityChecks')
```

---

### 4.6 Drafts Module

**Purpose:** Search, review, open, and copy outreach drafts.

**Main files:**

```text
app\Http\Controllers\OutreachDraftController.php
resources\views\outreach-drafts\index.blade.php
resources\views\outreach-drafts\show.blade.php
routes\web.php
resources\css\app.css
resources\js\app.js
```

**Features:**

- Server-side search
- Status filter
- Pagination
- Draft detail page
- Copy full draft
- Copy email
- Copy LinkedIn note
- Copy follow-ups
- Prospect context card
- AI review card

**Copy behavior:**

Copy buttons use hidden textareas with `.copy-source` and JS listener `.js-copy-text` in `resources\js\app.js`.

---

### 4.7 Knowledge Assets Module

**Purpose:** Manage proof links, case studies, service pages, and guides used by proof matching and AI draft improvement.

**Main files:**

```text
app\Http\Controllers\KnowledgeAssetController.php
resources\views\knowledge-assets\index.blade.php
resources\views\knowledge-assets\create.blade.php
resources\views\knowledge-assets\edit.blade.php
resources\views\knowledge-assets\_form.blade.php
app\Models\KnowledgeAsset.php
app\Services\Knowledge\KnowledgeAssetSeederService.php
routes\web.php
resources\css\app.css
```

**Features:**

- List knowledge assets
- Search by title, URL, tag, industry, technology
- Filter by asset type and active status
- Create asset
- Edit asset
- Activate or deactivate asset
- Seed default Zestminds assets

**Important:** Avoid adding weak or unrelated proof links. Draft quality depends heavily on this table.

---

### 4.8 AI Draft Tuning V1

**Purpose:** Use OpenAI to help review and improve outreach drafts while keeping human approval mandatory.

**Main files:**

```text
app\Services\AI\OpenAiClientService.php
app\Services\AI\DraftImprovementService.php
app\Services\AI\DraftQualityService.php
app\Models\AiLog.php
app\Models\OutreachDraft.php
app\Http\Controllers\OutreachDraftController.php
resources\views\outreach-drafts\show.blade.php
routes\web.php
resources\css\app.css
```

**Database changes:**

`outreach_drafts` has latest AI review fields:

```text
ai_quality_score
ai_personalization_score
ai_relevance_score
ai_proof_score
ai_spam_risk_score
ai_quality_notes
ai_improvement_notes
ai_model_used
ai_prompt_version
ai_last_checked_at
```

`ai_logs` stores AI request and response history:

```text
loggable_type
loggable_id
action
model
prompt_version
input_snapshot
output_json
status
error_message
created_at
updated_at
```

**Current AI actions:**

```text
AI Quality Check
Improve with AI
```

**Important current issue:**

The drafts are still too salesy, too polished, and sometimes confusing. Prompt work is not finished.

Pending prompt direction:

- Shorter prompts
- Simple structure
- Human and relatable tone
- Company-specific practical problem
- No clever phrasing
- No vague wording like "bits behind it"
- No "brochure site" type negative framing
- No ampersand or fancy characters
- Friendly, simple, Shivam-like writing

---

## 5. Review Queue Cleanup

**File updated:**

```text
app\Services\Review\ReviewQueueService.php
```

Old code loaded:

```php
with(['qualityChecks' => fn($qq) => $qq->latest()->limit(1)])
```

This was removed because `qualityChecks` is not part of the current data model.

Draft loading should now use statuses like:

```php
->whereIn('status', [
    'quality_checked',
    'ai_quality_checked',
    'ai_improved',
])
```

---

## 6. Route Map

Core routes include:

```php
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/prospects', [ProspectController::class, 'index'])->name('prospects.index');
Route::get('/prospects/create', [ProspectController::class, 'create'])->name('prospects.create');
Route::get('/prospects/{prospect}', [ProspectController::class, 'show'])->name('prospects.show');

Route::get('/discovery', [DiscoveryController::class, 'index'])->name('discovery.index');
Route::get('/discovery/create', [DiscoveryController::class, 'create'])->name('discovery.create');
Route::get('/discovery/{campaign}', [DiscoveryController::class, 'show'])->name('discovery.show');
Route::post('/discovery/{campaign}/run', [DiscoveryController::class, 'run'])->name('discovery.run');

Route::get('/discovery-results/{discoveryResult}', [DiscoveryResultController::class, 'show'])->name('discovery-results.show');
Route::post('/discovery-results/{discoveryResult}/reject', [DiscoveryResultController::class, 'reject'])->name('discovery-results.reject');

Route::get('/outreach-drafts', [OutreachDraftController::class, 'index'])->name('outreach-drafts.index');
Route::get('/outreach-drafts/{outreachDraft}', [OutreachDraftController::class, 'show'])->name('outreach-drafts.show');
Route::post('/outreach-drafts/{outreachDraft}/improve-ai', [OutreachDraftController::class, 'improveWithAi'])->name('outreach-drafts.improve-ai');
Route::post('/outreach-drafts/{outreachDraft}/quality-check-ai', [OutreachDraftController::class, 'qualityCheckWithAi'])->name('outreach-drafts.quality-check-ai');

Route::get('/knowledge-assets', [KnowledgeAssetController::class, 'index'])->name('knowledge-assets.index');
Route::get('/knowledge-assets/create', [KnowledgeAssetController::class, 'create'])->name('knowledge-assets.create');
Route::post('/knowledge-assets', [KnowledgeAssetController::class, 'store'])->name('knowledge-assets.store');
Route::post('/knowledge-assets/seed', [KnowledgeAssetController::class, 'seed'])->name('knowledge-assets.seed');
Route::get('/knowledge-assets/{knowledgeAsset}/edit', [KnowledgeAssetController::class, 'edit'])->name('knowledge-assets.edit');
Route::put('/knowledge-assets/{knowledgeAsset}', [KnowledgeAssetController::class, 'update'])->name('knowledge-assets.update');
Route::patch('/knowledge-assets/{knowledgeAsset}/toggle', [KnowledgeAssetController::class, 'toggle'])->name('knowledge-assets.toggle');
```

---

## 7. Important Debug Commands

Search for old broken quality check references:

```powershell
findstr /S /N /I "qualityChecks" app\*.php resources\views\*.blade.php
```

Expected result:

```text
No results
```

Search for old table references:

```powershell
findstr /S /N /I "quality_checks" app\*.php resources\views\*.blade.php database\migrations\*.php
```

Clear Laravel cache:

```powershell
.\php83.bat artisan optimize:clear
```

Restart queue worker:

```powershell
.\php83.bat artisan queue:work --timeout=600 --tries=1
```

---

## 8. Known Errors Fixed

### Error 1

```text
Call to undefined relationship [qualityChecks] on model [App\Models\OutreachDraft]
```

**Cause:** Old relation was still eager loaded.

**Fix:** Removed `with('qualityChecks')` from `ProspectController.php` and `ReviewQueueService.php`.

### Error 2

```text
Call to a member function first() on null
```

**Cause:** `resources\views\prospects\show.blade.php` still called `$draft->qualityChecks->first()`.

**Fix:** Replaced that UI block with direct fields:

```php
$draft->ai_quality_score
$draft->ai_personalization_score
$draft->ai_relevance_score
$draft->ai_spam_risk_score
```

---

## 9. Coding Decisions and Rules

### Data source decisions

```text
Latest AI quality result = outreach_drafts columns
AI history and debug = ai_logs table
No old qualityChecks relation in V1
```

### Outreach rules

```text
No auto-send
No bulk spam
Human approval required
AI can improve and review, but cannot send
```

### Draft style rules

Current desired voice:

```text
Friendly
Simple
Relatable
Specific to the prospect
Senior developer-led
Not salesy
Not over-polished
Not a generic agency pitch
```

Avoid in final outreach text:

```text
em dash
en dash
curly quotes
fancy bullets
arrows
ampersand
marketing buzzwords
vague clever phrases
```

Use only normal laptop keyboard characters.

---

## 10. Pending Work

Highest priority next tasks:

1. Simplify AI prompts for draft generation and improvement.
2. Make drafts more friendly and relatable.
3. Add AI text sanitizer for fancy characters and ampersand.
4. Improve rewrite flow using AI quality feedback.
5. Consider storing `ai_verdict` as a separate column.
6. Tune proof matcher after draft prompt quality is stable.
7. Test 3 real draft patterns:
   - Webflow or design agency
   - SaaS or product company
   - AI automation or integration prospect

---

## 11. Developer Handoff Notes

A developer picking up this project should first verify:

```powershell
.\php83.bat artisan migrate
.\php83.bat artisan optimize:clear
npm run build
findstr /S /N /I "qualityChecks" app\*.php resources\views\*.blade.php
```

Then run:

```powershell
.\php83.bat artisan serve
.\php83.bat artisan queue:work --timeout=600 --tries=1
```

Then test these pages:

```text
http://127.0.0.1:8000
http://127.0.0.1:8000/discovery
http://127.0.0.1:8000/prospects
http://127.0.0.1:8000/outreach-drafts
http://127.0.0.1:8000/knowledge-assets
```

The project is currently functional enough for manual discovery, manual review, draft management, and AI-assisted draft checks. The next major quality improvement is prompt tuning, not more UI.