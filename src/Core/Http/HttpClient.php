<?php

/**
 * HTTP Client
 * 
 * WordPress HTTP API wrapper with retry logic, timeout handling, and error handling
 *
 * @package FP\PerfSuite\Core\Http
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Http;

class HttpClient implements HttpClientInterface
{
    /** @var int Default timeout in seconds */
    private int $timeout;
    
    /** @var int Maximum retry attempts */
    private int $maxRetries;
    
    /** @var int Retry delay in seconds */
    private int $retryDelay;
    
    /**
     * Constructor
     * 
     * @param int $timeout Request timeout
     * @param int $maxRetries Maximum retry attempts
     * @param int $retryDelay Retry delay in seconds
     */
    public function __construct(int $timeout = 30, int $maxRetries = 3, int $retryDelay = 1)
    {
        $this->timeout = $timeout;
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $retryDelay;
    }
    
    /**
     * Make HTTP GET request
     * 
     * @param string $url Request URL
     * @param array $args Request arguments
     * @return array|\WP_Error Response data or error
     */
    public function get(string $url, array $args = [])
    {
        $args['method'] = 'GET';
        return $this->request($url, $args);
    }
    
    /**
     * Make HTTP POST request
     * 
     * @param string $url Request URL
     * @param array $body Request body
     * @param array $args Request arguments
     * @return array|\WP_Error Response data or error
     */
    public function post(string $url, array $body = [], array $args = [])
    {
        $args['method'] = 'POST';
        $args['body'] = $body;
        return $this->request($url, $args);
    }
    
    /**
     * Make HTTP request
     * 
     * @param string $url Request URL
     * @param array $args Request arguments
     * @return array|\WP_Error Response data or error
     */
    public function request(string $url, array $args = [])
    {
        // Set default timeout
        if (!isset($args['timeout'])) {
            $args['timeout'] = $this->timeout;
        }
        
        // Add default headers
        if (!isset($args['headers'])) {
            $args['headers'] = [];
        }
        
        $args['headers']['User-Agent'] = $args['headers']['User-Agent'] ?? 'FP-Performance-Suite/' . (defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : '2.0.0');
        
        // Retry logic
        $attempts = 0;
        $lastError = null;
        
        while ($attempts < $this->maxRetries) {
            $response = wp_remote_request($url, $args);
            
            // Check if request succeeded
            if (!is_wp_error($response)) {
                $statusCode = wp_remote_retrieve_response_code($response);
                
                // Retry on 5xx errors
                if ($statusCode >= 500 && $statusCode < 600 && $attempts < $this->maxRetries - 1) {
                    $attempts++;
                    sleep($this->retryDelay * $attempts); // Exponential backoff
                    continue;
                }
                
                return [
                    'body' => wp_remote_retrieve_body($response),
                    'headers' => wp_remote_retrieve_headers($response),
                    'response' => [
                        'code' => $statusCode,
                        'message' => wp_remote_retrieve_response_message($response),
                    ],
                ];
            }
            
            $lastError = $response;
            $attempts++;
            
            if ($attempts < $this->maxRetries) {
                sleep($this->retryDelay * $attempts);
            }
        }
        
        return $lastError ?? new \WP_Error('http_request_failed', __('HTTP request failed after retries', 'fp-performance-suite'));
    }
}









