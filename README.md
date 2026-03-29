# Social Media Monitoring Dashboard (Local MVP)

Repo: `https://github.com/anomusss/social-media`

To jest **działający lokalnie** starter full-stack (API + frontend + Postgres), który możesz uruchomić na MacBooku z terminala.

## Szybki start (dokładnie dla Twojego repo)

```bash
cd ~/Desktop
git clone https://github.com/anomusss/social-media.git
cd social-media
open URUCHOM.command
```

## Jeśli chcesz od razu wypchnąć zmiany na GitHub

```bash
bash scripts/push-to-github.sh
```

Skrypt ustawia/zastępuje `origin` i robi `git push -u origin <aktualny-branch>`.

## Chcesz paczkę ZIP z kodem?

```bash
bash scripts/create-release-zip.sh
```

Powstanie plik:

- `dist/social-monitor-dashboard.zip`

## Co zawiera projekt

- `apps/api` — Node.js + Express API
- `apps/web` — przeglądarkowy dashboard
- `db/init.sql` — schemat PostgreSQL
- `docker-compose.yml` — lokalna baza
- `URUCHOM.command` — automatyczny start na macOS
- `scripts/create-release-zip.sh` — tworzenie ZIP
- `scripts/push-to-github.sh` — push do GitHub repo
- `docs/` — dokumentacja architektury i roadmap
