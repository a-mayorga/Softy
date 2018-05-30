<?php

namespace Softy;

class Response {

  public static function ok($msg = "") {
    http_response_code(200);
    $response = array();

    array_push($response, array(
      "res"=>$msg
    ));

    return $response;
  }

  public static function unauthorized($msg = "") {
    http_response_code(401);
    $response = array();

    array_push($response, array(
      "res"=>$msg
    ));

    return $response;
  }

  public static function serverError($msg = "") {
    http_response_code(500);
    $response = array();

    array_push($response, array(
      "res"=>$msg
    ));

    return $response;
  }

}

?>
