<template>
    <div class="marketplace">
        <div class="marketplace-header">
            <h3>Plugin Marketplace</h3>
            <div class="marketplace-search">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search plugins..."
                    class="marketplace-search-input"
                />
            </div>
        </div>
        <div class="marketplace-body">
            <p class="marketplace-placeholder">Plugin marketplace is coming soon.</p>
            <p class="marketplace-info">
                Browse, discover, and install plugins to extend Escalated. Find integrations, channels, AI tools, and
                workflow enhancements built by the community and Escalated team.
            </p>
            <div class="marketplace-categories">
                <span class="category-tag">Channels</span>
                <span class="category-tag">Integrations</span>
                <span class="category-tag">AI & Automation</span>
                <span class="category-tag">Security</span>
                <span class="category-tag">Reporting</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const searchQuery = ref('');
const plugins = ref([]);
</script>

<style scoped>
.marketplace {
    background: var(--escalated-bg-secondary, #1e1e2e);
    border: 1px solid var(--escalated-border, #2e2e3e);
    border-radius: 8px;
    padding: 16px;
    color: var(--escalated-text, #e0e0e0);
}

.marketplace-header {
    margin-bottom: 12px;
}

.marketplace-header h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
}

.marketplace-search-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--escalated-border, #2e2e3e);
    border-radius: 6px;
    background: var(--escalated-bg-tertiary, #0d0d1a);
    color: var(--escalated-text, #e0e0e0);
    font-size: 13px;
    outline: none;
    box-sizing: border-box;
}

.marketplace-search-input:focus {
    border-color: var(--escalated-accent, #6366f1);
}

.marketplace-search-input::placeholder {
    color: var(--escalated-text-muted, #666);
}

.marketplace-placeholder {
    font-size: 13px;
    color: var(--escalated-text-muted, #888);
    margin-bottom: 8px;
}

.marketplace-info {
    font-size: 12px;
    color: var(--escalated-text-muted, #666);
    line-height: 1.5;
    margin-bottom: 16px;
}

.marketplace-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.category-tag {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 12px;
    background: var(--escalated-bg-tertiary, #2e2e3e);
    color: var(--escalated-text-muted, #888);
}
</style>
