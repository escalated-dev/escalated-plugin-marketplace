<template>
    <teleport to="body">
        <div class="pd-overlay" @click.self="$emit('close')">
            <div :class="['pd-modal', { 'pd-dark': isDark }]">
                <!-- ============================================================= -->
                <!-- Modal header                                                  -->
                <!-- ============================================================= -->
                <div class="pd-modal-header">
                    <button class="pd-close" @click="$emit('close')" title="Close">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                </div>

                <!-- ============================================================= -->
                <!-- Modal body                                                    -->
                <!-- ============================================================= -->
                <div class="pd-body">
                    <!-- Left: main content -->
                    <div class="pd-main">
                        <!-- Icon + title section -->
                        <div class="pd-hero">
                            <div class="pd-icon" :style="{ backgroundColor: plugin.color || '#6366f1' }">
                                <span class="pd-icon-letter">{{ (plugin.name || '?')[0] }}</span>
                            </div>
                            <div class="pd-hero-info">
                                <h2 class="pd-name">{{ plugin.name }}</h2>
                                <div class="pd-meta-row">
                                    <span class="pd-author">by {{ plugin.author || 'Escalated' }}</span>
                                    <span class="pd-dot">&middot;</span>
                                    <span class="pd-version">v{{ plugin.version }}</span>
                                    <span class="pd-dot">&middot;</span>
                                    <span class="pd-category-badge">{{ formatCategory(plugin.category) }}</span>
                                </div>
                                <div class="pd-stats-row">
                                    <!-- Rating -->
                                    <div class="pd-rating">
                                        <div class="pd-stars">
                                            <svg
                                                v-for="star in 5"
                                                :key="star"
                                                :class="['pd-star', { 'pd-star-filled': star <= Math.round(plugin.rating || 0) }]"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                width="14"
                                                height="14"
                                            >
                                                <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <span class="pd-rating-num">{{ (plugin.rating || 0).toFixed(1) }}</span>
                                    </div>

                                    <!-- Downloads -->
                                    <div class="pd-downloads">
                                        <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                            <path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z" />
                                            <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                                        </svg>
                                        <span>{{ formatDownloads(plugin.downloads) }} downloads</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="pd-section">
                            <h3 class="pd-section-title">Description</h3>
                            <p class="pd-description">{{ plugin.description }}</p>
                        </div>

                        <!-- Screenshots carousel -->
                        <div v-if="screenshots.length > 0" class="pd-section">
                            <h3 class="pd-section-title">Screenshots</h3>
                            <div class="pd-screenshots">
                                <div class="pd-screenshots-track" :style="{ transform: `translateX(-${screenshotIndex * 100}%)` }">
                                    <div
                                        v-for="(shot, idx) in screenshots"
                                        :key="idx"
                                        class="pd-screenshot"
                                    >
                                        <div class="pd-screenshot-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="32" height="32" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 001.5-1.5V4.5a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v15a1.5 1.5 0 001.5 1.5z" />
                                            </svg>
                                            <span>{{ shot }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="screenshots.length > 1" class="pd-screenshot-nav">
                                    <button
                                        class="pd-screenshot-btn"
                                        :disabled="screenshotIndex === 0"
                                        @click="screenshotIndex--"
                                    >
                                        <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <span class="pd-screenshot-counter">
                                        {{ screenshotIndex + 1 }} / {{ screenshots.length }}
                                    </span>
                                    <button
                                        class="pd-screenshot-btn"
                                        :disabled="screenshotIndex >= screenshots.length - 1"
                                        @click="screenshotIndex++"
                                    >
                                        <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Changelog -->
                        <div v-if="plugin.changelog" class="pd-section">
                            <h3 class="pd-section-title">What's New</h3>
                            <p class="pd-changelog">{{ plugin.changelog }}</p>
                        </div>

                        <!-- Tags -->
                        <div v-if="plugin.tags && plugin.tags.length" class="pd-section">
                            <h3 class="pd-section-title">Tags</h3>
                            <div class="pd-tags">
                                <span v-for="tag in plugin.tags" :key="tag" class="pd-tag">{{ tag }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: sidebar -->
                    <div class="pd-sidebar">
                        <!-- Price & CTA -->
                        <div class="pd-sidebar-card pd-cta-card">
                            <div class="pd-price-display">
                                <span v-if="plugin.is_free" class="pd-price-free">Free</span>
                                <template v-else>
                                    <span class="pd-price-amount">${{ formatPrice(plugin.price) }}</span>
                                    <span class="pd-price-period">/ 6-month license</span>
                                </template>
                            </div>

                            <button
                                v-if="plugin.is_installed && plugin.has_update"
                                class="pd-btn pd-btn-primary pd-btn-full"
                                :disabled="installing === plugin.slug"
                                @click="$emit('install', plugin.slug)"
                            >
                                <svg v-if="installing === plugin.slug" class="pd-btn-spinner" viewBox="0 0 20 20" width="14" height="14">
                                    <circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2" opacity="0.3" />
                                    <path d="M10 2a8 8 0 018 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                                {{ installing === plugin.slug ? 'Updating...' : 'Update to v' + plugin.version }}
                            </button>
                            <button
                                v-else-if="!plugin.is_installed"
                                class="pd-btn pd-btn-primary pd-btn-full"
                                :disabled="installing === plugin.slug"
                                @click="$emit('install', plugin.slug)"
                            >
                                <svg v-if="installing === plugin.slug" class="pd-btn-spinner" viewBox="0 0 20 20" width="14" height="14">
                                    <circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2" opacity="0.3" />
                                    <path d="M10 2a8 8 0 018 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                                {{ installing === plugin.slug ? 'Installing...' : 'Install Plugin' }}
                            </button>
                            <div v-else class="pd-installed-badge">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                Installed
                                <span v-if="plugin.installed_version" class="pd-installed-ver">
                                    v{{ plugin.installed_version }}
                                </span>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="pd-sidebar-card">
                            <h4 class="pd-sidebar-title">Requirements</h4>
                            <div class="pd-req-row">
                                <span class="pd-req-label">Escalated</span>
                                <span class="pd-req-value">v{{ plugin.requires || '0.6.0' }}+</span>
                            </div>
                            <div class="pd-req-row">
                                <span class="pd-req-label">Category</span>
                                <span class="pd-req-value">{{ formatCategory(plugin.category) }}</span>
                            </div>
                            <div class="pd-req-row">
                                <span class="pd-req-label">Version</span>
                                <span class="pd-req-value">{{ plugin.version }}</span>
                            </div>
                            <div class="pd-req-row">
                                <span class="pd-req-label">Author</span>
                                <span class="pd-req-value">{{ plugin.author || 'Escalated' }}</span>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="pd-sidebar-card">
                            <h4 class="pd-sidebar-title">Statistics</h4>
                            <div class="pd-stat-grid">
                                <div class="pd-stat">
                                    <span class="pd-stat-value">{{ (plugin.rating || 0).toFixed(1) }}</span>
                                    <span class="pd-stat-label">Rating</span>
                                </div>
                                <div class="pd-stat">
                                    <span class="pd-stat-value">{{ formatDownloads(plugin.downloads) }}</span>
                                    <span class="pd-stat-label">Downloads</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { ref, computed, inject } from 'vue';

// ---------------------------------------------------------------------------
// Props & emits
// ---------------------------------------------------------------------------
const props = defineProps({
    plugin: {
        type: Object,
        required: true,
    },
    installing: {
        type: String,
        default: null,
    },
});

defineEmits(['close', 'install']);

// ---------------------------------------------------------------------------
// Dark mode
// ---------------------------------------------------------------------------
const isDark = inject('esc-dark', false);

// ---------------------------------------------------------------------------
// Screenshots carousel
// ---------------------------------------------------------------------------
const screenshotIndex = ref(0);

const screenshots = computed(() => {
    return props.plugin?.screenshots || [];
});

// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------
function formatPrice(price) {
    if (!price || price === 0) return '0.00';
    return Number(price).toFixed(2);
}

function formatDownloads(count) {
    if (!count) return '0';
    if (count >= 1000) return (count / 1000).toFixed(1) + 'k';
    return String(count);
}

function formatCategory(cat) {
    if (!cat) return 'Other';
    return cat.charAt(0).toUpperCase() + cat.slice(1);
}
</script>

<style scoped>
/* ======================================================================== */
/* Variables                                                                */
/* ======================================================================== */
.pd-modal {
    --pd-bg: #ffffff;
    --pd-bg2: #f9fafb;
    --pd-bg3: #f3f4f6;
    --pd-border: #e5e7eb;
    --pd-text: #111827;
    --pd-text2: #4b5563;
    --pd-text3: #9ca3af;
    --pd-accent: #6366f1;
    --pd-accent-hover: #4f46e5;
    --pd-green: #22c55e;
    --pd-green-bg: #f0fdf4;
    --pd-orange: #f59e0b;
    --pd-radius: 12px;
    --pd-radius-sm: 6px;
}

.pd-dark {
    --pd-bg: #0f172a;
    --pd-bg2: #1e293b;
    --pd-bg3: #334155;
    --pd-border: #334155;
    --pd-text: #f1f5f9;
    --pd-text2: #94a3b8;
    --pd-text3: #64748b;
    --pd-accent: #818cf8;
    --pd-accent-hover: #6366f1;
    --pd-green-bg: #052e16;
}

/* ======================================================================== */
/* Overlay & modal                                                          */
/* ======================================================================== */
.pd-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    overflow-y: auto;
}

.pd-modal {
    width: 100%;
    max-width: 880px;
    max-height: 90vh;
    background: var(--pd-bg);
    border: 1px solid var(--pd-border);
    border-radius: var(--pd-radius);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: var(--pd-text);
}

/* ======================================================================== */
/* Modal header                                                             */
/* ======================================================================== */
.pd-modal-header {
    display: flex;
    justify-content: flex-end;
    padding: 12px 16px 0;
}

.pd-close {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    border-radius: var(--pd-radius-sm);
    color: var(--pd-text3);
    cursor: pointer;
    transition: all 0.15s;
}

.pd-close:hover {
    background: var(--pd-bg3);
    color: var(--pd-text);
}

/* ======================================================================== */
/* Modal body (two-column layout)                                           */
/* ======================================================================== */
.pd-body {
    display: flex;
    gap: 24px;
    padding: 16px 24px 24px;
    overflow-y: auto;
    flex: 1;
}

@media (max-width: 640px) {
    .pd-body {
        flex-direction: column;
    }
}

.pd-main {
    flex: 1;
    min-width: 0;
}

.pd-sidebar {
    width: 260px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

@media (max-width: 640px) {
    .pd-sidebar {
        width: 100%;
    }
}

/* ======================================================================== */
/* Hero                                                                     */
/* ======================================================================== */
.pd-hero {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
}

.pd-icon {
    width: 64px;
    height: 64px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pd-icon-letter {
    color: #ffffff;
    font-size: 28px;
    font-weight: 700;
    text-transform: uppercase;
}

.pd-hero-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.pd-name {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--pd-text);
    line-height: 1.2;
}

.pd-meta-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--pd-text3);
    flex-wrap: wrap;
}

