import { defineEscalatedPlugin } from '@escalated-dev/escalated';
import MarketplaceBrowser from './components/MarketplaceBrowser.vue';
import PluginDetailCard from './components/PluginDetailCard.vue';

// ---------------------------------------------------------------------------
// Category definitions (client-side mirror of PHP catalog categories)
// ---------------------------------------------------------------------------

const CATEGORIES = [
    { id: 'all', label: 'All', icon: 'squares-2x2' },
    { id: 'integrations', label: 'Integrations', icon: 'link' },
    { id: 'automation', label: 'Automation', icon: 'cog' },
    { id: 'reporting', label: 'Reporting', icon: 'chart-bar' },
    { id: 'channels', label: 'Channels', icon: 'inbox' },
    { id: 'security', label: 'Security', icon: 'shield-check' },
    { id: 'productivity', label: 'Productivity', icon: 'bolt' },
];

const SORT_OPTIONS = [
    { id: 'popular', label: 'Popular' },
    { id: 'newest', label: 'Newest' },
    { id: 'price_asc', label: 'Price (Low to High)' },
    { id: 'price_desc', label: 'Price (High to Low)' },
    { id: 'rating', label: 'Rating' },
];

// ---------------------------------------------------------------------------
// Plugin definition
// ---------------------------------------------------------------------------

