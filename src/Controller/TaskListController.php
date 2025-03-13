<?php

namespace App\Controller;

use App\Attribute\MapEntity;
use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;

use App\Resolver\EntityValueResolver;
use App\Service\TaskListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function show(
        #[MapEntity(resolver: EntityValueResolver::class, message: 'Task list not found')] TaskList $taskList
    ): JsonResponse
    {
        return $this->json(new TaskListDto($taskList));
    }

    #[Route(path: "/task-lists", name: 'create_task_list', methods: ["POST"])]
    public function create(
        #[MapRequestPayload(
            validationGroups: ['create']
        )] TaskListDto $taskList,
    ): JsonResponse
    {
        $taskListId = $this->taskListService->createTaskList($taskList);

        return $this->json(['message' => 'Task List created successfully', 'id' => $taskListId], Response::HTTP_CREATED);
    }

    #[Route(path: "/task-lists/{id}", name: 'update_task_list', methods: ["PUT"], format: 'json')]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function update(
        #[MapRequestPayload(
            validationGroups: ['update']
        )] TaskListDto $taskListDto,
        #[MapEntity(resolver: EntityValueResolver::class, message: 'Task list not found')] TaskList $taskList
    ): JsonResponse
    {
        $this->taskListService->updateTaskList($taskList, $taskListDto);

        return $this->json(['message' => 'Task List updated successfully']);
    }

    #[Route(path: "/task-lists/{id}", name: 'delete_task_list', methods: ["DELETE"])]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function delete(
        #[MapEntity(resolver: EntityValueResolver::class, message: 'Task list not found')] TaskList $taskList
    ): JsonResponse
    {
        $this->taskListService->deleteTaskList($taskList);

        return $this->json(['message' => 'Task List deleted successfully']);
    }
}
