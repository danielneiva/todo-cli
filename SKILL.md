---
name: todo-cli
description: A complete GTD (Getting Things Done) CLI task manager using SQLite for persistence. Interact with it to manage user tasks, priorities, and workflow in the terminal.
homepage: https://github.com/danielneiva/todo-cli
metadata:
  {
    "openclaw":
      {
        "emoji": "✅",
        "requires": { "bins": ["todo-cli"], "env": [] },
        "install":
          [
            {
              "id": "bash",
              "kind": "bash",
              "script": "./install.sh",
              "bins": ["todo-cli"],
              "label": "Install todo-cli (bash)",
            },
          ],
      },
  }
---

# todo-cli

A complete GTD (Getting Things Done) CLI task manager using SQLite for persistence at `~/.todo/database.sqlite`.

Install

- Run the install script: `curl -fsSL https://raw.githubusercontent.com/danielneiva/todo-cli/main/install.sh | bash`
- To automatically install this skill to OpenClaw: `curl -fsSL https://raw.githubusercontent.com/danielneiva/todo-cli/main/install.sh | bash -s -- --openclaw`
- Or manually download the binary from GitHub Releases and put it in your PATH.

Initial Setup

- Before first use, the application must be initialized to create the database and seed defaults.
- Install defaults: `todo-cli task:install` (Requires user interaction, or bypass locally)

Common commands

- Add task: `todo-cli task:add --name="Task name" --category="Category" --priority=High --deadline=YYYY-MM-DD`
- Show Inbox: `todo-cli task:inbox`
- List active tasks: `todo-cli task:list`
- Filter tasks: `todo-cli task:list --status="Next Action" --category="Work"`
- View task details: `todo-cli task:show <id>`
- Edit task: `todo-cli task:edit <id> --deadline=YYYY-MM-DD`
- Change status: `todo-cli task:status <id> "Next Action"`
- Complete task: `todo-cli task:status <id> Done`
- Interactive Weekly Review: `todo-cli task:review`

Notes

- GTD Workflow: Tasks start in `inbox`. Process them to `Next Action`, `Waiting`, `Someday`, or `Done` using the status command.
- The `task:install` command must be run before any other commands will work.
