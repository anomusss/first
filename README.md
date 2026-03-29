# Social Media Monitoring Dashboard (Local MVP)

To jest **działający lokalnie** starter full-stack (API + frontend + Postgres), który możesz uruchomić na MacBooku z terminala.

## Najszybszy start

1. Ściągnij repo (git clone lub ZIP).
2. Otwórz plik `URUCHOM.command` w katalogu projektu.
3. Gotowe — skrypt sam zrobi konfigurację i odpali aplikację.

Szczegóły krok po kroku: `POBIERZ_I_START.md`.

## Chcesz gotową paczkę ZIP z tego repo?

W projekcie jest skrypt, który buduje paczkę do pobrania:

```bash
bash scripts/create-release-zip.sh
```

Po wykonaniu dostaniesz plik:

- `dist/social-monitor-dashboard.zip`

Ten ZIP zawiera cały projekt (bez `.git`, `node_modules`, `dist`).

## Co zawiera projekt

- `apps/api` — Node.js + Express API z endpointami dashboardu
- `apps/web` — prosta przeglądarkowa aplikacja dashboardu
- `db/init.sql` — schemat bazy PostgreSQL
- `docker-compose.yml` — lokalna baza danych
- `URUCHOM.command` — automatyczny start projektu na macOS
- `scripts/create-release-zip.sh` — tworzenie paczki ZIP
- `docs/` — pełna dokumentacja architektury i roadmap

## Ręczny start (alternatywa)

```bash
cp .env.example .env
npm run db:up
npm install
npm run dev
```

Po starcie:
- Frontend: http://localhost:5173
- API: http://localhost:4000/api/v1/health

## Najważniejsze endpointy

- `GET /api/v1/health`
- `GET /api/v1/dashboard/overview`
- `GET /api/v1/analytics/:platform/growth`
- `POST /api/v1/reports/weekly/generate`

## Zatrzymanie środowiska

```bash
npm run db:down
```
