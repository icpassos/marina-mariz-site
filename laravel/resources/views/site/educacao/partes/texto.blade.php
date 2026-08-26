{{-- Descricao completa do painel, quebrada em paragrafos. E o mesmo campo
     que o card mostra em duas linhas (escopos das paginas). --}}
@foreach (preg_split('/\R{2,}/', trim((string) $item->description)) ?: [] as $paragrafo)
        <p>{{ $paragrafo }}</p>
@endforeach
