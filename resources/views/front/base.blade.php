<!doctype html>
<html lang="pt-br">
    <head>
      <!-- Global site tag (gtag.js) - Google Analytics -->
      <script async src="https://www.googletagmanager.com/gtag/js?id=UA-175097640-1"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-175097640-1');
      </script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('page-title') - Teses & Súmulas</title>

        <meta name="description" content="{{ config('tes_constants.options.meta_description') }}">
        <meta name="author" content="Mauro Lopes">

        <!-- Open Graph Meta -->
        <meta property="og:title" content="Teses e Súmulas">
        <meta property="og:site_name" content="tesesesumulas">
        <meta property="og:description" content="{{ config('tes_constants.options.meta_description') }}">
        <meta property="og:type" content="website">
<!--         <meta property="og:url" content="">
        <meta property="og:image" content=""> -->

        <!-- Icons -->
        <link rel="apple-touch-icon" sizes="57x57" href='{{ url("assets/img/icons/apple-icon-57x57.png") }}'>
        <link rel="apple-touch-icon" sizes="60x60" href='{{ url("assets/img/icons/apple-icon-60x60.png") }}'>
        <link rel="apple-touch-icon" sizes="72x72" href='{{ url("assets/img/icons/apple-icon-72x72.png") }}'>
        <link rel="apple-touch-icon" sizes="76x76" href='{{ url("assets/img/icons/apple-icon-76x76.png") }}'>
        <link rel="apple-touch-icon" sizes="114x114" href='{{ url("assets/img/icons/apple-icon-114x114.png") }}'>
        <link rel="apple-touch-icon" sizes="120x120" href='{{ url("assets/img/icons/apple-icon-120x120.png") }}'>
        <link rel="apple-touch-icon" sizes="144x144" href='{{ url("assets/img/icons/apple-icon-144x144.png") }}'>
        <link rel="apple-touch-icon" sizes="152x152" href='{{ url("assets/img/icons/apple-icon-152x152.png") }}'>
        <link rel="apple-touch-icon" sizes="180x180" href='{{ url("assets/img/icons/apple-icon-180x180.png") }}'>
        <link rel="icon" type="image/png" sizes="192x192"  href='{{ url("assets/img/icons/android-icon-192x192.png") }}'>
        <link rel="icon" type="image/png" sizes="32x32" href='{{ url("assets/img/icons/favicon-32x32.png") }}'>
        <link rel="icon" type="image/png" sizes="96x96" href='{{ url("assets/img/icons/favicon-96x96.png") }}'>
        <link rel="icon" type="image/png" sizes="16x16" href='{{ url("assets/img/icons/favicon-16x16.png") }}'>
        <link rel="manifest" href='{{ url("assets/img/icons/manifest.json") }}'>
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content='{{ url("assets/img/icons/ms-icon-144x144.png") }}'>
        <meta name="theme-color" content="#ffffff">
      <!-- END Icons -->
        
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600,700%7COpen+Sans:300,400,400italic,600,700">
        <link rel="stylesheet" id="css-main" href='{{ url("assets/css/tescustom.min.css") }}' type="text/css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link rel="stylesheet" href='{{ url("assets/css/tes.css") }}' type="text/css">
        <link rel="stylesheet" href='{{ url("assets/css/tespdf.css") }}' type="text/css" media="tespdf">
  	
        @yield('styles')

  </head>
    <body>
        <!-- Page Container -->
        <div id="page-container" class="main-content-boxed">

            <!-- Main Container -->
            <main id="main-container">

            @yield('content')


            </main>
            <!-- END Main Container -->

            <!-- Footer -->
            <footer id="page-footer" class="bg-body-light" style="">
              <div class="content py-3">
                    <div class="row font-size-sm">
                        <div class="col-sm-6 order-sm-2 py-1 text-center text-sm-right">
                            Criado por <a class="font-w600" href="mailto:mauluis@gmail.com" target="_blank">Mauro Lopes</a> &copy; <span data-toggle="year-copy"></span>. Todos os direitos reservados.
                        </div>
                        <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-left">
                          Fontes:
                           
                          <a class="font-w600" href="https://jurisprudencia.stf.jus.br/pages/search" target="_blank">STF</a>
                           -  
                          <a class="font-w600" href="https://jurisprudencia.tst.jus.br/" target="_blank">TST</a>
                           -  
                          <a class="font-w600" href="https://scon.stj.jus.br/SCON/" target="_blank">STJ</a>
                           -  
                          <a class="font-w600" href="https://www2.cjf.jus.br/jurisprudencia/tnu/" target="_blank">TNU</a>
                           -  
                          <a class="font-w600" href="https://pesquisa.apps.tcu.gov.br/#/pesquisa/jurisprudencia" target="_blank">TCU</a>
                           -  
                          <a class="font-w600" href="http://idg.carf.fazenda.gov.br/jurisprudencia/sumulas-carf" target="_blank">CARF</a>
                           -  
                          <a class="font-w600" href="https://www.cnj.jus.br/corregedoria-nacional-de-justica/redescobrindo-os-juizados-especiais/enunciados-fonaje/" target="_blank">FONAJE</a>
                           -  
                          <a class="font-w600" href="https://www.cjf.jus.br/enunciados/" target="_blank">CEJ</a>
                                                  </div>
                    </div>
                </div>
            </footer>
            <!-- END Footer -->

            
        </div>
        <!-- END Page Container -->

        <script src='{{ url("assets/js/tescustom.core.min.js") }}'></script>
        <script src='{{ url("assets/js/tescustom.app.min.js") }}'></script>
        <script src='{{ url("assets/js/tes.js") }}'></script>
        
    </body>
</html>