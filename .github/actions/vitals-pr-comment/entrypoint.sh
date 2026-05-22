#!/usr/bin/env bash
# Laravel Vitals — PR performance comment
# Posts a Markdown delta table on the PR comparing preview vs base scores.
set -euo pipefail

WORKING_DIR="${WORKING_DIR:-.}"
PREVIEW_URL="${PREVIEW_URL:-}"
BASE_URL="${BASE_URL:-}"
FAIL_ON_REGRESSION="${FAIL_ON_REGRESSION:-false}"
REGRESSION_THRESHOLD="${REGRESSION_THRESHOLD:-5}"
DEVICES="${DEVICES:-mobile}"
BASELINE_FILE="${WORKING_DIR}/vitals-baseline.json"

if [[ -z "$PREVIEW_URL" ]]; then
    echo "::error::preview-url input is required"
    exit 1
fi

# ── helpers ────────────────────────────────────────────────────────────────────

jq_field() {
    echo "${1}" | jq -r "${2} // \"n/a\""
}

score_delta_icon() {
    local base="${1}"
    local preview="${2}"
    local threshold="${REGRESSION_THRESHOLD}"

    if [[ "$base" == "n/a" || "$preview" == "n/a" ]]; then
        echo "→"
        return
    fi

    local delta
    delta=$(echo "$preview $base" | awk '{printf "%d", $1 - $2}')

    if (( delta < -threshold )); then
        echo "🔴 ${delta}"
    elif (( delta > 0 )); then
        echo "🟢 +${delta}"
    else
        echo "→"
    fi
}

ms_delta_icon() {
    local base="${1}"
    local preview="${2}"
    local threshold=300   # ms regression threshold

    if [[ "$base" == "n/a" || "$preview" == "n/a" ]]; then
        echo "→"
        return
    fi

    local delta
    delta=$(echo "$preview $base" | awk '{printf "%d", $1 - $2}')

    if (( delta > threshold )); then
        echo "🔴 +${delta}ms"
    elif (( delta < 0 )); then
        echo "🟢 ${delta}ms"
    else
        echo "→"
    fi
}

cls_delta_icon() {
    local base="${1}"
    local preview="${2}"

    if [[ "$base" == "n/a" || "$preview" == "n/a" ]]; then
        echo "→"
        return
    fi

    local worse
    worse=$(echo "$preview $base" | awk '{print ($1 > $2 + 0.05) ? "1" : "0"}')

    if [[ "$worse" == "1" ]]; then
        echo "🔴 +$(echo "$preview $base" | awk '{printf "%.3f", $1 - $2}')"
    else
        echo "→"
    fi
}

has_regression() {
    local base="${1}"
    local preview="${2}"
    local threshold="${REGRESSION_THRESHOLD}"

    if [[ "$base" == "n/a" || "$preview" == "n/a" ]]; then
        echo "0"
        return
    fi

    echo "$preview $base $threshold" | awk '{print ($1 < $2 - $3) ? "1" : "0"}'
}

# ── audit preview ──────────────────────────────────────────────────────────────

echo "::group::Running Vitals audit against preview URL"
cd "${WORKING_DIR}"

IFS=',' read -ra DEVICE_LIST <<< "${DEVICES}"
PREVIEW_RESULTS="{}"

for DEVICE in "${DEVICE_LIST[@]}"; do
    DEVICE=$(echo "$DEVICE" | xargs)  # trim whitespace
    echo "Auditing preview (${DEVICE}): ${PREVIEW_URL}"

    RESULT=$(php artisan vitals:audit --json --device="${DEVICE}" --url="${PREVIEW_URL}" 2>/dev/null || echo "{}")
    PREVIEW_RESULTS=$(echo "${PREVIEW_RESULTS}" | jq --arg device "${DEVICE}" --argjson result "${RESULT}" '. + {($device): $result}')
done

echo "::endgroup::"

# ── resolve baseline ──────────────────────────────────────────────────────────

BASE_RESULTS="{}"

