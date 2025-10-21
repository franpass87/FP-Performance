<?php

namespace FP\PerfSuite\Utils;

class Htaccess
{
    private Fs $fs;
    private const MAX_BACKUPS = 3;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    public function isSupported(): bool
    {
        if (!function_exists('got_mod_rewrite')) {
            $helper = ABSPATH . 'wp-admin/includes/misc.php';
            if (is_readable($helper)) {
                require_once $helper;
            }
        }

        if (!function_exists('got_mod_rewrite') || !got_mod_rewrite()) {
            return false;
        }

        $file = ABSPATH . '.htaccess';
        if ($this->fs->exists($file)) {
            return is_writable($file);
        }

        return is_writable(ABSPATH);
    }

    public function backup(string $file): ?string
    {
        try {
            if (!$this->fs->exists($file)) {
                return null;
            }
            $this->pruneBackups($file);
            $backup = $file . '.bak-' . gmdate('YmdHis');
            $this->fs->copy($file, $backup, true);
            Logger::info('.htaccess backup created', ['backup' => basename($backup)]);
            return $backup;
        } catch (\Throwable $e) {
            Logger::error('Failed to back up .htaccess', $e);
            return null;
        }
    }

    private function pruneBackups(string $file): void
    {
        $pattern = $file . '.bak-*';
        $backups = glob($pattern);

        if (!is_array($backups)) {
            return;
        }

        sort($backups);

        while (count($backups) >= self::MAX_BACKUPS) {
            $oldest = array_shift($backups);

            if (!is_string($oldest) || '' === $oldest) {
                continue;
            }

            try {
                $this->fs->delete($oldest);
            } catch (\Throwable $e) {
                Logger::error('Failed to prune .htaccess backup', $e);
                break;
            }
        }
    }

