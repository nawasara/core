<?php

namespace Nawasara\Core\Traits;

trait HasSearch
{
    public function scopeFilter($query, $term = [])
    {
        // $term = "%$term%";

        // $query->where(function ($query) use ($term) {
        //     $query->where('name', 'like', $term)
        //           ->orWhere('email', 'like', $term)
        //           ->orWhere('username', 'like', $term);
        // });
    }

    public function scopeOrderByDefault($q)
    {
        $q->orderBy('name');
    }
}