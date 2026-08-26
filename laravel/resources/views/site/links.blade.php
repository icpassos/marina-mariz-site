@extends('layouts.site', [
    'titulo' => 'Links — Dra. Marina Mariz',
    'descricao' => 'Todos os canais da Dra. Marina Mariz em um lugar só: consulta, conteúdo, podcast e comunidade.',
    'canonical' => 'https://dramarinamariz.com.br/links',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/marina-retrato.webp',
    'comMenu' => false,
    'comRodape' => false,
    // A marca vem do sprite, como no menu e no rodape do site.
    'comSprite' => true,
])

{{-- Fora do Google de proposito: quem chega vem do perfil no Instagram, e
     esta pagina nao deve competir com o site na busca. `follow` continua
     valendo, para o valor dos links passar adiante. --}}
@section('robots')
<meta name="robots" content="noindex,follow">
@endsection

@php
    // Mesmo conjunto e mesma ordem do rodape do site: `comunidade` nao entra
    // como rede — se a Marina quiser, ela vira um botao da lista.
    $visiveis = \App\Services\Site::redesVisiveis();
    $redes = collect(['instagram', 'youtube', 'spotify', 'linkedin', 'whatsapp'])
        ->mapWithKeys(fn (string $chave): array => [$chave => $visiveis[$chave] ?? null])
        ->filter();

    $papel = collect($dados->especialidades ?? [])
        ->pluck('especialidade')
        ->filter()
        ->implode(' · ');

    // Linha do CFM no pé, em duas linhas: nome, CRM e a primeira
    // especialidade em cima; os RQE embaixo. Travessão vira hífen simples.
    // Escapado peça por peça porque o `<br>` entra depois, cru.
    $registro = collect(\App\Services\Site::especialidadesComRqe())
        ->map(fn (string $item): string => e(str_replace(' — ', ' - ', $item)))
        ->implode(' · ');
    $registro = \Illuminate\Support\Str::replaceFirst(' - ', '<br>', $registro);
@endphp

@section('conteudo')
<!-- ===== /links — agregador de "link na bio" (doc 09) ===== -->
<section class="scene lnk scheme-04" data-scene data-tone="dark" aria-labelledby="lnk-nome">
  <div class="lnk__grain" aria-hidden="true"></div>
  <div class="lnk__coluna">
    {{-- O halo acompanha a coluna, que se move conforme a altura da tela:
         preso à cena, a luz sobraria no vazio de cima. --}}
    <div class="lnk__halo" aria-hidden="true"></div>

    <header class="lnk__topo">
      <h1 class="lnk__marca rise" id="lnk-nome">
        {{-- A mesma assinatura do menu e do rodapé. A variante com
             "Ginecologia e Obstetrícia" embaixo repetiria o anel do selo
             e a linha de especialidades logo abaixo. --}}
        <svg viewBox="0 0 201.47 32.24" role="img" aria-label="Dra. {{ $dados->nome_divulgacao }}"><use href="#logo-p"></use></svg>
      </h1>
      <p class="lnk__papel rise" data-d="1">{{ $papel }}</p>

@if ($redes->isNotEmpty())
      <nav class="lnk__redes rise" data-d="2" aria-label="Redes sociais">
@foreach ($redes as $chave => $rede)
        <a href="{{ $rede['url'] }}" target="_blank" rel="noopener" aria-label="{{ $rede['rotulo'] }}"><i class="ph ph-{{ $chave }}-logo" aria-hidden="true"></i></a>
@endforeach
      </nav>
@endif
    </header>

@if ($links->isNotEmpty())
    <ul class="lnk__lista">
@foreach ($links as $i => $link)
      <li class="rise" data-d="{{ min($i + 2, 4) }}">
        <a class="lnk__item" data-glow href="{{ $link->url }}"@if ($link->ehExterno()) target="_blank" rel="noopener"@endif>
@if ($link->imagem)
          <img class="lnk__mini" src="{{ $link->imagem->url() }}" alt="" width="44" height="44" loading="lazy">
@endif
          <span class="lnk__texto">
            <span class="lnk__titulo">{{ $link->title }}</span>
@if ($link->description)
            <span class="lnk__apoio">{{ $link->description }}</span>
@endif
          </span>
          <i class="ph ph-arrow-up-right lnk__seta" aria-hidden="true"></i>
        </a>
      </li>
@endforeach
    </ul>
@else
    <p class="lnk__vazio rise" data-d="2">Em breve.</p>
@endif

    {{-- Registro visivel e exigencia da Resolucao CFM 2.336/2023: vale
         aqui tambem, mesmo sem o rodape do site. --}}
    <footer class="lnk__pe">
      <p class="lnk__crm"><b>Dra. {{ $dados->nome_divulgacao }} - {{ $dados->crm }}</b> · {!! $registro !!}</p>
      <p class="lnk__legal">
        <a href="/">Site</a>
        <a href="/politica-de-privacidade">Política de Privacidade</a>
        <a href="/termos-de-uso">Termos de Uso</a>
        <a href="#aviso-de-cookies">Cookies</a>
      </p>
    </footer>

  </div>
</section>
@endsection
