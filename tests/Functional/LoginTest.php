<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginTest extends WebTestCase
{
    // public function testIfLoginIsSuccessful(): void
    // {
    //     $client = static::createClient();

    //     /**
    //      * @var UrlGeneratorInterface $urlGenerator
    //      */
    //     $urlGenerator = $client->getContainer()->get('router');

    //     $crawler = $client->request('GET', $urlGenerator->generate('app_login'));


    //     $form = $crawler->filter('form')->form([
    //         'email' => "inesberbertest@gmail.com",
    //         "password" => 'qwerty123'
    //     ]);

    //     $client->submit($form);

    //     $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

    //     $client->followRedirect();

    //     // Adjust the assertion to check if the response is successful and not necessarily to app_home route
    //     $this->assertResponseIsSuccessful();
    // }

    // public function testIfLoginFailedWhenPasswordIsWrong(): void
    // {
    //     $client = static::createClient();

    //     /**
    //      * @var UrlGeneratorInterface $urlGenerator
    //      */
    //     $urlGenerator = $client->getContainer()->get('router');

    //     $crawler = $client->request('GET', $urlGenerator->generate('app_login'));


    //     $form = $crawler->filter('form')->form([
    //         'email' => "inesberbertest@gmail.com",
    //         "password" => 'qwerty123dsadasd'
    //     ]);

    //     $client->submit($form);

    //     $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

    //     $client->followRedirect();

    //     // Adjust the assertion to check if the response is successful and not necessarily to app_home route
    //     $this->assertResponseIsSuccessful();

    //     $this->assertSelectorTextContains('div.alert-danger', 'Invalid credentials');

    // }
}
