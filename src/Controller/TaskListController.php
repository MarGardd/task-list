<?php

namespace App\Controller;

use App\Attribute\MapEntity;
use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Resolver\EntityValueResolver;
use App\Service\PaginationService;
use App\Service\TaskListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: "/api", name: "api_")]
class TaskListController extends AbstractController
{
    public function __construct(
        private readonly TaskListService $taskListService,
        private readonly SerializerInterface $serializer,
        private readonly PaginationService $paginationService,
        private readonly TaskListRepository $taskListRepository,
    ) {}

    #[Route(path: "/task-lists", name: 'get_task_lists', methods: ["GET"])]
    public function index(#[CurrentUser] ?User $currentUser, Request $request): JsonResponse
    {
        $pagination = $this->taskListRepository->findPaginatedTaskListsByUser(
            $currentUser,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        return $this->json(
            $this->paginationService->getPaginatonResult($pagination)
        );
    }

    #[Route(path: "/task-lists/{id}", name: 'get_task_list', methods: ["GET"])]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function show(
        #[MapEntity(resolver: EntityValueResolver::class, message: TaskList::NOT_FOUND_EXCEPTION_MESSAGE)]
        TaskList $taskList
    ): JsonResponse
    {
        return $this->json($this->getDeserializedTaskList($taskList));
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

        return $this->json([
            'message' => 'Task List created successfully',
            'data' => $this->getDeserializedTaskList($taskList)
        ], Response::HTTP_CREATED);
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

        return $this->json([
            'message' => 'Task List updated successfully',
            'data' => $this->getDeserializedTaskList($taskList)
        ]);
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

    private function getDeserializedTaskList(TaskList $taskList)
    {
        return json_decode($this->serializer->serialize($taskList, 'json'));
    }
}
