<?php

namespace Escalated\Plugins\Marketplace\Services;

use Escalated\Plugins\Marketplace\Support\Config;
use Illuminate\Support\Facades\Http;

/**
 * MarketplaceClient handles all communication with the remote plugin registry
 * and local plugin installation via ZIP extraction.
 *
 * Methods:
 *  - fetchCatalog()   : Paginated catalog from the registry API
 *  - fetchPlugin()    : Single plugin detail from the registry API
 *  - installPlugin()  : Download ZIP and extract to the plugins directory
 */
class MarketplaceClient
{
    /**
     * Base URL for the plugin registry API.
     */
    protected string $registryUrl;

    /**
     * Absolute path to the plugins directory where plugin folders live.
     */
    protected string $pluginsDir;

    public function __construct(?string $registryUrl = null, ?string $pluginsDir = null)
    {
        $this->registryUrl = rtrim($registryUrl ?? Config::get('registry_url', 'https://registry.escalated.dev/api/v1'), '/');
        $this->pluginsDir  = $pluginsDir ?? dirname(__DIR__, 2);
    }

    // -------------------------------------------------------------------------
    // Registry API
    // -------------------------------------------------------------------------

    /**
     * Fetch the paginated plugin catalog from the remote registry.
     *
     * @param  int $page    Page number (1-based).
     * @param  int $perPage Number of results per page.
     * @return array        Decoded JSON response body, or an error array.
     */
    public function fetchCatalog(int $page = 1, int $perPage = 20): array
    {
        $response = Http::timeout(15)->get($this->registryUrl . '/api/plugins', [
            'page'     => $page,
            'per_page' => $perPage,
        ]);

        if ($response->failed()) {
            $this->log("Registry catalog request failed with status {$response->status()}");

            return [
                'success' => false,
                'message' => 'Failed to fetch catalog from registry (HTTP ' . $response->status() . ').',
                'plugins' => [],
            ];
        }

        $body = $response->json();

        return [
            'success' => true,
            'plugins' => $body['plugins'] ?? $body['data'] ?? [],
            'total'   => $body['total'] ?? count($body['plugins'] ?? $body['data'] ?? []),
            'page'    => $body['page'] ?? $page,
            'per_page'=> $body['per_page'] ?? $perPage,
        ];
    }

    /**
     * Fetch a single plugin's detail from the remote registry.
     *
     * @param  string $slug  Plugin slug identifier.
     * @return array         Decoded JSON response body, or an error array.
     */
    public function fetchPlugin(string $slug): array
    {
        $response = Http::timeout(15)->get($this->registryUrl . '/api/plugins/' . urlencode($slug));

        if ($response->failed()) {
            $this->log("Registry detail request for '{$slug}' failed with status {$response->status()}");

            return [
                'success' => false,
                'message' => "Failed to fetch plugin '{$slug}' from registry (HTTP {$response->status()}).",
                'plugin'  => null,
            ];
        }

        $body = $response->json();

        return [
            'success' => true,
            'plugin'  => $body['plugin'] ?? $body,
        ];
    }

    // -------------------------------------------------------------------------
    // Installation
    // -------------------------------------------------------------------------

    /**
     * Install a plugin by downloading its ZIP archive and extracting it.
     *
     * Steps:
     *  1. Download the ZIP via HTTP (60 s timeout for large archives).
     *  2. Write the response body to a temporary file.
     *  3. Open and extract using PHP's built-in ZipArchive.
     *  4. Clean up the temporary file.
     *  5. Optionally activate the plugin via the platform helper.
     *
     * @param  string $downloadUrl  Full URL to the plugin ZIP archive.
     * @param  string $slug         Plugin slug (used for logging and activation).
     * @return array                Result array with success flag and message.
     */
    public function installPlugin(string $downloadUrl, string $slug): array
    {
        // Verify the plugin is not already installed
        $installed = Config::installedPlugins();
        if (isset($installed[$slug])) {
            return [
                'success' => false,
                'slug'    => $slug,
                'message' => "Plugin '{$slug}' is already installed.",
            ];
        }

        // Step 1: Download the ZIP archive
        $response = Http::timeout(60)->get($downloadUrl);

        if ($response->failed()) {
            $this->log("ZIP download for '{$slug}' failed with status {$response->status()}");

            return [
                'success' => false,
                'slug'    => $slug,
                'message' => "Failed to download plugin ZIP (HTTP {$response->status()}).",
            ];
        }

        $zipBody = $response->body();

        if (empty($zipBody)) {
            return [
                'success' => false,
                'slug'    => $slug,
                'message' => 'Downloaded ZIP archive is empty.',
            ];
        }

        // Step 2: Write to a temporary file
        $tmpFile = sys_get_temp_dir() . '/escalated-plugin-' . $slug . '-' . uniqid() . '.zip';

        if (file_put_contents($tmpFile, $zipBody) === false) {
            return [
                'success' => false,
                'slug'    => $slug,
                'message' => 'Failed to write temporary ZIP file.',
            ];
        }

        // Step 3: Extract using ZipArchive
        $zip    = new \ZipArchive();
        $result = $zip->open($tmpFile);

        if ($result !== true) {
            @unlink($tmpFile);
            $this->log("ZipArchive::open() failed for '{$slug}' with error code {$result}");

            return [
                'success' => false,
                'slug'    => $slug,
                'message' => 'Failed to open downloaded ZIP archive (error code ' . $result . ').',
            ];
        }

        $extractDir = $this->pluginsDir;

        if (!is_dir($extractDir)) {
            @mkdir($extractDir, 0755, true);
        }

        $extracted = $zip->extractTo($extractDir);
        $zip->close();

        // Step 4: Clean up temporary file
        @unlink($tmpFile);

        if (!$extracted) {
            $this->log("ZipArchive::extractTo() failed for '{$slug}'");

            return [
                'success' => false,
                'slug'    => $slug,
                'message' => 'Failed to extract plugin ZIP archive to the plugins directory.',
            ];
        }

        $this->log("Plugin '{$slug}' installed successfully to {$extractDir}");

        // Step 5: Activate via platform helper if available
        if (function_exists('escalated_activate_plugin')) {
            escalated_activate_plugin($slug);
        }

        // Broadcast the installation event
        if (function_exists('escalated_broadcast')) {
            $catalogEntry = Config::findBySlug($slug);
            escalated_broadcast('admin', 'marketplace.plugin_installed', [
                'slug'    => $slug,
                'name'    => $catalogEntry['name'] ?? $slug,
                'version' => $catalogEntry['version'] ?? 'unknown',
            ]);
        }

        return [
            'success' => true,
            'slug'    => $slug,
            'message' => "Plugin '{$slug}' installed successfully.",
        ];
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Write a log message via the platform logger if available.
     */
    protected function log(string $message): void
    {
        if (function_exists('escalated_log')) {
            escalated_log('marketplace', $message);
        }
    }
}
