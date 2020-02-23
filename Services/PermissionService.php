<?php

namespace App\Services;
use Illuminate\Http\Request;

class PermissionService
{
    /** Verify if user has access to the module */
    public function verify($seg) {

        $usr_modules = session('usr_modules');

        foreach ($usr_modules as $perm => $modules) {
            foreach ($modules as $mod) {
                if ($mod->code == $seg) {
                    return 1;
                } 
            }
        }

        return 0;
    }
    
}
