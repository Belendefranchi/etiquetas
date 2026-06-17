<?php
	
function generarUbicaciones(array $inicio, array $fin): array{
		$actual = $inicio;
		$resultado = [];

		while (true) {

				$resultado[] = $actual;

				if ($actual === $fin) {
						break;
				}

				for ($i = 3; $i >= 0; $i--) {

						$actual[$i]++;

						if ($actual[$i] <= $fin[$i]) {
								break;
						}

						$actual[$i] = $inicio[$i];
				}
		}

		return $resultado;
}
