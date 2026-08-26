<x-filament::section class="cartao-largura-total">
    <x-slot name="heading">Atalhos</x-slot>
    <x-slot name="description">O mapa do painel: só aparece aqui o que o seu acesso permite abrir.</x-slot>

    {{-- Grade em vez de fila: com nove entradas isto e um indice, nao tres botoes. --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($this::atalhos() as $atalho)
            <x-filament::button
                tag="a"
                color="gray"
                :href="$atalho['url']"
                :icon="$atalho['icone']"
                class="justify-start"
            >
                {{ $atalho['rotulo'] }}
            </x-filament::button>
        @endforeach
    </div>
</x-filament::section>
