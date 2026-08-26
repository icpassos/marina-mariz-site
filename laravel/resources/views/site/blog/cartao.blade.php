{{-- Card de post. O mesmo na lista do blog e nos relacionados do post. --}}
<li class="rise" data-d="1"><article class="post-card">
  <figure class="post-card__shot">
    @if ($post->imagem)
    <img src="{{ $post->imagem->url() }}" alt="" width="{{ $post->imagem->width }}" height="{{ $post->imagem->height }}" loading="lazy" decoding="async">
    @endif
    {{-- Post sem categoria nao mostra pilula. --}}
    @if ($post->categoria)
    <span class="post-card__cat">{{ $post->categoria->name }}</span>
    @endif
  </figure>
  <div class="post-card__body">
    <p class="post-card__date"><time datetime="{{ $post->published_at->format('Y-m-d') }}">{{ $post->published_at->translatedFormat('j \d\e F \d\e Y') }}</time></p>
    <h3 class="post-card__title"><a href="/blog/{{ $post->slug }}">{{ $post->title }}</a></h3>
    <p class="post-card__desc">{{ $post->description }}</p>
    <span class="post-card__go">Ler o post <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
  </div>
</article></li>