.pd-dot {
    font-size: 8px;
}

.pd-category-badge {
    padding: 1px 8px;
    border-radius: 4px;
    background: var(--pd-bg3);
    color: var(--pd-text2);
    font-size: 11px;
    font-weight: 500;
}

.pd-stats-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 4px;
}

/* ======================================================================== */
/* Rating                                                                   */
/* ======================================================================== */
.pd-rating {
    display: flex;
    align-items: center;
    gap: 4px;
}

.pd-stars {
    display: flex;
    gap: 1px;
}

.pd-star {
    color: var(--pd-border);
}

.pd-star-filled {
    color: #f59e0b;
}

.pd-rating-num {
    font-size: 12px;
    color: var(--pd-text2);
    font-weight: 600;
}

/* ======================================================================== */
/* Downloads                                                                */
/* ======================================================================== */
.pd-downloads {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--pd-text3);
}

/* ======================================================================== */
/* Sections                                                                 */
/* ======================================================================== */
.pd-section {
    margin-bottom: 20px;
}

.pd-section-title {
    margin: 0 0 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--pd-text);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.pd-description {
    margin: 0;
    font-size: 13px;
    color: var(--pd-text2);
    line-height: 1.65;
}

.pd-changelog {
    margin: 0;
    font-size: 13px;
    color: var(--pd-text2);
    line-height: 1.6;
    padding: 10px 14px;
    background: var(--pd-bg2);
    border-radius: var(--pd-radius-sm);
    border-left: 3px solid var(--pd-accent);
}

