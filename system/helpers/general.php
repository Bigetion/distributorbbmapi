<?php 
function get_header($header_key){
    $headers = getallheaders();
    $header_key = strtolower($header_key);
    $new_headers = array();
    foreach($headers as $key=>$val){
        $new_headers[strtolower($key)] = $val; 
    }
    $headers = $new_headers;
    if($header_key){
        if(array_key_exists($header_key, $headers)) $headers = $headers[$header_key];
        else $headers = false;
    }else $headers = false;
    return $headers;
}
?>