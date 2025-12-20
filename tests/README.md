# FP Performance Suite - Test Suite

## Overview

This directory contains the complete test suite for FP Performance Suite plugin, implementing the comprehensive QA Plan.

## Test Structure

```
tests/
├── Unit/                    # Unit tests for individual classes
│   ├── Core/               # Core service tests
│   └── Services/           # Service class tests
├── Integration/            # Integration tests
│   ├── Container/         # Container tests
│   ├── ServiceProviders/ # Service provider tests
│   ├── Options/          # Options integration tests
│   └── Hooks/            # Hook registration tests
├── Functional/            # Functional tests
│   ├── Admin/            # Admin UI tests
│   ├── REST/             # REST API tests
│   └── CLI/              # WP-CLI tests
├── Security/              # Security tests
├── Performance/           # Performance tests
├── Fixtures/              # Test data fixtures
├── e2e/                   # End-to-end tests (Playwright)
├── Bootstrap.php          # Test bootstrap
└── TestCase.php           # Base test case
```

## Running Tests

### All Tests
```bash
vendor/bin/phpunit
```

### Specific Test Suite
```bash
# Unit tests only
vendor/bin/phpunit --testsuite=Unit

# Integration tests
vendor/bin/phpunit --testsuite=Integration

# Functional tests
vendor/bin/phpunit --testsuite=Functional

# Security tests
vendor/bin/phpunit --testsuite=Security
```

### With Coverage
```bash
vendor/bin/phpunit --coverage-html tests/coverage/html
```

### Specific Test File
```bash
vendor/bin/phpunit tests/Unit/Core/OptionsRepositoryTest.php
```

## Test Coverage

Current coverage targets:
- Core services: 80%+
- Security-critical paths: 100%
- Admin UI: 60%+
- REST API: 70%+
- Asset optimization: 50%+

## Writing New Tests

1. Place tests in appropriate directory (Unit/Integration/Functional)
2. Extend `TestCase` class
3. Use BrainMonkey for WordPress function mocking
4. Follow naming convention: `*Test.php`
5. Write descriptive test method names starting with `test`

## Example Test

```php
<?php

namespace FP\PerfSuite\Tests\Unit\Services;

use FP\PerfSuite\Tests\TestCase;
use Brain\Monkey\Functions;

class MyServiceTest extends TestCase
{
    public function testMyMethod(): void
    {
        Functions\expect('get_option')
            ->once()
            ->andReturn('value');

        // Test implementation
        $this->assertTrue(true);
    }
}
```










