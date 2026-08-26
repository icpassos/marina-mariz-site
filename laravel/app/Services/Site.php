<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\Configuracao;
use App\Models\DadosDoSite;
use App\Models\TextoLegal;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Fonte unica dos dados do site (docs 03 e 06). Rodape, menu, paginas
 * legais e schema.org leem daqui — nenhum desses valores fica escrito em
 * pagina nenhuma. E o que impede telefone certo no rodape e telefone
 * velho na pagina de contato.
 *
 * O site le isto varias vezes por requisicao, entao fica no cache do
 * Laravel; salvar no painel derruba as tres chaves.
 */
class Site
{
    private const CACHE = [
        'site.dados',
        'site.configuracoes',
        'site.textos-legais',
    ];

    public static function limparCache(): void
    {
        foreach (self::CACHE as $chave) {
            Cache::forget($chave);
        }
    }

    public static function dados(): DadosDoSite
    {
        return Cache::rememberForever('site.dados', fn () => DadosDoSite::instancia());
    }

    public static function configuracoes(): Configuracao
    {
        return Cache::rememberForever(
            'site.configuracoes',
            fn () => Configuracao::instancia()->load('imagemDeCompartilhamento'),
        );
    }

    // ── Contato ──────────────────────────────────────────────────────

    /** (31) 99608-2883 — o banco guarda so digitos. */
    public static function telefoneFormatado(): ?string
    {
        return self::formatarTelefone(self::dados()->telefone);
    }

    public static function whatsappFormatado(): ?string
    {
        return self::formatarTelefone(self::dados()->whatsapp);
    }

    public static function linkTel(): ?string
    {
        $telefone = self::dados()->telefone;

        return filled($telefone) ? "tel:+55{$telefone}" : null;
    }

    public static function linkWhatsapp(): ?string
    {
        $whatsapp = self::dados()->whatsapp;

        return filled($whatsapp) ? "https://wa.me/55{$whatsapp}" : null;
    }

