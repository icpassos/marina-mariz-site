#!/usr/bin/env python3
"""Gera Acervo_Marina.html a partir de acervo/indice.json.

A numeração do índice é contrato: é por ela que a Isabela cita uma foto
("troca a 09 pela 14"). Foto nova entra no fim, com o próximo número livre —
renumerar quebraria toda conversa anterior.

Uso:
    python3 acervo/gerar.py

Lê o índice, recorta as miniaturas que faltam, descobre sozinho em que páginas
cada arquivo aparece e reescreve a página. Rodar de novo é seguro.
"""

import html
import json
import re
import subprocess
from pathlib import Path

RAIZ = Path(__file__).resolve().parent.parent      # Site Institucional/
ACERVO = RAIZ / "acervo"
THUMBS = ACERVO / "thumbs"
IMG = RAIZ / "site" / "assets" / "img"
PAGINA = RAIZ / "Acervo_Marina.html"

LARGURA_THUMB = 420        # o cartão nunca passa de ~300px de largura na tela
QUALIDADE_THUMB = 68


def origem_bruta(indice):
    """Pasta das fotos originais, fora do projeto. Pode não existir nesta máquina.

    O nome em disco tem um espaço à direita que é fácil de perder ao copiar, então
    caímos num glob pelo nome-pai quando o caminho literal não resolve.
    """
    caminho = Path(indice["origem"]).expanduser()
    if caminho.is_dir():
        return caminho
    for vizinha in sorted(caminho.parent.glob(caminho.name.strip() + "*")):
        if vizinha.is_dir():
            return vizinha
    return caminho


def dimensoes(caminho):
    saida = subprocess.run(
        ["sips", "-g", "pixelWidth", "-g", "pixelHeight", str(caminho)],
        capture_output=True, text=True,
    ).stdout
    achados = dict(re.findall(r"pixel(Width|Height): (\d+)", saida))
    return int(achados.get("Width", 0)), int(achados.get("Height", 0))


def fonte_da_foto(foto, bruta):
    """A melhor origem disponível: a foto bruta, ou o arquivo já no site."""
    candidata = bruta / foto["origem"]
    if candidata.exists():
        return candidata
    for arquivo in foto["arquivos"]:
        no_site = IMG / arquivo
        if no_site.exists():
            return no_site
    return None


def miniatura(foto, fonte):
    """Recorta a miniatura se ainda não existir. Devolve o caminho relativo."""
    THUMBS.mkdir(parents=True, exist_ok=True)
    destino = THUMBS / f"{foto['n']:02d}.jpg"
    if not destino.exists():
        subprocess.run(
            ["sips", "-s", "format", "jpeg", "-s", "formatOptions", str(QUALIDADE_THUMB),
             "-Z", str(LARGURA_THUMB), str(fonte), "--out", str(destino)],
            capture_output=True,
        )
    return f"acervo/thumbs/{destino.name}"


def usos_por_arquivo():
    """Varre os HTML do site: {'abraco.jpg': ['sobre.html', ...]}."""
    mapa = {}
    for pagina in sorted((RAIZ / "site").glob("*.html")):
        texto = pagina.read_text(encoding="utf-8")
        for alvo in set(re.findall(r'src="assets/img/([^"]+)"', texto)):
            mapa.setdefault(alvo, set()).add(pagina.stem)
    return {k: sorted(v) for k, v in mapa.items()}


def ficha(foto, fonte, thumb, usos):
    paginas = sorted({p for a in foto["arquivos"] for p in usos.get(a, [])})
    usada = bool(paginas)
    largura, altura = dimensoes(fonte) if fonte else (0, 0)

    if usada:
        selo, onde = "em uso", " · ".join(paginas)
    elif foto["arquivos"]:
        selo, onde = "importada", "no projeto, mas fora de todas as páginas"
    else:
        selo, onde = "livre", "ainda não entrou no projeto"

    destino = " · ".join(foto["arquivos"]) if foto["arquivos"] else ""

    return f"""      <li class="card{'' if usada else ' card--free'}" id="foto-{foto['n']:02d}">
        <figure class="card__shot"><img src="{thumb}" alt="{html.escape(foto['desc'])}" loading="lazy"></figure>
        <div class="card__meta">
          <span class="card__n">{foto['n']:02d}</span>
          <div class="card__facts">
            <p class="card__file">{html.escape(foto['origem'])}</p>
            <p class="card__dim">{largura} × {altura}</p>
          </div>
        </div>
        <p class="card__desc">{html.escape(foto['desc'])}</p>
        <p class="card__use"><span class="chip">{selo}</span>{html.escape(onde)}</p>
        <p class="card__dest">{html.escape(destino)}</p>
      </li>"""


