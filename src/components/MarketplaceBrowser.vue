<template>
    <div :class="['mp-root', { 'mp-dark': isDark }]">
        <!-- ============================================================= -->
        <!-- Header                                                        -->
        <!-- ============================================================= -->
        <div class="mp-header">
            <div class="mp-header-left">
                <h2 class="mp-title">Plugin Marketplace</h2>
                <span class="mp-subtitle">
                    Discover and install plugins to extend your helpdesk
                </span>
            </div>
            <div class="mp-header-right">
                <div class="mp-search-wrap">
                    <svg class="mp-search-icon" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Search plugins..."
                        class="mp-search-input"
                        @input="onSearchInput"
                    />
                    <button
                        v-if="searchInput"
                        class="mp-search-clear"
                        @click="clearSearch"
                        title="Clear search"
                    >
                        &times;
                    </button>
                </div>
                <button class="mp-btn mp-btn-outline" @click="handleCheckUpdates" :disabled="loading">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.379 2.624L4.22 12.336a.75.75 0 011.06-1.06l1.713 1.712a4 4 0 006.822-1.908.75.75 0 011.497.344zM4.688 8.576a5.5 5.5 0 019.379-2.624l1.713 1.712a.75.75 0 01-1.06 1.06l-1.713-1.712a4 4 0 00-6.822 1.908.75.75 0 01-1.497-.344z" clip-rule="evenodd" />
                    </svg>
                    Check Updates
                    <span v-if="updates.length" class="mp-badge-count">{{ updates.length }}</span>
                </button>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- Category tabs                                                 -->
        <!-- ============================================================= -->
        <div class="mp-tabs-bar">
            <div class="mp-tabs">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    :class="['mp-tab', { 'mp-tab-active': activeCategory === cat.id }]"
                    @click="selectCategory(cat.id)"
                >
                    {{ cat.label }}
                    <span v-if="cat.id !== 'all' && categoryCounts[cat.id]" class="mp-tab-count">
                        {{ categoryCounts[cat.id] }}
                    </span>
                </button>
            </div>
            <div class="mp-sort-wrap">
                <label class="mp-sort-label">Sort:</label>
                <select v-model="activeSort" class="mp-sort-select" @change="onSortChange">
                    <option v-for="opt in sortOptions" :key="opt.id" :value="opt.id">
                        {{ opt.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- Update banner                                                 -->
        <!-- ============================================================= -->
        <div v-if="updates.length > 0" class="mp-update-banner">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
            </svg>
            <span>{{ updates.length }} plugin{{ updates.length !== 1 ? 's' : '' }} with updates available</span>
        </div>

        <!-- ============================================================= -->
        <!-- Loading indicator                                             -->
        <!-- ============================================================= -->
        <div v-if="loading && plugins.length === 0" class="mp-loading">
            <div class="mp-spinner"></div>
            <span>Loading marketplace...</span>
        </div>

        <!-- ============================================================= -->
        <!-- Empty state                                                   -->
        <!-- ============================================================= -->
        <div v-else-if="!loading && plugins.length === 0" class="mp-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="48" height="48" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <p class="mp-empty-title">No plugins found</p>
            <p class="mp-empty-desc">
                Try adjusting your search or browse a different category.
            </p>
            <button class="mp-btn mp-btn-outline" @click="resetFilters">Clear Filters</button>
        </div>

        <!-- ============================================================= -->
        <!-- Plugin grid                                                   -->
        <!-- ============================================================= -->
        <div v-else class="mp-grid">
            <div
                v-for="plugin in plugins"
                :key="plugin.slug"
                class="mp-card"
                @click="openDetail(plugin)"
            >
                <!-- Card icon -->
                <div class="mp-card-icon" :style="{ backgroundColor: plugin.color || '#6366f1' }">
                    <span class="mp-card-icon-letter">{{ (plugin.name || '?')[0] }}</span>
                </div>

                <!-- Card body -->
                <div class="mp-card-body">
                    <div class="mp-card-title-row">
                        <h3 class="mp-card-name">{{ plugin.name }}</h3>
                        <span v-if="plugin.is_installed && plugin.has_update" class="mp-badge mp-badge-update">Update</span>
                        <span v-else-if="plugin.is_installed" class="mp-badge mp-badge-installed">Installed</span>
                    </div>
                    <p class="mp-card-desc">{{ truncate(plugin.description, 100) }}</p>

                    <div class="mp-card-meta">
                        <span class="mp-card-author">{{ plugin.author }}</span>
                        <span class="mp-card-dot">&middot;</span>
                        <span class="mp-card-version">v{{ plugin.version }}</span>
                    </div>

                    <div class="mp-card-footer">
                        <!-- Price -->
                        <span :class="['mp-price', { 'mp-price-free': plugin.is_free }]">
                            {{ plugin.is_free ? 'Free' : '$' + formatPrice(plugin.price) }}
                        </span>

                        <!-- Rating -->
                        <div class="mp-rating">
                            <div class="mp-stars">
                                <svg
                                    v-for="star in 5"
                                    :key="star"
                                    :class="['mp-star', { 'mp-star-filled': star <= Math.round(plugin.rating || 0) }]"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    width="12"
                                    height="12"
                                >
                                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="mp-rating-num">{{ plugin.rating?.toFixed(1) || '0.0' }}</span>
                        </div>

                        <!-- Downloads -->
                        <span class="mp-downloads">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12">
                                <path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z" />
                                <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                            </svg>
                            {{ formatDownloads(plugin.downloads) }}
                        </span>
                    </div>
                </div>

                <!-- Install / Update button overlay on hover -->
                <div class="mp-card-actions" @click.stop>
                    <button
                        v-if="plugin.is_installed && plugin.has_update"
                        class="mp-btn mp-btn-primary mp-btn-sm"
                        :disabled="installing === plugin.slug"
                        @click.stop="handleInstall(plugin.slug)"
                    >
                        {{ installing === plugin.slug ? 'Updating...' : 'Update' }}
                    </button>
                    <button
                        v-else-if="!plugin.is_installed"
                        class="mp-btn mp-btn-primary mp-btn-sm"
                        :disabled="installing === plugin.slug"
                        @click.stop="handleInstall(plugin.slug)"
                    >
                        {{ installing === plugin.slug ? 'Installing...' : 'Install' }}
                    </button>
                    <span v-else class="mp-installed-check">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        Installed
                    </span>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- Plugin detail modal                                           -->
        <!-- ============================================================= -->
        <PluginDetailCard
            v-if="showDetail && selectedPlugin"
            :plugin="selectedPlugin"
            :installing="installing"
            @close="closeDetail"
            @install="handleInstall"
        />
    </div>
</template>

<script setup>
import { ref, computed, inject, onMounted, watch } from 'vue';
import PluginDetailCard from './PluginDetailCard.vue';

// ---------------------------------------------------------------------------
// Dark mode
// ---------------------------------------------------------------------------
const isDark = inject('esc-dark', false);

// ---------------------------------------------------------------------------
// Marketplace service (provided by index.js setup)
// ---------------------------------------------------------------------------
const marketplace = inject('marketplace', null);

// ---------------------------------------------------------------------------
// Local reactive state for template binding
// ---------------------------------------------------------------------------
const searchInput = ref('');
const activeCategory = ref('all');
const activeSort = ref('popular');
const showDetail = ref(false);
const selectedPlugin = ref(null);

// ---------------------------------------------------------------------------
// Derived data
// ---------------------------------------------------------------------------
const loading = computed(() => marketplace?.state?.loading ?? false);
const installing = computed(() => marketplace?.state?.installing ?? null);
const plugins = computed(() => marketplace?.state?.catalog ?? []);
const updates = computed(() => marketplace?.state?.updates ?? []);

const categories = computed(() => {
    return marketplace?.CATEGORIES ?? [
        { id: 'all', label: 'All' },
        { id: 'integrations', label: 'Integrations' },
        { id: 'automation', label: 'Automation' },
        { id: 'reporting', label: 'Reporting' },
        { id: 'channels', label: 'Channels' },
        { id: 'security', label: 'Security' },
        { id: 'productivity', label: 'Productivity' },
    ];
});

const sortOptions = computed(() => {
    return marketplace?.SORT_OPTIONS ?? [
        { id: 'popular', label: 'Popular' },
        { id: 'newest', label: 'Newest' },
        { id: 'price_asc', label: 'Price (Low to High)' },
        { id: 'price_desc', label: 'Price (High to Low)' },
        { id: 'rating', label: 'Rating' },
    ];
});

/**
 * Count plugins per category from the full catalog.
 */
const categoryCounts = computed(() => {
    const counts = {};
    const allPlugins = marketplace?.state?.catalog ?? [];
    for (const p of allPlugins) {
        const cat = p.category || 'other';
        counts[cat] = (counts[cat] || 0) + 1;
    }
    return counts;
});

// ---------------------------------------------------------------------------
// Search debounce
// ---------------------------------------------------------------------------
let searchTimeout = null;

function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (marketplace) {
            marketplace.setSearch(searchInput.value);
        }
    }, 300);
}

