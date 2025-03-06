<?php

namespace App\Controller;

use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: "/api", name: "api_")]
class TaskListController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface    $serializer,
        private readonly ValidatorInterface     $validator
    )
    {}

    #[Route(path: "/task-lists", name: 'get_task_lists', methods: ["GET"])]
    public function index(#[CurrentUser] ?User $currentUser, TaskListRepository $taskListRepository): JsonResponse
    {
        $lists = $taskListRepository->findBy(['user' => $currentUser]);
        $data = json_decode($this->serializer->serialize($lists, 'json'));

        return $this->json($data);
    }

    #[Route(path: "/task-lists/{id}", name: 'get_task_list', methods: ["GET"])]
    public function show(#[CurrentUser] ?User $currentUser, TaskList $taskList = null): JsonResponse
    {
        if (!$taskList || !($currentUser->getId() === $taskList->getUserId())) {
            return $this->json(['error' => 'Task List not found'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($this->serializer->serialize($taskList, 'json'), true);

        return $this->json($data);
    }

    #[Route(path: "/task-lists", name: 'create_task_list', methods: ["POST"])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        $errors = $this->validateRequest($request->getPayload()->all());
        if (count($errors) > 0) {
            return $this->handleValidationErrors($errors);
        }
        $taskListUser = $userRepository->find($request->get('user_id')) ?? null;

        $taskList = $this->initializeTaskList(new TaskList(), $request->get('title'), $taskListUser);
        return $this->saveTaskList($taskList, "Task List created successfully");
    }

    #[Route(path: "/task-lists/{id}", name: 'update_task_list', methods: ["PUT"])]
    public function update(Request $request, TaskList $taskList = null): JsonResponse
    {
        $title = $request->get('title');
        if(!$title) {
            return $this->json(['error' => 'Title is required'], Response::HTTP_NOT_FOUND);
        }
        $this->initializeTaskList($taskList, $request->get('title'));
        return $this->saveTaskList($taskList, 'Task List updated successfully');
    }

    #[Route(path: "/task-lists/{id}", name: 'delete_task_list', methods: ["DELETE"])]
    public function delete(TaskList $taskList = null): JsonResponse
    {
        $this->entityManager->remove($taskList);
        $this->entityManager->flush();

        return $this->json(['message' => 'Task List deleted successfully']);
    }

    private function saveTaskList(TaskList $taskList, string $message): JsonResponse
    {
        $this->entityManager->persist($taskList);
        $this->entityManager->flush();

        return $this->json(['message' => $message, 'id' => $taskList->getId()], Response::HTTP_CREATED);
    }

    private function validateRequest(array $requestData): ConstraintViolationListInterface
    {
        $taskListDto = $this->serializer->deserialize(
            json_encode($requestData),
            TaskListDto::class,
            'json'
        );

        return $this->validator->validate($taskListDto);
    }

    private function initializeTaskList(TaskList $taskList, string $title, ?User $taskListUser = null): TaskList
    {
        $taskList->setTitle($title);
        if($taskListUser) {
            $taskList->setUser($taskListUser);
        }

        return $taskList;
    }

    private function handleValidationErrors($errors): JsonResponse
    {
        $errorMessages = array_map(fn($error) => $error->getMessage(), iterator_to_array($errors));
        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }
}
