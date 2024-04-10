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
        // on crée une instance de la classe User et à laquelle on passe ces valeurs
        $user = new User();

        // génération du formulaire à partir de la classe UserType(qui est lié à la classe User)
        $form = $this->createForm(UserType::class, $user);

        // ici on va gérer la requête entrante
        $form->handleRequest($request);

        // si le form est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // on récupère les valeurs du formulaire    
            $user = $form->getData();

            // Encode le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));

            // set de sa prop active à 0
            $user->setActive(0);

            // on appelle la méthode generateToken juste en dessous pour générer une chaine de caractère aléatoire et unique
            $token = $this->generateToken();

            // on l'affecte à notre utilisateur
            $user->setToken($token);

            // on persiste les valeurs (l'ordre n'est pas important avant persiste())
            $manager->persist($user);

            // on exécute la transaction
            $manager->flush();

            // message de confirmation
            $this->addFlash('success', 'Votre compte a bien été créé, allez vite l\'activer');

            // on prépare l'email
            $emailService->sendEmail($user->getEmail(), 'Activez votre compte', '<p>Veuillez clicker sur le liens ci-dessous pour confirmer votre inscription</p><p>Si vous n\'êtes pas l\'origine de cette demande merci de ne pas prendre en considération cet email et nous excuser pour la gêne</p>', 'validate_account', 'Activer mon compte', $user, 'token', $this->getParameter('img_dir'));

            // ensuite on redirige vers la route app_login
            return $this->redirectToRoute('app_login');
        }

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
        // on va requeter un user sur son token
        $user = $repository->findOneBy(['token' => $token]);

        // si on a un résultat, on passe sa propriété active à 1, son token à null et on persiste, execute(flush) et redirige sur la page de connexion avec un message de success

        if ($user) {
            $user->setToken(null);
            $user->setActive(1);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Féliciation votre compte est à présent actif, connectez-vous!!!');
        } else {
            $this->addFlash('danger', 'Une erreur s\'est produite');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // méthode pour mot de passe oublié pour accéder au formulaire demandant la saisie de l'email et générer l'envoie d'un mail de réinitialisation

    #[Route('/reset-password', name: 'reset_password')]
    public function reset_password(Request $request, UserRepository $repository, EntityManagerInterface $manager, EmailService $emailService): Response
    {
        // récupération de la saisie formulaire ->request = $_POST, ->query = $_GET
        $email = $request->request->get('email', '');

        if (!empty($email)) {
            // requete de user par son email
            $user = $repository->findOneBy(['email' => $email]);

            // si on a utilisateur et que son compte est actif on procède à l'envoie de l'email de récupération
            if ($user && $user->getActive() === 1) {
                $user->setActive(0);
                // on génère un token
                $user->setToken($this->generateToken());
                $manager->persist($user);
                $manager->flush();
                $emailService->sendEmail($user->getEmail(), 'Mot de passe perdu?', '<p>Veuillez clicker sur le liens ci-dessous pour réinitaliser votre mot de passe</p><p>Si vous n\'êtes pas l\'origine de cette demande merci de ne pas prendre en considération cet email et nous excuser pour la gêne</p>', 'new_password', 'Réinitaliser le mot de passe', $user, 'token', $this->getParameter('img_dir'));

                $this->addFlash('success', "Un email de reset vous a été envoyé");

                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('security/reset_password.html.twig', []);
    }
    #[Route('/password/new/{token}', name: 'new_password')]
    public function newPassword($token, UserRepository $repo, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        // on récupère un user par son token
        $user = $repo->findOneBy(['token' => $token]);

        if ($user) {
            $form = $this->createForm(NewPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // on hash le nouveau mdp
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $user->setActive(1);
                // on repasse le token à null
                $user->setToken(null);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', "Votre mot de passe a bien été modifié");

                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/newPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
