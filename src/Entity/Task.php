<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tasks'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tasks'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tasks'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $task_list_id = null;

    #[ORM\ManyToOne(targetEntity: "TaskList", inversedBy: "tasks")]
    #[ORM\JoinColumn(name: "task_list_id", referencedColumnName: "id")]
    private TaskList $taskList;

    public const NOT_FOUND_EXCEPTION_MESSAGE = 'Task not found';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[Ignore]
    public function getTaskListId(): ?string
    {
        return $this->task_list_id;
    }

    public function setTaskListId(string $task_list_id): static
    {
        $this->task_list_id = $task_list_id;

        return $this;
    }

    #[Ignore]
    public function getTaskList(): ?TaskList
    {
        return $this->taskList;
    }

    public function setTaskList(TaskList $taskList): static
    {
        $this->taskList = $taskList;

        return $this;
    }

    #[Ignore]
    public function getOwner(): User
    {
        return $this->taskList->getUser();
    }
}