if [[ -n "$BASE_URL" ]]; then
    echo "::group::Running Vitals audit against base URL"
    for DEVICE in "${DEVICE_LIST[@]}"; do
        DEVICE=$(echo "$DEVICE" | xargs)
        echo "Auditing base (${DEVICE}): ${BASE_URL}"
        RESULT=$(php artisan vitals:audit --json --device="${DEVICE}" --url="${BASE_URL}" 2>/dev/null || echo "{}")
        BASE_RESULTS=$(echo "${BASE_RESULTS}" | jq --arg device "${DEVICE}" --argjson result "${RESULT}" '. + {($device): $result}')
    done
    echo "::endgroup::"
elif [[ -f "${BASELINE_FILE}" ]]; then
    echo "Using baseline from ${BASELINE_FILE}"
    BASE_RESULTS=$(cat "${BASELINE_FILE}")
else
    echo "::warning::No base-url and no vitals-baseline.json found. Delta columns will be empty."
fi

# ── build markdown comment ─────────────────────────────────────────────────────

REGRESSION_FOUND=0
COMMENT_BODY="## ⚡ Laravel Vitals — preview perf\n\n"

for DEVICE in "${DEVICE_LIST[@]}"; do
    DEVICE=$(echo "$DEVICE" | xargs)

    P_SCORE=$(echo "${PREVIEW_RESULTS}" | jq -r ".${DEVICE}.score_performance // \"n/a\"")
    P_ACCESS=$(echo "${PREVIEW_RESULTS}" | jq -r ".${DEVICE}.score_accessibility // \"n/a\"")
    P_LCP=$(echo "${PREVIEW_RESULTS}"    | jq -r ".${DEVICE}.lcp_ms // \"n/a\"")
    P_INP=$(echo "${PREVIEW_RESULTS}"    | jq -r ".${DEVICE}.inp_ms // \"n/a\"")
    P_CLS=$(echo "${PREVIEW_RESULTS}"    | jq -r ".${DEVICE}.cls // \"n/a\"")
    P_TTFB=$(echo "${PREVIEW_RESULTS}"   | jq -r ".${DEVICE}.ttfb_ms // \"n/a\"")
    P_AUDIT_ID=$(echo "${PREVIEW_RESULTS}" | jq -r ".${DEVICE}.id // \"\"")

    B_SCORE=$(echo "${BASE_RESULTS}" | jq -r ".${DEVICE}.score_performance // \"n/a\"")
    B_ACCESS=$(echo "${BASE_RESULTS}" | jq -r ".${DEVICE}.score_accessibility // \"n/a\"")
    B_LCP=$(echo "${BASE_RESULTS}"   | jq -r ".${DEVICE}.lcp_ms // \"n/a\"")
    B_INP=$(echo "${BASE_RESULTS}"   | jq -r ".${DEVICE}.inp_ms // \"n/a\"")
    B_CLS=$(echo "${BASE_RESULTS}"   | jq -r ".${DEVICE}.cls // \"n/a\"")
    B_TTFB=$(echo "${BASE_RESULTS}"  | jq -r ".${DEVICE}.ttfb_ms // \"n/a\"")

    D_SCORE=$(score_delta_icon  "$B_SCORE"  "$P_SCORE")
    D_ACCESS=$(score_delta_icon "$B_ACCESS" "$P_ACCESS")
    D_LCP=$(ms_delta_icon       "$B_LCP"    "$P_LCP")
    D_INP=$(ms_delta_icon       "$B_INP"    "$P_INP")
    D_TTFB=$(ms_delta_icon      "$B_TTFB"   "$P_TTFB")
    D_CLS=$(cls_delta_icon      "$B_CLS"    "$P_CLS")

    # Format LCP/INP/TTFB as seconds for display
    fmt_ms() {
        local v="${1}"
        if [[ "$v" == "n/a" ]]; then echo "n/a"; return; fi
        echo "$v" | awk '{printf "%.2fs", $1/1000}'
    }

    P_LCP_FMT=$(fmt_ms "$P_LCP")
    P_INP_FMT=$(fmt_ms "$P_INP")
    P_TTFB_FMT=$(fmt_ms "$P_TTFB")
    B_LCP_FMT=$(fmt_ms "$B_LCP")
    B_INP_FMT=$(fmt_ms "$B_INP")
    B_TTFB_FMT=$(fmt_ms "$B_TTFB")

    # Check for regressions
    if [[ "$(has_regression "$B_SCORE" "$P_SCORE")" == "1" ]] || \
       [[ "$(has_regression "$B_ACCESS" "$P_ACCESS")" == "1" ]]; then
        REGRESSION_FOUND=1
    fi

    AUDIT_LINK=""
    if [[ -n "$P_AUDIT_ID" ]]; then
        VITALS_URL="${APP_URL:-${PREVIEW_URL}}"
        AUDIT_LINK="\n\n[View full audit →](${VITALS_URL}/vitals/audits/${P_AUDIT_ID})"
    fi

    COMMENT_BODY+="### Device: ${DEVICE}\n\n"
    COMMENT_BODY+="| Metric | Base | Preview | Δ |\n"
    COMMENT_BODY+="|---|---|---|---|\n"
    COMMENT_BODY+="| Performance | ${B_SCORE} | ${P_SCORE} | ${D_SCORE} |\n"
    COMMENT_BODY+="| Accessibility | ${B_ACCESS} | ${P_ACCESS} | ${D_ACCESS} |\n"
    COMMENT_BODY+="| LCP | ${B_LCP_FMT} | ${P_LCP_FMT} | ${D_LCP} |\n"
    COMMENT_BODY+="| INP | ${B_INP_FMT} | ${P_INP_FMT} | ${D_INP} |\n"
    COMMENT_BODY+="| CLS | ${B_CLS} | ${P_CLS} | ${D_CLS} |\n"
    COMMENT_BODY+="| TTFB | ${B_TTFB_FMT} | ${P_TTFB_FMT} | ${D_TTFB} |\n"
    COMMENT_BODY+="${AUDIT_LINK}\n\n"
