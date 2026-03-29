# Nie widzisz plików na GitHub? Zrób to krok po kroku

Rozumiem frustrację — jeśli nie ma `git push`, plików nie będzie widać na GitHub.

## 1) Wejdź do katalogu projektu

```bash
cd ~/Desktop/social-media
```

## 2) Sprawdź status repo

```bash
bash scripts/github-status.sh
```

## 3) Ustaw origin (jeśli potrzeba)

```bash
git remote remove origin 2>/dev/null || true
git remote add origin https://github.com/anomusss/social-media.git
```

## 4) Wypchnij branch

```bash
git push -u origin $(git rev-parse --abbrev-ref HEAD)
```

## 5) Jeśli push odrzuca hasło

GitHub wymaga tokena (PAT) albo SSH key.

### Opcja HTTPS + PAT
1. Utwórz token na GitHub (Settings → Developer settings → Personal access tokens).
2. Przy `git push` podaj:
   - username: Twój login GitHub
   - password: **token PAT** (nie hasło konta)

### Opcja SSH
```bash
ssh-keygen -t ed25519 -C "twoj_email@example.com"
cat ~/.ssh/id_ed25519.pub
```
Wklej klucz publiczny na GitHub (Settings → SSH and GPG keys), potem ustaw remote SSH.
