# Social Media Monitoring Dashboard (Local MVP)

To jest **działający lokalnie** starter full-stack (API + frontend + Postgres), który możesz uruchomić na MacBooku z terminala.

## Ważne (żeby uniknąć błędu jak na screenie)

Jeśli widzisz błąd typu:

```bash
bash: scripts/create-release-zip.sh: No such file or directory
```

to znaczy, że nie jesteś w katalogu projektu. Najpierw:

```bash
cd ~/Desktop/social-monitor-dashboard
```

Dopiero potem uruchamiaj skrypty.

## Najszybszy start

1. Ściągnij repo (git clone lub ZIP).
2. Wejdź do katalogu projektu.
3. Otwórz plik `URUCHOM.command`.

Szczegóły krok po kroku: `POBIERZ_I_START.md`.

## Chcesz gotową paczkę ZIP z tego repo?

```bash
cd ~/Desktop/social-monitor-dashboard
bash scripts/create-release-zip.sh
```

Powstanie plik:

- `dist/social-monitor-dashboard.zip`

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

## Zatrzymanie środowiska

```bash
npm run db:down
```
