<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Статусы транзакций.
 */
enum TransactionStatusEnum: string
{
    /**
     * Зачисление средств.
     */
    case DEPOSIT = 'deposit';
    /**
     * Списание средств.
     */
    case WITHDRAW = 'withdraw';
    /**
     * Перевод от отправителя (уменьшение баланса).
     */
    case TRANSFER_OUT = 'transfer_out';
    /**
     * Перевод получателю (увеличение баланса).
     */
    case TRANSFER_IN = 'transfer_in';
}
