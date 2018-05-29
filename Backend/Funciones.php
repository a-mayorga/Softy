<?php

	function Conectar(){
		$con = mysqli_connect("localhost","root","","dssdelitos");
		return $con;
	}

	function EjecutaQuery($con, $consulta){
		$query = mysqli_query($con,$consulta) or die(mysqli_error($con));
		return $query;
	}

	function Desconectar($con){
		mysqli_close($con);
	}
?>
