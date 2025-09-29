# Task Tracker (PHP + MySQL)

A tiny task-tracking API built with plain PHP (mysqli) and MySQL. The repository is optimized for local development on XAMPP (Windows) and exposes a small HTTP API to list, add, and delete tasks.

Live demo / deployed project: https://office-task-tracker.infinityfree.me/ (hosted on InfinityFree)

## Contents

- `index.php` — (Front controller/entry point; can be used for UI or routing if extended)
- `api.php` — REST-ish API for tasks (list/add/delete)
- `db.php` — Local database connection details (ignored by Git)
- `db.example.php` — Example database config you can copy and customize
- `.gitignore` — Ensures `db.php` is not committed

## Prerequisites

- PHP 7.4+ with `mysqli` extension
- MySQL 5.7+/MariaDB 10.3+
- Web server (Apache via XAMPP recommended)

## Getting started (XAMPP on Windows)

1. Clone the repo into your XAMPP web root (default is `C:\xampp\htdocs`).
2. Copy `db.example.php` to `db.php` and update credentials:
   - `DB host`: default `127.0.0.1`
   - `DB username`: default `root`
   - `DB password`: set yours 
   - `DB name`: `task_tracker`
3. Create the database and table:

```sql
CREATE DATABASE IF NOT EXISTS task_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_tracker;

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

```

4. Start Apache and MySQL in XAMPP.
5. Access the API at `http://localhost/dev_intern/api.php`.

## Database connection

The app includes two connection files:

- `db.php` (ignored by Git): your local credentials. Current default in this repo uses:
  - host: `dbhostname`
  - user: `dbusername`
  - pass: `dbpassword`
  - db: `dbname`
- `db.example.php`: a template you can copy to `db.php` and fill in.

`.gitignore` contains `/db.php`, so your local credentials are not pushed to GitHub.

## API reference

Base URL: `http://localhost/dev_intern/api.php`

All responses are JSON with the shape `{ success: boolean, ... }` and appropriate HTTP status codes.

### 1) List tasks

- Method: GET
- Query: `action=list`
- Example: `GET /api.php?action=list`
- Response 200:

```json
{
  "success": true,
  "tasks": [
    { "id": 2, "title": "Buy milk", "created_at": "2025-09-29 10:28:00" },
    { "id": 1, "title": "Read book", "created_at": "2025-09-29 09:00:00" }
  ]
}
```

### 2) Add task

- Method: POST
- Query: `action=add`
- Body: either `application/json` or form-encoded
  - Field: `title` (string, required, max 255)
- Behavior:
  - Validates non-empty title
  - Rejects duplicates by title at the API layer (409)
- Responses:
  - 201 Created on success
  - 422 Validation error
  - 409 Duplicate title
  - 500 Internal errors

Examples (PowerShell on Windows):

```powershell
# JSON body
$body = @{ title = 'My New Task' } | ConvertTo-Json
Invoke-RestMethod -Method Post "http://localhost/dev_intern/api.php?action=add" -ContentType 'application/json' -Body $body

# Form body
Invoke-RestMethod -Method Post "http://localhost/dev_intern/api.php?action=add" -Body @{ title = 'My New Task' }
```

Example response (201):

```json
{ "success": true, "message": "Task added", "id": 3, "title": "My New Task" }
```

### 3) Delete task

- Method: POST
- Query: `action=delete`
- ID can be provided via query `?id=`, form field `id`, or JSON body `{ "id": number }`
- Responses:
  - 200 Deleted
  - 404 Not found
  - 422 Missing/invalid id


## Error handling

- Missing `action` -> 400
- Unknown action -> 400
- Non-POST calls to `add`/`delete` -> 405 with `Allow: POST`
- Database errors -> 500 with generic message

## Extending

- Add update/complete endpoints
- Add pagination to `list`
- Add authentication
- Add FCM (Firebase Cloud Messaging) integration to send push notifications to mobile devices when tasks are added/updated/deleted

## Contributing

1. Fork and clone the repo
2. Create a feature branch
3. Keep changes small and documented
4. Open a PR

