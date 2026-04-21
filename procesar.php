<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$target_dir = "uploads/";

	// limpiar archivos previos
	foreach (glob($target_dir . "*.txt") as $file) {
		unlink($file);
	}

	$valor_inicial = $_POST["valor_inicial"];
	$valor_final = $_POST["valor_final"];
	$tipo = $_POST["tipo_etiqueta"];

	if ($valor_final < $valor_inicial) {
		die("El valor final no puede ser menor al inicial.");
	}

	// longitud del código (para conservar ceros)
	$longitud = strlen($valor_inicial);
	$inicio = intval($valor_inicial);
	$fin = intval($valor_final);

	$zpl_file = "etiqueta.zpl";
	$codigo_zpl_base = file_get_contents($zpl_file);

	$printerIP = $_POST['printerIP']; // IP de la impresora Zebra
	$printerPort = 9100;
	$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

	if ($socket === false) {
		$conn = false;
	} else {
		$conn = @socket_connect($socket, $printerIP, $printerPort);
	}

	if ($conn) {

		for ($i = $inicio; $i <= $fin; $i++) {

			$codbar = str_pad($i, $longitud, "0", STR_PAD_LEFT);

			$codigo_zpl_modificado = $codigo_zpl_base;
			$codigo_zpl_modificado = str_replace('[TIPO]', $tipo, $codigo_zpl_modificado);

			if ($tipo == "K") {
				$codigo_zpl_modificado = str_replace('[CODBAR]', $valor_inicial, $codigo_zpl_modificado);
					$codigo_zpl_modificado = str_replace('[CANT]', 2, $codigo_zpl_modificado);
			} else {
				$codigo_zpl_modificado = str_replace('[CODBAR]', $codbar, $codigo_zpl_modificado);

				if ($tipo == "B") {
					$codigo_zpl_modificado = str_replace('[CANT]', 2, $codigo_zpl_modificado);
				} else {
					$codigo_zpl_modificado = str_replace('[CANT]', 1, $codigo_zpl_modificado);
				}
			}

			$archivo_modificado = $target_dir . $tipo . $i . ".txt";

			file_put_contents($archivo_modificado, $codigo_zpl_modificado);
		}

		$files = glob('uploads/*.txt');
		$files = array_reverse($files); //para imprimir del ultimo al primero

		if ($files) {
			foreach ($files as $file) {
				$selected_file = $file;
				$codigo_zpl = file_get_contents($selected_file);
				socket_write($socket, $codigo_zpl, strlen($codigo_zpl));
				unlink($file);
			}
			socket_close($socket);
			echo "Archivo enviado e impreso correctamente.";
			echo '<br><br><a href="index.html"><button>Volver</button></a>';
		} else {
			echo "No se encontraron archivos para imprimir.";
			echo '<br><br><a href="index.html"><button>Volver</button></a>';
		}
	} else {
		echo "<strong>NO SE PUDO ESTABLECER CONEXIÓN CON LA IMPRESORA:</strong><br>IP: " . $printerIP . "<br><br>Verifique si está encendida y conectada a la red, y que la ip ingresada sea correcta.<br><br>";

		if ($socket !== false) {
			echo socket_strerror(socket_last_error($socket));
		} else {
			echo socket_strerror(socket_last_error());
		}
		echo '<br><br><a href="index.html"><button>Volver</button></a>';
	}
}


?>