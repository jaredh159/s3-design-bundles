<?php

namespace ProPhoto\S3DesignBundles\Design;

use Aws\S3\S3Client;
use GuzzleHttp\Promise\Promise;
use ProPhoto\Core\Model\Design\Bundle;
use ProPhoto\Infrastructure\Service\Design\Distribution\Bundler;

class S3Bundler extends Bundler
{
    /**
     * Export the bundle to a path and return it
     *
     * @param Bundle $bundle
     * @return string
     */
    public function bundle(Bundle $bundle)
    {
        $jsonFile = $this->prepare($bundle);

        if ($jsonFile === null) {
            return $bundle;
        }

        // create a zip out of just the json file
        $fileName = $this->getPath($bundle, 'zip');
        $zip = new \PclZip($fileName);
        $result = $zip->create([$jsonFile], PCLZIP_OPT_REMOVE_ALL_PATH);

        // return null if compression fails
        if ($result === 0) {
            return null;
        }

        return $fileName;
    }

    /**
     * Prepare the S3 bundle. This will compress the JSON and upload
     * valid assets to S3. Returns a path to the design json if S3 upload and json generation
     * are successful
     *
     * @param Bundle $bundle
     * @return string|null
     */
    protected function prepare(Bundle $bundle)
    {
        // return null if json file could not be created
        $jsonFile = $this->bundleData($bundle);
        if ($jsonFile === null) {
            return null;
        }

        // return null if ANY assets failed to upload to S3
        try {
            $promise = $this->upload($bundle);
            $promise->wait(); // wait until all uploads have finished
        } catch(\Exception $e) {
            return null;
        }

        return $jsonFile;
    }

    /**
     * Upload all assets to S3
     *
     * @param Bundle $bundle
     * @return Promise
     */
    protected function upload(Bundle $bundle)
    {
        $config = $this->getAwsConfig();
        $client = new S3Client($config);
        $bucket = get_site_option('prophoto_s3_bundler_bucket');
        $assetPromises  = $this->uploadAssets($client, $bundle, $bucket);
        $galleryPromises = $this->uploadGalleries($client, $bundle, $bucket);
        return \GuzzleHttp\Promise\all(array_merge($assetPromises, $galleryPromises));
    }

    /**
     * Upload all images from the bundle to S3
     *
     * @param S3Client $client
     * @param Bundle $bundle
     * @param string $bucket
     * @return Promise[]
     */
    protected function uploadAssets(S3Client $client, Bundle $bundle, $bucket)
    {
        $promises = [];
        $dir = $this->getRootDir($bundle);
        $images = $bundle->getImages();
        $fonts = $bundle->getFonts();
        $assets = array_merge($images, $fonts);
        foreach ($assets as $asset) {
            $promises[] = $client->putObjectAsync([
                'Bucket' => $bucket,
                'Key' => "$dir/" . basename($asset),
                'Body' => fopen($asset, 'r'),
                'ACL' => 'public-read'
            ]);
        }
        return $promises;
    }

    /**
     * Upload galleries from the bundle to S3
     *
     * @param S3Client $client
     * @param Bundle $bundle
     * @param $bucket
     * @return Promise[]
     */
    protected function uploadGalleries(S3Client $client, Bundle $bundle, $bucket)
    {
        $galleries = $bundle->getGalleries();
        if (empty($galleries)) {
            return [];
        }

        $promises = [];
        $dir = $this->getRootDir($bundle) . "/gallery";
        foreach ($galleries as $gallery) {
            $id = $gallery->getId();
            $folder = "$dir/$id";
            $images = $gallery->getImages();
            foreach ($images as $image) {
                $promises[] = $client->putObjectAsync([
                    'Bucket' => $bucket,
                    'Key' => "$folder/" . basename($image),
                    'Body' => fopen($image, 'r'),
                    'ACL' => 'public-read'
                ]);
            }
        }
        return $promises;
    }

    /**
     * Get config used for communicating with S3
     *
     * @return array
     */
    protected function getAwsConfig()
    {
        return [
            'version' => 'latest',
            'region' => get_site_option('prophoto_s3_bundler_region'),
            'credentials' => [
                'key' => get_site_option('prophoto_s3_bundler_key'),
                'secret' => get_site_option('prophoto_s3_bundler_secret'),
            ]
        ];
    }

    /**
     * Add the S3 bucket to the bundled JSON data
     *
     * @param Bundle $bundle
     * @return array
     */
    protected function getBundleData(Bundle $bundle)
    {
        $data = parent::getBundleData($bundle);
        $bucket = get_site_option('prophoto_s3_bundler_bucket');
        $region = get_site_option('prophoto_s3_bundler_region');
        $data['s3'] = [
            'root' => $this->getRootDir($bundle),
            'bucket' => $bucket,
            'region' => $region
        ];
        return $data;
    }

    /**
     * Returns the root dir name that will represent the bundle in S3
     *
     * @param Bundle $bundle
     * @return string
     */
    protected function getRootDir(Bundle $bundle)
    {
        $name = apply_filters('prophoto_s3_bundle_name', $bundle->getName());
        return sanitize_title_with_dashes($name);
    }
}
