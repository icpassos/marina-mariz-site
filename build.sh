#!/usr/bin/env bash
#
# Monta o pacote de producao no layout fora do docroot:
#
#   dist/<versao>/laravel/       aplicacao, nunca servida pelo Apache
#   dist/<versao>/public_html/   docroot, so o conteudo de public/
#
# Uso:  ./build.sh v0.1.0
#
set -euo pipefail

VERSAO="${1:?informe a versao, ex: ./build.sh v0.1.0}"
RAIZ="$(cd "$(dirname "$0")" && pwd)"
APP="$RAIZ/laravel"
SAIDA="$RAIZ/dist/$VERSAO"

PHP="${PHP:-/opt/homebrew/opt/php@8.4/bin/php}"
COMPOSER="${COMPOSER:-$(command -v composer)}"

echo "==> 1/9  PHP de producao"
"$PHP" -r 'if (version_compare(PHP_VERSION, "8.4", "<") || version_compare(PHP_VERSION, "8.5", ">=")) { fwrite(STDERR, "PHP 8.4 exigido, encontrado ".PHP_VERSION."\n"); exit(1); } echo "PHP ".PHP_VERSION."\n";'

echo "==> 2/9  Dependencias PHP com dev, para testar"
cd "$APP" && "$PHP" "$COMPOSER" install --no-interaction --quiet

echo "==> 3/9  PHPUnit"
"$PHP" artisan test

echo "==> 4/9  Assets do Vite e do Filament"
npm ci
npm run build
"$PHP" artisan filament:assets

echo "==> 5/9  Dependencias PHP sem dev, otimizadas"
"$PHP" "$COMPOSER" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --quiet

echo "==> 6/9  Montando $SAIDA"
rm -rf "$SAIDA"
mkdir -p "$SAIDA/laravel" "$SAIDA/public_html"

# Aplicacao. Fora: segredos, git, node_modules, testes, uploads reais,
# logs, cache e o proprio public/ (vai para o docroot).
rsync -a --quiet \
  --exclude '.env' --exclude '.env.*' --exclude 'auth.json' \
  --exclude '.git' --exclude '.github' \
  --exclude 'node_modules' --exclude 'tests' \
  --exclude 'public' \
  --exclude 'storage/app/private/*' --exclude 'storage/app/public/*' \
  --exclude 'storage/framework/cache/data/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  --exclude 'storage/framework/testing/*' \
  --exclude 'storage/logs/*' \
  --exclude '.phpunit.result.cache' --exclude '.phpunit.cache' \
  "$APP/" "$SAIDA/laravel/"

# Docroot: somente o conteudo de public/.
rsync -a --quiet "$APP/public/" "$SAIDA/public_html/"

# Os tres caminhos que o index.php resolve passam a apontar para a pasta
# irma. E a unica edicao de codigo que o layout exige.
"$PHP" -r '
$f = $argv[1];
$s = file_get_contents($f);
foreach (["storage/framework/maintenance.php", "vendor/autoload.php", "bootstrap/app.php"] as $alvo) {
    $de = "__DIR__.\"/../" . $alvo . "\"";
    $de = str_replace("\"", "\x27", $de);
    $para = str_replace("/../", "/../laravel/", $de);
    if (! str_contains($s, $de)) { fwrite(STDERR, "caminho nao encontrado no index.php: $alvo\n"); exit(1); }
    $s = str_replace($de, $para, $s);
}
file_put_contents($f, $s);
echo "index.php reapontado para ../laravel\n";
' "$SAIDA/public_html/index.php"

echo "==> 7/9  Conferindo o pacote"
[ -f "$SAIDA/laravel/vendor/autoload.php" ]      || { echo "FALTA laravel/vendor/autoload.php"; exit 1; }
[ -f "$SAIDA/public_html/build/manifest.json" ]  || { echo "FALTA public_html/build/manifest.json"; exit 1; }
[ -f "$SAIDA/public_html/index.php" ]            || { echo "FALTA public_html/index.php"; exit 1; }
[ -f "$SAIDA/public_html/.htaccess" ]            || { echo "FALTA public_html/.htaccess"; exit 1; }

# Nada sensivel pode existir dentro do docroot.
for proibido in .env vendor storage bootstrap config database routes resources artisan composer.json; do
  [ -e "$SAIDA/public_html/$proibido" ] && { echo "PROIBIDO dentro do docroot: $proibido"; exit 1; }
done
find "$SAIDA" -name '.env' -print -quit | grep -q . && { echo "PROIBIDO: .env dentro do pacote"; exit 1; }
echo "docroot limpo, aplicacao completa"

echo "==> 8/9  ZIP"
cd "$RAIZ/dist"
rm -f "marina-$VERSAO.zip"
zip -rq "marina-$VERSAO.zip" "$VERSAO"
echo "==> 9/9  Devolvendo dependencias de dev ao diretorio de trabalho"
cd "$APP" && "$PHP" "$COMPOSER" install --no-interaction --quiet

echo
echo "Pacote:  dist/marina-$VERSAO.zip"
echo "Extraido em: dist/$VERSAO"