/* ======================================================================== */
/* Screenshots                                                              */
/* ======================================================================== */
.pd-screenshots {
    position: relative;
    overflow: hidden;
    border-radius: var(--pd-radius-sm);
    border: 1px solid var(--pd-border);
    background: var(--pd-bg2);
}

.pd-screenshots-track {
    display: flex;
    transition: transform 0.3s ease;
}

.pd-screenshot {
    flex: 0 0 100%;
    min-width: 100%;
}

.pd-screenshot-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 180px;
    color: var(--pd-text3);
    font-size: 12px;
}

.pd-screenshot-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 8px;
    border-top: 1px solid var(--pd-border);
}

.pd-screenshot-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--pd-border);
    border-radius: var(--pd-radius-sm);
    background: var(--pd-bg);
    color: var(--pd-text2);
    cursor: pointer;
    transition: all 0.15s;
}

.pd-screenshot-btn:hover:not(:disabled) {
    border-color: var(--pd-accent);
    color: var(--pd-accent);
}

.pd-screenshot-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.pd-screenshot-counter {
    font-size: 12px;
    color: var(--pd-text3);
}

/* ======================================================================== */
/* Tags                                                                     */
/* ======================================================================== */
.pd-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.pd-tag {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 12px;
    background: var(--pd-bg3);
    color: var(--pd-text2);
}

