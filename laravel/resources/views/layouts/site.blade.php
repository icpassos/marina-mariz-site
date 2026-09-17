{{--
    Layout do site publico. Tudo que e comum as paginas mora aqui: head,
    menu e rodape. A pagina entrega titulo, descricao, canonical e imagem
    social como variaveis, e o resto por @section.

    Extensoes: preloads · robots · jsonld · conteudo · pos-conteudo · pos-rodape.
    Variaveis: titulo · descricao · canonical · ogTipo · ogImagem · atual
               classeNav · classeDrawer · classeRodape · navEstatico
               comMenu · comRodape (padrao true; /links desliga os dois)
               comSprite (padrao: so com menu ou rodape; /links pede de volta).

    Os caminhos de assets usam barra inicial porque o mesmo layout serve a
    pagina 404, que pode responder em qualquer profundidade de URL.
--}}
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $titulo }}</title>
<meta name="description" content="{{ $descricao }}">
<link rel="icon" type="image/svg+xml" href="/images/marina-simbolo.svg?v=2">
<link rel="preload" href="/assets/fonts/mozaic-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/alverata-400.woff2" as="font" type="font/woff2" crossorigin>
@yield('preloads')
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/regular/style.css"
        integrity="sha384-6p9AefaqUhEVheRlj1mpAkbngHXy9mbYMrIdcIt4Jlc9lOLIablJq3bBsLOjGwZ7" crossorigin="anonymous">
<link rel="stylesheet" href="/assets/css/site.css?v=6">
<link rel="manifest" href="/site.webmanifest">
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:type" content="{{ $ogTipo ?? 'website' }}">
@yield('robots')
<meta property="og:site_name" content="Dra. Marina Mariz">
<meta property="og:locale" content="pt_BR">
<meta property="og:title" content="{{ $titulo }}">
<meta property="og:description" content="{{ $descricao }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImagem }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $titulo }}">
<meta name="twitter:description" content="{{ $descricao }}">
<meta name="twitter:image" content="{{ $ogImagem }}">
@yield('jsonld')
{{-- Sem escolha registrada no Aviso de Cookies, nada de rastreamento entra. --}}
@php $analyticsPermitido ??= \App\Support\AvisoDeCookies::escolha(request())['analiticos'] ?? false; @endphp
@include('site.analytics', ['analyticsPermitido' => $analyticsPermitido])
</head>
<body>
{{-- O sprite guarda a marca e os simbolos das cenas. Pagina que entra sem
     menu e sem rodape geralmente nao usa nenhum deles — e sao 52 KB —, mas
     pode pedir o sprite de volta com `comSprite`. --}}
@if ($comSprite ?? (($comMenu ?? true) || ($comRodape ?? true)))
@include('site.partials.sprite')
@endif
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

{{-- /links e uma pagina avulsa: entra sem menu e sem rodape. --}}
@if ($comMenu ?? true)
@include('site.partials.menu')
@endif

<main id="conteudo">
@yield('conteudo')
</main>

{{-- Chamada final da pagina: cada uma escreve a sua. --}}
@yield('pos-conteudo')

@if ($comRodape ?? true)
@include('site.partials.rodape')
@endif

{{-- Barra fixa de agendamento ou barra de leitura, conforme a pagina. --}}
@yield('pos-rodape')

<script src="/assets/js/site.js?v=3"></script>
@include('site.partials.aviso-de-cookies')
</body>
</html>
