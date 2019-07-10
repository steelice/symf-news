<?php


namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ArticleRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Return last articles with pagination
     *
     * @param int $page
     * @param User|null $user
     * @return array
     */
    public function findLatest(int $page = 1, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('u')
            ->innerJoin('p.user', 'u')
            ->orderBy('p.createdAt', 'DESC');

        if ($user) {
            $qb->andWhere('p.user = :user')->setParameter('user', $user);
        }

        return $this->createPaginator($qb, $page);
    }


    private function createPaginator(
        QueryBuilder $queryBuilder,
        int $currentPage,
        int $pageSize = Article::ARTICLES_PER_PAGE
    ): array {
        $currentPage = $currentPage < 1 ? 1 : $currentPage;
        $firstResult = ($currentPage - 1) * $pageSize;
        $query = $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($pageSize)
            ->getQuery();
        $paginator = new Paginator($query);
        $numResults = $paginator->count();
        $hasPreviousPage = $currentPage > 1;
        $hasNextPage = ($currentPage * $pageSize) < $numResults;

        return [
            'results' => $paginator->getIterator(),
            'currentPage' => $currentPage,
            'hasPreviousPage' => $hasPreviousPage,
            'hasNextPage' => $hasNextPage,
            'previousPage' => $hasPreviousPage ? $currentPage - 1 : null,
            'nextPage' => $hasNextPage ? $currentPage + 1 : null,
            'numPages' => (int)ceil($numResults / $pageSize),
            'haveToPaginate' => $numResults > $pageSize,
        ];
    }

}