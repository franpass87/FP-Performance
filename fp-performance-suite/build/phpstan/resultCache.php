<?php declare(strict_types = 1);

return [
	'lastFullAnalysisTime' => 1759945425,
	'meta' => array (
  'cacheVersion' => 'v12-linesToIgnore',
  'phpstanVersion' => '1.12.32',
  'phpVersion' => 80405,
  'projectConfig' => '{parameters: {bootstrapFiles: [/workspace/fp-performance-suite/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php, /workspace/fp-performance-suite/vendor/szepeviktor/phpstan-wordpress/bootstrap.php], dynamicConstantNames: [WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY, EMPTY_TRASH_DAYS, WP_CLI, COOKIE_DOMAIN, SAVEQUERIES, SCRIPT_DEBUG], earlyTerminatingFunctionCalls: [wp_send_json, wp_send_json_success, wp_send_json_error, wp_nonce_ays, comment_footer_die, dead_db, do_favicon, install_theme_information, media_send_to_editor, redirect_post, wp_ajax_heartbeat, wp_ajax_nopriv_heartbeat], earlyTerminatingMethodCalls: {Custom_Background: [wp_set_background_image], IXR_Server: [output], WP_Ajax_Response: [send], WP_CLI: [error, halt], WP_Recovery_Mode: [redirect_protected], WP_Sitemaps_Stylesheet: [render_stylesheet]}, level: 6, paths: [/workspace/fp-performance-suite/src], tmpDir: /workspace/fp-performance-suite/build/phpstan}, rules: [SzepeViktor\\PHPStan\\WordPress\\HookCallbackRule, SzepeViktor\\PHPStan\\WordPress\\HookDocsRule, SzepeViktor\\PHPStan\\WordPress\\IsWpErrorRule], services: [{class: SzepeViktor\\PHPStan\\WordPress\\HookDocBlock}, {class: SzepeViktor\\PHPStan\\WordPress\\WpThemeGetDynamicMethodReturnTypeExtension, tags: [phpstan.broker.dynamicMethodReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\IsWpErrorFunctionTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.functionTypeSpecifyingExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\WpThemeMagicPropertiesClassReflectionExtension, tags: [phpstan.broker.propertiesClassReflectionExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\EchoKeyDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetApprovedCommentsDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetPermalinkDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetListTableDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\RedirectCanonicalDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\StringOrArrayDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetTermsDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetPostDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetPostsDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetSitesDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetTaxonomiesDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetObjectTaxonomiesDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\GetCommentDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\HasFilterDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\ShortcodeAttsDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\MySQL2DateDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\CurrentTimeDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\ApplyFiltersDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\EchoParameterDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\WpErrorParameterDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\TermExistsDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\WpParseUrlFunctionDynamicReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\WpDieDynamicFunctionReturnTypeExtension, tags: [phpstan.broker.dynamicFunctionReturnTypeExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\HookDocsVisitor, tags: [phpstan.parser.richParserNodeVisitor]}, {class: SzepeViktor\\PHPStan\\WordPress\\AssertWpErrorTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.methodTypeSpecifyingExtension]}, {class: SzepeViktor\\PHPStan\\WordPress\\AssertNotWpErrorTypeSpecifyingExtension, tags: [phpstan.typeSpecifier.methodTypeSpecifyingExtension]}]}',
  'analysedPaths' => 
  array (
    0 => '/workspace/fp-performance-suite/src',
  ),
  'scannedFiles' => 
  array (
  ),
  'composerLocks' => 
  array (
    '/workspace/fp-performance-suite/composer.lock' => '32f664db923c8ebff9cb9be256bf5d37b6c30dbd',
  ),
  'composerInstalled' => 
  array (
    '/workspace/fp-performance-suite/vendor/composer/installed.php' => 
    array (
      'versions' => 
      array (
        'clue/ndjson-react' => 
        array (
          'pretty_version' => 'v1.3.0',
          'version' => '1.3.0.0',
          'reference' => '392dc165fce93b5bb5c637b67e59619223c931b0',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../clue/ndjson-react',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'composer/pcre' => 
        array (
          'pretty_version' => '3.3.2',
          'version' => '3.3.2.0',
          'reference' => 'b2bed4734f0cc156ee1fe9c0da2550420d99a21e',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/./pcre',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'composer/semver' => 
        array (
          'pretty_version' => '3.4.4',
          'version' => '3.4.4.0',
          'reference' => '198166618906cb2de69b95d7d47e5fa8aa1b2b95',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/./semver',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'composer/xdebug-handler' => 
        array (
          'pretty_version' => '3.0.5',
          'version' => '3.0.5.0',
          'reference' => '6c1925561632e83d60a44492e0b344cf48ab85ef',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/./xdebug-handler',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'evenement/evenement' => 
        array (
          'pretty_version' => 'v3.0.2',
          'version' => '3.0.2.0',
          'reference' => '0a16b0d71ab13284339abb99d9d2bd813640efbc',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../evenement/evenement',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'fidry/cpu-core-counter' => 
        array (
          'pretty_version' => '1.3.0',
          'version' => '1.3.0.0',
          'reference' => 'db9508f7b1474469d9d3c53b86f817e344732678',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../fidry/cpu-core-counter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'friendsofphp/php-cs-fixer' => 
        array (
          'pretty_version' => 'v3.88.2',
          'version' => '3.88.2.0',
          'reference' => 'a8d15584bafb0f0d9d938827840060fd4a3ebc99',
          'type' => 'application',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../friendsofphp/php-cs-fixer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'myclabs/deep-copy' => 
        array (
          'pretty_version' => '1.13.4',
          'version' => '1.13.4.0',
          'reference' => '07d290f0c47959fd5eed98c95ee5602db07e0b6a',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../myclabs/deep-copy',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'nikic/php-parser' => 
        array (
          'pretty_version' => 'v5.6.1',
          'version' => '5.6.1.0',
          'reference' => 'f103601b29efebd7ff4a1ca7b3eeea9e3336a2a2',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../nikic/php-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phar-io/manifest' => 
        array (
          'pretty_version' => '2.0.4',
          'version' => '2.0.4.0',
          'reference' => '54750ef60c58e43759730615a392c31c80e23176',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phar-io/manifest',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phar-io/version' => 
        array (
          'pretty_version' => '3.2.1',
          'version' => '3.2.1.0',
          'reference' => '4f7fd7836c6f332bb2933569e566a0d6c4cbed74',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phar-io/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'php-stubs/wordpress-stubs' => 
        array (
          'pretty_version' => 'v6.8.2',
          'version' => '6.8.2.0',
          'reference' => '9c8e22e437463197c1ec0d5eaa9ddd4a0eb6d7f8',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../php-stubs/wordpress-stubs',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpstan/phpstan' => 
        array (
          'pretty_version' => '1.12.32',
          'version' => '1.12.32.0',
          'reference' => '2770dcdf5078d0b0d53f94317e06affe88419aa8',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phpstan/phpstan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-code-coverage' => 
        array (
          'pretty_version' => '10.1.16',
          'version' => '10.1.16.0',
          'reference' => '7e308268858ed6baedc8704a304727d20bc07c77',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phpunit/php-code-coverage',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-file-iterator' => 
        array (
          'pretty_version' => '4.1.0',
          'version' => '4.1.0.0',
          'reference' => 'a95037b6d9e608ba092da1b23931e537cadc3c3c',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phpunit/php-file-iterator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-invoker' => 
        array (
          'pretty_version' => '4.0.0',
          'version' => '4.0.0.0',
          'reference' => 'f5e568ba02fa5ba0ddd0f618391d5a9ea50b06d7',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phpunit/php-invoker',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-text-template' => 
        array (
          'pretty_version' => '3.0.1',
          'version' => '3.0.1.0',
          'reference' => '0c7b06ff49e3d5072f057eb1fa59258bf287a748',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phpunit/php-text-template',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/php-timer' => 
        array (
          'pretty_version' => '6.0.0',
          'version' => '6.0.0.0',
          'reference' => 'e2a2d67966e740530f4a3343fe2e030ffdc1161d',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phpunit/php-timer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'phpunit/phpunit' => 
        array (
          'pretty_version' => '10.5.58',
          'version' => '10.5.58.0',
          'reference' => 'e24fb46da450d8e6a5788670513c1af1424f16ca',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../phpunit/phpunit',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'psr/container' => 
        array (
          'pretty_version' => '2.0.2',
          'version' => '2.0.2.0',
          'reference' => 'c71ecc56dfe541dbd90c5360474fbc405f8d5963',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../psr/container',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'psr/event-dispatcher' => 
        array (
          'pretty_version' => '1.0.0',
          'version' => '1.0.0.0',
          'reference' => 'dbefd12671e8a14ec7f180cab83036ed26714bb0',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../psr/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'psr/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => true,
          'provided' => 
          array (
            0 => '1.0',
          ),
        ),
        'psr/log' => 
        array (
          'pretty_version' => '3.0.2',
          'version' => '3.0.2.0',
          'reference' => 'f16e1d5863e37f8d8c2a01719f5b34baa2b714d3',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../psr/log',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'psr/log-implementation' => 
        array (
          'dev_requirement' => true,
          'provided' => 
          array (
            0 => '1.0|2.0|3.0',
          ),
        ),
        'react/cache' => 
        array (
          'pretty_version' => 'v1.2.0',
          'version' => '1.2.0.0',
          'reference' => 'd47c472b64aa5608225f47965a484b75c7817d5b',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../react/cache',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'react/child-process' => 
        array (
          'pretty_version' => 'v0.6.6',
          'version' => '0.6.6.0',
          'reference' => '1721e2b93d89b745664353b9cfc8f155ba8a6159',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../react/child-process',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'react/dns' => 
        array (
          'pretty_version' => 'v1.13.0',
          'version' => '1.13.0.0',
          'reference' => 'eb8ae001b5a455665c89c1df97f6fb682f8fb0f5',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../react/dns',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'react/event-loop' => 
        array (
          'pretty_version' => 'v1.5.0',
          'version' => '1.5.0.0',
          'reference' => 'bbe0bd8c51ffc05ee43f1729087ed3bdf7d53354',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../react/event-loop',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'react/promise' => 
        array (
          'pretty_version' => 'v3.3.0',
          'version' => '3.3.0.0',
          'reference' => '23444f53a813a3296c1368bb104793ce8d88f04a',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../react/promise',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'react/socket' => 
        array (
          'pretty_version' => 'v1.16.0',
          'version' => '1.16.0.0',
          'reference' => '23e4ff33ea3e160d2d1f59a0e6050e4b0fb0eac1',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../react/socket',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'react/stream' => 
        array (
          'pretty_version' => 'v1.4.0',
          'version' => '1.4.0.0',
          'reference' => '1e5b0acb8fe55143b5b426817155190eb6f5b18d',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../react/stream',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/cli-parser' => 
        array (
          'pretty_version' => '2.0.1',
          'version' => '2.0.1.0',
          'reference' => 'c34583b87e7b7a8055bf6c450c2c77ce32a24084',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/cli-parser',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/code-unit' => 
        array (
          'pretty_version' => '2.0.0',
          'version' => '2.0.0.0',
          'reference' => 'a81fee9eef0b7a76af11d121767abc44c104e503',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/code-unit',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/code-unit-reverse-lookup' => 
        array (
          'pretty_version' => '3.0.0',
          'version' => '3.0.0.0',
          'reference' => '5e3a687f7d8ae33fb362c5c0743794bbb2420a1d',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/code-unit-reverse-lookup',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/comparator' => 
        array (
          'pretty_version' => '5.0.4',
          'version' => '5.0.4.0',
          'reference' => 'e8e53097718d2b53cfb2aa859b06a41abf58c62e',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/comparator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/complexity' => 
        array (
          'pretty_version' => '3.2.0',
          'version' => '3.2.0.0',
          'reference' => '68ff824baeae169ec9f2137158ee529584553799',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/complexity',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/diff' => 
        array (
          'pretty_version' => '5.1.1',
          'version' => '5.1.1.0',
          'reference' => 'c41e007b4b62af48218231d6c2275e4c9b975b2e',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/diff',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/environment' => 
        array (
          'pretty_version' => '6.1.0',
          'version' => '6.1.0.0',
          'reference' => '8074dbcd93529b357029f5cc5058fd3e43666984',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/environment',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/exporter' => 
        array (
          'pretty_version' => '5.1.4',
          'version' => '5.1.4.0',
          'reference' => '0735b90f4da94969541dac1da743446e276defa6',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/exporter',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/global-state' => 
        array (
          'pretty_version' => '6.0.2',
          'version' => '6.0.2.0',
          'reference' => '987bafff24ecc4c9ac418cab1145b96dd6e9cbd9',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/global-state',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/lines-of-code' => 
        array (
          'pretty_version' => '2.0.2',
          'version' => '2.0.2.0',
          'reference' => '856e7f6a75a84e339195d48c556f23be2ebf75d0',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/lines-of-code',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-enumerator' => 
        array (
          'pretty_version' => '5.0.0',
          'version' => '5.0.0.0',
          'reference' => '202d0e344a580d7f7d04b3fafce6933e59dae906',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/object-enumerator',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/object-reflector' => 
        array (
          'pretty_version' => '3.0.0',
          'version' => '3.0.0.0',
          'reference' => '24ed13d98130f0e7122df55d06c5c4942a577957',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/object-reflector',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/recursion-context' => 
        array (
          'pretty_version' => '5.0.1',
          'version' => '5.0.1.0',
          'reference' => '47e34210757a2f37a97dcd207d032e1b01e64c7a',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/recursion-context',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/type' => 
        array (
          'pretty_version' => '4.0.0',
          'version' => '4.0.0.0',
          'reference' => '462699a16464c3944eefc02ebdd77882bd3925bf',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/type',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'sebastian/version' => 
        array (
          'pretty_version' => '4.0.1',
          'version' => '4.0.1.0',
          'reference' => 'c51fa83a5d8f43f1402e3f32a005e6262244ef17',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../sebastian/version',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'squizlabs/php_codesniffer' => 
        array (
          'pretty_version' => '3.13.4',
          'version' => '3.13.4.0',
          'reference' => 'ad545ea9c1b7d270ce0fc9cbfb884161cd706119',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../squizlabs/php_codesniffer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/console' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => '2b9c5fafbac0399a20a2e82429e2bd735dcfb7db',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/console',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/deprecation-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '63afe740e99a13ba87ec199bb07bbdee937a5b62',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/deprecation-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/event-dispatcher' => 
        array (
          'pretty_version' => 'v7.3.3',
          'version' => '7.3.3.0',
          'reference' => 'b7dc69e71de420ac04bc9ab830cf3ffebba48191',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/event-dispatcher',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/event-dispatcher-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => '59eb412e93815df44f05f342958efa9f46b1e586',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/event-dispatcher-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/event-dispatcher-implementation' => 
        array (
          'dev_requirement' => true,
          'provided' => 
          array (
            0 => '2.0|3.0',
          ),
        ),
        'symfony/filesystem' => 
        array (
          'pretty_version' => 'v7.3.2',
          'version' => '7.3.2.0',
          'reference' => 'edcbb768a186b5c3f25d0643159a787d3e63b7fd',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/filesystem',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/finder' => 
        array (
          'pretty_version' => 'v7.3.2',
          'version' => '7.3.2.0',
          'reference' => '2a6614966ba1074fa93dae0bc804227422df4dfe',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/finder',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/options-resolver' => 
        array (
          'pretty_version' => 'v7.3.3',
          'version' => '7.3.3.0',
          'reference' => '0ff2f5c3df08a395232bbc3c2eb7e84912df911d',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/options-resolver',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-ctype' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'a3cc8b044a6ea513310cbd48ef7333b384945638',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-ctype',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-intl-grapheme' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '380872130d3a5dd3ace2f4010d95125fde5d5c70',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-intl-grapheme',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-intl-normalizer' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '3833d7255cc303546435cb650316bff708a1c75c',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-intl-normalizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-mbstring' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '6d857f4d76bd4b343eac26d6b539585d2bc56493',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-mbstring',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-php73' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '0f68c03565dcaaf25a890667542e8bd75fe7e5bb',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-php73',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-php80' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '0cc9dd0f17f61d8131e7df6b84bd344899fe2608',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-php80',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-php81' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => '4a4cfc2d253c21a5ad0e53071df248ed48c6ce5c',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-php81',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/polyfill-php84' => 
        array (
          'pretty_version' => 'v1.33.0',
          'version' => '1.33.0.0',
          'reference' => 'd8ced4d875142b6a7426000426b8abc631d6b191',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/polyfill-php84',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/process' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'f24f8f316367b30810810d4eb30c543d7003ff3b',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/process',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/service-contracts' => 
        array (
          'pretty_version' => 'v3.6.0',
          'version' => '3.6.0.0',
          'reference' => 'f021b05a130d35510bd6b25fe9053c2a8a15d5d4',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/service-contracts',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/stopwatch' => 
        array (
          'pretty_version' => 'v7.3.0',
          'version' => '7.3.0.0',
          'reference' => '5a49289e2b308214c8b9c2fda4ea454d8b8ad7cd',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/stopwatch',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'symfony/string' => 
        array (
          'pretty_version' => 'v7.3.4',
          'version' => '7.3.4.0',
          'reference' => 'f96476035142921000338bad71e5247fbc138872',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../symfony/string',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'szepeviktor/phpstan-wordpress' => 
        array (
          'pretty_version' => 'v1.3.5',
          'version' => '1.3.5.0',
          'reference' => '7f8cfe992faa96b6a33bbd75c7bace98864161e7',
          'type' => 'phpstan-extension',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../szepeviktor/phpstan-wordpress',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
        'theseer/tokenizer' => 
        array (
          'pretty_version' => '1.2.3',
          'version' => '1.2.3.0',
          'reference' => '737eda637ed5e28c3413cb1ebe8bb52cbf1ca7a2',
          'type' => 'library',
          'install_path' => '/workspace/fp-performance-suite/vendor/composer/../theseer/tokenizer',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
      ),
    ),
  ),
  'executedFilesHashes' => 
  array (
    '/workspace/fp-performance-suite/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php' => '453eb11cbde1730b2f61449094bd9c2d4e208903',
    '/workspace/fp-performance-suite/vendor/szepeviktor/phpstan-wordpress/bootstrap.php' => '19f2ebf6ff2aed9d3183a80688a33656e0794de5',
    'phar:///workspace/fp-performance-suite/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/Attribute.php' => 'eaf9127f074e9c7ebc65043ec4050f9fed60c2bb',
    'phar:///workspace/fp-performance-suite/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionAttribute.php' => '0b4b78277eb6545955d2ce5e09bff28f1f8052c8',
    'phar:///workspace/fp-performance-suite/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionIntersectionType.php' => 'a3e6299b87ee5d407dae7651758edfa11a74cb11',
    'phar:///workspace/fp-performance-suite/vendor/phpstan/phpstan/phpstan.phar/stubs/runtime/ReflectionUnionType.php' => '1b349aa997a834faeafe05fa21bc31cae22bf2e2',
  ),
  'phpExtensions' => 
  array (
    0 => 'Core',
    1 => 'FFI',
    2 => 'PDO',
    3 => 'Phar',
    4 => 'Reflection',
    5 => 'SPL',
    6 => 'SimpleXML',
    7 => 'Zend OPcache',
    8 => 'calendar',
    9 => 'ctype',
    10 => 'curl',
    11 => 'date',
    12 => 'dom',
    13 => 'exif',
    14 => 'fileinfo',
    15 => 'filter',
    16 => 'ftp',
    17 => 'gettext',
    18 => 'hash',
    19 => 'iconv',
    20 => 'json',
    21 => 'libxml',
    22 => 'mbstring',
    23 => 'openssl',
    24 => 'pcntl',
    25 => 'pcre',
    26 => 'posix',
    27 => 'random',
    28 => 'readline',
    29 => 'session',
    30 => 'shmop',
    31 => 'sockets',
    32 => 'sodium',
    33 => 'standard',
    34 => 'sysvmsg',
    35 => 'sysvsem',
    36 => 'sysvshm',
    37 => 'tokenizer',
    38 => 'xml',
    39 => 'xmlreader',
    40 => 'xmlwriter',
    41 => 'xsl',
    42 => 'zip',
    43 => 'zlib',
  ),
  'stubFiles' => 
  array (
  ),
  'level' => '6',
),
	'projectExtensionFiles' => array (
),
	'errorsCallback' => static function (): array { return array (
  '/workspace/fp-performance-suite/src/Admin/Assets.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_FILE not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'line' => 20,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 20,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_VERSION not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'line' => 22,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 22,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_FILE not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'line' => 27,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 27,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_VERSION not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'line' => 29,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Assets.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 29,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 44,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php',
       'line' => 40,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 40,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 37,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php',
       'line' => 48,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 48,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Database.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Database.php',
       'line' => 42,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Database.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 42,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 36,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Media.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Media.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Media.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 37,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 34,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 36,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Settings.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Settings.php',
       'line' => 39,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Settings.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 39,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
       'line' => 56,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 56,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Cli/Commands.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::cache() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 30,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 30,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::cache() has parameter $args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 30,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 30,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::cache() has parameter $assoc_args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 30,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 30,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 39,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 39,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 52,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 52,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method success() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 55,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 58,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 58,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 72,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 72,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 73,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 73,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 74,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 74,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 76,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 76,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::db() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 104,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 104,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::db() has parameter $args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 104,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 104,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::db() has parameter $assoc_args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 104,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 104,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 113,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 113,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::dbCleanup() has parameter $assoc_args with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 120,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 120,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 132,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 132,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 133,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 137,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 137,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 142,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 142,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method success() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 147,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 147,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 150,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 150,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 164,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 164,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 165,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 165,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 166,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 166,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 169,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 169,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 172,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 172,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::webp() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 194,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 194,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    28 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::webp() has parameter $args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 194,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 194,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    29 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::webp() has parameter $assoc_args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 194,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 194,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    30 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 203,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 203,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    31 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::webpConvert() has parameter $assoc_args with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 210,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 210,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    32 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 218,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 218,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    33 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 223,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 223,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    34 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 228,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 228,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    35 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 229,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 229,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    36 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 239,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 239,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    37 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method success() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 246,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 246,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    38 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 248,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 248,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    39 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 252,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 252,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    40 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 266,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 266,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    41 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 267,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 267,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    42 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 268,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 268,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    43 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 269,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 269,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    44 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 271,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 271,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    45 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::score() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 285,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 285,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    46 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::score() has parameter $args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 285,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 285,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    47 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::score() has parameter $assoc_args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 285,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 285,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    48 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 291,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 291,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    49 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method colorize() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 294,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 294,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    50 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 294,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 294,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    51 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method colorize() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 295,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 295,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    52 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 295,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 295,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    53 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method colorize() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 297,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 297,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    54 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 297,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 297,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    55 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method colorize() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 300,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 300,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    56 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 300,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 300,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    57 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method colorize() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 304,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 304,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    58 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 304,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 304,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    59 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 306,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 306,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    60 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method error() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 311,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 311,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    61 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::info() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 325,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 325,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    62 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::info() has parameter $args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 325,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 325,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    63 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Cli\\Commands::info() has parameter $assoc_args with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 325,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 325,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    64 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method colorize() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 327,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 327,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    65 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 327,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 327,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    66 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 328,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 328,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    67 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 329,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 329,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    68 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 330,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 330,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    69 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method colorize() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 332,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 332,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    70 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 332,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 332,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    71 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 333,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 333,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    72 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 334,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 334,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    73 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 335,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 335,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    74 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 336,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 336,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    75 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 337,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 337,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    76 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 338,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 338,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    77 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 339,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 339,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    78 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method log() on an unknown class WP_CLI.',
       'file' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'line' => 340,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Cli/Commands.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 340,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\CacheInterface::settings() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php',
       'line' => 20,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 20,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\CacheInterface::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php',
       'line' => 27,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 27,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\CacheInterface::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php',
       'line' => 39,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 39,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/LoggerInterface.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\LoggerInterface::debug() has parameter $context with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/LoggerInterface.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/LoggerInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 28,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\OptimizerInterface::settings() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php',
       'line' => 20,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 20,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\OptimizerInterface::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php',
       'line' => 27,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 27,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\OptimizerInterface::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php',
       'line' => 34,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 34,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/SettingsRepositoryInterface.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface::all() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Contracts/SettingsRepositoryInterface.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Contracts/SettingsRepositoryInterface.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 49,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/CdnProvider.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Enums\\CdnProvider::requiredFields() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Enums/CdnProvider.php',
       'line' => 72,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Enums/CdnProvider.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 72,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/CleanupTask.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Enums\\CleanupTask::recommendedForScheduled() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Enums/CleanupTask.php',
       'line' => 82,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Enums/CleanupTask.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 82,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Enums\\CleanupTask::all() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Enums/CleanupTask.php',
       'line' => 96,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Enums/CleanupTask.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 96,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Enums\\CleanupTask::byRiskLevel() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Enums/CleanupTask.php',
       'line' => 114,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Enums/CleanupTask.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 114,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/HostingPreset.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Enums\\HostingPreset::config() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Enums/HostingPreset.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Enums/HostingPreset.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 44,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Enums\\HostingPreset::all() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Enums/HostingPreset.php',
       'line' => 77,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Enums/HostingPreset.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 77,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/LogLevel.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Enums\\LogLevel::all() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Enums/LogLevel.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Enums/LogLevel.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 68,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\DatabaseCleanedEvent::getResults() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php',
       'line' => 26,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 26,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\DatabaseCleanedEvent::getScope() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 43,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/Event.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Events\\Event::$data type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'line' => 13,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 13,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\Event::__construct() has parameter $data with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'line' => 16,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 16,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\Event::getData() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'line' => 30,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 30,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\Event::get() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'line' => 38,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 38,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\Event::get() has parameter $default with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'line' => 38,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/Event.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 38,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/EventDispatcher.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Events\\EventDispatcher::$listeners type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'line' => 18,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Events\\EventDispatcher::$dispatched type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'line' => 19,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 19,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\EventDispatcher::getDispatched() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'line' => 118,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 118,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Events\\EventDispatcher::getListeners() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'line' => 137,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 137,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Health/HealthCheck.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::addTests() has parameter $tests with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 33,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 33,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::addTests() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 33,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 33,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::testPageCache() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 61,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 61,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::testWebPCoverage() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 120,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 120,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::testDatabaseHealth() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 207,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 207,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::testAssetOptimization() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 275,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 275,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::addDebugInfo() has parameter $info with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 347,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 347,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::addDebugInfo() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 347,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 347,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Health\\HealthCheck::errorResult() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'line' => 405,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 405,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Http/Routes.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::permissionCheck() has parameter $request with generic class WP_REST_Request but does not specify its types: T',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 133,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 133,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::logsTail() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 151,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 151,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::logsTail() has parameter $request with generic class WP_REST_Request but does not specify its types: T',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 151,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 151,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::debugToggle() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 167,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 167,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::debugToggle() has parameter $request with generic class WP_REST_Request but does not specify its types: T',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 167,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 167,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::presetApply() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 179,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::presetApply() has parameter $request with generic class WP_REST_Request but does not specify its types: T',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 179,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::presetRollback() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 190,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 190,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::dbCleanup() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 200,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 200,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Http\\Routes::dbCleanup() has parameter $request with generic class WP_REST_Request but does not specify its types: T',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 200,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 200,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.generics',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 208,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 208,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'line' => 232,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Http/Routes.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 232,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Monitoring\\QueryMonitor::$metrics type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 18,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor::addCollector() has parameter $collectors with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 36,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor::addCollector() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 36,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor::addOutputter() has parameter $output with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 46,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 46,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor::addOutputter() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 46,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 46,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $collectors of method FP\\PerfSuite\\Monitoring\\QueryMonitor::addOutputter() has invalid type QM_Collectors.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 46,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 46,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to static method get() on an unknown class QM_Collectors.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 50,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 50,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor::track() has parameter $value with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 60,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 60,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor::getMetrics() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'line' => 88,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 88,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class FP\\PerfSuite\\Monitoring\\QueryMonitor\\Collector extends unknown class QM_Collector.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'line' => 13,
       'canBeIgnored' => false,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 13,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Class_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Monitoring\\QueryMonitor\\Collector::$id has no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 15,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.property',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property FP\\PerfSuite\\Monitoring\\QueryMonitor\\Collector::$data.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'line' => 31,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 31,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property FP\\PerfSuite\\Monitoring\\QueryMonitor\\Collector::$data.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 68,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Class FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output extends unknown class QM_Output_Html.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 8,
       'canBeIgnored' => false,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 8,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Class_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter $collector of method FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::__construct() has invalid type QM_Collector.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 10,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 10,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::__construct() calls parent::__construct() but FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output does not extend any class.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 12,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 12,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'class.noParent',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::$collector.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 23,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 23,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::adminMenu() has parameter $menu with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 149,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 149,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::adminMenu() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 149,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 149,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::$collector.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 151,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 151,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to an undefined property FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::$collector.',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>',
       'nodeLine' => 160,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'property.notFound',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to an undefined method FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output::menu().',
       'file' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 160,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'method.notFound',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Plugin.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_VERSION not found.',
       'file' => '/workspace/fp-performance-suite/src/Plugin.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Plugin.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 49,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_FILE not found.',
       'file' => '/workspace/fp-performance-suite/src/Plugin.php',
       'line' => 57,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Plugin.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 57,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_DIR not found.',
       'file' => '/workspace/fp-performance-suite/src/Plugin.php',
       'line' => 94,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Plugin.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 94,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Constant FP_PERF_SUITE_FILE not found.',
       'file' => '/workspace/fp-performance-suite/src/Plugin.php',
       'line' => 226,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Plugin.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 226,
       'nodeType' => 'PhpParser\\Node\\Expr\\ConstFetch',
       'identifier' => 'constant.notFound',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Plugin.php',
       'line' => 235,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Plugin.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 235,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Repositories\\TransientRepository::get() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php',
       'line' => 27,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 27,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Repositories\\TransientRepository::get() has parameter $default with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php',
       'line' => 27,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 27,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Repositories\\WpOptionsRepository::all() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
       'line' => 102,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 102,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Repositories\\WpOptionsRepository::bulkSet() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
       'line' => 130,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 130,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Repositories\\WpOptionsRepository::getByPattern() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
       'line' => 145,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 145,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ServiceContainer.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\ServiceContainer::$settingsCache type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'line' => 13,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 13,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Template type T of method FP\\PerfSuite\\ServiceContainer::get() is not referenced in a parameter.',
       'file' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'line' => 29,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 29,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'method.templateTypeNotInParameter',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ServiceContainer::getCachedSettings() has parameter $defaults with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ServiceContainer::getCachedSettings() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ServiceContainer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property _WP_Dependency::$deps (array<string>) on left side of ?? is not nullable.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php',
       'line' => 254,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 254,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.property',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Else branch is unreachable because ternary operator condition is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php',
       'line' => 255,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 255,
       'nodeType' => 'PhpParser\\Node\\Expr\\Ternary',
       'identifier' => 'ternary.elseUnreachable',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Ternary operator condition is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 90,
       'nodeType' => 'PhpParser\\Node\\Expr\\Ternary',
       'identifier' => 'ternary.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $queue in empty() always exists and is not falsy.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PhpParser\\Node\\Expr\\Empty_',
       'identifier' => 'empty.variable',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property _WP_Dependency::$deps (array<string>) on left side of ?? is not nullable.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
       'line' => 132,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 132,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.property',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Ternary operator condition is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
       'line' => 97,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 97,
       'nodeType' => 'PhpParser\\Node\\Expr\\Ternary',
       'identifier' => 'ternary.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $queue in empty() always exists and is not falsy.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
       'line' => 98,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 98,
       'nodeType' => 'PhpParser\\Node\\Expr\\Empty_',
       'identifier' => 'empty.variable',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property _WP_Dependency::$deps (array<string>) on left side of ?? is not nullable.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
       'line' => 143,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 143,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.property',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\CriticalCss::update() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'line' => 80,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 80,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\CriticalCss::generate() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'line' => 136,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 136,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\CriticalCss::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'line' => 311,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 311,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 151,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 151,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::dnsPrefetch() has parameter $hints with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 216,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 216,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::dnsPrefetch() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 216,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 216,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::preloadResources() has parameter $hints with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 224,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 224,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::preloadResources() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 224,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 224,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::heartbeatSettings() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 232,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 232,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::heartbeatSettings() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 232,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 232,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 238,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 238,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Assets\\Optimizer::resolveFlag() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'line' => 303,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 303,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Action callback returns bool but should not return anything.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 42,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 42,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'return.void',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Action callback returns bool but should not return anything.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 43,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'return.void',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::settings() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 49,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 68,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 85,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 85,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::rewriteUrl() has parameter $id with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 96,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 96,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::rewriteSrcset() has parameter $attachment_id with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 138,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::rewriteSrcset() has parameter $image_meta with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 138,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::rewriteSrcset() has parameter $image_src with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 138,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::rewriteSrcset() has parameter $size_array with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 138,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::rewriteSrcset() has parameter $sources with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 138,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::rewriteSrcset() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 138,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::selectCdnDomain() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 170,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 170,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 212,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 212,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::purgeCloudflare() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 247,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 247,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $e of static method FP\\PerfSuite\\Utils\\Logger::error() expects Throwable|null, WP_Error given.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 266,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 266,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::purgeBunnyCdn() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 277,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 277,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #2 $e of static method FP\\PerfSuite\\Utils\\Logger::error() expects Throwable|null, WP_Error given.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 295,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 295,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::testConnection() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 305,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 305,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 346,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 346,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::sanitizeDomains() has parameter $domains with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 362,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 362,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::sanitizeDomains() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 362,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 362,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::sanitizeExtensions() has parameter $extensions with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 374,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 374,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\CDN\\CdnManager::sanitizeExtensions() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'line' => 374,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 374,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Cache/Headers.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Used function wp_cache_get_cookies_values not found.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'line' => 12,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 12,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Use_',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Cache\\Headers::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'line' => 138,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 138,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Cache\\Headers::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'line' => 166,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 166,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Cache\\Headers::normalizeHtaccess() has parameter $rules with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'line' => 202,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 202,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Used function wp_cache_get_cookies_values not found.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'line' => 11,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 11,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Use_',
       'identifier' => 'function.notFound',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Services\\Cache\\PageCache::$env is never read, only written.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'line' => 19,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/developing-extensions/always-read-written-properties',
       'nodeLine' => 14,
       'nodeType' => 'PHPStan\\Node\\ClassPropertiesNode',
       'identifier' => 'property.onlyWritten',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Cache\\PageCache::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'line' => 70,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 70,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Comparison operation ">" between int<1, max> and 0 is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'line' => 140,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 140,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Greater',
       'identifier' => 'greater.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Cache\\PageCache::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'line' => 300,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 300,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Comparison operation ">" between int<1, max> and 0 is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'line' => 335,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 335,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Greater',
       'identifier' => 'greater.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 73,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 73,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::registerSchedules() has parameter $schedules with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 89,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 89,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::registerSchedules() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 89,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 89,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'schedule\' on array{schedule: string, batch: int} on left side of ?? always exists and is not nullable.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 118,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 118,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 164,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 164,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 203,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 203,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::cleanupPosts() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 219,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 219,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::cleanupComments() has parameter $statuses with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 240,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 240,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::cleanupComments() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 240,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 240,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::cleanupTransients() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 259,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 259,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::cleanupMeta() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 300,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 300,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::optimizeTables() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 315,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 315,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 346,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 346,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\DB\\Cleaner::normalizeSchedule() has parameter $schedule with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'line' => 357,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 357,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Services\\Logs\\DebugToggler::$env is never read, only written.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/developing-extensions/always-read-written-properties',
       'nodeLine' => 11,
       'nodeType' => 'PHPStan\\Node\\ClassPropertiesNode',
       'identifier' => 'property.onlyWritten',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Logs\\DebugToggler::status() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 23,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 23,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to function is_string() with bool will always evaluate to false.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 28,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.impossibleType',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Result of && is always false.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 28,
       'nodeType' => 'PHPStan\\Node\\BooleanAndNode',
       'identifier' => 'booleanAnd.alwaysFalse',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Logs\\DebugToggler::determineLogValue() has parameter $parsed with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 137,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 137,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to function is_string() with bool will always evaluate to false.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 157,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 157,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'function.impossibleType',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Result of && is always false.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 157,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 157,
       'nodeType' => 'PHPStan\\Node\\BooleanAndNode',
       'identifier' => 'booleanAnd.alwaysFalse',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Result of && is always false.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 157,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 157,
       'nodeType' => 'PHPStan\\Node\\BooleanAndNode',
       'identifier' => 'booleanAnd.alwaysFalse',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Logs\\DebugToggler::parseConstant() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 181,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 181,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset 1 on array{string, string} on left side of ?? always exists and is not nullable.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'line' => 195,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 195,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Logs/RealtimeLog.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Logs\\RealtimeLog::tail() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Logs/RealtimeLog.php',
       'line' => 15,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Logs/RealtimeLog.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 15,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor::shouldForceConversion() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php',
       'line' => 90,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 90,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'If condition is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php',
       'line' => 185,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 185,
       'nodeType' => 'PhpParser\\Node\\Stmt\\If_',
       'identifier' => 'if.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $image of function imagepalettetotruecolor expects GdImage, resource given.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 173,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 173,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $image of function imagealphablending expects GdImage, resource given.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 179,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 179,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $image of function imagesavealpha expects GdImage, resource given.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 182,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 182,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $image of function imagewebp expects GdImage, resource given.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 186,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 186,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Parameter #1 $image of function imagedestroy expects GdImage, resource given.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 187,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 187,
       'nodeType' => 'PhpParser\\Node\\Expr\\FuncCall',
       'identifier' => 'argument.type',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter::createImageResource() never returns resource so it can be removed from the return type.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 203,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 203,
       'nodeType' => 'PHPStan\\Node\\MethodReturnStatementsNode',
       'identifier' => 'return.unusedType',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter::createImageResource() should return resource|null but returns GdImage|null.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 210,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 210,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter::createImageResource() should return resource|null but returns GdImage|null.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'line' => 216,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 216,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Return_',
       'identifier' => 'return.type',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'filename\' on array{dirname?: string, basename: string, extension?: string, filename: string} on left side of ?? always exists and is not nullable.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php',
       'line' => 28,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 28,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Offset \'filename\' on array{dirname?: string, basename: string, extension?: string, filename: string} on left side of ?? always exists and is not nullable.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php',
       'line' => 42,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 42,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Coalesce',
       'identifier' => 'nullCoalesce.offset',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Services\\Media\\WebPConverter::$fs is never read, only written.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
       'line' => 37,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/developing-extensions/always-read-written-properties',
       'nodeLine' => 32,
       'nodeType' => 'PHPStan\\Node\\ClassPropertiesNode',
       'identifier' => 'property.onlyWritten',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Media\\WebPConverter::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
       'line' => 95,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 95,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::$currentPageMetrics type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 21,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 21,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::settings() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 64,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 64,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 80,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 80,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 93,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 93,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::storeMetric() has parameter $metrics with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 140,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 140,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::track() has parameter $value with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 162,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 162,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::getStats() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 196,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 196,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::getRecent() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 242,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 242,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor::getTrends() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 306,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 306,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Comparison operation ">" between int<1, max> and 0 is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 346,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 346,
       'nodeType' => 'PhpParser\\Node\\Expr\\BinaryOp\\Greater',
       'identifier' => 'greater.alwaysTrue',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Else branch is unreachable because ternary operator condition is always true.',
       'file' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'line' => 346,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
       'traitFilePath' => NULL,
       'tip' => 'Because the type is coming from a PHPDoc, you can turn off this check by setting <fg=cyan>treatPhpDocTypesAsCertain: false</> in your <fg=cyan>%configurationFile%</>.',
       'nodeLine' => 346,
       'nodeType' => 'PhpParser\\Node\\Expr\\Ternary',
       'identifier' => 'ternary.elseUnreachable',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Presets/Manager.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Services\\Presets\\Manager::$debugToggler is never read, only written.',
       'file' => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
       'line' => 22,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/developing-extensions/always-read-written-properties',
       'nodeLine' => 13,
       'nodeType' => 'PHPStan\\Node\\ClassPropertiesNode',
       'identifier' => 'property.onlyWritten',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Presets\\Manager::apply() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
       'line' => 76,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 76,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::addSchedules() has parameter $schedules with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 36,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::addSchedules() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 36,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 36,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::settings() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 54,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 54,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::update() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 71,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 91,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 91,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 117,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 117,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 161,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 161,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::generateReport() has parameter $settings with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 180,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 180,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::generateReport() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 180,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 180,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::renderEmailTemplate() has parameter $data with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 222,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 222,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Reports\\ScheduledReports::sendTestReport() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'line' => 381,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 381,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Score/Scorer.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::activeOptimizations() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 134,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 134,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::gzipScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 158,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 158,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::browserCacheScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 212,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 212,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::pageCacheScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 221,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 221,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::assetsScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 229,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 229,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::webpScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 247,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 247,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::databaseScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 260,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 260,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::heartbeatScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 272,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 272,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::emojiScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 285,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 285,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::criticalCssScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 296,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 296,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Services\\Score\\Scorer::logScore() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'line' => 309,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 309,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::get() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 18,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::get() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 18,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::get() has parameter $default with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 18,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 18,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::set() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 44,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::set() has parameter $value with no type specified.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 44,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 44,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.parameter',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::only() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 71,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::only() has parameter $keys with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 71,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::only() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 71,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 71,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::except() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 79,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 79,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::except() has parameter $keys with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 79,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 79,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::except() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 79,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 79,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::pluck() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 87,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 87,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::pluck() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 87,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 87,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::flatten() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 108,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 108,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::flatten() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 108,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 108,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::groupBy() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 128,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 128,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::groupBy() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 128,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 128,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::isAssoc() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 148,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 148,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::mergeRecursive() has parameter $arrays with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 160,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::mergeRecursive() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 160,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 160,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::sortBy() has parameter $array with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 180,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 180,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\ArrayHelper::sortBy() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'line' => 180,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 180,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Benchmark.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Utils\\Benchmark::$timers type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'line' => 13,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 13,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\Utils\\Benchmark::$counters type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'line' => 14,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 14,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\Benchmark::measure() has no return type specified.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'line' => 50,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 50,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.return',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\Benchmark::get() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'line' => 62,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 62,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\Benchmark::getAll() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'line' => 70,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 70,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Htaccess.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
       'line' => 45,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 45,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
       'line' => 108,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 108,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Static method FP\\PerfSuite\\Utils\\Logger::info() invoked with 2 parameters, 1 required.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
       'line' => 136,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 136,
       'nodeType' => 'PhpParser\\Node\\Expr\\StaticCall',
       'identifier' => 'arguments.count',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Logger.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\Utils\\Logger::debug() has parameter $context with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/Utils/Logger.php',
       'line' => 74,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/Utils/Logger.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 74,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\CacheSettings::fromArray() has parameter $data with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php',
       'line' => 47,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 47,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\CacheSettings::toArray() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php',
       'line' => 58,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 58,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\ValueObjects\\PerformanceScore::$breakdown type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'line' => 20,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 20,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Property FP\\PerfSuite\\ValueObjects\\PerformanceScore::$suggestions type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'line' => 21,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 21,
       'nodeType' => 'PHPStan\\Node\\ClassPropertyNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\PerformanceScore::__construct() has parameter $breakdown with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'line' => 23,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 23,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\PerformanceScore::__construct() has parameter $suggestions with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'line' => 23,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 23,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\PerformanceScore::fromArray() has parameter $data with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'line' => 39,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 39,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\PerformanceScore::toArray() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'line' => 51,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 51,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\WebPSettings::fromArray() has parameter $data with no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php',
       'line' => 43,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 43,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Method FP\\PerfSuite\\ValueObjects\\WebPSettings::toArray() return type has no value type specified in iterable type array.',
       'file' => '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php',
       'line' => 56,
       'canBeIgnored' => true,
       'filePath' => '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php',
       'traitFilePath' => NULL,
       'tip' => 'See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type',
       'nodeLine' => 56,
       'nodeType' => 'PHPStan\\Node\\InClassMethodNode',
       'identifier' => 'missingType.iterableValue',
       'metadata' => 
      array (
      ),
    )),
  ),
); },
	'locallyIgnoredErrorsCallback' => static function (): array { return array (
); },
	'linesToIgnore' => array (
),
	'unmatchedLineIgnores' => array (
),
	'collectedDataCallback' => static function (): array { return array (
); },
	'dependencies' => array (
  '/workspace/fp-performance-suite/src/Admin/Assets.php' => 
  array (
    'fileHash' => '7c4739dc911a9f3c26dd7f71386a09db86c7a4a9',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Menu.php' => 
  array (
    'fileHash' => '80e414b3c848ecfeea657f7952d60bad10b8803d',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/AbstractPage.php' => 
  array (
    'fileHash' => '52f4c40ea9dd3e1da6469c56041f033f6288b7c0',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
      2 => '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php',
      3 => '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php',
      4 => '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php',
      5 => '/workspace/fp-performance-suite/src/Admin/Pages/Database.php',
      6 => '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php',
      7 => '/workspace/fp-performance-suite/src/Admin/Pages/Media.php',
      8 => '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php',
      9 => '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php',
      10 => '/workspace/fp-performance-suite/src/Admin/Pages/Settings.php',
      11 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php' => 
  array (
    'fileHash' => '39c81c2a63d836f124473193a6434c4101566daf',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php' => 
  array (
    'fileHash' => 'afd86d53b4ab6c2774e1e613bb7631df57e0c11f',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php' => 
  array (
    'fileHash' => '305002d34d0fdf6a2a002a7a625a6e418406044d',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php' => 
  array (
    'fileHash' => 'a042fdf0021e521722363fb452b257f59a5fb540',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Database.php' => 
  array (
    'fileHash' => 'be07563122943d2eed54e4c9c44a34261f40bff6',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php' => 
  array (
    'fileHash' => '112fe7a515cca4999fb6769b9463d0c42779a8a8',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Media.php' => 
  array (
    'fileHash' => '53317d5614e1a40c70ecd5fea74dd847503f080e',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php' => 
  array (
    'fileHash' => '21d57990aa9f320a2020e1dfca1caa4c22715fdd',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php' => 
  array (
    'fileHash' => '6fabf478f97a14e03c88a50e70d39174cab3a0c6',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Settings.php' => 
  array (
    'fileHash' => '1e34dbee6e8689b09378d9ee9396d19e87296c91',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php' => 
  array (
    'fileHash' => 'f28432bf97f642084381c49686fad2a18af8d9f7',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Cli/Commands.php' => 
  array (
    'fileHash' => 'c5540085174c7a7bf73c1faacd84a8e22cd90cf7',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php' => 
  array (
    'fileHash' => 'e585e2576b2b4e02a9e424e44c40bbe24e6599d0',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
      2 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      3 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      4 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
      5 => '/workspace/fp-performance-suite/src/Plugin.php',
      6 => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
      7 => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
      8 => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Contracts/LoggerInterface.php' => 
  array (
    'fileHash' => '78adf06b85c3afb52d49a8f71a426f5ddabd7f7f',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php' => 
  array (
    'fileHash' => '4ca39f50de90f6181f954c53e43d5fb9da248b4a',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Contracts/SettingsRepositoryInterface.php' => 
  array (
    'fileHash' => '57464896877a3f6d45e012b4d0b6d904c52dbc2f',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Enums/CacheType.php' => 
  array (
    'fileHash' => '55f4e1ff6bb89d295d9c1227f36f943056a8d913',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Enums/CdnProvider.php' => 
  array (
    'fileHash' => '5e488e48040951525c212a39e09f755099c14905',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Enums/CleanupTask.php' => 
  array (
    'fileHash' => 'befd262073dd76c396a4dc663fc990b854d5400d',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Enums/HostingPreset.php' => 
  array (
    'fileHash' => 'b680a31b4fb67eee80a41559958217ed1f691f55',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Enums/LogLevel.php' => 
  array (
    'fileHash' => '1b1edc45220f66105c1b537aa793768569324d67',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Events/CacheClearedEvent.php' => 
  array (
    'fileHash' => '30ccead9f442e7824aa582b14b01a11a144e0203',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php' => 
  array (
    'fileHash' => 'fa4636b01f1e5aa51704a63d5b2d945f304a78fb',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Events/Event.php' => 
  array (
    'fileHash' => 'a43303e69badedb17ecbe7eec31711143c50c00e',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Events/CacheClearedEvent.php',
      1 => '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php',
      2 => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
      3 => '/workspace/fp-performance-suite/src/Events/WebPConvertedEvent.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Events/EventDispatcher.php' => 
  array (
    'fileHash' => 'e37ca5adf1fe81a2142446a4e7e6f82bc895d3c8',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Events/WebPConvertedEvent.php' => 
  array (
    'fileHash' => '7f569b47109ac0e2d91ddf3cf7a444942fdf4d40',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Health/HealthCheck.php' => 
  array (
    'fileHash' => '61ccd3d4b25f144f49b4f094998e29f112f6ac8e',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Http/Routes.php' => 
  array (
    'fileHash' => 'a64c5049ff9a69c217b3e47d6d89c097cacdcdb5',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php' => 
  array (
    'fileHash' => '4708c2eb37d75ba9d877bf1c4720f5a855310a5d',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
      1 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php' => 
  array (
    'fileHash' => '37594a4bf91f01a1e95141f99e4f8f8c30c6a664',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php' => 
  array (
    'fileHash' => 'a235d80fe36901ec7cafdb926b7c7286d1161269',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Plugin.php' => 
  array (
    'fileHash' => 'ed0a3b215bf5aacc4f319699cf5e248f00b1e843',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      1 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      2 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
      3 => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
      4 => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php' => 
  array (
    'fileHash' => '9abe0d57528d0f87b222850e69527d7995167916',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php' => 
  array (
    'fileHash' => '7b6258a4eefa18970274c2582aea2353ac683a88',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/ServiceContainer.php' => 
  array (
    'fileHash' => '332e47ddf8ed55bd69405c2085f8ba02d337049b',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/AbstractPage.php',
      2 => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
      3 => '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php',
      4 => '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php',
      5 => '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php',
      6 => '/workspace/fp-performance-suite/src/Admin/Pages/Database.php',
      7 => '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php',
      8 => '/workspace/fp-performance-suite/src/Admin/Pages/Media.php',
      9 => '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php',
      10 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
      11 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      12 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      13 => '/workspace/fp-performance-suite/src/Http/Routes.php',
      14 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
      15 => '/workspace/fp-performance-suite/src/Plugin.php',
      16 => '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php',
      17 => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php' => 
  array (
    'fileHash' => 'a5e706856d2417aec33bca37a07f2740a755b499',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
      1 => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
      2 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php' => 
  array (
    'fileHash' => '52635e9cec8d9390277bbf0853055e9a71a59363',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/DependencyResolver.php' => 
  array (
    'fileHash' => 'a1c6caccc4c86cb22b1ecd0337a056f3d4b3e2c5',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php',
      2 => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php',
      3 => '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php',
      4 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php' => 
  array (
    'fileHash' => '55c441daab9146f6fff83de10d611212add2e2f4',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php' => 
  array (
    'fileHash' => '1975be5e6c08c7b31c470fc6abb32ffbdcdc4eec',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
      1 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/HtmlMinifier.php' => 
  array (
    'fileHash' => '5a8df0213af1264f6b821ad8dc49211a02badda3',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php' => 
  array (
    'fileHash' => 'ee4d1a1305f80c7e2d44b57c65fed756fc2f8518',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
      2 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      3 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
      4 => '/workspace/fp-performance-suite/src/Plugin.php',
      5 => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
      6 => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/ResourceHints/ResourceHintsManager.php' => 
  array (
    'fileHash' => 'e0db6ecc4577373a3b77eede7c476a9f5a20439d',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/ScriptOptimizer.php' => 
  array (
    'fileHash' => '852d6cf030c46a561be55d809c9a2ab693baf7a1',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/WordPressOptimizer.php' => 
  array (
    'fileHash' => '806948030a75a242057d6a25af0d551b34238843',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php' => 
  array (
    'fileHash' => '85e33e86488ce3d857a78064dda02da7c9098b2a',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
      1 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Cache/Headers.php' => 
  array (
    'fileHash' => 'd020b51cb666c0dc99d742a7c1b2d13de111aec3',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
      2 => '/workspace/fp-performance-suite/src/Plugin.php',
      3 => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
      4 => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php' => 
  array (
    'fileHash' => 'f359767d1a8bd66bc1d111d29aa4b4f5e341abe6',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
      2 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      3 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      4 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
      5 => '/workspace/fp-performance-suite/src/Plugin.php',
      6 => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
      7 => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php' => 
  array (
    'fileHash' => 'e1dcf884a99e4abccf3d86864c25e8547271db0c',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Database.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
      2 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      3 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      4 => '/workspace/fp-performance-suite/src/Http/Routes.php',
      5 => '/workspace/fp-performance-suite/src/Plugin.php',
      6 => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
      7 => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php' => 
  array (
    'fileHash' => 'ebb9b2f92d7588d7eeb0f678e14068e75c3d44ee',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php',
      1 => '/workspace/fp-performance-suite/src/Http/Routes.php',
      2 => '/workspace/fp-performance-suite/src/Plugin.php',
      3 => '/workspace/fp-performance-suite/src/Services/Logs/RealtimeLog.php',
      4 => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
      5 => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Logs/RealtimeLog.php' => 
  array (
    'fileHash' => '65733e4ae72b64c0198e92268234fd3b34d51df3',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Http/Routes.php',
      1 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php' => 
  array (
    'fileHash' => '515b9f5479dbc3cf82fd092527dd4f08225b85f9',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPBatchProcessor.php',
      2 => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPBatchProcessor.php' => 
  array (
    'fileHash' => '7dd389c481ed0f60fdf05488193f28efd86acc29',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php' => 
  array (
    'fileHash' => 'd19d332fee22951fd9dadff4c4c3b863bbe36e1a',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php',
      2 => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php' => 
  array (
    'fileHash' => 'd111307cc324c95bcf2fb6afb848a3fd3f09d591',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php',
      2 => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPQueue.php' => 
  array (
    'fileHash' => '45a2c5bc03b4825d0708ac0329135501bb2cffc7',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPBatchProcessor.php',
      2 => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php' => 
  array (
    'fileHash' => '9de22d29e4e750bf630218dfcbeaef232519c3a3',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Media.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php',
      2 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      3 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      4 => '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php',
      5 => '/workspace/fp-performance-suite/src/Plugin.php',
      6 => '/workspace/fp-performance-suite/src/Services/Presets/Manager.php',
      7 => '/workspace/fp-performance-suite/src/Services/Score/Scorer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php' => 
  array (
    'fileHash' => 'faba06148ec26ebc514e515220f98949779a4601',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php',
      2 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Presets/Manager.php' => 
  array (
    'fileHash' => '8f7e84b5249ef4b09a41d9ec0cd3ff2eafdbc622',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php',
      2 => '/workspace/fp-performance-suite/src/Http/Routes.php',
      3 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php' => 
  array (
    'fileHash' => '7c60b210adfa8f7f0e97cc92770993eb89a24b19',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php',
      1 => '/workspace/fp-performance-suite/src/Plugin.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Services/Score/Scorer.php' => 
  array (
    'fileHash' => 'ad28f4c9f68e658abf4a27a592939d4714f1fc19',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php',
      1 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      2 => '/workspace/fp-performance-suite/src/Http/Routes.php',
      3 => '/workspace/fp-performance-suite/src/Plugin.php',
      4 => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php' => 
  array (
    'fileHash' => '706dc965bdd3fcb7d4dcdd55ef70b4d1d2980c58',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/Benchmark.php' => 
  array (
    'fileHash' => '0482bc36a8c1121600457108952adb6a1fc3781b',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/Capabilities.php' => 
  array (
    'fileHash' => '61451de22194e3158b08ccca0df96754e285fabd',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Admin/Menu.php',
      1 => '/workspace/fp-performance-suite/src/Admin/Pages/AbstractPage.php',
      2 => '/workspace/fp-performance-suite/src/Http/Routes.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/Env.php' => 
  array (
    'fileHash' => '2887eed4c4edd5d9918e9aceb83437514d46906b',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
      2 => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
      3 => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
      4 => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/Fs.php' => 
  array (
    'fileHash' => '9d570d09438a10f75b9751ef7d64c40dd3f53a08',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
      2 => '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php',
      3 => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
      4 => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/Htaccess.php' => 
  array (
    'fileHash' => '604edef03af37e77dd1f9ff753005d99e17ca30d',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Cache/Headers.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/Logger.php' => 
  array (
    'fileHash' => '526c058f44ac01dafabdff3457fec4805b551d46',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Cli/Commands.php',
      1 => '/workspace/fp-performance-suite/src/Events/EventDispatcher.php',
      2 => '/workspace/fp-performance-suite/src/Health/HealthCheck.php',
      3 => '/workspace/fp-performance-suite/src/Http/Routes.php',
      4 => '/workspace/fp-performance-suite/src/Plugin.php',
      5 => '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php',
      6 => '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php',
      7 => '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php',
      8 => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
      9 => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php',
      10 => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPQueue.php',
      11 => '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php',
      12 => '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php',
      13 => '/workspace/fp-performance-suite/src/Utils/Benchmark.php',
      14 => '/workspace/fp-performance-suite/src/Utils/Htaccess.php',
      15 => '/workspace/fp-performance-suite/src/Utils/RateLimiter.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/RateLimiter.php' => 
  array (
    'fileHash' => '713d63ef9a0b6ddb9ccf880709b6d3df279a2450',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php',
      2 => '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPQueue.php',
      3 => '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/Utils/Semaphore.php' => 
  array (
    'fileHash' => 'f19228c98b8786da3b995b999a9a727275b932b4',
    'dependentFiles' => 
    array (
      0 => '/workspace/fp-performance-suite/src/Plugin.php',
      1 => '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php',
    ),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php' => 
  array (
    'fileHash' => '978670bdaf9b759709ad2e7752aa50e8a1bd61c2',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php' => 
  array (
    'fileHash' => '9b73c37a2576171cc0837a6be99159168181e63c',
    'dependentFiles' => 
    array (
    ),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php' => 
  array (
    'fileHash' => 'e7c929c87123e43c58521e594abd17f0a378828f',
    'dependentFiles' => 
    array (
    ),
  ),
),
	'exportedNodesCallback' => static function (): array { return array (
  '/workspace/fp-performance-suite/src/Admin/Assets.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Assets',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'boot',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'enqueue',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hook',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Menu.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Menu',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'container',
               'type' => 'FP\\PerfSuite\\ServiceContainer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'boot',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/AbstractPage.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'phpDoc' => NULL,
       'abstract' => true,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'container',
          ),
           'phpDoc' => NULL,
           'type' => 'FP\\PerfSuite\\ServiceContainer',
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'container',
               'type' => 'FP\\PerfSuite\\ServiceContainer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'render',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, mixed>
     */',
             'namespace' => 'FP\\PerfSuite\\Admin\\Pages',
             'uses' => 
            array (
              'servicecontainer' => 'FP\\PerfSuite\\ServiceContainer',
              'capabilities' => 'FP\\PerfSuite\\Utils\\Capabilities',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'requiredCapability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Advanced',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Advanced Features Admin Page
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Admin\\Pages',
         'uses' => 
        array (
          'servicecontainer' => 'FP\\PerfSuite\\ServiceContainer',
          'criticalcss' => 'FP\\PerfSuite\\Services\\Assets\\CriticalCss',
          'cdnmanager' => 'FP\\PerfSuite\\Services\\CDN\\CdnManager',
          'performancemonitor' => 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor',
          'scheduledreports' => 'FP\\PerfSuite\\Services\\Reports\\ScheduledReports',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'container',
               'type' => 'FP\\PerfSuite\\ServiceContainer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'handleSave',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Assets',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Cache',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Dashboard',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'container',
               'type' => 'FP\\PerfSuite\\ServiceContainer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'exportCsv',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Database.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Database',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Logs',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Media.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Media',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Performance',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Performance Metrics Dashboard
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Admin\\Pages',
         'uses' => 
        array (
          'servicecontainer' => 'FP\\PerfSuite\\ServiceContainer',
          'performancemonitor' => 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor',
          'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Presets',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Settings.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Settings',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Admin\\Pages\\Tools',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Admin\\Pages\\AbstractPage',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'slug',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'title',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'capability',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'view',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'data',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'content',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'normalizeBrowserCacheImport',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,headers:array{Cache-Control:string},expires_ttl:int,htaccess:string}
     */',
             'namespace' => 'FP\\PerfSuite\\Admin\\Pages',
             'uses' => 
            array (
              'optimizer' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
              'headers' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
              'filter_validate_int' => 'FILTER_VALIDATE_INT',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'incoming',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'defaults',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'normalizePageCacheImport',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,ttl:int}
     */',
             'namespace' => 'FP\\PerfSuite\\Admin\\Pages',
             'uses' => 
            array (
              'optimizer' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
              'headers' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
              'filter_validate_int' => 'FILTER_VALIDATE_INT',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'incoming',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'defaults',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'normalizeWebpImport',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,quality:int,keep_original:bool,lossy:bool}
     */',
             'namespace' => 'FP\\PerfSuite\\Admin\\Pages',
             'uses' => 
            array (
              'optimizer' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
              'headers' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
              'filter_validate_int' => 'FILTER_VALIDATE_INT',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'incoming',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'defaults',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'normalizeAssetSettingsImport',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */',
             'namespace' => 'FP\\PerfSuite\\Admin\\Pages',
             'uses' => 
            array (
              'optimizer' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
              'headers' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
              'filter_validate_int' => 'FILTER_VALIDATE_INT',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'incoming',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'defaults',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Cli/Commands.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Cli\\Commands',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WP-CLI commands for FP Performance Suite
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Cli',
         'uses' => 
        array (
          'plugin' => 'FP\\PerfSuite\\Plugin',
          'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
          'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
          'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
          'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear all caches
     *
     * ## EXAMPLES
     *
     *     # Clear page cache
     *     wp fp-performance cache clear
     *
     * @when after_wp_load
     */',
             'namespace' => 'FP\\PerfSuite\\Cli',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'assoc_args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'db',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Database cleanup operations
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Run in dry-run mode (no actual deletions)
     *
     * [--scope=<scope>]
     * : Comma-separated list of cleanup tasks
     * ---
     * default: revisions,auto_drafts,trash_posts,spam_comments,expired_transients
     * ---
     *
     * ## EXAMPLES
     *
     *     # Dry run cleanup
     *     wp fp-performance db cleanup --dry-run
     *
     *     # Actually cleanup revisions only
     *     wp fp-performance db cleanup --scope=revisions
     *
     * @when after_wp_load
     */',
             'namespace' => 'FP\\PerfSuite\\Cli',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'assoc_args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'webp',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * WebP conversion operations
     *
     * ## OPTIONS
     *
     * [--limit=<limit>]
     * : Number of images to convert
     * ---
     * default: 20
     * ---
     *
     * ## EXAMPLES
     *
     *     # Convert 50 images to WebP
     *     wp fp-performance webp convert --limit=50
     *
     * @when after_wp_load
     */',
             'namespace' => 'FP\\PerfSuite\\Cli',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'assoc_args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'score',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Calculate performance score
     *
     * ## EXAMPLES
     *
     *     # Show performance score
     *     wp fp-performance score
     *
     * @when after_wp_load
     */',
             'namespace' => 'FP\\PerfSuite\\Cli',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'assoc_args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'info',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Plugin information and status
     *
     * ## EXAMPLES
     *
     *     # Show plugin info
     *     wp fp-performance info
     *
     * @when after_wp_load
     */',
             'namespace' => 'FP\\PerfSuite\\Cli',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'assoc_args',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedInterfaceNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Contracts\\CacheInterface',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Interface for cache implementations
 */',
         'namespace' => 'FP\\PerfSuite\\Contracts',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'extends' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isEnabled',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if cache is enabled
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get cache settings
     * 
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update cache settings
     * 
     * @param array $settings
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear all cache
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get cache status
     * 
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/LoggerInterface.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedInterfaceNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Contracts\\LoggerInterface',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Interface for logging implementations
 */',
         'namespace' => 'FP\\PerfSuite\\Contracts',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'extends' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'error',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log error message
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'e',
               'type' => '?Throwable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'warning',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log warning message
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'info',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log info message
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'debug',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log debug message
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'context',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedInterfaceNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Contracts\\OptimizerInterface',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Interface for asset optimization implementations
 */',
         'namespace' => 'FP\\PerfSuite\\Contracts',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'extends' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register optimizer hooks
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get optimizer settings
     * 
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update optimizer settings
     * 
     * @param array $settings
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get optimizer status
     * 
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Contracts/SettingsRepositoryInterface.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedInterfaceNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Interface for settings storage
 */',
         'namespace' => 'FP\\PerfSuite\\Contracts',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'extends' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get a setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'set',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Value to store
     * @return bool Success
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'has',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if setting exists
     * 
     * @param string $key Setting key
     * @return bool
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delete',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete a setting
     * 
     * @param string $key Setting key
     * @return bool Success
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'all',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all settings
     * 
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Contracts',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/CacheType.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Enums\\CacheType',
       'scalarType' => 'string',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Cache Type Enumeration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Enums',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'PAGE',
           'value' => '\'page\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'BROWSER',
           'value' => '\'browser\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'OBJECT',
           'value' => '\'object\'',
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'TRANSIENT',
           'value' => '\'transient\'',
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'ASSET',
           'value' => '\'asset\'',
           'phpDoc' => NULL,
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'label',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get human-readable label
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'description',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get description
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'icon',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get icon
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/CdnProvider.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Enums\\CdnProvider',
       'scalarType' => 'string',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * CDN Provider Enumeration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Enums',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'CUSTOM',
           'value' => '\'custom\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'CLOUDFLARE',
           'value' => '\'cloudflare\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'BUNNYCDN',
           'value' => '\'bunnycdn\'',
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'STACKPATH',
           'value' => '\'stackpath\'',
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'CLOUDFRONT',
           'value' => '\'cloudfront\'',
           'phpDoc' => NULL,
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'FASTLY',
           'value' => '\'fastly\'',
           'phpDoc' => NULL,
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'name',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get provider name
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setupUrl',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get setup URL
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'supportsApiPurge',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if supports API purge
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'requiresApiKey',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if requires API credentials
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'requiredFields',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get required fields for setup
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'icon',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get icon/logo
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/CleanupTask.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Enums\\CleanupTask',
       'scalarType' => 'string',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Database Cleanup Task Enumeration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Enums',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'REVISIONS',
           'value' => '\'revisions\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'AUTO_DRAFTS',
           'value' => '\'auto_drafts\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'TRASH_POSTS',
           'value' => '\'trash_posts\'',
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'SPAM_COMMENTS',
           'value' => '\'spam_comments\'',
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'EXPIRED_TRANSIENTS',
           'value' => '\'expired_transients\'',
           'phpDoc' => NULL,
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'ORPHAN_POSTMETA',
           'value' => '\'orphan_postmeta\'',
           'phpDoc' => NULL,
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'ORPHAN_TERMMETA',
           'value' => '\'orphan_termmeta\'',
           'phpDoc' => NULL,
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'ORPHAN_USERMETA',
           'value' => '\'orphan_usermeta\'',
           'phpDoc' => NULL,
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'OPTIMIZE_TABLES',
           'value' => '\'optimize_tables\'',
           'phpDoc' => NULL,
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'label',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get human-readable label
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'description',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get description
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'riskLevel',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get risk level
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isSafe',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if task is safe (low risk)
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'recommendedForScheduled',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get recommended for scheduled cleanup
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'all',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all tasks
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'byRiskLevel',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get tasks by risk level
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'level',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/HostingPreset.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Enums\\HostingPreset',
       'scalarType' => 'string',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Hosting Preset Enumeration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Enums',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'GENERAL',
           'value' => '\'generale\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'IONOS',
           'value' => '\'ionos\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'ARUBA',
           'value' => '\'aruba\'',
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'label',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get human-readable label
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'description',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get description
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'config',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get configuration array
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'all',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all available presets
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'fromString',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get preset from string
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => '?self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isRecommended',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if preset is recommended for current environment
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Enums/LogLevel.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedEnumNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Enums\\LogLevel',
       'scalarType' => 'string',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Log Level Enumeration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Enums',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'implements' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'ERROR',
           'value' => '\'ERROR\'',
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'WARNING',
           'value' => '\'WARNING\'',
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'INFO',
           'value' => '\'INFO\'',
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedEnumCaseNode::__set_state(array(
           'name' => 'DEBUG',
           'value' => '\'DEBUG\'',
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'priority',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get numeric priority (lower = more severe)
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'color',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get color for UI
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'emoji',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get emoji
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'shouldLog',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if this level should log
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'configuredLevel',
               'type' => 'FP\\PerfSuite\\Enums\\LogLevel',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'all',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all levels
     */',
             'namespace' => 'FP\\PerfSuite\\Enums',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/CacheClearedEvent.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Events\\CacheClearedEvent',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Event fired when cache is cleared
 */',
         'namespace' => 'FP\\PerfSuite\\Events',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Events\\Event',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'name',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getFilesDeleted',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get number of files deleted
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCacheType',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get cache type
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Events\\DatabaseCleanedEvent',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Event fired when database cleanup completes
 */',
         'namespace' => 'FP\\PerfSuite\\Events',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Events\\Event',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'name',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isDryRun',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if this was a dry run
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getResults',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get cleanup results
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTotalDeleted',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get total items deleted
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getScope',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get cleanup scope
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/Event.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Events\\Event',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Base Event class
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Events',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => true,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'data',
          ),
           'phpDoc' => NULL,
           'type' => 'array',
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'timestamp',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'name',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get event name
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getData',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get event data
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get specific data value
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'timestamp',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get event timestamp
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'shouldPropagate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if event should be propagated
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/EventDispatcher.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Events\\EventDispatcher',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Event Dispatcher
 * 
 * Central event dispatching system for the plugin
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Events',
         'uses' => 
        array (
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get singleton instance
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'dispatch',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Dispatch an event
     * 
     * @param Event $event Event to dispatch
     * @return Event The event (possibly modified by listeners)
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Events\\Event',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'event',
               'type' => 'FP\\PerfSuite\\Events\\Event',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'listen',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register event listener
     * 
     * @param string $eventName Event name to listen for
     * @param callable $listener Callback function
     * @param int $priority Priority (lower runs first)
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'eventName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'listener',
               'type' => 'callable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'priority',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'remove',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove event listener
     * 
     * @param string $eventName Event name
     * @param callable $listener Callback to remove
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'eventName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'listener',
               'type' => 'callable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getDispatched',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all dispatched events
     * 
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clearHistory',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear dispatched events history
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getListeners',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get listeners for an event
     * 
     * @param string $eventName Event name
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'eventName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Events/WebPConvertedEvent.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Events\\WebPConvertedEvent',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Event fired when image is converted to WebP
 */',
         'namespace' => 'FP\\PerfSuite\\Events',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Events\\Event',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'name',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getOriginalFile',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get original file path
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWebPFile',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get WebP file path
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getSizeReduction',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get size reduction percentage
     */',
             'namespace' => 'FP\\PerfSuite\\Events',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'float',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Health/HealthCheck.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Health\\HealthCheck',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WordPress Site Health integration
 * 
 * Adds FP Performance Suite checks to WordPress Site Health
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Health',
         'uses' => 
        array (
          'plugin' => 'FP\\PerfSuite\\Plugin',
          'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
          'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
          'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register health checks
     */',
             'namespace' => 'FP\\PerfSuite\\Health',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addTests',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add health check tests
     */',
             'namespace' => 'FP\\PerfSuite\\Health',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'tests',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'testPageCache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Test: Page Cache Status
     */',
             'namespace' => 'FP\\PerfSuite\\Health',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'testWebPCoverage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Test: WebP Coverage
     */',
             'namespace' => 'FP\\PerfSuite\\Health',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'testDatabaseHealth',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Test: Database Health
     */',
             'namespace' => 'FP\\PerfSuite\\Health',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'testAssetOptimization',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Test: Asset Optimization
     */',
             'namespace' => 'FP\\PerfSuite\\Health',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addDebugInfo',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add debug information to Site Health Info tab
     */',
             'namespace' => 'FP\\PerfSuite\\Health',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'info',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Http/Routes.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Http\\Routes',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'container',
               'type' => 'FP\\PerfSuite\\ServiceContainer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'boot',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'permissionCheck',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'WP_REST_Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logsTail',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'WP_REST_Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'debugToggle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'WP_REST_Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'presetApply',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'WP_REST_Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'presetRollback',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'dbCleanup',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'request',
               'type' => 'WP_REST_Request',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'score',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'WP_REST_Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'progress',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'WP_REST_Response',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Monitoring\\QueryMonitor',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Query Monitor Integration
 * 
 * Adds FP Performance Suite metrics to Query Monitor plugin
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Monitoring',
         'uses' => 
        array (
          'plugin' => 'FP\\PerfSuite\\Plugin',
          'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register integration if Query Monitor is active
     */',
             'namespace' => 'FP\\PerfSuite\\Monitoring',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addCollector',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add FP Performance collector
     */',
             'namespace' => 'FP\\PerfSuite\\Monitoring',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'collectors',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addOutputter',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add FP Performance outputter
     */',
             'namespace' => 'FP\\PerfSuite\\Monitoring',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'output',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'collectors',
               'type' => 'QM_Collectors',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'track',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Track metric
     */',
             'namespace' => 'FP\\PerfSuite\\Monitoring',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'startTimer',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Start timing
     */',
             'namespace' => 'FP\\PerfSuite\\Monitoring',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'stopTimer',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Stop timing
     */',
             'namespace' => 'FP\\PerfSuite\\Monitoring',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getMetrics',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all metrics
     */',
             'namespace' => 'FP\\PerfSuite\\Monitoring',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Monitoring\\QueryMonitor\\Collector',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Query Monitor Collector for FP Performance Suite
 */',
         'namespace' => 'FP\\PerfSuite\\Monitoring\\QueryMonitor',
         'uses' => 
        array (
          'plugin' => 'FP\\PerfSuite\\Plugin',
          'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
          'optimizer' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
          'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'QM_Collector',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'id',
          ),
           'phpDoc' => NULL,
           'type' => NULL,
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'name',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'process',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Monitoring\\QueryMonitor\\Output',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Query Monitor Output for FP Performance Suite
 */',
         'namespace' => 'FP\\PerfSuite\\Monitoring\\QueryMonitor',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'QM_Output_Html',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'collector',
               'type' => 'QM_Collector',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'name',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'output',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'adminMenu',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'menu',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Plugin.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Plugin',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'init',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'container',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'FP\\PerfSuite\\ServiceContainer',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'onActivate',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'onDeactivate',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Repositories\\TransientRepository',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Transient-based temporary storage repository
 * 
 * For caching and temporary data
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Repositories',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'prefix',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'defaultExpiration',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get transient value
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'set',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set transient value
     * 
     * @param string $key Transient key
     * @param mixed $value Value to store
     * @param int|null $expiration Expiration in seconds (null uses default)
     * @return bool Success
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'expiration',
               'type' => '?int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'has',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if transient exists and is not expired
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delete',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete transient
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'remember',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remember value with callback
     * 
     * Get from cache or generate and cache result
     * 
     * @param string $key Cache key
     * @param callable $callback Function to generate value if not cached
     * @param int|null $expiration Expiration in seconds
     * @return mixed Cached or generated value
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'callback',
               'type' => 'callable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'expiration',
               'type' => '?int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'increment',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Increment numeric value
     * 
     * @param string $key Transient key
     * @param int $amount Amount to increment by
     * @return int New value
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'decrement',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Decrement numeric value
     * 
     * @param string $key Transient key
     * @param int $amount Amount to decrement by
     * @return int New value
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear all transients with this prefix
     * 
     * @return int Number of transients deleted
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Repositories\\WpOptionsRepository',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WordPress Options-based settings repository
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Repositories',
         'uses' => 
        array (
          'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
          'plugin' => 'FP\\PerfSuite\\Plugin',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
        0 => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'prefix',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get setting value
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
              'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
              'plugin' => 'FP\\PerfSuite\\Plugin',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'set',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set setting value
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
              'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
              'plugin' => 'FP\\PerfSuite\\Plugin',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'has',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if setting exists
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
              'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
              'plugin' => 'FP\\PerfSuite\\Plugin',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delete',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete setting
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
              'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
              'plugin' => 'FP\\PerfSuite\\Plugin',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'all',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all settings with this prefix
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
              'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
              'plugin' => 'FP\\PerfSuite\\Plugin',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'bulkSet',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Bulk set settings
     * 
     * @param array $settings Array of key => value pairs
     * @return bool Success
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
              'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
              'plugin' => 'FP\\PerfSuite\\Plugin',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getByPattern',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get settings matching pattern
     * 
     * @param string $pattern Wildcard pattern (e.g., \'cache_*\')
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Repositories',
             'uses' => 
            array (
              'settingsrepositoryinterface' => 'FP\\PerfSuite\\Contracts\\SettingsRepositoryInterface',
              'plugin' => 'FP\\PerfSuite\\Plugin',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pattern',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ServiceContainer.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\ServiceContainer',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'set',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param string $id
     * @param callable $factory
     */',
             'namespace' => 'FP\\PerfSuite',
             'uses' => 
            array (
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'factory',
               'type' => 'callable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @template T
     * @param class-string<T>|string $id
     * @return T|mixed
     */',
             'namespace' => 'FP\\PerfSuite',
             'uses' => 
            array (
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'has',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCachedSettings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get cached settings to reduce database queries
     * 
     * @param string $optionName WordPress option name
     * @param array $defaults Default values
     * @return array Parsed settings
     */',
             'namespace' => 'FP\\PerfSuite',
             'uses' => 
            array (
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'optionName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'defaults',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'invalidateSettingsCache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Invalidate settings cache after update
     * 
     * @param string $optionName WordPress option name to invalidate
     */',
             'namespace' => 'FP\\PerfSuite',
             'uses' => 
            array (
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'optionName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clearSettingsCache',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear all settings cache
     */',
             'namespace' => 'FP\\PerfSuite',
             'uses' => 
            array (
              'runtimeexception' => 'RuntimeException',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\AssetCombinerBase',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Base Asset Combiner
 * 
 * Provides common functionality for CSS and JS combiners
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
          'lock_ex' => 'LOCK_EX',
          'php_url_host' => 'PHP_URL_HOST',
          'php_url_path' => 'PHP_URL_PATH',
          'php_url_scheme' => 'PHP_URL_SCHEME',
        ),
      )),
       'abstract' => true,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'dependencyResolver',
          ),
           'phpDoc' => NULL,
           'type' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
           'public' => false,
           'private' => false,
           'static' => false,
           'readonly' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'dependencyResolver',
               'type' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getExtension',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get asset type extension
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'lock_ex' => 'LOCK_EX',
              'php_url_host' => 'PHP_URL_HOST',
              'php_url_path' => 'PHP_URL_PATH',
              'php_url_scheme' => 'PHP_URL_SCHEME',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getType',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get asset type identifier
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'lock_ex' => 'LOCK_EX',
              'php_url_host' => 'PHP_URL_HOST',
              'php_url_path' => 'PHP_URL_PATH',
              'php_url_scheme' => 'PHP_URL_SCHEME',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isDependencyCombinable',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if dependency item is combinable
     * 
     * @param object $item Dependency item
     * @return bool
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'lock_ex' => 'LOCK_EX',
              'php_url_host' => 'PHP_URL_HOST',
              'php_url_path' => 'PHP_URL_PATH',
              'php_url_scheme' => 'PHP_URL_SCHEME',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'item',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resolveDependencySource',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Resolve dependency source to local file path
     * 
     * @param \\WP_Dependencies $collection
     * @param object $item
     * @return array{path:string,url:string}|null
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'lock_ex' => 'LOCK_EX',
              'php_url_host' => 'PHP_URL_HOST',
              'php_url_path' => 'PHP_URL_PATH',
              'php_url_scheme' => 'PHP_URL_SCHEME',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'collection',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'item',
               'type' => 'object',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'writeCombinedAsset',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Write combined asset to file
     * 
     * @param array<int,array{handle:string,path:string,url:string}> $files
     * @return array{handles:array<int,string>,url:string}|null
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'lock_ex' => 'LOCK_EX',
              'php_url_host' => 'PHP_URL_HOST',
              'php_url_path' => 'PHP_URL_PATH',
              'php_url_scheme' => 'PHP_URL_SCHEME',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'files',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'replaceDependencies',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Replace dependencies after combination
     * 
     * @param \\WP_Dependencies $collection
     * @param array<int, string> $replacedHandles
     * @param string $replacement
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'lock_ex' => 'LOCK_EX',
              'php_url_host' => 'PHP_URL_HOST',
              'php_url_path' => 'PHP_URL_PATH',
              'php_url_scheme' => 'PHP_URL_SCHEME',
            ),
          )),
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'collection',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'replacedHandles',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'replacement',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * CSS Asset Combiner
 * 
 * Combines multiple CSS files into a single file to reduce HTTP requests
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\AssetCombinerBase',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getExtension',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getType',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'combine',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Combine CSS styles
     * 
     * @return bool True if combination was successful
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isCombined',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if styles have been combined
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'reset',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Reset combination state
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/DependencyResolver.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Dependency Resolver for Asset Combination
 * 
 * Performs topological sort on dependencies to determine safe combination order
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'resolveDependencies',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Resolve dependencies using topological sort
     * 
     * @param array<string, array{handle:string,path:string,url:string,deps:array<int,string>}> $candidates
     * @param array<string, int> $positions Original queue positions
     * @return array<int, string>|null Ordered handles or null if circular dependency
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'candidates',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'positions',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'filterExternalDependencies',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Filter candidates that have external dependencies
     * 
     * @param array<string, array{handle:string,path:string,url:string,deps:array<int,string>}> $candidates
     * @param array<string, bool> $queueLookup Map of all queued handles
     * @return array<string, array{handle:string,path:string,url:string,deps:array<int,string>}>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'candidates',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'queueLookup',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'normalizeDependencies',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Normalize dependencies array
     * 
     * @param mixed $depsProperty
     * @return array<int, string>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'depsProperty',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * JavaScript Asset Combiner
 * 
 * Combines multiple JS files into a single file to reduce HTTP requests
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\AssetCombinerBase',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getExtension',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getType',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'combine',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Combine JavaScript files
     * 
     * @param bool $footer Whether to combine footer scripts
     * @return bool True if combination was successful
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'footer',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isCombined',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if scripts have been combined
     * 
     * @param bool|null $footer Check specific location, or null for any
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'footer',
               'type' => '?bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'reset',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Reset combination state
     * 
     * @param bool|null $footer Reset specific location, or null for all
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\Combiners',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'footer',
               'type' => '?bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\CriticalCss',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Critical CSS management service
 * 
 * Allows administrators to define critical CSS that should be inlined
 * for above-the-fold content optimization.
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets',
         'uses' => 
        array (
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register hooks
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isEnabled',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if critical CSS is enabled and available
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get stored critical CSS
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update critical CSS
     * 
     * @param string $css The critical CSS to store
     * @return array Result with success/error
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'css',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear critical CSS
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'inlineCriticalCss',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Inline critical CSS in head
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate critical CSS from current page (basic implementation)
     * 
     * This is a placeholder for more advanced implementations using
     * services like critical.css, penthouse, or puppeteer
     * 
     * @param string $url URL to analyze
     * @return array Result with CSS or error
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'url',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get status information
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/HtmlMinifier.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\HtmlMinifier',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * HTML Minification Service
 * 
 * Removes unnecessary whitespace, newlines, and tabs from HTML output
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'startBuffer',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Start output buffering for HTML minification
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'endBuffer',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * End output buffering
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'minify',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Minify HTML content
     * 
     * @param string $html HTML content to minify
     * @return string Minified HTML
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'html',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isBuffering',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if buffering has started
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Asset Optimization Orchestrator
 * 
 * Coordinates various asset optimization strategies including minification,
 * combination, defer/async loading, and resource hints.
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets',
         'uses' => 
        array (
          'csscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
          'dependencyresolver' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
          'jscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
          'resourcehintsmanager' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
          'semaphore' => 'FP\\PerfSuite\\Utils\\Semaphore',
        ),
         'constUses' => 
        array (
          'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
          'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'semaphore',
               'type' => 'FP\\PerfSuite\\Utils\\Semaphore',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'htmlMinifier',
               'type' => '?FP\\PerfSuite\\Services\\Assets\\HtmlMinifier',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'scriptOptimizer',
               'type' => '?FP\\PerfSuite\\Services\\Assets\\ScriptOptimizer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'wpOptimizer',
               'type' => '?FP\\PerfSuite\\Services\\Assets\\WordPressOptimizer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'resourceHints',
               'type' => '?FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'dependencyResolver',
               'type' => '?FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get current settings
     * 
     * @return array{
     *  minify_html:bool,
     *  defer_js:bool,
     *  async_js:bool,
     *  remove_emojis:bool,
     *  dns_prefetch:array<int,string>,
     *  preload:array<int,string>,
     *  heartbeat_admin:int,
     *  combine_css:bool,
     *  combine_js:bool
     * }
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'csscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
              'dependencyresolver' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
              'jscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
              'resourcehintsmanager' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
              'semaphore' => 'FP\\PerfSuite\\Utils\\Semaphore',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'applyCombination',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'startBuffer',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'endBuffer',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'minifyHtml',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @deprecated Use HtmlMinifier::minify() directly
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'csscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
              'dependencyresolver' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
              'jscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
              'resourcehintsmanager' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
              'semaphore' => 'FP\\PerfSuite\\Utils\\Semaphore',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'html',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'filterScriptTag',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'tag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'handle',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'src',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'dnsPrefetch',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @deprecated Use ResourceHintsManager directly
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'csscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
              'dependencyresolver' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
              'jscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
              'resourcehintsmanager' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
              'semaphore' => 'FP\\PerfSuite\\Utils\\Semaphore',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hints',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'relation',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'preloadResources',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @deprecated Use ResourceHintsManager directly
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'csscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
              'dependencyresolver' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
              'jscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
              'resourcehintsmanager' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
              'semaphore' => 'FP\\PerfSuite\\Utils\\Semaphore',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hints',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'relation',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'heartbeatSettings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @deprecated Use WordPressOptimizer directly
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'csscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
              'dependencyresolver' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
              'jscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
              'resourcehintsmanager' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
              'semaphore' => 'FP\\PerfSuite\\Utils\\Semaphore',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'riskLevels',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<int, array<string, string>>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
              'csscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
              'dependencyresolver' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\DependencyResolver',
              'jscombiner' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
              'resourcehintsmanager' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
              'semaphore' => 'FP\\PerfSuite\\Utils\\Semaphore',
            ),
             'constUses' => 
            array (
              'filter_null_on_failure' => 'FILTER_NULL_ON_FAILURE',
              'filter_validate_boolean' => 'FILTER_VALIDATE_BOOLEAN',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getHtmlMinifier',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Assets\\HtmlMinifier',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getScriptOptimizer',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Assets\\ScriptOptimizer',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        16 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWordPressOptimizer',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Assets\\WordPressOptimizer',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        17 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getResourceHintsManager',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        18 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCssCombiner',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\CssCombiner',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        19 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getJsCombiner',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Assets\\Combiners\\JsCombiner',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/ResourceHints/ResourceHintsManager.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints\\ResourceHintsManager',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Resource Hints Manager
 * 
 * Manages DNS prefetch and preload resource hints for better performance
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
          'pathinfo_extension' => 'PATHINFO_EXTENSION',
          'php_url_path' => 'PHP_URL_PATH',
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addDnsPrefetch',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add DNS prefetch hint
     * 
     * @param array<int, mixed> $hints Current hints
     * @param string $relation Relation type
     * @return array<int, mixed> Modified hints
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'pathinfo_extension' => 'PATHINFO_EXTENSION',
              'php_url_path' => 'PHP_URL_PATH',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hints',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'relation',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addPreloadHints',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add preload resource hints
     * 
     * @param array<int, mixed> $hints Current hints
     * @param string $relation Relation type
     * @return array<int, mixed> Modified hints
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'pathinfo_extension' => 'PATHINFO_EXTENSION',
              'php_url_path' => 'PHP_URL_PATH',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hints',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'relation',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setDnsPrefetchUrls',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set DNS prefetch URLs
     * 
     * @param array<int, string>|string $urls URLs to prefetch
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'pathinfo_extension' => 'PATHINFO_EXTENSION',
              'php_url_path' => 'PHP_URL_PATH',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'urls',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setPreloadUrls',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set preload URLs
     * 
     * @param array<int, string>|string $urls URLs to preload
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets\\ResourceHints',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
              'pathinfo_extension' => 'PATHINFO_EXTENSION',
              'php_url_path' => 'PHP_URL_PATH',
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'urls',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/ScriptOptimizer.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\ScriptOptimizer',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * JavaScript Script Tag Optimizer
 * 
 * Adds defer and async attributes to script tags for better loading performance
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'filterScriptTag',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Filter script tag to add defer/async attributes
     * 
     * @param string $tag Original script tag
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @param bool $defer Whether to add defer attribute
     * @param bool $async Whether to add async attribute
     * @return string Modified script tag
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'tag',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'handle',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'src',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'defer',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'async',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setSkipHandles',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set custom skip handles
     * 
     * @param array<int, string> $handles
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'handles',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getSkipHandles',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get current skip handles
     * 
     * @return array<int, string>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Assets/WordPressOptimizer.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Assets\\WordPressOptimizer',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WordPress Core Optimizations
 * 
 * Handles WordPress-specific optimizations like removing emoji scripts
 * and controlling the heartbeat API
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Assets',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'disableEmojis',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Disable WordPress emoji scripts and styles
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'configureHeartbeat',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Configure WordPress heartbeat interval
     * 
     * @param array<string, mixed> $settings Current heartbeat settings
     * @param int $interval Desired interval in seconds
     * @return array<string, mixed> Modified settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'interval',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'registerHeartbeat',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register heartbeat filter with given interval
     * 
     * @param int $interval Heartbeat interval in seconds
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Assets',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'interval',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\CDN\\CdnManager',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * CDN Integration Manager
 * 
 * Rewrites asset URLs to use CDN and provides purge functionality
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\CDN',
         'uses' => 
        array (
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register CDN hooks
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get CDN settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update CDN settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rewriteUrl',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Rewrite URL to CDN
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'url',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rewriteSrcset',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Rewrite srcset URLs to CDN
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'sources',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'size_array',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'image_src',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'image_meta',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'attachment_id',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rewriteContentUrls',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Rewrite URLs in post content
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'content',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'purgeAll',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Purge all CDN cache
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'purgeFile',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Purge specific file from CDN
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'testConnection',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Test CDN connection
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get CDN status
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\CDN',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Cache/Headers.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'htaccess',
               'type' => 'FP\\PerfSuite\\Utils\\Htaccess',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'env',
               'type' => 'FP\\PerfSuite\\Utils\\Env',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array{
     *     enabled:bool,
     *     headers:array<string,string>,
     *     expires_ttl:int,
     *     htaccess:string
     * }
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Cache',
             'uses' => 
            array (
              'env' => 'FP\\PerfSuite\\Utils\\Env',
              'htaccess' => 'FP\\PerfSuite\\Utils\\Htaccess',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'applyHtaccess',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'rules',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
        0 => 'FP\\PerfSuite\\Contracts\\CacheInterface',
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'fs',
               'type' => 'FP\\PerfSuite\\Utils\\Fs',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'env',
               'type' => 'FP\\PerfSuite\\Utils\\Env',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isEnabled',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array{enabled:bool,ttl:int}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Cache',
             'uses' => 
            array (
              'cacheinterface' => 'FP\\PerfSuite\\Contracts\\CacheInterface',
              'env' => 'FP\\PerfSuite\\Utils\\Env',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cacheDir',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'maybeServeCache',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'startBuffering',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'maybeFilterOutput',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'buffer',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'saveBuffer',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'CRON_HOOK',
               'value' => '\'fp_ps_db_cleanup\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'env',
               'type' => 'FP\\PerfSuite\\Utils\\Env',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'rateLimiter',
               'type' => '?FP\\PerfSuite\\Utils\\RateLimiter',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'primeSchedules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array{schedule:string,batch:int}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\DB',
             'uses' => 
            array (
              'env' => 'FP\\PerfSuite\\Utils\\Env',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wpdb' => 'wpdb',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'registerSchedules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param array $schedules
     * @return array
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\DB',
             'uses' => 
            array (
              'env' => 'FP\\PerfSuite\\Utils\\Env',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wpdb' => 'wpdb',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'schedules',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'reschedule',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param mixed $old
     * @param mixed $value
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\DB',
             'uses' => 
            array (
              'env' => 'FP\\PerfSuite\\Utils\\Env',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wpdb' => 'wpdb',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'old',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'maybeSchedule',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'force',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'runScheduledCleanup',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cleanup',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param array<int,string> $scope
     * @return array<string, mixed>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\DB',
             'uses' => 
            array (
              'env' => 'FP\\PerfSuite\\Utils\\Env',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wpdb' => 'wpdb',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'scope',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'dryRun',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'batch',
               'type' => '?int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'overhead',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'float',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Logs\\DebugToggler',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'fs',
               'type' => 'FP\\PerfSuite\\Utils\\Fs',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'env',
               'type' => 'FP\\PerfSuite\\Utils\\Env',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toggle',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'enabled',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'log',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'backup',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'config',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'contents',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'revertLatest',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Logs/RealtimeLog.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Logs\\RealtimeLog',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'MAX_LINES',
               'value' => '1000',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'toggler',
               'type' => 'FP\\PerfSuite\\Services\\Logs\\DebugToggler',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'tail',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'lines',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'level',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'query',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WebP Attachment Processor
 * 
 * Processes individual WordPress attachments and their sizes for WebP conversion
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'converter',
               'type' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pathHelper',
               'type' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'process',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Process attachment for WebP conversion
     * 
     * @param int $attachmentId WordPress attachment ID
     * @param array<string,mixed> $metadata Attachment metadata
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings Conversion settings
     * @return array{metadata:array<string,mixed>,converted:bool}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'attachmentId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'metadata',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPBatchProcessor.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WebP Batch Processing Engine
 * 
 * Processes batches of images for WebP conversion via cron
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'queue',
               'type' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'attachmentProcessor',
               'type' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'processBatch',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Process queued batch
     * 
     * @param array{quality:int,lossy:bool,keep_original:bool} $settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setChunkSize',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set chunk size for testing
     * 
     * @param int $size
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'size',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WebP Image Conversion Engine
 * 
 * Handles the actual image conversion to WebP format using Imagick or GD
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
         'uses' => 
        array (
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'convert',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Convert an image file to WebP format
     * 
     * @param string $sourceFile Source image path
     * @param string $targetFile Target WebP path
     * @param array{quality:int,lossy:bool} $settings Conversion settings
     * @param bool $force Force reconversion even if target exists
     * @return bool True if conversion was successful
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'sourceFile',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'targetFile',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'force',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isConvertible',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if file can be converted to WebP
     * 
     * @param string $file File path
     * @return bool
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getSupportedExtensions',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get supported file extensions
     * 
     * @return array<int, string>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WebP Path Helper Utilities
 * 
 * Helper functions for WebP file path manipulation
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getWebPPath',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get WebP path for a given file
     * 
     * @param string $file Original file path
     * @return string WebP file path
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'withWebPExtension',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Convert file path to WebP extension
     * 
     * @param string $file Original file path
     * @return string Path with .webp extension
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'safeFilesize',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get file size safely
     * 
     * @param string $file File path
     * @return int|null File size in bytes or null on failure
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPQueue.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WebP Conversion Queue Manager
 * 
 * Manages the queue for bulk WebP conversions
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
         'uses' => 
        array (
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
          'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
          'wp_query' => 'WP_Query',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'rateLimiter',
               'type' => '?FP\\PerfSuite\\Utils\\RateLimiter',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'initializeBulkConversion',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Initialize bulk conversion queue
     * 
     * @param int $limit Maximum number of images to convert
     * @param int $offset Starting offset
     * @return array{converted:int,total:int,queued:bool,error?:string}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'limit',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'offset',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getState',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get current queue state
     * 
     * @return array{limit:int,offset:int,processed:int,converted:int,total:int}|null
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateState',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update queue state
     * 
     * @param array{limit?:int,offset?:int,processed?:int,converted?:int,total?:int} $updates
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'updates',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clear',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear queue
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'scheduleBatch',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Schedule next batch processing
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getNextBatch',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get attachment IDs for next batch
     * 
     * @param int $batchSize Number of images to process
     * @param int $batchOffset Starting offset for this batch
     * @return array<int, int> Attachment IDs
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'batchSize',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'batchOffset',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isActive',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if queue is active
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCronHook',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get cron hook name
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media\\WebP',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
              'wp_query' => 'WP_Query',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WebP Conversion Orchestrator
 * 
 * Coordinates WebP conversion modules for automatic and bulk conversions
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Media',
         'uses' => 
        array (
          'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
          'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
          'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
          'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
          'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
          'fs' => 'FP\\PerfSuite\\Utils\\Fs',
          'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'fs',
               'type' => 'FP\\PerfSuite\\Utils\\Fs',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'rateLimiter',
               'type' => '?FP\\PerfSuite\\Utils\\RateLimiter',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'imageConverter',
               'type' => '?FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'queue',
               'type' => '?FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'attachmentProcessor',
               'type' => '?FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'batchProcessor',
               'type' => '?FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            6 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pathHelper',
               'type' => '?FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get current settings
     * 
     * @return array{enabled:bool,quality:int,keep_original:bool,lossy:bool}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media',
             'uses' => 
            array (
              'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
              'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
              'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
              'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
              'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'generateWebp',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate WebP on attachment upload/update
     * 
     * @param array<string, mixed> $metadata
     * @param int $attachment_id
     * @return array<string, mixed>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media',
             'uses' => 
            array (
              'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
              'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
              'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
              'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
              'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'metadata',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'attachment_id',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'convert',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Convert single file to WebP
     * 
     * @param string $file File path
     * @param array<string, mixed> $settings Conversion settings
     * @param bool $force Force reconversion
     * @return bool True if conversion was successful
     * 
     * @deprecated Use WebPImageConverter::convert() directly
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media',
             'uses' => 
            array (
              'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
              'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
              'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
              'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
              'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'force',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'bulkConvert',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Start bulk conversion
     * 
     * @param int $limit Maximum number of images
     * @param int $offset Starting offset
     * @return array{converted:int,total:int,queued:bool,error?:string}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media',
             'uses' => 
            array (
              'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
              'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
              'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
              'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
              'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'limit',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'offset',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'runQueue',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Process queued batch (called by cron)
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media',
             'uses' => 
            array (
              'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
              'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
              'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
              'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
              'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'coverage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Calculate WebP coverage percentage
     * 
     * @return float Percentage of images converted to WebP
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media',
             'uses' => 
            array (
              'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
              'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
              'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
              'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
              'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'float',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'status',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get conversion status
     * 
     * @return array{enabled:bool,quality:int,coverage:float}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Media',
             'uses' => 
            array (
              'webpattachmentprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
              'webpbatchprocessor' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
              'webpimageconverter' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
              'webppathhelper' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
              'webpqueue' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
              'fs' => 'FP\\PerfSuite\\Utils\\Fs',
              'ratelimiter' => 'FP\\PerfSuite\\Utils\\RateLimiter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getImageConverter',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPImageConverter',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getQueue',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPQueue',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getBatchProcessor',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPBatchProcessor',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getAttachmentProcessor',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPAttachmentProcessor',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getPathHelper',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'FP\\PerfSuite\\Services\\Media\\WebP\\WebPPathHelper',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Performance Monitoring Service
 * 
 * Tracks and stores performance metrics over time
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
         'uses' => 
        array (
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'instance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get singleton instance
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register monitoring hooks
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isEnabled',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if monitoring is enabled
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get monitoring settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update monitoring settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'recordPageLoad',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Record page load metrics
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'track',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Track custom metric for current page
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'startTimer',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Start timing an operation
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'stopTimer',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Stop timing an operation
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'float',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStats',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get performance statistics
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'days',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getRecent',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get recent metrics
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'limit',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clearMetrics',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear all stored metrics
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'injectTimingScript',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Inject client-side timing script
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTrends',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get performance trends
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Monitoring',
             'uses' => 
            array (
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'days',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Presets/Manager.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Presets\\Manager',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pageCache',
               'type' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'headers',
               'type' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'optimizer',
               'type' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'webp',
               'type' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'cleaner',
               'type' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'debugToggler',
               'type' => 'FP\\PerfSuite\\Services\\Logs\\DebugToggler',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'presets',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array<string, array<string, mixed>>
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Presets',
             'uses' => 
            array (
              'optimizer' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
              'headers' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'debugtoggler' => 'FP\\PerfSuite\\Services\\Logs\\DebugToggler',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'apply',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'rollback',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getActivePreset',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'labelFor',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'id',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Reports\\ScheduledReports',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Scheduled Performance Reports
 * 
 * Sends periodic email reports about site performance
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Services\\Reports',
         'uses' => 
        array (
          'plugin' => 'FP\\PerfSuite\\Plugin',
          'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
          'logger' => 'FP\\PerfSuite\\Utils\\Logger',
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register hooks
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Reports',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'addSchedules',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Add custom cron schedules
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Reports',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'schedules',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'settings',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Reports',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'update',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update settings
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Reports',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'settings',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'maybeSchedule',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Schedule reports if enabled
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Reports',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'force',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'sendReport',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Send performance report
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Reports',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'sendTestReport',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Send test report
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Reports',
             'uses' => 
            array (
              'plugin' => 'FP\\PerfSuite\\Plugin',
              'scorer' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
              'logger' => 'FP\\PerfSuite\\Utils\\Logger',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'recipient',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Services/Score/Scorer.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Services\\Score\\Scorer',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pageCache',
               'type' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'headers',
               'type' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'optimizer',
               'type' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'webp',
               'type' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'cleaner',
               'type' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            5 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'debugToggler',
               'type' => 'FP\\PerfSuite\\Services\\Logs\\DebugToggler',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'calculate',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @return array{total:int,breakdown:array<string,int>,suggestions:array<int,string>}
     */',
             'namespace' => 'FP\\PerfSuite\\Services\\Score',
             'uses' => 
            array (
              'optimizer' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
              'headers' => 'FP\\PerfSuite\\Services\\Cache\\Headers',
              'pagecache' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
              'cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
              'debugtoggler' => 'FP\\PerfSuite\\Services\\Logs\\DebugToggler',
              'webpconverter' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'activeOptimizations',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\ArrayHelper',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Array manipulation helpers
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Utils',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get value from array using dot notation
     * 
     * @example get([\'user\' => [\'name\' => \'John\']], \'user.name\') => \'John\'
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'default',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'set',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set value in array using dot notation
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => true,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'value',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'only',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Filter array by keys
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'keys',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'except',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Remove keys from array
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'keys',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'pluck',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Pluck an array of values from array
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'column',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => '?string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'flatten',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Flatten multi-dimensional array
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'depth',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'groupBy',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Group array items by key
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isAssoc',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if array is associative
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'mergeRecursive',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Recursively merge arrays
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'arrays',
               'type' => 'array',
               'byRef' => false,
               'variadic' => true,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'sortBy',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Sort array by column
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'array',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'column',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'descending',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Benchmark.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\Benchmark',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Benchmarking utility for performance testing
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Utils',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'start',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Start a timer
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'stop',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Stop a timer and return duration
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => '?float',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'measure',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Measure a callable
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'callback',
               'type' => 'callable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'get',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get timer result
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => '?array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getAll',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all timers
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'increment',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Increment a counter
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'amount',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCounter',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get counter value
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'reset',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Reset all benchmarks
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'formatDuration',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Format duration for display
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'seconds',
               'type' => 'float',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'formatMemory',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Format memory for display
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'bytes',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'report',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Generate report
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logReport',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log report to error log
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Capabilities.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\Capabilities',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'required',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Env.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\Env',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isMultisite',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isCli',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'serverSoftware',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isApache',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Fs.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\Fs',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'putContents',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'contents',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getContents',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'exists',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'mkdir',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'path',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'delete',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'path',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'copy',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'source',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'destination',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'overwrite',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Htaccess.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\Htaccess',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'fs',
               'type' => 'FP\\PerfSuite\\Utils\\Fs',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isSupported',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'backup',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'file',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'injectRules',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'section',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'rules',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'removeSection',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'section',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'hasSection',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'section',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Logger.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\Logger',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Centralized logging utility for FP Performance Suite.
 * 
 * Provides consistent logging with context and severity levels.
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Utils',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'ERROR',
               'value' => '\'ERROR\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log levels
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'WARNING',
               'value' => '\'WARNING\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'INFO',
               'value' => '\'INFO\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'DEBUG',
               'value' => '\'DEBUG\'',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'error',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log an error message
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'e',
               'type' => '?Throwable',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'warning',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log a warning message
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'info',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log an informational message
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'debug',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Log a debug message with optional context
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'message',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'context',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'setLevel',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Set the minimum log level
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'level',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/RateLimiter.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\RateLimiter',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Rate limiting utility to prevent abuse of resource-intensive operations.
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\Utils',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isAllowed',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if an action is allowed within rate limits
     * 
     * @param string $action Unique action identifier
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $windowSeconds Time window in seconds
     * @return bool True if action is allowed, false otherwise
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'maxAttempts',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'windowSeconds',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'reset',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Reset rate limit for a specific action
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatus',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get current rate limit status
     * 
     * @return array{count: int, maxAttempts: int, remaining: int, resetAt: int}|null
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'action',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'maxAttempts',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'windowSeconds',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'clearAll',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clear all rate limit data
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/Utils/Semaphore.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\Utils\\Semaphore',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'describe',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * @param string $key
     * @param string $color
     * @param string $description
     * @return array<string, string>
     */',
             'namespace' => 'FP\\PerfSuite\\Utils',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'key',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'color',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'description',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\ValueObjects\\CacheSettings',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Cache Settings Value Object
 * 
 * Immutable object representing cache configuration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\ValueObjects',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => true,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'MIN_TTL',
               'value' => '60',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'MAX_TTL',
               'value' => '86400 * 30',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'DEFAULT_TTL',
               'value' => '3600',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'enabled',
          ),
           'phpDoc' => NULL,
           'type' => 'bool',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'ttl',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'enabled',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'ttl',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'fromArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create from array
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Convert to array
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'withEnabled',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create with updated enabled status
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'enabled',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'withTtl',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create with updated TTL
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'ttl',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isActive',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if cache is active (enabled with valid TTL)
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getTtlHumanReadable',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get TTL in human readable format
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\ValueObjects\\PerformanceScore',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Performance Score Value Object
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\ValueObjects',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => true,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'MAX_SCORE',
               'value' => '100',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'GRADE_A',
               'value' => '90',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'GRADE_B',
               'value' => '75',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'GRADE_C',
               'value' => '60',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'GRADE_D',
               'value' => '45',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'total',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'breakdown',
          ),
           'phpDoc' => NULL,
           'type' => 'array',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'suggestions',
          ),
           'phpDoc' => NULL,
           'type' => 'array',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'total',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'breakdown',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'suggestions',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'fromArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create from array
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Convert to array
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getGrade',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get letter grade
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getColor',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get color for score
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getStatusMessage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get status message
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        14 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isPassing',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if score is passing
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        15 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getEmoji',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get emoji representation
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        16 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getPercentage',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get percentage
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        17 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'compareTo',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Compare with another score
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'other',
               'type' => 'FP\\PerfSuite\\ValueObjects\\PerformanceScore',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        18 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isImprovedFrom',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if improved from another score
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'other',
               'type' => 'FP\\PerfSuite\\ValueObjects\\PerformanceScore',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        19 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getDelta',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get improvement delta
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'other',
               'type' => 'FP\\PerfSuite\\ValueObjects\\PerformanceScore',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'FP\\PerfSuite\\ValueObjects\\WebPSettings',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * WebP Settings Value Object
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */',
         'namespace' => 'FP\\PerfSuite\\ValueObjects',
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => true,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'MIN_QUALITY',
               'value' => '1',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'MAX_QUALITY',
               'value' => '100',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedClassConstantsNode::__set_state(array(
           'constants' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedClassConstantNode::__set_state(array(
               'name' => 'DEFAULT_QUALITY',
               'value' => '82',
               'attributes' => 
              array (
              ),
            )),
          ),
           'public' => true,
           'private' => false,
           'final' => false,
           'phpDoc' => NULL,
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'enabled',
          ),
           'phpDoc' => NULL,
           'type' => 'bool',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'quality',
          ),
           'phpDoc' => NULL,
           'type' => 'int',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'keepOriginal',
          ),
           'phpDoc' => NULL,
           'type' => 'bool',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedPropertiesNode::__set_state(array(
           'names' => 
          array (
            0 => 'lossy',
          ),
           'phpDoc' => NULL,
           'type' => 'bool',
           'public' => true,
           'private' => false,
           'static' => false,
           'readonly' => true,
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'enabled',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'quality',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'keepOriginal',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'lossy',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => true,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'fromArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create from array
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'data',
               'type' => 'array',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'toArray',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Convert to array
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'withQuality',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create with updated quality
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'quality',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'withEnabled',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create with updated enabled status
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'self',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'enabled',
               'type' => 'bool',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        12 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCompressionMode',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get compression mode description
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        13 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getQualityLevel',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get quality level description
     */',
             'namespace' => 'FP\\PerfSuite\\ValueObjects',
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
); },
];