/* ======================================================================== */
/* Sidebar cards                                                            */
/* ======================================================================== */
.pd-sidebar-card {
    padding: 14px;
    border: 1px solid var(--pd-border);
    border-radius: var(--pd-radius-sm);
    background: var(--pd-bg2);
}

.pd-sidebar-title {
    margin: 0 0 10px;
    font-size: 12px;
    font-weight: 600;
    color: var(--pd-text);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

/* ======================================================================== */
/* CTA card (price + install)                                               */
/* ======================================================================== */
.pd-cta-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pd-price-display {
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.pd-price-free {
    font-size: 22px;
    font-weight: 700;
    color: var(--pd-green);
}

.pd-price-amount {
    font-size: 22px;
    font-weight: 700;
    color: var(--pd-text);
}

.pd-price-period {
    font-size: 12px;
    color: var(--pd-text3);
}

/* ======================================================================== */
/* Buttons                                                                  */
/* ======================================================================== */
.pd-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: var(--pd-radius-sm);
    cursor: pointer;
    transition: all 0.15s;
    border: none;
}

.pd-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.pd-btn-primary {
    background: var(--pd-accent);
    color: #ffffff;
}

.pd-btn-primary:hover:not(:disabled) {
    background: var(--pd-accent-hover);
}

.pd-btn-full {
    width: 100%;
}

.pd-btn-spinner {
    animation: pd-spin 0.7s linear infinite;
}

@keyframes pd-spin {
    to {
        transform: rotate(360deg);
    }
}

/* ======================================================================== */
/* Installed badge                                                          */
/* ======================================================================== */
.pd-installed-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--pd-green);
    background: var(--pd-green-bg);
    border-radius: var(--pd-radius-sm);
}

.pd-installed-ver {
    font-weight: 400;
    font-size: 12px;
    color: var(--pd-text3);
}

/* ======================================================================== */
/* Requirements rows                                                        */
/* ======================================================================== */
.pd-req-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 12px;
    border-bottom: 1px solid var(--pd-border);
}

.pd-req-row:last-child {
    border-bottom: none;
}

.pd-req-label {
    color: var(--pd-text3);
}

.pd-req-value {
    color: var(--pd-text);
    font-weight: 500;
}

/* ======================================================================== */
/* Stats grid                                                               */
/* ======================================================================== */
.pd-stat-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.pd-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 8px;
    background: var(--pd-bg);
    border-radius: var(--pd-radius-sm);
}

.pd-stat-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--pd-text);
}

.pd-stat-label {
    font-size: 11px;
    color: var(--pd-text3);
}
</style>
