<?php

namespace App\Controller;

use App\Entity\User;
use App\Message\SendEmailMessage;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface          $validator,
        private readonly EmailService $emailService,
        private readonly MessageBusInterface $messageBus,
    ) {}

    #[Route('/api/register', name: 'app_register', methods: ['POST'], format: 'json')]
    public function register(Request $request): Response
    {
        $user = new User();
        $payload = $request->getPayload();
        $user->setEmail($payload->get('email', ''));
        $user->setPassword(
            $payload->get('password')
                ? $this->passwordHasher->hashPassword($user, $payload->get('password'))
                : ''
        );
        if($response = $this->validateRegistration($user)){
            return $response;
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
//        $this->emailService->sendEmailConfirmation($user);
        $this->messageBus->dispatch(new SendEmailMessage($user));
        return new JsonResponse([
            'message' => 'A confirmation email has been sent to your email address.',
        ], Response::HTTP_CREATED);
    }

    #[Route('api/verify/email', name: 'app_verify_email', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $id = $request->query->getInt('id');
        if(!$id) {
            return new JsonResponse([
                'error' => 'Invalid verification link.',
            ], Response::HTTP_BAD_REQUEST);
        }
        $user = $userRepository->find($id);
        if (null === $user) {
            return new JsonResponse([
                'error' => 'User not found.',
            ], Response::HTTP_NOT_FOUND);
        }
        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            return new JsonResponse([
                'error' => $e->getReason(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Your email address has been verified.',
        ]);
    }

    private function validateRegistration(User $user): ?JsonResponse
    {

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = array_map(fn($error) => $error->getMessage(), iterator_to_array($errors));
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return null;
    }
}