    public function injectRules(string $section, string $rules): bool
    {
        $file = ABSPATH . '.htaccess';
        try {
            $existing = $this->fs->exists($file) ? $this->fs->getContents($file) : '';
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $normalizedRules = trim($rules);
            $block = $markerStart . PHP_EOL . $normalizedRules . PHP_EOL . $markerEnd;
            $pattern = sprintf('/%s\s*.*?\s*%s/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));

            $updated = $existing;
            $hasBlock = preg_match($pattern, $existing, $matches) === 1;
            if ($hasBlock && isset($matches[0]) && trim($matches[0]) === trim($block)) {
                return true;
            }

            if ($hasBlock) {
                $updated = (string) preg_replace($pattern, $block, $existing, 1);
            } else {
                $trimmed = rtrim($existing);
                $prefix = $trimmed === '' ? '' : $trimmed . PHP_EOL . PHP_EOL;
                $updated = $prefix . $block . PHP_EOL;
            }

            $this->backup($file);
            $result = $this->fs->putContents($file, $updated);
            if ($result) {
                Logger::info('.htaccess rules injected', ['section' => $section]);
                do_action('fp_ps_htaccess_updated', $section, $rules);
            }
            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to inject .htaccess rules', $e);
            return false;
        }
    }

    public function removeSection(string $section): bool
    {
        $file = ABSPATH . '.htaccess';
        try {
            if (!$this->fs->exists($file)) {
                return false;
            }
            $existing = $this->fs->getContents($file);
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $pattern = sprintf('/%s\s*.*?\s*%s\n?/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
            if (preg_match($pattern, $existing) !== 1) {
                return false;
            }
            $updated = preg_replace($pattern, '', $existing, 1);
            $this->backup($file);
            $result = $this->fs->putContents($file, (string) $updated);
            if ($result) {
                Logger::info('.htaccess section removed', ['section' => $section]);
                do_action('fp_ps_htaccess_section_removed', $section);
            }
            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to remove .htaccess section', $e);
            return false;
        }
    }

    public function hasSection(string $section): bool
    {
        $file = ABSPATH . '.htaccess';
        try {
            if (!$this->fs->exists($file)) {
                return false;
            }
            $contents = $this->fs->getContents($file);
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $pattern = sprintf('/%s\s*.*?\s*%s/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
            return preg_match($pattern, $contents) === 1;
        } catch (\Throwable $e) {
            Logger::error('Failed to read .htaccess', $e);
            return false;
        }
    }

    /**
     * Ottiene la lista di tutti i backup disponibili
     * 
     * @return array Lista di backup con informazioni
     */
    public function getBackups(): array
    {
        $file = ABSPATH . '.htaccess';
        $pattern = $file . '.bak-*';
        $backups = glob($pattern);

        if (!is_array($backups)) {
            return [];
        }

        $result = [];
        foreach ($backups as $backup) {
            if (!is_file($backup)) {
                continue;
            }

            $timestamp = str_replace($file . '.bak-', '', $backup);
            $result[] = [
                'path' => $backup,
                'filename' => basename($backup),
                'timestamp' => $timestamp,
                'date' => \DateTime::createFromFormat('YmdHis', $timestamp),
                'size' => filesize($backup),
                'readable_date' => $this->formatBackupDate($timestamp),
            ];
        }

        // Ordina per timestamp decrescente (più recenti prima)
        usort($result, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        return $result;
    }

    /**
     * Formatta la data del backup in modo leggibile
     * 
     * @param string $timestamp Timestamp nel formato YmdHis
     * @return string Data formattata
     */
    private function formatBackupDate(string $timestamp): string
    {
        $date = \DateTime::createFromFormat('YmdHis', $timestamp);
        if (!$date) {
            return $timestamp;
        }
        return $date->format('d/m/Y H:i:s');
    }

    /**
     * Ripristina un backup specifico
     * 
     * @param string $backupPath Percorso del backup da ripristinare
     * @return bool True se il ripristino ha avuto successo
     */
    public function restore(string $backupPath): bool
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            if (!$this->fs->exists($backupPath)) {
                Logger::error('.htaccess backup not found', ['path' => $backupPath]);
                return false;
            }

            // Crea un backup del file corrente prima di ripristinare
            if ($this->fs->exists($file)) {
                $this->backup($file);
            }

            // Ripristina il backup
            $result = $this->fs->copy($backupPath, $file, true);
            
            if ($result) {
                Logger::info('.htaccess restored from backup', ['backup' => basename($backupPath)]);
                do_action('fp_ps_htaccess_restored', $backupPath);
            }

            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to restore .htaccess backup', $e);
            return false;
        }
    }

    /**
     * Valida la sintassi del file .htaccess
     * 
     * @param string|null $content Contenuto da validare (null = file corrente)
     * @return array Array con 'valid' (bool) e 'errors' (array)
     */
    public function validate(?string $content = null): array
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            if ($content === null) {
                if (!$this->fs->exists($file)) {
                    return ['valid' => true, 'errors' => []];
                }
                $content = $this->fs->getContents($file);
            }

            $errors = [];
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                $line = trim($line);
                
                // Salta linee vuote e commenti
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }

                // Controlla direttive comuni problematiche
                $this->validateLine($line, $lineNum + 1, $errors);
            }

            // Controlla BEGIN/END markers bilanciati
            $this->validateMarkers($content, $errors);

