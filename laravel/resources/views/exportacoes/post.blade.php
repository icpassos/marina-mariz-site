{{-- Template próprio da exportação: simples de propósito, não reproduz o
     layout do site (doc 02). Nada de status, SEO ou ID entra aqui. --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $post->title }}</title>
    <style>
        @page { margin: 2.5cm 2cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.5; color: #1a1a1a; }
        h1 { font-size: 20pt; margin: 0 0 .4em; }
        h2 { font-size: 14pt; margin: 1.4em 0 .4em; }
        h3 { font-size: 12pt; margin: 1.2em 0 .3em; }
        .meta { font-size: 9pt; color: #555; margin-bottom: 1.2em; }
        .descricao { font-weight: bold; margin-bottom: 1.4em; }
        img { max-width: 100%; }
        blockquote { margin: 1em 0; padding-left: 1em; border-left: 3px solid #ccc; color: #444; }
        table { width: 100%; border-collapse: collapse; margin: 1em 0; }
        th, td { border: 1px solid #bbb; padding: .4em .6em; text-align: left; }
        a { color: #5648A8; }
        .aviso { margin-top: 2em; padding-top: .8em; border-top: 1px solid #ddd; font-size: 9pt; color: #555; }
    </style>
</head>
<body>
    <h1>{{ $post->title }}</h1>

    <p class="meta">
        {{ implode(' · ', array_filter([
            $post->nomeDaCategoria(),
            $post->author,
            $post->published_at?->format('d/m/Y'),
        ])) }}
    </p>

    @if (filled($post->description))
        <p class="descricao">{{ $post->description }}</p>
    @endif

    @if ($imagem)
        <p><img src="{{ $imagem }}" alt="{{ $post->imagem->alt }}"></p>
    @endif

    {{-- Já sanitizado pelo RichContentRenderer; nunca conteúdo cru. --}}
    {!! $conteudo !!}

    <p class="aviso">{{ $aviso }}</p>
</body>
</html>
