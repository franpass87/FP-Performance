<?php

/**
 * HTTP Client Interface
 * 
 * Defines contract for HTTP client service
 *
 * @package FP\PerfSuite\Core\Http
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Http;

interface HttpClientInterface
{
    /**
     * Make HTTP GET request
     * 
     * @param string $url Request URL
     * @param array $args Request arguments
     * @return array|\WP_Error Response data or error
     */
    public function get(string $url, array $args = []);
    
    /**
     * Make HTTP POST request
     * 
     * @param string $url Request URL
     * @param array $body Request body
     * @param array $args Request arguments
     * @return array|\WP_Error Response data or error
     */
    public function post(string $url, array $body = [], array $args = []);
    
    /**
     * Make HTTP request
     * 
     * @param string $url Request URL
     * @param array $args Request arguments
     * @return array|\WP_Error Response data or error
     */
    public function request(string $url, array $args = []);
}









