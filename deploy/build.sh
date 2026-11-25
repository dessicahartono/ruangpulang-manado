#!/usr/bin/env bash
# Membuat file zip siap unggah untuk hosting tanpa akses SSH.
#
#   deploy/build.sh           Deploy pertama: kode, vendor, .env, dan database baru.
#   deploy/build.sh --update  Pembaruan: hanya kode dan vendor. Tidak menyentuh
#                             .env, database, dan foto unggahan yang ada di server.
#
# Hasilnya ada di folder build/. Jangan di-commit, isinya memuat rahasia.
set -euo pipefail

MODE=full
[[ "${1:-}" == "--update" ]] && MODE=update

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/build"
STAGE="$OUT/ruangpulang"
ZIP="$OUT/ruangpulang-$MODE.zip"
APP_URL="${APP_URL:-https://ruangpulang.org}"

rm -rf "$STAGE" "$ZIP"
mkdir -p "$STAGE"

# 1. Salin kode ke folder sementara supaya proyek asli tidak terganggu.
rsync -a \
  --exclude '.git' --exclude '.DS_Store' --exclude '.claude' --exclude '.idea' --exclude '.vscode' \
  --exclude '.phpunit.cache' --exclude '.phpunit.result.cache' --exclude '/phpunit.xml' \
  --exclude '/build' --exclude '/vendor' --exclude '/node_modules' \
  --exclude '/tests' --exclude '/docs' --exclude '/ruang-pulang-ui' \
  --exclude '/.env' --exclude '/.env.backup' --exclude '/.env.production' \
  --exclude '/database/database.sqlite' \
  --exclude '/public/uploads' --exclude '/public/storage' --exclude '/public/hot' \
  --exclude '/storage/logs/*' \
  --exclude '/storage/framework/cache/*' --exclude '/storage/framework/sessions/*' \
  --exclude '/storage/framework/views/*' --exclude '/storage/framework/testing/*' \
  "$ROOT/" "$STAGE/"

cd "$STAGE"
rm -f bootstrap/cache/*.php

# 2. Pasang paket tanpa paket pengembangan.
composer install --no-dev --optimize-autoloader --no-interaction

if [[ "$MODE" == "full" ]]; then
  set_env() { perl -pi -e "s{^$1=.*\$}{$1=$2}" .env; }

  # 3. .env produksi.
  cp .env.example .env
  set_env APP_ENV production
  set_env APP_DEBUG false
  set_env APP_URL "$APP_URL"
  set_env LOG_LEVEL error
  php artisan key:generate --force

  # 4. Database baru berisi data awal (pengurus dan artikel contoh).
  mkdir -p public/uploads/pengurus public/uploads/sponsor
  : > database/database.sqlite
  php artisan migrate --seed --force

  # 5. Kata sandi admin. Hash-nya disimpan di .env.
  if [[ -t 0 ]]; then
    php artisan admin:password
  else
    echo "Terminal tidak interaktif, kata sandi admin belum dibuat." >&2
  fi
  grep -q '^ADMIN_PASSWORD_HASH=' .env \
    || echo "PERHATIAN: ADMIN_PASSWORD_HASH belum ada di .env. Halaman Kelola tidak bisa dibuka." >&2
fi

# 6. Bersihkan sisa log dan cache dari proses build.
find storage -type f ! -name '.gitignore' -delete
rm -f bootstrap/cache/*.php.tmp

# 7. Zip. Titik di akhir ikut menyertakan file tersembunyi seperti .htaccess dan .env.
zip -rqX "$ZIP" . -x '.DS_Store' -x '*/.DS_Store'

echo
echo "Selesai: $ZIP ($(du -h "$ZIP" | cut -f1))"
if [[ "$MODE" == "full" ]]; then
  echo "File ini memuat .env dan kata sandi admin. Hapus setelah diunggah."
fi
