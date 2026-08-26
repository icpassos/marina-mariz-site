# Site Institucional — Dra. Marina Mariz

Projeto do site institucional de **dramarinamariz.com.br** (ginecologia e obstetrícia).
Este arquivo é carregado automaticamente em qualquer sessão nova aberta nesta pasta — leia antes de mexer em qualquer coisa.

---

## 0. Regra principal — leia antes de escrever qualquer linha

**Toda página deste site nasce do `DS_Marina.html`.** Qualquer página pedida — nova ou refeita — precisa seguir o aspecto visual e as definições daquele arquivo: esquemas de cor, escala tipográfica, tokens semânticos, componentes, variantes de logotipo, efeitos de imagem e regras de acessibilidade. Não inventar cor, tamanho de texto, componente ou animação que não esteja lá.

Antes de construir uma página, o caminho é sempre este:

1. **Ler o escopo da página** em `Escopo das Páginas/<Nome>.md` no vault do Obsidian — é de lá que vêm os textos, a ordem das seções e as chamadas.
2. **Ler o `DS_Marina.html`** para pegar o componente e os tokens que a página precisa. Reaproveitar; só criar componente novo se o escopo pedir algo que não existe — e, nesse caso, construí-lo com os tokens do DS e documentá-lo no próprio DS.
3. **Conferir a lógica** no `Mapa do Site.canvas` e nos documentos do painel: o que é fixo em código, o que é dinâmico, para onde cada ação leva.

Se o escopo e o DS discordarem, perguntar — não escolher sozinho.

**O vault Obsidian é a fonte da verdade.** Se este `CLAUDE.md` discordar do vault em qualquer ponto, o vault vence — e este arquivo é corrigido para bater com ele.

**Onde está cada coisa:**

| O que | Onde |
|---|---|
| Aspecto visual, tokens, componentes, motion, acessibilidade | `DS_Marina.html` (nesta pasta) |
| Textos e escopo de cada página, seção a seção | vault Obsidian → `Escopo das Páginas/` |
| Mapa do site, lógica, ações, fluxos, temas transversais | vault Obsidian → `Mapa do Site.canvas` |
| Painel administrativo, permissões, deploy | vault Obsidian → `Painel Administrativo/` |
| Stack fechada | vault Obsidian → `Painel Administrativo/08 — Deploy e Segurança.md` (resumo na seção 1 abaixo) |
| Navegação e rodapé | vault Obsidian → `Menu.md`, `Rodapé.md` |
| Acervo de fotos, numerado | `Acervo_Marina.html` (nesta pasta) |

---

## 1. Onde vive a especificação

**Toda a spec, escopo e regras do site estão fora deste repositório**, num vault Obsidian no Google Drive:

```
/Users/isabelapassos/Library/CloudStorage/GoogleDrive-isabelapassos@outlook.com/Meu Drive/Isabela Passos/Site Dra. Marina Mariz/
```

A raiz do vault é a pasta-pai `Isabela Passos/` — é lá que fica o `.obsidian/`. Por isso, referências dentro de arquivos `.canvas` precisam do prefixo `Site Dra. Marina Mariz/`.

### O que tem lá

| Caminho | Conteúdo |
|---|---|
| `Mapa do Site.canvas` | Spec visual: páginas do site à esquerda, módulos do painel no meio, temas transversais à direita |
| `Escopo das Páginas/` | 17 documentos, um por página — conteúdo seção a seção |
| `Painel Administrativo/` | 9 documentos dos módulos do painel |
| `Menu.md` | Estrutura de navegação e links |
| `Rodapé.md` | Conteúdo do rodapé |
| `Aviso de Cookies.md` | Texto e comportamento do banner |

### Páginas previstas

`Início` · `Sobre` · `Especialidades` · `Amara` · `Podcast Sem Neura` · `Blog` · `Contato` · `Página 404` · `Política de Privacidade` · `Termos de Uso` · `Newsletter`

`Newsletter` é página de apoio: o único link para ela é o rodapé, na coluna Conteúdo. Assinar continua acontecendo pelos blocos de inscrição que fecham o Blog e as páginas de Educação.

Em **Educação** (dropdown no menu): `Livros` · `E-books` · `Cursos` · `Eventos` · `Materiais Gratuitos` · `Formação Profissional`

