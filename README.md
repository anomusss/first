# Social Media Monitoring Dashboard (Local MVP)

Jeśli klonujesz `https://github.com/anomusss/social-media` i repo jest puste, najpierw musisz zrobić **pierwszy commit**.

## Szybki fix (bez żadnych skryptów)

```bash
cd ~/Desktop/social-media
cat > README.md <<'TXT'
# social-media

Initial commit.
TXT

git add README.md
git commit -m "chore: initial commit"
git push -u origin main || git push -u origin master
```

Dopiero po tym GitHub przestanie pokazywać "empty repository".

## Dlaczego wcześniej nie działało

- `bootstrap-empty-repo.sh: No such file or directory` → bo repo było puste, więc plik nie istniał.
- `src refspec HEAD does not match any` → bo nie było żadnego commita (HEAD nie istniał).
- `open URUCHOM.command` → ten plik też nie mógł istnieć w pustym repo.

Szczegóły: `EMERGENCY_BOOTSTRAP.md`.
