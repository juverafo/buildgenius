<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;

class EmailService
{
    private $mailer;

    // Le constructeur permet d'injecter automatiquement le service MailerInterface lors de l'instanciation du service dans les contrôleurs.
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    // Méthode pour envoyer un email
    public function sendEmail($to, $title, $content, $route, $button, $user, $paramName = null, $imgDir = null)
    {
        // Création d'un nouvel email basé sur un modèle Twig
        $email = (new TemplatedEmail())
            ->from('arakelyan11@gmail.com') // Adresse email de l'expéditeur
            ->to($to) // Adresse email du destinataire
            ->addPart((new DataPart(fopen($imgDir . '/logo_buildgenius.png', 'r'), 'logo_buildgenius', 'image/png'))->asInline()) // Ajout d'une pièce jointe (logo) en tant qu'image en ligne
            ->subject($title) // Objet de l'email
            ->htmlTemplate('email/validateAccount.html.twig') // Template Twig pour le contenu HTML de l'email
            ->context([
                'user' => $user, // Données de l'utilisateur à transmettre au template Twig
                'content' => $content, // Contenu supplémentaire à transmettre au template Twig
                'title' => $title, // Titre de l'email
                'route' => $route, // Route à utiliser dans le lien du bouton
                'button' => $button, // Texte du bouton dans l'email
                'param' => $user->getToken(), // Paramètre à transmettre dans l'URL du lien du bouton
                'paramName' => $paramName // Nom du paramètre dans l'URL du lien du bouton
            ]);

        // Envoi de l'email
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            // En cas d'erreur lors de l'envoi, affichage du message d'erreur
            dd($e);
        }
    }
}
