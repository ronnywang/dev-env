<?php

error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);

include(__DIR__ . '/pixframework/Pix/Loader.php');
set_include_path(__DIR__ . '/pixframework/');
Pix_Loader::registerAutoLoad();
date_default_timezone_set('Asia/Taipei');
