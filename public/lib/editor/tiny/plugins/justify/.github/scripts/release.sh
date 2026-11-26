#!/usr/bin/env bash
# Utilitários de release dos plugins Moodle (usados pelos workflows release.yml e release-check.yml).
# Uso: bash .github/scripts/release.sh <comando> [argumentos]
#
#   meta                  imprime component, release, tag, folder, zip e prerelease (formato chave=valor)
#   not-released          falha se a tag da versão já existe ou já há release com essa tag
#   notes                 confere a seção do CHANGELOG.md e escreve dist/notes.md
#   package [ref]         monta dist/<componente>-<versão>.zip com a pasta raiz do plugin (padrão: HEAD)
#   ci-green [sha]        falha se o CI (checks "Moodle ...") não estiver 100% verde no commit
#   draft                 cria a release como RASCUNHO com o ZIP e as notas (a tag nasce quando for publicada)
#   verify-asset <tag>    confere uma release publicada: tag x version.php, ZIP anexado igual ao do código,
#                         pré-lançamento coerente com a maturidade (apenas aviso) e CI verde
#
# Variáveis: GITHUB_REPOSITORY (dono/repositório) e GH_TOKEN (ou `gh` autenticado) para os comandos que usam a API.
set -euo pipefail

fail() { echo "::error::$*" >&2; exit 1; }

read_version() {
  [ -f version.php ] || fail "version.php not found; run from the plugin root"
  component=$(awk -F"'" '/plugin->component/ {print $2; exit}' version.php)
  release=$(awk -F"'" '/plugin->release/ {print $2; exit}' version.php)
  maturity=$(grep -o 'MATURITY_[A-Z]*' version.php | head -1 || true)
  if [ -z "${component}" ] || [ -z "${release}" ] || [ -z "${maturity}" ]; then
    fail "Could not read component, release and maturity from version.php"
  fi
  folder="${component#*_}"
  zip="${component}-${release}.zip"
  tag="v${release}"
  if [ "${maturity}" = "MATURITY_STABLE" ]; then prerelease=false; else prerelease=true; fi
}

changes() {
  awk -v ver="$release" '
    $0 ~ "^## " ver "( |$)" { found = 1; next }
    found && /^## / { exit }
    found { print }
  ' CHANGELOG.md | sed -e :a -e '/^\n*$/{$d;N;ba' -e '}'
}

cmd="${1:-}"
[ -n "${cmd}" ] || fail "Missing command (meta, not-released, notes, package, ci-green, draft, verify-asset)"
shift || true
read_version

