<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\DataTableQueryParams;
use App\DataObjects\TransactionData;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TransactionService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    public function getPaginatedTransactions(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->select('t', 'c', 'r')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.receipts', 'r')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['description', 'amount', 'date', 'category'])
            ? $params->orderBy
            : 'date';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('t.description LIKE :description')
                  ->setParameter('description', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        //echo($query->getQuery()->getSQL());

        if ($orderBy === 'category') {
            $query->orderBy('c.name', $orderDir);
        } else {
            $query->orderBy('t.' . $orderBy, $orderDir);
        }

        return new Paginator($query);
    }

    public function create(
        TransactionData $transactionData,
        User $user,
        bool $flush = false
    ): Transaction {
        $transaction = new Transaction();

        $transaction->setUser($user);

        return $this->update($transaction, $transactionData, $flush);
    }

    public function update(
        Transaction $transaction,
        TransactionData $transactionData,
        bool $flush = false
    ): Transaction {
        $transaction->setDescription($transactionData->description);
        $transaction->setAmount($transactionData->amount);
        $transaction->setDate($transactionData->date);
        $transaction->setCategory($transactionData->category);

        $this->entityManager->persist($transaction);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $transaction;
    }

    public function getById(int $id): ?Transaction
    {
        return $this->entityManager->find(Transaction::class, $id);
    }

    public function delete(int $id, bool $flush = false): void
    {
        $transaction = $this->entityManager->find(Transaction::class, $id);

        $this->entityManager->remove($transaction);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
