<?php
require_once("../Response.php");
require_once("../Model.php");

use Softy\Response;
use Softy\Model;

$data = json_decode(file_get_contents("php://input"), true);

$user = new Model("usuarios");

$where = array("correoUsuario" => array("=", $data["email"]));
$res = $user->find("*", $where);

if(mysqli_num_rows($res) == 0) {
  $params = array("nombreUsuario" => utf8_decode($data["name"]), "password" => $data["pass"], "correoUsuario" => $data["email"]);
  $res = $user->save($params);

  if ($res) {
      echo json_encode(Response::ok("Se creó correctamente tu cuenta."));
  } else {
      echo json_encode(Response::serverError("No se pudo crear la cuenta."));
  }
}
else {
  echo json_encode(Response::serverError("El correo ya está registrado"));
}
