<?php
#defined('EMONCMS_EXEC') or die('Restricted access');

function create_hash($password) {
  $command = "/opt/mosquitto-go-auth/pw -p $password";

  $output = [];
  $return_var = 0;
  exec($command, $output, $return_var);

  if ($return_var != 0) {
    throw new Exception("Hash generation failed");
  }
  return $output[0];
}
