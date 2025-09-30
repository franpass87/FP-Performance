<?php

namespace FP\PerfSuite\Http;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Services\Logs\RealtimeLog;
use FP\PerfSuite\Services\Presets\Manager as PresetManager;
use FP\PerfSuite\Services\Score\Scorer;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use function esc_html__;

class Routes
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    public function boot(): void
    {
        add_action('rest_api_init', [$this, 'register']);
    }

    public function register(): void
    {
        register_rest_route('fp-ps/v1', '/logs/tail', [
            'methods' => 'GET',
            'callback' => [$this, 'logsTail'],
            'permission_callback' => [$this, 'permissionCheck'],
            'args' => [
                'lines' => ['default' => 200],
                'level' => ['default' => ''],
                'query' => ['default' => ''],
            ],
        ]);

        register_rest_route('fp-ps/v1', '/debug/toggle', [
            'methods' => 'POST',
            'callback' => [$this, 'debugToggle'],
            'permission_callback' => [$this, 'permissionCheck'],
        ]);

        register_rest_route('fp-ps/v1', '/preset/apply', [
            'methods' => 'POST',
            'callback' => [$this, 'presetApply'],
            'permission_callback' => [$this, 'permissionCheck'],
        ]);

        register_rest_route('fp-ps/v1', '/preset/rollback', [
            'methods' => 'POST',
            'callback' => [$this, 'presetRollback'],
            'permission_callback' => [$this, 'permissionCheck'],
        ]);

        register_rest_route('fp-ps/v1', '/db/cleanup', [
            'methods' => 'POST',
            'callback' => [$this, 'dbCleanup'],
            'permission_callback' => [$this, 'permissionCheck'],
        ]);

        register_rest_route('fp-ps/v1', '/score', [
            'methods' => 'GET',
            'callback' => [$this, 'score'],
            'permission_callback' => [$this, 'permissionCheck'],
        ]);

        register_rest_route('fp-ps/v1', '/progress', [
            'methods' => 'GET',
            'callback' => [$this, 'progress'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function permissionCheck(WP_REST_Request $request): bool
    {
        if (!current_user_can('manage_options')) {
            return false;
        }
        $nonce = $request->get_header('X-WP-Nonce');
        return (bool) wp_verify_nonce($nonce, 'wp_rest');
    }

    public function logsTail(WP_REST_Request $request)
    {
        $lines = (int) $request->get_param('lines');
        $level = (string) $request->get_param('level');
        $query = (string) $request->get_param('query');
        $log = $this->container->get(RealtimeLog::class);
        $data = $log->tail($lines, $level, $query);
        return rest_ensure_response(['data' => $data]);
    }

    public function debugToggle(WP_REST_Request $request)
    {
        $enabled = (bool) $request->get_param('enabled');
        $log = (bool) $request->get_param('log');
        $toggler = $this->container->get(DebugToggler::class);
        $result = $toggler->toggle($enabled, $log);
        if (!$result) {
            return new WP_Error('fp_ps_debug', esc_html__('Unable to toggle debug mode.', 'fp-performance-suite'));
        }
        return rest_ensure_response(['success' => true, 'status' => $toggler->status()]);
    }

    public function presetApply(WP_REST_Request $request)
    {
        $id = sanitize_key($request->get_param('id'));
        $manager = $this->container->get(PresetManager::class);
        $result = $manager->apply($id);
        return rest_ensure_response($result);
    }

    public function presetRollback(): WP_REST_Response
    {
        $manager = $this->container->get(PresetManager::class);
        $result = $manager->rollback();
        return rest_ensure_response(['success' => $result]);
    }

    public function dbCleanup(WP_REST_Request $request)
    {
        $scope = (array) $request->get_param('scope');
        $dry = filter_var($request->get_param('dryRun'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $dry = $dry === null ? true : $dry;
        $batch = $request->get_param('batch');
        $batch = $batch ? (int) $batch : null;
        $cleaner = $this->container->get(Cleaner::class);
        $result = $cleaner->cleanup($scope, $dry, $batch);
        return rest_ensure_response($result);
    }

    public function score(): WP_REST_Response
    {
        $scorer = $this->container->get(Scorer::class);
        return rest_ensure_response($scorer->calculate());
    }

    public function progress(): WP_REST_Response
    {
        $file = FP_PERF_SUITE_DIR . '/../.codex-state.json';
        if (!file_exists($file)) {
            return rest_ensure_response([]);
        }
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            $data = [];
        }
        return rest_ensure_response($data);
    }
}
