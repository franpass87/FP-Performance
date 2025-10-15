<?php

namespace FP\PerfSuite\Services\Cache\EdgeCache;

use FP\PerfSuite\Utils\Logger;

/**
 * AWS CloudFront Edge Cache Provider
 *
 * @package FP\PerfSuite\Services\Cache\EdgeCache
 * @author Francesco Passeri
 */
class CloudFrontProvider implements EdgeCacheProvider
{
    private string $accessKeyId;
    private string $secretAccessKey;
    private string $distributionId;
    private string $region;

    public function __construct(string $accessKeyId, string $secretAccessKey, string $distributionId, string $region = 'us-east-1')
    {
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->distributionId = $distributionId;
        $this->region = $region;
    }

    /**
     * {@inheritdoc}
     */
    public function purgeAll(): bool
    {
        return $this->createInvalidation(['/*']);
    }

    /**
     * {@inheritdoc}
     */
    public function purgeUrls(array $urls): bool
    {
        if (empty($urls)) {
            return true;
        }

        // Convert full URLs to paths
        $paths = array_map(function($url) {
            $parsed = parse_url($url);
            return $parsed['path'] ?? '/';
        }, $urls);

        return $this->createInvalidation($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function purgeTags(array $tags): bool
    {
        // CloudFront doesn't support tag-based purging
        // We'd need to maintain a mapping of tags to paths
        Logger::warning('CloudFront does not support tag-based purging');
        return false;
    }

    /**
     * Create CloudFront invalidation
     *
     * @param array $paths Paths to invalidate
     * @return bool True if successful
     */
    private function createInvalidation(array $paths): bool
    {
        // CloudFront allows max 3000 paths per invalidation
        if (count($paths) > 3000) {
            $paths = array_slice($paths, 0, 3000);
        }

        $callerReference = 'fp-ps-' . time() . '-' . wp_generate_password(8, false);

        $xml = $this->buildInvalidationXml($paths, $callerReference);
        
        $endpoint = sprintf(
            'https://cloudfront.amazonaws.com/2020-05-31/distribution/%s/invalidation',
            $this->distributionId
        );

        $headers = $this->signRequest('POST', $endpoint, $xml);

        $response = wp_remote_post($endpoint, [
            'headers' => $headers,
            'body' => $xml,
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            Logger::error('CloudFront invalidation failed', ['error' => $response->get_error_message()]);
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code === 201) {
            Logger::info('CloudFront invalidation created', ['paths' => count($paths)]);
            return true;
        }

        Logger::error('CloudFront invalidation failed', [
            'code' => $code,
            'response' => wp_remote_retrieve_body($response),
        ]);

        return false;
    }

    /**
     * Build invalidation XML
     *
     * @param array $paths Paths to invalidate
     * @param string $callerReference Unique reference
     * @return string XML
     */
    private function buildInvalidationXml(array $paths, string $callerReference): string
    {
        $pathsXml = '';
        foreach ($paths as $path) {
            $pathsXml .= '<Path>' . htmlspecialchars($path) . '</Path>';
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<InvalidationBatch xmlns="http://cloudfront.amazonaws.com/doc/2020-05-31/">
    <Paths>
        <Quantity>{count($paths)}</Quantity>
        <Items>
            $pathsXml
        </Items>
    </Paths>
    <CallerReference>$callerReference</CallerReference>
</InvalidationBatch>
XML;
    }

    /**
     * Sign AWS request (AWS Signature Version 4)
     *
     * @param string $method HTTP method
     * @param string $endpoint Endpoint URL
     * @param string $body Request body
     * @return array Headers
     */
    private function signRequest(string $method, string $endpoint, string $body): array
    {
        $service = 'cloudfront';
        $timestamp = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');

        $parsedUrl = parse_url($endpoint);
        $host = $parsedUrl['host'];
        $path = $parsedUrl['path'];

        // Create canonical request
        $payloadHash = hash('sha256', $body);
        
        $canonicalHeaders = "host:$host\nx-amz-date:$timestamp\n";
        $signedHeaders = 'host;x-amz-date';

        $canonicalRequest = "$method\n$path\n\n$canonicalHeaders\n$signedHeaders\n$payloadHash";

        // Create string to sign
        $credentialScope = "$date/{$this->region}/$service/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n$timestamp\n$credentialScope\n" . hash('sha256', $canonicalRequest);

        // Calculate signature
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $this->secretAccessKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        // Build authorization header
        $authorization = "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

        return [
            'Authorization' => $authorization,
            'x-amz-date' => $timestamp,
            'x-amz-content-sha256' => $payloadHash,
            'Content-Type' => 'application/xml',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): array
    {
        $endpoint = sprintf(
            'https://cloudfront.amazonaws.com/2020-05-31/distribution/%s',
            $this->distributionId
        );

        $headers = $this->signRequest('GET', $endpoint, '');

        $response = wp_remote_get($endpoint, [
            'headers' => $headers,
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200) {
            return [
                'success' => false,
                'message' => 'Invalid AWS credentials or Distribution ID',
            ];
        }

        return [
            'success' => true,
            'message' => 'Connected successfully',
            'info' => [
                'distribution_id' => $this->distributionId,
                'region' => $this->region,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getStats(): array
    {
        // CloudFront stats require CloudWatch API integration
        // This is a simplified placeholder
        Logger::info('CloudFront stats require CloudWatch integration');
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'AWS CloudFront';
    }
}