`Comunidade` é link externo para `comunidade.dramarinamariz.com.br` — outro projeto, fica em `../Landing Page - Comunidade/`.

### Decisões já travadas na spec

- A maioria das páginas é fixa em código, não editável pelo painel. Só **Blog**, o **catálogo de Educação** e a **caixa de leads/mensagens** são dinâmicos.
- Painel em `/paineladm`. Login por **e-mail + senha + 2FA TOTP** obrigatório, usando a autenticação padrão do Filament. Não existe fluxo próprio de primeiro acesso, username ou bootstrap: o primeiro Administrador é criado com `make:filament-user` e tem o papel marcado no banco uma única vez.

### Stack fechada

Definida em `Painel Administrativo/08 — Deploy e Segurança.md`. Não trocar nada disso sem pedido explícito.

| Camada | Escolha |
|---|---|
| Linguagem | PHP 8.4 |
| Framework | Laravel 12 |
| Painel | Filament 5 + Livewire 4 |
| Site público | Blade, renderizado no servidor |
| Front-end público | HTML + CSS + JavaScript nativo |
| CSS / build | Tailwind CSS 4.1 + Vite |
| Banco | MariaDB 11.8 (Hostinger) + Eloquent, driver `mariadb` |
| E-mail | Laravel Mail + SMTP Hostinger |
| Autenticação | Filament: e-mail, senha, reset e TOTP |
| Sessão, cache e fila | drivers `database` |
| Agendamento | Laravel Scheduler + cron do hPanel |
| Testes | PHPUnit + testes Livewire/Filament |

**Fora da stack:** React, Next.js, Redis, Docker, Horizon, Supervisor, navegador headless, WebSocket próprio ou worker permanente. Versões travadas em `composer.lock` e `package-lock.json`.

O DS foi escrito em CSS puro com custom properties. Ao portar para Tailwind, os tokens do `:root` e as classes `.scheme-XX` continuam sendo a fonte da verdade — mapear o tema do Tailwind para eles, não duplicar valores.

---

## 2. O que já existe nesta pasta

### `DS_Marina.html` — Design System (DS/01.1)

Arquivo único, autocontido, ~1 MB. Fontes e SVGs embutidos em base64; nenhuma dependência externa além do Phosphor Icons via CDN. Abrir direto no navegador.

Foi clonado de `../design_system.html` (sistema da marca anterior, "Comunidade Sem Neura") e adaptado à identidade da Marina. Nenhum vestígio da marca antiga deve permanecer.

**Seções, na ordem:** Logotipo · Tipografia · Sistema cromático · Esquemas de página · UI Components · Iconografia · Motion.

### `Acervo_Marina.html` — catálogo de fotos

Página gerada, aberta direto no navegador. Mostra todas as fotos do acervo em miniatura, cada uma com **um número que não muda**, a descrição da cena, as dimensões da origem e em que páginas do site ela já aparece.

**Esse número é o contrato de conversa.** A Isabela cita a foto por ele ("troca a 09 pela 14"), e é assim que o pedido chega sem ambiguidade. Já houve um erro por isso: a numeração do Finder não bate com o `N` do nome do arquivo.

| Arquivo | Papel |
|---|---|
| `Acervo_Marina.html` | a página — **gerada, nunca editar à mão** |
| `acervo/indice.json` | a fonte: número, arquivo de origem, descrição e destinos no site |
| `acervo/gerar.py` | reescreve a página a partir do índice |
| `acervo/thumbs/` | miniaturas, recortadas sob demanda |

Ao **importar uma foto nova** para `site/assets/img/`: acrescentar a entrada no fim de `fotos` usando `proximo_numero`, incrementar `proximo_numero` e rodar `python3 acervo/gerar.py`. Nunca renumerar o que já existe — isso faria toda conversa anterior apontar para a imagem errada. O campo `arquivos` lista os nomes dentro de `site/assets/img/`; em que página cada um aparece o gerador descobre sozinho.

---

## 3. Convenções — use estes nomes ao pedir mudanças

### Logotipo

Onze variantes, embutidas como sprite SVG (`<symbol>` + `<use>`). Os códigos batem 1:1 com os arquivos de origem em `~/Downloads/Assets Marina Site Institucional /Logotipo/`.

