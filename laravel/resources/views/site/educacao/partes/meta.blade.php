{{-- Linha de metadados do card e da ficha. O separador e decorativo
     (`.book__meta .sep` e display:none) e so aparece entre dois valores. --}}
<p class="book__meta">@foreach (array_values(array_filter($meta, 'filled')) as $i => $valor)@if ($i)<span class="sep" aria-hidden="true">·</span>@endif<span>{{ $valor }}</span>@endforeach</p>
