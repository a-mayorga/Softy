<?php

require_once("../Response.php");
require_once("../Model.php");
require_once("../libs/PHPMailer/PHPMailerAutoload.php");

use Softy\Response;
use Softy\Model;

$data = json_decode(file_get_contents("php://input"), true);

// OBENER VALORES DEL FORMULARIO DE INICIO
$k = $data["k"];
$j = $data["j"];
$m = $data["m"];
$alfa = $data["alpha"];
$pronostico = $data["forecast"];
$delito = $data["crime"];
$name = $data["name"];
$email = $data["email"];
$clickedButton = $data["button"];

$dss = new Model("datosdelitos");

$where = array("idTipoDelito" => array("=", $delito));
$res = $dss->find("periodoDato, frecuenciaDato", $where);


// $cant = mysqli_num_rows($res);

$numRows = mysqli_num_rows($res);

// LLENAR LA MATRIZ CON LOS VALORES DE LA BASE DE DATOS

for ($i = 0; $i <= $numRows; $i++) {
    if($i < $numRows) {
      $row = mysqli_fetch_row($res);
      $Matriz[$i][0] = $row[0];
      $Matriz[$i][1] = $row[1];
    }
    else {
      $date = new DateTime($Matriz[$i - 1][0]);
      $date->modify('+1 day');
      $Matriz[$i][0] =  $date->format('Y-m-d');
      $Matriz[$i][1] = null;
    }

    $Matriz[$i][2] = null;
    $Matriz[$i][3] = null;
    $Matriz[$i][4] = null;
    $Matriz[$i][5] = null;
    $Matriz[$i][6] = null;
    $Matriz[$i][7] = null;
    $Matriz[$i][8] = null;
    $Matriz[$i][9] = null;
    $Matriz[$i][10] = null;
    $Matriz[$i][11] = null;
    $Matriz[$i][12] = null;
    $Matriz[$i][13] = null;
    $Matriz[$i][14] = null;
    $Matriz[$i][15] = null;
    $Matriz[$i][16] = null;
}

// Esta parte del código va a servir para calcular el PS
$acumulador = 0;
$contador = 0;

for($i = 1; $i <= $numRows; $i++) {
  $acumulador = $acumulador + $Matriz[$i - 1][1];
  // PS
  $Matriz[$i][2] = number_format((float)$acumulador / $i, 2, ".", "");

  // E ABS(PS)
  if($Matriz[$i][1] != null && $Matriz[$i][2] != null) {
    $Matriz[$i][3] = number_format((float)abs($Matriz[$i][1] - $Matriz[$i][2]), 2, ".", "");
  }
}

$contador = 0;

// PMS
if($k > 0 && $k < $numRows - 1) {
  for($a = $k; $a <= $numRows; $a++) {
    for($i = $a - $k; $i < $a; $i++) {
      $contador = $contador + $Matriz[$i][1];
    }

    // PMS
    $Matriz[$a][4] = number_format((float)$contador / $k, 2, ".", "");

    // E ABS(PMS)
    if($Matriz[$a][1] != null && $Matriz[$a][4] != null) {
      $Matriz[$a][5] = number_format((float)abs($Matriz[$a][4] - $Matriz[$a][1]), 2, ".", "");
    }

    $contador = 0;
  }
}

// PMD, A, B, PMDA
if($j > 0 && $j < $numRows - $k - 1) {
  for($b = $j + $k; $b <= $numRows; $b++) {
    for($i = $b - $j; $i < $b; $i++) {
      $contador = $contador + $Matriz[$i][4];
    }

    // PMD
    $Matriz[$b][6] = number_format((float)($contador / $j), 2, ".", "");
    // A
    $promA = (2 * $Matriz[$b][4]) - $Matriz[$b][6];
    $Matriz[$b][8] = number_format((float)$promA, 2, ".", "");
    // B
    $promB = (2 * abs($Matriz[$b][4] - $Matriz[$b][6])) / ($numRows - 1);
    $Matriz[$b][9] = number_format((float)$promB, 2, ".", "");
    // PMDA
    $promPMDA = $Matriz[$b][8] + ($Matriz[$b][9] * $m);
    $Matriz[$b][10] = number_format((float)$promPMDA, 2, ".", "");

    // E ABS(PMD), E ABS(PMDA)
    if($Matriz[$b][1] != null) {
      $Matriz[$b][7] = number_format((float)abs($Matriz[$b][6] - $Matriz[$b][1]), 2, ".", "");
      $Matriz[$b][11] = number_format((float)abs($Matriz[$b][10] - $Matriz[$b][1]), 2, ".", "");;
    }

    $contador = 0;
  }
}

