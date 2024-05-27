<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\DataTableQueryParams;
use App\Entity\Category;
use App\Entity\User;
use Psr\SimpleCache\CacheInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryService
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
        private readonly CacheInterface $cache
    ) {
    }

    public function create(string $name, User $user): Category
    {
        $category = new Category();

        $category->setUser($user);

        $userId = $user->getId();
        $this->deleteCategoriesKeyedByNameFromCache($userId);

        return $this->update($category, $name, $userId);
    }

    public function getPaginatedCategories(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['name', 'createdAt', 'updatedAt']) ? $params->orderBy : 'updatedAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('c.name LIKE :name')
            ->setParameter('name', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('c.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function getById(int $id): ?Category
    {
        return $this->entityManager->find(Category::class, $id);
    }

    public function update(Category $category, string $name, int $userId): Category
    {
        $category->setName($name);

        $this->deleteCategoriesKeyedByNameFromCache($userId);
        return $category;
    }

    public function getCategoryNames(): array
    {
        return $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select('c.id', 'c.name')
            ->getQuery()
            ->getArrayResult();
    }

    public function findByName(string $name): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->findBy(['name' => $name])[0] ?? null;
    }

    public function getAllKeyedByName(int $userId): array
    {
        $cacheKey = 'categories_keyed_by_name_' . $userId;
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $categories  = $this->entityManager->getRepository(Category::class)->findAll();
        $categoryMap = [];

        foreach ($categories as $category) {
            $categoryMap[strtolower($category->getName())] = $category;
        }

        $this->cache->set($cacheKey, $categoryMap);

        return $categoryMap;
    }
    public function delete(int $userId): Category
    {
        $this->deleteCategoriesKeyedByNameFromCache($userId);

        return $category;
    }

    public function deleteCategoriesKeyedByNameFromCache(int $userId): void
    {
        $this->cache->delete('categories_keyed_by_name_' . $userId);
    }
}
