<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;


trait BranchTrait
{
    protected static function bootBranchTrait()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $branchId = decrypt(session('logged_session_data.branch_id'));
            $builder->where('branch_id', $branchId);
        });
    }
}