case "${cmd}" in
  meta)
    printf '%s\n' "component=${component}" "release=${release}" "tag=${tag}" "folder=${folder}" "zip=${zip}" "prerelease=${prerelease}"
    ;;

  not-released)
    if git ls-remote --exit-code --tags origin "refs/tags/${tag}" >/dev/null 2>&1; then
      fail "Tag ${tag} already exists. Bump \$plugin->release (and the CHANGELOG) before preparing a new release"
    fi
    if gh release view "${tag}" --repo "${GITHUB_REPOSITORY}" >/dev/null 2>&1; then
      fail "A release for ${tag} already exists (maybe a draft). Publish or delete it first"
    fi
    echo "Version ${release} is not released yet"
    ;;

  notes)
    mkdir -p dist
    changes > dist/changes.md
    grep -q '[^[:space:]]' dist/changes.md || fail "CHANGELOG.md has no section (or an empty one) for version ${release}"
    {
      if [ "${prerelease}" = "true" ]; then
        echo "**Pre-release / Pré-lançamento (${maturity#MATURITY_}).**"
        echo
      fi
      cat dist/changes.md
      echo
      echo "### Instalação / Installation"
      echo "Download \`${zip}\` (root folder \`${folder}\`, as Moodle expects) and install it at *Site administration > Plugins > Install plugins*, then run the Moodle upgrade and purge caches."
      echo "Baixe \`${zip}\`, instale em *Administração do site > Plugins > Instalar plugins*, conclua o upgrade do Moodle e limpe os caches."
      echo
      echo "Docs: https://${GITHUB_REPOSITORY%%/*}.github.io/${GITHUB_REPOSITORY#*/}/"
    } > dist/notes.md
    echo "Release notes written to dist/notes.md"
    ;;

  package)
    ref="${1:-HEAD}"
    mkdir -p dist
    git archive --format=zip --prefix="${folder}/" -o "dist/${zip}" "${ref}"
    unzip -Z1 "dist/${zip}" | grep -qx "${folder}/version.php" || fail "version.php is not inside ${folder}/ in the ZIP"
    ls -l "dist/${zip}"
    echo "Files in the ZIP: $(unzip -Z1 "dist/${zip}" | wc -l)"
    ;;

  ci-green)
    sha="${1:-$(git rev-parse HEAD)}"
    results=$(gh api "repos/${GITHUB_REPOSITORY}/commits/${sha}/check-runs" --paginate \
      --jq '.check_runs[] | select(.name | startswith("Moodle ")) | .conclusion')
    total=$(printf '%s\n' "${results}" | grep -c . || true)
    ok=$(printf '%s\n' "${results}" | grep -c '^success$' || true)
    echo "CI check runs on ${sha:0:7}: ${ok}/${total} successful"
    if [ "${total}" -eq 0 ] || [ "${ok}" -ne "${total}" ]; then
      fail "CI is not green on ${sha:0:7}; fix it (or wait for it to finish) before releasing"
    fi
    ;;

  draft)
    [ -f "dist/${zip}" ] && [ -f dist/notes.md ] || fail "Run notes and package first"
    flags=()
    if [ "${prerelease}" = "true" ]; then flags+=(--prerelease); fi
    url=$(gh release create "${tag}" "dist/${zip}" --repo "${GITHUB_REPOSITORY}" --draft \
      --target "$(git rev-parse HEAD)" --title "${component} ${release}" --notes-file dist/notes.md "${flags[@]}")
    echo "Draft release created: ${url}"
    if [ -n "${GITHUB_STEP_SUMMARY:-}" ]; then
      {
        echo "### Draft release ready: ${component} ${release}"
        echo
        echo "Review the notes and the attached \`${zip}\` at ${url}, then click **Publish release**."
        echo "The tag \`${tag}\` is only created when you publish (the release is immutable after that)."
      } >> "${GITHUB_STEP_SUMMARY}"
    fi
    ;;

  verify-asset)
    want="${1:-}"
    [ -n "${want}" ] || fail "Usage: verify-asset <tag>"
    [ "${want}" = "${tag}" ] || fail "Release tag ${want} does not match \$plugin->release (${release}) in version.php (expected ${tag})"
    info=$(gh release view "${want}" --repo "${GITHUB_REPOSITORY}" --json assets,isPrerelease,isDraft)
    echo "${info}" | grep -q "\"name\":\"${zip}\"" || fail "Release ${want} has no asset named ${zip}. It was published without the package; delete it and prepare it again"
    is_pre=$(echo "${info}" | python3 -c 'import sys,json; print(str(json.load(sys.stdin)["isPrerelease"]).lower())')
    if [ "${prerelease}" = "true" ] && [ "${is_pre}" != "true" ]; then
      # Publishing a non-stable version as a regular release is a deliberate choice of the maintainers: warn only.
      echo "::warning::${maturity} is not stable but the release is not marked as pre-release"
      if [ -n "${GITHUB_STEP_SUMMARY:-}" ]; then
        echo "Warning: ${maturity} is not stable but ${want} is not marked as pre-release." >> "${GITHUB_STEP_SUMMARY}"
      fi
    fi
    if [ "${prerelease}" = "false" ] && [ "${is_pre}" = "true" ]; then
      echo "::warning::${maturity} is stable but the release is marked as pre-release"
    fi
    rm -rf dist && mkdir -p dist/published
    gh release download "${want}" --repo "${GITHUB_REPOSITORY}" --pattern "${zip}" --dir dist/published
    bash "${BASH_SOURCE[0]}" package HEAD >/dev/null
    published=$(sha256sum "dist/published/${zip}" | cut -d' ' -f1)
    rebuilt=$(sha256sum "dist/${zip}" | cut -d' ' -f1)
    echo "published: ${published}"
    echo "rebuilt:   ${rebuilt}"
    [ "${published}" = "${rebuilt}" ] || fail "The attached ${zip} is not identical to the source of ${want}"
    bash "${BASH_SOURCE[0]}" ci-green "$(git rev-parse HEAD)"
    echo "Release ${want} verified"
    ;;

  *)
    fail "Unknown command: ${cmd}"
    ;;
esac
