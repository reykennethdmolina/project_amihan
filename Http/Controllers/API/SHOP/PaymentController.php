<?php

namespace App\Http\Controllers\API\SHOP;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\OrderTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;


use Carbon\Carbon;
use Dragonpay;

class PaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth('api')->user()->id;
        $buyer = $request->info;
        $orderLists = $request->data;

        /** Initiate order transaction */
        $transaction = OrderTransaction::firstOrCreate(['buyer_id' => $user,
            'refNo' => null,
            'purchased_date' => null,
            'payment_date' => null,
            'payment_status' => 'P',
        ]);

        if (!empty($transaction)) {
            /** Initiate transaction */
            $transaction->update([
                'buyer_id' => $user,
                'fullname' => $buyer['fullname'],
                'mobile' => $buyer['mobile'],
                'hoblst' => $buyer['hoblst'],
                'barangay' => $buyer['barangay'],
                'city' => $buyer['city'],
                'province' => $buyer['province'],
                'postal_code' => $buyer['postal_code'],
                'landmark' => $buyer['landmark'],
            ]);

            /** Initiate order transaction */
            $orderIds = array();
            $ttotalWeight = 0; $ttotalCost = 0; $ttotalShippingCost = 0; $ttotalAmount = 0;
            foreach ($orderLists as $orderList) {                
                $order = Order::firstOrCreate([
                    'order_transaction_id' => $transaction->id,
                    'buyer_id' => $user,
                    'seller_id' => $orderList[0]['seller_id'],
                    'status' => 'P',
                ]);

                array_push($orderIds, $order->id);

                $orderItemProducts = array();
                $totalWeight = 0; $totalCost = 0; $totalShippingCost = 0; $totalAmount = 0;
                $vatable = 0; $vatexempt = 0; $vatzero = 0; $vatamount = 0; $commission = 0;
                    
                foreach ($orderList as $item) {
                    
                    $orderItem = OrderItem::firstOrCreate([
                        'order_id' => $order->id,
                        'buyer_id' => $user,
                        'seller_id' => $order->seller_id,
                        'product_id' => $item['id'],
                        'status' => 'P',
                    ]);

                    array_push($orderItemProducts, $item['id']);

                    $product = Product::find($item['id']);

                    $orderItem->update([
                        'sku' => $item['sku'],
                        'name' => $product->name,
                        'variety' => $item['variety'],
                        'qty' => $item['qty'],
                        'price' => $product['price'],
                        'vatable_amount' => $product['vatable_amount'] * $item['qty'],
                        'vat_exempt_amount' => $product['vat_exempt_amount'] * $item['qty'],
                        'vat_zero_amount' => $product['vat_zero_amount'] * $item['qty'],
                        'vat_amount' => $product['vat_amount'] * $item['qty'],
                        'total_price' => $product['price'] * $item['qty'],
                        'vat_type' => $product['vat_type'],
                        'vat_rate' => $product['vat_rate'],
                        'commission_rate' => $product['commission_rate'],
                        'commission_amount' => $product['commission_amount'] * $item['qty'],
                        'weight' => $product['weight'] * $item['qty'],
                        'unit' => $item['unit'],
                    ]);

                    $totalWeight += $product['weight'] * $item['qty'];
                    $totalCost += $product['price'] * $item['qty'];
                    $totalShippingCost += $item['shippingCost'];
                    $totalAmount += ($product['price'] * $item['qty']) + $item['shippingCost'];

                    $vatable += $product['vatable_amount'] * $item['qty'];
                    $vatexempt += $product['vat_exempt_amount'] * $item['qty'];
                    $vatzero += $product['vat_zero_amount'] * $item['qty'];
                    $vatamount += $product['vat_amount'] * $item['qty'];
                    $commission += $product['commission_amount'] * $item['qty'];
                }

                $ttotalWeight += $totalWeight; 
                $ttotalCost += $totalCost; 
                $ttotalShippingCost += $totalShippingCost; 
                $ttotalAmount += $totalAmount;

                $order->update([
                    'vatable_amount' => $vatable,
                    'vat_exempt_amount' => $vatexempt,
                    'vat_zero_amount' => $vatzero,
                    'vat_amount' => $vatamount,
                    'commission_amount' => $commission,
                    'totalCost' => $totalCost,
                    'totalWeight' => $totalWeight,
                    'totalShippingCost' => $totalShippingCost,
                    'totalAmount' => $totalAmount,
                    'shippingOption' => $orderList[0]['shippingOption'],
                    'pickUpLocation' => $orderList[0]['pickUpLocation'],
                ]);
                
                OrderItem::where('order_id', $order->id)
                    ->where('status', 'P')
                    ->whereNotIn('product_id', $orderItemProducts)->forcedelete();
            }

            $transaction->update([
                'totalCost' => $ttotalCost,
                'totalWeight' => $ttotalWeight,
                'totalShippingCost' => $ttotalShippingCost,
                'totalAmount' => $ttotalAmount,
            ]);

            Order::where('order_transaction_id', $transaction->id)
                ->whereNotIn('id', $orderIds)->forcedelete();

            OrderItem::where('status', 'P')
                ->where('buyer_id', $user)
                ->whereNotIn('order_id', $orderIds)->forcedelete();

            $url = $this->finalPay($transaction, "DRAGONPAY");
            
            return ['status' => true, 'url' => $url];
        } else {
            return false;
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCOD(Request $request)
    {
        $user = auth('api')->user()->id;
        $buyer = $request->info;
        $orderLists = $request->data;

        /** Initiate order transaction */
        $transaction = OrderTransaction::firstOrCreate(['buyer_id' => $user,
            'refNo' => null,
            'purchased_date' => null,
            'payment_date' => null,
            'payment_status' => 'COD',
        ]);

        if (!empty($transaction)) {
            /** Initiate transaction */
            $transaction->update([
                'buyer_id' => $user,
                'fullname' => $buyer['fullname'],
                'mobile' => $buyer['mobile'],
                'hoblst' => $buyer['hoblst'],
                'barangay' => $buyer['barangay'],
                'city' => $buyer['city'],
                'province' => $buyer['province'],
                'postal_code' => $buyer['postal_code'],
                'landmark' => $buyer['landmark'],
                'purchased_date' => Carbon::now(),
                'refNo' => 'AGR0'.''.str_pad($transaction->id, 6 ,"0", STR_PAD_LEFT),
            ]);

            /** Initiate order transaction */
            $orderIds = array();
            $ttotalWeight = 0; $ttotalCost = 0; $ttotalShippingCost = 0; $ttotalAmount = 0;
            foreach ($orderLists as $orderList) {                
                $order = Order::firstOrCreate([
                    'order_transaction_id' => $transaction->id,
                    'buyer_id' => $user,
                    'seller_id' => $orderList[0]['seller_id'],
                    'status' => 'A',
                ]);

                array_push($orderIds, $order->id);

                $orderItemProducts = array();
                $totalWeight = 0; $totalCost = 0; $totalShippingCost = 0; $totalAmount = 0;
                $vatable = 0; $vatexempt = 0; $vatzero = 0; $vatamount = 0; $commission = 0;
                    
                foreach ($orderList as $item) {
                    
                    $orderItem = OrderItem::firstOrCreate([
                        'order_id' => $order->id,
                        'buyer_id' => $user,
                        'seller_id' => $order->seller_id,
                        'product_id' => $item['id'],
                        'status' => 'A',
                    ]);

                    array_push($orderItemProducts, $item['id']);

                    $product = Product::find($item['id']);

                    $orderItem->update([
                        'sku' => $item['sku'],
                        'name' => $product->name,
                        'variety' => $item['variety'],
                        'qty' => $item['qty'],
                        'price' => $product['price'],
                        'vatable_amount' => $product['vatable_amount'] * $item['qty'],
                        'vat_exempt_amount' => $product['vat_exempt_amount'] * $item['qty'],
                        'vat_zero_amount' => $product['vat_zero_amount'] * $item['qty'],
                        'vat_amount' => $product['vat_amount'] * $item['qty'],
                        'total_price' => $product['price'] * $item['qty'],
                        'vat_type' => $product['vat_type'],
                        'vat_rate' => $product['vat_rate'],
                        'commission_rate' => $product['commission_rate'],
                        'commission_amount' => $product['commission_amount'] * $item['qty'],
                        'weight' => $product['weight'] * $item['qty'],
                        'unit' => $item['unit'],
                    ]);

                    $totalWeight += $product['weight'] * $item['qty'];
                    $totalCost += $product['price'] * $item['qty'];
                    $totalShippingCost += $item['shippingCost'];
                    $totalAmount += ($product['price'] * $item['qty']) + $item['shippingCost'];

                    $vatable += $product['vatable_amount'] * $item['qty'];
                    $vatexempt += $product['vat_exempt_amount'] * $item['qty'];
                    $vatzero += $product['vat_zero_amount'] * $item['qty'];
                    $vatamount += $product['vat_amount'] * $item['qty'];
                    $commission += $product['commission_amount'] * $item['qty'];
                }

                $ttotalWeight += $totalWeight; 
                $ttotalCost += $totalCost; 
                $ttotalShippingCost += $totalShippingCost; 
                $ttotalAmount += $totalAmount;

                $order->update([
                    'vatable_amount' => $vatable,
                    'vat_exempt_amount' => $vatexempt,
                    'vat_zero_amount' => $vatzero,
                    'vat_amount' => $vatamount,
                    'commission_amount' => $commission,
                    'totalCost' => $totalCost,
                    'totalWeight' => $totalWeight,
                    'totalShippingCost' => $totalShippingCost,
                    'totalAmount' => $totalAmount,
                    'shippingOption' => $orderList[0]['shippingOption'],
                    'pickUpLocation' => $orderList[0]['pickUpLocation'],
                ]);
              
            }

            $transaction->update([
                'totalCost' => $ttotalCost,
                'totalWeight' => $ttotalWeight,
                'totalShippingCost' => $ttotalShippingCost,
                'totalAmount' => $ttotalAmount,
            ]);
            
            return ['status' => true, 'url' => 'COD'];   
        } else {
            return false;
        }

    }

    public function finalPay($transaction , $paymentMode)
    {
        if ($paymentMode === "DRAGONPAY") {
            return $this->dragonpay($transaction);
        } else {
            return false;
        }
    }

    public function dragonpay($transaction)
    {
        $description = "Payment for agronegosyo";
        $params = [
            'transactionId' => $transaction->id,
            'amount'        => number_format($transaction->totalAmount, 2, '.', ''),
            'currency'      => 'PHP',
            'description'   => $description,
            'email'         => auth('api')->user()->email,
        ];

        #$message; 0u0p7fJcVzEGFVh
        #echo sha1('AGRONEGOSYO:6104:1169.7:PHP:Payment for agronegosyo:john@gmail.com:0u0p7fJcVzEGFVh');

        $url = Dragonpay::getUrl($params);

        if ($url) {
            /* Update purchased date */
            $datetime = Carbon::now();
            $transaction->update(['purchased_date' => $datetime]);
        }

        return $url;
    }
}