done

COMMENT_BODY+="<sub>Generated by [Laravel Vitals](https://github.com/corentinbtmps/laravel-vitals) · $(date -u '+%Y-%m-%d %H:%M UTC')</sub>"

# ── post PR comment ────────────────────────────────────────────────────────────

echo "::group::Posting PR comment"
# Delete previous Vitals comment if it exists (idempotent updates)
EXISTING_COMMENT_ID=$(gh api \
    --method GET \
    "repos/${GITHUB_REPOSITORY}/issues/${GITHUB_REF_NAME#refs/pull/}/comments" \
    --jq '.[] | select(.body | startswith("## ⚡ Laravel Vitals")) | .id' \
    2>/dev/null | head -n1 || true)

if [[ -n "$EXISTING_COMMENT_ID" ]]; then
    gh api --method DELETE "repos/${GITHUB_REPOSITORY}/issues/comments/${EXISTING_COMMENT_ID}" || true
fi

# Post new comment
PR_NUMBER="${PR_NUMBER:-}"
if [[ -z "$PR_NUMBER" ]]; then
    # Try to extract from GITHUB_REF (refs/pull/42/merge)
    PR_NUMBER=$(echo "${GITHUB_REF:-}" | grep -oP '(?<=pull/)\d+' || true)
fi

if [[ -n "$PR_NUMBER" ]]; then
    echo -e "$COMMENT_BODY" | gh pr comment "${PR_NUMBER}" --body-file -
else
    echo "::warning::Could not determine PR number. Printing comment to stdout instead."
    echo -e "$COMMENT_BODY"
fi
echo "::endgroup::"

# ── exit code ─────────────────────────────────────────────────────────────────

if [[ "$FAIL_ON_REGRESSION" == "true" && "$REGRESSION_FOUND" -eq 1 ]]; then
    echo "::error::Performance regression detected! Score dropped by more than ${REGRESSION_THRESHOLD} points."
    exit 1
fi

exit 0
