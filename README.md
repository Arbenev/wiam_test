# Loan Requests Service (Yii2 + Docker)

Мини-сервис на Yii2 для работы с заявками на займ.  
Сервис предоставляет два JSON API эндпоинта:

- `POST /requests` — создание заявки на займ.
- `GET /processor` — обработка заявок с учётом параллельных запросов и ограничения: **не более одной одобренной заявки на пользователя**.

Проект упакован в Docker (PHP-FPM 8.1, Nginx, PostgreSQL).

---

## Стек

- PHP 8.1 (FPM)
- Yii2 Basic Application Template
- PostgreSQL 14
- Nginx
- Docker + Docker Compose v2

---

## Предварительные требования

- Установлен Docker
- Установлен Docker Compose v2 (подкоманда `docker compose`)
- Свободен порт `80` на хосте (для HTTP)
- Порт PostgreSQL на хосте либо:
  - не используется, если порт не пробрасываем, **или**
  - не конфликтует с тем, что указан в `docker-compose.yml` (например, `5433:5432`)

---

## Структура проекта

Главное:

- `docker-compose.yml` — описание сервисов `app`, `nginx`, `db`
- `docker/php/Dockerfile` — образ PHP-FPM 8.1 с расширением `pdo_pgsql`
- `docker/nginx/default.conf` — конфигурация Nginx
- `config/db.php` — настройки подключения к PostgreSQL
- `migrations/` — миграции БД (в т.ч. `loan_request` + partial unique index)
- `models/LoanRequest.php` — ActiveRecord-модель заявки
- `controllers/RequestController.php` — эндпоинт `POST /requests`
- `controllers/ProcessorController.php` — эндпоинт `GET /processor`
- `services/LoanProcessorService.php` — бизнес-логика обработки заявок
- `tests/` — заготовка под unit/functional тесты

---

## Запуск проекта

### 1. Собрать и поднять контейнеры

Из корня проекта:

```bash
docker compose up -d
```
При необходимости можно посмотреть статус контейнеров:
```bash
docker compose ps
```
Ожидаемые сервисы:

- loans-app — PHP-FPM + Yii2
- loans-nginx — Nginx
- loans-db — PostgreSQL

### 2. Применить миграции
```
docker compose exec app php yii migrate --interactive=0
```
После успешного применения миграций будет создана таблица loan_request
### Конфигурация БД
Приложение использует PostgreSQL, описанный в docker-compose.yml как сервис db.
Внутри Docker-сети приложение обращается к БД по хосту db и порту 5432.
Публикация порта на хост (например, 5433:5432) настроена в docker-compose.yml и при необходимости может быть изменена.
### Схема данных
### Таблица loan_request:

- id — первичный ключ
- user_id — идентификатор пользователя (целое, not null)
- amount — сумма займа (целое, not null)
- term — срок займа (целое, not null, например, в днях)
- status — статус заявки:
    - new
    - processing
    - approved
    - declined
- created_at — метка времени создания (UNIX timestamp)
- updated_at — метка времени обновления (UNIX timestamp)
- processed_at — метка времени обработки (UNIX timestamp, nullable)

#### Ограничения:

- Индекс по user_id

## API
### 1. POST /requests
Создание новой заявки на займ.
#### Запрос:
- Метод: POST
- Путь: /requests
- Тело: JSON

#### Пример:
```json
{
  "user_id": 123,
  "amount": 10000,
  "term": 30
}
```
#### Правила:
1. user_id, amount, term — обязательные, положительные целые.
2. Перед созданием заявки выполняется проверка:
    - Если у пользователя уже есть заявка со статусом approved, новая заявка не создаётся.

#### Ответ при успехе:
- Код: 201 Created
- Тело:
```json
{
  "result": true,
  "id": 1
}
```
где id — идентификатор созданной заявки.
#### Ответ при ошибке валидации или бизнес-логики:
- Код: 400 Bad Request
- Тело:
```json
{
  "result": false,
  "errors": {
    "field": ["Error message"]
  }
}
```
Некорректные значения amount/term и т.п. — ошибки вернутся по данным полям.
### 2. GET /processor
Обработка заявок в очередь.
#### Запрос:
- Метод: GET
- Путь: /processor
- Параметры query:
    - delay (опционально) — задержка обработки каждой заявки в секундах, по умолчанию 5.
#### Пример:
```bash
curl "http://localhost/processor?delay=5"
```
#### Поведение:
- За один вызов обрабатывается до 100 заявок со статусом new.
- Каждая заявка:
    1. Блокируется и переводится в статус processing с использованием row-locking (FOR UPDATE SKIP LOCKED), чтобы несколько параллельных запросов не обрабатывали одну и ту же запись.
    2. Выполняется sleep(delay) — имитация долгой обработки.
    3. Принимается решение:
        - с вероятностью 10% заявка пытается перейти в approved,
        - в остальных 90% — в declined.
    4. При попытке перехода в approved:
        - если partial unique index не нарушен — заявка сохраняется в approved,
        - если в этот момент уже есть другая approved-заявка у того же пользователя (конкурентная обработка) — ловится IntegrityException, и текущая заявка переводится в declined.
    5. Поле processed_at заполняется текущим временем.
#### Ответ:
- Код: 200 OK
- Тело:
```json
{
  "result": true,
  "processed": <количество обработанных записей>
}
```
Детали по каждой заявке в API не возвращаются; информация сохраняется в таблице loan_request.

## Учёт времени

- Инициализация Yii2 и Git: ~1 ч
- Настройка Docker/Nginx/PostgreSQL: ~1 ч
- Миграции и модель LoanRequest: ~2 ч
- Реализация POST /requests: ~2 ч
- Реализация GET /processor + сервис: ~3 ч
- Документация (этот README) и полировка: ~5 ч

**Общее время:** ~14 ч
