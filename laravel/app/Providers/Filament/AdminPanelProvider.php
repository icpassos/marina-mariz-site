<?php

namespace App\Providers\Filament;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('paineladm')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('Dra. Marina Mariz')
            // LOGO-P do DS. A view devolve o SVG em linha para que a cor venha
            // de --logo-text/--logo-mark, que o esquema troca sozinho — por isso
            // nao existe variante separada de modo escuro.
            ->brandLogo(fn (): View => view('filament.brand-logo'))
            ->brandLogoHeight('1.75rem')
            // SIMB do DS, o simbolo isolado.
            ->favicon(asset('images/marina-simbolo.svg'))
            // Mozaic GEO, a fonte de leitura e interface do DS. LocalFontProvider
            // sem url so define --font-family; o @font-face vive no tema, servido
            // pelo Vite a partir de resources/fonts. Nenhuma CDN.
            ->font('Mozaic GEO', provider: LocalFontProvider::class)
            // Menu no topo, como o do site: cada grupo de navegacao vira um
            // painel que abre no clique. A tela de edicao continua em duas
            // colunas, o wireframe do WordPress — o que muda e so onde o
            // menu mora. Abaixo de `lg` o Filament recolhe o topo numa
            // gaveta lateral; e por isso que as regras de menu lateral
            // continuam no tema.
            ->topNavigation()
            // O conteudo usa a largura inteira, na lista e na edicao.
            ->maxContentWidth(Width::Full)
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            // 2FA TOTP obrigatorio: o usuario sem app configurado e levado
            // para a tela de configuracao antes de alcancar qualquer pagina.
            ->multiFactorAuthentication(
                AppAuthentication::make()
                    ->recoverable()
                    ->recoveryCodeCount(10),
                isRequired: true,
            )
            // Falha quando um Resource nao tem Policy, em vez de liberar acesso.
            ->strictAuthorization()
            ->databaseNotifications()
            // Rampas do DS. Precisam existir aqui, e nao so no CSS, porque o
            // Filament 5 escolhe em PHP qual degrau usar de fundo e de texto em
            // botao, badge e link medindo o contraste da propria rampa — se o
            // PHP e o CSS discordarem, a escolha sai errada.
            //
            // Passar so '#5648A8' nao servia: o Filament pega apenas o matiz e
            // regenera a rampa com claridade fixa, e o 600 resultante ficava em
            // torno do Violeta 3, que reprova como texto em fundo claro (~2.9:1).
            //
            // Os degraus marcados sao cor do DS. Os demais sao interpolacao em
            // OKLab entre eles e o Preto/Branco do DS — derivados, nao inventados.
            ->colors([
                'primary' => [
                    50 => '#F4F2FF', 100 => '#E7E2FF', 200 => '#D8D0FF',
                    300 => '#CCC0FF', // Violeta Intuitivo 4
                    400 => '#B8A3F5', // Violeta Intuitivo — acento de texto em fundo escuro
                    500 => '#907DE2', // Violeta Intuitivo 3 — preenchimento
                    600 => '#5648A8', // Violeta 2 — acento de texto em fundo claro
                    700 => '#443C88', 800 => '#35326C', 900 => '#292957', 950 => '#1A1F3D',
                ],
                // Rampa neutra do Sereno (.scheme-05). O modo escuro troca esta
                // rampa por Oceano no proprio tema, em :root.dark.
                'gray' => [
                    50 => '#F3F1E8',  // Serafim Neutro — --surface
                    100 => '#EFE9D3', // Serafim 3 — --surface-2
                    200 => '#CCC5A8', // Serafim 2 — --surface-3
                    300 => '#999999', // Cinza 3
                    400 => '#565654', // Cinza 4 — --text-muted
                    500 => '#565654', // Cinza 4 — o DS tem um tom atenuado por esquema
                    600 => '#3E4C63', // Azul Norturno 4
                    700 => '#273449', // Azul Norturno 3
                    800 => '#1B2B3F', // Azul Norturno 2
                    900 => '#1C222E', // Azul Norturno
                    950 => '#0b1423', // Preto — --text
                ],
                'danger' => [
                    50 => '#FEF2F2', 100 => '#FDE5E6', 200 => '#FBD6D8', 300 => '#FACCCE',
                    400 => '#F9C6C9', // --danger do Oceano
                    500 => '#AA6A6C',
                    600 => '#6B2229', // --danger do Sereno
                    700 => '#532129', 800 => '#401F28', 900 => '#321D27', 950 => '#1F1925',
                ],
                'success' => [
                    50 => '#ECF8F2', 100 => '#D8F1E5', 200 => '#C1E9D5', 300 => '#B1E3CC',
                    400 => '#A8E0C6', // --ok do Oceano
                    500 => '#5A8A77',
                    600 => '#1F4A3C', // --ok do Sereno
                    700 => '#1A3C36', 800 => '#163131', 900 => '#13292D', 950 => '#0F1E28',
                ],
                'warning' => [
                    50 => '#FBF2E8', 100 => '#F8E5D1', 200 => '#F4D7B6', 300 => '#F2CDA4',
                    400 => '#F0C79A', // --warn do Oceano
                    500 => '#9B764C',
                    600 => '#5A3A0F', // --warn do Sereno
                    700 => '#45311B', 800 => '#352A1F', 900 => '#2A2421', 950 => '#1A1C23',
                ],
                // O DS nao tem 'info'. O unico badge informativo dele e o
                // .badge--purple, entao 'info' herda o acento ate o DS decidir.
                // No painel 'info' so aparece ao lado de gray, success e warning,
                // nunca ao lado de primary, entao continua distinguivel.
                'info' => [
                    50 => '#F4F2FF', 100 => '#E7E2FF', 200 => '#D8D0FF', 300 => '#CCC0FF',
                    400 => '#B8A3F5', 500 => '#907DE2', 600 => '#5648A8',
                    700 => '#443C88', 800 => '#35326C', 900 => '#292957', 950 => '#1A1F3D',
                ],
            ])
            // Ordem dos grupos do menu, conforme o doc 00.
            ->navigationGroups(['Conteúdo', 'Pessoas', 'Sistema'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            // A tela inicial vem por discoverPages (App\Filament\Pages\Dashboard),
            // que estende a do Filament so para trazer a linha de explicacao.
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // A saudacao sai da grade da tela inicial e vai para o topo, ao
            // lado do menu, liberando a largura inteira para os atalhos.
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): View => view('filament.bem-vindo'),
            )
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
