<?php

/*
 * @author Attila HOFFEREK <azhofi@gmail.com>
 */

namespace App\Tests\Controller;

use App\Pagination\Paginator;
use App\Entity\Review;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReviewControllerTest extends WebTestCase
{
    private $entityManager;
    private $client;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient(); //kernel bootolás
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        $review = new Review();
        $review->setCompanyName('Teszt cég kft.')
            ->setRating(4)
            ->setReviewText('Nagyon jó cég')
            ->setAuthorEmail('teszt@example.com');

        $this->entityManager->persist($review);
        $this->entityManager->flush();
    }

    public function testInitialDataExists(): void
    {
        $review = $this->entityManager->getRepository(Review::class)->findOneBy([
            'authorEmail' => 'teszt@example.com'
        ]);

        $this->assertNotNull($review);
        $this->assertSame('Teszt cég kft.', $review->getCompanyName());
        $this->assertSame(4, $review->getRating());
        $this->assertSame('Nagyon jó cég', $review->getReviewText());
    }

    public function testIndex(): void
    {
        $client = $this->client;
        $crawler = $client->request('GET', '/en');

        $this->assertResponseIsSuccessful();

        $this->assertCount(
            1,
            $crawler->filter('article'),
            'The page displays the right number of reviews.'
        );
    }

    public function testCreateNewReview(): void
    {
        $client = $client = $this->client;

        $crawler = $client->request('GET', '/en/new');
        $this->assertResponseIsSuccessful();

        $client->submitForm('Értékelés beküldése', [
            'review[companyName]' => 'Teszt Cég Kft.',
            'review[rating]'      => 5,
            'review[authorEmail]' => 'teszt@example.com',
            'review[reviewText]'  => 'Nagyon elégedett voltam a szolgáltatással!',
        ]);
        $this->assertResponseRedirects();
        
        $crawler = $client->request('GET', '/en');
        $this->assertSelectorTextContains('body', 'Teszt Cég Kft.');

        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        
        $review = $em->getRepository(Review::class)->findOneBy([
            'authorEmail' => 'teszt@example.com'
        ]);

        $this->assertNotNull($review);
        $this->assertSame(4, $review->getRating());
    }
    
    public function testEditExistingReview(): void
    {
        $client = $client = $this->client;
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        $review = new Review();
        $review->setCompanyName('Eredeti Cég Kft.');
        $review->setRating(2);
        $review->setAuthorEmail('szerkeszto@example.com');
        $review->setReviewText('Gyenge volt.');

        $em->persist($review);
        $em->flush();

        $client->request('GET', sprintf('/en/edit/%d', $review->getId()));
        $this->assertResponseIsSuccessful();

        $client->submitForm('Módosítások mentése', [
            'review[companyName]' => 'Módosított Cég Kft.',
            'review[rating]'      => 4,
            'review[authorEmail]' => 'szerkeszto@example.com',
            'review[reviewText]'  => 'Kijavították a hibát, így már jobb!',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();

        $em->clear();
        $updatedReview = $em->getRepository(Review::class)->find($review->getId());

        $this->assertSame('Módosított Cég Kft.', $updatedReview->getCompanyName());
        $this->assertSame(4, $updatedReview->getRating());
        $this->assertSame('Kijavították a hibát, így már jobb!', $updatedReview->getReviewText());
    }

    public function testAjaxSearch(): void
    {
        $client = $client = $this->client;
        $crawler = $client->request('GET', '/en/search', ['q' => 'Kft']);

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('article.post'));
        $this->assertSame('★ ★ ★ ★ ☆ Teszt cég kft.', $crawler->filter('article.post')->first()->filter('h2 > a')->text());
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager !== null) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}