// TMAC, PTMAC
$a = $k;

for($b = $a - $k; $b <= $numRows; $b++) {
  if($a <= $numRows) {
    $TMAC = (($Matriz[$b + 1][1] / $Matriz[$b][1]) - 1) * 100;
    // TMAC
    $Matriz[$a][12] = number_format((float)$TMAC, 2, ".", "");
    $PTMAC = ($Matriz[$b + 1][1] + ($Matriz[$b + 1][1] * ($Matriz[$a][12] / 100)));
    // PTMAC
    $Matriz[$a][13] = number_format((float)$PTMAC, 2, ".", "");

    // E ABS(PTMAC)
    if($Matriz[$a][1] != null && $Matriz[$a][13] != null) {
      $Matriz[$a][14] = number_format((float)abs($Matriz[$a][13] - $Matriz[$a][1]), 2, ".", "");
    }
  }

  $a++;
}

// SUAVIZACIÓN EXPONENCIAL

//PS, PMS, PMD, PMDA, PTMAC

$valSE = 0;
$columna = 0;
switch ($pronostico) {
    case 1:
        $valSE = 2;
        $columna = 2;
        break;
    case 2:
        $valSE = $k + 1;
        $columna = 4;
        break;
    case 3:
        $valSE = $k + $j + 1;
        $columna = 6;
        break;
    case 4:
        $valSE = $k + $j + 1;
        $columna = 10;
        break;
    case 5:
        $valSE = 3;
        $columna = 13;
        break;
}

// 	PREDICCIÓN (SE)
for ($x = $valSE; $x <= $numRows; $x++) {
    $pronSE = $Matriz[$x - 1][$columna] + ($alfa * ($Matriz[$x - 1][1] - $Matriz[$x - 1][$columna]));
    $Matriz[$x][15] = number_format((float)$pronSE, 2, '.', '');

    if($Matriz[$x][1] != null && $Matriz[$x][15] != null) {
      $Matriz[$x][16] = number_format((float)abs($Matriz[$x][1] - $Matriz[$x][15]), 2, '.', '');
    }
}

// Errores medios

$error = 0;
$cont = 0;

// EM(PS)
for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][3] != null) {
    $cont++;
    $error = $error + $Matriz[$i][3];
  }
}

$Matriz[$numRows + 1][0] = number_format((float)($error / $cont), 2, ".", "");
$error = 0;
$cont = 0;

// EM(PMS)
for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][5] != null) {
    $cont++;
    $error = $error + $Matriz[$i][5];
  }
}

$Matriz[$numRows + 1][1] = number_format((float)($error / $cont), 2, ".", "");
$error = 0;
$cont = 0;

// EM(PMD)
for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][7] != null) {
    $cont++;
    $error = $error + $Matriz[$i][7];
  }
}

$Matriz[$numRows + 1][2] = number_format((float)($error / $cont), 2, ".", "");
$error = 0;
$cont = 0;

// EM(PMDA)
for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][11] != null) {
    $cont++;
    $error = $error + $Matriz[$i][11];
  }
}

$Matriz[$numRows + 1][3] = number_format((float)($error / $cont), 2, ".", "");
$error = 0;
$cont = 0;

// EM(PTMAC)
for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][14] != null) {
    $cont++;
    $error = $error + $Matriz[$i][14];
  }
}

$Matriz[$numRows + 1][4] = number_format((float)($error / $cont), 2, ".", "");
$error = 0;
$cont = 0;

// EM(SE)
for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][15] != null) {
    $cont++;
    $error = $error + $Matriz[$i][15];
  }
}

$Matriz[$numRows + 1][5] = number_format((float)($error / $cont), 2, ".", "");


// ERRORES RELATIVOS

// ER(PS)
$erps = ($Matriz[$numRows + 1][0] * 100) / $Matriz[$numRows - 1][1];
$Matriz[$numRows + 1][6] = number_format((float)$erps, 2, ".", "");

$erpms = ($Matriz[$numRows + 1][1] * 100) / $Matriz[$numRows - 1][1];
$Matriz[$numRows + 1][7] = number_format((float)$erpms, 2, ".", "");

