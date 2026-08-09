<?php

/*
 * @author Attila HOFFEREK <azhofi@gmail.com>
 */

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional test that implements a "smoke test" of all the public and secure
 * URLs of the application.
 */
final class DefaultControllerTest extends WebTestCase
{
    /**
     * PHPUnit's data providers allow to execute the same tests repeated times
     * using a different set of data each time.
     * See https://symfony.com/doc/current/testing.html#testing-against-different-sets-of-data.
     */
    #[DataProvider('getPublicUrls')]
    public function testPublicUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful(\sprintf('The %s public URL loads correctly.', $url));
    }

    public static function getPublicUrls(): \Generator
    {
        yield ['/'];
        yield ['/en/new'];
        yield ['/en/search'];
        yield ['/en/companies'];
        yield ['/en/show/1'];
        yield ['/en/edit/1'];
    }
}
