<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$valor_inicial = $_POST["valor_inicial"] ?? null;
	$valor_final = $_POST["valor_final"] ?? null;
	$etiqueta = $_POST["tipo_etiqueta"] ?? null;
	$alternativo = $_POST["alternativo"] ?? null;
	$descripcion = $_POST["descripcion"] ?? null;
	$barras = $_POST["barras"] ?? null;

	// longitud del código (para conservar ceros)
	$longitud = strlen($valor_inicial);
	$inicio = ctype_digit($valor_inicial ?? '') ? intval($valor_inicial) : null;
	$fin = ctype_digit($valor_final ?? '') ? intval($valor_final) : null;

	$zpl_file_descartable = "../etiquetas/etiqueta_descartable.zpl";
	$zpl_file_cubeta_filo = "../etiquetas/etiqueta_cubeta_filo.zpl";
	$zpl_file_cubeta_lateral = "../etiquetas/etiqueta_cubeta_lateral.zpl";
	$zpl_file_gaveta_frente = "../etiquetas/etiqueta_gaveta_frente.zpl";
	$zpl_file_gaveta_grande_lateral = "../etiquetas/etiqueta_gaveta_grande_lateral.zpl";
	$zpl_file_gaveta_chica_lateral = "../etiquetas/etiqueta_gaveta_chica_lateral.zpl";
	$zpl_file_rack = "../etiquetas/etiqueta_rack.zpl";
	$zpl_file_minirack = "../etiquetas/etiqueta_minirack.zpl";
	$zpl_file_livianas = "../etiquetas/etiqueta_livianas.zpl";
	$zpl_file_dinamicas = "../etiquetas/etiqueta_dinamicas.zpl";
	$zpl_file_productos = "../etiquetas/etiqueta_productos.zpl";

	$codigo_zpl_base_descartable = file_get_contents($zpl_file_descartable);
	$codigo_zpl_base_cubeta_filo = file_get_contents($zpl_file_cubeta_filo);
	$codigo_zpl_base_cubeta_lateral = file_get_contents($zpl_file_cubeta_lateral);
	$codigo_zpl_base_gaveta_frente = file_get_contents($zpl_file_gaveta_frente);
	$codigo_zpl_base_gaveta_grande_lateral = file_get_contents($zpl_file_gaveta_grande_lateral);
	$codigo_zpl_base_gaveta_chica_lateral = file_get_contents($zpl_file_gaveta_chica_lateral);
	$codigo_zpl_base_rack = file_get_contents($zpl_file_rack);
	$codigo_zpl_base_minirack = file_get_contents($zpl_file_minirack);
	$codigo_zpl_base_livianas = file_get_contents($zpl_file_livianas);
	$codigo_zpl_base_dinamicas = file_get_contents($zpl_file_dinamicas);
	$codigo_zpl_base_productos = file_get_contents($zpl_file_productos);

	function descomponer($codigo)
	{
		return [
			intval(substr($codigo, 1, 2)),
			intval(substr($codigo, 3, 2)),
			intval(substr($codigo, 5, 2)),
			intval(substr($codigo, 7, 2)),
		];
	}

	function armarCodigo($partes)
	{
		return
			str_pad($partes[0], 2, '0', STR_PAD_LEFT) .
			str_pad($partes[1], 2, '0', STR_PAD_LEFT) .
			str_pad($partes[2], 2, '0', STR_PAD_LEFT) .
			str_pad($partes[3], 2, '0', STR_PAD_LEFT);
	}

	function extraerPasillo($partes)
	{
		return
			str_pad($partes[0], 2, '0', STR_PAD_LEFT);
	}

	function extraerResto($partes)
	{
		return
			str_pad($partes[1], 2, '0', STR_PAD_LEFT) .
			str_pad($partes[2], 2, '0', STR_PAD_LEFT) .
			str_pad($partes[3], 2, '0', STR_PAD_LEFT);
	}

	function incrementarCodigo($valor_inicial, $valor_final, $maximos)
	{

		$actual = descomponer($valor_inicial);
		$final = descomponer($valor_final);

		$codigos = [];
		$pasillos = [];
		$resto = [];

		while (true) {

			$codigos[] = armarCodigo($actual);
			$pasillos[] = extraerPasillo($actual);
			$resto[] = extraerResto($actual);

			if ($actual === $final) {
				break;
			}

			// incrementar desde el último bloque
			for ($i = 3; $i >= 0; $i--) {

				$actual[$i]++;

				if ($actual[$i] <= $maximos[$i]) {
					break;
				}

				// reiniciar y hacer acarreo
				$actual[$i] = 1;
			}
		}
		$result = [
			'codigos' => $codigos,
			'pasillos' => $pasillos,
			'resto' => $resto
		];
		return $result;
	}

	switch ($etiqueta) {
		case "CuF":
			$tipo = null;
			$codigo_zpl_base = $codigo_zpl_base_cubeta_filo;
			break;

		case "CuL":
			$tipo = null;
			$codigo_zpl_base = $codigo_zpl_base_cubeta_lateral;
			break;

		case "CF":
			$tipo = "C";
			$codigo_zpl_base = $codigo_zpl_base_gaveta_frente;
			break;

		case "CL":
			$tipo = "C";
			$codigo_zpl_base = $codigo_zpl_base_gaveta_chica_lateral;
			break;

		case "GF":
			$tipo = "G";
			$codigo_zpl_base = $codigo_zpl_base_gaveta_frente;
			break;

		case "GL":
			$tipo = "G";
			$codigo_zpl_base = $codigo_zpl_base_gaveta_grande_lateral;
			break;

		case "R":
			$tipo = "P";
			$codigo_zpl_base = $codigo_zpl_base_rack;
			$maximos = [6, 19, 10, 12];
			$pasillos = incrementarCodigo($valor_inicial, $valor_final, $maximos)['pasillos'];
			$codigos = incrementarCodigo($valor_inicial, $valor_final, $maximos)['resto'];
			break;

		case "M":
			$tipo = "P";
			$codigo_zpl_base = $codigo_zpl_base_minirack;
			$maximos = [6, 40, 10, 8];
			$codigos = incrementarCodigo($valor_inicial, $valor_final, $maximos)['codigos'];
			break;

		case "Li":
			$tipo = "L";
			$codigo_zpl_base = $codigo_zpl_base_livianas;
			$maximos = [22, 6, 6, 6];
			$codigos = incrementarCodigo($valor_inicial, $valor_final, $maximos)['codigos'];
			break;

		case "Di":
			$tipo = "D";
			$codigo_zpl_base = $codigo_zpl_base_dinamicas;
			$maximos = [4, 14, 4, 16];
			$codigos = incrementarCodigo($valor_inicial, $valor_final, $maximos)['codigos'];
			break;

		case "H":
			$tipo = $etiqueta;
			$codigo_zpl_base = $codigo_zpl_base_livianas;
			$maximos = [3, 4, 6, 14];
			$codigos = incrementarCodigo($valor_inicial, $valor_final, $maximos)['codigos'];
			break;

		case "A":
			$tipo = $etiqueta;
			$codigo_zpl_base = $codigo_zpl_base_livianas;
			$maximos = [1, 3, 2, 5];
			$codigos = incrementarCodigo($valor_inicial, $valor_final, $maximos)['codigos'];
			break;

		case "Pro":
			$tipo = $etiqueta;
			$codigo_zpl_base = $codigo_zpl_base_productos;
			break;

		default:
			$tipo = $etiqueta;
			$codigo_zpl_base = $codigo_zpl_base_descartable;
			break;
	}

	$printerIP = $_POST['printerIP']; // IP de la impresora Zebra
	$printerPort = 9100;
	$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

	if ($socket === false) {
		$conn = false;
	} else {
		$conn = @socket_connect($socket, $printerIP, $printerPort);
	}

	if ($conn) {

		// generar lista de códigos
		if ($etiqueta == "R" || 
			$etiqueta == "M" || 
			$etiqueta == "Li" || 
			$etiqueta == "Di" || 
			$etiqueta == "H" || 
			$etiqueta == "A"
		) {

			$lista_codigos = $codigos;

		} elseif ($etiqueta == "Pro") {

			$lista_codigos = [null];

		} else {

			$lista_codigos = [];

			for ($i = $inicio; $i <= $fin; $i++) {
				$lista_codigos[] = str_pad($i, $longitud, "0", STR_PAD_LEFT);
			}
		}

		// imprimir
		$lista_codigos = array_reverse($lista_codigos);
		foreach ($lista_codigos as $codbar) {

			$codigo =
				substr($codbar, 0, 2) . ' ' .
				substr($codbar, 2, 2) . ' ' .
				substr($codbar, 4, 2) . ' ' .
				substr($codbar, 6, 2);

			$pasillo = substr($codbar, 0, 2);
			$resto = substr($codbar, 2, 2) . ' ' .
				substr($codbar, 4, 2) . ' ' .
				substr($codbar, 6, 2);

			$codigo_zpl_modificado = $codigo_zpl_base;

			$codigo_zpl_modificado = str_replace('[TIPO]', $tipo, $codigo_zpl_modificado);
			$codigo_zpl_modificado = str_replace('[CODIGO]', $codigo, $codigo_zpl_modificado);
			$codigo_zpl_modificado = str_replace('[ALTERNATIVO]', $alternativo, $codigo_zpl_modificado);
			$codigo_zpl_modificado = str_replace('[DESCRIPCION]', $descripcion, $codigo_zpl_modificado);
			$codigo_zpl_modificado = str_replace('[BARRAS]', $barras, $codigo_zpl_modificado);
			$codigo_zpl_modificado = str_replace('[PASILLO]', $pasillo, $codigo_zpl_modificado);
			$codigo_zpl_modificado = str_replace('[RESTO]', $resto, $codigo_zpl_modificado);

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

			socket_write($socket, $codigo_zpl_modificado, strlen($codigo_zpl_modificado));
		}

		if (true) {
			socket_close($socket);
			echo "Etiquetas impresas correctamente.";
			echo '<br><br><button onClick="history.go(-1);">Volver</button>';
		}

	} else {
		echo "<strong>NO SE PUDO ESTABLECER CONEXIÓN CON LA IMPRESORA:</strong><br>IP: " . $printerIP . "<br><br>Verifique si está encendida y conectada a la red, y que la ip ingresada sea correcta.<br><br>";

		if ($socket !== false) {
			echo socket_strerror(socket_last_error($socket));
		} else {
			echo socket_strerror(socket_last_error());
		}
		echo '<br><br><button onClick="history.go(-1);">Volver</button>';
	}
}

?>