# Frontend Component Structure (React)

## Proposed app structure

```text
apps/web/src/
  app/
    routes.tsx
    providers/
      AuthProvider.tsx
      QueryProvider.tsx
      ThemeProvider.tsx
  pages/
    DashboardPage.tsx
    PlatformDetailPage.tsx
    ReportsPage.tsx
    InsightsPage.tsx
    SettingsIntegrationsPage.tsx
    LoginPage.tsx
  components/
    layout/
      AppShell.tsx
      Sidebar.tsx
      TopNav.tsx
    dashboard/
      PlatformOverviewCards.tsx
      GrowthIndicator.tsx
      RealtimeStatusBadge.tsx
      EngagementTrendChart.tsx
      CrossPlatformComparisonChart.tsx
      TopContentTable.tsx
    platform/
      PlatformSummaryHeader.tsx
      ContentPerformanceTable.tsx
      ContentTrendChart.tsx
    reports/
      ReportGeneratorForm.tsx
      ReportHistoryTable.tsx
      ExportButtons.tsx
    insights/
      InsightCardList.tsx
      RecommendationPanel.tsx
    common/
      DateRangePicker.tsx
      MetricCard.tsx
      ErrorState.tsx
      EmptyState.tsx
  services/
    apiClient.ts
    dashboardService.ts
    analyticsService.ts
    reportsService.ts
    integrationsService.ts
  hooks/
    useDashboardOverview.ts
    usePlatformAnalytics.ts
    useReports.ts
    useAlerts.ts
  types/
    api.ts
    metrics.ts
```

## UX requirements mapping

- **Overview of all platforms** → `DashboardPage` + `PlatformOverviewCards`
- **Real-time metrics** → polling + websocket/SSE fallback in `RealtimeStatusBadge`
- **Growth arrows/percentages** → `GrowthIndicator`
- **Detailed platform views** → `PlatformDetailPage`
- **Historical trend graphs** → chart components with date range selector
- **Report generation/export** → `ReportsPage`, `ReportGeneratorForm`, `ExportButtons`
- **Coaching insights** → `InsightsPage`, `RecommendationPanel`

## State/data strategy

- TanStack Query for server state + caching
- URL-driven filters (`from`, `to`, `platform`, `metric`)
- optimistic UI only where safe (alerts resolve)
- explicit loading/error/empty states for all async views

## Browser support

- Latest stable Chrome, Edge, Firefox, Safari
- Responsive layout for desktop/tablet (mobile read-only optional)
