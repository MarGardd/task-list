<?php

namespace App\Security;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class CustomJWTAuthenticator extends JWTAuthenticator implements AuthenticationEntryPointInterface
{
    public function authenticate(Request $request): Passport
    {
        $passport = parent::authenticate($request);
        $user = $passport->getUser();
        if(!$user instanceof User || !$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('Please verify your email address.');
        }

        return $passport;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse([
            'error' => $exception->getMessageKey(),
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return new JsonResponse([
            'error' => 'Authentication required.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}