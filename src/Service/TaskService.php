<?php

namespace App\Service;

use App\Dto\TaskDto;
use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly TaskRepository $taskRepository,
        private readonly UserRepository $userRepository,
    )
    {}

    public function getTasksByUser(User $user): array
    {
        $userWithTasks = $this->userRepository->findAllTasksWithTaskListsByUser($user);
        $data = [];
        foreach ($userWithTasks->getTaskLists() as $taskList) {
            foreach ($taskList->getTasks() as $task) {
                $data[] = [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'task_list_id' => $taskList->getId(),
                    'task_list_title' => $taskList->getTitle(),
                ];
            }
        }

        return $data;
    }

    public function getTasksByList(TaskList $taskList): array
    {
        return $taskList->getTasks()->toArray();
    }

    public function getTaskById(int $id, User $user): ?Task
    {
        $task = $this->taskRepository->find($id);
        if(!$task) return null;
        $taskList = $task->getTaskList();
        if (!$taskList || $taskList->getUser() !== $user) {
            return null;
        }

        return $task;
    }

    public function createTask(User $currentUser, array $data): array
    {
        $errors = $this->validateTaskData($data);
        if (count($errors) > 0) {
            return ['errors' => $this->formatValidationErrors($errors)];
        }
        $taskList = $this->entityManager->getRepository(TaskList::class)->find($data['task_list_id']);
        if (!$taskList || $taskList->getUserId() !== $currentUser->getId()) {
            return ['errors' => ['Task List not found']];
        }
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? '');
        $task->setTaskList($taskList);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return ['message' => 'Task created successfully', 'id' => $task->getId()];
    }

    public function updateTask(Task $task, array $data): array
    {
        if (empty($data['title']) && empty($data['description'])) {
            return ['errors' => ['The title or description field must be filled']];
        }
        $task->setTitle($data['title'] ?? $task->getTitle());
        $task->setDescription($data['description'] ?? $task->getDescription());
        $this->entityManager->flush();

        return ['message' => 'Task updated successfully'];
    }

    public function deleteTask(Task $task): array
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return ['message' => 'Task deleted successfully'];
    }

    private function validateTaskData(array $data): ConstraintViolationListInterface
    {
        $taskListDto = new TaskDto(
            title: $data['title'] ?? '',
            task_list_id: $data['task_list_id'] ?? ''
        );

        return $this->validator->validate($taskListDto);
    }

    private function formatValidationErrors(ConstraintViolationListInterface $errors): array
    {
        return array_map(fn($error) => $error->getMessage(), iterator_to_array($errors));
    }
}