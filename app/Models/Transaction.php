<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property float $amount
 * @property int|null $transfer_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'comment',
        'transfer_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Получаем пользователя кто сделал транзакцию.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь транзакции с переводом.
     *
     * @return BelongsTo
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }
}
