import { definePlugin } from '@escalated-dev/plugin-sdk'
import type { PluginContext } from '@escalated-dev/plugin-sdk'

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

type PluginCategory = 'integrations' | 'automation' | 'reporting' | 'channels' | 'security' | 'productivity'

interface MarketplacePlugin {
    slug: string
    name: string
    description: string
    version: string
    author: string
    category: PluginCategory
    icon?: string
    screenshots?: string[]
    tags?: string[]
    downloads?: number
    rating?: number
    installed?: boolean
    installed_version?: string
    has_update?: boolean
    zip_url?: string
    readme_url?: string
}

const CATEGORIES: PluginCategory[] = [
    'integrations', 'automation', 'reporting', 'channels', 'security', 'productivity',
]

// ---------------------------------------------------------------------------
// Registry client
// ---------------------------------------------------------------------------

async function fetchCatalog(
    ctx: PluginContext,
    registryUrl: string,
    params: { category?: string; search?: string; sort?: string },
): Promise<MarketplacePlugin[]> {
    const url = new URL(`${registryUrl.replace(/\/$/, '')}/api/plugins`)
    if (params.category) url.searchParams.set('category', params.category)
    if (params.search) url.searchParams.set('search', params.search)
    if (params.sort) url.searchParams.set('sort', params.sort)

    try {
        const res = await ctx.http.get(url.toString())
        const body = await res.json() as { plugins?: MarketplacePlugin[] }
        return body.plugins ?? []
    } catch (err: unknown) {
        ctx.log.warn('[marketplace] Failed to fetch catalog', { error: String(err) })
        return []
    }
}

// ---------------------------------------------------------------------------
// Plugin definition
// ---------------------------------------------------------------------------

