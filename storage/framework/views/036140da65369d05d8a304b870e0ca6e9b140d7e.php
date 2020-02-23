<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 mt-5">
            <div class="error-page mt-5">
            <h2 class="headline green mt-5"> 404</h2>
    
            <div class="error-content mt-5">
                <h3><i class="fa fa-warning text-warning"></i> Oops! Page not found.</h3>
    
                <p>
                We could not find the page you were looking for.
                Meanwhile, you may <a href="/">return to home page</a>
                </p>
    
            </div>
            <!-- /.error-content -->
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>