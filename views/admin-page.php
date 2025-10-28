<?php
/** @var array<string, mixed> $pageData */
$title = $pageData['title'] ?? '';
$content = $pageData['content'] ?? '';
$breadcrumbs = $pageData['breadcrumbs'] ?? [];
?>
<div class="fp-ps-wrap">
    <header class="fp-ps-header">
        <h1><?php echo esc_html($title); ?></h1>
        <?php if (!empty($breadcrumbs)) : ?>
            <nav class="fp-ps-breadcrumbs" aria-label="Breadcrumb">
                <ol>
                    <?php foreach ($breadcrumbs as $crumb) : ?>
                        <li><?php echo esc_html($crumb); ?></li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
    </header>
    <main class="fp-ps-content">
        <?php echo $content; // already escaped/sanitized within content provider ?>
    </main>
</div>