function clearSearch() {
    searchInput.value = '';
    if (marketplace) {
        marketplace.setSearch('');
    }
}

// ---------------------------------------------------------------------------
// Category & sort
// ---------------------------------------------------------------------------
function selectCategory(id) {
    activeCategory.value = id;
    if (marketplace) {
        marketplace.setCategory(id);
    }
}

function onSortChange() {
    if (marketplace) {
        marketplace.setSort(activeSort.value);
    }
}

function resetFilters() {
    searchInput.value = '';
    activeCategory.value = 'all';
    activeSort.value = 'popular';
    if (marketplace) {
        marketplace.fetchCatalog({ category: 'all', search: '', sort: 'popular' });
    }
}

// ---------------------------------------------------------------------------
// Detail panel
// ---------------------------------------------------------------------------
function openDetail(plugin) {
    selectedPlugin.value = plugin;
    showDetail.value = true;
    if (marketplace) {
        marketplace.showPluginDetail(plugin);
    }
}

function closeDetail() {
    selectedPlugin.value = null;
    showDetail.value = false;
    if (marketplace) {
        marketplace.hidePluginDetail();
    }
}

// ---------------------------------------------------------------------------
// Install / Update
// ---------------------------------------------------------------------------
async function handleInstall(slug) {
    if (!marketplace) return;
    try {
        await marketplace.installPlugin(slug);
    } catch {
        // Error is handled inside the service
    }
}

