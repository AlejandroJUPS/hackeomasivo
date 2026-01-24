<?php

/**
 * Busca la caratula correspondiente a un ROM
 * @param string $system - Sistema (nes, snes, n64, megadrive, psx)
 * @param string $romName - Nombre del archivo ROM
 * @return string|null - Ruta de la caratula o null si no encuentra
 */
function findCaratula($system, $romName) {
    // Mapeo de nombres de sistema a carpetas de caratulas
    $systemMap = [
        'nes' => 'NES',
        'snes' => 'Snes C',
        'n64' => 'N64',
        'megadrive' => 'SMD',
        'psx' => 'Ps1 C'
    ];
    
    if (!isset($systemMap[$system])) {
        return null;
    }
    
    $caratulaDir = __DIR__ . '/Caratulas/' . $systemMap[$system];
    
    if (!is_dir($caratulaDir)) {
        return null;
    }
    
    // Limpiar el nombre del ROM
    $cleanName = pathinfo($romName, PATHINFO_FILENAME);
    $cleanName = trim(preg_replace('/\s*[\(\[].*?[\)\]]/','', $cleanName));
    
    // Buscar archivos en la carpeta
    $files = scandir($caratulaDir);
    
    // Primero intentar coincidencia exacta
    foreach ($files as $file) {
        if (is_file($caratulaDir . '/' . $file)) {
            $fileBase = pathinfo($file, PATHINFO_FILENAME);
            
            // Coincidencia exacta
            if (strtolower($fileBase) === strtolower($cleanName)) {
                return 'Caratulas/' . $systemMap[$system] . '/' . $file;
            }
        }
    }
    
    // Si no hay coincidencia exacta, buscar por similitud
    $bestMatch = null;
    $bestScore = 0;
    
    foreach ($files as $file) {
        if (is_file($caratulaDir . '/' . $file)) {
            $fileBase = pathinfo($file, PATHINFO_FILENAME);
            
            // Usar similitud de strings
            similar_text(strtolower($cleanName), strtolower($fileBase), $percent);
            
            if ($percent > $bestScore && $percent > 60) {
                $bestScore = $percent;
                $bestMatch = 'Caratulas/' . $systemMap[$system] . '/' . $file;
            }
        }
    }
    
    return $bestMatch;
}

/**
 * Obtiene la ruta de caratula o una imagen por defecto
 */
function getCaratulaPath($system, $romName) {
    $caratula = findCaratula($system, $romName);
    return $caratula ?? 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 280%22%3E%3Crect fill=%22%23333%22 width=%22200%22 height=%22280%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 fill=%22%23999%22 font-family=%22Arial%22 font-size=%2214%22%3ENo hay caratula%3C/text%3E%3C/svg%3E';
}
?>
