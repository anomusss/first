# Jak ściągnąć i uruchomić lokalnie (macOS)

## Opcja A: przez Git (polecane)

```bash
cd ~/Desktop
git clone <TWOJ_URL_REPO> social-monitor-dashboard
cd social-monitor-dashboard
open URUCHOM.command
```

## Opcja B: ZIP z GitHub

1. Na GitHub kliknij **Code → Download ZIP**.
2. Rozpakuj paczkę na komputerze.
3. Wejdź do rozpakowanego folderu.
4. Kliknij dwukrotnie `URUCHOM.command` (albo w terminalu: `open URUCHOM.command`).

## Co robi `URUCHOM.command`

- sprawdza Node/npm/Docker,
- tworzy `.env` z `.env.example` (jeśli brak),
- odpala PostgreSQL (`npm run db:up`),
- instaluje zależności (`npm install`),
- uruchamia API + frontend (`npm run dev`).

## Po starcie

- Frontend: http://localhost:5173
- API health: http://localhost:4000/api/v1/health

## Zatrzymanie

W osobnym terminalu:

```bash
cd <folder_projektu>
npm run db:down
```
