/* Dra. Marina Mariz — comportamento do site.
   Sem dependências. Tudo desliga em prefers-reduced-motion. */
(() => {
  'use strict';
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  /* --- Abertura: vídeo de fundo ------------------------------------------- */
  if (reduced.matches) $$('.scene__bg video').forEach(v => v.pause());

  /* --- Abertura: máquina de escrever em loop ------------------------------- */
  const typer = $('.intro__type');
  if (typer) {
    const lines = JSON.parse(typer.dataset.type);
    if (reduced.matches) {
      typer.textContent = lines.join(' ');
    } else {
      let l = 0, i = 0, apagando = false;
      const tick = () => {
        const linha = lines[l];
        i += apagando ? -1 : 1;
        typer.textContent = linha.slice(0, i);
        let espera = apagando ? 42 : 95;
        if (!apagando && i === linha.length) { apagando = true; espera = 1900; }
        else if (apagando && i === 0) { apagando = false; l = (l + 1) % lines.length; espera = 450; }
        setTimeout(tick, espera);
      };
      setTimeout(tick, 700);
    }
  }

  /* --- Barra de leitura (só nas páginas de post) --------------------------- */
  const lerbar = $('.lerbar');
  if (lerbar) {
    const medir = () => {
      const rolavel = document.documentElement.scrollHeight - innerHeight;
      lerbar.style.setProperty('--lido', rolavel > 0 ? Math.min(1, scrollY / rolavel).toFixed(4) : 0);
    };
    addEventListener('scroll', medir, { passive: true });
    addEventListener('resize', medir, { passive: true });
    medir();
  }

  /* --- Dropdown do DS (portado de DS_Marina.html) -------------------------- */
  document.querySelectorAll('[data-dropdown]').forEach(root=>{
    const control=root.querySelector('[role="combobox"],.dropdown-button.main-button'),trigger=root.querySelector('.dropdown-button.main-button'),input=root.querySelector('.dropdown-input'),panel=root.querySelector('.dropdown-list-container'),list=root.querySelector('.dropdown-list'),options=[...root.querySelectorAll('.dropdown-list-item')],hidden=root.querySelector('input[type="hidden"]'),title=root.querySelector('.dropdown-title'),status=root.querySelector('.dropdown-status'),empty=root.querySelector('.dropdown-empty'),floating=root.querySelector('.floating-icon'),wrapper=root.querySelector('.dropdown-list-wrapper');
    if(!control||!panel||!list||!options.length)return;
    control.setAttribute('role','combobox');let active=-1,pointerFrame=0,typeahead='',typeTimer=0;
    const visible=()=>options.filter(option=>!option.hidden);
    const resize=()=>root.style.setProperty('--dropdown-height',root.classList.contains('is-open')?`${panel.scrollHeight+2}px`:'0px');
    const setActive=option=>{options.forEach(item=>item.classList.toggle('is-active',item===option));if(!option){active=-1;list.classList.remove('has-active');control.removeAttribute('aria-activedescendant');return}active=visible().indexOf(option);list.classList.add('has-active');list.style.setProperty('--translate-value',`${option.offsetTop}px`);control.setAttribute('aria-activedescendant',option.id);option.scrollIntoView({block:'nearest'});if(floating){floating.innerHTML=`<i class="ph ${option.dataset.icon||'ph-sparkle'}"></i>`}};
    const open=()=>{document.querySelectorAll('[data-dropdown].is-open').forEach(other=>{if(other!==root)other.dispatchEvent(new CustomEvent('dropdown:close'))});root.classList.add('is-open');control.setAttribute('aria-expanded','true');panel.setAttribute('aria-hidden','false');panel.inert=false;requestAnimationFrame(()=>{resize();setActive(options.find(o=>o.getAttribute('aria-selected')==='true'&&!o.hidden)||visible()[0])})};
    const close=()=>{root.classList.remove('is-open');root.dataset.pointerInside='false';control.setAttribute('aria-expanded','false');panel.setAttribute('aria-hidden','true');panel.inert=true;resize();list.classList.remove('has-active')};
    root.addEventListener('dropdown:close',close);
    const commit=option=>{if(!option)return;options.forEach(item=>item.setAttribute('aria-selected',String(item===option)));const label=option.querySelector('.dropdown-option__text')?.textContent.trim()||option.textContent.trim();if(title)title.textContent=label;if(input)input.value=label;if(hidden){hidden.value=option.dataset.value||label;hidden.dispatchEvent(new Event('change',{bubbles:true}))}root.classList.add('has-value');status.textContent=`Selecionado: ${label}`;close();control.focus()};
    trigger?.addEventListener('click',()=>root.classList.contains('is-open')?close():open());
    options.forEach(option=>{option.addEventListener('pointerenter',()=>setActive(option));option.addEventListener('click',()=>commit(option))});
    input?.addEventListener('focus',open);input?.addEventListener('input',()=>{const query=input.value.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();options.forEach(option=>{const text=option.textContent.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();option.hidden=!text.includes(query)});const count=visible().length;if(empty)empty.hidden=count>0;status.textContent=`${count} ${count===1?'opção encontrada':'opções encontradas'}`;setActive(visible()[0]);open();resize()});
    control.addEventListener('keydown',event=>{const items=visible();if(!items.length)return;if(event.key==='Escape'){event.preventDefault();close();return}if(event.key==='Tab'){close();return}if(event.key==='ArrowDown'||event.key==='ArrowUp'){event.preventDefault();if(!root.classList.contains('is-open'))open();const step=event.key==='ArrowDown'?1:-1;active=(active+step+items.length)%items.length;setActive(items[active]);return}if(event.key==='Home'||event.key==='End'){event.preventDefault();setActive(items[event.key==='Home'?0:items.length-1]);return}if(event.key==='Enter'||(event.key===' '&&!input)){event.preventDefault();if(root.classList.contains('is-open')&&active>=0)commit(items[active]);else open();return}if(!input&&event.key.length===1){typeahead+=event.key.toLowerCase();clearTimeout(typeTimer);typeTimer=setTimeout(()=>typeahead='',650);const found=items.find(item=>item.textContent.trim().toLowerCase().startsWith(typeahead));if(found){open();setActive(found)}}});
    if(matchMedia('(hover:hover) and (pointer:fine)').matches&&wrapper){wrapper.addEventListener('pointermove',event=>{cancelAnimationFrame(pointerFrame);pointerFrame=requestAnimationFrame(()=>{const rect=wrapper.getBoundingClientRect();root.style.setProperty('--floating-icon-left',`${event.clientX-rect.left}px`);root.style.setProperty('--floating-icon-top',`${event.clientY-rect.top}px`);root.dataset.pointerInside='true'})});wrapper.addEventListener('pointerleave',()=>root.dataset.pointerInside='false')}
    close();
  });

  /* --- Índice das páginas legais: marca a seção em quadro ------------------ */
  const toc = $('.leg__toc');
  if (toc && 'IntersectionObserver' in window) {
    const links = $$('a', toc);
    const alvos = links.map(a => document.getElementById(a.getAttribute('href').slice(1))).filter(Boolean);
    const marcar = (id) => links.forEach(a => a.classList.toggle('is-aqui', a.getAttribute('href') === '#' + id));
    const io = new IntersectionObserver(entradas => {
      // a seção mais alta ainda visível é a que está sendo lida
      const visiveis = entradas.filter(e => e.isIntersecting).map(e => e.target);
      if (visiveis.length) marcar(visiveis.sort((a, b) => a.offsetTop - b.offsetTop)[0].id);
    }, { rootMargin: '-20% 0px -70% 0px' });
    alvos.forEach(t => io.observe(t));
  }

  /* --- Fichas em <dialog>: abrir, fechar e clicar fora --------------------- */
  $$('[data-abre]').forEach(btn => btn.addEventListener('click', () => {
    // botão dentro de uma ficha (ex.: "Baixar grátis"): fecha a ficha antes de abrir a próxima
    btn.closest('dialog')?.close();
    document.getElementById(btn.dataset.abre)?.showModal();
  }));
  $$('dialog.modal').forEach(dlg => {
    $('[data-fecha]', dlg)?.addEventListener('click', () => dlg.close());
    // clique fora do conteúdo fecha; dentro, não
    dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });
  });

  /* --- Carregamento infinito das listas de Educação ------------------------ */
  /* O servidor continua paginando; aqui a próxima página é buscada e anexada.
     O botão fica visível: quem não tem JS, ou prefere clicar, segue pelo link. */
  $$('[data-infinito]').forEach(bloco => {
    const btn = $('.mais__btn', bloco);
    const fim = $('.mais__fim', bloco);
    const grade = $(bloco.dataset.alvo);
    let carregando = false;

    const encerrar = () => {
      bloco.removeAttribute('data-proxima');
      if (btn) btn.hidden = true;
      if (fim) fim.hidden = false;
    };

    const carregar = async () => {
      const url = bloco.dataset.proxima;
      if (!url || carregando || !grade) return;
      carregando = true;
      btn?.classList.add('is-carregando');
      try {
        const resposta = await fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
        if (!resposta.ok) throw new Error(resposta.status);
        const doc = new DOMParser().parseFromString(await resposta.text(), 'text/html');
        const novos = doc.querySelector(bloco.dataset.alvo);
        const seguinte = doc.querySelector('[data-infinito]')?.dataset.proxima;
        if (!novos || !novos.children.length) return encerrar();
        grade.append(...novos.children);
        if (seguinte) bloco.dataset.proxima = seguinte; else encerrar();
      } catch {
        encerrar();  // sem próxima página ou rede fora: a lista termina aqui
      } finally {
        carregando = false;
        btn?.classList.remove('is-carregando');
      }
    };

    // O botao e um link para ?pagina=N, para funcionar sem JavaScript.
    // Com JavaScript, a leva seguinte e anexada em vez de navegar.
    btn?.addEventListener('click', e => { e.preventDefault(); carregar(); });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(entradas => {
        if (entradas.some(e => e.isIntersecting)) carregar();
      }, { rootMargin: '400px 0px' }).observe(bloco);
    }
  });

  /* --- Cenas: ativa a que está em quadro ---------------------------------- */
  const scenes = $$('[data-scene]');
  const nav = $('.nav');
  if (scenes.length && 'IntersectionObserver' in window) {
    const so = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        // Cena mais alta que a janela nunca chega aos 12% de area visivel —
        // um documento legal de dez telas para em ~10% e ficaria invisivel,
        // com o texto no HTML e opacidade 0. Para essas, basta entrar em quadro.
        const maisAltaQueAJanela = entry.target.offsetHeight > window.innerHeight * 0.9;

        // entra e sai: a cena reanima toda vez que volta ao quadro
        entry.target.classList.toggle(
          'is-live',
          entry.isIntersecting && (maisAltaQueAJanela || entry.intersectionRatio >= 0.12),
        );
      });
    }, { threshold: [0, 0.12], rootMargin: '-4% 0px -8% 0px' });
    scenes.forEach(s => so.observe(s));

  } else {
    scenes.forEach(s => s.classList.add('is-live'));
  }


  /* --- Barra fixa: escondida nas duas primeiras cenas e sobre o rodapé ------ */
  const bar = $('.bar');
  let noRodape = false;
  const jaPassouAsCenas = () => !scenes[1] || scenes[1].getBoundingClientRect().bottom <= 80;

  /* --- Menu: some ao descer, volta ao subir -------------------------------- */
  if (nav) {
    const navStatic = nav.dataset.navMode === 'static';
    requestAnimationFrame(() => nav.classList.add('is-in'));
    let last = window.scrollY, ticking = false;
    const onScroll = () => {
      const y = window.scrollY;
      if (!navStatic) nav.classList.toggle('is-stuck', y > 60);
      if (bar) bar.classList.toggle('is-away', noRodape || !jaPassouAsCenas());
      last = y; ticking = false;
    };
    window.addEventListener('scroll', () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(onScroll);
    }, { passive: true });
    onScroll();
  }

  /* --- Profundidade nas fotos de fundo ------------------------------------- */
  const layers = $$('.scene__bg img[data-depth]');
  if (layers.length && !reduced.matches) {
    let raf = false;
    const run = () => {
      const vh = window.innerHeight;
      layers.forEach(img => {
        const scene = img.closest('.scene');
        const r = scene.getBoundingClientRect();
        if (r.bottom < -200 || r.top > vh + 200) return;
        const p = (r.top + r.height / 2 - vh / 2) / vh;
        const d = parseFloat(img.dataset.depth) || 0.05;
        img.style.transform = `scale(1.08) translate3d(0, ${(-p * vh * d).toFixed(1)}px, 0)`;
      });
      raf = false;
    };
    window.addEventListener('scroll', () => {
      if (raf) return;
      raf = true;
      requestAnimationFrame(run);
    }, { passive: true });
    run();
  }

  /* --- Dropdown Educação ---------------------------------------------------- */
  $$('.nav__dd').forEach(dd => {
    const trigger = $('.nav__dd-trigger', dd), panel = $('.nav__panel', dd);
    if (!trigger || !panel) return;
    const items = $$('a', panel);
    const close = (back = false) => {
      dd.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
      if (back) trigger.focus();
    };
    const open = () => {
      dd.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
    };
    trigger.addEventListener('click', () => {
      dd.classList.contains('is-open') ? close() : open();
    });
    if (matchMedia('(hover:hover)').matches) {
      dd.addEventListener('mouseenter', open);
      dd.addEventListener('mouseleave', () => close());
    }
    dd.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') return close(true);
      if (!dd.classList.contains('is-open')) return;
      const i = items.indexOf(document.activeElement);
      if (e.key === 'ArrowDown') { e.preventDefault(); items[(i + 1) % items.length]?.focus(); }
      if (e.key === 'ArrowUp')   { e.preventDefault(); items[(i - 1 + items.length) % items.length]?.focus(); }
    });
    document.addEventListener('click', (e) => { if (!dd.contains(e.target)) close(); });
  });

  /* --- Menu mobile ---------------------------------------------------------- */
  const drawer = $('.drawer'), toggle = $('.nav__toggle');
  if (drawer && toggle) {
    let lastFocus = null;
    const focusables = () => $$('a[href], button:not([disabled])', drawer);
    const setOpen = (open) => {
      drawer.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
      document.body.style.overflow = open ? 'hidden' : '';
      if (open) {
        lastFocus = document.activeElement;
        $$('.drawer__link', drawer).forEach((el, i) => { el.style.transitionDelay = `${0.05 + i * 0.04}s`; });
        focusables()[0]?.focus();
      } else {
        lastFocus?.focus();
      }
    };
    toggle.addEventListener('click', () => setOpen(!drawer.classList.contains('is-open')));
    drawer.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false); });
    document.addEventListener('keydown', (e) => {
      if (!drawer.classList.contains('is-open')) return;
      if (e.key === 'Escape') return setOpen(false);
      if (e.key !== 'Tab') return;
      const list = focusables(), first = list[0], last = list[list.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
    $$('.drawer__acc', drawer).forEach(acc => acc.addEventListener('click', () => {
      acc.setAttribute('aria-expanded', String(acc.getAttribute('aria-expanded') !== 'true'));
    }));
  }

  /* --- Lanterna nos cards (segue o ponteiro) -------------------------------- */
  if (matchMedia('(pointer:fine)').matches) {
    $$('.team__card,[data-glow]').forEach(area => {
      let frame = 0, x = 0, y = 0;
      area.addEventListener('mousemove', e => {
        x = e.clientX; y = e.clientY;
        if (frame) return;
        frame = requestAnimationFrame(() => {
          const r = area.getBoundingClientRect();
          area.style.setProperty('--mouse-x', `${x - r.left}px`);
          area.style.setProperty('--mouse-y', `${y - r.top}px`);
          frame = 0;
        });
      }, { passive: true });
    });
  }

  /* --- Especialidades: um painel aberto por vez ----------------------------- */
  const specBtns = $$('.spec__btn');
  specBtns.forEach(btn => btn.addEventListener('click', () => {
    const willOpen = btn.getAttribute('aria-expanded') !== 'true';
    specBtns.forEach(b => b.setAttribute('aria-expanded', 'false'));
    btn.setAttribute('aria-expanded', String(willOpen));
  }));

  /* --- Barra fixa recolhe sobre o rodapé ------------------------------------ */
  const footer = $('.footer');
  if (bar && footer && 'IntersectionObserver' in window) {
    new IntersectionObserver(([entry]) => {
      noRodape = entry.isIntersecting;
      bar.classList.toggle('is-away', noRodape || !jaPassouAsCenas());
    }, { rootMargin: '0px 0px -32% 0px' }).observe(footer);
  }

  /* --- Títulos que revelam letra a letra ------------------------------------ */
  /* fatia só os nós de texto: <br> e <strong> continuam de pé. Sem JS ou com
     movimento reduzido, o título aparece inteiro — o CSS só esconde .char. */
  if (!reduced.matches) $$('[data-split]').forEach(el => {
    const fatia = (no) => {
      [...no.childNodes].forEach(n => {
        if (n.nodeType === 3) {
          if (!n.textContent.trim()) return;
          const frag = document.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach(parte => {
            if (!parte.trim()) return frag.appendChild(document.createTextNode(parte));
            const palavra = document.createElement('span');
            palavra.className = 'word';
            [...parte].forEach(c => {
              const letra = document.createElement('span');
              letra.className = 'char';
              letra.textContent = c;
              palavra.appendChild(letra);
            });
            frag.appendChild(palavra);
          });
          n.replaceWith(frag);
        } else if (n.nodeType === 1 && n.tagName !== 'BR') fatia(n);
      });
    };
    fatia(el);
    el.classList.add('is-split');
    $$('.char', el).forEach((c, i) => { c.style.transitionDelay = (i * 0.016).toFixed(3) + 's'; });
  });

  /* --- Copiar o link do post ------------------------------------------------ */
  /* único botão de compartilhar que precisa de JS; WhatsApp e LinkedIn são <a>. */
  $$('[data-copy-link]').forEach(btn => btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(location.href);
    } catch {
      return;  // sem permissão de área de transferência: o link segue na barra
    }
    btn.classList.add('is-copied');
    const antes = btn.getAttribute('aria-label');
    btn.setAttribute('aria-label', 'Link copiado');
    setTimeout(() => {
      btn.classList.remove('is-copied');
      btn.setAttribute('aria-label', antes);
    }, 1800);
  }));

  /* --- Ano corrente --------------------------------------------------------- */
  const year = $('[data-year]');
  if (year) year.textContent = new Date().getFullYear();
})();
