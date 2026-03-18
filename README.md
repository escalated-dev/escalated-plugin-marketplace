# @escalated-dev/plugin-marketplace

Browse and install plugins from the Escalated marketplace registry. Supports self-hosted registries.

## Features

- Catalog browser with search and category filtering
- Plugin detail view with screenshots and readme
- One-click install via registry download URL or custom zip URL
- Automatic daily update checks with admin broadcast notification
- "Browse Marketplace" button injected into admin plugins page

## Hooks

| Type | Hook | Description |
|------|------|-------------|
| Filter | `admin.plugins.actions` | Adds "Browse Marketplace" to the plugins admin action menu |
| Cron | `every:1d` | Checks for plugin updates and notifies admin if found |

## Endpoints

| Method | Path | Capability |
|--------|------|-----------|
| GET | `/catalog` | `manage_plugins` |
| GET | `/detail/:slug` | `manage_plugins` |
| POST | `/install` | `manage_plugins` |
| GET | `/check-updates` | `manage_plugins` |
| GET/POST | `/settings` | `manage_settings` |
