# Social Media Monitoring Dashboard (Local MVP)

Jeśli klonujesz `https://github.com/anomusss/social-media` i repo jest puste (empty repository), to normalne: nie ma jeszcze żadnych plików/commitów.

## Szybkie rozwiązanie dla pustego repo

W pustym sklonowanym repo uruchom:

```bash
bash bootstrap-empty-repo.sh
```

Ten skrypt:
- tworzy cały szkielet projektu (API + web + DB + skrypty),
- robi pierwszy commit,
- na końcu podaje komendę `git push`.

## Po bootstrapie

```bash
git push -u origin $(git rev-parse --abbrev-ref HEAD)
open URUCHOM.command
```

## Dlaczego miałeś błędy

- `No such file or directory` — bo repo na GitHub było puste, więc lokalnie nie było plików `scripts/*` i `URUCHOM.command`.
- `src refspec HEAD does not match any` — bo nie było jeszcze żadnego commita.
