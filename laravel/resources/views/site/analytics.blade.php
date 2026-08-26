{{--
    Scripts de Analytics (doc 06). ID em branco = script nao carregado,
    nenhuma requisicao de rastreamento sai do site.

    `$analyticsPermitido` e `$marketingPermitido` vem do Aviso de Cookies,
    compartilhados pelo middleware antes desta view renderizar. Sem
    consentimento explicito nada e emitido, e o padrao e nao emitir: o ID
    sozinho nao liga o rastreamento.

    Analiticos = Google Analytics OU Tag Manager. Marketing = Meta Pixel.
--}}
@php
    $configuracoes = \App\Services\Site::configuracoes();
@endphp

@if (($analyticsPermitido ?? false))
    @if (filled($configuracoes->google_tag_manager_id) || filled($configuracoes->google_analytics_id))
        <script>
            // Antes de qualquer tag do Google: sem isto, uma tag de
            // marketing dentro do container do GTM dispararia mesmo com
            // marketing recusado (doc 06).
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            @php $marketing = ($marketingPermitido ?? false) ? 'granted' : 'denied'; @endphp
            gtag('consent', 'default', {
                analytics_storage: 'granted',
                ad_storage: @js($marketing),
                ad_user_data: @js($marketing),
                ad_personalization: @js($marketing),
            });
        </script>
    @endif

    @if (filled($configuracoes->google_tag_manager_id))
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
            j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer',@js($configuracoes->google_tag_manager_id));
        </script>
    @endif

    @if (filled($configuracoes->google_analytics_id))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $configuracoes->google_analytics_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @js($configuracoes->google_analytics_id));
        </script>
    @endif

@endif

@if (($marketingPermitido ?? false))
    @if (filled($configuracoes->meta_pixel_id))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @js($configuracoes->meta_pixel_id));
            fbq('track', 'PageView');
        </script>
    @endif
@endif
