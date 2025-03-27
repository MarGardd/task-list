<?php

namespace App\Tests\Controller;

use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListControllerTest extends WebTestCase
{
    private $client;
    private $user;
    private $taskList;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $userRepository = $container->get(UserRepository::class);
        $taskListRepository = $container->get(TaskListRepository::class);

        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('password');
        $userRepository->save($this->user, true);

        $this->taskList = new TaskList();
        $this->taskList->setTitle('Test Task List');
        $this->taskList->setUser($this->user);
        $taskListRepository->save($this->taskList, true);

        $this->client->loginUser($this->user);
    }

    private function createTaskListData(string $title, string $description = ''): array
    {
        return [
            'title' => $title,
            'description' => $description,
        ];
    }

    private function assertTaskListResponse(array $responseData, string $expectedTitle): void
    {
        $this->assertIsArray($responseData);
        $this->assertEquals($expectedTitle, $responseData['title']);
    }

    public function testGetAllTaskLists(): void
    {
        $this->client->request('GET', '/api/task-lists/all');
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);
        $this->assertEquals('Test Task List', $responseData[0]['title']);
    }

    public function testGetTaskList(): void
    {
        $this->client->request('GET', '/api/task-lists/' . $this->taskList->getId());
        $this->assertResponseIsSuccessful();
        $this->assertJson($this->client->getResponse()->getContent());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTaskListResponse($responseData, 'Test Task List');
    }

    public function testCreateTaskList(): void
    {
        $taskListData = $this->createTaskListData('New Task List', 'This is a new task list.');

        $this->client->request(
            'POST',
            '/api/task-lists',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($taskListData)
        );

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertTaskListResponse($responseData['data'], 'New Task List');
    }

    public function testUpdateTaskList(): void
    {
        $taskListData = $this->createTaskListData('Updated Task List', 'This is an updated task list.');

        $this->client->request(
            'PUT',
            '/api/task-lists/' . $this->taskList->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($taskListData)
        );

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertTaskListResponse($responseData['data'], 'Updated Task List');
    }

    public function testDeleteTaskList(): void
    {
        $this->client->request('DELETE', '/api/task-lists/' . $this->taskList->getId());
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Task List deleted successfully', $responseData['message']);
    }
}