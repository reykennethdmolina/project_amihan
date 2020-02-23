<?php

namespace App\Repositories;

use DB;
use App\Models\RegistryForm;

class RegistryRepository extends Repository
{
    public function getRegistry($mao, $request)
    {
        $builder = RegistryForm::where('mao_id', $mao)
            ->orderBy('created_at', 'DESC');

        //return $builder;
        return $this->filter($builder, $request);
    }

    
    public function filter($query, $request)
    {
        // return $request->all();
        // if ($request->has('createdfrom')) {
        //     $query->whereBetween('created_at', [$request->input('createdfrom'), $request->input('createdto')]);
        // }
        if ($request->input('createdfrom') != '' && $request->input('createdto') != '') {
            $query->whereBetween('created_at', [$request->input('createdfrom'), $request->input('createdto')]);
        } else {
            if ($request->has('createdfrom')) {
                if ($request->input('createdfrom') != '') {
                    $query->where('created_at', '>=', $request->input('createdfrom'));
                }
            }
    
            if ($request->has('createdto')) {
                if ($request->input('createdto') != '') {
                    $query->where('created_at', '=<', $request->input('createdto'));
                }
            }
        }

        if ($request->has('education')) {
            if ($request->input('education') != '') {
                $query->where('education', $request->input('education'));
            }
        }

        if ($request->has('religion')) {
            if ($request->input('religion') != '') {
                $query->where('religion', $request->input('religion'));
            }
        }

        if ($request->has('barangay')) {
            if ($request->input('barangay') != '') {
                $query->where('barangay', 'LIKE', '%'.$request->input('barangay').'%');
            }
        }

        if ($request->has('gender')) {
            if ($request->input('gender') != '') {
                $query->where('gender', $request->input('gender'));
            }
        }

        if ($request->has('civil_status')) {
            if ($request->input('civil_status') != '') {
                $query->where('civil_status', $request->input('civil_status'));
            }
        }

        if ($request->has('livelihood')) {
            if ($request->input('livelihood') != '') {
                $query->where('livelihood_type', $request->input('livelihood'));
            }
        }

        

        // if ($request->has('merchant')) {
        //     if ($request->input('merchant') != '') {
        //         $query->where('owner_id', $request->input('merchant'));
        //     }
        // }

        // if ($request->has('refno')) {
        //     if ($request->input('refno') != '') {
        //         $query->where('refno', 'LIKE', '%'.$request->input('refno').'%');
        //     }
        // }

        // if ($request->has('status')) {
        //     if ($request->get('status') != 'all') {
        //         $query->where('status', strtoupper($request->get('status')));
        //     }
        // }

        return $query;
    }
}
