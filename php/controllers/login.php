<?php
require_once("../Response.php");
require_once("../Model.php");

use Softy\Response;
use Softy\Model;

$data = json_decode(file_get_contents("php://input"), true);

$user = new Model('usuarios');
$where = array('correoUsuario' => array("=", $data['email']));
$res = mysqli_fetch_assoc($user->find('*', $where));

if ($res['password'] == $data['pass']) {
    unset($res["password"]);
    echo json_encode(Response::ok($res));
} else {
    echo json_encode(Response::unauthorized('Los datos son incorrectos'));
}