| Código | Nome | Composição |
|---|---|---|
| **LOGO-P** | Principal | símbolo + nome, linha única — primeira escolha sempre |
| **LOGO-1** | Bloco com especialidade | símbolo + nome em duas linhas + especialidade ao lado |
| **LOGO-2** | Linha com especialidade | símbolo + nome + especialidade abaixo |
| **LOGO-3** | Estendido | símbolo + nome + especialidade ao lado |
| **LOGO-4** | Nome com especialidade | sem símbolo |
| **LOGO-6** | Nome bloco | sem símbolo |
| **LOGO-7** | Nome empilhado | sem símbolo, sem especialidade |
| **LOGO-8** | Nome estendido | sem símbolo |
| **LOGO-9** | Assinatura | nome em linha única |
| **SIMB** | Símbolo | isolado, favicon, avatar |
| **BADGE** | Selo | especialidade em círculo |

Não existe LOGO-5 — a numeração segue os arquivos originais, que pulam o 5.

**Regra de cor**, aplicada automaticamente pelo esquema via `--logo-text` e `--logo-mark`:
- Fundo claro → símbolo Violeta Intuitivo 3 `#907DE2`, nome e especialidade Preto `#0b1423`
- Fundo escuro → símbolo Violeta Intuitivo 4 `#CCC0FF`, nome e especialidade Branco `#FEFEFE`
- Monocromático é permitido, mas as versões de símbolo destacado são as principais

### Esquemas de cor

Cada combinação da identidade virou uma classe que redefine superfície, texto, acento e feedback de uma vez. A numeração é a das combinações da marca.

| Classe | Nome | Estado |
|---|---|---|
| `.scheme-04` | **Oceano** — Azul Norturno 2 `#1B2B3F` | **em uso** — base do site |
| `.scheme-05` | **Sereno** — Serafim Neutro `#F3F1E8` | **em uso** — alterna com Oceano |
| `.scheme-01` | Ametista — Violeta 2 `#5648A8` | reserva |
| `.scheme-02` | Lilás — Violeta Intuitivo `#B8A3F5` | reserva |
| `.scheme-03` | Noite — Preto `#0b1423` | reserva |

O padrão do site está no `:root` (Oceano). As seções alternam Oceano e Sereno.

### Exceção — Sem Neura Podcast

O podcast tem identidade própria, **fora dos esquemas do DS**. Vale **somente** na seção `#podcast` da home e na página `/podcast`; nenhuma outra parte do site usa essas cores.

| Papel | Cor |
|---|---|
| Fundo | `#dce048` |
| Texto | `#0c0c0c` |
| Botão — base e borda | `#515106` |
| Botão — variação clara / ativo | `#bcbc1d` |
| Botão — texto sobre fundo escuro | `#ffff9f` |

No CSS isso é feito redefinindo os tokens semânticos dentro do seletor `.podcast` — não espalhar valores literais pelo arquivo. Logotipo em `assets/logo/podcast-sem-neura.svg`.

### Efeitos de imagem

| Nome | Classe | Uso |
|---|---|---|
| **FX-TILT** | `.fx-tilt` | cards e imagens: repouso −4°, hover endireita e cresce 2,5% |
| **FX-FLOAT** | `.fx-float` | SIMB flutuando sobre imagem, 8s |
| **FX-SPIN** | `.fx-spin` | BADGE girando sobre imagem, 18s linear |

`.fx-overlay` posiciona qualquer um sobre a foto. Todos respeitam `prefers-reduced-motion`.

---

## 4. Regras do sistema

### Tipografia

- **Mozaic GEO** (400/500/600/700) — leitura e interface
- **Alverata Informal** (400/700) — versão alternativa dos títulos, mesma escala
- Escala em tokens: `--h1` a `--h6`, `--body-lg`, `--body`, `--body-sm`, `--caption`, `--eyebrow`. Nenhum tamanho de texto é literal no CSS — tudo aponta para esses tokens.
- Não existe tier "display". Foi removido de propósito: não faz sentido para a Alverata.

### Cor

Camada semântica: `--surface-rgb`, `--surface-2`, `--surface-3-rgb`, `--text-rgb`, `--text-muted`, `--accent-rgb`, `--accent-text`, `--on-accent`, `--shadow-rgb`, `--veil-rgb`, `--ok`, `--warn`, `--danger`. Aliases antigos (`--paper`, `--ink`, `--purple`, `--cream`, `--sand`, `--warm`, `--muted`) apontam para eles.

