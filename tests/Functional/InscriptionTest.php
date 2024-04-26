<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class InscriptionTest extends WebTestCase
{
    public function testInscription(): void
    {
        // $client = static::createClient();
        // $crawler = $client->request('GET', '/security/signup');

        // $this->assertResponseIsSuccessful();
        // $this->assertSelectorTextContains('h1', 'Inscription');

        // // Récupérer le formulaire
        // $submitButton =  $crawler->selectButton('Valider');
        // $form = $submitButton->form();

        // $form["user[email]"] = "inesberbertest@gmail.com";

        // $form["user[password][first]"] = "qwerty123";

        // $form["user[password][second]"] = "qwerty123";

        // $form["user[agreeTerms]"] = true;

        // // Soumettre le formulaire
        // $client->submit($form);
        // // Vérifier le statut HTTP
        // $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        // // Vérifier l'envoie du mail
        // $this->assertEmailCount(1);

        // $client->followRedirect();
    }
}
