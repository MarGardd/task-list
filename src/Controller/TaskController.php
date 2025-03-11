<?php

namespace App\Controller;

use App\Dto\TaskDto;
use App\Entity\Task;
use App\Entity\User;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: "/api", name: "api_")]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    #[Route(path: "/tasks", name: 'get_tasks', methods: ["GET"])]
    public function index(#[CurrentUser] ?User $currentUser): JsonResponse
    {
        $tasks = $this->taskService->getTasksByUser($currentUser);

        return $this->json($tasks);
    }

    #[Route(path: "/tasks/{id}", name: 'get_task', methods: ["GET"])]
    public function show(#[CurrentUser] ?User $currentUser, int $id): JsonResponse
    {
        $task = $this->taskService->getTaskById($id, $currentUser);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(new TaskDto($task));
    }

    #[Route(path: "/tasks", name: 'create_task', methods: ["POST"])]
    public function create(#[CurrentUser]?User $user, Request $request): JsonResponse
    {
        $result = $this->taskService->createTask($user, $request->getPayload()->all());
        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result, Response::HTTP_CREATED);
    }

    #[Route(path: "/tasks/{id}", name: 'update_task', methods: ["PUT"])]
    public function update(#[CurrentUser] ?User $user, Request $request, Task $task = null): JsonResponse
    {
        if (!$task || $task->getTaskList()->getUserId() !== $user->getId()) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }
        $result = $this->taskService->updateTask($task, $request->query->all());
        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result);
    }

    #[Route(path: "/tasks/{id}", name: 'delete_task', methods: ["DELETE"])]
    public function delete(#[CurrentUser] ?User $user, Task $task = null): JsonResponse
    {
        if (!$task || $task->getTaskList()->getUserId() !== $user->getId()) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }
        $result = $this->taskService->deleteTask($task);

        return $this->json($result);
    }
}
