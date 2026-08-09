<?php
/**
 * @author Attila HOFFEREK <azhofi@gmail.com>
 */

namespace App\Repository;

use App\Entity\Review;
use App\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Symfony\Component\String\u;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findLatest(int $page = 1): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.createdAt <= :now')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter('now', new \DateTimeImmutable())
        ;

        return new Paginator($qb)->paginate($page);
    }
    
    /**
     * @return Review[]
     */
    public function findBySearchQuery(string $query, int $limit = Paginator::PAGE_SIZE): array
    {
        $searchTerms = $this->extractSearchTerms($query);

        if (0 === \count($searchTerms)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.companyName LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

        /** @var Post[] $result */
        $result = $queryBuilder
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }
    
    /**
     * Transforms the search string into an array of search terms.
     *
     * @return string[]
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $terms = array_unique(u($searchQuery)->replaceMatches('/[[:space:]]+/', ' ')->trim()->split(' '));

        // ignore the search terms that are too short
        return array_filter($terms, static fn ($term) => 2 <= $term->length());
    }
    
    public function save(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Egyedi lekérdezési példa: Értékelések lekérése cégtípus / név alapján
     *
     * @return Review[]
     */
    public function findByCompanyName(string $companyName): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.companyName = :companyName')
            ->setParameter('companyName', $companyName)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Cégnevenkénti statisztika: értékelések száma és átlaga
     * 
     * @return array<int, array{companyName: string, reviewCount: int, averageRating: float}>
     */
    public function getCompanyStatistics(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.companyName', 'COUNT(r.id) as reviewCount', 'AVG(r.rating) as averageRating')
            ->groupBy('r.companyName')
            ->orderBy('averageRating', 'DESC')
            ->addOrderBy('reviewCount', 'DESC') // Egyenlő átlag esetén a több értékelésű van előbb
            ->getQuery()
            ->getResult();
    }
}