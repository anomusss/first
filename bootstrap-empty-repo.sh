#!/bin/bash
set -euo pipefail

# Run this inside an EMPTY cloned repo, e.g. ~/Desktop/social-media

mkdir -p apps/api/src apps/web/src db scripts docs

cat > .gitignore <<'EOG'
node_modules/
dist/
.env
EOG

cat > .env.example <<'EOG'
PORT=4000
DATABASE_URL=postgresql://social:social@localhost:5432/social_monitor
CORS_ORIGIN=http://localhost:5173
EOG

cat > package.json <<'EOG'
{
  "name": "social-monitor-dashboard",
  "private": true,
  "workspaces": ["apps/api", "apps/web"],
  "scripts": {
    "dev": "npm run dev -w apps/api & npm run dev -w apps/web && wait",
    "db:up": "docker compose up -d postgres",
    "db:down": "docker compose down"
  }
}
EOG

cat > docker-compose.yml <<'EOG'
services:
  postgres:
    image: postgres:16
    environment:
      POSTGRES_DB: social_monitor
      POSTGRES_USER: social
      POSTGRES_PASSWORD: social
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./db/init.sql:/docker-entrypoint-initdb.d/init.sql:ro
volumes:
  postgres_data:
EOG

cat > db/init.sql <<'EOG'
CREATE TABLE IF NOT EXISTS platform_accounts (
  id UUID PRIMARY KEY,
  platform TEXT NOT NULL,
  display_name TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE TABLE IF NOT EXISTS metric_snapshots (
  id BIGSERIAL PRIMARY KEY,
  platform_account_id UUID REFERENCES platform_accounts(id),
  snapshot_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  followers_count INTEGER NOT NULL DEFAULT 0,
  likes_count INTEGER NOT NULL DEFAULT 0,
  comments_count INTEGER NOT NULL DEFAULT 0,
  shares_count INTEGER NOT NULL DEFAULT 0,
  views_count INTEGER NOT NULL DEFAULT 0
);
EOG

cat > apps/api/package.json <<'EOG'
{
  "name": "@apps/api",
  "type": "module",
  "scripts": {"dev": "node --watch src/index.js"},
  "dependencies": {"cors":"^2.8.5","dotenv":"^16.4.5","express":"^4.19.2","pg":"^8.12.0"}
}
EOG

cat > apps/api/src/index.js <<'EOG'
import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import pkg from 'pg';
const { Pool } = pkg;
const app = express();
const pool = new Pool({ connectionString: process.env.DATABASE_URL || 'postgresql://social:social@localhost:5432/social_monitor' });
app.use(cors({ origin: process.env.CORS_ORIGIN || 'http://localhost:5173' }));
app.get('/api/v1/health', async (_req, res) => {
  try { await pool.query('SELECT 1'); res.json({ status: 'ok' }); }
  catch (e) { res.status(500).json({ status: 'error', message: e.message }); }
});
app.listen(process.env.PORT || 4000, () => console.log('API on :4000'));
EOG

cat > apps/web/package.json <<'EOG'
{"name":"@apps/web","type":"module","scripts":{"dev":"node server.js"},"dependencies":{"express":"^4.19.2"}}
EOG

cat > apps/web/server.js <<'EOG'
import express from 'express';
const app = express();
app.use(express.static('src'));
app.listen(5173, () => console.log('Web on :5173'));
EOG

cat > apps/web/src/index.html <<'EOG'
<!doctype html><html><body style="font-family:Arial"><h1>Social Monitor MVP</h1><p>API: <a href="http://localhost:4000/api/v1/health">health</a></p></body></html>
EOG

cat > URUCHOM.command <<'EOG'
#!/bin/bash
set -e
cd "$(dirname "$0")"
cp -n .env.example .env || true
npm run db:up
npm install
npm run dev
EOG
chmod +x URUCHOM.command

cat > README.md <<'EOG'
# Social Monitor MVP

To repo było puste na GitHub. Ten projekt został wygenerowany lokalnie skryptem `bootstrap-empty-repo.sh`.

## Start

```bash
open URUCHOM.command
```
EOG

git add .
git commit -m "Bootstrap project into previously empty GitHub repository"

echo "Gotowe. Teraz wykonaj: git push -u origin main (albo aktualny branch)"
