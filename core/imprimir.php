<?php

function imprimirEtiquetas(
    array $lista_codigos,
    string $codigo_zpl_base,
    string $printerIP,
    string $tipo,
    ?string $valor_inicial = null,
    ?string $alternativo = null,
    ?string $descripcion = null,
    ?string $barras = null
)
{
    $printerPort = 9100;

    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    if ($socket === false) {
        return [
            'success' => false,
            'message' => socket_strerror(socket_last_error())
        ];
    }

    $conn = @socket_connect($socket, $printerIP, $printerPort);

    if (!$conn) {

        $error = socket_strerror(socket_last_error($socket));

        socket_close($socket);

        return [
            'success' => false,
            'message' => $error
        ];
    }

    $lista_codigos = array_reverse($lista_codigos);

    foreach ($lista_codigos as $codbar) {

        $codigo =
            substr($codbar, 0, 2) . ' ' .
            substr($codbar, 2, 2) . ' ' .
            substr($codbar, 4, 2) . ' ' .
            substr($codbar, 6, 2);

        $pasillo = substr($codbar, 0, 2);

        $resto =
            substr($codbar, 2, 2) . ' ' .
            substr($codbar, 4, 2) . ' ' .
            substr($codbar, 6, 2);

        $zpl = $codigo_zpl_base;

        $zpl = str_replace('[TIPO]', $tipo, $zpl);
        $zpl = str_replace('[CODIGO]', $codigo, $zpl);
        $zpl = str_replace('[ALTERNATIVO]', $alternativo, $zpl);
        $zpl = str_replace('[DESCRIPCION]', $descripcion, $zpl);
        $zpl = str_replace('[BARRAS]', $barras, $zpl);
        $zpl = str_replace('[PASILLO]', $pasillo, $zpl);
        $zpl = str_replace('[RESTO]', $resto, $zpl);

        if ($tipo == "K") {

            $zpl = str_replace('[CODBAR]', $valor_inicial, $zpl);
            $zpl = str_replace('[CANT]', 2, $zpl);

        } else {

            $zpl = str_replace('[CODBAR]', $codbar, $zpl);

            if ($tipo == "B") {
                $zpl = str_replace('[CANT]', 2, $zpl);
            } else {
                $zpl = str_replace('[CANT]', 1, $zpl);
            }
        }

        socket_write($socket, $zpl, strlen($zpl));
    }

    socket_close($socket);

    return [
        'success' => true,
        'message' => 'Etiquetas impresas correctamente.'
    ];
}