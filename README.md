# Escalated Plugin: Marketplace

Browse, install, and update plugins from the Escalated marketplace registry. Supports self-hosted registries for private plugin distribution.

## Features

- Catalog browser with search, category filtering, and sorting
- Plugin detail view with screenshots and readme
- One-click install via registry download URL or custom zip URL
- Automatic daily update checks with admin broadcast notification
- "Browse Marketplace" button injected into the admin plugins page
- Install status tracking with version comparison

## Configuration

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `registry_url` | url | No | Base URL of the plugin registry. Defaults to `https://marketplace.escalated.dev`. |
| `auto_check_updates` | boolean | No | Automatically check for plugin updates daily. Defaults to `true`. |

## Admin Pages

- **marketplace** — Browse the plugin catalog with search, categories, and install actions.

## Hooks

### Filters
- `admin.plugins.actions` — Adds a "Browse Marketplace" action to the admin plugins page.

### Cron
- `every:1d` — Checks for available plugin updates and notifies admins via broadcast.

## Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/catalog` | Browse the plugin catalog with optional category, search, and sort filters. |
| GET | `/detail/:slug` | Get detailed information about a specific plugin. |
| POST | `/install` | Install a plugin by slug from the registry. |
| GET | `/check-updates` | Check all installed plugins for available updates. |
| GET | `/settings` | Get plugin configuration. |
| POST | `/settings` | Save plugin configuration. |

## Installation

```bash
npm install @escalated-dev/plugin-marketplace
```

## License

MIT
