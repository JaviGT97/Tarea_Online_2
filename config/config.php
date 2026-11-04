<?php
/**
 * Configuración global de la aplicación "Mi pequeño Blog".
 */
const APP_DIR_IMAGENES = __DIR__ . '/../images/';   // carpeta física del servidor (Ej. 2.8)
const RUTA_IMAGENES    = 'images/';                 // ruta web pública
const TAM_MAX_IMAGEN   = 2 * 1024 * 1024;           // 2 MB máximo por imagen
const EXT_VALIDAS      = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
