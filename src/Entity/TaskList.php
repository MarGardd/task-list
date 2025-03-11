<?php

namespace App\Entity;

use App\Repository\TaskListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Ignore;


#[ORM\Entity(repositoryClass: TaskListRepository::class)]
class TaskList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Field 'title' is required")]
    #[Assert\Type('string', message: "Field 'title' must be of type string")]
    private ?string $title = null;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: "taskList", cascade: ['persist', 'remove'])]
    private Collection $tasks;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "taskLists")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private User $user;

    #[ORM\Column]
    private ?int $user_id = null;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

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

    #[Ignore]
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setTaskList($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getTaskList() === $this) {
                $task->setTaskList(null);
            }
        }

        return $this;
    }

    #[Ignore]
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    #[Ignore]
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): User
    {
        $this->user = $user;

        return $this->user;
    }
}
