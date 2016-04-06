<?php

use NetRivet\Container\Container;

add_action('pp_container_binding', function (Container $container) {
    require_once __DIR__ . '/../src/Design/S3Bundler.php';

    $container
        ->when('ProPhoto\DesignerPlugin\Middleware\ApiMiddleware')
        ->needs('ProPhoto\Core\Service\Design\BundlerInterface')
        ->give('ProPhoto\S3DesignBundles\Design\S3Bundler');
});
