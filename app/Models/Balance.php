<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Сущность баланс.
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 */
class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Получить пользователя текущего баланса.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