    private static function formatarTelefone(?string $digitos): ?string
    {
        if (blank($digitos)) {
            return null;
        }

        // 11 digitos e celular (9 na frente), 10 e fixo.
        return strlen($digitos) === 11
            ? sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7))
            : sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
    }

    // ── Endereco ─────────────────────────────────────────────────────

    /**
     * Linhas do endereco como o rodape as empilha.
     *
     * @return array<int, string>
     */
    public static function enderecoLinhas(): array
    {
        $dados = self::dados();

        $segunda = implode(' — ', array_filter([$dados->endereco_complemento, $dados->endereco_bairro]));
        $terceira = implode(' - ', array_filter([$dados->endereco_cidade, $dados->endereco_estado]));

        return array_values(array_filter([$dados->endereco_logradouro, $segunda, $terceira]));
    }

    // ── Identificacao profissional ───────────────────────────────────

    /**
     * "Marina Mariz — MÉDICO — CRM-MG 48.386". A palavra obrigatoria e o
     * registro sao exigencia da Resolucao CFM 2.336/2023 para divulgacao
     * de servico medico, por isso entram literais.
     */
    public static function identificacaoProfissional(): string
    {
        $dados = self::dados();

        return implode(' — ', array_filter([
            $dados->nome_divulgacao,
            $dados->palavra_obrigatoria,
            $dados->crm,
        ]));
    }

    /**
     * Cada RQE ao lado da sua especialidade — a correspondencia vem
     * cadastrada em par, nunca deduzida pela ordem dos numeros.
     *
     * @return array<int, string>
     */
    public static function especialidadesComRqe(): array
    {
        return collect(self::dados()->especialidades ?? [])
            ->filter(fn (array $item): bool => filled($item['especialidade'] ?? null))
            ->map(fn (array $item): string => implode(' — ', array_filter([
                $item['especialidade'],
                filled($item['rqe'] ?? null) ? "RQE {$item['rqe']}" : null,
            ])))
            ->values()
            ->all();
    }

    // ── Redes ────────────────────────────────────────────────────────

    /**
     * Rede oculta some do site sem perder o link cadastrado.
     *
     * @return array<string, array{rotulo: string, url: string}>
     */
    public static function redesVisiveis(): array
    {
        $redes = self::dados()->redes ?? [];
        $visiveis = [];

        foreach (DadosDoSite::REDES as $chave => $rotulo) {
            if (! ($redes[$chave]['visivel'] ?? false)) {
                continue;
            }

            // WhatsApp nao tem URL propria: o link vem do numero do bloco
            // Contato, para nao existirem dois numeros no cadastro.
            $url = $chave === 'whatsapp' ? self::linkWhatsapp() : ($redes[$chave]['url'] ?? null);

            if (filled($url)) {
                $visiveis[$chave] = ['rotulo' => $rotulo, 'url' => $url];
            }
        }

        return $visiveis;
    }

    // ── Textos legais ────────────────────────────────────────────────

    /**
     * Texto em uso no site, com as variaveis do sistema ja substituidas.
     * Devolve null enquanto o texto nunca foi publicado.
     *
     * @return array{titulo: ?string, conteudo: string, atualizado_em: ?Carbon}|null
     */
    public static function textoLegal(string $chave): ?array
    {
        return self::textosLegais()[$chave] ?? null;
    }

    /**
     * @return array<string, array{titulo: ?string, conteudo: string, atualizado_em: ?Carbon, indice: array<int, array{id: string, titulo: string}>, minutos: int}>
     */
    public static function textosLegais(): array
    {
        return Cache::rememberForever('site.textos-legais', function (): array {
            $variaveis = self::variaveis();

            return TextoLegal::query()
                ->whereNotNull('conteudo_publicado')
                ->get()
                ->mapWithKeys(function (TextoLegal $texto) use ($variaveis): array {
                    $html = RichContentRenderer::make($texto->conteudo_publicado)
                        ->mergeTags($variaveis)
                        ->toHtml();

                    [$html, $indice] = self::ancorasDoTextoLegal($html);

                    return [$texto->chave => [
                        'titulo' => $texto->titulo_publicado,
                        'conteudo' => $html,
                        'atualizado_em' => $texto->publicado_em,
                        'indice' => $indice,
                        'minutos' => self::minutosDeLeitura($html),
                    ]];
                })
                ->all();
        });
    }

    /**
     * Poe uma ancora em cada `<h2>` do texto publicado e devolve o indice na
     * mesma ordem. O sumario da pagina vem daqui, nao de uma lista escrita a
     * mao no Blade: renomear uma secao no painel renomeia no indice, e o
     * link para de apontar para um `id` que nao existe.
     *
     * @return array{0: string, 1: array<int, array{id: string, titulo: string}>}
     */
    private static function ancorasDoTextoLegal(string $html): array
    {
        $indice = [];
        $usados = [];

        $html = (string) preg_replace_callback(
            '/<h2\b([^>]*)>(.*?)<\/h2>/s',
            function (array $achado) use (&$indice, &$usados): string {
                $titulo = trim(html_entity_decode(strip_tags($achado[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $id = Str::slug($titulo) ?: 'secao';

                // Dois titulos iguais nao podem virar o mesmo `id`.
                $usados[$id] = ($usados[$id] ?? 0) + 1;

                if ($usados[$id] > 1) {
                    $id .= '-'.$usados[$id];
                }

                $indice[] = ['id' => $id, 'titulo' => $titulo];

                return '<h2 id="'.e($id).'"'.$achado[1].'>'.$achado[2].'</h2>';
            },
            $html,
        );

        return [$html, $indice];
    }

    /** Tempo de leitura a 200 palavras por minuto, no minimo 1. */
    private static function minutosDeLeitura(string $html): int
    {
        $palavras = str_word_count(strip_tags($html), 0, 'áàâãéêíóôõúüçÁÀÂÃÉÊÍÓÔÕÚÜÇ');

        return max(1, (int) ceil($palavras / 200));
    }

    /**
     * Variaveis do sistema disponiveis dentro dos textos legais. Contato,
     * endereco e identificacao entram por aqui; nunca sao copiados a mao
     * para dentro do texto.
     *
     * @return array<string, string>
     */
    public static function variaveis(): array
    {
        $dados = self::dados();

        return [
            'email' => (string) $dados->email,
            'telefone' => (string) self::telefoneFormatado(),
            'whatsapp' => (string) self::whatsappFormatado(),
            'endereco' => implode(', ', self::enderecoLinhas()),
            'horario_atendimento' => (string) $dados->horario_atendimento,
            'identificacao_profissional' => self::identificacaoProfissional(),
        ];
    }

    /** Rotulos das variaveis, para o menu do editor rico. */
    public static function rotulosDasVariaveis(): array
    {
        return array_keys(self::variaveis());
    }

    /** Status que um texto legal pode ter: nao existe "arquivado" aqui. */
    public static function statusDeTextoLegal(): array
    {
        return [
            ContentStatus::Rascunho->value => ContentStatus::Rascunho->getLabel(),
            ContentStatus::Publicado->value => ContentStatus::Publicado->getLabel(),
        ];
    }
}
