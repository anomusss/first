# Jak ściągnąć i uruchomić lokalnie (macOS)

Repozytorium: `https://github.com/anomusss/social-media`

## 1) Pobranie repo (Git)

```bash
cd ~/Desktop
git clone https://github.com/anomusss/social-media.git
cd social-media
```

## 2) Uruchomienie aplikacji

```bash
open URUCHOM.command
```

## 3) Gdy chcesz wypchnąć zmiany na GitHub

```bash
bash scripts/push-to-github.sh
```

Skrypt ustawia `origin` na:
- `https://github.com/anomusss/social-media.git`
i wypycha aktualny branch.

## Typowy problem: `No such file or directory`

To znaczy, że nie jesteś w katalogu projektu. Sprawdź:

```bash
pwd
ls
# powinieneś widzieć m.in. apps/ db/ URUCHOM.command package.json
```

## Po starcie

- Frontend: http://localhost:5173
- API health: http://localhost:4000/api/v1/health
