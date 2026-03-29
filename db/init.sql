CREATE TABLE IF NOT EXISTS platform_accounts (
  id UUID PRIMARY KEY,
  platform TEXT NOT NULL CHECK (platform IN ('facebook','instagram','youtube','tiktok')),
  display_name TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS metric_snapshots (
  id BIGSERIAL PRIMARY KEY,
  platform_account_id UUID NOT NULL REFERENCES platform_accounts(id),
  snapshot_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  followers_count INTEGER NOT NULL DEFAULT 0,
  likes_count INTEGER NOT NULL DEFAULT 0,
  comments_count INTEGER NOT NULL DEFAULT 0,
  shares_count INTEGER NOT NULL DEFAULT 0,
  views_count INTEGER NOT NULL DEFAULT 0,
  UNIQUE(platform_account_id, snapshot_at)
);

CREATE TABLE IF NOT EXISTS generated_reports (
  id UUID PRIMARY KEY,
  report_type TEXT NOT NULL,
  range_start DATE NOT NULL,
  range_end DATE NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  payload_json JSONB NOT NULL
);
