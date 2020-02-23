<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

  <title>Agronegosyo - Admin</title>
  <!-- Styles -->
  <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDMdF1qaV6tbUJjAC_WT2neYrD4CIyjK8M" ></script>  
  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119539035-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-119539035-1');
  </script>

</head>
<body class="hold-transition sidebar-mini" style="height: auto;">
<div class="wrapper" id="app">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand bg-white navbar-light border-bottom">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" id="pushmenu" href="#"><i class="fa fa-bars"></i></a>
      </li>
    </ul>

    <!-- SEARCH FORM -->
    <div class="input-group input-group-sm">
      <input class="form-control form-control-navbar" v-model="search" @keyup="searchData" type="search" placeholder="Search" aria-label="Search">
      <div class="input-group-append">
        <button class="btn btn-navbar" @click="searchData">
          <i class="fa fa-search"></i>
        </button>
      </div>
    </div>

  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4" style="min-height: 249px;">
    <!-- Brand Logo -->
    <div class="d-sm-block d-md-block mt-5">
    </div>
    <?php if(Auth::user()->org != ''): ?>
    <a href="/espclientele/<?php echo e(Auth::user()->org); ?>" class="brand-link">
      <img src="<?php echo e(asset('img/logo.png')); ?>" alt="B2B Agri Market Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light"><small>Agronegosyo</small></span>
    </a>
    <?php else: ?>
    <a href="/" class="brand-link">
      <img src="<?php echo e(asset('img/logo.png')); ?>" alt="B2B Agri Market Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light"><small>Agronegosyo</small></span>
    </a>
    <?php endif; ?>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?php echo e(asset('/img/profile/')); ?>/<?php echo e(Auth::user()->photo); ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="/home" class="d-block text-capitalize"><?php if(session('worker_status')): ?> <?php echo e(session('workername')); ?> <?php else: ?> <?php echo e(Auth::user()->firstname); ?> <?php echo e(Auth::user()->lastname); ?> <?php endif; ?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2" style="font-size:12px;">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->

          <?php $__currentLoopData = Session::get('usr_modules'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm => $modules): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li class="nav-item has-treeview menu-close">
            <a href="#" class="nav-link">
              <i class="nav-icon <?php echo e(@$modules[0]['main_icon']); ?>"></i>
              <p>
                <?php echo e($perm); ?> 
                <i class="right fa fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">

            <?php $__currentLoopData = $modules->sortBy('sort'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="nav-item ml-2">
                  <a href="/<?php echo e(@$mod['code']); ?>" class="nav-link">
                    <i class="fas <?php echo e(@$mod['icon']); ?> nav-icon teal"></i>
                    <p><?php echo e(@$mod['name']); ?></p>
                  </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($perm == 'sales'): ?>
              <?php if(Auth::user()->show == 1): ?>
              <li class="nav-item ml-2">
                <a href="/store/<?php echo e(Auth::user()->profile->business_name_slug); ?>" class="nav-link" target="_blank">
                  <i class="fas fa-store nav-icon teal"></i>
                  <p>Store Front</p>
                </a>
              </li>
              <?php endif; ?>
            <?php endif; ?>
            </ul>
          </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          
          <li class="nav-item">
            <a class="nav-link" href="/logout">
              <i class="nav-icon fas fa-power-off"></i>
              <p>
                Logout
              </p>
            </a>
          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="min-height: 610px;">
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <router-view></router-view>
        <vue-progress-bar></vue-progress-bar>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      Terms & Condition
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2019 <a href="https://agronegosyo.com">Agronegosyo Technologies Inc.</a>.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<?php if(auth()->guard()->check()): ?>
<script>
    window.user = <?php echo json_encode(auth()->user(), 15, 512) ?>;
</script>
<?php endif; ?>

<!-- Scripts -->
<script src="<?php echo e(asset('js/app-admin.js')); ?>"></script>
<!-- JSON-LD markup generated by Google Structured Data Markup Helper. -->
<script type="application/ld+json">
  {
      "@context" : "http://schema.org",
      "@type" : "LocalBusiness",  
      "name" : "Agronegosyo",
      "email" : "agronegosyo@gmail.com",
      "address" : {
      "@type" : "PostalAddress",
      "addressCountry" : "Philippines"
      },
      "url" : "https://www.agronegosyo.com/"
  }
  </script>
  <!-- /JavaScript
  ================================================== -->
</body>
</html>