            return [
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to validate .htaccess', $e);
            return [
                'valid' => false,
                'errors' => ['Errore durante la validazione: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Valida una singola linea
     * 
     * @param string $line Linea da validare
     * @param int $lineNum Numero della linea
     * @param array &$errors Array di errori da popolare
     */
    private function validateLine(string $line, int $lineNum, array &$errors): void
    {
        // Controlla parentesi angolari non chiuse
        $openTags = substr_count($line, '<');
        $closeTags = substr_count($line, '>');
        if ($openTags !== $closeTags) {
            $errors[] = "Linea {$lineNum}: Tag non bilanciati (<>)";
        }

        // Controlla RewriteRule senza RewriteEngine On
        if (stripos($line, 'RewriteRule') === 0 || stripos($line, 'RewriteCond') === 0) {
            // Questo è solo un warning, non un errore critico
            // Lo gestiamo nella riparazione automatica
        }

        // Controlla direttive duplicate potenzialmente problematiche
        if (stripos($line, 'ErrorDocument') === 0) {
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 3) {
                $errors[] = "Linea {$lineNum}: Sintassi ErrorDocument non valida";
            }
        }
    }

    /**
     * Valida che i markers BEGIN/END siano bilanciati
     * 
     * @param string $content Contenuto da validare
     * @param array &$errors Array di errori da popolare
     */
    private function validateMarkers(string $content, array &$errors): void
    {
        $beginMarkers = [];
        $endMarkers = [];

        preg_match_all('/# BEGIN (.+)$/m', $content, $beginMatches);
        preg_match_all('/# END (.+)$/m', $content, $endMatches);

        if (!empty($beginMatches[1])) {
            $beginMarkers = $beginMatches[1];
        }

        if (!empty($endMatches[1])) {
            $endMarkers = $endMatches[1];
        }

        // Controlla che ogni BEGIN abbia il suo END
        foreach ($beginMarkers as $marker) {
            if (!in_array($marker, $endMarkers, true)) {
                $errors[] = "Marker non bilanciato: BEGIN {$marker} senza END corrispondente";
            }
        }

        // Controlla che ogni END abbia il suo BEGIN
        foreach ($endMarkers as $marker) {
            if (!in_array($marker, $beginMarkers, true)) {
                $errors[] = "Marker non bilanciato: END {$marker} senza BEGIN corrispondente";
            }
        }
    }

    /**
     * Ripara errori comuni nel file .htaccess
     * 
     * @return array Array con 'success' (bool), 'fixes' (array), 'errors' (array)
     */
    public function repairCommonIssues(): array
    {
        $file = ABSPATH . '.htaccess';
        $fixes = [];
        $errors = [];

        try {
            if (!$this->fs->exists($file)) {
                return [
                    'success' => false,
                    'fixes' => [],
                    'errors' => ['File .htaccess non trovato'],
                ];
            }

            $content = $this->fs->getContents($file);
            $originalContent = $content;
            $lines = explode("\n", $content);
            $hasRewriteRules = false;
            $hasRewriteEngine = false;

            // Analizza il contenuto
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (stripos($trimmed, 'RewriteRule') === 0 || stripos($trimmed, 'RewriteCond') === 0) {
                    $hasRewriteRules = true;
                }
                if (stripos($trimmed, 'RewriteEngine') === 0) {
                    $hasRewriteEngine = true;
                }
            }

            // Fix 1: Aggiungi RewriteEngine On se manca
            if ($hasRewriteRules && !$hasRewriteEngine) {
                // Trova la prima RewriteRule o RewriteCond e aggiungi RewriteEngine On prima
                $updated = preg_replace(
                    '/(RewriteRule|RewriteCond)/i',
                    "RewriteEngine On\n$1",
                    $content,
                    1
                );
                if ($updated !== $content) {
                    $content = $updated;
                    $fixes[] = 'Aggiunto "RewriteEngine On" mancante';
                }
            }

            // Fix 2: Rimuovi righe duplicate
            $uniqueLines = [];
            $removedDuplicates = 0;
            foreach ($lines as $line) {
                $trimmed = trim($line);
                // Non rimuovere linee vuote o commenti
                if (empty($trimmed) || strpos($trimmed, '#') === 0) {
                    $uniqueLines[] = $line;
                    continue;
                }
                
                if (!in_array($line, $uniqueLines, true)) {
                    $uniqueLines[] = $line;
                } else {
                    $removedDuplicates++;
                }
            }

            if ($removedDuplicates > 0) {
                $content = implode("\n", $uniqueLines);
                $fixes[] = "Rimosse {$removedDuplicates} righe duplicate";
            }

            // Fix 3: Correggi markers non bilanciati
            $fixedMarkers = $this->fixUnbalancedMarkers($content);
            if ($fixedMarkers !== $content) {
                $content = $fixedMarkers;
                $fixes[] = 'Corretti markers BEGIN/END non bilanciati';
            }

            // Se ci sono modifiche, salva il file
            if ($content !== $originalContent) {
                $this->backup($file);
                $result = $this->fs->putContents($file, $content);
                
                if (!$result) {
                    return [
                        'success' => false,
                        'fixes' => $fixes,
                        'errors' => ['Impossibile salvare il file riparato'],
                    ];
                }

                Logger::info('.htaccess repaired', ['fixes' => $fixes]);
                do_action('fp_ps_htaccess_repaired', $fixes);
            }

            return [
                'success' => true,
                'fixes' => $fixes,
                'errors' => [],
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to repair .htaccess', $e);
            return [
                'success' => false,
                'fixes' => $fixes,
                'errors' => ['Errore durante la riparazione: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Corregge markers BEGIN/END non bilanciati
     * 
     * @param string $content Contenuto da correggere
     * @return string Contenuto corretto
     */
    private function fixUnbalancedMarkers(string $content): string
    {
        $lines = explode("\n", $content);
        $stack = [];
        $toRemove = [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);
            
            if (preg_match('/^# BEGIN (.+)$/', $trimmed, $matches)) {
                $stack[] = ['name' => $matches[1], 'line' => $index];
            } elseif (preg_match('/^# END (.+)$/', $trimmed, $matches)) {
                if (empty($stack)) {
                    // END senza BEGIN corrispondente
                    $toRemove[] = $index;
                } else {
                    $last = array_pop($stack);
                    if ($last['name'] !== $matches[1]) {
                        // Nome non corrisponde
                        $toRemove[] = $index;
                        $stack[] = $last; // Rimetti nello stack
                    }
                }
            }
        }

        // Rimuovi markers non bilanciati
        foreach ($toRemove as $index) {
            unset($lines[$index]);
        }

        // Rimuovi BEGIN senza END
        foreach ($stack as $unmatched) {
            unset($lines[$unmatched['line']]);
        }

        return implode("\n", $lines);
    }

    /**
     * Ripristina il file .htaccess alle regole WordPress standard
     * 
     * @return bool True se il reset ha avuto successo
     */
    public function resetToWordPressDefault(): bool
    {
        $file = ABSPATH . '.htaccess';
        
        try {
            // Crea backup prima di resettare
            if ($this->fs->exists($file)) {
                $this->backup($file);
            }

            // Regole WordPress standard
            $defaultRules = "# BEGIN WordPress\n";
            $defaultRules .= "RewriteEngine On\n";
            $defaultRules .= "RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n";
            $defaultRules .= "RewriteBase /\n";
            $defaultRules .= "RewriteRule ^index\.php$ - [L]\n";
            $defaultRules .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
            $defaultRules .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
            $defaultRules .= "RewriteRule . /index.php [L]\n";
            $defaultRules .= "# END WordPress\n";

            $result = $this->fs->putContents($file, $defaultRules);
            
            if ($result) {
                Logger::info('.htaccess reset to WordPress defaults');
                do_action('fp_ps_htaccess_reset');
            }

            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to reset .htaccess', $e);
            return false;
        }
    }

    /**
     * Ottiene informazioni sul file .htaccess corrente
     * 
     * @return array Informazioni sul file
     */
    public function getFileInfo(): array
    {
        $file = ABSPATH . '.htaccess';
        
        $info = [
            'exists' => false,
            'path' => $file,
            'writable' => false,
            'size' => 0,
            'size_formatted' => '0 B',
            'modified' => null,
            'modified_formatted' => null,
            'lines' => 0,
            'sections' => [],
        ];

        if (!$this->fs->exists($file)) {
            return $info;
        }

        $info['exists'] = true;
        $info['writable'] = is_writable($file);
        $info['size'] = filesize($file);
        $info['size_formatted'] = size_format($info['size']);
        
        $modified = filemtime($file);
        if ($modified) {
            $info['modified'] = $modified;
            $info['modified_formatted'] = date_i18n('d/m/Y H:i:s', $modified);
        }

        try {
            $content = $this->fs->getContents($file);
            $info['lines'] = count(explode("\n", $content));
            
            // Trova tutte le sezioni
            preg_match_all('/# BEGIN (.+)$/m', $content, $matches);
            if (!empty($matches[1])) {
                $info['sections'] = $matches[1];
            }
        } catch (\Throwable $e) {
            Logger::error('Failed to read .htaccess info', $e);
        }

        return $info;
    }

    /**
     * Elimina un backup specifico
     * 
     * @param string $backupPath Percorso del backup da eliminare
     * @return bool True se l'eliminazione ha avuto successo
     */
    public function deleteBackup(string $backupPath): bool
    {
        try {
            if (!$this->fs->exists($backupPath)) {
                return false;
            }

            $result = $this->fs->delete($backupPath);
            
            if ($result) {
                Logger::info('.htaccess backup deleted', ['backup' => basename($backupPath)]);
            }

            return $result;
        } catch (\Throwable $e) {
            Logger::error('Failed to delete .htaccess backup', $e);
            return false;
        }
    }
}
