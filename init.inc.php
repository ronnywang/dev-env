<?php

error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);

if (!getenv('SESSION_PATH')) {
    putenv('SESSION_PATH=/tmp/dev-sessions');
    if (!file_exists(getenv('SESSION_PATH'))) {
        mkdir(getenv('SESSION_PATH'));
    }
}
if (!getenv('FILE_PATH')) {
    putenv('FILE_PATH=/tmp/dev-files');
    if (!file_exists(getenv('FILE_PATH'))) {
        mkdir(getenv('FILE_PATH'));
        foreach (glob(__DIR__ . "/template/*") as $f) {
            copy($f, getenv('FILE_PATH'));
        }
    }
}
include(__DIR__ . '/pixframework/Pix/Loader.php');
set_include_path(__DIR__ . '/pixframework/');
Pix_Loader::registerAutoLoad();
date_default_timezone_set('Asia/Taipei');
