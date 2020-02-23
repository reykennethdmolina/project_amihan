<?php $__env->startSection('content'); ?>
<style>
.navbar,.footer{display:none;}
</style>


<div class="agr-login">
    <div class="sct brand"><a href="/"><img src="<?php echo e(asset('img/agronegosyo.png')); ?>" alt="agro logo"></a></div>
    <div class="sct login">
        <form method="POST" action="<?php echo e(route('login')); ?>" aria-label="<?php echo e(__('Login')); ?>">
            <?php echo csrf_field(); ?>
            <h4 class="text-center mb-4">Login to start your session</h4>
            
            <input id="email" type="email" class="form-control<?php echo e($errors->has('email') ? ' is-invalid' : ''); ?>" name="email" value="<?php echo e(old('email')); ?>" required placeholder="Email">
            
            <input id="password" type="password" class="form-control<?php echo e($errors->has('password') ? ' is-invalid' : ''); ?>" name="password" required placeholder="Password">
            
            <?php if($errors->has('email')): ?>
                <span class="invalid-feedback" role="alert">
                    <strong><?php echo e($errors->first('email')); ?></strong>
                </span>
            <?php endif; ?>

            <div class="forgot-remember">
                    <label class="control control-checkbox">
                        Remember me
                        <input type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                        <div class="control_indicator"></div>
                    </label>
                <div class="forgot">
                        <a href="<?php echo e(route('password.request')); ?>">Forgot Password?</a>
                </div> 
            </div>
            <input type="submit" name="send" value="Login">
            <p class="text-center mb-0"><a href="<?php echo e(route('register')); ?>"><?php echo e(__('Register new account')); ?></a></p>
            <br>
            <p class="text-center mb-0">Connect using<br><i class="fa fa-angle-down" aria-hidden="true"></i></p>
            <div class="social-sign">
                <a href="<?php echo e(route('auth.redirect', ['provider' => 'facebook', 'org' => ''])); ?>"><i class="fab fa-facebook" ></i></a>
                <!-- <a href="https://www.instagram.com/agronegosyo"><i class="fab fa-instagram" ></i></a> -->
            </div>
            <p class="text-center mt-3">
                <a class="text-warning pull-center text-center" href="<?php echo e(route('login.farm.worker')); ?>">Switch to Worker Login</a>
            </p>
        </form>
    </div> 
</div> 
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>