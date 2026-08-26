{{-- Descadastro sem login e sem formulario: so o clique (doc 04). --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Descadastro da newsletter</title>
</head>
<body>
    <main>
        @if ($descadastrada)
            <h1>Pronto, você foi descadastrada.</h1>
            <p>Não enviaremos mais a newsletter para este e-mail.</p>
        @else
            <h1>Este link já foi usado.</h1>
            <p>O link de descadastro vale uma vez só. Se ainda receber a newsletter,
               escreva para contato@dramarinamariz.com.br.</p>
        @endif
    </main>
</body>
</html>
