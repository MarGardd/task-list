<?php

namespace App\Controller;

use App\Dto\RegistrationDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SerializerInterface         $serializer,
        private readonly ValidatorInterface          $validator
    ) {}

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $registrationDto = $this->serializer->deserialize(
            json_encode($request->getPayload()->all()),
            RegistrationDto::class,
            'json'
        );
        if ($validationResult = $this->validateRegistrationDto($registrationDto)) {
            return $validationResult;
        }
        $user = new User();
        $user->setEmail($registrationDto->email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $registrationDto->password)
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'User registered successfully',
            'userId' => $user->getId(),
        ], Response::HTTP_CREATED);
    }

    private function validateRegistrationDto(RegistrationDto $registrationDto): ?JsonResponse
    {
        $errors = $this->validator->validate($registrationDto);
        if (count($errors) > 0) {
            $errorMessages = array_map(fn($error) => $error->getMessage(), iterator_to_array($errors));
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }
}
