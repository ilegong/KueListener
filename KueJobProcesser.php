<?php
require_once 'vendor/autoload.php';

use Kue\Kue;

$kue = Kue::createQueue();
// Process all types
$kue->process('curl', function ($job) {
    // Process logic
    $data = $job->data;
    execute_curl($data);
});


function execute_curl($data)
{
    $url = '' . $data['url'];
    $s = curl_init();
    curl_setopt($s, CURLOPT_URL, $url);
    curl_setopt($s, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($s, CURLOPT_TIMEOUT, 5);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($s, CURLOPT_HEADER, 1);
    curl_setopt($s, CURLINFO_HEADER_OUT, true);
    curl_setopt($s, CURLOPT_HTTPHEADER, $this->genReqestHeader($data));
    curl_setopt($s, CURLOPT_POST, false);
    curl_setopt($s, CURLOPT_POSTFIELDS, base64_decode($data['postdata']));
    $ret = curl_exec($s);
    $info = curl_getinfo($s);
    $error = curl_error($s);
    curl_close($s);
    if (empty($info['http_code'])) {
        log($error);
    } else if ($info['http_code'] != 200) {
        log("taskqueue service internal error, httpcode isn't 200, code:" . $info['http_code']);
    } else {
        log('execute ' . $url . ' success result ' . $ret);
        return true;
    }
    return false;
}

function genSignature($content, $secretkey)
{
    $sig = base64_encode(hash_hmac('sha256', $content, $secretkey, true));
    return $sig;
}

function genReqestHeader()
{
    $timestamp = date('Y-m-d H:i:s');
    //$cont1 = "ACCESSKEY" . $this->_accesskey . "TIMESTAMP" . $timestamp;
    //$reqhead = array("TimeStamp: $timestamp", "AccessKey: " . $this->_accesskey, "Signature: " . $this->genSignature($cont1, $this->_secretkey));
    //print_r($reqhead);
    $reqhead = array("TimeStamp: $timestamp");
    return $reqhead;
}