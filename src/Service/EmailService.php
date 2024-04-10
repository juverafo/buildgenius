<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;

class EmailService
{


    private $mailer;


    public function __construct( MailerInterface $mailer)
    {
        $this->mailer=$mailer;

    }


    public function sendEmail($to, $title, $content, $route, $button, $user, $paramName=null, $imgDir=null)
    {
        $email = (new TemplatedEmail())
            ->from('arakelyan11@gmail.com')
            ->to($to)
            ->addPart((new DataPart(fopen( $imgDir.'/logo_buildgenius.png', 'r'), 'logo_buildgenius', 'image/png'))->asInline())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($title)
            ->htmlTemplate('email/validateAccount.html.twig')
            ->context([
                'user' => $user,
                'content'=>$content,
                'title'=>$title,
                'route'=>$route,
                'button'=>$button,
                'param'=>$user->getToken(),
                'paramName'=>$paramName
            ]);
        // $mailer->IsSMTP();
        try {
            $this->mailer->send($email);
        }catch (TransportExceptionInterface $e){

            dd($e);


        }

    }

}