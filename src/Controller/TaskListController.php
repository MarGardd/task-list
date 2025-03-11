<?php

namespace App\Controller;

use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;;
use App\Service\TaskListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: "/api", name: "api_")]
class TaskListController extends AbstractController
{
    public function __construct(
        private readonly TaskListService $taskListService
    ) {}

    #[Route(path: "/task-lists", name: 'get_task_lists', methods: ["GET"])]
    public function index(#[CurrentUser] ?User $currentUser): JsonResponse
    {
        $taskLists = $this->taskListService->getTaskListsForUser($currentUser);

        return $this->json($taskLists);
    }

    #[Route(path: "/task-lists/{id}", name: 'get_task_list', methods: ["GET"])]
    public function show(#[CurrentUser] ?User $currentUser, int $id): JsonResponse
    {
        $taskList = $this->taskListService->getTaskListById($id, $currentUser);
        if (!$taskList) {
            return $this->json(['error' => 'Task List not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(new TaskListDto($taskList));
    }

    #[Route(path: "/task-lists", name: 'create_task_list', methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $result = $this->taskListService->createTaskList($request->getPayload()->all());
        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result, Response::HTTP_CREATED);
    }

    #[Route(path: "/task-lists/{id}", name: 'update_task_list', methods: ["PUT"])]
    public function update(Request $request, TaskList $taskList = null): JsonResponse
    {
        if (!$taskList) {
            return $this->json(['error' => 'Task list not found'], Response::HTTP_NOT_FOUND);
        }
        $result = $this->taskListService->updateTaskList($taskList, $request->query->all());
        if (isset($result['errors'])) {
            return $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result);
    }

    #[Route(path: "/task-lists/{id}", name: 'delete_task_list', methods: ["DELETE"])]
    public function delete(TaskList $taskList = null): JsonResponse
    {
        if (!$taskList) {
            return $this->json(['error' => 'Task list not found'], Response::HTTP_NOT_FOUND);
        }
        $result = $this->taskListService->deleteTaskList($taskList);

        return $this->json($result);
    }
}
