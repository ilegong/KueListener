<?php
require_once 'vendor/autoload.php';

use Kue\Kue;

$kue = Kue::createQueue();
// Process all types
$kue->process(function($job){
    // Process logic
    // todo
    log($job->type . ' processed');
});