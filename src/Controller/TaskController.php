<?php

namespace App\Controller;

use App\Attribute\MapEntity;
use App\Dto\TaskDto;
use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Resolver\EntityValueResolver;
use App\Service\PaginationService;
use App\Service\TaskService;
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
class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly SerializerInterface $serializer,
        private readonly TaskRepository $taskRepository,
        private readonly PaginationService $paginationService
    ) {}

    #[Route(path: "/tasks", name: 'get_tasks', methods: ["GET"])]
    public function index(#[CurrentUser] ?User $currentUser, Request $request): JsonResponse
    {
        $pagination = $this->taskRepository->findPaginatedTasksByUser(
            $currentUser,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 5)
        );

        return $this->json(
            $this->paginationService->getPaginatonResult($pagination)
        );
    }

    #[Route(path: "/tasks/{id}", name: 'get_task', methods: ["GET"], format: 'json')]
    #[IsGranted('OWN', 'task', message: 'Access Denied')]
    public function show(
        #[MapEntity(resolver: EntityValueResolver::class, message: Task::NOT_FOUND_EXCEPTION_MESSAGE)]
        Task $task
    ): JsonResponse
    {
        return $this->json($this->getDeserializedTask($task));
    }

    #[Route(path: "/task-lists/{id}/tasks", name: 'create_task', methods: ["POST"])]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function create(
        #[MapEntity(
            resolver: EntityValueResolver::class,
            message: TaskList::NOT_FOUND_EXCEPTION_MESSAGE
        )] TaskList $taskList,
        #[MapRequestPayload(
            validationGroups: ['create']
        )] TaskDto $task,
    ): JsonResponse
    {
        $task = $this->taskService->createTask($task, $taskList);

        return $this->json([
            'message' => 'Task created successfully',
            'data' => $this->getDeserializedTask($task)
        ], Response::HTTP_CREATED);
    }

    #[Route(path: "/task-lists/{task_list_id}/tasks/{id}", name: 'update_task', methods: ["PUT"], format: 'json')]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function update(
        #[MapEntity(
            expr: 'repository.find(task_list_id)',
            resolver: EntityValueResolver::class,
            message: TaskList::NOT_FOUND_EXCEPTION_MESSAGE
        )] TaskList $taskList,
        #[MapRequestPayload(validationGroups: ['update'])]
        TaskDto $taskDto,
        #[MapEntity(
            expr: 'repository.findOneBy({"id": id, "task_list_id": task_list_id})',
            resolver: EntityValueResolver::class,
            message: Task::NOT_FOUND_EXCEPTION_MESSAGE
        )] Task $task,
    ): JsonResponse
    {
        $this->taskService->updateTask($task, $taskDto);

        return $this->json([
            'message' => 'Task updated successfully',
            'data' => $this->getDeserializedTask($task)
        ]);
    }

    #[Route(path: "/task-lists/{task_list_id}/tasks/{id}", name: 'delete_task', methods: ["DELETE"])]
    #[IsGranted('OWN', 'task', message: 'Access Denied')]
    #[IsGranted('OWN', 'taskList', message: 'Access Denied')]
    public function delete(
        #[MapEntity(
            expr: 'repository.find(task_list_id)',
            resolver: EntityValueResolver::class,
            message: TaskList::NOT_FOUND_EXCEPTION_MESSAGE
        )]
        TaskList $taskList,
        #[MapEntity(
            expr: 'repository.findOneBy({"id": id, "task_list_id": task_list_id})',
            resolver: EntityValueResolver::class,
            message: Task::NOT_FOUND_EXCEPTION_MESSAGE
        )]
        Task $task
    ): JsonResponse
    {
        $this->taskService->deleteTask($task);

        return $this->json(['message' => 'Task deleted successfully']);
    }

    private function getDeserializedTask(Task $task)
    {
        return json_decode($this->serializer->serialize($task, 'json', [
            'attributes' => ['id', 'title', 'description', 'taskListId']
        ]));
    }
}
