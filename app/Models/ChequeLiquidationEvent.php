<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ChequeLiquidationEvent extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $fillable = [
        'company_id', 'cheque_liquidation_id', 'user_id',
        'action', 'from_status', 'to_status',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
