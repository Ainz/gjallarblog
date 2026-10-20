#!/bin/bash
set -euo pipefail
source_root=${GJALLARBLOG_SOURCE_ROOT:-/mnt/d/Development/gjallarblog}
runtime=/var/lib/gjallarblog-alpha
install -d -m700 "$runtime" "$runtime/dev" "$runtime/.dev/core" "$runtime/.dev/package"
cp "$source_root/.dockerignore" "$runtime/"
cp "$source_root/dev/Alpha.Dockerfile" "$source_root/dev/php.ini" "$runtime/dev/"
cp -a "$source_root/.dev/package/gjallarblog" "$runtime/.dev/package/"
for core in 15.7.0 16.4.0; do
  test -f "$runtime/.dev/core/piwigo-$core.zip" || curl -fLsS "https://piwigo.org/download/dlcounter.php?code=$core" -o "$runtime/.dev/core/piwigo-$core.zip"
done
printf '%s  %s\n' '41b899cc2d8954bc3024c55b3476ced9104e530d328e6b647f3051f65744d6b6' "$runtime/.dev/core/piwigo-15.7.0.zip" '72c58a337a1a0639b6ad8dc8f5900a2781fc19091b60ae157eb2ee58c5431c17' "$runtime/.dev/core/piwigo-16.4.0.zip" | sha256sum -c -
cd "$runtime"
entries=('15.7.0 8.3 8089 a15' '16.4.0 8.4 8090 a16')
if [[ ${1:-} == --extended ]]; then entries+=('15.7.0 8.2 8091 a1582' '16.4.0 8.2 8092 a1682' '16.4.0 8.3 8093 a1683' '16.4.0 8.5 8094 a1685'); fi
for entry in "${entries[@]}"; do
  read -r core php port project <<< "$entry"
  password=$(head -c24 /dev/urandom | od -An -tx1 | tr -d ' \n')
  mkdir -p "$source_root/.dev/$project"
  if [[ -f "$source_root/.dev/$project/gallery.env" ]]; then
    password=$(sed -n 's/^DB_PASSWORD=//p' "$source_root/.dev/$project/gallery.env")
  else
    printf 'DB_PASSWORD=%s\n' "$password" > "$source_root/.dev/$project/gallery.env"
  fi
  cat > "$runtime/$project.yaml" <<EOF
name: gjallarblog-$project
services:
  gallery:
    build:
      context: .
      dockerfile: dev/Alpha.Dockerfile
      args:
        PHP_VERSION: "$php"
        PIWIGO_VERSION: "$core"
    ports: ["127.0.0.1:$port:80"]
    depends_on:
      db:
        condition: service_healthy
    volumes:
      - ./.dev/package/gjallarblog:/var/www/html/plugins/gjallarblog:ro
      - config:/var/www/html/local/config
      - data:/var/www/html/_data
  db:
    image: mariadb:11.4
    environment:
      MARIADB_RANDOM_ROOT_PASSWORD: "yes"
      MARIADB_DATABASE: piwigo
      MARIADB_USER: piwigo
      MARIADB_PASSWORD: "$password"
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 3s
      timeout: 5s
      retries: 40
    volumes: ["db:/var/lib/mysql"]
volumes:
  config:
  data:
  db:
EOF
  chmod 600 "$runtime/$project.yaml"
  docker compose -f "$runtime/$project.yaml" up -d --build > "$runtime/$project-build.log" 2>&1 || { tail -60 "$runtime/$project-build.log"; exit 1; }
  echo "Ready: $core / PHP $php at localhost:$port"
done
sha256sum "$runtime"/.dev/core/*.zip