// ---------------------------------------------------------------------------
// Check updates
// ---------------------------------------------------------------------------
async function handleCheckUpdates() {
    if (!marketplace) return;
    await marketplace.checkForUpdates();
}

// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------
function truncate(str, max) {
    if (!str) return '';
    return str.length > max ? str.slice(0, max) + '...' : str;
}

function formatPrice(price) {
    if (!price || price === 0) return '0';
    return Number(price).toFixed(2);
}

function formatDownloads(count) {
    if (!count) return '0';
    if (count >= 1000) return (count / 1000).toFixed(1) + 'k';
    return String(count);
}

// ---------------------------------------------------------------------------
// Sync service state -> local refs when service pushes changes
// ---------------------------------------------------------------------------
watch(
    () => marketplace?.state?.showDetail,
    (val) => {
        showDetail.value = !!val;
        if (val) {
            selectedPlugin.value = marketplace?.state?.selectedPlugin ?? null;
        }
    }
);

// ---------------------------------------------------------------------------
// Lifecycle
// ---------------------------------------------------------------------------
onMounted(() => {
    if (marketplace) {
        marketplace.fetchCatalog();
    }
});
</script>

<style scoped>
/* ======================================================================== */
/* Root & dark mode variables                                               */
/* ======================================================================== */
.mp-root {
    --mp-bg: #ffffff;
    --mp-bg2: #f9fafb;
    --mp-bg3: #f3f4f6;
    --mp-border: #e5e7eb;
    --mp-text: #111827;
    --mp-text2: #4b5563;
    --mp-text3: #9ca3af;
    --mp-accent: #6366f1;
    --mp-accent-hover: #4f46e5;
    --mp-green: #22c55e;
    --mp-green-bg: #f0fdf4;
    --mp-blue: #3b82f6;
    --mp-blue-bg: #eff6ff;
    --mp-orange: #f59e0b;
    --mp-orange-bg: #fffbeb;
    --mp-radius: 10px;
    --mp-radius-sm: 6px;
    --mp-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
    --mp-shadow-lg: 0 4px 12px rgba(0, 0, 0, 0.1);

    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: var(--mp-text);
    background: var(--mp-bg);
    padding: 24px;
    min-height: 100%;
}

