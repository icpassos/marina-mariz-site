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
<link rel="icon" href="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iQ2FtYWRhXzIiIGRhdGEtbmFtZT0iQ2FtYWRhIDIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDQ1LjUgNDUuNDMiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICM5MDdkZTI7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgPC9kZWZzPgogIDxnIGlkPSJDYW1hZGFfMS0yIiBkYXRhLW5hbWU9IkNhbWFkYSAxIj4KICAgIDxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTQ1LjUsMjAuNTR2LTQuODZoLTUuNjlsNC4wMi00LjAyLTMuNDQtMy40NGMtMi40NiwyLjY2LTUuNCw0LjU3LTguMDIsNC45MS4zNC0yLjYyLDIuMjQtNS41Niw0LjkxLTguMDJsLTMuNDQtMy40NC00LDRWMGgtNC44NmMuMTUsMy42Mi0uNTksNy4wNS0yLjIsOS4xNC0xLjYxLTIuMDktMi4zNS01LjUyLTIuMi05LjE0aC00Ljg2djUuNjlMMTEuNjUsMS42NGwtMy40NCwzLjQ0YzIuNjYsMi40Niw0LjU3LDUuNCw0LjkxLDguMDItMi42Mi0uMzQtNS41Ni0yLjI0LTguMDItNC45MWwtMy40NCwzLjQ0LDQsNEgwdjQuODZjMy42Mi0uMTUsNy4wNS41OSw5LjE0LDIuMi0yLjA5LDEuNjEtNS41MiwyLjM1LTkuMTQsMi4ydjQuODZoNS42OWwtNC4wMiw0LjAyLDMuNDQsMy40NGMyLjQ2LTIuNjYsNS40LTQuNTcsOC4wMi00LjkxLS4zNCwyLjYyLTIuMjQsNS41Ni00LjkxLDguMDJsMy40NCwzLjQ0LDQtNHY1LjY3aDQuODZjLS4xNS0zLjYyLjU5LTcuMDUsMi4yLTkuMTQsMS42MSwyLjA5LDIuMzUsNS41MiwyLjIsOS4xNGg0Ljg2di01LjY5bDQuMDYsNC4wNiwzLjQ0LTMuNDRjLTIuNjYtMi40Ni00LjU3LTUuNC00LjkxLTguMDIsMi42Mi4zNCw1LjU2LDIuMjQsOC4wMiw0LjkxbDMuNDQtMy40NC00LTRoNS42N3YtNC44NmMtMy42Mi4xNS03LjA1LS41OS05LjE0LTIuMiwyLjA5LTEuNjEsNS41Mi0yLjM1LDkuMTQtMi4yWk00MC40LDkuOTlsMS42NywxLjY3LTQuMDIsNC4wMmgtNS42NnYuMTFjLS4wOC0uNDYtLjExLS45Mi0uMTEtMS4zOSwzLjE3LS40NSw1LjUyLTEuOTUsOC4xMi00LjQxWk0xMS44OSwxOS42OGwtMy4yMS0yLjgsNC4zNC4zYy0uMy44OS0uNjksMS43My0xLjE0LDIuNTFaTTEyLjkyLDI4LjIzbC00LjIyLjI4LDMuMjQtMi44NGMuNDIuODMuNzQsMS42OC45OCwyLjU3Wk0xNy4yMSwzMi40NmMuODkuMjksMS43MS42OCwyLjQ4LDEuMTJsLTIuNzcsMy4xNy4yOS00LjI5Wk0yMi42MywzNC4wOGMtMS44My0xLjU1LTUuMjMtMy4zNy03Ljk3LTMuMzgtLjIxLTIuNDQtMS4zNC02LjExLTMuMjgtOC4wNywxLjU0LTEuODIsMy4zOS01LjIzLDMuNDEtOC4wMSwyLjQxLS4yLDYuMTItMS4zMiw4LjA4LTMuMjcsMS44MywxLjU0LDUuMjMsMy4zNyw3Ljk3LDMuMzguMjEsMi40NCwxLjM0LDYuMTEsMy4yOCw4LjA3LS41OS43Ny0xLjg1LDIuNTItMi41Nyw0LjE2LS41MiwxLjE5LS43NSwyLjk1LS44NCwzLjg1LTIuNDEuMjEtNi4xMSwxLjMxLTguMDgsMy4yN1pNMjguMywxMi45N2MtLjg5LS4yOS0xLjcxLS42OC0yLjQ4LTEuMTJsMi43Ny0zLjE3LS4yOSw0LjI5Wk0zMy42MSwyNS43NWwzLjIxLDIuOC00LjM0LS4zYy4zLS44OS42OS0xLjczLDEuMTQtMi41MVpNMzIuNTgsMTcuMmw0LjIyLS4yOC0zLjI0LDIuODRjLS40Mi0uODItLjc0LTEuNjktLjk4LTIuNTdaTTI5Ljg0LDcuNDNsNC00LDEuNjcsMS42N2MtMi40NiwyLjYtMy45Niw0Ljk1LTQuNDEsOC4xMi0uNDUsMC0xLjI3LS4xMS0xLjI3LS4xMXYtNS42OFpNMjYuMjMsMS4yNWgyLjM2djUuNjdsLTQuMDIsNC4wMmguMDFjLS4zNS0uMjQtLjY4LS41Mi0uOTgtLjgzLDEuOTItMi41NiwyLjUzLTUuMjgsMi42Mi04Ljg2Wk0xNi45NiwxLjI1aDIuMzZjLjA5LDMuNTguNyw2LjMsMi42Miw4Ljg2LS4zOC4zOC0xLjAyLjgtMS4wMi44bC0zLjk3LTMuOTdWMS4yNVpNMTkuNzksMTEuOTNjLS44NC40Mi0xLjcxLjc0LTIuNTkuOTdsLS4yNS00LjIsMi44NCwzLjIzWk05Ljk5LDUuMDdsMS42Ny0xLjY3LDQuMDYsNC4wNnY1LjYzYy0uNDQuMDctLjg4LjEtMS4zMi4xLS40NS0zLjE3LTEuOTUtNS41Mi00LjQxLTguMTJaTTMuNDMsMTEuNjNsMS42Ny0xLjY3YzIuNiwyLjQ2LDQuOTUsMy45Niw4LjEyLDQuNDEsMCwuNDUtLjExLDEuMjctLjExLDEuMjdoLTUuNjhsLTQtNFpNMS4yNSwxOS4yNHYtMi4zNmg1LjY3bDQuMDIsNC4wMmguMDFjLS4yNi4zNC0uNTQuNjYtLjg0Ljk3LTIuNTYtMS45Mi01LjI4LTIuNTMtOC44Ni0yLjYyWk0xLjI1LDI4LjUxdi0yLjM2YzMuNTgtLjA5LDYuMy0uNyw4Ljg2LTIuNjIuMzEuMzEuODMuOTguODMuOThsLTQsNEgxLjI1Wk01LjEsMzUuNDVsLTEuNjctMS42Nyw0LjAyLTQuMDJoNS42NnYtLjExYy4wOC40Ni4xMS45Mi4xMSwxLjM5LTMuMTcuNDUtNS41MiwxLjk1LTguMTIsNC40MVpNMTUuNjcsMzhsLTQsNC0xLjY3LTEuNjdjMi40Ni0yLjYsMy45Ni00Ljk1LDQuNDEtOC4xMi40NSwwLDEuMjcuMTEsMS4yNy4xMXY1LjY4Wk0xOS4yNyw0NC4xOGgtMi4zNnYtNS42N2w0LjAyLTQuMDJoLS4wMWMuMzUuMjQuNjguNTIuOTguODMtMS45MiwyLjU2LTIuNTMsNS4yOC0yLjYyLDguODZaTTI4LjU1LDQ0LjE4aC0yLjM2Yy0uMDktMy41OC0uNy02LjMtMi42Mi04Ljg2LjM4LS4zOCwxLjAyLS44LDEuMDItLjhsMy45NywzLjk3djUuNjlaTTI1LjcxLDMzLjVjLjg0LS40MiwxLjcxLS43NCwyLjU5LS45N2wuMjUsNC4yLTIuODQtMy4yM1pNMzUuNTIsNDAuMzZsLTEuNjcsMS42Ny00LjA2LTQuMDZ2LTUuNjNjLjQ0LS4wNy44OC0uMSwxLjMyLS4xLjQ1LDMuMTcsMS45NSw1LjUyLDQuNDEsOC4xMlpNNDIuMDcsMzMuODFsLTEuNjcsMS42N2MtMi42LTIuNDYtNC45NS0zLjk2LTguMTItNC40MSwwLS40NS4xMS0xLjI3LjExLTEuMjdoNS42OGw0LDRaTTQ0LjI2LDI2LjE5djIuMzZoLTUuNjdsLTQuMDItNC4wMmgtLjAxYy4yNi0uMzQuNTQtLjY2Ljg0LS45NywyLjU2LDEuOTIsNS4yOCwyLjUzLDguODYsMi42MlpNNDQuMjYsMTkuMjhjLTMuNTguMDktNi4zLjctOC44NiwyLjYyLS4zMS0uMzEtLjgzLS45OC0uODMtLjk4bDQtNGg1LjY5djIuMzZaIi8+CiAgPC9nPgo8L3N2Zz4=">
<link rel="preload" href="/assets/fonts/mozaic-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/alverata-400.woff2" as="font" type="font/woff2" crossorigin>
@yield('preloads')
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/regular/style.css"
        integrity="sha384-6p9AefaqUhEVheRlj1mpAkbngHXy9mbYMrIdcIt4Jlc9lOLIablJq3bBsLOjGwZ7" crossorigin="anonymous">
<link rel="stylesheet" href="/assets/css/site.css">
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

<script src="/assets/js/site.js"></script>
@include('site.partials.aviso-de-cookies')
</body>
</html>
