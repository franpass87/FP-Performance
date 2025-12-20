<?php

namespace FP\PerfSuite\Tests\Functional\REST;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * Test REST API error structures
 *
 * @package FP\PerfSuite\Tests\Functional\REST
 */
class RestApiErrorStructuresTest extends TestCase
{
    public function testErrorStructureIsConsistent(): void
    {
        $error = new \WP_Error(
            'invalid_param',
            'Invalid parameter',
            ['status' => 400, 'param' => 'url']
        );

        $this->assertInstanceOf(\WP_Error::class, $error);
        $this->assertEquals('invalid_param', $error->get_error_code());
        $this->assertEquals('Invalid parameter', $error->get_error_message());
        $this->assertArrayHasKey('status', $error->get_error_data());
    }

    public function testValidationErrorStructure(): void
    {
        $error = new \WP_Error(
            'rest_invalid_param',
            'Invalid parameter: url',
            [
                'status' => 400,
                'params' => ['url']
            ]
        );

        $this->assertEquals(400, $error->get_error_data()['status']);
        $this->assertArrayHasKey('params', $error->get_error_data());
    }

    public function testAuthenticationErrorStructure(): void
    {
        $error = new \WP_Error(
            'rest_forbidden',
            'Sorry, you are not allowed to do that.',
            ['status' => 401]
        );

        $this->assertEquals(401, $error->get_error_data()['status']);
        $this->assertStringContainsString('not allowed', $error->get_error_message());
    }

    public function testPermissionErrorStructure(): void
    {
        $error = new \WP_Error(
            'rest_forbidden',
            'Sorry, you do not have permission to do that.',
            ['status' => 403]
        );

        $this->assertEquals(403, $error->get_error_data()['status']);
        $this->assertStringContainsString('permission', $error->get_error_message());
    }

    public function testNotFoundErrorStructure(): void
    {
        $error = new \WP_Error(
            'rest_post_invalid_id',
            'Invalid post ID.',
            ['status' => 404]
        );

        $this->assertEquals(404, $error->get_error_data()['status']);
        $this->assertStringContainsString('Invalid', $error->get_error_message());
    }

    public function testServerErrorStructure(): void
    {
        $error = new \WP_Error(
            'rest_server_error',
            'Internal server error',
            ['status' => 500]
        );

        $this->assertEquals(500, $error->get_error_data()['status']);
        $this->assertStringContainsString('error', $error->get_error_message());
    }

    public function testErrorMessagesAreHelpful(): void
    {
        $errors = [
            new \WP_Error('invalid_param', 'URL parameter is required'),
            new \WP_Error('invalid_url', 'Invalid URL format'),
            new \WP_Error('cache_failed', 'Failed to purge cache')
        ];

        foreach ($errors as $error) {
            $this->assertNotEmpty($error->get_error_message());
            $this->assertGreaterThan(5, strlen($error->get_error_message()));
        }
    }

    public function testErrorCodesAreConsistent(): void
    {
        $errorCodes = [
            'rest_invalid_param',
            'rest_forbidden',
            'rest_post_invalid_id',
            'rest_server_error'
        ];

        foreach ($errorCodes as $code) {
            $this->assertStringStartsWith('rest_', $code);
        }
    }
}










