{{--
    Aviso de Cookies. Cartao fixo no canto direito da tela, sobre o
    conteudo — nao e modal e nao escurece a pagina: travar a leitura e o
    jeito mais rapido de a pessoa clicar em "aceitar tudo" sem ler.

    Aparece enquanto nao houver escolha registrada na versao atual do
    aviso. Depois de escolher, sai e so volta pelo link "Preferencias de
    cookies" do rodape, que aponta para `#aviso-de-cookies`: e o `:target`
    que reabre o cartao, sem depender de JavaScript.

    O desenho sai do DS: superficie e borda do `.card`, raio `--radius-md`,
    botoes `.btn`, toggles `.switch` e escala tipografica em tokens.

    Contrato com o layout: enquanto o cartao esta visivel, o `<html>` ganha
    `data-aviso-de-cookies` e a variavel `--altura-aviso-de-cookies` com a
    altura real dele. A barra fixa de agendamento deve sumir com esse
    atributo, para nada ficar coberto (doc 00, acessibilidade global).
--}}
@php
    use App\Support\AvisoDeCookies;

    $escolha = $cookiesEscolha ?? AvisoDeCookies::escolha(request());
    $configuracoes = \App\Services\Site::configuracoes();
@endphp

{{-- `scheme-04` para os tokens do DS resolverem aqui dentro, seja qual for
     o esquema da secao que ficou atras do cartao. --}}
<div id="aviso-de-cookies" class="aviso-de-cookies scheme-04" tabindex="-1" role="region"
     aria-label="Aviso de cookies" aria-live="polite" {{ $escolha ? 'hidden' : '' }}>
    <form class="aviso-de-cookies__caixa" method="POST" action="{{ route('cookies.preferencias') }}">
        @csrf

        <p class="aviso-de-cookies__texto">
            Usamos cookies para o site funcionar e para entender como ele é usado.
            Você escolhe o que aceitar.
            <a href="{{ url('/politica-de-privacidade') }}">Ver a Política de Privacidade</a>
        </p>

        <div class="aviso-de-cookies__acoes">
            {{-- Os tres botoes com o mesmo peso visual: esconder ou apagar
                 "Recusar" e pratica proibida. --}}
            <details class="aviso-de-cookies__personalizar">
                <summary class="btn btn--outline" aria-expanded="false"><span class="btn__label">Personalizar</span></summary>

                <div class="aviso-de-cookies__painel">
                    <h2 class="aviso-de-cookies__titulo" id="aviso-de-cookies-titulo" tabindex="-1">
                        Escolha o que aceitar
                    </h2>

                    <div class="switch-stack">
                        <label class="switch">
                            {{-- Sempre ligado: `disabled` nao envia nada, e nao ha
                                 o que enviar — necessarios nao se desligam. --}}
                            <input type="checkbox" role="switch" checked disabled>
                            <span class="inner" aria-hidden="true"><span class="tick"></span></span>
                            <span>
                                <b>Necessários</b>
                                <small class="choice-helper">
                                    Mantêm o site funcionando: sessão, segurança e sua própria
                                    escolha de cookies. Fornecedor: este site. Duração: 12 meses.
                                </small>
                            </span>
                        </label>

                        <label class="switch">
                            <input type="checkbox" role="switch" name="analiticos" value="1"
                                   @checked($escolha['analiticos'] ?? false)>
                            <span class="inner" aria-hidden="true"><span class="tick"></span></span>
                            <span>
                                <b>Analíticos</b>
                                <small class="choice-helper">
                                    Mostram como o site é usado, de forma agregada, para melhorá-lo.
                                    @if (filled($configuracoes->google_analytics_id))
                                        Fornecedor: Google Analytics. Duração: até 2 anos.
                                    @elseif (filled($configuracoes->google_tag_manager_id))
                                        Fornecedor: Google Tag Manager. Duração: até 2 anos.
                                    @else
                                        Nenhuma ferramenta analítica está configurada no momento.
                                    @endif
                                </small>
                            </span>
                        </label>

                        <label class="switch">
                            <input type="checkbox" role="switch" name="marketing" value="1"
                                   @checked($escolha['marketing'] ?? false)>
                            <span class="inner" aria-hidden="true"><span class="tick"></span></span>
                            <span>
                                <b>Marketing</b>
                                <small class="choice-helper">
                                    Medem a eficácia de campanhas.
                                    @if (filled($configuracoes->meta_pixel_id))
                                        Fornecedor: Meta Pixel. Duração: até 3 meses.
                                    @else
                                        Nenhuma ferramenta de marketing está configurada no momento.
                                    @endif
                                </small>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn--primary" name="escolha" value="salvar">
                        <span class="btn__label">Salvar preferências</span>
                    </button>
                </div>
            </details>

            <button type="submit" class="btn btn--outline" name="escolha" value="recusar"><span class="btn__label">Recusar</span></button>
            <button type="submit" class="btn btn--outline" name="escolha" value="aceitar"><span class="btn__label">Aceitar tudo</span></button>
        </div>

        @if ($escolha)
            {{-- So quem ja decidiu ve isto: fechar sem mexer na escolha
                 antiga. O `#` limpa o `:target` e a faixa volta a sumir. --}}
            <a class="aviso-de-cookies__fechar" href="#">Fechar sem alterar</a>
        @endif
    </form>
