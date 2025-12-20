<?php

namespace FP\PerfSuite\Utils\Htaccess;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;
use function explode;
use function trim;
use function strpos;
use function preg_match_all;
use function in_array;
use const ABSPATH;

/**
 * Valida il file .htaccess
 * 
 * @package FP\PerfSuite\Utils\Htaccess
 * @author Francesco Passeri
 */
class Validator
{
    private Fs $fs;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Valida la sintassi del file .htaccess
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
}















