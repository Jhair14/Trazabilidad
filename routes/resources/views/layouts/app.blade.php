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
        </style>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="hold-transition sidebar-mini layout-fixed">
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
    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- INICIO -->
                <li class="nav-header">Inicio</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Panel de Control</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dashboard-cliente') }}" class="nav-link">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Panel del Cliente</p>
                    </a>
                </li>
                <!-- MATERIA PRIMA -->
                <li class="nav-header">Materia Prima</li>
                <li class="nav-item">
                    <a href="{{ route('materia-prima-base') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Materias Prima Base</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('solicitar-materia-prima') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Solicitar Materias Prima</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('recepcion-materia-prima') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Recepcion Materias Prima</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('proveedores.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Proveedores</p>
                    </a>
                </li>
                <!-- LOTE -->
                <li class="nav-header">Lote</li>
                <li class="nav-item">
                    <a href="{{ route('gestion-lotes') }}" class="nav-link">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Lotes</p>
                    </a>
                </li>
                <!-- PROCESOS -->
                <li class="nav-header">Procesos</li>
                <li class="nav-item has-treeview">
                    <a href="{{ route('maquinas.index') }}" class="nav-link">
                      <i class="nav-icon fas fa-layer-group"></i>
                        Maquinas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('procesos.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-cube"></i>
                        <p>Procesos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('variables-estandar') }}" class="nav-link">
                        <i class="nav-icon fas fa-sliders-h"></i>
                        <p>Variables Estandar</p>
                    </a>
                </li>
                <!-- CERTIFICACIÓN -->
                <li class="nav-header">Certificación</li>
                <li class="nav-item">
                    <a href="{{ route('certificar-lote') }}" class="nav-link">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>Certificar Lote</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('certificados') }}" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>Certificados</p>
                    </a>
                </li>
                <!-- ALMACEN -->
                <li class="nav-header">Almacen</li>
                <li class="nav-item">
                    <a href="{{ route('almacenaje') }}" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>Almacenar lotes</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('lotes-almacenados') }}" class="nav-link">
                        <i class="nav-icon fas fa-list"></i>
                        <p>Lotes almacenados</p>
                    </a>
                </li>
                <!-- PEDIDOS -->
                <li class="nav-header">Pedidos</li>
                <li class="nav-item">
                    <a href="{{ route('mis-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>Mis Pedidos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('gestion-pedidos') }}" class="nav-link">
                        <i class="nav-icon fas fa-list"></i>
                        <p>Gestión de Pedidos</p>
                    </a>
                </li>
                <!-- ADMINISTRACIÓN -->
                <li class="nav-header">Administración</li>
                <li class="nav-item">
                    <a href="{{ route('usuarios') }}" class="nav-link">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Usuarios</p>
                    </a>
                </li>
                <!-- CERRAR SESIÓN -->
                <li class="nav-item mt-3">
                    <form method="POST" action="{{ route('logout') }}" class="w-100">
                        @csrf
                        <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-left" style="cursor: pointer; padding: 0.5rem 1rem;">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p style="display: inline-block; margin: 0;">Cerrar sesión</p>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
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
    </body>
    </html>





