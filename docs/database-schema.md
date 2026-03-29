# Database Schema Design (PostgreSQL)

## Core entities

### 1. users
- `id (uuid, pk)`
- `email (unique)`
- `password_hash`
- `name`
- `created_at`, `updated_at`

### 2. workspaces
- `id (uuid, pk)`
- `name`
- `owner_user_id (fk users.id)`
- `timezone`
- `created_at`, `updated_at`

### 3. workspace_members
- `workspace_id (fk workspaces.id)`
- `user_id (fk users.id)`
- `role (owner|analyst|viewer)`
- `created_at`
- Composite PK: `(workspace_id, user_id)`

### 4. platform_accounts
Represents one connected social account/channel/page.
- `id (uuid, pk)`
- `workspace_id (fk)`
- `platform (facebook|instagram|youtube|tiktok)`
- `external_account_id`
- `display_name`
- `status (active|disconnected|error)`
- `connected_at`
- `last_successful_sync_at`

### 5. oauth_tokens
- `id (uuid, pk)`
- `platform_account_id (fk)`
- `access_token_encrypted`
- `refresh_token_encrypted`
- `expires_at`
- `scopes`
- `created_at`, `updated_at`

### 6. content_items
Canonical content object (post/video/reel/short).
- `id (uuid, pk)`
- `platform_account_id (fk)`
- `external_content_id`
- `content_type (post|video|reel|short|story)`
- `title`
- `published_at`
- `permalink`
- `created_at`

### 7. metric_snapshots (append-only)
Account-level daily/hourly snapshots.
- `id (bigserial, pk)`
- `workspace_id`
- `platform_account_id`
- `snapshot_at (timestamptz)`
- `followers_count`
- `subscribers_count`
- `likes_count`
- `views_count`
- `engagement_total`
- `source_payload_jsonb`
- Unique: `(platform_account_id, snapshot_at)`

### 8. content_metric_snapshots (append-only)
Per-content metrics over time.
- `id (bigserial, pk)`
- `content_item_id (fk)`
- `snapshot_at (timestamptz)`
- `views`
- `likes`
- `comments`
- `shares`
- `engagement_rate`
- `watch_time_seconds`
- Unique: `(content_item_id, snapshot_at)`

### 9. ingestion_jobs
- `id (uuid, pk)`
- `workspace_id`
- `platform_account_id`
- `job_type (account_metrics|content_metrics|backfill)`
- `status (queued|running|success|failed|dead_letter)`
- `attempt_count`
- `error_message`
- `started_at`, `finished_at`

### 10. generated_reports
- `id (uuid, pk)`
- `workspace_id`
- `report_type (weekly|custom_range)`
- `range_start`, `range_end`
- `status (pending|complete|failed)`
- `pdf_uri`, `csv_uri`
- `created_by_user_id`
- `created_at`

### 11. alerts
- `id (uuid, pk)`
- `workspace_id`
- `platform_account_id`
- `alert_type (drop_in_engagement|spike_in_followers|api_failure|rate_limit)`
- `severity (info|warning|critical)`
- `message`
- `triggered_at`
- `resolved_at`

### 12. insight_cards
Materialized coaching recommendations.
- `id (uuid, pk)`
- `workspace_id`
- `platform_account_id nullable`
- `title`
- `description`
- `evidence_jsonb`
- `score`
- `generated_at`

## Key indexing strategy

- `metric_snapshots(platform_account_id, snapshot_at desc)`
- `content_metric_snapshots(content_item_id, snapshot_at desc)`
- `content_items(platform_account_id, published_at desc)`
- `generated_reports(workspace_id, created_at desc)`
- Partial index for active accounts: `platform_accounts(status)` where active

## Data retention

- No hard delete on metrics/snapshots.
- Use partitioning by `snapshot_at` month for snapshot tables.
- Archive report files in object storage; metadata remains indefinitely.