def montar(indice, fichas, livres):
    contagem = len(indice["fotos"])
    lista_livres = ", ".join(f"{n:02d}" for n in livres) or "nenhuma"
    return CABECALHO + f"""
<div class="shell">
  <header>
    <span class="eyebrow">Referência de trabalho</span>
    <h1>Acervo Marina Mariz</h1>
    <p class="dek">As {contagem} fotos do acervo, cada uma com um número que não muda. Cite a foto por esse número — <code>troca a 09 pela 14</code> — e não há como errar de imagem.</p>
    <p class="legend">
      <b>Como ler</b>
      <span>Ficha tracejada = ainda não entrou em nenhuma página do site.</span>
      <span>A linha em mono no pé mostra o nome do arquivo dentro de <code>site/assets/img/</code>.</span>
    </p>
  </header>

  <ul class="grid">
{fichas}
  </ul>

  <footer>
    <p><strong>Fotos ainda livres:</strong> {lista_livres}.</p>
    <p>Esta página é gerada por <code>acervo/gerar.py</code> a partir de <code>acervo/indice.json</code>. Foto nova entra no fim do índice, com o próximo número livre — os números já usados nunca mudam, senão toda conversa anterior passaria a apontar para a imagem errada.</p>
  </footer>
</div>
</body>
</html>
"""


