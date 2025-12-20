<?php

namespace FP\PerfSuite\Services\Cache\PageCache;

/**
 * Gestisce la normalizzazione e conversione degli URL
 * 
 * @package FP\PerfSuite\Services\Cache\PageCache
 * @author Francesco Passeri
 */
class UrlNormalizer
{
    /**
     * Converte un URL in una chiave cache
     */
    public function urlToKey(string $url): string
    {
        // Normalizza l'URL
        $url = $this->normalizeUrl($url);
        
        // Usa lo stesso metodo di generazione chiave usato altrove
        return md5($url);
    }

    /**
     * Normalizza un URL per coerenza nella cache
     */
    public function normalizeUrl(string $url): string
    {
        // Rimuovi schema per evitare duplicati http/https
        $url = preg_replace('#^https?://#i', '', $url);
        
        // Rimuovi trailing slash
        $url = rtrim($url, '/');
        
        // Converti in lowercase per case-insensitive matching
        $url = strtolower($url);
        
        return $url;
    }

    /**
     * Converte un pattern wildcard in regex
     */
    public function patternToRegex(string $pattern): string
    {
        // Se già un regex (inizia con / o #), usa direttamente
        if (preg_match('/^[\/\#]/', $pattern)) {
            return $pattern;
        }
        
        // Converti wildcard in regex
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        $pattern = str_replace('\?', '.', $pattern);
        
        return '/' . $pattern . '/i';
    }
}
















