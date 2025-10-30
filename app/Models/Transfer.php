<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Модель Transfer описывает перевод между пользователями.
 * Каждый перевод фиксирует, кто отправил деньги, кто получил, сумму и комментарий.
 *
 * @property int $id
 * @property int $from_user_id     ID пользователя-отправителя
 * @property int $to_user_id       ID пользователя-получателя
 * @property float $amount         Сумма перевода
 * @property string|null $comment  Комментарий к переводу
 * @property Carbon $created_at    Дата создания
 * @property Carbon $updated_at    Дата обновления
 */
class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'amount',
        'comment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Получить пользователя, который сделал перевод.
     *
     * @return BelongsTo
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Получить пользователя, которому предназначался перевод.
     *
     * @return BelongsTo
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Получить транзакции, к которой относится перевод.
     *
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
