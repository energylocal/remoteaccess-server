<?php

function get_application_path()
{
    // Default to http protocol
    $proto = "http";

    // Detect if we are running HTTPS or proxied HTTPS
    if (server('HTTPS') == 'on') {
        // Web server is running native HTTPS
        $proto = "https";
    } elseif (server('HTTP_X_FORWARDED_PROTO') == "https") {
        // Web server is running behind a proxy which is running HTTPS
        $proto = "https";
    }

    if( isset( $_SERVER['HTTP_X_FORWARDED_SERVER'] ))
        $path = dirname("$proto://" . server('HTTP_X_FORWARDED_SERVER') . server('SCRIPT_NAME')) . "/";
    else
        $path = dirname("$proto://" . server('HTTP_HOST') . server('SCRIPT_NAME')) . "/";

    return $path;
}

function view($filepath, array $args)
{
    extract($args);
    ob_start();
    include "$filepath";
    $content = ob_get_clean();
    return $content;
}

function get($index)
{
    $val = null;
    if (isset($_GET[$index])) $val = $_GET[$index];
    
    if (get_magic_quotes_gpc()) $val = stripslashes($val);
    return $val;
}

function post($index)
{
    $val = null;
    if (isset($_POST[$index])) $val = $_POST[$index];
    
    if (get_magic_quotes_gpc()) $val = stripslashes($val);
    return $val;
}

function prop($index)
{
    $val = null;
    if (isset($_GET[$index])) $val = $_GET[$index];
    if (isset($_POST[$index])) $val = $_POST[$index];
    
    if (get_magic_quotes_gpc()) $val = stripslashes($val);
    return $val;
}


function server($index)
{
    $val = null;
    if (isset($_SERVER[$index])) $val = $_SERVER[$index];
    return $val;
}

function http_request($method,$url,$data) {

    $options = array();
    $urlencoded = http_build_query($data);
    
    if ($method=="GET") { 
        $url = "$url?$urlencoded";
    } else if ($method=="POST") {
        $options[CURLOPT_POST] = 1;
        $options[CURLOPT_POSTFIELDS] = $data;
    }
    
    $options[CURLOPT_URL] = $url;
    $options[CURLOPT_RETURNTRANSFER] = 1;
    $options[CURLOPT_CONNECTTIMEOUT] = 2;
    $options[CURLOPT_TIMEOUT] = 5;

    $curl = curl_init();
    curl_setopt_array($curl,$options);
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
}
