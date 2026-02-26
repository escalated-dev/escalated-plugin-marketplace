<?php

/**
 * Marketplace Plugin for Escalated
 *
 * Provides a plugin marketplace browser so administrators can discover,
 * install, and update plugins directly from the Escalated admin panel.
 * Includes a local catalog of all 24 official Escalated plugins with
 * metadata (name, description, version, pricing, ratings, etc.), stub
 * endpoints for installation and update checking, and page/menu
 * registration for the admin-facing marketplace UI.
 */

// Prevent direct access
if (!defined('ESCALATED_LOADED')) {
    exit('Direct access not allowed.');
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

define('ESC_MARKETPLACE_VERSION', '0.1.0');
define('ESC_MARKETPLACE_SLUG', 'marketplace');
define('ESC_MARKETPLACE_CONFIG_DIR', __DIR__ . '/config');
define('ESC_MARKETPLACE_CONFIG_FILE', ESC_MARKETPLACE_CONFIG_DIR . '/settings.json');

// ---------------------------------------------------------------------------
// Plugin catalog -- all 24 official Escalated plugins
// ---------------------------------------------------------------------------

/**
 * Return the full local catalog of available Escalated plugins.
 *
 * Each entry includes slug, name, description, version, author, category,
 * price metadata, icon hint, rating, download count, and screenshot
 * placeholders.
 *
 * Categories: integrations, automation, reporting, channels, security,
 *             productivity
 *
 * @return array
 */
function esc_marketplace_catalog(): array
{
    return [
        [
            'slug'        => 'ai-copilot',
            'name'        => 'AI Copilot',
            'description' => 'AI-powered reply suggestions, ticket summarization, and auto-categorization. Helps agents respond faster with context-aware suggested replies, automatically summarises long ticket threads, and classifies incoming tickets into the right categories.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'automation',
            'price'       => 29.00,
            'is_free'     => false,
            'icon'        => 'sparkles',
            'color'       => '#8b5cf6',
            'rating'      => 4.8,
            'downloads'   => 3420,
            'screenshots' => ['ai-copilot-1.png', 'ai-copilot-2.png', 'ai-copilot-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with reply suggestions, summarization, and auto-categorization.',
            'tags'        => ['ai', 'automation', 'replies', 'summarization'],
        ],
        [
            'slug'        => 'approvals',
            'name'        => 'Approvals',
            'description' => 'Approval workflows on tickets with request, approve, reject, and escalate actions. Define multi-level approval chains, set up automatic routing based on ticket properties, and track approval history with full audit trails.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'automation',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'check-badge',
            'color'       => '#22c55e',
            'rating'      => 4.5,
            'downloads'   => 2180,
            'screenshots' => ['approvals-1.png', 'approvals-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with multi-level approval chains and audit trails.',
            'tags'        => ['workflow', 'approvals', 'automation'],
        ],
        [
            'slug'        => 'community',
            'name'        => 'Community Forums',
            'description' => 'Community forums with topics, replies, upvotes, and moderation. Build a self-service knowledge community where customers can ask questions, share solutions, and vote on helpful answers. Includes moderation tools and ticket conversion.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'productivity',
            'price'       => 19.00,
            'is_free'     => false,
            'icon'        => 'chat-bubble-left-right',
            'color'       => '#3b82f6',
            'rating'      => 4.6,
            'downloads'   => 1890,
            'screenshots' => ['community-1.png', 'community-2.png', 'community-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with forums, voting, moderation, and ticket conversion.',
            'tags'        => ['community', 'forums', 'self-service', 'knowledge'],
        ],
        [
            'slug'        => 'compliance',
            'name'        => 'Compliance & Audit',
            'description' => 'HIPAA/SOC2/GDPR compliance tools, data audit, and access logs. Monitor data access patterns, generate compliance reports, enforce retention policies, and maintain detailed audit trails for regulatory requirements.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'security',
            'price'       => 39.00,
            'is_free'     => false,
            'icon'        => 'shield-check',
            'color'       => '#ef4444',
            'rating'      => 4.7,
            'downloads'   => 1560,
            'screenshots' => ['compliance-1.png', 'compliance-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with HIPAA, SOC2, and GDPR compliance tooling.',
            'tags'        => ['compliance', 'audit', 'security', 'GDPR', 'HIPAA'],
        ],
        [
            'slug'        => 'custom-layouts',
            'name'        => 'Custom Layouts',
            'description' => 'Custom workspace layouts for agent ticket views. Customize ticket detail field ordering, visibility, and list view column configuration. Includes layout presets (Default, Compact, Detailed) and per-category overrides.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'productivity',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'view-columns',
            'color'       => '#6366f1',
            'rating'      => 4.3,
            'downloads'   => 2750,
            'screenshots' => ['custom-layouts-1.png', 'custom-layouts-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with layout presets and per-category overrides.',
            'tags'        => ['layouts', 'customization', 'views', 'productivity'],
        ],
        [
            'slug'        => 'custom-objects',
            'name'        => 'Custom Objects',
            'description' => 'Custom data objects UI for managing custom object records. Define custom schemas, create and manage records, link objects to tickets and contacts, and build custom views for your unique data models.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'productivity',
            'price'       => 19.00,
            'is_free'     => false,
            'icon'        => 'cube',
            'color'       => '#f59e0b',
            'rating'      => 4.4,
            'downloads'   => 1340,
            'screenshots' => ['custom-objects-1.png', 'custom-objects-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with schema builder, record management, and linking.',
            'tags'        => ['custom-objects', 'data', 'schema', 'records'],
        ],
        [
            'slug'        => 'ip-restriction',
            'name'        => 'IP Restriction',
            'description' => 'IP whitelist/blocklist for agent access control. Restrict agent login to specific IP addresses or ranges, block known malicious IPs, and enforce geographic access policies for enhanced security.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'security',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'shield-exclamation',
            'color'       => '#dc2626',
            'rating'      => 4.2,
            'downloads'   => 3100,
            'screenshots' => ['ip-restriction-1.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with IP whitelist, blocklist, and range support.',
            'tags'        => ['security', 'ip', 'access-control', 'whitelist'],
        ],
        [
            'slug'        => 'jira',
            'name'        => 'Jira Integration',
            'description' => 'Jira issue linking, sync status, and create Jira issues from tickets. Bi-directional sync between Escalated tickets and Jira issues, with automatic status updates and seamless issue creation from the ticket sidebar.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'integrations',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'link',
            'color'       => '#2563eb',
            'rating'      => 4.6,
            'downloads'   => 4200,
            'screenshots' => ['jira-1.png', 'jira-2.png', 'jira-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with bi-directional sync and issue creation.',
            'tags'        => ['jira', 'integration', 'issue-tracking', 'sync'],
        ],
        [
            'slug'        => 'kb-ai',
            'name'        => 'KB AI',
            'description' => 'AI-powered KB article suggestions, content gap analysis, and auto-draft. Automatically suggests relevant knowledge base articles to agents, identifies gaps in your documentation, and drafts new articles from resolved tickets.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'automation',
            'price'       => 24.00,
            'is_free'     => false,
            'icon'        => 'book-open',
            'color'       => '#a855f7',
            'rating'      => 4.5,
            'downloads'   => 1780,
            'screenshots' => ['kb-ai-1.png', 'kb-ai-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with article suggestions, gap analysis, and auto-drafting.',
            'tags'        => ['ai', 'knowledge-base', 'articles', 'content'],
        ],
        [
            'slug'        => 'livechat',
            'name'        => 'Live Chat',
            'description' => 'Real-time live chat messaging widget for customer support. Embed a fully customizable chat widget on your website for instant customer communication, with typing indicators, file sharing, and agent transfer capabilities.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'channels',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'chat-bubble-bottom-center-text',
            'color'       => '#10b981',
            'rating'      => 4.7,
            'downloads'   => 5100,
            'screenshots' => ['livechat-1.png', 'livechat-2.png', 'livechat-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with customizable widget, file sharing, and transfers.',
            'tags'        => ['chat', 'live', 'widget', 'real-time', 'messaging'],
        ],
        [
            'slug'        => 'marketplace',
            'name'        => 'Marketplace',
            'description' => 'Plugin marketplace browser to discover and install plugins. Browse, search, and install Escalated plugins from a curated catalog, check for updates, and manage your plugin ecosystem from one central interface.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'productivity',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'squares-plus',
            'color'       => '#6366f1',
            'rating'      => 4.4,
            'downloads'   => 6200,
            'screenshots' => ['marketplace-1.png', 'marketplace-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with catalog browsing, search, and install support.',
            'tags'        => ['marketplace', 'plugins', 'install', 'discover'],
        ],
        [
            'slug'        => 'mobile-sdk',
            'name'        => 'Mobile SDK',
            'description' => 'Mobile SDK documentation, helpers, and push notification configuration. Provides tools for integrating Escalated into native iOS and Android apps, including push notification setup, deep linking, and SDK code generators.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'channels',
            'price'       => 29.00,
            'is_free'     => false,
            'icon'        => 'device-phone-mobile',
            'color'       => '#14b8a6',
            'rating'      => 4.3,
            'downloads'   => 980,
            'screenshots' => ['mobile-sdk-1.png', 'mobile-sdk-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with iOS/Android SDK helpers and push notifications.',
            'tags'        => ['mobile', 'sdk', 'push-notifications', 'ios', 'android'],
        ],
        [
            'slug'        => 'nps',
            'name'        => 'NPS Surveys',
            'description' => 'Net Promoter Score surveys with scheduling and analytics. Automatically send NPS surveys after ticket resolution, track scores over time, segment by agent/team/category, and visualize trends on a rich dashboard.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'reporting',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'chart-bar',
            'color'       => '#f97316',
            'rating'      => 4.5,
            'downloads'   => 2340,
            'screenshots' => ['nps-1.png', 'nps-2.png', 'nps-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with NPS surveys, scheduling, and analytics dashboard.',
            'tags'        => ['nps', 'surveys', 'analytics', 'reporting', 'csat'],
        ],
        [
            'slug'        => 'omnichannel-routing',
            'name'        => 'Omnichannel Routing',
            'description' => 'Unified cross-channel routing with priority and capacity rules. Route conversations from any channel to the best available agent based on skills, capacity, priority, and custom rules. Supports round-robin, least-busy, and skill-based routing.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'automation',
            'price'       => 19.00,
            'is_free'     => false,
            'icon'        => 'arrows-right-left',
            'color'       => '#0ea5e9',
            'rating'      => 4.6,
            'downloads'   => 2870,
            'screenshots' => ['omnichannel-routing-1.png', 'omnichannel-routing-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with multi-strategy routing and capacity management.',
            'tags'        => ['routing', 'omnichannel', 'automation', 'skills'],
        ],
        [
            'slug'        => 'phone',
            'name'        => 'Phone',
            'description' => 'Voice/IVR integration with call logging and recording. Add voice support to your helpdesk with IVR menus, call recording, automatic ticket creation from calls, and integration with popular telephony providers.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'channels',
            'price'       => 39.00,
            'is_free'     => false,
            'icon'        => 'phone',
            'color'       => '#06b6d4',
            'rating'      => 4.4,
            'downloads'   => 1650,
            'screenshots' => ['phone-1.png', 'phone-2.png', 'phone-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with IVR, call logging, recording, and auto-ticketing.',
            'tags'        => ['phone', 'voice', 'ivr', 'call', 'recording'],
        ],
        [
            'slug'        => 'proactive-messages',
            'name'        => 'Proactive Messages',
            'description' => 'Proactive customer outreach based on triggers and conditions. Send targeted messages to customers based on behavior, events, or schedules. Build campaigns with templates, audience targeting, and delivery tracking.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'automation',
            'price'       => 14.00,
            'is_free'     => false,
            'icon'        => 'megaphone',
            'color'       => '#ec4899',
            'rating'      => 4.3,
            'downloads'   => 1420,
            'screenshots' => ['proactive-messages-1.png', 'proactive-messages-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with campaign builder, audience targeting, and tracking.',
            'tags'        => ['proactive', 'messages', 'campaigns', 'outreach'],
        ],
        [
            'slug'        => 'scheduled-reports',
            'name'        => 'Scheduled Reports',
            'description' => 'Scheduled email delivery of reports on daily, weekly, or monthly cadence. Automate report generation for ticket volume, agent performance, SLA compliance, resolution time, and CSAT scores with flexible delivery options.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'reporting',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'clock',
            'color'       => '#8b5cf6',
            'rating'      => 4.4,
            'downloads'   => 2560,
            'screenshots' => ['scheduled-reports-1.png', 'scheduled-reports-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with five report types and multi-channel delivery.',
            'tags'        => ['reports', 'scheduled', 'email', 'analytics'],
        ],
        [
            'slug'        => 'slack',
            'name'        => 'Slack Integration',
            'description' => 'Slack integration for notifications and ticket creation from Slack messages. Forward ticket events to Slack channels, DM agents on assignment, thread replies back to Slack, and create tickets directly from Slack messages.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'integrations',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'hashtag',
            'color'       => '#4a154b',
            'rating'      => 4.7,
            'downloads'   => 5400,
            'screenshots' => ['slack-1.png', 'slack-2.png', 'slack-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with event forwarding, DM notifications, and threading.',
            'tags'        => ['slack', 'integration', 'notifications', 'messaging'],
        ],
        [
            'slug'        => 'sms',
            'name'        => 'SMS',
            'description' => 'SMS channel via Twilio/Vonage for ticket notifications and replies. Send and receive SMS messages as a ticket channel, with auto-reply templates, delivery receipts, and opt-in/opt-out management.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'channels',
            'price'       => 14.00,
            'is_free'     => false,
            'icon'        => 'chat-bubble-left',
            'color'       => '#2563eb',
            'rating'      => 4.3,
            'downloads'   => 1980,
            'screenshots' => ['sms-1.png', 'sms-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with Twilio/Vonage support and auto-reply templates.',
            'tags'        => ['sms', 'twilio', 'vonage', 'messaging', 'channel'],
        ],
        [
            'slug'        => 'social',
            'name'        => 'Social Channels',
            'description' => 'Facebook, X (Twitter), and Instagram DM channel integration. Manage social media conversations as tickets, reply to customers on their preferred social platform, and monitor mentions and DMs from a unified inbox.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'channels',
            'price'       => 24.00,
            'is_free'     => false,
            'icon'        => 'globe-alt',
            'color'       => '#1d9bf0',
            'rating'      => 4.5,
            'downloads'   => 2210,
            'screenshots' => ['social-1.png', 'social-2.png', 'social-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with Facebook, X, and Instagram DM support.',
            'tags'        => ['social', 'facebook', 'twitter', 'instagram', 'channels'],
        ],
        [
            'slug'        => 'ticket-sharing',
            'name'        => 'Ticket Sharing',
            'description' => 'Cross-instance ticket sharing between Escalated installations. Share tickets across multiple Escalated instances for partner collaboration, vendor escalation, or multi-brand support with full sync of updates and replies.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'integrations',
            'price'       => 19.00,
            'is_free'     => false,
            'icon'        => 'share',
            'color'       => '#f59e0b',
            'rating'      => 4.2,
            'downloads'   => 890,
            'screenshots' => ['ticket-sharing-1.png', 'ticket-sharing-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with cross-instance sharing and bi-directional sync.',
            'tags'        => ['sharing', 'collaboration', 'multi-instance', 'sync'],
        ],
        [
            'slug'        => 'unified-status',
            'name'        => 'Unified Status',
            'description' => 'Cross-channel agent availability status management. Set your status once and have it sync across all channels (chat, phone, email, social). Includes schedule-based auto-status, capacity tracking, and team status dashboards.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'productivity',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'signal',
            'color'       => '#22c55e',
            'rating'      => 4.4,
            'downloads'   => 3350,
            'screenshots' => ['unified-status-1.png', 'unified-status-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with cross-channel sync and schedule-based status.',
            'tags'        => ['status', 'availability', 'agents', 'capacity'],
        ],
        [
            'slug'        => 'web-widget',
            'name'        => 'Web Widget',
            'description' => 'Embeddable JavaScript widget for websites with contact form and KB search. A lightweight, customizable support widget that provides instant help through knowledge base search, contact forms, and live chat integration.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'channels',
            'price'       => 0,
            'is_free'     => true,
            'icon'        => 'window',
            'color'       => '#6366f1',
            'rating'      => 4.6,
            'downloads'   => 4800,
            'screenshots' => ['web-widget-1.png', 'web-widget-2.png', 'web-widget-3.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with contact form, KB search, and theme customization.',
            'tags'        => ['widget', 'website', 'embed', 'contact-form', 'kb'],
        ],
        [
            'slug'        => 'whatsapp',
            'name'        => 'WhatsApp',
            'description' => 'WhatsApp Business API messaging channel for customer support. Connect your WhatsApp Business account to receive and reply to customer messages as tickets, with template message support and media attachments.',
            'version'     => '0.1.0',
            'author'      => 'Escalated',
            'category'    => 'channels',
            'price'       => 24.00,
            'is_free'     => false,
            'icon'        => 'chat-bubble-oval-left',
            'color'       => '#25d366',
            'rating'      => 4.6,
            'downloads'   => 3780,
            'screenshots' => ['whatsapp-1.png', 'whatsapp-2.png'],
            'requires'    => '0.6.0',
            'changelog'   => 'Initial release with Business API integration and template messages.',
            'tags'        => ['whatsapp', 'messaging', 'channel', 'business-api'],
        ],
    ];
}

// ---------------------------------------------------------------------------
// Configuration helpers
// ---------------------------------------------------------------------------

/**
 * Return the default settings structure.
 */
function esc_marketplace_default_settings(): array
{
    return [
        'registry_url'     => 'https://registry.escalated.dev/api/v1',
        'auto_check'       => true,
        'check_interval'   => 86400,  // 24 hours in seconds
        'last_check'       => null,
        'license_key'      => '',
    ];
}

/**
 * Read the current settings from the JSON config file.
 */
function esc_marketplace_get_settings(): array
{
    if (!file_exists(ESC_MARKETPLACE_CONFIG_FILE)) {
        return esc_marketplace_default_settings();
    }

    $json = file_get_contents(ESC_MARKETPLACE_CONFIG_FILE);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return esc_marketplace_default_settings();
    }

    return array_merge(esc_marketplace_default_settings(), $data);
}

/**
 * Persist settings to the JSON config file.
 */
function esc_marketplace_save_settings(array $settings): bool
{
    if (!is_dir(ESC_MARKETPLACE_CONFIG_DIR)) {
        mkdir(ESC_MARKETPLACE_CONFIG_DIR, 0755, true);
    }

    $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return file_put_contents(ESC_MARKETPLACE_CONFIG_FILE, $json) !== false;
}

// ---------------------------------------------------------------------------
// Catalog & query functions
// ---------------------------------------------------------------------------

/**
 * Fetch the plugin catalog, optionally filtered by category and/or search.
 *
 * @param  array $filters  Optional: ['category' => '...', 'search' => '...', 'sort' => '...']
 * @return array           Filtered and sorted catalog entries.
 */
function esc_marketplace_fetch_catalog(array $filters = []): array
{
    $catalog  = esc_marketplace_catalog();
    $category = $filters['category'] ?? '';
    $search   = $filters['search'] ?? '';
    $sort     = $filters['sort'] ?? 'popular';

    // Filter by category
    if ($category !== '' && $category !== 'all') {
        $catalog = array_filter($catalog, function ($plugin) use ($category) {
            return $plugin['category'] === $category;
        });
    }

    // Filter by search query (matches name, description, tags)
    if ($search !== '') {
        $searchLower = strtolower($search);
        $catalog = array_filter($catalog, function ($plugin) use ($searchLower) {
            $haystack = strtolower(
                $plugin['name'] . ' ' .
                $plugin['description'] . ' ' .
                implode(' ', $plugin['tags'] ?? [])
            );
            return strpos($haystack, $searchLower) !== false;
        });
    }

    // Re-index after filtering
    $catalog = array_values($catalog);

    // Sort
    switch ($sort) {
        case 'popular':
            usort($catalog, function ($a, $b) {
                return ($b['downloads'] ?? 0) - ($a['downloads'] ?? 0);
            });
            break;

        case 'newest':
            // In a real registry this would sort by publish date. Here we
            // reverse alphabetical as a placeholder.
            usort($catalog, function ($a, $b) {
                return strcmp($b['slug'], $a['slug']);
            });
            break;

        case 'price_asc':
            usort($catalog, function ($a, $b) {
                return ($a['price'] ?? 0) <=> ($b['price'] ?? 0);
            });
            break;

        case 'price_desc':
            usort($catalog, function ($a, $b) {
                return ($b['price'] ?? 0) <=> ($a['price'] ?? 0);
            });
            break;

        case 'rating':
            usort($catalog, function ($a, $b) {
                return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
            });
            break;
    }

    return $catalog;
}

// ---------------------------------------------------------------------------
// Installed plugins helper
// ---------------------------------------------------------------------------

/**
 * Get a list of currently installed plugins.
 *
 * Uses the platform helper if available, otherwise scans the plugins
 * directory for plugin.json manifests.
 *
 * @return array  Array of installed plugin data keyed by slug.
 */
function esc_marketplace_get_installed_plugins(): array
{
    // Use platform API if available
    if (function_exists('escalated_get_plugins')) {
        $plugins = escalated_get_plugins();
        $indexed = [];
        foreach ($plugins as $p) {
            $slug = $p['slug'] ?? '';
            if ($slug !== '') {
                $indexed[$slug] = $p;
            }
        }
        return $indexed;
    }

    // Fallback: scan sibling plugin directories
    $pluginsDir = dirname(__DIR__);
    $installed  = [];

    if (!is_dir($pluginsDir)) {
        return $installed;
    }

    $dirs = glob($pluginsDir . '/escalated-plugin-*', GLOB_ONLYDIR);

    foreach ($dirs as $dir) {
        $manifestFile = $dir . '/plugin.json';
        if (!file_exists($manifestFile)) {
            continue;
        }

        $json = file_get_contents($manifestFile);
        $data = json_decode($json, true);

        if (!is_array($data) || empty($data['slug'])) {
            continue;
        }

        $installed[$data['slug']] = [
            'slug'        => $data['slug'],
            'name'        => $data['name'] ?? $data['slug'],
            'version'     => $data['version'] ?? '0.0.0',
            'description' => $data['description'] ?? '',
            'author'      => $data['author'] ?? '',
            'requires'    => $data['requires'] ?? '',
            'path'        => $dir,
            'active'      => true, // If the directory exists we assume active
        ];
    }

    return $installed;
}

// ---------------------------------------------------------------------------
// Update checking
// ---------------------------------------------------------------------------

/**
 * Compare installed plugin versions against the catalog to find updates.
 *
 * @return array  Array of plugins that have a newer version available.
 *                Each entry: { slug, name, installed_version, available_version }
 */
function esc_marketplace_check_for_updates(): array
{
    $installed = esc_marketplace_get_installed_plugins();
    $catalog   = esc_marketplace_catalog();
    $updates   = [];

    foreach ($catalog as $available) {
        $slug = $available['slug'] ?? '';

        if (!isset($installed[$slug])) {
            continue;
        }

        $installedVersion  = $installed[$slug]['version'] ?? '0.0.0';
        $availableVersion  = $available['version'] ?? '0.0.0';

        if (version_compare($availableVersion, $installedVersion, '>')) {
            $updates[] = [
                'slug'              => $slug,
                'name'              => $available['name'],
                'installed_version' => $installedVersion,
                'available_version' => $availableVersion,
                'changelog'         => $available['changelog'] ?? '',
                'is_free'           => $available['is_free'],
                'price'             => $available['price'],
            ];
        }
    }

    // Update the last check timestamp
    $settings = esc_marketplace_get_settings();
    $settings['last_check'] = gmdate('Y-m-d\TH:i:s\Z');
    esc_marketplace_save_settings($settings);

    return $updates;
}

// ---------------------------------------------------------------------------
// Install from marketplace (stub)
// ---------------------------------------------------------------------------

/**
 * Install a plugin from the marketplace.
 *
 * In production this would download a ZIP from the registry, verify its
 * signature, extract it to the plugins directory, and activate it.
 * This stub validates the slug exists in the catalog and returns a
 * success response.
 *
 * @param  string $slug       Plugin slug to install.
 * @param  string $zipUrl     URL to the plugin ZIP archive (unused in stub).
 * @return array              Result: { success, slug, message }
 */
function esc_marketplace_install_from_marketplace(string $slug, string $zipUrl = ''): array
{
    // Verify the plugin exists in the catalog
    $catalog = esc_marketplace_catalog();
    $found   = null;

    foreach ($catalog as $plugin) {
        if (($plugin['slug'] ?? '') === $slug) {
            $found = $plugin;
            break;
        }
    }

    if ($found === null) {
        return [
            'success' => false,
            'slug'    => $slug,
            'message' => "Plugin '{$slug}' not found in the catalog.",
        ];
    }

    // Check if already installed
    $installed = esc_marketplace_get_installed_plugins();
    if (isset($installed[$slug])) {
        return [
            'success' => false,
            'slug'    => $slug,
            'message' => "Plugin '{$slug}' is already installed.",
        ];
    }

    // TODO: Production implementation
    // 1. Download ZIP from $zipUrl (or registry URL + slug)
    //    $registryUrl = esc_marketplace_get_settings()['registry_url'];
    //    $downloadUrl = $zipUrl ?: "{$registryUrl}/plugins/{$slug}/download";
    //
    // 2. Verify the ZIP signature / checksum
    //
    // 3. Extract to plugins directory
    //    $targetDir = dirname(__DIR__) . "/escalated-plugin-{$slug}";
    //    $zip = new ZipArchive();
    //    $zip->open($tmpFile);
    //    $zip->extractTo($targetDir);
    //    $zip->close();
    //
    // 4. Activate the plugin
    //    if (function_exists('escalated_activate_plugin')) {
    //        escalated_activate_plugin($slug);
    //    }

    // Stub: simulate successful installation
    if (function_exists('escalated_log')) {
        escalated_log('marketplace', "Stub install for plugin '{$slug}' (v{$found['version']})");
    }

    // Broadcast the installation event
    if (function_exists('escalated_broadcast')) {
        escalated_broadcast('admin', 'marketplace.plugin_installed', [
            'slug'    => $slug,
            'name'    => $found['name'],
            'version' => $found['version'],
        ]);
    }

    return [
        'success' => true,
        'slug'    => $slug,
        'name'    => $found['name'],
        'version' => $found['version'],
        'message' => "Plugin '{$found['name']}' (v{$found['version']}) installed successfully.",
        'stub'    => true,
    ];
}

// ---------------------------------------------------------------------------
// API endpoint: fetch catalog
// ---------------------------------------------------------------------------

escalated_add_action('api.marketplace.catalog', function ($request = []) {
    $filters = [
        'category' => $request['category'] ?? '',
        'search'   => $request['search'] ?? '',
        'sort'     => $request['sort'] ?? 'popular',
    ];

    $catalog   = esc_marketplace_fetch_catalog($filters);
    $installed = esc_marketplace_get_installed_plugins();

    // Annotate each catalog entry with installed status
    foreach ($catalog as &$plugin) {
        $slug = $plugin['slug'] ?? '';
        $plugin['is_installed'] = isset($installed[$slug]);
        $plugin['installed_version'] = $installed[$slug]['version'] ?? null;

        // Check if update is available
        if ($plugin['is_installed'] && $plugin['installed_version']) {
            $plugin['has_update'] = version_compare(
                $plugin['version'],
                $plugin['installed_version'],
                '>'
            );
        } else {
            $plugin['has_update'] = false;
        }
    }
    unset($plugin);

    return [
        'plugins'    => $catalog,
        'total'      => count($catalog),
        'categories' => ['integrations', 'automation', 'reporting', 'channels', 'security', 'productivity'],
    ];
}, 10);

// ---------------------------------------------------------------------------
// API endpoint: install plugin
// ---------------------------------------------------------------------------

escalated_add_action('api.marketplace.install', function ($request = []) {
    $slug   = $request['slug'] ?? '';
    $zipUrl = $request['zip_url'] ?? '';

    if ($slug === '') {
        return ['success' => false, 'message' => 'Plugin slug is required.'];
    }

    return esc_marketplace_install_from_marketplace($slug, $zipUrl);
}, 10);

// ---------------------------------------------------------------------------
// API endpoint: check for updates
// ---------------------------------------------------------------------------

escalated_add_action('api.marketplace.check_updates', function ($request = []) {
    $updates  = esc_marketplace_check_for_updates();
    $settings = esc_marketplace_get_settings();

    return [
        'updates'    => $updates,
        'count'      => count($updates),
        'checked_at' => $settings['last_check'],
    ];
}, 10);

// ---------------------------------------------------------------------------
// API endpoint: get plugin detail
// ---------------------------------------------------------------------------

escalated_add_action('api.marketplace.detail', function ($request = []) {
    $slug = $request['slug'] ?? '';

    if ($slug === '') {
        return ['success' => false, 'message' => 'Plugin slug is required.'];
    }

    $catalog = esc_marketplace_catalog();
    $found   = null;

    foreach ($catalog as $plugin) {
        if (($plugin['slug'] ?? '') === $slug) {
            $found = $plugin;
            break;
        }
    }

    if ($found === null) {
        return ['success' => false, 'message' => "Plugin '{$slug}' not found."];
    }

    // Annotate with install status
    $installed = esc_marketplace_get_installed_plugins();
    $found['is_installed']      = isset($installed[$slug]);
    $found['installed_version'] = $installed[$slug]['version'] ?? null;
    $found['has_update']        = false;

    if ($found['is_installed'] && $found['installed_version']) {
        $found['has_update'] = version_compare(
            $found['version'],
            $found['installed_version'],
            '>'
        );
    }

    return ['success' => true, 'plugin' => $found];
}, 10);

// ---------------------------------------------------------------------------
// Page registration: admin/marketplace
// ---------------------------------------------------------------------------

escalated_register_page('admin/marketplace', [
    'title'      => 'Plugin Marketplace',
    'component'  => 'MarketplaceBrowser',
    'capability' => 'manage_plugins',
    'props'      => [
        'pluginSlug' => ESC_MARKETPLACE_SLUG,
        'categories' => ['integrations', 'automation', 'reporting', 'channels', 'security', 'productivity'],
    ],
]);

// ---------------------------------------------------------------------------
// Admin menu item
// ---------------------------------------------------------------------------

escalated_register_menu_item([
    'id'         => 'marketplace',
    'label'      => 'Marketplace',
    'icon'       => 'squares-plus',
    'route'      => '/admin/marketplace',
    'parent'     => 'admin',
    'order'      => 90,
    'capability' => 'manage_plugins',
]);

// ---------------------------------------------------------------------------
// Page component: "Browse Marketplace" button on admin.plugins header
// ---------------------------------------------------------------------------

escalated_add_page_component('admin.plugins', 'header', [
    'component' => 'MarketplaceBrowseButton',
    'props'     => [
        'label' => 'Browse Marketplace',
        'route' => '/admin/marketplace',
        'icon'  => 'squares-plus',
    ],
    'order' => 10,
]);

// ---------------------------------------------------------------------------
// Filter: admin.plugins.actions -- add marketplace link to plugin actions
// ---------------------------------------------------------------------------

escalated_add_filter('admin.plugins.actions', function (array $actions) {
    $actions[] = [
        'id'    => 'browse-marketplace',
        'label' => 'Browse Marketplace',
        'icon'  => 'squares-plus',
        'route' => '/admin/marketplace',
    ];

    return $actions;
}, 10);

// ---------------------------------------------------------------------------
// Activation hook
// ---------------------------------------------------------------------------

escalated_add_action('escalated_plugin_activated_marketplace', function () {
    // Ensure config directory exists
    if (!is_dir(ESC_MARKETPLACE_CONFIG_DIR)) {
        mkdir(ESC_MARKETPLACE_CONFIG_DIR, 0755, true);
    }

    // Create default settings if they do not exist
    if (!file_exists(ESC_MARKETPLACE_CONFIG_FILE)) {
        esc_marketplace_save_settings(esc_marketplace_default_settings());
    }

    // Store plugin version
    if (function_exists('escalated_update_option')) {
        escalated_update_option('marketplace_plugin_version', ESC_MARKETPLACE_VERSION);
    }

    // Perform initial update check
    esc_marketplace_check_for_updates();
}, 10);

// ---------------------------------------------------------------------------
// Deactivation hook
// ---------------------------------------------------------------------------

escalated_add_action('escalated_plugin_deactivated_marketplace', function () {
    // Preserve settings so re-activation restores the configuration.
    // Full cleanup only happens on uninstall.

    if (function_exists('escalated_broadcast')) {
        escalated_broadcast('admin', 'marketplace.deactivated', [
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        ]);
    }
}, 10);
