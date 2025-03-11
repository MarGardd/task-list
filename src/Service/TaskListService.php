<?php

namespace App\Service;

use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskListService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly TaskListRepository $taskListRepository,
        private readonly UserRepository $userRepository
    )
    {}

    public function getTaskListsForUser(User $user): array
    {
        $taskLists = $this->taskListRepository->findTaskListsWithTasksByUser($user);
        return array_map(function ($taskList) {
            return [
                'id' => $taskList->getId(),
                'title' => $taskList->getTitle(),
                'tasks' => array_map(function ($task) {
                    return [
                        'id' => $task->getId(),
                        'title' => $task->getTitle(),
                        'description' => $task->getDescription(),
                    ];
                }, $taskList->getTasks()->toArray()),
            ];
        }, $taskLists);
    }

    public function getTaskListById(int $id, User $user): ?TaskList
    {
        $taskList = $this->taskListRepository->find($id);
        if (!$taskList || $taskList->getUser() !== $user) {
            return null;
        }
        return $taskList;
    }

    public function createTaskList(array $data): array
    {
        $errors = $this->validateTaskListData($data);
        if (count($errors) > 0) {
            return ['errors' => $this->formatValidationErrors($errors)];
        }

        $user = $this->userRepository->find($data['user_id']);
        $taskList = new TaskList();
        $taskList->setTitle($data['title']);
        $taskList->setUser($user);
        $this->entityManager->persist($taskList);
        $this->entityManager->flush();

        return ['message' => 'Task List created successfully', 'id' => $taskList->getId()];
    }

    public function updateTaskList(TaskList $taskList, array $data): array
    {
        if (empty($data['title'])) {
            return ['errors' => ['Title is required']];
        }
        $taskList->setTitle($data['title']);
        $this->entityManager->flush();

        return ['message' => 'Task List updated successfully'];
    }

    public function deleteTaskList(TaskList $taskList): array
    {
        $this->entityManager->remove($taskList);
        $this->entityManager->flush();

        return ['message' => 'Task List deleted successfully'];
    }

    private function validateTaskListData(array $data): ConstraintViolationListInterface
    {
        $taskListDto = new TaskListDto(
            title: $data['title'] ?? '',
            user_id: $data['user_id'] ?? ''
        );

        return $this->validator->validate($taskListDto);
    }

    private function formatValidationErrors(ConstraintViolationListInterface $errors): array
    {
        return array_map(fn($error) => $error->getMessage(), iterator_to_array($errors));
    }
}