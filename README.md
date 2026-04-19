# Safely – Task Management

A compliance task management system built with Laravel 13. Site managers can create tasks, assign them to users, track due dates, and mark tasks as completed or non-compliant with corrective actions.

---

## Requirements

- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js (optional – only needed if you modify frontend assets)

---

## Setup Instructions

### 1. Clone the repository

```bash
git clone <repository-url>
cd task-management
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Create the database

```sql
CREATE DATABASE task_management;
```

### 5. Run migrations and seeders

```bash
php artisan migrate --seed
```

### 6. Start the development server

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000) — you will be redirected to the task dashboard.

---

## Seeded Users

All users have the password: **`password`**

| Name | Email |
|---|---|
| Admin User | admin@example.com |
| + 9 randomly generated users | — |

---

## Running Tests

```bash
php artisan test tests/Feature/TaskTest.php
```

---

## Features Built

### Web

- Create tasks with title, description, due date, assigned user, and priority — new tasks always start as `pending`
- Task dashboard with filters for status, assigned user, due date, and sort order
- Sort options: Due Date (default), Recently Updated, Newly Created
- Pagination (10 per page) with all filters and sort preserved across pages
- Mark tasks as completed or non-compliant via AJAX modal — no page reload
- Corrective action required when marking a task as non-compliant
- Overdue highlighting — pending tasks due today or earlier are highlighted red with an Overdue badge; highlighting updates instantly on status change without a page reload
- Task detail page showing full task information including corrective action if applicable
- Activity log on the detail page — records every status change with old/new status, who made the change, timestamp, and corrective action notes
- Status can be updated from both the dashboard and the task detail page

### API

- `GET /api/tasks` — returns all tasks as JSON with assigned user details

**Optional query parameters:**

| Parameter | Description | Example |
|---|---|---|
| `status` | Filter by status | `?status=pending` |
| `assigned_user_id` | Filter by assigned user | `?assigned_user_id=1` |

**Example response:**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Inspect fire extinguishers",
      "description": "Check all extinguishers on site.",
      "due_date": "2026-04-24",
      "priority": "high",
      "priority_label": "High",
      "status": "pending",
      "status_label": "Pending",
      "corrective_action": null,
      "assigned_to": {
        "id": 1,
        "name": "Admin User"
      },
      "created_at": "2026-04-19 10:00:00",
      "updated_at": "2026-04-19 10:00:00"
    }
  ],
  "total": 15
}
```

---

## Project Structure

```
app/
├── Enums/
│   ├── TaskPriority.php       # low, medium, high — with label() and badgeClass()
│   └── TaskStatus.php         # pending, completed, non_compliant — with label() and badgeClass()
├── Http/
│   ├── Controllers/
│   │   ├── API/TaskController.php     # API endpoint
│   │   └── TaskController.php         # Web controller
│   └── Requests/
│       ├── StoreTaskRequest.php       # Create validation
│       └── UpdateTaskStatusRequest.php # Status update validation
└── Models/
    ├── Task.php               # Casts, relationships, query scopes
    ├── TaskActivityLog.php    # Activity log model
    └── User.php

database/
├── factories/
│   ├── TaskFactory.php
│   └── UserFactory.php
├── migrations/                # tasks + task_activity_logs with indexes
└── seeders/
    ├── UserSeeder.php         # 1 known admin + 9 factory users
    └── TaskSeeder.php         # 15 tasks across all statuses, priorities, and due dates
```

---

## Assumptions

- No authentication is required per the brief — the activity log uses `user_id: 1` as a placeholder. This is clearly noted and easy to swap to `auth()->id()` once auth is added.
- New tasks always start as `pending` — status is not selectable on creation since it is meaningless before any action is taken.
- A task due today and still pending is treated as overdue — in a compliance context, today's deadline carries the same urgency as a past one.
- The "assigned user" represents the person responsible for completing the task, not the creator. A manager/worker distinction was considered out of scope per the brief.
- Pagination is set to 10 tasks per page as a sensible default for a compliance dashboard.
- The API endpoint has no authentication — consistent with the rest of the app and the brief's scope.

---

## AI Usage Note

This project was built using **Claude Code** as an AI assistant.

**What AI was used for:**
- Scaffolding boilerplate — migrations, models, seeders, form requests, controllers, and views
- Working on the jQuery AJAX modal logic for status updates
- Generating the Bootstrap 5 layout and table structure
- Setting up the API endpoint

**Where it helped most:**
- Speed — the full feature set was produced in a fraction of the time it would take manually
- Consistency — naming conventions, relationships, and validation rules were kept uniform across all files

**What was reviewed or changed manually:**
- All enum values, badge classes, and label methods were reviewed for correctness
- The overdue logic (`!isFuture()` vs `isPast()`) was adjusted after testing edge cases with tasks due today
- The JS AJAX success handler went through several iterations to correctly handle all status transitions — overdue row highlighting, badge toggling, and modal pre-selection on re-open
- The decision to remove status from the create form was a manual judgment call based on re-reading the brief
- Discovered that Laravel 13 renamed the CSRF middleware to `PreventRequestForgery` — fixed manually after test failures
- Sort and pagination column widths were balanced manually in the filter bar

**Trade-offs and shortcuts:**
- No authentication — `user_id: 1` used as a placeholder in the activity log
- No queued notifications for non-compliant tasks — listed under "what I'd improve next"
- API returns all results without pagination — acceptable for the assessment scope but noted below as a next improvement

---

## What I Would Improve Next

Given another 2–3 hours, I would prioritise:

1. **Queued notification job for non-compliant tasks** — dispatch a `TaskMarkedNonCompliant` event that triggers a queued `SendNonCompliantNotification` job. This would notify the assigned user and the site manager via email without blocking the request. Laravel Queue with the database driver makes this straightforward to add.

2. **Role-based access** — distinguish between a site manager (can create and assign tasks) and a worker (can only update status on tasks assigned to them). Laravel's built-in `Gate` or a simple `role` column on users would be sufficient without reaching for a full package.

3. **Filter by priority** — the priority field exists on every task but is not currently a filter option on the dashboard. Adding a priority dropdown alongside the existing status and user filters would let managers quickly surface all high-priority tasks across the site.

4. **Date range filtering** — the current due date filter only supports "Due Today" and "Overdue" presets. A "From / To" date picker would allow managers to view tasks due within a specific period, which is useful for weekly planning and compliance reporting cycles.

5. **Stats dashboard with charts** — a dedicated overview page showing key metrics at a glance: total tasks by status (pending / completed / non-compliant), overdue count, tasks per user, and completion trends over time. This could be built with a lightweight charting library such as Chart.js, fed by a small set of aggregate queries, and would give site managers the visibility they need without having to scan the full task list.

6. **API pagination and versioning** — the current API returns all results in one response. Adding `paginate()` with a consistent envelope structure and an `/api/v1/` prefix would make it production-ready.

7. **Export to CSV** — compliance teams regularly need to export task lists for reporting. A simple filtered export endpoint using Laravel's `StreamedResponse` would cover this without any additional packages.
