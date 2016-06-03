<?php

if (strpos($_SERVER['REQUEST_URI'], '/static') === 0) {
    return false;    // serve the requested resource as-is.
}

include(__DIR__ . '/init.inc.php');
Pix_Controller::addCommonHelpers();
Pix_Controller::dispatch(__DIR__ . '/webdata/');
