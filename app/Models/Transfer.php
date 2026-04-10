<?php

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'status',
        'provider',
        'sender_amount_cad',
        'fee_cad',
        'exchange_rate',
        'recipient_amount_xaf',
        'recipient_name',
        'recipient_phone',
        'idempotency_key',
        'failure_reason',
        'paid_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'sender_amount_cad' => 'decimal:2',
            'fee_cad' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'recipient_amount_xaf' => 'decimal:2',
            'paid_at' => 'datetime',
            'processed_at' => 'datetime',
            'status' => TransferStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
