<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>Welcome - Agronegosyo Market Place</title>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({
        google_ad_client: "ca-pub-2684224008431001",
        enable_page_level_ads: true
    });
    </script>


    <!-- Scripts -->
    
    <link href="<?php echo e(asset('css/landing.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('css/glider.min.css')); ?>" rel="stylesheet" type="text/css">
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119539035-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-119539035-1');
    </script>
    <style>
        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>

    <!-- Styles -->
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel agr-fxd-nav">
            <div class="container">
                <div class="d-lg-block d-xl-block agr-shop-blck">
                <a href="/" class="brand-link agr-shop-logo-hldr">
                    <img src="<?php echo e(asset('img/agronegosyo.png')); ?>" alt="Agronegosyo Logo" class="img-fluid">
                </a>
                </div>
                <button class="navbar-toggler mt-2 agr-cstm-tgglr" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?php echo e(__('Toggle navigation')); ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        <?php if(auth()->guard()->guest()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('login')); ?>"><i class="fas"></i><?php echo e(__('Login')); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('register')); ?>"><?php echo e(__('Register')); ?></a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" title="<?php echo e(Auth::user()->firstname); ?> <?php echo e(Auth::user()->lastname); ?>" class="nav-link dropdown-toggle text-capitalize" style="min-width:200px;" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-user fa-fw"></i><?php if(session('worker_status')): ?> <?php echo e(str_limit(session('workername'), 10)); ?> <?php else: ?>  <?php echo e(str_limit(Auth::user()->firstname.' '.Auth::user()->lastname, 10)); ?> <?php endif; ?><span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="<?php echo e(route('home')); ?>">
                                            <?php echo e(__('Home')); ?>

                                    </a>
                                    <a class="dropdown-item" href="/logout">
                                        <?php echo e(__('Logout')); ?>

                                    </a>
                                    
                                </div>
                            </li>
                        <?php endif; ?>
                        
                    </ul>

                </div>
                
                <?php if(@Auth::user()->org == ''): ?>
                <a href="/poform" class="arg-float-shop" style="top:0;text-decoration:none;color:#fff">
                    
                    <i class="fa fa-cart-plus fa-2x my-order">
                        <span class="badge badge-dark" id="totalCart" style="font-size: 14px;margin-top:-10px;margin-left:-10px;position:fixed"></span>
                    </i>
                </a>
                <?php else: ?>
                <a href="/poform-clientele" class="arg-float-shop" style="top:100px;text-decoration:none; color:#fff">
                    <i class="fa fa-cart-plus fa-2x my-order">
                        <span class="badge badge-dark" id="totalCart" style="font-size: 14px;margin-top:-10px;margin-left:-10px;position:fixed"></span>
                    </i>
                </a>
                <?php endif; ?>
            </div>
        </nav>
        
        <main class="py-4" style="height:100%;">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <footer class="footer bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 h-100 text-center text-lg-left my-auto">
                        <ul class="list-inline mb-2">
                        <li class="list-inline-item">
                            <a href="<?php echo e(route('about')); ?>">About</a>
                        </li>
                        <li class="list-inline-item">&sdot;</li>
                        <li class="list-inline-item">
                            <a href="<?php echo e(route('privacy')); ?>">Privacy</a>
                        </li>
                        <li class="list-inline-item">&sdot;</li>
                        <li class="list-inline-item">
                            <a href="<?php echo e(route('term-and-condition')); ?>">Terms & Condition</a>
                        </li>
                        <li class="list-inline-item">&sdot;</li>
                        <li class="list-inline-item">
                            <a href="<?php echo e(route('contact-us')); ?>">Contact Us</a>
                        </li>
                        </ul>
                        <p class="text-muted small mb-4 mb-lg-0">&copy; Agronegosyo Technologies Inc. 2019. All Rights Reserved.</p>
                    </div>
                    <div class="col-lg-6 h-100 text-center text-lg-right my-auto">
                        <ul class="list-inline mb-0">
                        <li class="list-inline-item mr-3">
                            <a href="https://www.facebook.com/agronegosyo" target="_blank">
                            <i class="fab fa-facebook fa-2x fa-fw"></i>
                            </a>
                        </li>
                        
                        <li class="list-inline-item">
                            <a href="https://www.instagram.com/agronegosyo" target="_blank">
                            <i class="fab fa-instagram fa-2x fa-fw"></i>
                            </a>
                        </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
        
    </div>

    <script src="<?php echo e(asset('js/jquery-2.2.js')); ?>"></script>
    <script src="<?php echo e(asset('js/bootstrap.js')); ?>"></script>
    <script>
        let total = localStorage.getItem('total-storage');
        if (total != 0) {
            document.getElementById("totalCart").innerHTML = total;
        }
        // let totalorder = localStorage.getItem('totalorder-storage');
        // if (totalorder != 0) {
        //     document.getElementById("totalOrderCart").innerHTML = totalorder;
        // }
    </script>
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
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>