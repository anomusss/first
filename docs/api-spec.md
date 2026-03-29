# API Endpoints Specification (Express REST)

Base URL: `/api/v1`
Auth: Bearer JWT

## Authentication

- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/refresh`
- `POST /auth/logout`

## Workspace & users

- `GET /workspaces`
- `POST /workspaces`
- `GET /workspaces/:workspaceId`
- `POST /workspaces/:workspaceId/members`
- `PATCH /workspaces/:workspaceId/members/:userId`

## Social account connections (OAuth)

- `GET /integrations/:platform/oauth/start`
- `GET /integrations/:platform/oauth/callback`
- `GET /integrations/accounts`
- `DELETE /integrations/accounts/:accountId` (disconnect; does not delete historical data)
- `POST /integrations/accounts/:accountId/sync`

## Dashboard overview

- `GET /dashboard/overview?workspaceId=...&from=...&to=...`
  - Returns:
    - aggregate engagement metrics across platforms
    - follower/subscriber growth deltas
    - top content snippets
    - directional indicators (up/down percentages)

- `GET /dashboard/realtime?workspaceId=...`
  - Cached current metrics and ingestion freshness status

## Platform analytics

- `GET /analytics/:platform/summary?workspaceId=...&from=...&to=...`
- `GET /analytics/:platform/growth?workspaceId=...&interval=daily|weekly`
- `GET /analytics/:platform/content?workspaceId=...&sort=engagement_rate&limit=50`
- `GET /analytics/:platform/content/:contentId/timeseries?metric=views`

## Comparative analytics

- `POST /analytics/compare-periods`
  - Body:
    - `workspaceId`
    - `periodA { from, to }`
    - `periodB { from, to }`
    - `platforms[]`

## Reports

- `POST /reports/weekly/generate`
- `POST /reports/custom/generate`
- `GET /reports?workspaceId=...`
- `GET /reports/:reportId`
- `GET /reports/:reportId/download?format=pdf|csv`

## Alerts & insights

- `GET /alerts?workspaceId=...&status=open`
- `PATCH /alerts/:alertId/resolve`
- `GET /insights?workspaceId=...`

## Webhooks (optional future)

- `POST /webhooks/meta`
- `POST /webhooks/youtube`
- `POST /webhooks/tiktok`

## Error handling contract

Standard JSON error envelope:

```json
{
  "error": {
    "code": "RATE_LIMITED",
    "message": "Provider API rate limit reached",
    "details": {"platform": "youtube"},
    "requestId": "req_123"
  }
}
```

## Rate limit behavior

- Internal API throttling per workspace/user
- Connector-level backoff on provider throttling
- Endpoint responses include `X-RateLimit-*` headers where applicable