**Importante:** os aliases precisam ser redeclarados dentro de cada `.scheme-XX`. Um `var()` declarado só no `:root` resolve uma vez e congela — não reage à troca de esquema.

Restrições medidas, não estimadas:
- Violeta 3 `#907DE2` **reprova** como texto sobre fundo claro (~2.9:1) — em fundo claro o acento de texto é Violeta 2 `#5648A8` (6.43:1). Violeta 3 fica para preenchimento.
- Sobre fundo escuro o acento de texto é Violeta `#B8A3F5`.
- Sobre Lilás `#B8A3F5` o texto é sempre Preto; Violeta 2 ali dá 3.32:1, só serve para título grande e borda.
- Feedback tem par claro/escuro por esquema para manter 4.5:1. Estado nunca é comunicado só por cor.

### Presença do violeta

O violeta atravessa os dois esquemas e marca: eyebrow e rótulo, metadado em mono, moldura de card, ação principal e anel de foco. Superfície e texto ficam neutros.

### Menu

Fixo em todas as páginas, sempre visível — nunca some ao rolar. Dois modos:

- **Padrão** (todas as páginas): transparente no topo; passando de 60px de scroll vira uma pill flutuante escura com blur, borda violeta e sombra, encolhendo a largura.
- **Estático** (só a Home): fica no estado inicial o tempo todo. Ativado por `data-nav-mode="static"` no `<header class="nav">`.

### Ponto de luz

O halo roxo de fundo só existe em fundo escuro. Fundo claro não recebe por padrão — pedir explicitamente quando quiser.

---

## 5. Armadilhas conhecidas

**Nunca usar `re.sub` em massa sobre o CSS sem ancorar o início do seletor.** Isso já quebrou a página duas vezes:

1. Uma função de substituição devolveu o corpo da regra sem o seletor — 24 regras viraram blocos órfãos `{…}` e o layout inteiro caiu.
2. Um padrão iniciado em `.image-carousel__meta` casou o meio de `.image-carousel--auto .image-carousel__meta::before`, deixou `.image-carousel--auto ` pendurado, e esse fragmento grudou na regra seguinte — o `.modal-backdrop` perdeu o `position:fixed` e o modal apareceu fixo no fim da página.

Ao remover regra, ancore em `(?:^|[};])\s*` antes do nome do seletor. E **verifique com o arquivo renderizado de verdade**, com JavaScript ativo — contagem de tags balanceadas não detecta aninhamento errado, e desligar o JS esconde conteúdo e mascara a quebra.

Checagem rápida de sanidade após qualquer edição de CSS:

```python
css.count('{') == css.count('}')
len(re.findall(r'\}\s*\{', css)) == 0          # blocos órfãos
len(re.findall(r'(?:^|[};])\s*\{', css)) == 0  # regra sem seletor
```

**Sintaxe de cor:** `rgb(var(--x-rgb)/α)` exige o triplete separado por **espaço** (`11 20 35`). Misturar vírgula e barra é inválido e o navegador descarta a declaração em silêncio.

---

## 6. Estado atual

O site está sendo construído em `site/`, página a página, por feedback visual no navegador. Prontas: `index.html`, `sobre.html`, `especialidades.html`, `amara.html`. Todas compartilham `assets/css/site.css` e `assets/js/site.js`.

**Método aprovado para páginas novas** (fechado em 20/08/2026, na página `/amara`): cada cena da página ganha **layout próprio**. O que se reaproveita é o *vocabulário* — tokens, botões, escala tipográfica, gestos de motion, moldura deslocada, selo girando, ponto de luz, marquee — nunca a composição de outra página. Antes de desenhar, ler `assets/js/site.js` inteiro para saber que efeitos existem (`data-scene`/`.is-live`, `data-depth`, `data-glow`, `data-split`, `.rise` + `data-d`). Alternar `.scheme-04` e `.scheme-05` para dar ritmo. Sempre cobrir responsivo (1180/960/680) e `prefers-reduced-motion` para tudo que for novo.

Não criar arquivos novos, estrutura de build ou dependências sem pedido explícito.

Quando a construção das páginas começar, o `DS_Marina.html` deixa de ser rascunho e passa a ser contrato: página que não bate com ele está errada, mesmo que pareça bonita.
