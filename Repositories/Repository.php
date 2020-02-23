<?php

namespace App\Repositories;

use Closure;

class Repository
{
    public function query($builder, $request, $filters)
    {
        return tap($builder, function ($builder) use ($request, $filters) {
            array_walk($filters, function ($item, $name) use ($builder, $request) {
                if (! ($item['callback'] ?? null) instanceof Closure) {
                    return;
                }

                $builder->when($value = $request->get($name), function($query) use ($item, $value) {
                    return call_user_func($item['callback'], $query, $value);
                });
            });
        });
    }
}
