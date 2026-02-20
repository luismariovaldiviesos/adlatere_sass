<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Admiro admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Admiro admin template, best javascript admin, dashboard template, bootstrap admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <title>Adlatere SaaS - Gestión Judicial </title>
    <!-- Favicon icon-->
    <link rel="icon" href="{{ asset('assets2/images/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets2/images/favicon.png') }}" type="image/x-icon">
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900;6..12,1000&amp;display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&amp;family=Dancing+Script:wght@700&amp;family=Lobster&amp;display=swap"
        rel="stylesheet">
    
    <!-- Google Analytics -->
    @if(env('GOOGLE_ANALYTICS_ID'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ env('GOOGLE_ANALYTICS_ID') }}');
    </script>
    @endif
    <!-- End Google Analytics -->
    
    <!-- Flag icon css -->
    <link rel="stylesheet" href="{{ asset('assets2/css/vendors/flag-icon.css') }}">
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ asset('assets2/css/iconly-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets2/css/bulk-style.css') }}">
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ asset('assets2/css/themify.css') }}">
    <!--fontawesome-->
    <link rel="stylesheet" href="{{ asset('assets2/css/fontawesome-min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets2/css/vendors/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets2/css/vendors/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets2/css/vendors/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets2/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" href="{{ asset('assets2/css/style.css') }}">
    @livewireStyles
    <style>
        :root {
            --landing-primary: #1c3faa;
            --landing-primary-dark: #2b51b4;
        }
        /* Override Landing Page Backgrounds */
        .landing-page .landing-header {
            background-color: var(--landing-primary) !important;
        }
        .landing-home {
            background: linear-gradient(180deg, var(--landing-primary) 0%, var(--landing-primary-dark) 100%) !important;
            padding-bottom: 50px; /* Ensure coverage */
        }
        .landing-footer {
            background-color: var(--landing-primary-dark) !important;
        }
        /* Ensure text visibility on dark backgrounds if generic classes verify */
        header .nav-link, header .navbar-brand, .landing-home h1, .landing-home p {
            color: #ffffff !important;
        }
        .landing-menu .nav-item .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }
        .landing-menu .nav-item .nav-link:hover {
            color: #ffffff !important;
            font-weight: bold;
        }
    </style>