</div>

<style>
    /* Canto direito, acima da barra fixa (z 115) e abaixo do menu (z 120).
       A largura fica aqui, no elemento fixo: um `position:fixed` sem largura
       propria estica ate o max-content do texto e o cartao desgruda da borda.
       No celular o `100%` resolve pela largura da tela — o cartao ocupa a
       tela menos as margens e vira uma faixa curta no rodape, em vez de um
       cartao espremido. */
    .aviso-de-cookies { position: fixed; inset: auto 0 0 auto; z-index: 116; width: min(412px, 100%); padding: 16px; }
    /* Reabre pelo link "Preferencias de cookies" do rodape, sem JavaScript. */
    #aviso-de-cookies:target { display: block; }
    /* O `tabindex="-1"` existe para o leitor de tela anunciar a regiao ao
       chegar pelo link do rodape. O Chrome trata esse foco como visivel e
       desenha o anel roxo global em volta da faixa inteira — anel de foco em
       caixa, e nao em controle, so polui. Os botoes de dentro continuam com
       o dele. */
    #aviso-de-cookies:focus, #aviso-de-cookies:focus-visible { outline: none; }

    /* Superficie, borda e raio do `.card` do DS. */
    .aviso-de-cookies__caixa {
        max-height: calc(100svh - var(--nav-h) - 32px); overflow: auto;
        display: grid; gap: 16px;
        padding: 22px;
        border: 1px solid rgb(var(--accent-rgb) / .26);
        border-radius: var(--radius-md);
        background: rgb(var(--surface-rgb));
        box-shadow: 0 26px 70px rgb(var(--shadow-rgb) / .45);
        font-size: var(--body-sm);
    }
    .aviso-de-cookies__texto { line-height: 1.6; color: var(--text-muted); }
    .aviso-de-cookies__texto a { color: var(--accent-text); text-decoration: underline; text-underline-offset: 3px; }

    /* Uma coluna: os tres botoes ficam do mesmo tamanho e do mesmo peso.
       Esconder ou apagar "Recusar" e pratica proibida. */
    .aviso-de-cookies__acoes { display: grid; gap: 10px; }
    .aviso-de-cookies__acoes .btn { min-height: 44px; width: 100%; }

    .aviso-de-cookies summary { list-style: none; }
    .aviso-de-cookies summary::-webkit-details-marker { display: none; }

    .aviso-de-cookies__painel {
        margin-top: 12px; padding: 18px;
        border: 1px solid rgb(var(--accent-rgb) / .26);
        border-radius: var(--radius-md);
    }
    .aviso-de-cookies__titulo { margin: 0 0 14px; font-size: var(--h5); line-height: var(--lh-h5); font-weight: 600; }
    .aviso-de-cookies__painel .btn { margin-top: 16px; }
    .aviso-de-cookies__fechar { justify-self: end; font-size: var(--caption); color: var(--accent-text); text-decoration: underline; text-underline-offset: 3px; }

    /* Toggles do DS (secao UI Components), portados como estao. */
    .switch { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; min-height: 44px; cursor: pointer; user-select: none; }
    .switch input { position: absolute; width: 1px; height: 1px; margin: -1px; clip: rect(0 0 0 0); clip-path: inset(50%); overflow: hidden; white-space: nowrap; }
    .switch .inner { position: relative; order: 2; flex: 0 0 auto; width: 52px; height: 30px; border-radius: 999px; background: rgb(var(--text-rgb) / .16); border: 1px solid rgb(var(--text-rgb) / .18); transition: background .3s, box-shadow .3s; }
    .switch .tick { position: absolute; top: 4px; left: 4px; width: 20px; height: 20px; border-radius: 50%; background: var(--paper); box-shadow: 0 3px 10px rgb(var(--shadow-rgb) / .2); transition: transform .48s var(--ease-bounce), background .3s; }
    .switch input:checked + .inner { background: var(--purple); box-shadow: 0 8px 24px rgb(var(--accent-rgb) / .22); }
    .switch input:checked + .inner .tick { transform: translateX(22px); background: var(--ink); }
    .switch input:focus-visible + .inner { outline: 3px solid rgb(var(--accent-rgb) / .38); outline-offset: 3px; }
    .switch:has(input:disabled) { opacity: .4; cursor: not-allowed; }
    .switch-stack { display: grid; gap: 7px; }
    .choice-helper { display: block; margin-top: 8px; color: var(--text-muted); font-size: var(--caption); line-height: 1.5; }

    @media (prefers-reduced-motion: no-preference) {
        .aviso-de-cookies__caixa { animation: aviso-de-cookies-entra .4s var(--ease-out-expo) both; }
        @keyframes aviso-de-cookies-entra { from { opacity: 0; transform: translateY(16px); } }
    }
    @media (prefers-reduced-motion: reduce) {
        .switch .inner, .switch .tick { transition: none; }
    }
    @media (forced-colors: active) {
        .switch .inner { forced-color-adjust: auto; border: 1px solid ButtonText; }
        .switch input:checked + .inner { background: Highlight; }
        .switch input:focus-visible + .inner { outline: 2px solid Highlight; }
    }

    /* O cartao cobre o rodape da tela; a pagina ganha exatamente a altura
       dele para nenhum conteudo ficar embaixo. */
    html[data-aviso-de-cookies] body { padding-bottom: var(--altura-aviso-de-cookies, 0px); }
</style>

<script>
    (() => {
        const aviso = document.getElementById('aviso-de-cookies');
        const painel = aviso.querySelector('details');
        const botao = painel.querySelector('summary');
        const titulo = painel.querySelector('.aviso-de-cookies__titulo');

        // Altura real da faixa, inclusive quando o texto quebra no celular.
        const medir = () => {
            const visivel = aviso.offsetHeight > 0;
            document.documentElement.style.setProperty(
                '--altura-aviso-de-cookies', visivel ? aviso.offsetHeight + 'px' : '0px');
            document.documentElement.toggleAttribute('data-aviso-de-cookies', visivel);
        };
        new ResizeObserver(medir).observe(aviso);

        painel.addEventListener('toggle', () => {
            botao.setAttribute('aria-expanded', painel.open);
            // Ao abrir, o foco vai ao titulo do painel — e so ai, nunca
            // sozinho no carregamento da pagina.
            if (painel.open) titulo.focus();
        });

        // Esc fecha apenas o painel e devolve o foco ao botao. Nao aceita
        // nem recusa nada em silencio.
        aviso.addEventListener('keydown', (evento) => {
            if (evento.key === 'Escape' && painel.open) {
                painel.open = false;
                botao.focus();
            }
        });
    })();
</script>
