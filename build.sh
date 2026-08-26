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

echo "==> 1/10  PHP de producao"
"$PHP" -r 'if (version_compare(PHP_VERSION, "8.4", "<") || version_compare(PHP_VERSION, "8.5", ">=")) { fwrite(STDERR, "PHP 8.4 exigido, encontrado ".PHP_VERSION."\n"); exit(1); } echo "PHP ".PHP_VERSION."\n";'

echo "==> 2/10  Dependencias PHP com dev, para testar"
cd "$APP" && "$PHP" "$COMPOSER" install --no-interaction --quiet

echo "==> 3/10  PHPUnit"
"$PHP" artisan test

echo "==> 4/10  Assets do Vite e do Filament"
npm ci
npm run build
"$PHP" artisan filament:assets

echo "==> 5/10  Dependencias PHP sem dev, otimizadas"
"$PHP" "$COMPOSER" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --quiet

echo "==> 6/10  Conteudo do painel (posts, catalogo, links, textos e dados)"
# Tudo que foi cadastrado a mao no painel viaja no pacote: o blog importado do
# WordPress, o catalogo de Educacao, os botoes de /links, os documentos legais
# e os dados de contato. Sem isto, producao sobe vazia e cada um deles teria de
# ser recadastrado. `users` fica de fora de proposito: conta de producao nasce
# la, por `make:filament-user`.
eval "$("$PHP" -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = config("database.connections." . config("database.default"));
printf("DBH=%s\nDBP=%s\nDBN=%s\nDBU=%s\nexport MYSQL_PWD=%s\n",
    escapeshellarg($c["host"]), escapeshellarg($c["port"]),
    escapeshellarg($c["database"]), escapeshellarg($c["username"]),
    escapeshellarg($c["password"]));
')"

mkdir -p "$RAIZ/dist"
mysqldump --host="$DBH" --port="$DBP" --user="$DBU" \
  --no-create-info --complete-insert --skip-extended-insert \
  --no-tablespaces --single-transaction --default-character-set=utf8mb4 \
  "$DBN" categories media posts post_related links education_items \
         textos_legais dados_do_site configuracoes > "$RAIZ/dist/$VERSAO.conteudo.sql"
unset MYSQL_PWD

echo "==> 7/10  Montando $SAIDA"
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

# Arquivos da Biblioteca de Midia. Ficam fora do docroot, servidos so por
# link assinado; as linhas de `media` no dump apontam para estes caminhos,
# entao os dois andam juntos ou nenhum dos dois vai.
mkdir -p "$SAIDA/laravel/storage/app/private"
if [ -d "$APP/storage/app/private/midia" ]; then
  rsync -a --quiet "$APP/storage/app/private/midia" "$SAIDA/laravel/storage/app/private/"
fi

mv "$RAIZ/dist/$VERSAO.conteudo.sql" "$SAIDA/conteudo.sql"

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

echo "==> 8/10  Conferindo o pacote"
[ -f "$SAIDA/laravel/vendor/autoload.php" ]      || { echo "FALTA laravel/vendor/autoload.php"; exit 1; }
[ -f "$SAIDA/public_html/build/manifest.json" ]  || { echo "FALTA public_html/build/manifest.json"; exit 1; }
[ -f "$SAIDA/public_html/index.php" ]            || { echo "FALTA public_html/index.php"; exit 1; }
[ -f "$SAIDA/public_html/.htaccess" ]            || { echo "FALTA public_html/.htaccess"; exit 1; }

[ -s "$SAIDA/conteudo.sql" ]                     || { echo "FALTA conteudo.sql"; exit 1; }

# Cada linha de `media` aponta para um arquivo. Se as contagens divergirem,
# o site sobe com imagem quebrada em post publicado — melhor recusar aqui.
LINHAS=$(grep -c "INSERT INTO \`media\`" "$SAIDA/conteudo.sql")
ARQUIVOS=$(find "$SAIDA/laravel/storage/app/private/midia" -type f 2>/dev/null | wc -l | tr -d " ")
[ "$LINHAS" = "$ARQUIVOS" ] || { echo "biblioteca inconsistente: $LINHAS linhas de media, $ARQUIVOS arquivos"; exit 1; }
echo "no dump: $(grep -c "INSERT INTO \`posts\`" "$SAIDA/conteudo.sql") posts, \
$(grep -c "INSERT INTO \`education_items\`" "$SAIDA/conteudo.sql") itens de Educacao, \
$(grep -c "INSERT INTO \`links\`" "$SAIDA/conteudo.sql") links, \
$(grep -c "INSERT INTO \`textos_legais\`" "$SAIDA/conteudo.sql") textos legais  |  biblioteca: $ARQUIVOS arquivos"

# /links sem botao nenhum e pagina em branco no Instagram. O seeder cria os
# padroes; se nem eles vieram, alguem esvaziou a tabela sem querer.
[ "$(grep -c "INSERT INTO \`links\`" "$SAIDA/conteudo.sql")" -gt 0 ] || { echo "FALTA links no dump"; exit 1; }

# Nada sensivel pode existir dentro do docroot.
for proibido in .env vendor storage bootstrap config database routes resources artisan composer.json; do
  [ -e "$SAIDA/public_html/$proibido" ] && { echo "PROIBIDO dentro do docroot: $proibido"; exit 1; }
done
find "$SAIDA" -name '.env' -print -quit | grep -q . && { echo "PROIBIDO: .env dentro do pacote"; exit 1; }
echo "docroot limpo, aplicacao completa"

echo "==> 9/10  ZIP"
cd "$RAIZ/dist"
rm -f "marina-$VERSAO.zip"
zip -rq "marina-$VERSAO.zip" "$VERSAO"
echo "==> 10/10  Devolvendo dependencias de dev ao diretorio de trabalho"
cd "$APP" && "$PHP" "$COMPOSER" install --no-interaction --quiet

echo
echo "Pacote:  dist/marina-$VERSAO.zip"
echo "Extraido em: dist/$VERSAO"
