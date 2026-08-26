{{-- Rodape (vault: Rodape.md). Contato, endereco, redes e a linha do CFM
     vem do painel pelo `Site`; o resto e navegacao fixa em codigo. --}}
@php
    $dados = \App\Services\Site::dados();
    // Ordem e conjunto que o rodape mostra. `comunidade` fica de fora: ela
    // aparece como link de navegacao na coluna Conteudo, nao como rede.
    $visiveis = \App\Services\Site::redesVisiveis();
    $redes = collect(['youtube', 'instagram', 'linkedin', 'spotify', 'whatsapp'])
        ->mapWithKeys(fn (string $chave): array => [$chave => $visiveis[$chave] ?? null])
        ->filter();
    // O endereco nao quebra entre cidade e estado.
    $endereco = collect(\App\Services\Site::enderecoLinhas())
        ->map(fn (string $linha): string => str_replace(' - ', '&nbsp;-&nbsp;', e($linha)))
        ->implode('<br>');
@endphp
<footer class="{{ $classeRodape ?? 'footer scheme-04' }}" data-scene data-tone="dark">
  <div class="shell">
    <div class="footer__cols">
      <div class="footer__brand rise">
        <svg viewBox="0 0 201.47 32.24" role="img" aria-label="Marina Mariz"><use href="#logo-p"></use></svg>
        <div class="footer__social" style="margin-top:20px">@foreach ($redes as $chave => $rede)<a href="{{ $rede['url'] }}" target="_blank" rel="noopener" aria-label="{{ $rede['rotulo'] }}"><i class="ph ph-{{ $chave }}-logo" aria-hidden="true"></i></a>@endforeach</div>
        <address class="footer__addr">{{ str($endereco)->toHtmlString() }}</address>
      </div>
      <div class="footer__col rise" data-d="1"><h3>Navegação</h3><ul><li><a href="/">Início</a></li><li><a href="/sobre">Sobre</a></li></ul></div>
      <div class="footer__col rise" data-d="2"><h3>Serviços</h3><ul><li><a href="/especialidades">Especialidades</a></li><li><a href="/amara">Amara</a></li></ul></div>
      <div class="footer__col rise" data-d="3"><h3>Conteúdo</h3><ul>
        <li><a href="https://comunidade.dramarinamariz.com.br" target="_blank" rel="noopener">Comunidade</a></li>
        <li><a href="/educacao">Educação</a></li><li><a href="/podcast">Podcast</a></li><li><a href="/blog">Blog</a></li><li><a href="/newsletter">Newsletter</a></li></ul></div>
      <div class="footer__col rise" data-d="4"><h3>Contato</h3><ul>
        <li><a href="/contato">Agendar Consulta</a></li>
        <li><a href="/contato">Fale Conosco</a></li>
        <li><a href="mailto:{{ $dados->email }}">{{ $dados->email }}</a></li>
        <li><a href="{{ \App\Services\Site::linkTel() }}">{{ \App\Services\Site::telefoneFormatado() }}</a></li>
        <li><a href="{{ \App\Services\Site::linkWhatsapp() }}" target="_blank" rel="noopener">{{ \App\Services\Site::whatsappFormatado() }}</a></li></ul>
      </div>
      <div class="footer__col rise" data-d="5"><h3>Redes sociais</h3><ul>
@foreach ($redes as $rede)
        <li><a href="{{ $rede['url'] }}" target="_blank" rel="noopener">{{ $rede['rotulo'] }}</a></li>
@endforeach
      </ul></div>
    </div>

    <div class="footer__mid">
      <span class="footer__wordmark">Marina Mariz</span>
      <div class="footer__legal">
        <a href="/politica-de-privacidade">Política de Privacidade</a>
        <a href="/termos-de-uso">Termos de Uso</a>
        <a href="#aviso-de-cookies">Preferências de cookies</a>
      </div>
      <span class="footer__copy">© <span data-year>2026</span> Dra. Marina Mariz. Todos os direitos reservados.</span>
    </div>

    {{-- Registro visivel e exigencia da Resolucao CFM 2.336/2023. --}}
    <p class="footer__crm"><b>Dra. {{ $dados->nome_divulgacao }} — {{ $dados->crm }}</b>@foreach (\App\Services\Site::especialidadesComRqe() as $especialidade) · {{ $especialidade }}@endforeach</p>
  </div>
</footer>
