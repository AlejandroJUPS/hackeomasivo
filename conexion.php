<?php
// Configuración de archivo JSON para almacenamiento de datos
$dataFile = __DIR__ . '/pedidos.json';

// Función para crear archivo si no existe
function inicializarArchivoJSON() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode([]), FILE_USE_INCLUDE_PATH);
    }
}

// Función para leer todos los pedidos
function obtenerPedidos() {
    global $dataFile;
    inicializarArchivoJSON();
    $contenido = file_get_contents($dataFile);
    return json_decode($contenido, true) ?? [];
}

// Función para guardar pedidos
function guardarPedidos($pedidos) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($pedidos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Función para agregar un nuevo pedido
function agregarPedido($codigo, $cliente, $sencillo, $especial) {
    $pedidos = obtenerPedidos();
    
    $nuevoPedido = [
        'id' => count($pedidos) > 0 ? max(array_column($pedidos, 'id')) + 1 : 1,
        'codigo' => $codigo,
        'cliente' => $cliente,
        'cantidad_sencillo' => (int)$sencillo,
        'cantidad_especial' => (int)$especial,
        'fecha' => date('Y-m-d H:i:s')
    ];
    
    $pedidos[] = $nuevoPedido;
    guardarPedidos($pedidos);
    return true;
}

// Función para eliminar un pedido
function eliminarPedido($id) {
    $pedidos = obtenerPedidos();
    $pedidos = array_filter($pedidos, function($pedido) use ($id) {
        return $pedido['id'] != $id;
    });
    guardarPedidos(array_values($pedidos));
    return true;
}

inicializarArchivoJSON();
?>
