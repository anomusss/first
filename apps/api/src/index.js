import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import pkg from 'pg';
import { v4 as uuidv4 } from 'uuid';

const { Pool } = pkg;
const app = express();

const port = Number(process.env.PORT || 4000);
const corsOrigin = process.env.CORS_ORIGIN || 'http://localhost:5173';
const connectionString = process.env.DATABASE_URL || 'postgresql://social:social@localhost:5432/social_monitor';

const pool = new Pool({ connectionString });

app.use(cors({ origin: corsOrigin }));
app.use(express.json());

async function seedIfNeeded() {
  const count = await pool.query('SELECT COUNT(*)::int AS count FROM platform_accounts');
  if (count.rows[0].count > 0) return;

  const accounts = [
    { id: uuidv4(), platform: 'facebook', display_name: 'My FB Page' },
    { id: uuidv4(), platform: 'instagram', display_name: 'My IG Business' },
    { id: uuidv4(), platform: 'youtube', display_name: 'My YouTube Channel' },
    { id: uuidv4(), platform: 'tiktok', display_name: 'My TikTok Creator' }
  ];

  for (const account of accounts) {
    await pool.query(
      'INSERT INTO platform_accounts (id, platform, display_name) VALUES ($1, $2, $3)',
      [account.id, account.platform, account.display_name]
    );

    const base = Math.floor(Math.random() * 15000) + 1000;
    for (let i = 7; i >= 0; i--) {
      const followers = base + (7 - i) * Math.floor(Math.random() * 40 + 10);
      const likes = Math.floor(followers * 0.12);
      const comments = Math.floor(likes * 0.18);
      const shares = Math.floor(likes * 0.08);
      const views = likes * 7;
      await pool.query(
        `INSERT INTO metric_snapshots
          (platform_account_id, snapshot_at, followers_count, likes_count, comments_count, shares_count, views_count)
         VALUES ($1, NOW() - ($2 || ' days')::interval, $3, $4, $5, $6, $7)
         ON CONFLICT DO NOTHING`,
        [account.id, i, followers, likes, comments, shares, views]
      );
    }
  }
}

app.get('/api/v1/health', async (_req, res) => {
  try {
    await pool.query('SELECT 1');
    res.json({ status: 'ok', database: 'connected' });
  } catch (error) {
    res.status(500).json({ status: 'error', database: 'disconnected', message: error.message });
  }
});

app.get('/api/v1/dashboard/overview', async (_req, res) => {
  try {
    const rows = await pool.query(`
      WITH latest AS (
        SELECT pa.platform, pa.display_name,
               ms.followers_count, ms.likes_count, ms.comments_count, ms.shares_count, ms.views_count,
               ROW_NUMBER() OVER (PARTITION BY pa.id ORDER BY ms.snapshot_at DESC) AS rn
        FROM platform_accounts pa
        JOIN metric_snapshots ms ON ms.platform_account_id = pa.id
      )
      SELECT platform, display_name, followers_count, likes_count, comments_count, shares_count, views_count
      FROM latest
      WHERE rn = 1
      ORDER BY platform;
    `);

    const totals = rows.rows.reduce(
      (acc, row) => {
        acc.followers += row.followers_count;
        acc.engagement += row.likes_count + row.comments_count + row.shares_count;
        acc.views += row.views_count;
        return acc;
      },
      { followers: 0, engagement: 0, views: 0 }
    );

    res.json({ totals, platforms: rows.rows, generatedAt: new Date().toISOString() });
  } catch (error) {
    res.status(500).json({ error: { code: 'INTERNAL_ERROR', message: error.message } });
  }
});

app.get('/api/v1/analytics/:platform/growth', async (req, res) => {
  const { platform } = req.params;
  try {
    const rows = await pool.query(
      `SELECT DATE(ms.snapshot_at) AS date, AVG(ms.followers_count)::int AS followers
       FROM metric_snapshots ms
       JOIN platform_accounts pa ON pa.id = ms.platform_account_id
       WHERE pa.platform = $1
       GROUP BY DATE(ms.snapshot_at)
       ORDER BY DATE(ms.snapshot_at)`,
      [platform]
    );
    res.json({ platform, series: rows.rows });
  } catch (error) {
    res.status(500).json({ error: { code: 'INTERNAL_ERROR', message: error.message } });
  }
});

app.post('/api/v1/reports/weekly/generate', async (_req, res) => {
  try {
    const now = new Date();
    const end = new Date(now);
    const start = new Date(now);
    start.setDate(now.getDate() - 7);

    const overview = await pool.query('SELECT COUNT(*)::int AS accounts FROM platform_accounts');
    const payload = {
      summary: 'Automated weekly report generated successfully.',
      accounts: overview.rows[0].accounts
    };

    const reportId = uuidv4();
    await pool.query(
      `INSERT INTO generated_reports (id, report_type, range_start, range_end, payload_json)
       VALUES ($1, 'weekly', $2, $3, $4::jsonb)`,
      [reportId, start.toISOString().slice(0, 10), end.toISOString().slice(0, 10), JSON.stringify(payload)]
    );

    res.status(201).json({ reportId, rangeStart: start, rangeEnd: end, payload });
  } catch (error) {
    res.status(500).json({ error: { code: 'REPORT_FAILED', message: error.message } });
  }
});

app.use((_req, res) => {
  res.status(404).json({ error: { code: 'NOT_FOUND', message: 'Endpoint not found' } });
});

app.listen(port, async () => {
  try {
    await seedIfNeeded();
    console.log(`API ready on http://localhost:${port}`);
  } catch (error) {
    console.error('Failed to seed database:', error.message);
  }
});
