<?php
require_once("../Response.php");
require_once("../Model.php");
require_once("../libs/PHPMailer/PHPMailerAutoload.php");

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
      enviar_email($data["email"], $data["name"]);
  } else {
      echo json_encode(Response::serverError("No se pudo crear la cuenta."));
  }
}
else {
  echo json_encode(Response::serverError("El correo ya está registrado"));
}

function enviar_email($correo, $nombre){
  try {
  $mail = new PHPMailer(true);
  ob_start();
  $mail->IsSMTP();
  $mail->CharSet = 'UTF-8';
  $mail->SMTPAuth = true;
  $mail->SMTPSecure = "tls";
  $mail->Host = 'smtp.gmail.com';
  $mail->Username = "softy.uaqdss@gmail.com";
  $mail->Password = "@Softy2018";
  $mail->Port = 587;
  $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
  $mail->From = $correo;
  $mail->FromName = "Softy Inc.";
  $mail->AddAddress($correo);
  $mail->Subject = "Softy Confirmación de Registro";
  $mail->addReplyTo('noreply@Softy2018.com', 'NoReply');
  $body  = "<div>

              <div style= 'flex-direction:column; text-align:center; justify-content:center;'>
                <h1 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.8); font-weight:lighter !important;'>Hola, {$nombre}:</h1>
                <h1 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.8); font-weight:lighter !important;'>GRACIAS POR REGISTRARTE</h1>
                <h2 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.7); font-weight:lighter !important;'>Tu registro en nuestra plataforma ha sido exitoso!
                <br>¡Enhorabuena!</h2>
                <h1 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.7); font-weight:lighter; margin-top:30px; margin-bottom:0px;'>Ya puedes navegar por nuestra plataforma y conocer todas las actividades que puedes realizar! :)</h1>
                <h4 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.5); font-weight:lighter !important;'> NOTA: Cualquier duda, aclaración o inconveniente,
                <br> favor de ponerte en contacto en cualquiera de nuestras redes sociales en la sección
                <h3 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.5); font-weight:lighter !important;'>Autores</h3> de nuestra plataforma o envíanos un correo de respuesta,
                <br> para darle solución de manera inmediata.</h4>
              </div>
            </div>";
  $mail->Body = $body;
  $mail->IsHTML(true);

  if ($mail->Send()) {
    echo json_encode(Response::ok("Se creó correctamente tu cuenta. Revisa tu correo."));
  } else {
    echo json_encode(Response::serverError("Se creó correctamente tu cuenta. No se envió correo."));
  }

  } catch(Exception $e) {
    echo json_encode(Response::serverError($e->getMessage()));
  }
}
