<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::apiResources([
//     'member' => 'API\MemberController'
// ]);

Route::group(['middleware' => 'api'], function () {

    /** Dashboard API */
    Route::get('dashboard/latestPO', 'API\DashboardController@latestPO');
    Route::get('dashboard/latestDuePO', 'API\DashboardController@latestDuePO');
    Route::get('dashboard/latestProduceDuePO', 'API\DashboardController@latestProduceDuePO');
    Route::get('dashboard/latestAllocationProduceDuePO', 'API\DashboardController@latestAllocationProduceDuePO');
    Route::get('dashboard/latestProduct', 'API\DashboardController@latestProduct');
    Route::get('dashboard/figures', 'API\DashboardController@figures');

    /** User-Profile API */
    Route::apiResources(['member' => 'API\MemberController']);
    Route::get('findMember', 'API\MemberController@search');
    Route::post('addExistingMember', 'API\MemberController@addExistingMember');
    Route::get('profile', 'API\ProfileController@profile');
    Route::put('profile', 'API\ProfileController@updateProfile');
    Route::put('profileBusiness', 'API\ProfileController@updateProfileBusiness');
    Route::put('profileAddress', 'API\ProfileController@updateProfileAddress');
    Route::put('profileSetting', 'API\ProfileController@updateProfileSetting');
    Route::get('farmtypo', 'API\MemberController@farmtypo');
    
    /** Product API */
    Route::apiResources(['myproduct' => 'API\MyProductController']);
    Route::put('myproduct/post/{myproduct}', 'API\MyProductController@post');
    Route::put('myproduct/hide/{myproduct}', 'API\MyProductController@hide');
    // Route::put('myproduct/guidelines/{myproduct}', 'API\MyProductController@guidelines');
    Route::get('findMyProduct', 'API\MyProductController@search');
    Route::get('listOfProduct', 'API\MyProductController@listOfProduct');
    Route::get('listOfCrops', 'API\MyProductController@listOfCrops');
    Route::get('listOfFruits', 'API\MyProductController@listOfFruits');

    /** Carting API */
    Route::post('shop/initOrderTransaction', 'API\SHOP\ProductController@initOrderTransaction');
    Route::post('shop/initPreOrderTransaction', 'API\SHOP\ProductController@initPreOrderTransaction');
    Route::post('shop/initPOTransaction', 'API\SHOP\ProductController@initPOTransaction');

    /** Shipping API */
    Route::get('getShippingCost', 'API\ShippingController@shippingCost');

    /** Payment API */
    Route::post('payment', 'API\SHOP\PaymentController@store');
    Route::post('paymentcod', 'API\SHOP\PaymentController@storeCOD');

    /** Orders API */
    Route::apiResources(['order' => 'API\OrderController']);
    Route::get('findOrder', 'API\OrderController@search');
    Route::get('thisOrder/{order}', 'API\OrderController@thisOrder');
    Route::put('order/setReadyForPickup/{order}', 'API\OrderController@setReadyForPickup');
    Route::put('order/QRSPackageDetail/{order}', 'API\OrderController@QRSPackageDetail');
    Route::put('order/tagAsDelivered/{order}', 'API\OrderController@tagAsDelivered');
    Route::put('order/readyFor/{order}', 'API\OrderController@readyFor');
    Route::put('order/cancel/{order}', 'API\OrderController@cancel');

    /** Pre Orders API */
    Route::apiResources(['preorder' => 'API\PreOrderController']);
    Route::put('preorder/cancel/{preorder}', 'API\PreOrderController@cancel');
    Route::put('preorder/accept/{preorder}', 'API\PreOrderController@accept');
    Route::get('findPreOrder', 'API\PreOrderController@search');
    Route::get('thisPreOrder/{preorder}', 'API\PreOrderController@thisPreOrder');
    Route::put('preorder/setReadyForPickup/{preorder}', 'API\PreOrderController@setReadyForPickup');
    Route::put('preorder/QRSPackageDetail/{preorder}', 'API\PreOrderController@QRSPackageDetail');
    Route::put('preorder/tagAsDelivered/{preorder}', 'API\PreOrderController@tagAsDelivered');
    Route::put('preorder/readyFor/{preorder}', 'API\PreOrderController@readyFor');

    /** My Orders API */
    Route::apiResources(['myorder' => 'API\MyOrderController']);
    Route::get('findMyOrder', 'API\MyOrderController@search');
    Route::put('myorder/cancel/{mypreorder}', 'API\MyOrderController@cancel');

    /** My Pre Orders API */
    Route::apiResources(['mypreorder' => 'API\MyPreOrderController']);
    Route::get('findMyPreOrder', 'API\MyPreOrderController@search');
    Route::put('mypreorder/cancel/{mypreorder}', 'API\MyPreOrderController@cancel');
    Route::put('mypreorder/purchased/{mypreorder}', 'API\MyPreOrderController@purchased');

    /** Customer API */
    Route::apiResources(['customer' => 'API\CustomerController']);
    Route::get('findCustomer', 'API\CustomerController@search');
    Route::get('listOfCustomer', 'API\CustomerController@listOfCustomer');

    /** Purchase Order API */
    Route::apiResources(['purchaseOrder' => 'API\PurchaseOrderController']);
    Route::get('findPurchaseOrder', 'API\PurchaseOrderController@search');
    Route::post('po', 'API\PurchaseOrderController@po');
    Route::post('buyerPO', 'API\PurchaseOrderController@buyerPO');
    Route::put('buyerPO/{id}', 'API\PurchaseOrderController@updateBuyerPO');
    Route::post('purchaseOrder/multiAllocate', 'API\PurchaseOrderController@multiAllocate');
    Route::put('purchaseOrder/moveToAllocation/{purchaseOrder}', 'API\PurchaseOrderController@moveToAllocation');
    Route::put('purchaseOrder/processPO/{purchaseOrder}', 'API\PurchaseOrderController@processPO');
    Route::put('purchaseOrder/declined/{purchaseOrder}', 'API\PurchaseOrderController@declined');
    Route::put('purchaseOrder/moveToOrder/{purchaseOrder}', 'API\PurchaseOrderController@moveToOrder');
    Route::get('purchaseOrder/getQRPaymentInfo/{slug?}', 'API\PurchaseOrderController@getQRPaymentInfo');
    Route::get('purchaseOrder/getBankDepositInfo/{slug?}', 'API\PurchaseOrderController@getBankDepositInfo');
    Route::get('purchaseOrder/getBankDepositData/{id}', 'API\PurchaseOrderController@getBankDepositData');


    /** My Purchase Order API */
    Route::apiResources(['myPurchaseOrder' => 'API\MyPurchaseOrderController']);
    Route::get('myFindPurchaseOrder', 'API\MyPurchaseOrderController@search');

    /** Order Allocation API */
    Route::apiResources(['orderAllocation' => 'API\OrderAllocationController']);
    Route::get('findOrderAllocation', 'API\OrderAllocationController@search');
    Route::get('orderAllocation/po/{po}', 'API\OrderAllocationController@thisPO');
    Route::get('orderAllocation/fulfiller/{po}', 'API\OrderAllocationController@fulfiller');
    Route::get('getMembers', 'API\OrderAllocationController@getMembers');
    Route::get('getMemberInProduction/{product}', 'API\OrderAllocationController@getMemberInProduction');
    Route::post('allocateToMembers', 'API\OrderAllocationController@allocateToMembers');
    Route::put('saveAllocation/{po}', 'API\OrderAllocationController@saveAllocation');
    Route::put('removeAllocation/{po}', 'API\OrderAllocationController@removeAllocation');
    Route::put('orderAllocation/updateAddress/{po}', 'API\OrderAllocationController@updateAddress');

    /** My Order Allocation API */
    Route::apiResources(['myOrderAllocation' => 'API\MyOrderAllocationController']);
    Route::get('findMyOrderAllocation', 'API\MyOrderAllocationController@search');
    Route::put('myOrderAllocation/setStatus/{id}/{status}', 'API\MyOrderAllocationController@setStatus');


    /** Order Monitoring API */
    Route::apiResources(['orderFulfillment' => 'API\OrderFulfillmentController']);
    Route::put('orderFulfillment/saveFulfillment/{id}', 'API\OrderFulfillmentController@saveFulfillment');
    Route::put('orderFulfillment/tagAsComplete/{id}', 'API\OrderFulfillmentController@tagAsComplete');
    Route::post('orderFulfillment/multiTagAsComplete/', 'API\OrderFulfillmentController@multiTagAsComplete');

    /** Production Master List API */
    Route::apiResources(['productionMasterList' => 'API\ProductionMasterListController']);
    Route::get('memberProductionMasterList', 'API\ProductionMasterListController@memberProductionMasterList');
    Route::get('productionList', 'API\ProductionMasterListController@productionList');
    Route::post('assignMembers', 'API\ProductionMasterListController@assignMembers');
    Route::post('applyProduction', 'API\ProductionMasterListController@applyProduction');
    Route::put('assignMembers/status/{id}/{status}', 'API\ProductionMasterListController@statusAssignMembers');
    
    Route::apiResources(['proceed' => 'API\CartController']);

    /** Rice Production API */
    Route::apiResources(['riceProduction' => 'API\RiceProductionController']);
    Route::get('findRiceProduction', 'API\RiceProductionController@search');
    Route::put('riceProduction/activate/{id}', 'API\RiceProductionController@activate');
    Route::put('riceProduction/endSeason/{id}', 'API\RiceProductionController@endSeason');

    Route::post('riceProduction/activity/{id}', 'API\RiceProductionController@storeActivity');
    Route::get('riceProduction/activity/{id}', 'API\RiceProductionController@showActivity');
    Route::put('riceProduction/activity/{id}', 'API\RiceProductionController@updateActivity');
    Route::delete('riceProduction/activity/{id}', 'API\RiceProductionController@destroyActivity');

    Route::post('riceProduction/harvest/{id}', 'API\RiceProductionController@storeHarvest');
    Route::get('riceProduction/harvest/{id}', 'API\RiceProductionController@showHarvest');
    Route::put('riceProduction/harvest/{id}', 'API\RiceProductionController@updateHarvest');
    Route::delete('riceProduction/harvest/{id}', 'API\RiceProductionController@destroyHarvest');

    /** Crop Production API */
    Route::apiResources(['cropProduction' => 'API\CropProductionController']);
    Route::get('findCropProduction', 'API\CropProductionController@search');
    Route::put('cropProduction/activate/{id}', 'API\CropProductionController@activate');
    Route::put('cropProduction/endSeason/{id}', 'API\CropProductionController@endSeason');

    Route::post('cropProduction/harvest/{id}', 'API\CropProductionController@storeHarvest');
    Route::get('cropProduction/harvest/{id}', 'API\CropProductionController@showHarvest');
    Route::put('cropProduction/harvest/{id}', 'API\CropProductionController@updateHarvest');
    Route::delete('cropProduction/harvest/{id}', 'API\CropProductionController@destroyHarvest');

    Route::post('cropProduction/activity/{id}', 'API\CropProductionController@storeActivity');
    Route::get('cropProduction/activity/{id}', 'API\CropProductionController@showActivity');
    Route::put('cropProduction/activity/{id}', 'API\CropProductionController@updateActivity');
    Route::delete('cropProduction/activity/{id}', 'API\CropProductionController@destroyActivity');

    Route::post('cropProduction/allocation/{id}', 'API\CropProductionController@storeAllocation');
    Route::get('cropProduction/allocation/{id}', 'API\CropProductionController@showAllocation');
    Route::get('cropProduction/availableAllocation/{id}', 'API\CropProductionController@availableAllocation');
    Route::post('cropProduction/allocate', 'API\CropProductionController@allocate');
    Route::put('cropProduction/removeAllocation/{id}/{plot}', 'API\CropProductionController@removeAllocation');

    // Route::get('cropProduction/pos/{id}', 'API\CropProductionController@pos');
    // Route::get('cropProduction/posAl/{id}', 'API\CropProductionController@posAl');
    // Route::post('cropProduction/allocate', 'API\CropProductionController@allocate');
    // Route::put('cropProduction/removeAllocate/{id}/{plot}', 'API\CropProductionController@removeAllocate');
    // Route::post('cropProduction/activity/{id}', 'API\CropProductionController@saveActivity');
    // Route::get('cropProduction/activityLogs/{id}', 'API\CropProductionController@activityLogs');
    // Route::put('cropProduction/activity/{id}', 'API\CropProductionController@updateActivityLogs');
    // Route::put('cropProduction/endSeason/{id}', 'API\CropProductionController@endSeason');

    /** Egg Production API */
    Route::apiResources(['eggProduction' => 'API\EggProductionController']);
    Route::put('eggProduction/retireProgram/{id}', 'API\EggProductionController@retireProgram');
    Route::get('listOfStrains/{choice}', 'API\EggProductionController@listOfStrains');
    Route::get('findEggProduction', 'API\EggProductionController@search');
    Route::put('eggProduction/activate/{id}', 'API\EggProductionController@activate');
    Route::put('eggProduction/move/{id}', 'API\EggProductionController@move');

    Route::post('eggProduction/harvest/{id}', 'API\EggProductionController@storeHarvest');
    Route::get('eggProduction/harvest/{id}', 'API\EggProductionController@showHarvest');
    Route::put('eggProduction/harvest/{id}', 'API\EggProductionController@updateHarvest');
    Route::delete('eggProduction/harvest/{id}', 'API\EggProductionController@destroyHarvest');

    Route::get('eggProduction/lastFeedingRecord/{id}', 'API\EggProductionController@lastFeedingRecord');
    Route::post('eggProduction/feeding/{id}', 'API\EggProductionController@storeFeeding');
    Route::get('eggProduction/feeding/{id}', 'API\EggProductionController@showFeeding');
    Route::put('eggProduction/feeding/{id}', 'API\EggProductionController@updateFeeding');
    Route::delete('eggProduction/feeding/{id}', 'API\EggProductionController@destroyFeeding');

    Route::post('eggProduction/mortality/{id}', 'API\EggProductionController@storeMortality');
    Route::get('eggProduction/mortality/{id}', 'API\EggProductionController@showMortality');
    Route::put('eggProduction/mortality/{id}', 'API\EggProductionController@updateMortality');
    Route::delete('eggProduction/mortality/{id}', 'API\EggProductionController@destroyMortality');

    Route::post('eggProduction/vaccine/{id}', 'API\EggProductionController@storeVaccine');
    Route::get('eggProduction/vaccine/{id}', 'API\EggProductionController@showVaccine');
    Route::put('eggProduction/vaccine/{id}', 'API\EggProductionController@updateVaccine');
    Route::delete('eggProduction/vaccine/{id}', 'API\EggProductionController@destroyVaccine');

    /** Broiler Production API */
    Route::apiResources(['broilerProduction' => 'API\BroilerProductionController']);
    Route::put('broilerProduction/activate/{id}', 'API\BroilerProductionController@activate');
    Route::put('broilerProduction/move/{id}', 'API\BroilerProductionController@move');
    Route::put('broilerProduction/retireProgram/{id}', 'API\BroilerProductionController@retireProgram');
    Route::get('findBroilerProduction', 'API\BroilerProductionController@search');

    Route::post('broilerProduction/harvest/{id}', 'API\BroilerProductionController@storeHarvest');
    Route::get('broilerProduction/harvest/{id}', 'API\BroilerProductionController@showHarvest');
    Route::put('broilerProduction/harvest/{id}', 'API\BroilerProductionController@updateHarvest');
    Route::delete('broilerProduction/harvest/{id}', 'API\BroilerProductionController@destroyHarvest');

    Route::post('broilerProduction/monitor/{id}', 'API\BroilerProductionController@storeMonitor');
    Route::get('broilerProduction/monitor/{id}', 'API\BroilerProductionController@showMonitor');
    Route::put('broilerProduction/monitor/{id}', 'API\BroilerProductionController@updateMonitor');
    Route::delete('broilerProduction/monitor/{id}', 'API\BroilerProductionController@destroyMonitor');

    Route::get('broilerProduction/lastFeedingRecord/{id}', 'API\BroilerProductionController@lastFeedingRecord');
    Route::post('broilerProduction/feeding/{id}', 'API\BroilerProductionController@storeFeeding');
    Route::get('broilerProduction/feeding/{id}', 'API\BroilerProductionController@showFeeding');
    Route::put('broilerProduction/feeding/{id}', 'API\BroilerProductionController@updateFeeding');
    Route::delete('broilerProduction/feeding/{id}', 'API\BroilerProductionController@destroyFeeding');

    Route::post('broilerProduction/mortality/{id}', 'API\BroilerProductionController@storeMortality');
    Route::get('broilerProduction/mortality/{id}', 'API\BroilerProductionController@showMortality');
    Route::put('broilerProduction/mortality/{id}', 'API\BroilerProductionController@updateMortality');
    Route::delete('broilerProduction/mortality/{id}', 'API\BroilerProductionController@destroyMortality');

    Route::post('broilerProduction/vaccine/{id}', 'API\BroilerProductionController@storeVaccine');
    Route::get('broilerProduction/vaccine/{id}', 'API\BroilerProductionController@showVaccine');
    Route::put('broilerProduction/vaccine/{id}', 'API\BroilerProductionController@updateVaccine');
    Route::delete('broilerProduction/vaccine/{id}', 'API\BroilerProductionController@destroyVaccine');

    /** Broiler Production API */
    Route::apiResources(['pigFattenerProduction' => 'API\PigFattenerProductionController']);
    Route::put('pigFattenerProduction/activate/{id}', 'API\PigFattenerProductionController@activate');
    Route::put('pigFattenerProduction/move/{id}', 'API\PigFattenerProductionController@move');
    Route::put('pigFattenerProduction/retireProgram/{id}', 'API\PigFattenerProductionController@retireProgram');
    Route::get('findPigFattenerProduction', 'API\PigFattenerProductionController@search');

    Route::post('pigFattenerProduction/harvest/{id}', 'API\PigFattenerProductionController@storeHarvest');
    Route::get('pigFattenerProduction/harvest/{id}', 'API\PigFattenerProductionController@showHarvest');
    Route::put('pigFattenerProduction/harvest/{id}', 'API\PigFattenerProductionController@updateHarvest');
    Route::delete('pigFattenerProduction/harvest/{id}', 'API\PigFattenerProductionController@destroyHarvest');

    Route::post('pigFattenerProduction/monitor/{id}', 'API\PigFattenerProductionController@storeMonitor');
    Route::get('pigFattenerProduction/monitor/{id}', 'API\PigFattenerProductionController@showMonitor');
    Route::put('pigFattenerProduction/monitor/{id}', 'API\PigFattenerProductionController@updateMonitor');
    Route::delete('pigFattenerProduction/monitor/{id}', 'API\PigFattenerProductionController@destroyMonitor');

    Route::get('pigFattenerProduction/lastFeedingRecord/{id}', 'API\PigFattenerProductionController@lastFeedingRecord');
    Route::post('pigFattenerProduction/feeding/{id}', 'API\PigFattenerProductionController@storeFeeding');
    Route::get('pigFattenerProduction/feeding/{id}', 'API\PigFattenerProductionController@showFeeding');
    Route::put('pigFattenerProduction/feeding/{id}', 'API\PigFattenerProductionController@updateFeeding');
    Route::delete('pigFattenerProduction/feeding/{id}', 'API\PigFattenerProductionController@destroyFeeding');

    Route::post('pigFattenerProduction/mortality/{id}', 'API\PigFattenerProductionController@storeMortality');
    Route::get('pigFattenerProduction/mortality/{id}', 'API\PigFattenerProductionController@showMortality');
    Route::put('pigFattenerProduction/mortality/{id}', 'API\PigFattenerProductionController@updateMortality');
    Route::delete('pigFattenerProduction/mortality/{id}', 'API\PigFattenerProductionController@destroyMortality');

    Route::post('pigFattenerProduction/vaccine/{id}', 'API\PigFattenerProductionController@storeVaccine');
    Route::get('pigFattenerProduction/vaccine/{id}', 'API\PigFattenerProductionController@showVaccine');
    Route::put('pigFattenerProduction/vaccine/{id}', 'API\PigFattenerProductionController@updateVaccine');
    Route::delete('pigFattenerProduction/vaccine/{id}', 'API\PigFattenerProductionController@destroyVaccine');

    /** Fruit Production API */
    Route::apiResources(['fruitProduction' => 'API\FruitProductionController']);
    Route::get('findFruitProduction', 'API\FruitProductionController@search');
    Route::put('fruitProduction/activate/{id}', 'API\FruitProductionController@activate');
    Route::put('fruitProduction/endSeason/{id}', 'API\FruitProductionController@endSeason');

    Route::post('fruitProduction/harvest/{id}', 'API\FruitProductionController@storeHarvest');
    Route::get('fruitProduction/harvest/{id}', 'API\FruitProductionController@showHarvest');
    Route::put('fruitProduction/harvest/{id}', 'API\FruitProductionController@updateHarvest');
    Route::delete('fruitProduction/harvest/{id}', 'API\FruitProductionController@destroyHarvest');

    Route::post('fruitProduction/activity/{id}', 'API\FruitProductionController@storeActivity');
    Route::get('fruitProduction/activity/{id}', 'API\FruitProductionController@showActivity');
    Route::put('fruitProduction/activity/{id}', 'API\FruitProductionController@updateActivity');
    Route::delete('fruitProduction/activity/{id}', 'API\FruitProductionController@destroyActivity');

    Route::post('fruitProduction/allocation/{id}', 'API\FruitProductionController@storeAllocation');
    Route::get('fruitProduction/allocation/{id}', 'API\FruitProductionController@showAllocation');
    Route::get('fruitProduction/availableAllocation/{id}', 'API\FruitProductionController@availableAllocation');
    Route::post('fruitProduction/allocate', 'API\FruitProductionController@allocate');
    Route::put('fruitProduction/removeAllocation/{id}/{plot}', 'API\FruitProductionController@removeAllocation');

    /** Community API */
    Route::apiResources(['communityPartner' => 'API\CommunityPartnerController']);
    Route::get('getCommunityProductListing/{id}', 'API\PartnerCommunityProductController@getCommunityProductListing');
    Route::get('getCommunityAvailProductListing/{id}', 'API\PartnerCommunityProductController@getCommunityAvailProductListing');
    Route::post('partnerCommunityProduct/addToProductListing', 'API\PartnerCommunityProductController@addToProductListing');
    Route::put('partnerCommunityProduct/removeToProductListing/{id}', 'API\PartnerCommunityProductController@removeToProductListing');
    Route::post('partnerCommunityProduct/updateProductListing', 'API\PartnerCommunityProductController@updateProductListing');
    Route::post('partnerCommunityProduct/updateCommunityDisallowDate', 'API\PartnerCommunityProductController@updateCommunityDisallowDate');
    Route::post('partnerCommunityProduct/updatePaymentOption', 'API\PartnerCommunityProductController@updatePaymentOption');
    Route::post('partnerCommunityProduct/updatePickup', 'API\PartnerCommunityProductController@updatePickup');

    /** Buyer Merchant API */
    Route::apiResources(['buyerMerchant' => 'API\BuyerMerchantController']);
    Route::get('getMerchantClient', 'API\BuyerMerchantController@getClient');
    Route::get('getClientProductListing/{id}', 'API\MerchantBuyerProductController@getClientProductListing');
    Route::get('getClientAvailProductListing/{id}', 'API\MerchantBuyerProductController@getClientAvailProductListing');
    Route::post('merchantBuyerProduct/addToProductListing', 'API\MerchantBuyerProductController@addToProductListing');
    Route::put('merchantBuyerProduct/removeToProductListing/{id}', 'API\MerchantBuyerProductController@removeToProductListing');
    Route::post('merchantBuyerProduct/updateProductListing', 'API\MerchantBuyerProductController@updateProductListing');
    Route::get('getMerchantBuyerProduct/{id}', 'API\MerchantBuyerProductController@getMerchantBuyerProduct');

    /** QRPayment API */
    Route::apiResources(['qrpayment' => 'API\QRPaymentController']);
    Route::post('qrpayment/signupOTP', 'API\QRPaymentController@signupOTP');
    Route::post('qrpayment/signup', 'API\QRPaymentController@signup');
    Route::post('qrpayment/loginOTP', 'API\QRPaymentController@loginOTP');
    Route::post('qrpayment/login', 'API\QRPaymentController@login');
    Route::post('qrpayment/checkUserBalance', 'API\QRPaymentController@checkUserBalance');
    Route::post('qrpayment/paymentQRPay', 'API\QRPaymentController@paymentQRPay');
    // Route::post('qrpayment/reqOTP', 'API\QRPaymentController@reqOTP');

    /** Registry Form API */
    Route::apiResources(['registryform' => 'API\RegistryFormController']);
    Route::get('findRegistryForm', 'API\RegistryFormController@search');
    Route::post('registryform/aid/{id}', 'API\RegistryFormController@storeAid');
    Route::put('registryform/aid/{id}', 'API\RegistryFormController@updateAid');
    Route::put('registryform/aid/delete/{id}', 'API\RegistryFormController@destroyAid');
    Route::post('registryform/family/{id}', 'API\RegistryFormController@storeFamily');
    Route::put('registryform/family/{id}', 'API\RegistryFormController@updateFamily');
    Route::put('registryform/family/delete/{id}', 'API\RegistryFormController@destroyFamily');
    Route::post('registryform/crop/{id}', 'API\RegistryFormController@storeCrop');
    Route::put('registryform/crop/{id}', 'API\RegistryFormController@updateCrop');
    Route::put('registryform/crop/delete/{id}', 'API\RegistryFormController@destroyCrop');
    Route::put('registryform/cropinfo/{id}', 'API\RegistryFormController@updateCropInfo');
    Route::put('registryform/fishery/{id}', 'API\RegistryFormController@updateFisheryInfo');
    Route::post('registryform/livestock/{id}', 'API\RegistryFormController@updateLivestockInfo');
    Route::put('registryform/livestock/delete/{id}', 'API\RegistryFormController@destroyLivestock');
    //Route::post('registryform/family/{id}', 'API\RegistryFormController@updateFamily');
    // Route::get('cropProduction/harvest/{id}', 'API\CropProductionController@showHarvest');
    // Route::put('cropProduction/harvest/{id}', 'API\CropProductionController@updateHarvest');
    // Route::delete('cropProduction/harvest/{id}', 'API\CropProductionController@destroyHarvest');

    /** Bank Deposit API */
    //Route::apiResources(['bankdeposit' => 'API\BankDepositController']);
    Route::post('bankdeposit/upload', 'API\BankDepositController@upload');
    Route::post('bankdeposit/action', 'API\BankDepositController@action');

    /** Rice Variety API */
    Route::apiResources(['ricevariety' => 'API\RiceVarietyController']);

    /** Crop API */
    Route::get('cropCategory', 'API\CropController@category');
    Route::get('cropList/{category}', 'API\CropController@cropList');
    
    /** Crop API */
    Route::get('livestocks', 'API\LivestockController@index');

    /** Farm Workers API */
    Route::apiResources(['farmWorker' => 'API\FarmWorkerController']);
    Route::get('findFarmWorker', 'API\FarmWorkerController@search');
    Route::get('userModules', 'API\FarmWorkerController@userModules');
});

/** Accessible APi */
Route::get('category', 'API\CategoryController@index');
Route::get('subcategory', 'API\CategoryController@subcategory');
Route::get('unit', 'API\UnitController@index');
Route::get('product', 'API\SHOP\ProductController@index');
Route::get('product/info/{product}', 'API\SHOP\ProductController@info');
Route::get('product/data/{product}', 'API\SHOP\ProductController@data');
Route::get('product/{seller}/other', 'API\SHOP\ProductController@other');
Route::get('product/search', 'API\SHOP\ProductController@search');
Route::get('seller', 'API\SellerController@index');
Route::get('seller/{slug}', 'API\SellerController@show');
Route::get('partner/{id}', 'API\SellerController@partner');
Route::get('partnerList/{id}', 'API\SellerController@partnerList');
Route::get('community', 'API\CommunityController@index2');
Route::get('community/{code}', 'API\CommunityController@show');
Route::get('findSeller', 'API\SellerController@search');
Route::get('findCommunity', 'API\CommunityController@search');
Route::get('store/product/{seller}', 'API\StoreController@product');
Route::get('store/communityProduct/{community}', 'API\StoreController@communityProduct');
Route::get('store/productCommunity/{community}/search', 'API\StoreController@productCommunitySearch');
Route::get('store/product/{seller}/search', 'API\StoreController@search');
Route::get('store', 'API\StoreController@index');
//Route::get('shop/initOrderTransaction/{ids}', 'API\SHOP\ProductController@initOrderTransaction');

Route::get('paymentmode/{default?}', 'API\PaymentModeController@index');
Route::get('province', 'API\ProvinceController@index');
Route::get('getCity/{province}', 'API\ProvinceController@getCity');
//Route::apiResources(['proceed' => 'API\CartController']);

/** Mobile API Access */

Route::group(['prefix' => 'apimbl'], function() {
    
    Route::group(['prefix' => 'fetch'], function () {
        
        Route::get('featproduct', 'API\MOBILE\FetchController@featProduct');    
        Route::get('product', 'API\MOBILE\FetchController@product');    
        Route::get('category', 'API\MOBILE\FetchController@category');    
        Route::get('partner', 'API\MOBILE\FetchController@partner');    
        Route::get('community', 'API\MOBILE\FetchController@community');    
        Route::get('community-partner', 'API\MOBILE\FetchController@communityPartner');    
        Route::get('product/{product}', 'API\MOBILE\FetchController@productData');

    });

    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', 'API\MOBILE\AuthController@login');

        Route::group(['middleware' => 'auth:api'], function() {
            Route::get('user', 'API\MOBILE\AuthController@user');
            Route::get('logout', 'API\MOBILE\AuthController@logout');
            Route::get('figures', 'API\MOBILE\DashboardController@figures');
            Route::get('active-rice-production', 'API\MOBILE\DashboardController@activeRiceProduction');
            Route::get('active-crop-production', 'API\MOBILE\DashboardController@activeCropProduction');

            /** My Order Allocation API */
            Route::apiResources(['myOrderAllocation' => 'API\MyOrderAllocationController']);
            Route::get('findMyOrderAllocation', 'API\MyOrderAllocationController@search');
            Route::put('myOrderAllocation/setStatus/{id}/{status}', 'API\MyOrderAllocationController@setStatus');
        });

        // Route::group(['middleware' => 'api'], function() {
        //     Route::get('areasize', 'API\MOBILE\DashboardController@areasize');
        // });
    });

});

// Route::group(['middleware' => 'mbl'], function () {
//     Route::get('fetch/category', 'API\MOBILE\FetchController@category');        
// });
