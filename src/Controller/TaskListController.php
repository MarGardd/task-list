<?php

namespace App\Controller;

use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: "/api", name: "api_")]
class TaskListController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    #[Route(path: "/task-lists", name: 'get_task_lists', methods: ["GET"])]
    public function index(TaskListRepository $taskListRepository, SerializerInterface $serializer): JsonResponse
    {
        $lists = $taskListRepository->findAll();
        $data = json_decode($serializer->serialize($lists, 'json'));

        return $this->json($data);
    }

    #[Route(path: "/task-lists/{id}", name: 'get_task_list', methods: ["GET"])]
    public function show(SerializerInterface $serializer, TaskList $taskList = null): JsonResponse
    {
        if (!$taskList) {
            return $this->json(['error' => 'Task List not found'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($serializer->serialize($taskList, 'json'), true);

        return $this->json($data);
    }

    #[Route(path: "/task-lists", name: 'create_task_list', methods: ["POST"])]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $taskList = new TaskList();
        $taskList->setTitle($request->get('title') ?? '');
        return $this->validateAndPersist($taskList, $validator);
    }

    #[Route(path: "/task-lists/{id}", name: 'update_task_list', methods: ["PUT"])]
    public function update(Request $request, ValidatorInterface $validator, TaskList $taskList = null): JsonResponse
    {
        if (!$taskList) {
            return $this->json(['error' => 'Task List not found'], Response::HTTP_NOT_FOUND);
        }
        $taskList->setTitle($request->get('title') ?? '');
        return $this->validateAndPersist($taskList, $validator);
    }

    #[Route(path: "/task-lists/{id}", name: 'delete_task_list', methods: ["DELETE"])]
    public function delete(TaskList $taskList = null): JsonResponse
    {
        if (!$taskList) {
            return $this->json(['error' => 'Task List not found'], Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($taskList);
        $this->entityManager->flush();

        return $this->json(['message' => 'Task List deleted successfully']);
    }

    private function validateAndPersist($taskList, ValidatorInterface $validator): JsonResponse
    {
        $errors = $validator->validate($taskList);
        if (count($errors) > 0) {
            $errorMessages = array_map(fn($error) => $error->getMessage(), iterator_to_array($errors));
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($taskList);
        $this->entityManager->flush();

        return $this->json(['message' => 'Task List updated successfully', 'id' => $taskList->getId()], Response::HTTP_CREATED);
    }
}
