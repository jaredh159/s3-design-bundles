<?php

add_action('init', function () {
    $options = ['bucket', 'region', 'key', 'secret'];
    foreach ($options as $option) {
        $key = "prophoto_s3_bundler_$option";
        $current = get_site_option($key);
        if ($current === false) {
            update_site_option($key, '', false);
        }
    }
});