export default definePlugin({
    name: 'marketplace',
    version: '0.1.0',
    description: 'Browse and install plugins from the Escalated marketplace registry',

    config: [
        { name: 'registry_url', label: 'Registry URL', type: 'url',
            default: 'https://marketplace.escalated.dev',
            help: 'Base URL of the plugin registry. Change for self-hosted registries.' },
        { name: 'auto_check_updates', label: 'Auto-Check for Updates', type: 'boolean', default: true },
    ],

    onActivate: async (ctx) => {
        ctx.log.info('[marketplace] Plugin activated')
    },

    onDeactivate: async (ctx) => {
        ctx.log.info('[marketplace] Plugin deactivated')
    },

    // -----------------------------------------------------------------------
    // Filter hooks
    // -----------------------------------------------------------------------

    filters: {
        'admin.plugins.actions': {
            priority: 10,
            handler: (actions) => [
                ...(actions as unknown[]),
                {
                    id: 'browse-marketplace',
                    label: 'Browse Marketplace',
                    icon: 'squares-plus',
                    route: '/admin/marketplace',
                },
            ],
        },
    },

    // -----------------------------------------------------------------------
    // Pages & components
    // -----------------------------------------------------------------------

    pages: [
        {
            route: 'marketplace',
            component: 'MarketplaceBrowser',
            layout: 'admin',
            capability: 'manage_plugins',
            menu: { label: 'Marketplace', section: 'admin', position: 90, icon: 'squares-plus' },
        },
    ],

    components: [
        {
            page: 'admin.plugins',
            slot: 'header',
            component: 'MarketplaceBrowseButton',
            props: { label: 'Browse Marketplace', route: '/admin/marketplace', icon: 'squares-plus' },
            order: 10,
            capability: 'manage_plugins',
        },
    ],

    // -----------------------------------------------------------------------
    // Endpoints
    // -----------------------------------------------------------------------

    endpoints: {
        'GET /catalog': {
            capability: 'manage_plugins',
            handler: async (ctx, req) => {
                const cfg = await ctx.config.all()
                const registryUrl = (cfg.registry_url as string) ?? 'https://marketplace.escalated.dev'

                const plugins = await fetchCatalog(ctx, registryUrl, {
                    category: req.query.category,
                    search: req.query.search,
                    sort: req.query.sort ?? 'popular',
                })

                // Annotate with install status
                const installed = (await ctx.store.query('installed', {})) as unknown as Array<{ slug: string; version: string }>
                const installedMap = new Map(installed.map((p) => [p.slug, p]))

                const annotated = plugins.map((p) => ({
                    ...p,
                    installed: installedMap.has(p.slug),
                    installed_version: installedMap.get(p.slug)?.version,
                    has_update: installedMap.has(p.slug) && installedMap.get(p.slug)?.version !== p.version,
                }))

                return { plugins: annotated, total: annotated.length, categories: CATEGORIES }
            },
        },

        'GET /detail/:slug': {
            capability: 'manage_plugins',
            handler: async (ctx, req) => {
                const cfg = await ctx.config.all()
                const registryUrl = (cfg.registry_url as string) ?? 'https://marketplace.escalated.dev'

                try {
                    const res = await ctx.http.get(
                        `${registryUrl.replace(/\/$/, '')}/api/plugins/${req.params.slug}`,
                    )
                    const plugin = await res.json() as MarketplacePlugin
                    const installed = await ctx.store.query('installed', { slug: req.params.slug })
                    return {
                        success: true,
                        plugin: {
                            ...plugin,
                            installed: installed.length > 0,
                            installed_version: installed.length > 0
                                ? (installed[0] as unknown as { version: string }).version
                                : undefined,
                        },
                    }
                } catch {
                    return { success: false, message: `Plugin '${req.params.slug}' not found` }
                }
            },
        },

        'POST /install': {
            capability: 'manage_plugins',
            handler: async (ctx, req) => {
                const { slug, zip_url } = req.body as { slug: string; zip_url?: string }
                if (!slug) return { success: false, message: 'Plugin slug is required' }

                const cfg = await ctx.config.all()
                const registryUrl = (cfg.registry_url as string) ?? 'https://marketplace.escalated.dev'
                const downloadUrl = zip_url ?? `${registryUrl.replace(/\/$/, '')}/plugins/${slug}/download`

                try {
                    // Emit install request — handled by the host bridge (downloads & installs package)
                    await ctx.emit('marketplace.install.requested', { slug, download_url: downloadUrl })

                    await ctx.store.insert('installed', {
                        slug,
                        installed_at: new Date().toISOString(),
                        download_url: downloadUrl,
                    })

                    return { success: true, message: `Plugin '${slug}' installation started` }
                } catch (err: unknown) {
                    return { success: false, message: String(err) }
                }
            },
        },

        'GET /check-updates': {
            capability: 'manage_plugins',
            handler: async (ctx) => {
                const cfg = await ctx.config.all()
                const registryUrl = (cfg.registry_url as string) ?? 'https://marketplace.escalated.dev'
                const installed = (await ctx.store.query('installed', {})) as unknown as Array<{ slug: string; version?: string }>

                const updates: Array<{ slug: string; current: string; latest: string }> = []

                for (const pkg of installed) {
                    try {
                        const res = await ctx.http.get(
                            `${registryUrl.replace(/\/$/, '')}/api/plugins/${pkg.slug}/version`,
                        )
                        const body = await res.json() as { version?: string }
                        if (body.version && body.version !== pkg.version) {
                            updates.push({ slug: pkg.slug, current: pkg.version ?? '', latest: body.version })
                        }
                    } catch {
                        // Skip if registry check fails for individual plugin
                    }
                }

                await ctx.store.set('update_check', 'last', { checked_at: new Date().toISOString(), updates })
                return { updates, count: updates.length, checked_at: new Date().toISOString() }
            },
        },

        'GET /settings': {
            capability: 'manage_settings',
            handler: async (ctx) => ctx.config.all(),
        },
        'POST /settings': {
            capability: 'manage_settings',
            handler: async (ctx, req) => {
                await ctx.config.set(req.body as Record<string, unknown>)
                return { success: true }
            },
        },
    },

    // -----------------------------------------------------------------------
    // Cron — daily update check
    // -----------------------------------------------------------------------

    cron: {
        'every:1d': async (ctx) => {
            const cfg = await ctx.config.all()
            if (!cfg.auto_check_updates) return

            const registryUrl = (cfg.registry_url as string) ?? 'https://marketplace.escalated.dev'
            const installed = (await ctx.store.query('installed', {})) as unknown as Array<{ slug: string; version?: string }>

            let updateCount = 0
            for (const pkg of installed) {
                try {
                    const res = await ctx.http.get(
                        `${registryUrl.replace(/\/$/, '')}/api/plugins/${pkg.slug}/version`,
                    )
                    const body = await res.json() as { version?: string }
                    if (body.version && body.version !== pkg.version) {
                        updateCount++
                    }
                } catch {
                    // Ignore individual plugin check failures
                }
            }

            if (updateCount > 0) {
                ctx.log.info(`[marketplace] ${updateCount} plugin update(s) available`)
                await ctx.broadcast.toChannel('admin', 'marketplace.updates_available', { count: updateCount })
            }
        },
    },
})
