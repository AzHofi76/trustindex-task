<?php

/*
 * @author Attila HOFFEREK <azhofi@gmail.com>
 */

namespace App\Tests\Repository;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReviewRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?ReviewRepository $reviewRepository = null;

    protected function setUp(): void
    {
        // 1. Elindítjuk a Symfony kernelt
        $kernel = self::bootKernel();

        // 2. Lekérjük az EntityManager-t és a ReviewRepository-t a szerviz konténerből
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->reviewRepository = $this->entityManager->getRepository(Review::class);
    }

    /**
     * A getCompanyStatistics() egyedi DQL/QueryBuilder metódus tesztelése
     */
    public function testGetCompanyStatistics(): void
    {
        // 1. Tesztadatok előkészítése: létrehozunk értékeléseket több céghez

        // "Alpha Kft." - 2 értékelés: 5 és 3 csillag (Átlag: 4.0)
        $review1 = new Review();
        $review1->setCompanyName('Alpha Kft.')
            ->setRating(5)
            ->setAuthorEmail('user1@example.com');

        $review2 = new Review();
        $review2->setCompanyName('Alpha Kft.')
            ->setRating(3)
            ->setAuthorEmail('user2@example.com');

        // "Beta Kft." - 1 értékelés: 5 csillag (Átlag: 5.0) -> Ennek kell az I. helyen lennie!
        $review3 = new Review();
        $review3->setCompanyName('Beta Kft.')
            ->setRating(5)
            ->setAuthorEmail('user3@example.com');

        $this->entityManager->persist($review1);
        $this->entityManager->persist($review2);
        $this->entityManager->persist($review3);
        $this->entityManager->flush();

        // 2. Meghívjuk a tesztelendő repository metódust
        $stats = $this->reviewRepository->getCompanyStatistics();

        // 3. Ellenőrzések (Assertions)

        // Ellenőrizzük, hogy pontosan 2 cég szerepel az eredményben (mivel csoportosítottunk)
        $this->assertCount(3, $stats);

        // Első helyezettnek a Beta Kft.-nek kell lennie (magasabb átlag: 5.0)
        $this->assertSame('Beta Kft.', $stats[0]['companyName']);
        $this->assertEquals(1, $stats[0]['reviewCount']);
        $this->assertEquals(5.0, $stats[0]['averageRating']);

        // Második helyezett az Alpha Kft. (átlag: 4.0)
        $this->assertSame('Alpha Kft.', $stats[1]['companyName']);
        $this->assertEquals(2, $stats[1]['reviewCount']);
        $this->assertEquals(4.0, $stats[1]['averageRating']);
    }

    /**
     * Entitás mentésének és lekérdezésének alapvető tesztelése
     */
    public function testSaveAndFindReview(): void
    {
        $review = new Review();
        $review->setCompanyName('Gamma Inc.')
            ->setRating(4)
            ->setAuthorEmail('gamma@example.com')
            ->setReviewText('Szuper szolgáltatás!');

        $this->entityManager->persist($review);
        $this->entityManager->flush();

        // Lekérjük az adatbázisból az ID alapján
        $savedReview = $this->reviewRepository->find($review->getId());

        $this->assertNotNull($savedReview);
        $this->assertSame('Gamma Inc.', $savedReview->getCompanyName());
        $this->assertSame(4, $savedReview->getRating());
        $this->assertNotNull($savedReview->getCreatedAt()); // Ellenőrizzük a PrePersist lifecycle callbacket
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // A memória szivárgások (memory leak) elkerülése érdekében lezárjuk az EntityManager-t
        if ($this->entityManager !== null) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}