# Application Architecture Plan

## 1) High-level architecture

```text
[React Dashboard] --HTTPS--> [API Gateway / Express API]
                                |      |        |
                                |      |        +--> [Report Service (PDF/CSV)]
                                |      +-----------> [Auth/OAuth Service]
                                |
                                +---------------> [Ingestion Orchestrator]
                                                    |
                                                    +--> [Job Queue (Redis/BullMQ)]
                                                    |
                                                    +--> [Platform Connectors]
                                                             |- Meta (FB/IG)
                                                             |- YouTube
                                                             |- TikTok

[PostgreSQL (OLTP + analytics snapshots)] <--------- all services
[Object Storage (optional report archives)] <------- report service
[Cache layer (Redis)] <----------------------------- API hot metrics
```

## 2) Service decomposition

### A. API Service (Express)
- Exposes REST endpoints for dashboard, detailed analytics, reports, alerts, insights
- Enforces authN/authZ and tenant isolation
- Aggregates metric snapshots for client charts

### B. OAuth/Identity Service
- Social account connection lifecycle
- Token refresh workflows
- Encrypted token vault persistence

### C. Ingestion Service
- Scheduled and on-demand metric pulls
- Handles rate limits and backoff by platform
- Writes immutable snapshots for each metric pull

### D. Report Service
- Weekly report generation scheduler (e.g., every Monday 08:00 local time)
- Generates PDF and CSV artifacts
- Stores report metadata and download URIs

### E. Insights/Rules Engine
- Computes content strategy guidance (best posting windows, best performing formats)
- Evaluates anomaly rules for significant changes and alerts

## 3) Data flow

1. User connects platform via OAuth.
2. Connector credentials are encrypted and stored.
3. Scheduler dispatches ingestion jobs per account/platform.
4. Connector fetches account metrics + content metrics and stores snapshots.
5. Aggregation queries power dashboard, trend charts, and comparisons.
6. Weekly job composes cross-platform report and persists export files.

## 4) Historical retention model (no deletion)

- `metric_snapshots` and `content_snapshots` are append-only.
- Corrections are represented by new records with source timestamps/version.
- Soft-delete only for user-facing entities (e.g., disconnected account), never for historical metrics.
- Optional table partitioning by month for long-term scale.

## 5) Rate-limit and resilience strategy

- Per-platform token bucket limiter
- Exponential backoff with jitter for 429/5xx
- Retry policy with idempotency keys
- Dead-letter queue for repeated failures
- Circuit breaker per provider API

## 6) Security model

- JWT-based sessions + refresh tokens
- OAuth tokens encrypted with envelope encryption (KMS/secret manager)
- RBAC roles: `owner`, `analyst`, `viewer`
- Audit events for login, report export, connector changes
- PII minimization and encrypted at-rest DB volumes

## 7) Observability

- Structured logs (request-id, tenant-id, provider)
- Metrics: ingestion latency, API error rate, queue depth, report generation time
- Traces for connector calls to diagnose API failures
- Alerting for ingestion lag and recurring connector failures

## 8) Deployment recommendation

- Monorepo: `/apps/api`, `/apps/web`, `/apps/worker`
- Containerized services via Docker
- CI/CD pipeline: lint/test/build/deploy
- Managed PostgreSQL + Redis
- Horizontal scaling for worker nodes based on queue depth
