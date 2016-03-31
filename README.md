S3 Design Bundler
=================

Hook into the bundling process of a ProPhoto design and ship those assets to S3!!!

## Options

These WordPress options need to be set. They are used for communicated with S3

* prophoto_s3_bundler_bucket - bucket name where the things are going
* prophoto_s3_bundler_region - aws region of the bucket
* prophoto_s3_bundler_key - aws key for signing api requests
* prophoto_s3_bundler_secret - aws secret for signing api requests

## Filters

There is one filter available for controlling the name of the root directory of the design when uploaded to the configured bucket.

```php
add_filter('prophoto_s3_bundle_name', function($name) {
    return $name . '-some-unique-id';
});
```

This filter may be useful for preventing collisions if multiple designs have the same name for whatever reason.

## Wiring up to the export process

The plugin auto wires into the design store export for ProPhoto. In order to wire it up to a local install, the `S3Bundler` needs to be bound to the standard `DesignExportResponder`:

```php
add_action('pp_container_binding', function($container) {
    $container
        ->when('ProPhoto\Core\Application\Responder\DesignExportResponder')
        ->needs('ProPhoto\Core\Service\Design\BundlerInterface')
        ->give('ProPhoto\S3DesignBundles\Design\S3Bundler');
});
```

Naturally, this will only work if the plugin is activated.
