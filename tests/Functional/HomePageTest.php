<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testHomePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $button = $crawler->selectLink('Inscription');
        $this->assertEquals(1, count($button));

        $sections = $crawler->filter('section');
        $this->assertEquals(4, count($sections));

        $this->assertSelectorTextContains('h1', 'BuildGenius');
    }
}
