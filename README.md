S3 Design Bundler
=================

Hook into the bundling process of a ProPhoto design and ship those assets to S3!!!

## Relevant options

These need to be set in order for things to work with computers

* prophoto_s3_bundler_bucket - bucket name where the things are going
* prophoto_s3_bundler_region - aws region of the bucket
* prophoto_s3_bundler_key - aws key for signing api requests
* prophoto_s3_bundler_secret - aws secret for signing api requests


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
