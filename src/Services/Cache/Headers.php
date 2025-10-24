<?php

namespace FP\PerfSuite\Services\Cache;

class Headers
{
    private $ttl;
    
    public function __construct($ttl = 3600)
    {
        $this->ttl = $ttl;
    }
    
    public function setCacheHeaders($ttl = null)
    {
        $ttl = $ttl ?: $this->ttl;
        
        if (!headers_sent()) {
            header('Cache-Control: public, max-age=' . $ttl);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
        }
    }
    
    public function setNoCacheHeaders()
    {
        if (!headers_sent()) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }
    
    public function setETag($content)
    {
        if (!headers_sent()) {
            $etag = md5($content);
            header('ETag: "' . $etag . '"');
        }
    }
    
    public function checkETag($etag)
    {
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
            http_response_code(304);
            exit;
        }
    }
}