# Todo CLI - GTD Task Manager

A powerful, command-line based task manager built on [Laravel Zero](https://laravel-zero.com/), following the **Getting Things Done (GTD)** methodology. It uses an embedded SQLite database (`~/.todo/database.sqlite`) to track your tasks seamlessly across terminal sessions.

## 🚀 Installation & Setup

Install `todo-cli` instantly using the installation script. It will automatically download the correct binary for your OS (or install the `todo-cli.phar` if a native binary isn't available).

```bash
curl -fsSL https://raw.githubusercontent.com/danielneiva/todo-cli/main/install.sh | bash
```

> **For AI Agents (OpenClaw):** If you want to automatically install the `SKILL.md` instruction file into your `~/.openclaw/skills` directory, append `--openclaw` to the install command via bash:
> `curl -fsSL https://raw.githubusercontent.com/danielneiva/todo-cli/main/install.sh | bash -s -- --openclaw`

Once installed, if this is your first time using Todo CLI, you need to set up the database and your preferences:

```bash
todo-cli task:install
```

This interactive command will help you configure:
1. **Statuses**: You can keep the GTD defaults (Inbox, Next Action, Waiting For, Someday/Maybe, Done, Cancelled) or define your own workflow.
2. **Priorities**: Defaults are Low, Medium, High, Urgent.
3. **Categories**: Defaults are Personal, Work, Learning.

## 📥 Managing Your Inbox

In GTD, any new thought or task should immediately go into your Inbox without overthinking it. You capture first and process later.

```bash
# Add a new task (prompts interactively if flags are missing)
todo-cli task:add -n "Buy groceries" --category="Personal" --priority="High" --deadline="2026-02-25"

# Show everything currently in your inbox
todo-cli task:inbox
```

## 🔄 The GTD Workflow

Once tasks are in your Inbox, you "process" them by giving them the appropriate status based on what action is required.

```bash
# Change a task's status (prompts you with a list of available statuses)
todo-cli task:status 1
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
todo-cli task:list

# Filter by a specific status
todo-cli task:list --status="Next Action"

# Filter by category or priority
todo-cli task:list --category="Work" --priority="Urgent"

# Search within task names and descriptions
todo-cli task:list --search="groceries"

# See what's falling behind
todo-cli task:list --overdue
```

## 📝 Modifying Tasks

You can view complete details of a task or edit it.

```bash
# View all details, description, and dates
todo-cli task:show 1

# Edit task fields (press Enter to keep current values interactively)
todo-cli task:edit 1
```

## 🔄 Weekly Review

The core of GTD is the Weekly Review. This interactive command helps you clear your Inbox, review all active tasks, and handle overdue deadlines one by one.

```bash
todo-cli task:review
```

## 🛠️ Tech Stack & Testing

This project uses:
* PHP 8.2+
* Laravel Zero 12
* SQLite
* Pest PHP (Testing)

**Running Tests:**
```bash
todo-cli test
```

## 📦 Building Standalone Binaries

If you want to build standalone binaries (for macOS and Linux) that don't require PHP to be installed on the host machine, you can use PHPacker. Because combining binaries requires higher memory, run it with the memory limit disabled:

```bash
# First, build the phar archive
todo-cli app:build todo-cli.phar

# Then package it into standalone binaries using the configured phpacker.json
php -d memory_limit=-1 ./vendor/bin/phpacker build
```
The resulting binaries will be saved in the `dist/` directory.

Enjoy getting things done from the terminal! 🎉
