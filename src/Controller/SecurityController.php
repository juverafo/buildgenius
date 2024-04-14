<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\NewPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/security')]
class SecurityController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function signup(EntityManagerInterface $manager, Request $request, UserPasswordHasherInterface $passwordHasher, EmailService $emailService): Response
    {
        // Création d'une nouvelle instance de l'entité User
        $user = new User();

        // Création du formulaire d'inscription
        $form = $this->createForm(UserType::class, $user);

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);

        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération des données du formulaire    
            $user = $form->getData();

            // Encodage du mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));

            // Définition de l'état actif du compte sur 0 (non activé)
            $user->setActive(0);

            // Génération d'un token unique pour l'activation du compte
            $token = $this->generateToken();

            // Attribution du token à l'utilisateur
            $user->setToken($token);

            // Persistation des données de l'utilisateur
            $manager->persist($user);

            // Exécution de la transaction
            $manager->flush();

            // message de confirmation
            $this->addFlash('success', 'Votre compte a bien été créé, allez vite l\'activer');

            // Envoi d'un email de confirmation
            $emailService->sendEmail($user->getEmail(), 'Activez votre compte', '<p>Veuillez clicker sur le liens ci-dessous pour confirmer votre inscription</p><p>Si vous n\'êtes pas l\'origine de cette demande merci de ne pas prendre en considération cet email et nous excuser pour la gêne</p>', 'validate_account', 'Activer mon compte', $user, 'token', $this->getParameter('img_dir'));

            // Redirection vers la page de connexion avec un message de succès
            return $this->redirectToRoute('app_login');
        }

        // Affichage du formulaire d'inscription
        return $this->render('security/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }
    private function generateToken()
    {
        // rtrim supprime les espaces en fin de chaine de caractère
        // strtr remplace des occurences dans une chaine ici +/ et -_ (caractères récurent dans l'encodage en base64) par des = pour générer des url valides
        // ce token sera utilisé dans les envoie de mail pour l'activation du compte ou la récupération de mot de passe
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
    // méthode d'entrée au click du mail de validation du compte
    #[Route('/validate-account/{token}', name: 'validate_account')]
    public function validate_account($token, UserRepository $repository, EntityManagerInterface $manager): Response
    {
        // Recherche de l'utilisateur par son token
        $user = $repository->findOneBy(['token' => $token]);

        // Si l'utilisateur est trouvé, activation de son compte
        if ($user) {
            $user->setToken(null);
            $user->setActive(1);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Féliciation votre compte est à présent actif, connectez-vous!!!');
        } else {
            $this->addFlash('danger', 'Une erreur s\'est produite');
        }

        // Redirection vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
       // Gestion de la connexion utilisateur
        // Récupération des erreurs de connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupération du dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affichage du formulaire de connexion
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode sera interceptée par la configuration de sécurité pour gérer la déconnexion
        // Il n'est pas nécessaire de l'implémenter, mais doit être déclarée dans les routes pour fonctionner correctement
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // Méthode pour la réinitialisation du mot de passe
    #[Route('/reset-password', name: 'reset_password')]
    public function reset_password(Request $request, UserRepository $repository, EntityManagerInterface $manager, EmailService $emailService): Response
    {
        // Récupération de l'email saisi dans le formulaire de demande de réinitialisation de mot de passe (->request = $_POST, ->query = $_GET)
        $email = $request->request->get('email', '');

        if (!empty($email)) {
            // Recherche de l'utilisateur par son email
            $user = $repository->findOneBy(['email' => $email]);

            // Si l'utilisateur est trouvé et que son compte est actif, procéder à l'envoi de l'email de réinitialisation
            if ($user && $user->getActive() === 1) {
                $user->setActive(0);
                // Génération d'un token pour la réinitialisation du mot de passe
                $user->setToken($this->generateToken());
                $manager->persist($user);
                $manager->flush();
                $emailService->sendEmail($user->getEmail(), 'Mot de passe perdu?', '<p>Veuillez clicker sur le liens ci-dessous pour réinitaliser votre mot de passe</p><p>Si vous n\'êtes pas l\'origine de cette demande merci de ne pas prendre en considération cet email et nous excuser pour la gêne</p>', 'new_password', 'Réinitaliser le mot de passe', $user, 'token', $this->getParameter('img_dir'));

                // Redirection avec un message de succès
                $this->addFlash('success', "Un email de reset vous a été envoyé");

                return $this->redirectToRoute('app_home');
            }
        }
        // Affichage du formulaire de réinitialisation de mot de passe
        return $this->render('security/reset_password.html.twig');
    }
    #[Route('/password/new/{token}', name: 'new_password')]
    public function newPassword($token, UserRepository $repo, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        // Recherche de l'utilisateur par son token
        $user = $repo->findOneBy(['token' => $token]);

        if ($user) {
            // Création du formulaire de saisie du nouveau mot de passe
            $form = $this->createForm(NewPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Hashage du nouveau mot de passe
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $user->setActive(1);
                // Réinitialisation du token à null
                $user->setToken(null);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', "Votre mot de passe a bien été modifié");

                // Redirection vers la page de connexion
                return $this->redirectToRoute('app_login');
            }
        }
        // Affichage du formulaire de saisie du nouveau mot de passe
        return $this->render('security/newPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
