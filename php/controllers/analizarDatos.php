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
$nombre = $data["name"];
$correo = $data["email"];

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

  if($Matriz[$i][1] != null && $Matriz[$i][2] != null) {
    $Matriz[$i][3] = number_format((float)abs($Matriz[$i][1] - $Matriz[$i][2]), 2, ".", "");
  }
}

$contador = 0;

// PMS(k = 2), E ABS(PMS)
if($k > 0 && $k < $numRows - 1) {
  for($a = $k; $a <= $numRows; $a++) {
    for($i = $a - $k; $i < $a; $i++) {
      $contador = $contador + $Matriz[$i][1];
    }

    // PMS
    $Matriz[$a][4] = number_format((float)$contador / $k, 2, ".", "");

    if($Matriz[$a][1] != null && $Matriz[$a][4] != null) {
      $Matriz[$a][5] = number_format((float)abs($Matriz[$a][4] - $Matriz[$a][1]), 2, ".", "");
    }

    $contador = 0;
  }
}

// PMD(j = 3), A, B, PMDA, E ABS(PMDA)
if($j > 0 && $j < $numRows - $k - 1) {
  for($b = $j + $k; $b <= $numRows; $b++) {
    for($i = $b - $j; $i < $b; $i++) {
      $contador = $contador + $Matriz[$i][4];
    }

    // PMD
    $Matriz[$b][6] = number_format((float)($contador / $j), 2, ".", "");
    $promA = (2 * $Matriz[$b][4]) - $Matriz[$b][6];
    $Matriz[$b][8] = number_format((float)$promA, 2, ".", "");
    $promB = (2 * abs($Matriz[$b][4] - $Matriz[$b][6])) / ($numRows - 1);
    $Matriz[$b][9] = number_format((float)$promB, 2, ".", "");
    $promPMDA = $Matriz[$b][8] + ($Matriz[$b][9] * $m);
    // PMDA
    $Matriz[$b][10] = number_format((float)$promPMDA, 2, ".", "");

    if($Matriz[$b][1] != null) {
      $Matriz[$b][7] = number_format((float)abs($Matriz[$b][6] - $Matriz[$b][1]), 2, ".", "");
      $Matriz[$b][11] = number_format((float)abs($Matriz[$b][10] - $Matriz[$b][1]), 2, ".", "");;
    }

    $contador = 0;
  }
}

// TMAC, PTMAC, E ABS(PTMAC)
$a = $k;

for($b = $a - $k; $b <= $numRows; $b++) {
  if($a < $numRows) {
    $TMAC = (($Matriz[$b + 1][1] / $Matriz[$b][1]) - 1) * 100;
    // TMAC
    $Matriz[$a][12] = number_format((float)$TMAC, 2, ".", "");
    $PTMAC = ($Matriz[$b + 1][1] + ($Matriz[$b + 1][1] * ($Matriz[$a][12] / 100)));
    // PTMAC
    $Matriz[$a][13] = number_format((float)$PTMAC, 2, ".", "");
    $Matriz[$a][14] = number_format((float)abs($Matriz[$a][13] - $Matriz[$a][1]), 2, ".", "");
  }

  $a++;
}

// Errores medios

$errPS = 0;
$errPMS = 0;
$errPMD = 0;
$errPMDA = 0;
$errPTMAC = 0;
$cont = 0;


for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][3] != null) {
    $cont++;
    $errPS = $errPS + $Matriz[$i][3];
  }
}

$Matriz[$numRows + 1][3] = number_format((float)($errPS / $cont), 2, ".", "");
$cont = 0;

for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][5] != null) {
    $cont++;
    $errPMS = $errPMS + $Matriz[$i][5];
  }
}

$Matriz[$numRows + 1][5] = number_format((float)($errPMS / $cont), 2, ".", "");
$cont = 0;

for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][7] != null) {
    $cont++;
    $errPMD = $errPMD + $Matriz[$i][7];
  }
}

$Matriz[$numRows + 1][7] = number_format((float)($errPMD / $cont), 2, ".", "");
$cont = 0;

for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][11] != null) {
    $cont++;
    $errPMDA = $errPMDA + $Matriz[$i][11];
  }
}

$Matriz[$numRows + 1][11] = number_format((float)($errPMDA / $cont), 2, ".", "");
$cont = 0;

for($i = 0; $i < $numRows; $i++) {
  if($Matriz[$i][14] != null) {
    $cont++;
    $errPTMAC = $errPTMAC + $Matriz[$i][14];
  }
}

$Matriz[$numRows + 1][14] = number_format((float)($errPTMAC / $cont), 2, ".", "");


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
}

