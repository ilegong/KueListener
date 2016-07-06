#!/usr/bin/env bash

nohup php KueJobProcesser.php  > run.log 2>&1&
echo $! >  KueJobProcesser.pid

 
