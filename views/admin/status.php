<?php
/**
 * Status Page View Template.
 *
 * @var array<string, mixed> $pageData
 */

$title = __('FP Performance Status', 'fp-performance-suite');
$content = $pageData['content'] ?? '';

// Include base admin page template
include FP_PERF_SUITE_DIR . '/views/admin-page.php';

