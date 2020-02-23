<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/** Socialite */
Route::get('/auth/redirect/{provider}/{org?}', 'Auth\RegisterController@redirect')->name('auth.redirect');
Route::get('/auth/callback/{provider}', 'Auth\RegisterController@callback');

Auth::routes();
Route::get('/logout', 'PageController@logout')->name('logout');
Route::get('/logout-farm-worker', 'PageController@logoutFarmWorker')->name('logout-farm-worker');
Route::get('/login/clientele/{code?}', 'PageController@login')->name('login.client');
Route::get('/forgot/clientele/{code?}', 'PageController@forgot')->name('forgot.client');
Route::get('/register/clientele/{code?}', 'PageController@register')->name('register.client');

Route::get('/login-farm-worker', 'PageController@loginFarmWorker')->name('login.farm.worker');
Route::post('/login-farm-worker', 'Auth\FarmWorkerLoginController@doLoginFarmWorker')->name('dologin.farm.worker');

Route::get('/', 'PageController@index2')->name('welcome');
Route::get('/indexlandingpagev3', 'PageController@index3')->name('welcome3');
Route::get('/shop', 'ShopController@index')->name('shop');
Route::get('/seller', 'ShopController@index')->name('seller');
Route::get('/store/{name}', 'ShopController@index')->name('store');
Route::get('/product/{slug}', 'ShopController@product')->name('product-view');
Route::get('/product/{slug}/invalid', 'ShopController@productNotFound')->name('product.notfound');

/** Special Clientele */
Route::get('/espclientele/{name}/{slug?}', 'CLIENTELE\ClientelePageController@index')->name('clientele');
// Route::get('/consortium', 'ShopController@consortium')->name('consortium');
Route::get('/consortium', 'ShopController@index')->name('consortium');

// Route::get('/single-product', 'PageController@singleproduct')->name('singlepproduct');

/* Dragonpay */
Route::get('dragonpay/return', 'DragonpayController@return'); // Live
Route::post('dragonpay/handle', 'DragonpayController@handle'); // Live
// Route::post('dragonpay/handle', 'DragonpayController@handle')->name('dragonpaytest'); // Live
// Route::get('dragonpay/test', 'DragonpayController@test'); // Dev testing

// Route::get('/home', 'HomeController@index')->name('home');
// Route::get('/v2', 'PageController@index2')->name('index2');

Route::get('/poform-clientele', 'ShopController@poformclientele')->name('poform.client');

Route::get('/about', 'PageController@about')->name('about');
Route::get('/privacy', 'PageController@privacy')->name('privacy');
Route::get('/term-and-condition', 'PageController@termAndCondition')->name('term-and-condition');
Route::get('/contact-us', 'PageController@contactUs')->name('contact-us');


/** QRPAY */
//Route::get('qrpay', 'QRPayController@index'); // Live

// Route::get('{path}', 'HomeController@index')->where('path', '([A-z\d-\/_.]+)?');
$regex = '([A-Za-z0-9-/+]+)';

