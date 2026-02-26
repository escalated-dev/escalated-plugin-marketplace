<?php
/**
 * Marketplace Plugin for Escalated -- thin entry point.
 * All logic lives in Support/Config and Services/MarketplaceClient.
 */
if (!defined('ESCALATED_LOADED')) {
    exit('Direct access not allowed.');
}

require_once __DIR__ . '/Support/Config.php';
require_once __DIR__ . '/Services/MarketplaceClient.php';

use Escalated\Plugins\Marketplace\Support\Config;
use Escalated\Plugins\Marketplace\Services\MarketplaceClient;

$categories = ['integrations','automation','reporting','channels','security','productivity'];

// -- API endpoints -----------------------------------------------------------
escalated_add_action('api.marketplace.catalog', function ($request = []) use ($categories) {
    $catalog   = Config::fetchCatalog(['category' => $request['category'] ?? '',
        'search' => $request['search'] ?? '', 'sort' => $request['sort'] ?? 'popular']);
    $installed = Config::installedPlugins();
    $catalog   = array_map(fn($p) => Config::annotateWithInstallStatus($p, $installed), $catalog);
    return ['plugins' => $catalog, 'total' => count($catalog), 'categories' => $categories];
}, 10);

escalated_add_action('api.marketplace.install', function ($request = []) {
    $slug = $request['slug'] ?? '';
    if ($slug === '') {
        return ['success' => false, 'message' => 'Plugin slug is required.'];
    }
    $settings    = Config::all();
    $downloadUrl = ($request['zip_url'] ?? '') ?: $settings['registry_url'] . '/plugins/' . $slug . '/download';
    return (new MarketplaceClient())->installPlugin($downloadUrl, $slug);
}, 10);

escalated_add_action('api.marketplace.check_updates', function () {
    $updates  = Config::checkForUpdates();
    return ['updates' => $updates, 'count' => count($updates), 'checked_at' => Config::get('last_check')];
}, 10);

escalated_add_action('api.marketplace.detail', function ($request = []) {
    $slug = $request['slug'] ?? '';
    if ($slug === '') {
        return ['success' => false, 'message' => 'Plugin slug is required.'];
    }
    $found = Config::findBySlug($slug);
    if ($found === null) {
        return ['success' => false, 'message' => "Plugin '{$slug}' not found."];
    }
    return ['success' => true, 'plugin' => Config::annotateWithInstallStatus($found, Config::installedPlugins())];
}, 10);

// -- UI registration ---------------------------------------------------------
escalated_register_page('admin/marketplace', [
    'title' => 'Plugin Marketplace', 'component' => 'MarketplaceBrowser',
    'capability' => 'manage_plugins',
    'props' => ['pluginSlug' => Config::SLUG, 'categories' => $categories],
]);
escalated_register_menu_item([
    'id' => 'marketplace', 'label' => 'Marketplace', 'icon' => 'squares-plus',
    'route' => '/admin/marketplace', 'parent' => 'admin', 'order' => 90, 'capability' => 'manage_plugins',
]);
escalated_add_page_component('admin.plugins', 'header', [
    'component' => 'MarketplaceBrowseButton',
    'props' => ['label' => 'Browse Marketplace', 'route' => '/admin/marketplace', 'icon' => 'squares-plus'],
    'order' => 10,
]);
escalated_add_filter('admin.plugins.actions', function (array $actions) {
    $actions[] = ['id' => 'browse-marketplace', 'label' => 'Browse Marketplace',
                  'icon' => 'squares-plus', 'route' => '/admin/marketplace'];
    return $actions;
}, 10);

// -- Lifecycle ---------------------------------------------------------------
escalated_add_action('escalated_plugin_activated_marketplace', [Config::class, 'onActivate'], 10);
escalated_add_action('escalated_plugin_deactivated_marketplace', [Config::class, 'onDeactivate'], 10);
