// Converte imagem para WebP antes de sair do computador de quem envia
// (doc 05). O Filament registra o plugin de transformacao do FilePond mas
// nao expoe o tipo de saida, entao ligamos pelas opcoes globais.
//
// O modulo do FilePond e carregado sob demanda pelo Filament: esperamos
// ele aparecer em window, com desistencia depois de 10s para nao deixar
// um timer rodando para sempre numa aba aberta.
(function () {
    const opcoes = {
        imageTransformOutputMimeType: 'image/webp',
        imageTransformOutputQuality: 82,
    }

    if (window.FilePond) {
        window.FilePond.setOptions(opcoes)
        return
    }

    const inicio = Date.now()

    const espera = setInterval(() => {
        if (window.FilePond) {
            clearInterval(espera)
            window.FilePond.setOptions(opcoes)
            return
        }

        if (Date.now() - inicio > 10000) {
            clearInterval(espera)
        }
    }, 100)
})()
