<?php

namespace App\Controller;

use App\Attribute\MapEntity;
use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Resolver\EntityValueResolver;
use App\Response\ApiResponse;
use App\Service\TaskListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: "/api", name: "api_")]
class TaskListController extends AbstractController
{
    public function __construct(
        private readonly TaskListService $taskListService,
        private readonly TaskListRepository $taskListRepository,
        private readonly ApiResponse $apiResponse
    ) {}

    #[Route(path: "/task-lists", name: 'get_task_lists', methods: ["GET"])]
    public function index(#[CurrentUser] ?User $currentUser, Request $request): JsonResponse
    {

        $pagination = $this->taskListRepository->findPaginatedTaskListsByUser(
            $currentUser,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        return $this->apiResponse->create($pagination, groups: ['task_list', 'tasks']);
    }

    #[Route(path: "/task-lists/all", name: 'get_all_task_lists', methods: ["GET"])]
    public function getAllTaskLists(#[CurrentUser] ?User $currentUser): JsonResponse
    {
        $taskLists = $this->taskListService->getAllTaskListsByUser($currentUser);

        return $this->apiResponse->create($taskLists, groups: ['task_list', 'tasks']);
    }

    #[Route(path: "/task-lists/{id}", name: 'get_task_list', methods: ["GET"])]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function show(
        #[MapEntity(resolver: EntityValueResolver::class, message: TaskList::NOT_FOUND_EXCEPTION_MESSAGE)]
        TaskList $taskList
    ): JsonResponse
    {
        return $this->apiResponse->create($taskList, groups: ['task_list', 'tasks']);
    }

    #[Route(path: "/task-lists", name: 'create_task_list', methods: ["POST"])]
    public function create(
        #[CurrentUser] User $user,
        #[MapRequestPayload(
            validationGroups: ['create']
        )] TaskListDto $taskList,
    ): JsonResponse
    {
        $taskList = $this->taskListService->createTaskList($taskList, $user);

        return $this->apiResponse->create(
            $taskList,
            'Task List created successfully',
            status: Response::HTTP_CREATED,
            groups: ['task_list']
        );
    }

    #[Route(path: "/task-lists/{id}", name: 'update_task_list', methods: ["PUT"], format: 'json')]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function update(
        #[MapRequestPayload(
            validationGroups: ['update']
        )] TaskListDto $taskListDto,
        #[MapEntity(
            resolver: EntityValueResolver::class, message: TaskList::NOT_FOUND_EXCEPTION_MESSAGE
        )] TaskList $taskList
    ): JsonResponse
    {
        $this->taskListService->updateTaskList($taskList, $taskListDto);

        return $this->apiResponse->create(
            $taskList,
            'Task List updated successfully',
            groups: ['task_list']
        );
    }

    #[Route(path: "/task-lists/{id}", name: 'delete_task_list', methods: ["DELETE"])]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function delete(
        #[MapEntity(resolver: EntityValueResolver::class, message: TaskList::NOT_FOUND_EXCEPTION_MESSAGE)] TaskList $taskList
    ): JsonResponse
    {
        $this->taskListService->deleteTaskList($taskList);

        return $this->json(['message' => 'Task List deleted successfully']);
    }
}
