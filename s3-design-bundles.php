<?php
/**
 * @package S3 Design Bundles
 * @version 1.0.0
 */
/*
Plugin Name: S3 Design Bundles
Plugin URI: http://prophoto.com
Description: Supports bundling prophoto designs to amazon s3
Author: Brian Scaturro
Version: 1.0.0
Author URI: http://github.com/brianium
*/
require 'vendor/autoload.php';

// require classes used by plugin
require 'src/Design/S3Bundler.php';

// require hooks
require 'hooks/index.php';