for ($x = ($valSE); $x < $numRows; $x++) {
    // ERROR ABSOLUTO (SE)
    $ease = abs($Matriz[$x][1] - $Matriz[$x][15]);
    $Matriz[$x][16] = number_format((float)$ease, 2, '.', '');
}

// ERRORES MEDIOS EA(SE)
$acumuladorEASE = 0;
$ease = 0;

for ($x = ($valSE); $x < $numRows; $x++) {
    $acumuladorEASE = $acumuladorEASE + $Matriz[$x][16];
}

$ease = $acumuladorEASE / ($numRows - $k - 1);

for ($y = $numRows + 1; $y < $numRows + 2; $y++) {
    $Matriz[$y][16]= number_format((float)$ease, 2, '.', '');
}


// ERRORES RELATIVOS EA(PMS)
$erpms = ($Matriz[$numRows + 1][16] * 100) / $Matriz[$numRows - 1][1];

for ($x = ($numRows + 2); $x < ($numRows + 3); $x++) {
    $Matriz[$x][16] = number_format((float)$erpms, 2, '.', '');
}

// // Llenar la matriz con los valores de la Base de Datos
// // -----------------------------------------------------------
// for ($x = 0; $x < $cant + 1; $x++) {
//     $row = mysqli_fetch_row($res);
//     $Matriz[$x][0] = $row[0];
//     $Matriz[$x][1] = $row[1];
// }
//
// // Valor del pronóstico
// for ($y = $cant; $y < $cant + 1; $y++) {
//     $Matriz[$y][0] = '2018-05-26';
// }
//
// // PROMEDIO SIMPLE (PS)
// // valores vacíos por default en PS
// $Matriz[0][2] = (int)"";
// $Matriz[0][3] = (int)"";
//
// $acumuladorPS = 0;
//
// for ($x = 1; $x < $cant; $x++) {
//     $acumuladorPS = $acumuladorPS + $Matriz[$x - 1][1];
//     $ps = $acumuladorPS / $x;
//     // PS
//     $Matriz[$x][2] = number_format((float)$ps, 2, '.', '');
//
//     // ERROR ABSOLUTO (PS)
//     $eaps = abs($Matriz[$x][1] - $Matriz[$x][2]);
//     $Matriz[$x][3] = number_format((float)$eaps, 2, '.', '');
// }
//
// // PRONÓSTICO (PS)
// for ($x = $cant; $x < $cant + 1; $x++) {
//     $acumuladorPS = $acumuladorPS + $Matriz[$x - 1][1];
//     $pronPS = $acumuladorPS / $x;
//     $Matriz[$x][2] = number_format((float)$pronPS, 2, '.', '');
// }
//
// // ERRORES MEDIOS EA(PS)
// $acumEMeaps = 0;
// $emps = 0;
//
// for ($x = 1; $x < $cant; $x++) {
//     $acumEMeaps = $acumEMeaps + $Matriz[$x][3];
// }
//
// $emps = $acumEMeaps / ($cant - 1);
//
// for ($x = ($cant + 1); $x < ($cant + 2); $x++) {
//     $Matriz[$x][3] = number_format((float)$emps, 2, '.', '');
// }
//
// // ERRORES RELATIVOS EA(PS)
// $erps = ($Matriz[$cant + 1][3] * 100) / $Matriz[$cant - 1][1];
//
// for ($x = ($cant + 2); $x < ($cant + 3); $x++) {
//     $Matriz[$x][3] = number_format((float)$erps, 2, '.', '');
// }
//
//
//
// // PROMEDIO MÓVIL SIMPLE (PMS)
// // -----------------------------------------------------------
// $acumuladorPMS = 0;
//
// // Mostrar los espacios vacíos en PMS
// for ($x = 0; $x < $k; $x++) {
//     $Matriz[$x][4] = (int)"";
//     $Matriz[$x][5] = (int)"";
// }
//
// for ($x = $k; $x < $cant; $x++) {
//     for ($y = $k; $y > 0; $y--) {
//         $acumuladorPMS = $acumuladorPMS + $Matriz[$x - $y][1];
//         $pms = $acumuladorPMS / $k;
//         // PMS
//         $Matriz[$x][4] =  number_format((float)$pms, 2, '.', '');
//     }
//
//     $acumuladorPMS =0;
//
//     // ERROR ABSOLUTO (PMS)
//     $eapms = abs($Matriz[$x][1] - $Matriz[$x][4]);
//     $Matriz[$x][5] = number_format((float)$eapms, 2, '.', '');
// }
//
// // 	PRONÓSTICO (PMS)
// for ($x = $cant; $x < $cant + 1; $x++) {
//     for ($y = $k; $y > 0; $y--) {
//         $acumuladorPMS = $acumuladorPMS + $Matriz[$x - $y][1];
//         $pronPMS = $acumuladorPMS / $k;
//         $Matriz[$x][4] = number_format((float)$pronPMS, 2, '.', '');
//     }
//
//     $acumuladorPMS = 0;
// }
//
// // ERRORES MEDIOS EA(PMS)
// $acumuladorEAPMS = 0;
// $eapms = 0;
//
// for ($x = $k; $x < $cant; $x++) {
//     $acumuladorEAPMS = $acumuladorEAPMS + $Matriz[$x][5];
// }
//
// $eapms = $acumuladorEAPMS / ($cant - $k);
//
// for ($x = $cant + 1; $x < $cant + 2; $x++) {
//     $Matriz[$x][5]= number_format((float)$eapms, 2, '.', '');
// }
//
// // ERRORES RELATIVOS EA(PMS)
// $erpms = ($Matriz[$cant + 1][5] * 100) / $Matriz[$cant - 1][1];
//
// for ($x = ($cant + 2); $x < ($cant + 3); $x++) {
//     $Matriz[$x][5] = number_format((float)$erpms, 2, '.', '');
// }
//
//
//
// // PROMEDIO MÓVIL DOBLE (PMD)
// // -----------------------------------------------------------
// $acumuladorPMD = 0;
//
// // mostrar los espacios vacíos en PMD
// for ($x = 0; $x < ($j + $k); $x++) {
//     $Matriz[$x][6] = (int)"";
//     $Matriz[$x][7] = (int)"";
// }
//
// for ($x = ($j + $k); $x < $cant; $x++) {
//     for ($y = ($j); $y > 0; $y--) {
//         $acumuladorPMD = $acumuladorPMD + $Matriz[$x - $y][4];
//         $pms = $acumuladorPMD / $j;
//         // PMD
//         $Matriz[$x][6] = number_format((float)$pms, 2, '.', '');
//     }
//
//     $acumuladorPMD = 0;
//
//     // ERROR ABSOLUTO (PMD)
//     $eapmd = abs($Matriz[$x][1] - $Matriz[$x][6]);
//     $Matriz[$x][7] = number_format((float)$eapmd, 2, '.', '');
// }
//
// // 	PREDICCIÓN (PMD)
// for ($x = $cant; $x < $cant + 1; $x++) {
//     for ($y = $j; $y > 0; $y--) {
//         $acumuladorPMD = $acumuladorPMD + $Matriz[$x - $y][4];
//         $pronPMD = $acumuladorPMD / $j;
//         $Matriz[$x][6] = number_format((float)$pronPMD, 2, '.', '');
//     }
//
//     $acumuladorPMD =0;
// }
//
// // ERRORES MEDIOS EA(PMD)
// $acumuladorEAPMD = 0;
// $eapmd = 0;
//
// for ($x = $j; $x < $cant; $x++) {
//     $acumuladorEAPMD = $acumuladorEAPMD + $Matriz[$x][7];
// }
//
// $eapmd = $acumuladorEAPMD / ($cant - $j - $k);
//
// for ($y = $cant + 1; $y < $cant + 2; $y++) {
//     $Matriz[$y][7]= number_format((float)$eapmd, 2, '.', '');
// }
//
// // ERRORES RELATIVOS EA(PMS)
// $erpms = ($Matriz[$cant + 1][7] * 100) / $Matriz[$cant - 1][1];
//
// for ($x = ($cant + 2); $x < ($cant + 3); $x++) {
//     $Matriz[$x][7]= number_format((float)$erpms, 2, '.', '');
// }
//
//
//
// // PROMEDIO MÓVIL DOBLE AJUSTADO (PMDA)
//
// // mostrar los espacios vacíos en A, B, PMDA y EA(PMDA)
// for ($x = 0; $x < ($j + $k); $x++) {
//     $Matriz[$x][8] = (int)"";
//     $Matriz[$x][9] = (int)"";
//     $Matriz[$x][10] = (int)"";
//     $Matriz[$x][11] = (int)"";
// }
//
//
// for ($x = ($j + $k); $x < $cant; $x++) {
//     // A
//     $pmdaA = (($Matriz[$x][4]) * 2) - $Matriz[$x][6];
//     $Matriz[$x][8] =  number_format((float)$pmdaA, 2, '.', '');
//
//     //B
//     $pmdaB = (abs($Matriz[$x][4] - $Matriz[$x][6]) * 2) / ($cant - 1);
//     $Matriz[$x][9] =  number_format((float)$pmdaB, 2, '.', '');
//
//     //PMDA
//     $pmda = ($pmdaA + ($pmdaB * $m));
//     $Matriz[$x][10] =  number_format((float)$pmda, 2, '.', '');
//
//     // ERROR ABSOLUTO (PMDA)
//     $eapmda = abs($Matriz[$x][1] - $Matriz[$x][10]);
//     $Matriz[$x][11] = number_format((float)$eapmda, 2, '.', '');
// }
//
// // 	PREDICCIÓN (PMDA)
// for ($x = $cant; $x < $cant + 1; $x++) {
//     // pron A
//     $pronPmdaA = (($Matriz[$x][4]) * 2) - $Matriz[$x][6];
//     $Matriz[$x][8] =  number_format((float)$pronPmdaA, 2, '.', '');
//
//     // pron B
//     $pronPmdaB = (abs($Matriz[$x][4] - $Matriz[$x][6]) * 2) / ($cant - 1);
//     $Matriz[$x][9] =  number_format((float)$pronPmdaB, 2, '.', '');
//
//     // pron PMDA
//     $pronPMDA = $pronPmdaA + ($pronPmdaB * $m);
//     $Matriz[$x][10] =  number_format((float)$pronPMDA, 2, '.', '');
// }
//
// // ERRORES MEDIOS EA(PMDA)
// $acumuladorEAPMDA = 0;
// $eapmda = 0;
//
// for ($x = $j; $x < $cant; $x++) {
//     $acumuladorEAPMDA = $acumuladorEAPMDA + $Matriz[$x][11];
// }
//
// $eapmda = $acumuladorEAPMDA / ($cant - ($j - $k));
//
// for ($y = $cant + 1; $y < $cant + 2; $y++) {
//     $Matriz[$y][11] = number_format((float)$eapmda, 2, '.', '');
// }
//
// // ERRORES RELATIVOS EA(PMS)
// $erpms = ($Matriz[$cant + 1][11] * 100) / $Matriz[$cant - 1][1];
//
// for ($x= ($cant + 2); $x < ($cant + 3); $x++) {
//     $Matriz[$x][11] = number_format((float)$erpms, 2, '.', '');
// }
//
//
//
// // PRONÓSTICO DE TASAS MEDIAS DE CRECIMIENTO
//
// // mostrar los espacios vacíos en TMAC, PTMAC y EA(PTMAC)
// for ($x = 0; $x < 2; $x++) {
//     $Matriz[$x][12] = (int)"";
//     $Matriz[$x][13] = (int)"";
//     $Matriz[$x][14] = (int)"";
// }
//
// //TMAC
// for ($x = 2; $x < $cant; $x++) {
//     $tmac = (($Matriz[$x - 1][1] / $Matriz[$x - 2][1]) - 1) * 100;
//     $Matriz[$x][12] =  number_format((float)$tmac, 2, '.', '');
// }
//
// // 	PRONÓSTICO (TMAC)
// for ($x = $cant; $x < $cant + 1; $x++) {
//     $pronTMAC = (($Matriz[$x - 1][1] / $Matriz[$x - 2][1]) - 1) * 100;
//     $Matriz[$x][12] =  number_format((float)$pronTMAC, 2, '.', '');
// }
//
//
// // PTMAC
// for ($x = 2; $x < $cant; $x++) {
//     $ptmac = $Matriz[$x - 1][1] + ($Matriz[$x - 1][1] * ($Matriz[$x][12] / 100));
//     $Matriz[$x][13] =  number_format((float)$ptmac, 2, '.', '');
//
//     // ERROR ABSOLUTO (PTMAC)
//     $eaptmac = abs($Matriz[$x][1] - $Matriz[$x][13]);
//     $Matriz[$x][14] = number_format((float)$eaptmac, 2, '.', '');
// }
//
// // 	PRONÓSTICO (PTMAC)
// for ($x = $cant; $x < $cant + 1; $x++) {
//     $pronPTMAC = $Matriz[$x - 1][1] + ($Matriz[$x - 1][1] * ($Matriz[$x][12] / 100));
//     $Matriz[$x][13] =  number_format((float)$pronPTMAC, 2, '.', '');
// }
//
//
// // ERRORES MEDIOS EA(PTMAC)
// $acumuladorEAPTMAC = 0;
// $eaptmac = 0;
//
// for ($x = 2; $x < $cant; $x++) {
//     $acumuladorEAPTMAC = $acumuladorEAPTMAC + $Matriz[$x][14];
// }
//
// $eaptmac = $acumuladorEAPTMAC / ($cant - 2);
//
// for ($x = $cant + 1; $x < $cant + 2 ; $x++) {
//     $Matriz[$x][14] = number_format((float)$eaptmac, 2, '.', '');
// }
//
// // ERRORES RELATIVOS EA(PMS)
// $erpms = ($Matriz[$cant + 1][14] * 100) / $Matriz[$cant - 1][1];
//
// for ($x = ($cant + 2); $x < ($cant + 3); $x++) {
//     $Matriz[$x][14] = number_format((float)$erpms, 2, '.', '');
// }
//
//
//
// // SUAVIZACIÓN EXPONENCIAL
//
// //PS, PMS, PMD, PMDA, PTMAC
//
// $valSE = 0;
// $columna = 0;
// switch ($pronostico) {
//     case 1:
//         $valSE = 2;
//         $columna = 2;
//         break;
//     case 2:
//         $valSE = $k + 1;
//         $columna = 4;
//         break;
//     case 3:
//         $valSE = $k + $j + 1;
//         $columna = 6;
//         break;
//     case 4:
//         $valSE = $k + $j + 1;
//         $columna = 10;
//         break;
//     case 5:
//         $valSE = 3;
//         $columna = 13;
//         break;
// }
//
//
// // mostrar los espacios vacíos en SE y EA(SE)
// for ($x = 0; $x < ($valSE); $x++) {
//     $Matriz[$x][15] = (int)"";
//     $Matriz[$x][16] = (int)"";
// }
//
// // SE
// for ($x = ($valSE); $x < $cant; $x++) {
//     $se = $Matriz[$x - 1][$columna] + $alfa * ($Matriz[$x - 1][1] - $Matriz[$x - 1][$columna]);
//     // SE
//     $Matriz[$x][15] =  number_format((float)$se, 2, '.', '');
//
//     // ERROR ABSOLUTO (SE)
//     $ease = abs($Matriz[$x][1] - $Matriz[$x][15]);
//     $Matriz[$x][16] = number_format((float)$ease, 2, '.', '');
// }
//
// // 	PREDICCIÓN (SE)
// for ($x = $cant; $x < $cant + 1; $x++) {
//     $pronSE = ($alfa * ($Matriz[$x - 1][1] - $Matriz[$x - 1][4])) + $Matriz[$x - 1][4];
//     $Matriz[$x][15] = number_format((float)$pronSE, 2, '.', '');
// }
//
// // ERRORES MEDIOS EA(SE)
// $acumuladorEASE = 0;
// $ease = 0;
//
// for ($x = ($valSE); $x < $cant; $x++) {
//     $acumuladorEASE = $acumuladorEASE + $Matriz[$x][16];
// }
//
// $ease = $acumuladorEASE / ($cant - $k - 1);
//
// for ($y = $cant + 1; $y < $cant + 2; $y++) {
//     $Matriz[$y][16]= number_format((float)$ease, 2, '.', '');
// }
//
// // ERRORES RELATIVOS EA(PMS)
// $erpms = ($Matriz[$cant + 1][16] * 100) / $Matriz[$cant - 1][1];
//
// for ($x = ($cant + 2); $x < ($cant + 3); $x++) {
//     $Matriz[$x][16]= number_format((float)$erpms, 2, '.', '');
// }
//
//
// // GUARDAR DATOS PARA GRÁFICOS
// // ------------------------------------------------------------------------
// //Guardar datos de Periodo y Frecuencia
//
// $ValorPronPS = $Matriz[$cant][2];
//
// $varP = "";
//
// for ($x = 0; $x < $cant; $x++) {
//     $varP = $varP . "['" . $Matriz[$x][0] . "'," . $Matriz[$x][1] . "," . $Matriz[$x][2] . "],";
// }
//
// for ($x = $cant; $x < $cant + 1; $x++) {
//     $varP = $varP . "['" . $Matriz[$x][0] . "'," . $Matriz[$x][1] . "," . $Matriz[$x][2] . "]";
// }

function enviar_notificacion ($correo, $nombre){
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
    echo json_encode(Response::ok("Notificación enviada."));
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
    echo json_encode(Response::ok("Alerta enviada."));
  } else {
    echo json_encode(Response::serverError("No hay Alerta"));
  }

  } catch(Exception $e) {
    echo json_encode(Response::serverError($e->getMessage()));
  }
}

enviar_notificacion($nombre,$correo);
echo json_encode($Matriz);
//
?>
