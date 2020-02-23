<?php

namespace App\Services;
use Illuminate\Http\Request;

class QRSService
{
    protected $NCL_AREA = ['Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales'];
    protected $SLR_AREA = ['Batangas', 'Cavite', 'Laguna', 'Quezon', 'Rizal'];

    protected $NCR_RATE_EXP = 140;
    protected $NCR_EXCESS_EXP = 50;
    protected $SLR_RATE_EXP = 140;
    protected $SLR_EXCESS_EXP = 50;
    protected $VISMIN_RATE_EXP = 180;
    protected $VISMIN_EXCESS_EXP = 55;

    protected $NCR_RATE_ECO = 110;
    protected $NCR_EXCESS_ECO = 50;
    protected $SLR_RATE_ECO = 110;
    protected $SLR_EXCESS_ECO = 50;
    protected $VISMIN_RATE_ECO = 130;
    protected $VISMIN_EXCESS_ECO = 55;

    protected $BASE_WEIGHT = 3;
    
    public function compute($code, $weight, $originCity, $originProvince, $distCity, $distProvince) 
    {
        // $originProvince = 'Bataan';
        // $distProvince = 'Bataan';
        $originCost = 0;
        $distCost = 0;
        $weight = 3;
        $shippingCost = 0;

        $NCR_RATE = $this->NCR_RATE_EXP;
        $NCR_EXCESS = $this->NCR_EXCESS_EXP;
        $SLR_RATE = $this->SLR_RATE_EXP;
        $SLR_EXCESS = $this->SLR_EXCESS_EXP;
        $VISMIN_RATE = $this->VISMIN_RATE_EXP;
        $VISMIN_EXCESS = $this->VISMIN_EXCESS_EXP;

        if ($code === 'QRSECO') {
            $NCR_RATE = $this->NCR_RATE_ECO;
            $NCR_EXCESS = $this->NCR_EXCESS_ECO;
            $SLR_RATE = $this->SLR_RATE_ECO;
            $SLR_EXCESS = $this->SLR_EXCESS_ECO;
            $VISMIN_RATE = $this->VISMIN_RATE_ECO;
            $VISMIN_EXCESS = $this->VISMIN_EXCESS_ECO;    
        }

        if (in_array($originProvince, $this->NCL_AREA)) {
            $originCost = $this->getCost($weight, $this->BASE_WEIGHT, $NCR_RATE, $NCR_EXCESS);
        } else if (in_array($originProvince, $this->SLR_AREA)) {
            $originCost = $this->getCost($weight, $this->BASE_WEIGHT, $SLR_RATE, $SLR_EXCESS);
        } else {
            $originCost = $this->getCost($weight, $this->BASE_WEIGHT, $VISMIN_RATE, $VISMIN_EXCESS);
        }

        if (in_array($distProvince, $this->NCL_AREA)) {
            $distCost = $this->getCost($weight, $this->BASE_WEIGHT, $NCR_RATE, $NCR_EXCESS);
        } else if (in_array($distProvince, $this->SLR_AREA)) {
            $distCost = $this->getCost($weight, $this->BASE_WEIGHT, $SLR_RATE, $SLR_EXCESS);
        } else {
            $distCost = $this->getCost($weight, $this->BASE_WEIGHT, $VISMIN_RATE, $VISMIN_EXCESS);
        }

        $shippingCost = $originCost;

        if ($distCost > $originCost) {
            $shippingCost = $distCost;    
        }

        return $shippingCost;
    }

    public function getCost($weight, $base_weight, $rate, $excess) 
    {
        $cost = $rate;
        if ($weight > $base_weight) {
            $cost += ($weight - $base_weight) * $excess;
        }
        return $cost;
    }
    
}
