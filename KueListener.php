<?php
require_once 'vendor/autoload.php';
/**
 * Test case for resource leaks on the automatic restart of the Daemon.
 * Check for open files using 'lsof handledeamontest*.log'
 * The main log will be open two times, one inherited from the first restart.
 * The new logfile in the restarted process will get handle id 0,
 * that will be considered STDIN and be closed before the next restart.
 * Therefore we only see two open logfiles.
 *
 * The amount of other open log files will graddaly increase.
 *
 */;

class KueListener extends \Core_Daemon {

    /** use long interval to allow for restart.*/
    protected $loop_interval = 10;


    /**
     * Exeucte method, fails on second try.
     * @throws \Exception
     */
    protected function execute() {

    }


    protected function log_file() {
        //Reuse the same logfile
        return "./handledeamontest.log";
    }

    protected function setup() {

    }
}


KueListener::getInstance()->run();