export default defineEscalatedPlugin({
    name: 'Marketplace',
    slug: 'marketplace',
    version: '0.1.0',
    description: 'Plugin marketplace browser to discover and install plugins',

    extensions: {
        menuItems: [
            {
                id: 'marketplace',
                label: 'Marketplace',
                icon: 'squares-plus',
                route: '/admin/marketplace',
                parent: 'admin',
                order: 90,
                capability: 'manage_plugins',
            },
        ],
        settingsPanels: [
            {
                id: 'marketplace-settings',
                title: 'Marketplace',
                component: MarketplaceBrowser,
                icon: 'squares-plus',
                category: 'plugins',
            },
        ],
        pageComponents: {
            'admin.marketplace': MarketplaceBrowser,
            'marketplace.detail': PluginDetailCard,
        },
    },

    hooks: {
        /**
         * Add "Browse Marketplace" to the admin plugins page header.
         */
        'admin.plugins.header': (items) => {
            return [
                ...items,
                {
                    id: 'browse-marketplace',
                    label: 'Browse Marketplace',
                    icon: 'squares-plus',
                    route: '/admin/marketplace',
                    type: 'button',
                    variant: 'primary',
                },
            ];
        },

        /**
         * Extend admin navigation with marketplace entry.
         */
        'admin.nav': (items) => {
            return [
                ...items,
                {
                    id: 'marketplace',
                    label: 'Marketplace',
                    icon: 'squares-plus',
                    section: 'admin',
                    order: 90,
                },
            ];
        },

        /**
         * Add update badge count to the marketplace menu item when updates
         * are available.
         */
        'admin.menu.badges': (badges, context) => {
            const service = context?.$escalated?.inject?.('marketplace');
            const count = service?.state?.updates?.length || 0;
            if (count > 0) {
                return {
                    ...badges,
                    marketplace: count,
                };
            }
            return badges;
        },
    },

    setup(context) {
        const { reactive, ref } = context.vue || {};
        const _reactive = reactive || ((o) => o);
        const _ref = ref || ((v) => ({ value: v }));

        // ------------------------------------------------------------------
        // Reactive state
        // ------------------------------------------------------------------
        const state = _reactive({
            catalog: [],
            installed: {},
            updates: [],
            selectedPlugin: null,
            showDetail: false,
            filters: {
                category: 'all',
                search: '',
                sort: 'popular',
            },
            pagination: {
                total: 0,
                page: 1,
                per_page: 24,
            },
            loading: false,
            installing: null, // slug of the plugin being installed
            lastCheck: null,
        });

        const saving = _ref(false);

        // ------------------------------------------------------------------
        // API helpers
        // ------------------------------------------------------------------
        const apiBase = () => {
            if (context.route) {
                return context.route('plugins.marketplace.api');
            }
            return '/api/plugins/marketplace';
        };

        async function apiRequest(path, options = {}) {
            const url = `${apiBase()}${path}`;
            const headers = {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            };

            if (options.body && typeof options.body === 'object') {
                headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(options.body);
            }

            const response = await fetch(url, { ...options, headers });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                throw new Error(error.message || `API request failed: ${response.status}`);
            }

            return response.json();
        }

        // ------------------------------------------------------------------
        // Catalog fetching
        // ------------------------------------------------------------------

        async function fetchCatalog(filters = {}) {
            state.loading = true;
            try {
                const mergedFilters = { ...state.filters, ...filters };
                const params = new URLSearchParams();

                if (mergedFilters.category && mergedFilters.category !== 'all') {
                    params.set('category', mergedFilters.category);
                }
                if (mergedFilters.search) {
                    params.set('search', mergedFilters.search);
                }
                if (mergedFilters.sort) {
                    params.set('sort', mergedFilters.sort);
                }

                const query = params.toString();
                const data = await apiRequest(`/catalog${query ? '?' + query : ''}`);

                state.catalog = data.plugins || [];
                state.pagination.total = data.total || 0;

                // Update filters state
                Object.assign(state.filters, mergedFilters);

                return state.catalog;
            } catch (err) {
                console.error('[marketplace] Failed to fetch catalog:', err);
                return [];
            } finally {
                state.loading = false;
            }
        }

        // ------------------------------------------------------------------
        // Plugin detail
        // ------------------------------------------------------------------

        async function fetchPluginDetail(slug) {
            state.loading = true;
            try {
                const data = await apiRequest(`/detail?slug=${encodeURIComponent(slug)}`);
                if (data.plugin) {
                    state.selectedPlugin = data.plugin;
                    state.showDetail = true;
                }
                return data.plugin || null;
            } catch (err) {
                console.error('[marketplace] Failed to fetch plugin detail:', err);
                return null;
            } finally {
                state.loading = false;
            }
        }

        function showPluginDetail(plugin) {
            state.selectedPlugin = plugin;
            state.showDetail = true;
        }

        function hidePluginDetail() {
            state.selectedPlugin = null;
            state.showDetail = false;
        }

        // ------------------------------------------------------------------
        // Install plugin
        // ------------------------------------------------------------------

        async function installPlugin(slug) {
            state.installing = slug;
            saving.value = true;
            try {
                const data = await apiRequest('/install', {
                    method: 'POST',
                    body: { slug },
                });

                if (data.success) {
                    // Update the catalog entry to reflect installed state
                    const plugin = state.catalog.find((p) => p.slug === slug);
                    if (plugin) {
                        plugin.is_installed = true;
                        plugin.installed_version = data.version || plugin.version;
                        plugin.has_update = false;
                    }

                    // Update installed map
                    state.installed[slug] = {
                        slug,
                        version: data.version || '0.1.0',
                        name: data.name || slug,
                        active: true,
                    };

                    // Update the detail view if it is the same plugin
                    if (state.selectedPlugin?.slug === slug) {
                        state.selectedPlugin.is_installed = true;
                        state.selectedPlugin.installed_version = data.version || state.selectedPlugin.version;
                        state.selectedPlugin.has_update = false;
                    }

                    // Notify on success
                    if (context.notify) {
                        context.notify({
                            type: 'success',
                            title: 'Plugin Installed',
                            message: data.message || `${data.name || slug} has been installed.`,
                        });
                    }
                } else {
                    if (context.notify) {
                        context.notify({
                            type: 'error',
                            title: 'Installation Failed',
                            message: data.message || `Could not install ${slug}.`,
                        });
                    }
                }

                return data;
            } catch (err) {
                console.error('[marketplace] Failed to install plugin:', err);

                if (context.notify) {
                    context.notify({
                        type: 'error',
                        title: 'Installation Error',
                        message: err.message || `An error occurred installing ${slug}.`,
                    });
                }

                throw err;
            } finally {
                state.installing = null;
                saving.value = false;
            }
        }

        // ------------------------------------------------------------------
        // Check for updates
        // ------------------------------------------------------------------

        async function checkForUpdates() {
            state.loading = true;
            try {
                const data = await apiRequest('/check_updates');

                state.updates = data.updates || [];
                state.lastCheck = data.checked_at || null;

                // Update catalog entries with update status
                for (const update of state.updates) {
                    const plugin = state.catalog.find((p) => p.slug === update.slug);
                    if (plugin) {
                        plugin.has_update = true;
                    }
                }

                return state.updates;
            } catch (err) {
                console.error('[marketplace] Failed to check for updates:', err);
                return [];
            } finally {
                state.loading = false;
            }
        }

        // ------------------------------------------------------------------
        // Get installed plugins
        // ------------------------------------------------------------------

        async function fetchInstalledPlugins() {
            try {
                const data = await apiRequest('/installed');
                if (data && typeof data === 'object') {
                    state.installed = data;
                }
                return state.installed;
            } catch (err) {
                console.error('[marketplace] Failed to fetch installed plugins:', err);
                return {};
            }
        }

        // ------------------------------------------------------------------
        // Filter helpers
        // ------------------------------------------------------------------

        function setCategory(category) {
            state.filters.category = category;
            fetchCatalog();
        }

        function setSearch(search) {
            state.filters.search = search;
            fetchCatalog();
        }

        function setSort(sort) {
            state.filters.sort = sort;
            fetchCatalog();
        }

        // ------------------------------------------------------------------
        // Provide the marketplace service to child components
        // ------------------------------------------------------------------

        context.provide('marketplace', {
            state,
            saving,
            CATEGORIES,
            SORT_OPTIONS,
            apiRequest,
            // Catalog
            fetchCatalog,
            fetchPluginDetail,
            showPluginDetail,
            hidePluginDetail,
            // Install
            installPlugin,
            // Updates
            checkForUpdates,
            fetchInstalledPlugins,
            // Filters
            setCategory,
            setSearch,
            setSort,
        });
    },
});
