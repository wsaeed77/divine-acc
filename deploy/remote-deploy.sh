#!/usr/bin/env bash
set -euo pipefail
# Sync this repository to the EC2 app root and run deploy/ec2-deploy-app.sh on the server.
#
# Local (PowerShell / Git Bash / WSL):
#   set SSH_KEY_PATH=C:\path\to\key.pem
#   set DEPLOY_HOST=1.2.3.4
#   set DEPLOY_USER=ec2-user
#   bash deploy/remote-deploy.sh
#
# CI (e.g. GitHub Actions): set SSH_PRIVATE_KEY to the full PEM contents, plus DEPLOY_HOST / DEPLOY_USER.

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

DEPLOY_HOST="${DEPLOY_HOST:?Set DEPLOY_HOST (server hostname or IP)}"
DEPLOY_USER="${DEPLOY_USER:-ec2-user}"
REMOTE_PATH="${REMOTE_PATH:-/var/www/divinne-acc}"

SSH_OPTS=( -o StrictHostKeyChecking=accept-new -o BatchMode=yes )

if [[ -n "${SSH_PRIVATE_KEY:-}" ]]; then
  KEY_FILE="$(mktemp)"
  chmod 600 "$KEY_FILE"
  printf '%s\n' "$SSH_PRIVATE_KEY" > "$KEY_FILE"
  trap 'rm -f "$KEY_FILE"' EXIT
  SSH_OPTS+=( -i "$KEY_FILE" )
elif [[ -n "${SSH_KEY_PATH:-}" ]]; then
  SSH_OPTS+=( -i "$SSH_KEY_PATH" )
else
  echo "Set SSH_PRIVATE_KEY (CI) or SSH_KEY_PATH (local path to .pem)" >&2
  exit 1
fi

RSYNC_EXCLUDES=(
  '--exclude=.git/'
  '--exclude=node_modules/'
  '--exclude=vendor/'
  '--exclude=.env'
  '--exclude=storage/logs/'
  '--exclude=storage/framework/cache/'
  '--exclude=storage/framework/sessions/'
  '--exclude=storage/framework/views/'
  '--exclude=public/hot'
  '--exclude=public/build'
)

# Intentionally no --delete: avoids removing production uploads under storage/app/public and
# other server-only files. To force a strict mirror, add --delete and narrow excludes.
rsync -avz \
  "${RSYNC_EXCLUDES[@]}" \
  -e "ssh ${SSH_OPTS[*]}" \
  ./ "${DEPLOY_USER}@${DEPLOY_HOST}:${REMOTE_PATH}/"

ssh "${SSH_OPTS[@]}" "${DEPLOY_USER}@${DEPLOY_HOST}" \
  "cd ${REMOTE_PATH} && bash deploy/ec2-deploy-app.sh"

echo "Remote deploy finished."
