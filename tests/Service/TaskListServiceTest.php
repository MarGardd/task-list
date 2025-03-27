<?php

namespace App\Tests\Service;

use App\Dto\TaskListDto;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Service\TaskListService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

class TaskListServiceTest extends TestCase
{
    private $entityManager;
    private $taskListRepository;
    private $taskListsCache;
    private $taskListService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->taskListRepository = $this->createMock(TaskListRepository::class);
        $this->taskListsCache = $this->createMock(CacheInterface::class);

        $this->taskListService = new TaskListService(
            $this->entityManager,
            $this->taskListRepository,
            $this->taskListsCache
        );
    }

    public function testGetAllTaskListsByUser()
    {
        $user = new User();
        $user->setId(1);

        $taskList = new TaskList();
        $taskList->setTitle('Test List');
        $taskList->setUser($user);

        $this->taskListsCache->expects($this->once())
            ->method('get')
            ->willReturn([$taskList]);

        $result = $this->taskListService->getAllTaskListsByUser($user);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Test List', $result[0]->getTitle());
    }

    public function testCreateTaskList()
    {
        $user = new User();
        $user->setId(1);

        $taskListDto = new TaskListDto();
        $taskListDto->title = 'New Task List';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(TaskList::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->taskListsCache->expects($this->once())
            ->method('delete')
            ->with('task_lists_user_1');

        $taskList = $this->taskListService->createTaskList($taskListDto, $user);

        $this->assertInstanceOf(TaskList::class, $taskList);
        $this->assertEquals('New Task List', $taskList->getTitle());
        $this->assertEquals($user, $taskList->getUser());
    }

    public function testUpdateTaskList()
    {
        $user = new User();
        $user->setId(1);

        $taskList = new TaskList();
        $taskList->setTitle('Old Title');
        $taskList->setUser($user);

        $taskListDto = new TaskListDto();
        $taskListDto->title = 'Updated Title';

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->taskListsCache->expects($this->once())
            ->method('delete')
            ->with('task_lists_user_1');

        $this->taskListService->updateTaskList($taskList, $taskListDto);

        $this->assertEquals('Updated Title', $taskList->getTitle());
    }

    public function testDeleteTaskList()
    {
        $user = new User();
        $user->setId(1);

        $taskList = new TaskList();
        $taskList->setTitle('Task List to Delete');
        $taskList->setUser($user);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($taskList);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->taskListsCache->expects($this->once())
            ->method('delete')
            ->with('task_lists_user_1');

        $this->taskListService->deleteTaskList($taskList);
    }
}
