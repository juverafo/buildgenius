<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    // Méthode pour authentifier l'utilisateur
    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        // Stocke le dernier email utilisé dans la session
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Crée un "passeport" avec les informations de connexion de l'utilisateur
        return new Passport(
            new UserBadge($email), // Badge pour identifier l'utilisateur par son email
            new PasswordCredentials($request->request->get('password', '')), // Badge pour les mots de passe
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')) // Badge pour le jeton CSRF
            ]
            );
    }

    // Méthode exécutée après une connexion réussie
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirige l'utilisateur vers la page précédemment visitée (s'il y en a une)
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Si l'utilisateur est administrateur, redirige vers la page d'administration
        if (in_array('ROLE_ADMIN',$token->getUser()->getRoles())){
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        } else { // Sinon, redirige vers la page d'accueil
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }
    }

    // Méthode pour obtenir l'URL de connexion
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
