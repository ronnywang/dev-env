<?php

if (strpos($_SERVER['REQUEST_URI'], '/static') === 0) {
    return false;    // serve the requested resource as-is.
}

error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);

include(__DIR__ . '/pixframework/Pix/Loader.php');
set_include_path(__DIR__ . '/pixframework/');
Pix_Loader::registerAutoLoad();
date_default_timezone_set('Asia/Taipei');
Pix_Controller::addCommonHelpers();
Pix_Controller::dispatch(__DIR__ . '/webdata/');