.mp-dark {
    --mp-bg: #0f172a;
    --mp-bg2: #1e293b;
    --mp-bg3: #334155;
    --mp-border: #334155;
    --mp-text: #f1f5f9;
    --mp-text2: #94a3b8;
    --mp-text3: #64748b;
    --mp-accent: #818cf8;
    --mp-accent-hover: #6366f1;
    --mp-green-bg: #052e16;
    --mp-blue-bg: #172554;
    --mp-orange-bg: #451a03;
    --mp-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    --mp-shadow-lg: 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* ======================================================================== */
/* Header                                                                   */
/* ======================================================================== */
.mp-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.mp-header-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mp-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: var(--mp-text);
}

.mp-subtitle {
    font-size: 13px;
    color: var(--mp-text3);
}

.mp-header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

/* ======================================================================== */
/* Search                                                                   */
/* ======================================================================== */
.mp-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.mp-search-icon {
    position: absolute;
    left: 10px;
    color: var(--mp-text3);
    pointer-events: none;
}

.mp-search-input {
    width: 260px;
    padding: 7px 30px 7px 32px;
    border: 1px solid var(--mp-border);
    border-radius: var(--mp-radius-sm);
    background: var(--mp-bg2);
    color: var(--mp-text);
    font-size: 13px;
    outline: none;
    transition: border-color 0.15s;
}

.mp-search-input:focus {
    border-color: var(--mp-accent);
}

.mp-search-input::placeholder {
    color: var(--mp-text3);
}

.mp-search-clear {
    position: absolute;
    right: 6px;
    width: 20px;
    height: 20px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--mp-text3);
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    line-height: 1;
}

.mp-search-clear:hover {
    color: var(--mp-text2);
}

/* ======================================================================== */
/* Buttons                                                                  */
/* ======================================================================== */
.mp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    border-radius: var(--mp-radius-sm);
    cursor: pointer;
    transition: all 0.15s;
    border: none;
    white-space: nowrap;
}

.mp-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.mp-btn-primary {
    background: var(--mp-accent);
    color: #ffffff;
}

.mp-btn-primary:hover:not(:disabled) {
    background: var(--mp-accent-hover);
}

.mp-btn-outline {
    background: var(--mp-bg);
    border: 1px solid var(--mp-border);
    color: var(--mp-text2);
}

.mp-btn-outline:hover:not(:disabled) {
    background: var(--mp-bg2);
    border-color: var(--mp-accent);
    color: var(--mp-accent);
}

.mp-btn-sm {
    padding: 5px 12px;
    font-size: 12px;
}

.mp-badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 9px;
    background: var(--mp-accent);
    color: #fff;
}

/* ======================================================================== */
/* Tabs bar                                                                 */
/* ======================================================================== */
.mp-tabs-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--mp-border);
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.mp-tabs {
    display: flex;
    gap: 0;
    overflow-x: auto;
}

.mp-tab {
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 500;
    color: var(--mp-text3);
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.mp-tab:hover {
    color: var(--mp-text2);
}

.mp-tab-active {
    color: var(--mp-accent);
    border-bottom-color: var(--mp-accent);
}

.mp-tab-count {
    font-size: 11px;
    padding: 1px 6px;
    border-radius: 8px;
    background: var(--mp-bg3);
    color: var(--mp-text3);
}

.mp-tab-active .mp-tab-count {
    background: var(--mp-accent);
    color: #ffffff;
}

/* ======================================================================== */
/* Sort                                                                     */
/* ======================================================================== */
.mp-sort-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    padding-bottom: 8px;
}

.mp-sort-label {
    font-size: 12px;
    color: var(--mp-text3);
    white-space: nowrap;
}

.mp-sort-select {
    padding: 5px 8px;
    font-size: 12px;
    border: 1px solid var(--mp-border);
    border-radius: var(--mp-radius-sm);
    background: var(--mp-bg2);
    color: var(--mp-text);
    outline: none;
    cursor: pointer;
}

