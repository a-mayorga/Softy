<?php
/**
 * Esta clase crea una conexión a la base de datos y provee de un método para realizar consultas
 *
 * @author Alberto Mayorga <mayorga.at@gmail.com>
 * @version 1.0
 */

namespace Softy;

require_once("Config.php");

class Conexion
{
    private $host;
    private $user;
    private $pass ;
    private $database;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->database = DB_NAME;
    }

    /**
     * Crea una conexión con la base de datos
     *
     * @return Object   La conexión a la base de datos
     */
    private function conectar()
    {
        $con = new \mysqli($this->host, $this->user, $this->pass, $this->database);

        if ($con->connect_errno) {
            die(var_dump($con));
        }

        return $con;
    }

    /**
     * Crea una conexión con la base de datos
     *
     * @param string $query   La consulta a ejecutar
     * @return Object   Los resultados que regresa la BD después de ejecutar la consulta
     */
    public function queryRes($query)
    {
        $con = $this->conectar();
        $res = mysqli_query($con, $query);
        mysqli_close($con);
        return $res;
    }
}
