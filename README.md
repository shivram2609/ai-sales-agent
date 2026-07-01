# AI Sales Agent Laravel

Laravel rebuild of the Zestminds AI Sales Agent / Outreach Operating System.

## What is included

- Prospect creation and detail pages
- Homepage crawler using Laravel HTTP + DOMDocument
- Rule-based prospect analyzer
- Zestminds knowledge assets seeder
- Proof matcher
- Outreach draft generator
- Draft quality guardrail
- Human review queue
- Outreach drafts helper screens
- Dynamic Discovery Module with Smart Query Builder and optional Exact Query mode
- Blade frontend with reusable layout, header, footer and Vite assets
- API routes matching the previous backend flow

## Recommended local path

```text
D:\ai-sales-agent-laravel
```

## Install

Run from the project root:

```powershell
composer install
copy .env.example .env
php artisan key:generate
```

Create a MySQL database:

```sql
CREATE DATABASE ai_sales_agent_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update `.env`:

```env
DB_DATABASE=ai_sales_agent_laravel
DB_USERNAME=root
DB_PASSWORD=
SERPAPI_API_KEY=your_serpapi_key_here
```

Run migrations:

```powershell
php artisan migrate
```

Install frontend assets:

```powershell
npm install
npm run dev
```

Start Laravel:

```powershell
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## First setup inside app

1. Open `Knowledge`
2. Click `Seed Defaults`
3. Create a discovery campaign or add a prospect manually.

## API examples

```http
GET /api/health
POST /api/prospects
POST /api/crawler/prospects/{id}/homepage
POST /api/analyzer/prospects/{id}/run
POST /api/knowledge-assets/seed-defaults
POST /api/proof-matcher/prospects/{id}/run
POST /api/draft-generator/prospects/{id}/run
POST /api/draft-quality/drafts/{id}/run
GET /api/review-queue
POST /api/discovery/campaigns
POST /api/discovery/campaigns/{id}/run
POST /api/discovery/results/{id}/convert-to-prospect
```

## Discovery campaign payload - smart mode

```json
{
  "name": "US Webflow SaaS Agencies",
  "query_mode": "smart",
  "service_focus": ["Webflow", "UX Design"],
  "target_audience": ["SaaS", "B2B", "Startups"],
  "regions": ["USA", "UK"],
  "cities": [],
  "exclude_terms": ["India", "Bangalore", "Mumbai"],
  "max_results_per_query": 10
}
```

## Discovery campaign payload - exact mode

```json
{
  "name": "Exact Webflow USA",
  "query_mode": "exact",
  "exact_queries": [
    "\"Webflow agency\" \"SaaS\" \"USA\" -India -Bangalore -Mumbai -reddit -clutch -blog -best -top"
  ],
  "max_results_per_query": 10
}
```

## Notes

- This version intentionally does not send email.
- Brevo sender setup should be added after review pipeline and discovery quality are stable.
- Decision-maker/contact enrichment is not included yet. That should be a separate module using Hunter/Bouncer or another provider.
- No Docker, no Redis, no vector DB in V1.