.mp-sort-select:focus {
    border-color: var(--mp-accent);
}

/* ======================================================================== */
/* Update banner                                                            */
/* ======================================================================== */
.mp-update-banner {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    margin-bottom: 16px;
    border-radius: var(--mp-radius-sm);
    background: var(--mp-blue-bg);
    color: var(--mp-blue);
    font-size: 13px;
    font-weight: 500;
    border: 1px solid color-mix(in srgb, var(--mp-blue) 20%, transparent);
}

/* ======================================================================== */
/* Loading & empty states                                                   */
/* ======================================================================== */
.mp-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 60px 20px;
    color: var(--mp-text3);
    font-size: 14px;
}

.mp-spinner {
    width: 28px;
    height: 28px;
    border: 3px solid var(--mp-border);
    border-top-color: var(--mp-accent);
    border-radius: 50%;
    animation: mp-spin 0.7s linear infinite;
}

@keyframes mp-spin {
    to {
        transform: rotate(360deg);
    }
}

.mp-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 60px 20px;
    color: var(--mp-text3);
    text-align: center;
}

.mp-empty-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--mp-text2);
}

.mp-empty-desc {
    margin: 0;
    font-size: 13px;
    max-width: 320px;
}

/* ======================================================================== */
/* Plugin grid                                                              */
/* ======================================================================== */
.mp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

@media (min-width: 1280px) {
    .mp-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 960px) and (max-width: 1279px) {
    .mp-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* ======================================================================== */
/* Plugin card                                                              */
/* ======================================================================== */
.mp-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: var(--mp-bg);
    border: 1px solid var(--mp-border);
    border-radius: var(--mp-radius);
    padding: 16px;
    cursor: pointer;
    transition: box-shadow 0.2s, border-color 0.2s, transform 0.15s;
    overflow: hidden;
}

.mp-card:hover {
    box-shadow: var(--mp-shadow-lg);
    border-color: var(--mp-accent);
    transform: translateY(-1px);
}

.mp-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-bottom: 12px;
}

.mp-card-icon-letter {
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    text-transform: uppercase;
}

.mp-card-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.mp-card-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.mp-card-name {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--mp-text);
    line-height: 1.3;
}

.mp-card-desc {
    margin: 0;
    font-size: 12px;
    color: var(--mp-text2);
    line-height: 1.5;
    flex: 1;
}

.mp-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--mp-text3);
    margin-top: 4px;
}

.mp-card-dot {
    font-size: 8px;
}

.mp-card-footer {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--mp-border);
}

/* ======================================================================== */
/* Price badge                                                              */
/* ======================================================================== */
.mp-price {
    font-size: 12px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--mp-bg3);
    color: var(--mp-text2);
}

.mp-price-free {
    background: var(--mp-green-bg);
    color: var(--mp-green);
}

/* ======================================================================== */
/* Rating                                                                   */
/* ======================================================================== */
.mp-rating {
    display: flex;
    align-items: center;
    gap: 4px;
}

.mp-stars {
    display: flex;
    gap: 1px;
}

.mp-star {
    color: var(--mp-border);
}

.mp-star-filled {
    color: #f59e0b;
}

.mp-rating-num {
    font-size: 11px;
    color: var(--mp-text3);
    font-weight: 500;
}

/* ======================================================================== */
/* Downloads                                                                */
/* ======================================================================== */
.mp-downloads {
    display: flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    color: var(--mp-text3);
    margin-left: auto;
}

/* ======================================================================== */
/* Badges                                                                   */
/* ======================================================================== */
.mp-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    flex-shrink: 0;
}

.mp-badge-installed {
    background: var(--mp-green-bg);
    color: var(--mp-green);
}

.mp-badge-update {
    background: var(--mp-orange-bg);
    color: var(--mp-orange);
}

/* ======================================================================== */
/* Card hover actions overlay                                               */
/* ======================================================================== */
.mp-card-actions {
    position: absolute;
    bottom: 12px;
    right: 12px;
    opacity: 0;
    transition: opacity 0.15s;
}

.mp-card:hover .mp-card-actions {
    opacity: 1;
}

.mp-installed-check {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--mp-green);
    font-size: 12px;
    font-weight: 500;
}
</style>
