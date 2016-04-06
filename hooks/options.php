<?php

add_action('init', function () {
    $options = ['bucket', 'region', 'key', 'secret'];
    foreach ($options as $option) {
        $key = "prophoto_s3_bundler_$option";
        $current = get_option($key);
        if ($current === false) {
            update_option($key, '', false);
        }
    }
});