</head>
@livewireScripts
<body class="landing-page">
    <!-- tap on top starts-->
    <div class="tap-top"><i class="iconly-Arrow-Up icli"></i></div>

    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="landing-page">
        <!-- Page Body Start-->
        <!-- header start-->
        <header class="landing-header">
            <div class="container-fluid fluid-space">
                <div class="row">
                    <div class="col-12">
                        <nav class="navbar navbar-light p-0" id="navbar-example2">
                            <a class="navbar-brand" href="javascript:void(0)">
                                <img class="img-fluid" src="{{ asset('assets2/images/logo_saas1_nobg.png') }}" alt=""
                                    style="height: 80px!important">
                            </a>
                            <ul class="landing-menu nav nav-pills">
                                <li class="nav-item menu-back">back<i class="fa-solid fa-angle-right"></i></li>
                                <li class="nav-item"><a class="nav-link" href="#home">Inicio</a></li>
                                <li class="nav-item"><a class="nav-link" href="#applications">Planes</a></li>
                                <li class="nav-item"><a class="nav-link" href="#core-feature">Funcionalidades</a></li>
                                <li class="nav-item"><a class="nav-link" href="javascript:void(0)" onclick="promptLogin()">Ingresar</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ url('/dash') }}">Admin</a></li>
                            </ul>
                            <div class="buy-block">
                                <div class="toggle-menu"><i class="fa-solid fa-bars"></i></div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </header>
        <!-- header end-->
        <!--home-section-start-->
        <section class="landing-home" id="home">
            <div class="container">
                <div class="landing-center landing-center-responsive title-padding">
                    <!-- FLASH MESSAGES -->
                    @if(session('error'))
                        <div class="alert alert-danger text-center mb-4">
                            <strong>¡Error!</strong> {{ session('error') }}
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success text-center mb-4">
                            <strong>¡Éxito!</strong> {{ session('success') }}
                        </div>
                    @endif
                    <!-- END FLASH MESSAGES -->
                    <div class="main-content-home">
                        <div class="title-content">
                            <h1>Tu Despacho <span class="animation-line">Inteligente </span> en la Nube
                            </h1>
                            <p>Gestión de casos, juicios, clientes.  Todo en un solo lugar.</p>
                            
                        </div>
                        <div class="main-buttons">
                            <button id="try-free-button" class="btn btn-primary">Empieza Ahora</button>
                        </div>

                    </div>
                    <div class="container-fluid">
                        <div class="landing_first_section_img">
                            <div class="img-set1"><img class="img-fluid"
                                    src="{{ asset('assets2/images/landing/screen2.png') }}" alt=""></div>
                            <div class="img-set2"><img class="img-fluid"
                                    src="{{ asset('assets2/images/landing/screen3.png') }}" alt=""></div>
                            <div class="img-set3"><img class="img-fluid"
                                    src="{{ asset('assets2/images/landing/screen1.png') }}" alt=""></div>
                        </div>
                    </div>
                    <div class="bottom-img-1"></div>
                    <div class="bottom-img-2"></div>
                </div><a class="tap-down" href="#applications"><i class="icon-angle-double-down"> </i></a>
            </div>
            <div class="round-tringle">
                <div class="bg_circle1"><img class="img-fluid"
                        src="{{ asset('assets2/images/landing/shape/shape1.png') }}" alt="">
                </div>
                <div class="bg_circle2"><img class="img-fluid"
                        src="{{ asset('assets2/images/landing/shape/shape2.png') }}" alt="">
                </div>
                <div class="bg_circle3">
                    <div class="d-flex"><img class="img-fluid"
                            src="{{ asset('assets2/images/landing/shape/shape3.png') }}" alt="">
                        <h4>Rápido</h4>
                    </div>
                </div>
                <div class="bg_circle4"><img class="img-fluid"
                        src="{{ asset('assets2/images/landing/shape/shape4.png') }}" alt="">
                </div>
                <div class="bg_circle5"><img class="img-fluid"
                        src="{{ asset('assets2/images/landing/shape/shape5.png') }}" alt="">
                </div>
                <div class="bg_circle6"><img class="img-fluid"
                        src="{{ asset('assets2/images/landing/shape/shape6.png') }}" alt="">
                </div>
                <div class="bg_circle7"><img class="img-fluid"
                        src="{{ asset('assets2/images/landing/shape/shape7.png') }}" alt="">
                </div>
            </div>
        </section>
        
        <!-- Plan section -->
        <section class="section-py-space application-section" id="applications">
            <div class="container-fluid fluid-space">
                <div class="row">
                    <div class="col-sm-12 wow pulse">
                        <div class="title-style wow pulse">
                            <h5 class="main-title">Planes Flexibles<span
                                    class="description-title">Elige el mejor para ti</span>
                            </h5>
                        </div>
                    </div>
                    <div class="col-sm-12 application">
                        <div class="card">

                            <div class="card-body row pricing-block">


                                 <livewire:payator />

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- application-section-end-->


        <!-- Feature section -->
        <section class="section-py-space features-section" id="core-feature">
            <div class="container-fluid fluid-space">
                <div class="row">
                    <div class="col-sm-12 wow pulse">
                        <div class="title-style wow pulse">
                            <h5 class="main-title">Todo lo que Necesitas<span class="description-title">
                                    Características Principales</span></h5>
                        </div>
                    </div>
                </div>
                <div class="row g-4 g-md-5 feature-content">
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.1s"
                        style="visibility: visible;-webkit-animation-duration: 0.1s; -moz-animation-duration: 0.1s; animation-duration: 0.1s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/1.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Facturación Electrónica</h5>
                            <p class="mb-0">Emisión  de comprobantes autorizados por el SRI. Olvídate del papeleo.</p>
                        </div>
                    </div>
                       <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.1s"
                        style="visibility: visible;-webkit-animation-duration: 0.1s; -moz-animation-duration: 0.1s; animation-duration: 0.1s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/1.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Notas de Crédito</h5>
                            <p class="mb-0">Emisión  de notas de crédito autorizadas por el SRI. Olvídate del papeleo.</p>
                        </div>
                    </div>
                       <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.1s"
                        style="visibility: visible;-webkit-animation-duration: 0.1s; -moz-animation-duration: 0.1s; animation-duration: 0.1s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/1.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Manejo de Cajas</h5>
                            <p class="mb-0">Control de cajas, apertura, cierre, arqueo y más.</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.2s"
                        style="visibility: visible;-webkit-animation-duration: 0.0.2s; -moz-animation-duration: 0.0.2s; animation-duration: 0.0.2s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/2.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Punto de Venta (POS)</h5>
                            <p class="mb-0">Interfaz ágil para ventas rápidas en mostrador. Busca productos por nombre o código de barras.</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.3s"
                        style="visibility: visible;-webkit-animation-duration: 0.3s; -moz-animation-duration: 0.3s; animation-duration: 0.3s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/3.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Control de Inventarios</h5>
                            <p class="mb-0">Gestiona tu stock en tiempo real. Alertas de bajo stock y movimientos detallados (kardex).</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.4s"
                        style="visibility: visible;-webkit-animation-duration: 0.4s; -moz-animation-duration: 0.4s; animation-duration: 0.4s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/4.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Reportes Inteligentes</h5>
                            <p class="mb-0">Dashboard con métricas clave: Ventas diarias, productos más vendidos y rendimiento mensual.</p>
                        </div>
                    </div>
                        <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.4s"
                        style="visibility: visible;-webkit-animation-duration: 0.4s; -moz-animation-duration: 0.4s; animation-duration: 0.4s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/4.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Manejo de impuestos y descuentos en productos</h5>
                            <p class="mb-0">Configura impuestos y descuentos personalizados para cada producto.</p>
                        </div>
                    </div>
                       <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.4s"
                        style="visibility: visible;-webkit-animation-duration: 0.4s; -moz-animation-duration: 0.4s; animation-duration: 0.4s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/4.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Manejo de usuarios, roles y permisos</h5>
                            <p class="mb-0">Configura usuarios, roles y permisos personalizados para cada usuario.</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.5s"
                        style="visibility: visible;-webkit-animation-duration: 0.5s; -moz-animation-duration: 0.5s; animation-duration: 0.5s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/5.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Tu Propia Base de Datos</h5>
                            <p class="mb-0">Cada cliente tiene su base de datos aislada para máxima seguridad y privacidad (Arquitectura Multi-tenant).</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.6s"
                        style="visibility: visible;-webkit-animation-duration: 0.6s; -moz-animation-duration: 0.6s; animation-duration: 0.6s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/6.svg') }}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Factura desde cualquier lugar</h5>
                            <p class="mb-0">Controla tu negocio desde cualquier lugar: Solo necesitas una conexión a internet.</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.7s"
                        style="visibility: visible;-webkit-animation-duration: 0.7s; -moz-animation-duration: 0.7s; animation-duration: 0.7s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{asset('assets2/images/landing/feature-icon/7.svg')}}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Soporte Dedicado</h5>
                            <p class="mb-0">Equipo de soporte listo para ayudarte a configurar y resolver dudas técnicas.</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-sm-6 wow fadeInUp animated" data-wow-duration="0.8s"
                        style="visibility: visible;-webkit-animation-duration: 8s; -moz-animation-duration: 8s; animation-duration: 8s;">
                        <div class="feature-box common-card bg-feature">
                            <div class="feature-icon bg-white">
                                <div><img src="{{ asset('assets2/images/landing/feature-icon/8.svg')}}"
                                        alt="feature-icon"></div>
                            </div>
                            <h5>Actualizaciones Gratuitas</h5>
                            <p class="mb-0">Mejoramos la plataforma constantemente sin costo adicional para ti.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- features-section-end-->

        
        <!-- Footer section -->
        <div class="footer landing-footer section-py-space" id="footer">
            <div class="container-fluid fluid-space">
                <div class="landing-center">
                    <div class="feature-content">
                        <div>
                            <h2>Comienza a tramitar hoy mismo</h2>
                            <div class="footer-rating"><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                        </div><a class="btn btn-primary footer-btn" href="#applications">
                            Ver Planes</a>
                    </div>
                </div>
                <div class="sub-footer row g-md-2 g-3">
                    <div class="col-md-6">
                        <div class="left-subfooter"><img class="img-fluid"
                                src="{{ asset('assets2/images/logo_saas1_nobg.png') }}" alt="logo">
                            <p class="mb-0">Copyright 2025 &copy; Adlatere SaaS - Todos los derechos reservados.</p>
                            <small>Desarrollado por Khipu Sistemas</small>
                            <small>Ni por mar ni por tierra encontraras el camino que conduce a los hiperbóreos. Ya Píndaro lo vaticinó </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="right-subfooter">
                        </div>
                    </div>
                </div>
            </div>
            <ul class="shape">
                <li class="shape1"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape1.png') }}"
                        alt=""></li>
                <li class="shape2"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape2.png') }}"
                        alt=""></li>
                <li class="shape3"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape3.png') }}"
                        alt=""></li>
                <li class="shape4"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape4.png') }}"
                        alt=""></li>
                <li class="shape5"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape5.png') }}"
                        alt=""></li>
                <li class="shape7"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape1.png') }}"
                        alt=""></li>
                <li class="shape8"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape1.png') }}"
                        alt=""></li>
                <li class="shape9"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape7.png') }}"
                        alt=""></li>
                <li class="shape10"><img class="img-fluid" src="{{ asset('assets2/images/landing/footer/shape7.png') }}"
                        alt=""></li>
            </ul>
        </div>
        <!-- footer-section-end-->
    </div>
    <!-- jquery-->
    <script src="{{ asset('assets2/js/vendors/jquery/jquery.min.js') }}"></script>
    <!-- bootstrap js-->
    <script src="{{ asset('assets2/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <!-- fontawesome js-->
    <script src="{{ asset('assets2/js/vendors/font-awesome/fontawesome-min.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('assets2/js/tooltip-init.js') }}"></script>
    <script src="{{ asset('assets2/js/animation/wow/wow.min.js') }}"></script>
    <script src="{{ asset('assets2/js/landing/landing.js') }}"></script>
    <script src="{{ asset('assets2/js/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets2/js/slick/slick.js') }}"></script>
    <script src="{{asset('assets2/js/sweetalert/sweetalert2.min.js') }}"></script>

    <script>
        // Debug Loading
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Facta SaaS Landing Loaded');
            if(typeof window.bootstrap === 'undefined') {
                console.warn('Bootstrap 5 Object not found on Global Scope');
            } else {
                console.log('Bootstrap 5 Detected');
            }
        });

        window.addEventListener('open-modal', event => {
            console.log('Event open-modal received');
            const modalId = '#paymentModal';
            
            // Prioritize jQuery for this template as it seems to depend on it
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
                console.log('Opening modal via jQuery');
                jQuery(modalId).modal('show');
            } 
            // Fallback to Bootstrap 5 Native
            else if (typeof window.bootstrap !== 'undefined') {
                console.log('Opening modal via Bootstrap 5 Native');
                const modalEl = document.querySelector(modalId);
                const myModal = window.bootstrap.Modal.getOrCreateInstance(modalEl); 
                myModal.show();
            } else {
                alert('Error: No se encuentra la librería Bootstrap o jQuery para abrir el modal.');
            }
        });
        document.getElementById('try-free-button').addEventListener('click', function() {
        // Desplazarse a la sección de los planes
        document.getElementById('applications').scrollIntoView({ behavior: 'smooth' });
    });





    window.setSuscriptionType = function(type) {
        var event = new CustomEvent('toggleModal', { detail: { type: type } })
        window.dispatchEvent(event)
    }

    function promptLogin() {
        Swal.fire({
            title: 'Ingresar a tu Espacio',
            text: 'Escribe el nombre de tu empresa (subdominio) para redirigirte.',
            input: 'text',
            inputPlaceholder: 'ej: empresa1',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ir al Login',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            inputValidator: (value) => {
                if (!value) {
                    return '¡Debes escribir el nombre de tu empresa!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const subdomain = result.value.trim().toLowerCase();
                const protocol = window.location.protocol;
                const host = window.location.host; // facta_saas.test
                
                // Construct URL: protocol // subdomain . host / login
                const targetUrl = `${protocol}//${subdomain}.${host}/login`;
                
                // Show loading before redirect
                Swal.fire({
                    title: 'Redirigiendo...',
                    html: `Navegando a <b>${targetUrl}</b>`,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                }).then(() => {
                    window.location.href = targetUrl;
                });
            }
        })
    }





    </script>
</body>

</html>
