# Task List

## Требования

Для запуска проекта на вашей машине должны быть установлены:
- **Docker** (включая Docker Compose)
- **Git**

## Список команд для запуска проекта
```bash
git clone https://github.com/MarGardd/task-list
cd task-list
cp .env.example .env
```
Откройте файл .env и настройте переменные окружения. Пример:
```bash
DB_NAME=task_db
DB_USER=task_user
DB_PASSWORD=task_password
APP_SECRET=random_secret_here
```
После настрйоки env
```bash
docker-compose up --build
```
Проект будет доступен по адресу:
```bash
http://localhost:8080
```
