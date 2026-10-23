#!/bin/bash
set -euo pipefail
source_root=${1:?Windows project mounted path required}
[[ $source_root == /mnt/?/* && -f $source_root/plugin/main.inc.php ]] || exit 64
docker cp "$source_root/dev/create-fixtures.php" gjallarblog-dev-gallery-1:/tmp/create-fixtures.php
docker exec gjallarblog-dev-gallery-1 php /tmp/create-fixtures.php
mkdir -p "$source_root/.dev/fixtures"
docker cp gjallarblog-dev-gallery-1:/var/www/html/_data/gjallarblog-fixtures/. "$source_root/.dev/fixtures/"
docker cp "$source_root/dev/seed-gallery-settings.php" gjallarblog-dev-gallery-1:/tmp/seed-gallery-settings.php
docker exec gjallarblog-dev-gallery-1 php /tmp/seed-gallery-settings.php