CABECALHO = """<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Acervo — Dra. Marina Mariz</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=JetBrains+Mono:wght@500;700&family=Newsreader:opsz,wght@6..72,300;6..72,400&display=swap">
<style>
/* paleta do DS: Sereno e Oceano, acento Violeta 2 no claro e Violeta no escuro */
:root{
  --ground:#F3F1E8; --surface:#FEFEFE; --surface-2:#EFEDE2;
  --ink:#0b1423; --ink-soft:#5C5768; --line:rgb(11 20 35 / .12);
  --accent:#5648A8; --accent-soft:rgb(86 72 168 / .12);
  --shadow:rgb(11 20 35 / .10);
}
@media (prefers-color-scheme: dark){
  :root:not([data-theme="light"]){
    --ground:#1B2B3F; --surface:#22344A; --surface-2:#1F3044;
    --ink:#EBEBEB; --ink-soft:#A9A5B8; --line:rgb(235 235 235 / .14);
    --accent:#B8A3F5; --accent-soft:rgb(184 163 245 / .14);
    --shadow:rgb(0 0 0 / .38);
  }
}
:root[data-theme="dark"]{
  --ground:#1B2B3F; --surface:#22344A; --surface-2:#1F3044;
  --ink:#EBEBEB; --ink-soft:#A9A5B8; --line:rgb(235 235 235 / .14);
  --accent:#B8A3F5; --accent-soft:rgb(184 163 245 / .14);
  --shadow:rgb(0 0 0 / .38);
}

*{box-sizing:border-box}
body{margin:0;background:var(--ground);color:var(--ink);font:400 16px/1.6 Archivo,system-ui,sans-serif;-webkit-font-smoothing:antialiased}
.shell{width:min(1240px,calc(100% - 48px));margin-inline:auto}

header{padding:clamp(44px,6vw,80px) 0 clamp(28px,3vw,40px)}
.eyebrow{display:inline-block;margin:0 0 14px;font:700 .72rem/1 "JetBrains Mono",monospace;letter-spacing:.18em;text-transform:uppercase;color:var(--accent)}
h1{margin:0 0 16px;max-width:18ch;font:300 clamp(2.1rem,4.4vw,3.4rem)/1.05 Newsreader,Georgia,serif;letter-spacing:-.02em;text-wrap:balance}
.dek{margin:0;max-width:62ch;color:var(--ink-soft)}
code{font:500 .92em/1 "JetBrains Mono",monospace;padding:2px 6px;border-radius:5px;background:var(--accent-soft);color:var(--accent)}
.legend{display:flex;flex-wrap:wrap;gap:10px 22px;align-items:center;margin:26px 0 0;padding-top:22px;border-top:1px solid var(--line);font-size:.86rem;color:var(--ink-soft)}
.legend b{font:700 .7rem/1 "JetBrains Mono",monospace;letter-spacing:.14em;text-transform:uppercase;color:var(--ink)}

.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(226px,1fr));gap:clamp(16px,1.8vw,24px);margin:0 0 clamp(56px,7vw,96px);padding:0;list-style:none}
.card{display:flex;flex-direction:column;border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--surface);box-shadow:0 6px 22px var(--shadow);scroll-margin-top:24px}
.card--free{background:var(--surface-2);border-style:dashed}
.card__shot{margin:0;aspect-ratio:3/4;overflow:hidden;background:var(--surface-2)}
.card__shot img{display:block;width:100%;height:100%;object-fit:cover}

.card__meta{display:flex;align-items:baseline;gap:12px;padding:14px 16px 10px;border-bottom:1px solid var(--line)}
.card__n{font:700 1.5rem/1 "JetBrains Mono",monospace;letter-spacing:-.02em;font-variant-numeric:tabular-nums;color:var(--accent)}
.card__facts{min-width:0;flex:1}
.card__file{margin:0;font-size:.78rem;line-height:1.35;overflow-wrap:anywhere}
.card__dim{margin:3px 0 0;font:500 .72rem/1 "JetBrains Mono",monospace;font-variant-numeric:tabular-nums;color:var(--ink-soft)}

.card__desc{margin:0;padding:12px 16px 0;font-size:.82rem;line-height:1.5;color:var(--ink-soft)}
.card__use{display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin:0;padding:12px 16px 4px;font-size:.8rem;line-height:1.45;color:var(--ink-soft)}
.chip{font:700 .64rem/1 "JetBrains Mono",monospace;letter-spacing:.14em;text-transform:uppercase;padding:5px 9px;border-radius:999px;background:var(--accent-soft);color:var(--accent)}
.card--free .chip{background:transparent;color:var(--ink-soft);box-shadow:inset 0 0 0 1px var(--line)}
.card__dest{margin:0;padding:8px 16px 16px;font:500 .72rem/1.45 "JetBrains Mono",monospace;overflow-wrap:anywhere}
.card__dest:empty{padding-bottom:14px}

footer{padding:0 0 clamp(48px,6vw,80px);font-size:.84rem;line-height:1.6;color:var(--ink-soft)}
footer p{margin:0 0 10px;max-width:70ch}
footer strong{color:var(--ink);font-weight:600}
</style>
</head>
<body>"""


def main():
    indice = json.loads((ACERVO / "indice.json").read_text(encoding="utf-8"))
    bruta = origem_bruta(indice)
    usos = usos_por_arquivo()

    fichas, livres, sem_fonte = [], [], []
    for foto in sorted(indice["fotos"], key=lambda f: f["n"]):
        fonte = fonte_da_foto(foto, bruta)
        if fonte is None:
            sem_fonte.append(foto["n"])
            continue
        fichas.append(ficha(foto, fonte, miniatura(foto, fonte), usos))
        if not any(usos.get(a) for a in foto["arquivos"]):
            livres.append(foto["n"])

    PAGINA.write_text(montar(indice, "\n".join(fichas), livres), encoding="utf-8")

    print(f"{PAGINA.name}: {len(fichas)} fichas, {len(livres)} sem uso")
    if sem_fonte:
        nums = ", ".join(f"{n:02d}" for n in sem_fonte)
        print(f"sem arquivo de origem, ficaram de fora: {nums}")


if __name__ == "__main__":
    main()
