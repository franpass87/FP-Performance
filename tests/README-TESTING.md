# FP Performance Suite - Testing Guide

## Overview

This plugin includes a comprehensive test suite covering unit tests, integration tests, functional tests, security tests, and performance tests.

## Test Structure

```
tests/
├── Unit/              # Unit tests for individual classes
├── Integration/        # Integration tests for service providers
├── Functional/        # Functional tests for REST API, CLI
├── Security/          # Security tests
├── Performance/       # Performance profiling tests
├── Fixtures/          # Test data fixtures
└── e2e/               # End-to-end tests (Playwright)
```

## Running Tests

### Unit Tests

```bash
vendor/bin/phpunit --testsuite=Unit
```

### Integration Tests

```bash
vendor/bin/phpunit --testsuite=Integration
```

### Functional Tests

```bash
vendor/bin/phpunit --testsuite=Functional
```

### Security Tests

```bash
vendor/bin/phpunit --testsuite=Security
```

### All Tests

```bash
vendor/bin/phpunit
```

### With Coverage

```bash
vendor/bin/phpunit --coverage-html tests/coverage/html
```

## E2E Tests

E2E tests use Playwright and require a running WordPress installation.

```bash
npm install
npx playwright test
```

## CI/CD

Tests run automatically on:
- Push to main/develop branches
- Pull requests
- Weekly schedule (Sunday)

See `.github/workflows/ci.yml` for details.

## Test Coverage Goals

- Core services: 80%+
- Security-critical paths: 100%
- Admin UI: 60%+
- REST API: 70%+
- Asset optimization: 50%+