Route::group(['middleware' => 'auth'], function () use ($regex) {
    Route::get('/change-password', 'ProfileController@changePassword')->name('change-password');
    Route::post('/change-password-request', 'ProfileController@changePasswordRequest')->name('change-password-request');
    

    /** Modules */

    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/home-farm-worker', 'HomeController@indexFarmWorker')->name('home-farm-worker');

    Route::get('/dashboard', 'HomeController@modules')->name('dashboard');
    Route::get('/myorderallocation', 'HomeController@modules')->name('myorderallocation');
    Route::get('/purchase-order', 'HomeController@modules')->name('purchase-order');
    Route::get('/customers', 'HomeController@modules')->name('customers');
    Route::get('/myproducts', 'HomeController@modules')->name('myproducts');
    Route::get('/productionmasterlist', 'HomeController@modules')->name('productionmasterlist');
    Route::get('/production-guide', 'HomeController@modules')->name('production.guide');
    Route::get('/production-guidelines/{id}', 'ProductionGuidelinesController@index')->name('production.guidelines');
    Route::put('/production-guidelines/{id}', 'ProductionGuidelinesController@update')->name('production.guidelines.update');
    Route::put('/production-matrix/{id}', 'ProductionGuidelinesController@updateMatrix')->name('production.matrix.update');
    Route::get('/mypo', 'HomeController@modules')->name('mypo');
    Route::get('/profile', 'HomeController@modules')->name('profile');
    Route::get('/myorder', 'HomeController@modules')->name('myorder');
    Route::get('/mypreorder', 'HomeController@modules')->name('mypreorder');
    Route::get('/members', 'HomeController@modules')->name('members');
    Route::get('/community-products', 'HomeController@modules')->name('community-products');
    Route::get('/myproductionlist', 'HomeController@modules')->name('myproductionlist');
    Route::get('/client-products', 'HomeController@modules')->name('client-products');
    Route::get('/registry-form', 'HomeController@modules')->name('registry-form');
    Route::get('/registry-form/create', 'HomeController@modules')->name('registry-form.create');
    Route::get('/registry-form/{id}/view', 'HomeController@checkRegistryFormView')->name('registry-form-view');
    Route::get('/farm-worker', 'HomeController@modules')->name('farm-worker');

    Route::get('/member-crop-dashboard', 'MemberCropDashboardController@index')->name('member.crop.dashboard');
    Route::get('/member-coop-crop-dashboard', 'MemberCropDashboardController@memberCoop')->name('member.coop.crop.dashboard');
    Route::get('/member-coop-rice-dashboard', 'MemberRiceDashboardController@memberCoop')->name('member.coop.rice.dashboard');

    Route::get('/rice-production', 'HomeController@modules')->name('rice-production');
    Route::get('/rice-production/{id}/view', 'HomeController@checkRiceProductionView')->name('rice-production-view');
    Route::get('/crop-production', 'HomeController@modules')->name('crop-production');
    Route::get('/crop-production/{id}/view', 'HomeController@checkCropProductionView')->name('crop-production-view');
    Route::get('/egg-production', 'HomeController@modules')->name('egg-production');
    Route::get('/egg-production/{id}/view', 'HomeController@checkEggProductionView')->name('egg-production-view');
    Route::get('/broiler-production', 'HomeController@modules')->name('broiler-production');
    Route::get('/broiler-production/{id}/view', 'HomeController@checkBroilerProductionView')->name('broiler-production-view');
    Route::get('/pigfattener-production', 'HomeController@modules')->name('pigfattener-production');
    Route::get('/pigfattener-production/{id}/view', 'HomeController@checkPigFattenerProductionView')->name('pigfattener-production-view');
    Route::get('/fruit-production', 'HomeController@modules')->name('fruit-production');
    Route::get('/fruit-production/{id}/view', 'HomeController@checkFruitProductionView')->name('fruit-production-view');

    Route::get('/report-po', 'Report\POController@index')->name('report-po');
    Route::get('/report-po/generate', 'Report\POController@generate')->name('report-po.generate');
    Route::get('/report-po/pdf', 'Report\POController@pdf')->name('report-po.pdf');
    Route::get('/report-po/excel', 'Report\POController@excel')->name('report-po.excel');

    Route::get('/report-mypo', 'Report\MyPOController@index')->name('report-mypo');
    Route::get('/report-mypo/generate', 'Report\MyPOController@generate')->name('report-mypo.generate');
    Route::get('/report-mypo/pdf', 'Report\MyPOController@pdf')->name('report-mypo.pdf');
    Route::get('/report-mypo/excel', 'Report\MyPOController@excel')->name('report-mypo.excel');

    Route::get('/report-registry', 'Report\RegistryController@index')->name('report-registry');
    Route::get('/report-registry/generate', 'Report\RegistryController@generate')->name('report-registry.generate');
    // Route::get('/report-po/pdf', 'Report\POController@pdf')->name('report-po.pdf');
    Route::get('/report-registry/excel', 'Report\RegistryController@excel')->name('report-registry.excel');
    
    Route::get('/order-allocation', 'HomeController@open')->name('order-allocation');
    Route::get('/order-allocation/{id}/view', 'HomeController@open')->name('order-allocation-view');
    Route::get('/order-fulfillment/{id}', 'HomeController@open')->name('order-fulfillment');
    Route::get('/order-monitoring/{id}', 'HomeController@open')->name('order-monitoring');
    

    Route::get('/poform', 'ShopController@index')->name('poform');
    //Route::get('/poform-clientele', 'ShopController@poformclientele')->name('poform.client');
    
    /** Old Route */
    Route::get('/orders', 'HomeController@orders')->name('orders');
    Route::get('/preorders', 'HomeController@orders')->name('preorders');
    Route::get('/shop/checkout', 'ShopController@checkout')->name('checkout');
    Route::get('/shop/precheckout', 'ShopController@precheckout')->name('precheckout');
    Route::get('/shop/qrpayment/{crypt?}', 'HomeController@qrpayment')->name('qrpayment');
    Route::get('/shop/bankdeposit/{crypt?}', 'HomeController@bankdeposit')->name('bankdeposit');
    Route::get('/payment/qrpayment/{id}', 'HomeController@qrpaymentConvert')->name('payment.qrpayment');
    Route::get('/payment/bankdeposit/{id}', 'HomeController@bankDepositConvert')->name('payment.bankdeposit');
    Route::get('/payment/bankdeposit/view/{id}', 'HomeController@bankDepositView')->name('payment.bankdepositview');
    Route::post('/payment/final', 'API\SHOP\PaymentController@final')->name('payment.final');
    Route::get('/waybill/{order}/{type?}', 'WaybillController@order')->name('waybill');
    Route::get('/prewaybill/{preorder}/{type?}', 'WaybillController@preorder')->name('prewaybill');


    /** FORMS PDF */
    Route::get('/report-mypo/pdf/{id}/form', 'Report\MyPOController@singlepdf')->name('report-mypo.singlepdf');
    Route::get('/report-po/pdf/{id}/form', 'Report\POController@singlepdf')->name('report-po.singlepdf');
    Route::get('/report-po/pdf/{id}/formallocation', 'Report\POController@singlepdfallocation')->name('report-po.singlepdfallocation');

    /** Registry Form */
    //Route::get('/registry-form', 'Profiling\RegistryFormController@index')->name('registry-form');
    //Route::get('/registry-form/create', 'Profiling\RegistryFormController@create')->name('registry-form.create');
});
