#!/usr/bin/env bash
set -euo pipefail

# Default remote URL (used if none is provided)
DEFAULT_REMOTE_URL="git@github.com:anilkumarthakur60/hbl-api.git"

# Usage:
#   ./push_current_branch_then_cleanup.sh
#   ./push_current_branch_then_cleanup.sh git@github.com:someone/other-repo.git
#
# Optional env var:
#   REMOTE_NAME=dev

REMOTE_URL="${1:-$DEFAULT_REMOTE_URL}"
REMOTE_NAME="${REMOTE_NAME:-dev}"

# Ensure we're inside a git repo
git rev-parse --is-inside-work-tree >/dev/null 2>&1

# Detect current active branch
CURRENT_BRANCH="$(git symbolic-ref --short HEAD)"

if [[ -z "${CURRENT_BRANCH}" ]]; then
  echo "Error: could not determine current branch."
  exit 1
fi

echo "Current branch: ${CURRENT_BRANCH}"

# Remove remote if it already exists
if git remote get-url "${REMOTE_NAME}" >/dev/null 2>&1; then
  echo "Remote '${REMOTE_NAME}' already exists. Removing it first..."
  git remote remove "${REMOTE_NAME}"
fi

echo "Adding temporary remote '${REMOTE_NAME}' → ${REMOTE_URL}"
git remote add "${REMOTE_NAME}" "${REMOTE_URL}"

echo "Pushing '${CURRENT_BRANCH}' to '${REMOTE_NAME}'..."
git push "${REMOTE_NAME}" "${CURRENT_BRANCH}"

echo "Removing temporary remote '${REMOTE_NAME}'..."
git remote remove "${REMOTE_NAME}"

echo
echo "✅ Done. Current remotes:"
git remote -v
