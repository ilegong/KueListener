<?php

function genReqestHeader()
{
    $timestamp = date('Y-m-d H:i:s');
    //$cont1 = "ACCESSKEY" . $this->_accesskey . "TIMESTAMP" . $timestamp;
    //$reqhead = array("TimeStamp: $timestamp", "AccessKey: " . $this->_accesskey, "Signature: " . $this->genSignature($cont1, $this->_secretkey));
    //print_r($reqhead);
    $reqhead = array("TimeStamp: $timestamp", "Referer: http://www.tongshijia.com");
    return $reqhead;
}

$url = 'http://www.tongshijia.com/manage/admin/ShareCountly/gen_sharer_statics_data_task/2016-02-21/10/58';
$s = curl_init();
curl_setopt($s, CURLOPT_URL, $url);
curl_setopt($s, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
curl_setopt($s, CURLOPT_TIMEOUT, 30);
curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
curl_setopt($s, CURLOPT_HEADER, 1);
curl_setopt($s, CURLINFO_HEADER_OUT, true);
curl_setopt($s, CURLOPT_HTTPHEADER, genReqestHeader());
curl_setopt($s, CURLOPT_POST, true);
$ret = curl_exec($s);
$info = curl_getinfo($s);
$error = curl_error($s);
curl_close($s);
echo $ret.' '.$info;


