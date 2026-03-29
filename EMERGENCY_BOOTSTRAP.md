# Emergency bootstrap (gdy repo na GitHub jest puste)

Jeśli po `git clone` masz pusty katalog i brak plików, zrób minimalny start ręcznie:

```bash
cd ~/Desktop/social-media

# 1) stwórz pierwszy plik
cat > README.md <<'TXT'
# social-media

Initial commit.
TXT

# 2) pierwszy commit
git add README.md
git commit -m "chore: initial commit"

# 3) push na GitHub
git push -u origin main || git push -u origin master
```

Po tym repo przestanie być "empty" i można dodawać kolejne pliki normalnie.

## Potem (opcjonalnie) scaffold projektu

Możesz uruchomić komendy tworzące strukturę projektu:

```bash
mkdir -p apps/api/src apps/web/src db scripts
```
