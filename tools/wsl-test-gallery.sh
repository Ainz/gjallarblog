#!/bin/bash
set -euo pipefail
action=${1:?Start, Status or Stop required}
source_root=${2:?Windows project mounted path required}
runtime=/var/lib/gjallarblog-dev
[[ $source_root == /mnt/?/* && -f $source_root/main.inc.php ]] || { echo 'Invalid project source path' >&2; exit 64; }
command -v docker >/dev/null || { echo 'Docker Engine is missing in Debian' >&2; exit 69; }
case "$action" in
  Start)
    systemctl is-active --quiet docker || systemctl start docker
    docker compose version
    install -d -m 700 "$runtime" "$runtime/dev" "$runtime/.dev" "$runtime/.dev/core" "$runtime/.dev/package"
    cp "$source_root/dev/compose.yaml" "$source_root/dev/Dockerfile" "$source_root/dev/php.ini" "$runtime/dev/"
    cp "$source_root/.dockerignore" "$runtime/"
    cp "$source_root/.dev/core/piwigo-16.4.0.zip" "$runtime/.dev/core/"
    install -m 600 "$source_root/.dev/gallery.env" "$runtime/dev/.env"
    install -d "$runtime/.dev/package/gjallarblog"
    cp -a "$source_root/.dev/package/gjallarblog/." "$runtime/.dev/package/gjallarblog/"
    rm -f "$runtime/.dev/package/gjallarblog/maintain.inc.php"
    docker compose --env-file "$runtime/dev/.env" -f "$runtime/dev/compose.yaml" config --quiet
    docker compose --env-file "$runtime/dev/.env" -f "$runtime/dev/compose.yaml" up -d --build
    ;;
  Status)
    [[ -f $runtime/dev/compose.yaml ]] || { echo 'Gallery is not provisioned'; exit 0; }
    docker compose --env-file "$runtime/dev/.env" -f "$runtime/dev/compose.yaml" ps
    ;;
  Stop)
    [[ -f $runtime/dev/compose.yaml ]] || exit 0
    docker compose --env-file "$runtime/dev/.env" -f "$runtime/dev/compose.yaml" down
    ;;
  *) echo 'Unknown action' >&2; exit 64 ;;
esac
