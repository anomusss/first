# Social Media Monitoring Dashboard (Local MVP)

Repo: `https://github.com/anomusss/social-media`

Jeśli **nie widzisz plików na GitHub**, to prawie zawsze znaczy, że lokalne commity nie zostały wypchnięte (`git push`).

## Naprawa w 3 komendach

```bash
cd ~/Desktop/social-media
bash scripts/github-status.sh
git push -u origin $(git rev-parse --abbrev-ref HEAD)
```

Pełna instrukcja: `KROKI_GITHUB.md`.

## Start aplikacji

```bash
open URUCHOM.command
```

## ZIP lokalnie

```bash
bash scripts/create-release-zip.sh
```

Powstanie: `dist/social-monitor-dashboard.zip`
