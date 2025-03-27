<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private $user;
    private $taskList;
    private $task;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $userRepository = $container->get(UserRepository::class);
        $taskListRepository = $container->get(TaskListRepository::class);
        $taskRepository = $container->get(TaskRepository::class);

        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('password');
        $userRepository->save($this->user, true);

        $this->taskList = new TaskList();
        $this->taskList->setTitle('Test Task List');
        $this->taskList->setUser($this->user);
        $taskListRepository->save($this->taskList, true);

        $this->task = new Task();
        $this->task->setTitle('Test Task');
        $this->task->setTaskList($this->taskList);
        $taskRepository->save($this->task, true);

        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/api/tasks');
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('items', $responseData);
        $this->assertCount(1, $responseData['items']);
        $this->assertEquals('Test Task', $responseData['items'][0]['title']);
    }

    public function testShow(): void
    {
        $this->client->request('GET', '/api/tasks/' . $this->task->getId());
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertSame('Test Task', $responseData['title']);
    }

    public function testCreate(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'This is a new task.',
        ];

        $this->client->request(
            'POST',
            '/api/task-lists/' . $this->taskList->getId() . '/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($taskData)
        );

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertSame('New Task', $responseData['data']['title']);
    }

    public function testUpdate(): void
    {
        $taskData = [
            'title' => 'Updated Task',
            'description' => 'This is an updated task.',
        ];

        $this->client->request(
            'PUT',
            '/api/task-lists/' . $this->taskList->getId() . '/tasks/' . $this->task->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($taskData)
        );

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertSame('Updated Task', $responseData['data']['title']);
    }

    public function testDelete(): void
    {
        $this->client->request('DELETE', '/api/task-lists/' . $this->taskList->getId() . '/tasks/' . $this->task->getId());
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertSame('Task deleted successfully', $responseData['message']);
    }
}
