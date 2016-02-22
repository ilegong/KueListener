<?php
require_once 'vendor/autoload.php';

use Kue\Kue;


$kue = Kue::createQueue();
// Process all types
$kue->process('curl', function ($job) {
    // Process logic
    $data = $job->data;
    $result = execute_curl($data);
    if($result){
        $job->complete();
    }else{
        $job->failed();
    }
});


function execute_curl($data)
{
    $url = 'http://www.tongshijia.com' . $data['url'];
    $s = curl_init();
    curl_setopt($s, CURLOPT_URL, $url);
    curl_setopt($s, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($s, CURLOPT_TIMEOUT, 30);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($s, CURLOPT_HEADER, 1);
    curl_setopt($s, CURLINFO_HEADER_OUT, true);
    curl_setopt($s, CURLOPT_HTTPHEADER, genReqestHeader());
    $is_post = false;
    if ($data['form_data']) {
        $decodePostData = base64_decode($data['form_data'], true);
        LogHelper::add_log_to_file('task post data ' . $decodePostData);
        curl_setopt($s, CURLOPT_POSTFIELDS, $decodePostData);
        $is_post = true;
    }
    curl_setopt($s, CURLOPT_POST, $is_post);
    $ret = curl_exec($s);
    $info = curl_getinfo($s);
    $error = curl_error($s);
    curl_close($s);
    if (empty($info['http_code'])) {
        LogHelper::add_log_to_file('error task ' . $url . ' ret error ' . $error . $ret, 'curl');
    } else if ($info['http_code'] != 200) {
        LogHelper::add_log_to_file('error task ' . $url . ' ret error ' . $error . $ret, 'curl');
    } else {
        LogHelper::add_log_to_file('success task ' . $url . ' ret ' . $ret, 'curl');
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
    $reqhead = array("TimeStamp: $timestamp", "Referer: http://www.tongshijia.com");
    return $reqhead;
}


/**
 * Class LogHelper
 * write queue task execute log to file
 */
class LogHelper{

    static $log_handle = false;
    static $log_file = '';
    static $log_file_check_at = 0;
    static $log_file_error = false;

    public static function add_log_to_file($message, $label = '', $indent = 0){
        $header = "\nDate                  PID   Label         Message\n";
        $date   = date("Y-m-d H:i:s");
        $pid    = str_pad(getmypid(), 5, " ", STR_PAD_LEFT);
        $label  = str_pad(substr($label, 0, 12), 13, " ", STR_PAD_RIGHT);
        $prefix = "[$date] $pid $label" . str_repeat("\t", $indent);

        if (time() >= self::$log_file_check_at && self::log_file() != self::$log_file) {
            self::$log_file = self::log_file();
            self::$log_file_check_at = mktime(date('H'), (date('i') - (date('i') % 5)) + 5, null);
            @fclose(self::$log_handle);
            self::$log_handle = $log_file_error = false;
        }

        if (self::$log_handle === false) {
            if (strlen(self::$log_file) > 0 && self::$log_handle = @fopen(self::$log_file, 'a+')) {
                fwrite(self::$log_handle, $header);
            } elseif (!self::$log_file_error) {
                self::$log_file_error = true;
                trigger_error(__CLASS__ . "Error: Could not write to logfile " . self::$log_file, E_USER_WARNING);
            }
        }
        $message = $prefix . ' ' . str_replace("\n", "\n$prefix ", trim($message)) . "\n";
        if (!empty(self::$log_handle)){
            fwrite(self::$log_handle, $message);
        }
    }

    static function log_file() {
        $dir = './log/KueListener/';
        if (@file_exists($dir) == false)
            @mkdir($dir, 0777, true);
        return $dir . '/log_' . date('Ymd').'.log';
    }

}
