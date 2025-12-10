<?php
/**
 * Sistema de versionado de assets para optimización de caché
 * Usa filemtime para generar versiones solo cuando los archivos cambian
 */

/**
 * Obtiene la versión de un asset basada en su fecha de modificación
 * @param string $relativePath Ruta relativa desde la raíz del proyecto
 * @return string Timestamp de modificación o '1' si el archivo no existe
 */
function asset_version($relativePath) {
    static $cache = [];
    
    // Cache en memoria para múltiples llamadas al mismo archivo
    if (isset($cache[$relativePath])) {
        return $cache[$relativePath];
    }
    
    $fullPath = __DIR__ . '/../' . $relativePath;
    
    if (file_exists($fullPath)) {
        $version = filemtime($fullPath);
        $cache[$relativePath] = $version;
        return $version;
    }
    
    // Fallback si el archivo no existe
    $cache[$relativePath] = '1';
    return '1';
}

/**
 * Genera la URL completa del asset con versión
 * @param string $relativePath Ruta relativa del asset
 * @return string URL con query string de versión
 */
function asset_url($relativePath) {
    $version = asset_version($relativePath);
    return $relativePath . '?v=' . $version;
}

/**
 * Versión simplificada para producción (usa constante)
 * Descomenta y define APP_VERSION para usar versión estática
 */
// define('APP_VERSION', '1.0.1');
// function asset_version($relativePath) {
//     return APP_VERSION;
// }
