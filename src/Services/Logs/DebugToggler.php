<?php

namespace FP\PerfSuite\Services\Logs;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Logger as StaticLogger;

use function get_option;
use function update_option;
use function wp_unslash;

class DebugToggler
{
    private const OPTION_LOG_EXPRESSION = 'fp_ps_debug_log_value';
    private Fs $fs;
    private Env $env;
    private ?OptionsRepositoryInterface $optionsRepo = null;
    private ?LoggerInterface $logger = null;

    /**
     * Costruttore
     * 
     * @param Fs $fs
     * @param Env $env
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     * @param LoggerInterface|null $logger Logger opzionale per logging
     */
    public function __construct(Fs $fs, Env $env, ?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->fs = $fs;
        $this->env = $env;
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     * @param \Throwable|null $exception Optional exception
     */
    private function log(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($this->logger !== null) {
            if ($exception !== null && method_exists($this->logger, $level)) {
                $this->logger->$level($message, $context, $exception);
            } else {
                $this->logger->$level($message, $context);
            }
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }

    public function status(): array
    {
        $logEnabled = defined('WP_DEBUG_LOG') ? (bool) WP_DEBUG_LOG : false;
        $logPath = '';
        if (defined('WP_DEBUG_LOG')) {
            if (is_string(WP_DEBUG_LOG) && WP_DEBUG_LOG !== '') {
                $logPath = WP_DEBUG_LOG;
            } elseif (WP_DEBUG_LOG) {
                $logPath = WP_CONTENT_DIR . '/debug.log';
            }
        }
        return [
            'WP_DEBUG' => defined('WP_DEBUG') ? (bool) WP_DEBUG : false,
            'WP_DEBUG_LOG' => $logEnabled,
            'WP_DEBUG_DISPLAY' => defined('WP_DEBUG_DISPLAY') ? (bool) WP_DEBUG_DISPLAY : false,
            'SCRIPT_DEBUG' => defined('SCRIPT_DEBUG') ? (bool) SCRIPT_DEBUG : false,
            'SAVEQUERIES' => defined('SAVEQUERIES') ? (bool) SAVEQUERIES : false,
            'log_file' => $logPath,
        ];
    }

    public function toggle(bool $enabled, bool $log = true): bool
    {
        return $this->updateSettings([
            'WP_DEBUG' => $enabled,
            'WP_DEBUG_LOG' => $log,
        ]);
    }

    /**
     * Update multiple debug settings at once
     *
     * @param array<string, bool|string> $settings Array of constant => value pairs
     * @return bool Success status
     */
    public function updateSettings(array $settings): bool
    {
        $config = ABSPATH . 'wp-config.php';
        $lockFile = WP_CONTENT_DIR . '/fp-ps-config.lock';
        $lock = null;

        try {
            // Acquire lock to prevent concurrent modifications
            $lock = fopen($lockFile, 'c+');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
                $this->log('error', 'Failed to acquire lock for wp-config.php modification');
                return false;
            }

            if (!$this->fs->exists($config)) {
                return false;
            }

            $original = $this->fs->getContents($config);
            $this->backup($config, $original);

            $updated = $original;
            $existingLogValue = $this->extractConstant($original, 'WP_DEBUG_LOG');
            $parsedLogValue = $this->parseConstant($existingLogValue);
            $existingRaw = $existingLogValue !== null ? trim($existingLogValue) : null;

            // Save custom log path if disabling
            if (isset($settings['WP_DEBUG_LOG']) && !$settings['WP_DEBUG_LOG'] && $existingRaw !== null && $existingRaw !== '' && strtolower($existingRaw) !== 'false') {
                $this->setOption(self::OPTION_LOG_EXPRESSION, $existingRaw);
            }

            // Build constant map
            $map = [];

            if (isset($settings['WP_DEBUG'])) {
                $map['WP_DEBUG'] = $settings['WP_DEBUG'] ? 'true' : 'false';
            }

            if (isset($settings['WP_DEBUG_LOG'])) {
                $map['WP_DEBUG_LOG'] = $settings['WP_DEBUG_LOG'] ? $this->determineLogValue($parsedLogValue) : 'false';
            }

            if (isset($settings['WP_DEBUG_DISPLAY'])) {
                $map['WP_DEBUG_DISPLAY'] = $settings['WP_DEBUG_DISPLAY'] ? 'true' : 'false';
            }

            if (isset($settings['SCRIPT_DEBUG'])) {
                $map['SCRIPT_DEBUG'] = $settings['SCRIPT_DEBUG'] ? 'true' : 'false';
            }

            if (isset($settings['SAVEQUERIES'])) {
                $map['SAVEQUERIES'] = $settings['SAVEQUERIES'] ? 'true' : 'false';
            }

            // Apply changes
            foreach ($map as $constant => $value) {
                $replacement = "define('{$constant}', {$value});";
                $pattern = "/define\s*\(\s*['\"]{$constant}['\"]\s*,\s*([^\)]*)\);/i";
                if (preg_match($pattern, $updated)) {
                    $updated = preg_replace($pattern, $replacement, $updated, 1);
                    continue;
                }
                $updated = preg_replace('/(<\?php)/', "$1\n{$replacement}", $updated, 1);
            }

            $result = $this->fs->putContents($config, $updated);
            if ($result && function_exists('wp_opcache_invalidate')) {
                wp_opcache_invalidate($config, true);
            }

            $this->log('info', 'Debug settings updated', $map);

            return $result;
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to update debug settings', [], $e);
            return false;
        } finally {
            // Always release lock
            if ($lock) {
                flock($lock, LOCK_UN);
                fclose($lock);
                @unlink($lockFile);
            }
        }
    }

    public function backup(string $config, string $contents): void
    {
        try {
            $backupFile = $config . '.fp-backup-' . gmdate('YmdHis');
            $this->fs->putContents($backupFile, $contents);
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to back up wp-config.php', [], $e);
        }
    }

    public function revertLatest(): bool
    {
        $config = ABSPATH . 'wp-config.php';
        $pattern = $config . '.fp-backup-*';
        try {
            $backups = glob($pattern);
            if (empty($backups)) {
                return false;
            }
            rsort($backups);
            $latest = $backups[0];
            $contents = $this->fs->getContents($latest);
            $result = $this->fs->putContents($config, $contents);
            if ($result && function_exists('wp_opcache_invalidate')) {
                wp_opcache_invalidate($config, true);
            }
            return $result;
        } catch (\Throwable $e) {
            $this->log('error', 'Failed to restore wp-config.php backup', [], $e);
            return false;
        }
    }

    private function determineLogValue(?array $parsed): string
    {
        if ($parsed) {
            if ($parsed['type'] === 'string' && $parsed['value'] !== '') {
                return $this->exportValue($parsed['value']);
            }
            if ($parsed['type'] === 'raw' && $parsed['value'] !== '') {
                return $parsed['value'];
            }
            if ($parsed['type'] === 'bool') {
                if ($parsed['value']) {
                    return 'true';
                }
                $stored = $this->getOption(self::OPTION_LOG_EXPRESSION, '');
                if (is_string($stored) && trim($stored) !== '') {
                    return trim($stored);
                }
                return 'true';
            }
        }
        if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG) && WP_DEBUG_LOG !== '') {
            return $this->exportValue(WP_DEBUG_LOG);
        }
        $stored = $this->getOption(self::OPTION_LOG_EXPRESSION, '');
        if (is_string($stored) && trim($stored) !== '') {
            return trim($stored);
        }
        return 'true';
    }

    private function exportValue(string $value): string
    {
        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
    }

    private function extractConstant(string $contents, string $constant): ?string
    {
        $pattern = "/define\s*\(\s*['\"]{$constant}['\"]\s*,\s*(.+?)\s*\);/is";
        if (preg_match($pattern, $contents, $matches)) {
            return $matches[1] ?? null;
        }
        return null;
    }

    private function parseConstant(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        $lower = strtolower($trimmed);
        if ($lower === 'true' || $lower === 'false') {
            return ['type' => 'bool', 'value' => $lower === 'true'];
        }
        if (preg_match("/^['\"](.*)['\"]$/s", $trimmed, $matches)) {
            return ['type' => 'string', 'value' => wp_unslash($matches[1] ?? '')];
        }
        return ['type' => 'raw', 'value' => $trimmed];
    }
}
