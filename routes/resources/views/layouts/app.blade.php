<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- AdminLTE 3 CSS with Bootstrap Icons and FontAwesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

        <!-- Custom CSS for Active Menu States and Arrow Rotation -->
        <style>
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: #007bff !important;
            color: #fff !important;
        }

        .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
        }

        .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link {
            color: #c2c7d0;
        }

        .sidebar-dark-primary .nav-sidebar .nav-treeview > .nav-item > .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        /* Ensure arrow rotation works correctly */
        .nav-sidebar .nav-item.has-treeview > .nav-link > .right {
            transition: transform 0.3s ease-in-out;
        }

        .nav-sidebar .nav-item.has-treeview.menu-open > .nav-link > .right {
            transform: rotate(-90deg);
        }

        /* Sidebar scroll and height - Solo un scroll */
        .main-sidebar {
            height: 100vh !important;
            overflow: hidden !important;
            display: flex;
            flex-direction: column;
            width: 250px !important;
        }

        .main-sidebar > .brand-link,
        .main-sidebar > .user-panel {
            flex-shrink: 0;
        }

        .sidebar {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            height: auto !important;
            display: flex;
            flex-direction: column;
        }

        .sidebar-footer {
            flex-shrink: 0;
            margin-top: auto;
        }

        /* Ajustar el contenido cuando el sidebar está expandido */
        .sidebar-expanded .content-wrapper,
        .sidebar-expanded .main-footer,
        .sidebar-expanded .main-header {
            margin-left: 250px !important;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #343a40;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #6c757d;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #5a6268;
        }

        /* Asegurar que el menú tenga espacio al final */
        .nav-sidebar {
            padding-bottom: 20px;
            min-height: 100%;
        }

        </style>
        @stack('css')
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="hold-transition sidebar-expanded layout-fixed">
        <div class="wrapper">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Right navbar links -->
                <ul class="navbar-nav ml-auto">
                    <!-- Navbar Search -->
                    <li class="nav-item">
                        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                            <i class="fas fa-search"></i>
                        </a>
                        <div class="navbar-search-block">
                            <form class="form-inline">
                                <div class="input-group input-group-sm">
                                    <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                    <div class="input-group-append">
                                        <button class="btn btn-navbar" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>

                    <!-- Messages Dropdown Menu -->

                    <!-- Notifications Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-bell"></i>
                            <span class="badge badge-warning navbar-badge">15</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-envelope mr-2"></i> 4 new messages
                                <span class="float-right text-muted text-sm">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-users mr-2"></i> 8 friend requests
                                <span class="float-right text-muted text-sm">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-file mr-2"></i> 3 new reports
                                <span class="float-right text-muted text-sm">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                            <i class="fas fa-th-large"></i>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Trazabilidad</span>
    </a>
    
    <!-- Sidebar user panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
            <a href="#" class="d-block">
                @auth
                    {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                @else
                    Usuario
                @endauth
            </a>
        </div>
    </div>
    
    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- PANELES DE CONTROL -->
                @can('ver panel control')
                <li class="nav-header">Paneles de Control</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Panel de Control</p>
                    </a>
                </li>
                @endcan
                @can('ver panel cliente')
                @if(!Auth::user()->can('ver panel control'))
                <li class="nav-header">Paneles de Control</li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('dashboard-cliente') }}" class="nav-link">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Panel del Cliente</p>
                    </a>
                </li>
                @endcan
                <!-- PEDIDOS -->
                @canany(['crear pedidos', 'ver mis pedidos', 'gestionar pedidos'])
                <li class="nav-header">Pedidos</li>
                @can('ver mis pedidos')
                <li class="nav-item">
                    <a href="{{ route('mis-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>Mis Pedidos</p>
                    </a>
                </li>
                @endcan
                @can('gestionar pedidos')
                <li class="nav-item">
                    <a href="{{ route('gestion-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-list"></i>
                        <p>Gestión de Pedidos</p>
                    </a>
                </li>
                @endcan
                @can('gestionar pedidos')
                <li class="nav-item">
                    <a href="{{ route('rutas-tiempo-real') }}" class="nav-link">
                        <i class="nav-icon fas fa-route"></i>
                        <p>Seguimiento de Pedidos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('documentacion-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Documentación de Pedidos</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- MATERIA PRIMA -->
                @canany(['ver materia prima', 'solicitar materia prima', 'recepcionar materia prima', 'gestionar proveedores'])
                <li class="nav-header">Materia Prima</li>
                @can('ver materia prima')
                <li class="nav-item">
                    <a href="{{ route('materia-prima-base') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Materias Prima Base</p>
                    </a>
                </li>
                @endcan
                @can('solicitar materia prima')
                <li class="nav-item">
                    <a href="{{ route('solicitar-materia-prima') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Solicitar Materias Prima</p>
                    </a>
                </li>
                @endcan
                @can('recepcionar materia prima')
                <li class="nav-item">
                    <a href="{{ route('recepcion-materia-prima') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Recepcion Materias Prima</p>
                    </a>
                </li>
                @endcan
                @can('gestionar proveedores')
                <li class="nav-item">
                    <a href="{{ route('proveedores.web.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Proveedores</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- LOTES -->
                @can('gestionar lotes')
                <li class="nav-header">Lotes</li>
                <li class="nav-item">
                    <a href="{{ route('gestion-lotes') }}" class="nav-link">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Lotes</p>
                    </a>
                </li>
                @endcan
                <!-- PROCESOS -->
                @canany(['gestionar maquinas', 'gestionar procesos', 'gestionar variables estandar'])
                <li class="nav-header">Procesos</li>
                @can('gestionar maquinas')
                <li class="nav-item has-treeview">
                    <a href="{{ route('maquinas.index') }}" class="nav-link">
                      <i class="nav-icon fas fa-layer-group"></i>
                        Maquinas
                    </a>
                </li>
                @endcan
                @can('gestionar procesos')
                <li class="nav-item">
                    <a href="{{ route('procesos.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Procesos</p>
                    </a>
                </li>
                @endcan
                @can('gestionar variables estandar')
                <li class="nav-item">
                    <a href="{{ route('variables-estandar') }}" class="nav-link">
                        <i class="nav-icon fas fa-sliders-h"></i>
                        <p>Variables Estandar</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- CERTIFICACIONES -->
                @canany(['certificar lotes', 'ver certificados'])
                <li class="nav-header">Certificaciones</li>
                @can('certificar lotes')
                <li class="nav-item">
                    <a href="{{ route('certificar-lote') }}" class="nav-link">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>Certificar Lote</p>
                    </a>
                </li>
                @endcan
                @can('ver certificados')
                <li class="nav-item">
                    <a href="{{ route('certificados') }}" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>Certificados</p>
                    </a>
                </li>
                @endcan
                @endcanany
                <!-- ALMACENES -->
                @can('almacenar lotes')
                <li class="nav-header">Almacenes</li>
                <li class="nav-item">
                    <a href="{{ route('almacenaje') }}" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>Almacenar lotes</p>
                    </a>
                </li>
                @endcan
                <!-- ADMINISTRACIÓN -->
                @can('gestionar usuarios')
                <li class="nav-header">Administración</li>
                <li class="nav-item">
                    <a href="{{ route('planta-ubicacion') }}" class="nav-link">
                        <i class="nav-icon fas fa-map-marker-alt"></i>
                        <p>Mi Ubicación</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('usuarios') }}" class="nav-link">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Usuarios</p>
                    </a>
                </li>
                @endcan
            </ul>
        </nav>
        
        <!-- CERRAR SESIÓN - Siempre visible al final del sidebar -->
        <div class="sidebar-footer" style="padding: 10px; border-top: 1px solid rgba(255,255,255,.1);">
            <form method="POST" action="{{ route('logout') }}" class="w-100">
                @csrf
                <button type="submit" class="btn btn-block btn-danger btn-sm" style="cursor: pointer;">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</aside>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0 text-sm">@yield('page_title')</h1>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>
                </section>
            </div>

            <!-- Footer -->
            <footer class="main-footer">
                <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
                All rights reserved.
                <div class="float-right d-none d-sm-inline-block">
                    <b>Version</b> 3.2.0
                </div>
            </footer>
        </div>

        <!-- jQuery, Bootstrap 4, and AdminLTE JS (CDN) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

        <!-- Sidebar State Management -->
        <script>
        $(document).ready(function() {
            // Detectar página actual y marcar como activa
            setActiveMenuItem();
        });

        function setActiveMenuItem() {
            var currentPath = window.location.pathname;

            // Remover todas las clases active existentes
            $('.nav-link').removeClass('active');
            $('.nav-item').removeClass('menu-open');

            // Buscar el enlace que coincida con la ruta actual
            $('.nav-link[href]').each(function() {
                var linkHref = $(this).attr('href');

                if (linkHref) {
                    // Obtener la pathname del enlace para comparación precisa
                    var linkPathname = new URL(linkHref, window.location.origin).pathname;

                    // Comparar rutas exactas
                    if (linkPathname === currentPath) {
                        $(this).addClass('active');

                        // Si es un submenú, marcar el menú padre como activo también
                        var $parentMenu = $(this).closest('li.nav-item.has-treeview');
                        if ($parentMenu.length > 0) {
                            $parentMenu.find('> a').addClass('active');
                            $parentMenu.addClass('menu-open');
                        }
                    }
                }
            });
        }
        </script>
        @stack('scripts')
        @stack('js')
    </body>
    </html>





