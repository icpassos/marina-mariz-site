{{-- Saudacao no topo, ao lado do menu. Substitui o cartao de conta que
     ocupava metade da primeira faixa da tela inicial. --}}
@auth
    <span class="fi-topbar-bem-vindo">
        Bem-vinda, <strong>{{ str(auth()->user()->name)->before(' ') }}</strong>
    </span>
@endauth
