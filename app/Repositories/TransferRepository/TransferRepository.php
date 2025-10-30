<?php

declare(strict_types=1);

namespace App\Repositories\TransferRepository;

use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 *
 */
class TransferRepository implements TransferRepositoryInterface
{
    /**
     * Создать новый перевод.
     *
     * @param Transfer $transfer
     * @return Transfer
     * @throws Throwable
     */
    public function create(Transfer $transfer)
    {
        $transfer->saveOrFail();

        return $transfer;
    }

    /**
     * Получить перевод по ID.
     *
     * @param int $transferID
     * @return Transfer
     */
    public function getByID(int $transferID): Transfer
    {
        return Transfer::query()->findOrFail($transferID);
    }

    /**
     * Получить перевод по ID с транзакциями.
     *
     * @param int $transferID
     * @return \Illuminate\Support\Collection
     */
    public function getByIDWithTransactions(int $transferID): \Illuminate\Support\Collection
    {
        $result = DB::table('transfers')
            ->leftJoin('transactions', 'transfers.id', '=', 'transactions.transfer_id')
            ->where('transfers.id', $transferID)
            ->select([
                'transfers.*',
                'transactions.id as transaction_id',
                'transactions.user_id as transaction_user_id',
                'transactions.type as transaction_type',
                'transactions.amount as transaction_amount',
                'transactions.comment as transaction_comment',
                'transactions.created_at as transaction_created_at',
                'transactions.updated_at as transaction_updated_at'
            ])
            ->get();

        if ($result->isEmpty()) {
            throw new ModelNotFoundException("Transfer not found with id: {$transferID}");
        }

        // Создаем объект Transfer из первой записи
        $firstRow = $result->first();
        $transfer = new Transfer([
            'id' => $firstRow->id,
            'from_user_id' => $firstRow->from_user_id,
            'to_user_id' => $firstRow->to_user_id,
            'amount' => $firstRow->amount,
            'comment' => $firstRow->comment,
            'created_at' => $firstRow->created_at,
            'updated_at' => $firstRow->updated_at,
        ]);

        // Создаем коллекцию Transaction
        $transactions = $result->map(function ($row) {
            return new Transaction([
                'id' => $row->transaction_id,
                'user_id' => $row->transaction_user_id,
                'type' => $row->transaction_type,
                'amount' => $row->transaction_amount,
                'comment' => $row->transaction_comment,
                'transfer_id' => $row->id,
                'created_at' => $row->transaction_created_at,
                'updated_at' => $row->transaction_updated_at,
            ]);
        })->filter();

        return collect([
            'transfer' => $transfer,
            'transactions' => $transactions
        ]);
    }


    /**
     * Получить переводы по ID пользователя.
     *
     * @param int $userID
     * @return Collection
     */
    public function getByUserID(int $userID): Collection
    {
        return Transfer::query()->where('from_user_id', $userID)
            ->orWhere('to_user_id', $userID)
            ->orderBy('created_at', 'desc')
            ->get();
    }


    /**
     * Получить переводы, где пользователь отправитель.
     *
     * @param int $fromUserID
     * @return Collection
     */
    public function getByFromUserID(int $fromUserID): Collection
    {
        return Transfer::query()->where('from_user_id', $fromUserID)
            ->orderBy('created_at', 'desc')
            ->get();
    }


    /**
     * Получить переводы, где пользователь получатель.
     *
     * @param int $toUserID
     * @return Collection
     */
    public function getByToUserID(int $toUserID): Collection
    {
        return Transfer::query()->where('to_user_id', $toUserID)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
