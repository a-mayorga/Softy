
<?php
require 'PHPMailer/PHPMailerAutoload.php';
enviar_email('saris.grooby12@gmail.com','Sarai Gil Ramos');

function enviar_email($correo, $nombre){
  try {
  // admin@ifilac.net
  // Cdesarrollo2017!
  $mail = new PHPMailer(true);
  ob_start();
  $mail->IsSMTP();
  $mail->CharSet = 'UTF-8';
  $mail->SMTPAuth = true;
  $mail->SMTPSecure = "tls";
  $mail->Host = 'smtp.gmail.com';
  $mail->Username = "softy.uaqdss@gmail.com";
  $mail->Password = "@Softy2018";
  // $mail->Username = "pruebantendamonos@gmail.com";
  // $mail->Password = "Ntendamonosprueba17";
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
    return "Correo Enviado";
  } else {
    return "Falló";
  }
  } catch(Exception $e) {
      echo 'Excepción capturada: ',  $e->getMessage(), "\n";
  }
}

function generateRandomString($length = 10) {
  return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}
?>
