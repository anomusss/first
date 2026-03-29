# Implementation Roadmap & Timeline Estimates

## Team assumption

- 1 full-stack engineer
- 1 frontend engineer
- 1 part-time designer/PM
- 1 QA/shared role

## Phase plan (12 weeks)

### Phase 0 (Week 1): Foundations
- Monorepo setup, CI/CD, lint/test format tooling
- Base auth (email/password + JWT)
- Workspace/membership scaffolding
- PostgreSQL schema migrations

### Phase 1 (Weeks 2-4): Integrations & ingestion
- OAuth flow for Meta, YouTube, TikTok
- Token encryption and refresh lifecycle
- Ingestion worker + job queue
- Initial account-level metric snapshots
- Rate-limit + retry handling

### Phase 2 (Weeks 5-7): Dashboard analytics UI
- Dashboard overview cards and trend charts
- Platform detail pages
- Content listing + performance breakdown
- Comparative period API and charts

### Phase 3 (Weeks 8-9): Reporting system
- Automated weekly report job
- PDF rendering templates
- CSV export endpoint and UI
- Report history and download center

### Phase 4 (Weeks 10-11): Insights + alerts
- Rule-based alerts for spikes/drops/failures
- Coaching insights (top formats, posting times)
- Notification center UI and alert resolution workflow

### Phase 5 (Week 12): Hardening & launch
- Security review and audit logging
- Load/performance testing
- Error budget dashboards and observability tuning
- UAT, bug bash, and production rollout

## Risk register

1. **API scope/approval delays** (especially TikTok/Meta permissions)
   - Mitigation: start with sandbox/dev mode early and parallel approval process.
2. **Provider API inconsistency across account types**
   - Mitigation: capability matrix per connected account.
3. **Rate-limit bottlenecks**
   - Mitigation: adaptive polling cadence + queue prioritization.
4. **Historical storage growth**
   - Mitigation: partitioning + compressed archival strategy.

## Third-party APIs and libraries checklist

### Provider APIs
- Meta Graph API
- YouTube Data API v3
- YouTube Analytics API
- TikTok Business/Display API

### Backend libs
- `express`, `zod`, `jsonwebtoken`, `passport`
- `bullmq`, `ioredis`
- `pg` or `prisma`
- `winston`/`pino` for structured logging
- `axios` with retry middleware
- `pdfkit` or `playwright` (HTML-to-PDF)

### Frontend libs
- `react`, `react-router-dom`
- `@tanstack/react-query`
- `recharts` or `echarts`
- `react-hook-form` + `zod`
- `date-fns`

### DevOps/tooling
- Docker, GitHub Actions
- OpenTelemetry SDK
- Sentry (optional)
