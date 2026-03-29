#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

echo "== Social Media Monitoring Dashboard: start =="

if ! command -v node >/dev/null 2>&1; then
  echo "[ERROR] Brak Node.js. Zainstaluj Node.js 20+ i uruchom ponownie."
  exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "[ERROR] Brak npm. Zainstaluj npm 10+ i uruchom ponownie."
  exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "[ERROR] Brak Dockera. Zainstaluj Docker Desktop i uruchom ponownie."
  exit 1
fi

if [ ! -f .env ]; then
  cp .env.example .env
  echo "[OK] Utworzono .env z .env.example"
fi

echo "[1/3] Uruchamiam PostgreSQL przez Docker..."
npm run db:up

echo "[2/3] Instaluję zależności npm..."
npm install

echo "[3/3] Uruchamiam API + Frontend..."
echo "Frontend: http://localhost:5173"
echo "API:      http://localhost:4000/api/v1/health"

npm run dev
