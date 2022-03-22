<?php

    //格式化传递
    function aaa ($string)
    {
    if($string['a']=='1'||$string['a']==1){
       aesGcm(json_encode(['ai'=>$string['ai'],'name'=>$string['name'],'idNum'=>$string['idNum']]));
    }else {
        $data1 = array();
        $data1['no'] = $string['no'];
        $data1['si'] = $string['si'];
        $data1['bt'] = $string['bt'];
        $data1['ot'] = $string['ot'];
        $data1['ct'] = $string['ct'];
        if ($string['ct'] == 2) {
            $data1['di'] = $string['di'];
        }
        if ($string['ct'] == 0) {
            $data1['pi'] = $string['pi'];
        }
        $date = array('collections' => array($data1));
        aesGcm(json_encode($date));
    }

}
 function aesGcm($string)
    {
        //中宣部给到的APP密钥Key
        $key = 'cec7f3f9ee9491d403cabeeb4a9b40d1';

        if(is_array($string)) $string = json_encode($string);

        //二进制key
        $key = hex2bin($key);//
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $encrypt = openssl_encrypt($string, $cipher, $key, OPENSSL_RAW_DATA,$iv,$tag);
        echo base64_encode(($iv.$encrypt.$tag));

    }
    function getClientArgs(): array
    {
    global $argv;
    array_shift($argv);
    $args = array();
    array_walk($argv, function($v ,$k) use(&$args){
        @list($key, $value) = @explode('=', $v);
        $args[$key] = $value;
    });
    return $args;
}
$args = getClientArgs();
aaa($args);