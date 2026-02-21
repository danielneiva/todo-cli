<p align="center">
  <img src="https://laravel-zero.com/assets/img/logo.png" alt="Laravel Zero Logo" width="250">
</p>

# Todo CLI - GTD Task Manager

A powerful, command-line based task manager built on [Laravel Zero](https://laravel-zero.com/), following the **Getting Things Done (GTD)** methodology. It uses an embedded SQLite database (`~/.todo/database.sqlite`) to track your tasks seamlessly across terminal sessions.

## 🚀 Getting Started

If this is your first time using Todo CLI, you need to set up the database and your preferences.

```bash
php todo-cli task:install
```

This interactive command will help you configure:
1. **Statuses**: You can keep the GTD defaults (Inbox, Next Action, Waiting For, Someday/Maybe, Done, Cancelled) or define your own workflow.
2. **Priorities**: Defaults are Low, Medium, High, Urgent.
3. **Categories**: Defaults are Personal, Work, Learning.

## 📥 Managing Your Inbox

In GTD, any new thought or task should immediately go into your Inbox without overthinking it. You capture first and process later.

```bash
# Add a new task (prompts interactively if flags are missing)
php todo-cli task:add -n "Buy groceries" --category="Personal" --priority="High" --deadline="2026-02-25"

# Show everything currently in your inbox
php todo-cli task:inbox
```

## 🔄 The GTD Workflow

Once tasks are in your Inbox, you "process" them by giving them the appropriate status based on what action is required.

```bash
# Change a task's status (prompts you with a list of available statuses)
php todo-cli task:status 1
```

* **Inbox**: Items just captured that haven't been processed yet.
* **Next Action**: Items that are ready to be worked on immediately.
* **Waiting For**: Items blocked or waiting on someone else.
* **Someday/Maybe**: Ideas you want to keep but won't act on right now.
* **Done**: Completed tasks.
* **Cancelled**: Tasks you decided not to do.

## 📋 Tracking Your Work

When you want to see what's on your plate, you can list and filter your tasks.

```bash
# List all active tasks
php todo-cli task:list

# Filter by a specific status
php todo-cli task:list --status="Next Action"

# Filter by category or priority
php todo-cli task:list --category="Work" --priority="Urgent"

# Search within task names and descriptions
php todo-cli task:list --search="groceries"

# See what's falling behind
php todo-cli task:list --overdue
```

## 📝 Modifying Tasks

You can view complete details of a task or edit it.

```bash
# View all details, description, and dates
php todo-cli task:show 1

# Edit task fields (press Enter to keep current values interactively)
php todo-cli task:edit 1
```

## 🔄 Weekly Review

The core of GTD is the Weekly Review. This interactive command helps you clear your Inbox, review all active tasks, and handle overdue deadlines one by one.

```bash
php todo-cli task:review
```

## 🛠️ Tech Stack & Testing

This project uses:
* PHP 8.2+
* Laravel Zero 12
* SQLite
* Pest PHP (Testing)

**Running Tests:**
```bash
php todo-cli test
```

Enjoy getting things done from the terminal! 🎉