$erpmd = ($Matriz[$numRows + 1][2] * 100) / $Matriz[$numRows - 1][1];
$Matriz[$numRows + 1][8] = number_format((float)$erpmd, 2, ".", "");

$ermpda = ($Matriz[$numRows + 1][3] * 100) / $Matriz[$numRows - 1][1];
$Matriz[$numRows + 1][9] = number_format((float)$ermpda, 2, ".", "");

$erptmac = ($Matriz[$numRows + 1][4] * 100) / $Matriz[$numRows - 1][1];
$Matriz[$numRows + 1][10] = number_format((float)$erptmac, 2, ".", "");

$erse = ($Matriz[$numRows + 1][5] * 100) / $Matriz[$numRows - 1][1];
$Matriz[$numRows + 1][11] = number_format((float)$erse, 2, ".", "");

$Matriz[$numRows + 1][12] = min($Matriz[$numRows][2], $Matriz[$numRows][4], $Matriz[$numRows][6], $Matriz[$numRows][10], $Matriz[$numRows][13], $Matriz[$numRows][15]);


// echo json_encode($Matriz);
enviar_notificacion($email, $name, $Matriz);

function enviar_notificacion ($correo, $nombre, $Matriz){
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
  $mail->Subject = "Softy Notificación Actividad Detectada";
  $mail->addReplyTo('noreply@Softy2018.com', 'NoReply');
  $body  = "<div>

              <div style= 'flex-direction:column; text-align:center; justify-content:center;'>
                <h1 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.8); font-weight:lighter !important;'>Hola, {$nombre}:</h1>
                <h1 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.8); font-weight:lighter !important;'>TENEMOS UNA NOTIFICACIÓN PARA TI</h1>
                <h2 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.7); font-weight:lighter !important;'>Ha sido detectado el registro de actividad en la sección de análisis de datos y pronóstico de tu cuenta, en nuestra aplicación</h2>
                <h4 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.5); font-weight:lighter !important;'> NOTA: Cualquier duda, aclaración o inconveniente,
                <br> favor de ponerte en contacto en cualquiera de nuestras redes sociales en la sección
                <h3 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.5); font-weight:lighter !important;'>Autores</h3> de nuestra plataforma o envíanos un correo de respuesta,
                <br> para darle solución de manera inmediata.</h4>
              </div>
            </div>";
  $mail->Body = $body;
  $mail->IsHTML(true);

  if ($mail->Send()) {
    echo json_encode($Matriz);
  } else {
    echo json_encode(Response::serverError("No hay notificación"));
  }

  } catch(Exception $e) {
    echo json_encode(Response::serverError($e->getMessage()));
  }
}

function enviar_alerta ($correo, $nombre){
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
  $mail->Subject = "Softy Alerta Pronóstico";
  $mail->addReplyTo('noreply@Softy2018.com', 'NoReply');
  $body  = "<div>

              <div style= 'flex-direction:column; text-align:center; justify-content:center;'>
                <h1 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.8); font-weight:lighter !important;'>ALERTA PRONÓSTICO</h1>
                <h1 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.8); font-weight:lighter !important;'>Hola, {$nombre}:</h1>
                <h2 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.7); font-weight:lighter !important;'>Te informamos, que de acuerdo a los datos analizados y pronosticados, la mejor opción se encuentra
                <br>por encima de los límites
                <br>de delitos aceptados por el sistema. Revisa tus datos.</h2>
                <h4 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.5); font-weight:lighter !important;'> NOTA: Cualquier duda, aclaración o inconveniente,
                <br> favor de ponerte en contacto en cualquiera de nuestras redes sociales en la sección
                <h3 style='font-family: 'Raleway', sans-serif; color:rgba(0,0,0,.5); font-weight:lighter !important;'>Autores</h3> de nuestra plataforma o envíanos un correo de respuesta,
                <br> para darle solución de manera inmediata.</h4>
              </div>
            </div>";
  $mail->Body = $body;
  $mail->IsHTML(true);

  if ($mail->Send()) {
    echo json_encode($Matriz);
  } else {
    echo json_encode(Response::serverError("No hay Alerta"));
  }

  } catch(Exception $e) {
    echo json_encode(Response::serverError($e->getMessage()));
  }
}
?>
