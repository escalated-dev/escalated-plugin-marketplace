import { defineEscalatedPlugin } from '@escalated-dev/escalated';
import MarketplaceBrowser from './components/MarketplaceBrowser.vue';

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
                route: '/marketplace',
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
            marketplace: MarketplaceBrowser,
        },
    },

    hooks: {},

    setup(context) {
        context.provide('marketplace', {
            // Marketplace service will be provided here
        });
    },
});
