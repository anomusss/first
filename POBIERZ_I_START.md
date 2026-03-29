# Jak ściągnąć i uruchomić lokalnie (macOS)

Masz błąd `No such file or directory`, bo komendę uruchomiłeś w `~` (home), a nie w folderze projektu.

## Najważniejsze: musisz być w katalogu projektu

```bash
cd ~/Desktop/social-monitor-dashboard
ls
# powinieneś widzieć m.in. apps/ db/ URUCHOM.command package.json
```

Dopiero wtedy uruchamiaj komendy.

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
3. Wejdź do rozpakowanego folderu, np.:
   ```bash
   cd ~/Downloads/social-monitor-dashboard-main
   ```
4. Uruchom:
   ```bash
   open URUCHOM.command
   ```

## Jeśli „nie widzisz repo na GitHub”

To normalne, jeśli branch/zmiany nie zostały jeszcze wypchnięte do Twojego repo.
Musisz mieć repo online i wypchnięty branch, albo dostać ZIP od osoby, która ma kod.

## Co robi `URUCHOM.command`

- sprawdza czy jesteś w katalogu projektu,
- sprawdza Node/npm/Docker,
- tworzy `.env` z `.env.example` (jeśli brak),
- odpala PostgreSQL (`npm run db:up`),
- instaluje zależności (`npm install`),
- opcjonalnie tworzy ZIP (`dist/social-monitor-dashboard.zip`),
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
