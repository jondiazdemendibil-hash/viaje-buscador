<?php
/**
 * Template Name: Single Tramo Custom
 * Template Post Type: tramo
 * 
 * Plantilla rediseñada para mostrar un tramo de viaje con:
 * - Diseño visual adaptado de single-dia.php
 * - Proporción 50% texto / 50% mapa
 * - Información del capítulo (si existe)
 * - Datos del tramo (inicio, fin, descripción)
 * - Mapa con todos los días del tramo (marcadores + polyline)
 * - Navegación entre tramos (prev/next)
 * - Controles de autoplay
 * - Responsive (móvil: columna vertical)
 * 
 * @version 2.0
 * @last-updated 2025-12-23
 */

// ------------------------------
// Helper para parsear fechas ACF
// ------------------------------
function viaje_parse_acf_date_tramo($raw) {
    if (!$raw) {
        return 0;
    }
    // ACF date formato Ymd (ej: 20260403)
    if (is_string($raw) && preg_match('/^\d{8}$/', $raw)) {
        $dt = DateTime::createFromFormat('Ymd', $raw);
        return $dt ? (int) $dt->getTimestamp() : 0;
    }
    $ts = strtotime((string) $raw);
    return $ts ? (int) $ts : 0;
}

// Función para validar coordenadas geográficas
function validar_coordenadas_tramo($lat, $lng) {
    $lat = (float) $lat;
    $lng = (float) $lng;
    
    if (empty($lat) || empty($lng) || !is_numeric($lat) || !is_numeric($lng)) {
        return false;
    }
    
    // Validar rangos: lat (-90 a 90), lng (-180 a 180)
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return false;
    }
    
    return true;
}

// ------------------------------
// Obtener datos del tramo actual
// ------------------------------
$tramo_id = get_the_ID();
$tramo_titulo = get_the_title($tramo_id);
$id_tramo = get_field('id_tramo', $tramo_id);
$id_capitulo = get_field('id_capitulo', $tramo_id);

$sitio_inicio_tramo = get_field('sitio_inicio_tramo', $tramo_id);
$fecha_inicio_tramo_raw = get_field('fecha_inicio_tramo', $tramo_id);
$sitio_fin_tramo = get_field('sitio_fin_tramo', $tramo_id);
$fecha_fin_tramo_raw = get_field('fecha_fin_tramo', $tramo_id);
$descripcion_tramo = get_field('descripcion_tramo', $tramo_id);

$fecha_inicio_ts = viaje_parse_acf_date_tramo($fecha_inicio_tramo_raw);
$fecha_fin_ts = viaje_parse_acf_date_tramo($fecha_fin_tramo_raw);

$fecha_inicio_tramo = $fecha_inicio_ts ? date_i18n('l, d \d\e F \d\e Y', $fecha_inicio_ts) : '';
$fecha_fin_tramo = $fecha_fin_ts ? date_i18n('l, d \d\e F \d\e Y', $fecha_fin_ts) : '';

// ------------------------------
// Obtener datos del capítulo (si existe)
// ------------------------------
$capitulo_nombre = '';
$capitulo_sitio_inicio = '';
$capitulo_fecha_inicio = '';
$capitulo_sitio_fin = '';
$capitulo_fecha_fin = '';

if ($id_capitulo) {
    $capitulo_posts = get_posts([
        'post_type' => 'capitulo',
        'meta_key' => 'id_capitulo',
        'meta_value' => $id_capitulo,
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ]);
    
    if ($capitulo_posts) {
        $capitulo_post = $capitulo_posts[0];
        $capitulo_nombre = $capitulo_post->post_title;
        $capitulo_sitio_inicio = get_field('sitio_inicio_capitulo', $capitulo_post->ID);
        $capitulo_fecha_inicio_raw = get_field('fecha_inicio_capitulo', $capitulo_post->ID);
        $capitulo_sitio_fin = get_field('sitio_fin_capitulo', $capitulo_post->ID);
        $capitulo_fecha_fin_raw = get_field('fecha_fin_capitulo', $capitulo_post->ID);
        
        $capitulo_inicio_ts = viaje_parse_acf_date_tramo($capitulo_fecha_inicio_raw);
        $capitulo_fin_ts = viaje_parse_acf_date_tramo($capitulo_fecha_fin_raw);
        
        $capitulo_fecha_inicio = $capitulo_inicio_ts ? date_i18n('l, d \d\e F \d\e Y', $capitulo_inicio_ts) : '';
        $capitulo_fecha_fin = $capitulo_fin_ts ? date_i18n('l, d \d\e F \d\e Y', $capitulo_fin_ts) : '';
    }
}

// ------------------------------
// Obtener todos los días del tramo para el mapa (con caché)
// ------------------------------
$dias_tramo = [];

if ($id_tramo) {
    // Intentar obtener de caché
    $cache_key = 'dias_tramo_' . $id_tramo;
    $dias_tramo = get_transient($cache_key);
    
    if (false === $dias_tramo) {
        // Caché no existe, ejecutar query
        $dias_tramo = [];
        
        $dias_query = new WP_Query([
            'post_type' => 'dia',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => 'id_tramo', 'value' => $id_tramo],
            ],
            'orderby' => 'meta_value_num',
            'meta_key' => 'dia',
            'order' => 'ASC',
        ]);
        
        if ($dias_query->have_posts()) {
            while ($dias_query->have_posts()) {
                $dias_query->the_post();
                $lat = (float) get_field('latitud');
                $lng = (float) get_field('longitud');
                
                // Validar coordenadas antes de añadir
                if (validar_coordenadas_tramo($lat, $lng)) {
                    $fecha_dia_raw = get_field('fecha_dia');
                    $fecha_ts = viaje_parse_acf_date_tramo($fecha_dia_raw);
                    $fecha_formateada = $fecha_ts ? date_i18n('d M Y', $fecha_ts) : '';
                    
                    $dias_tramo[] = [
                        'lat' => $lat,
                        'lng' => $lng,
                        'title' => get_field('sitio_dia'),
                        'dia_numero' => (int) get_field('dia'),
                        'url' => get_permalink(),
                        'fecha' => $fecha_formateada,
                        'hito' => get_field('hito_dia'),
                    ];
                } else {
                    error_log("⚠️ Tramo {$tramo_titulo}: Día " . get_the_title() . " tiene coordenadas inválidas");
                }
            }
            wp_reset_postdata();
        }
        
        // Guardar en caché por 12 horas
        set_transient($cache_key, $dias_tramo, 12 * HOUR_IN_SECONDS);
        error_log("✅ Caché creada para días del tramo: {$id_tramo} (" . count($dias_tramo) . " días)");
    } else {
        error_log("⚡ Caché usada para días del tramo: {$id_tramo} (" . count($dias_tramo) . " días)");
    }
}

// ------------------------------
// Obtener primer día del tramo (para botón "Descubrir")
// ------------------------------
$primer_dia_url = '';

if ($id_tramo) {
    $primer_dia_query = new WP_Query([
        'post_type' => 'dia',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'meta_query' => [
            ['key' => 'id_tramo', 'value' => $id_tramo],
        ],
        'orderby' => 'meta_value_num',
        'meta_key' => 'dia',
        'order' => 'ASC',
    ]);
    
    if ($primer_dia_query->have_posts()) {
        $primer_dia_query->the_post();
        $primer_dia_url = get_permalink();
        wp_reset_postdata();
    }
}

// ------------------------------
// Navegación: Tramo anterior/siguiente
// ------------------------------
$tramo_anterior = [];
$tramo_siguiente = [];

// Intentar obtener lista de tramos de caché
$cache_key_tramos = 'todos_tramos_ids';
$tramos_ids = get_transient($cache_key_tramos);

if (false === $tramos_ids) {
    // Caché no existe, ejecutar query
    $todos_tramos_query = new WP_Query([
        'post_type' => 'tramo',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'meta_value',
        'meta_key' => 'fecha_inicio_tramo',
        'order' => 'ASC',
    ]);
    
    $tramos_ids = [];
    if ($todos_tramos_query->have_posts()) {
        while ($todos_tramos_query->have_posts()) {
            $todos_tramos_query->the_post();
            $tramos_ids[] = get_the_ID();
        }
        wp_reset_postdata();
    }
    
    // Guardar en caché por 24 horas
    set_transient($cache_key_tramos, $tramos_ids, 24 * HOUR_IN_SECONDS);
    error_log("✅ Caché creada para navegación tramos");
} else {
    error_log("⚡ Caché usada para navegación tramos");
}

$posicion_actual = array_search($tramo_id, $tramos_ids);

if ($posicion_actual !== false) {
    // Tramo anterior
    if ($posicion_actual > 0) {
        $tramo_ant_id = $tramos_ids[$posicion_actual - 1];
        $tramo_anterior = [
            'url' => get_permalink($tramo_ant_id),
            'titulo' => get_the_title($tramo_ant_id),
        ];
    }
    
    // Tramo siguiente
    if ($posicion_actual < count($tramos_ids) - 1) {
        $tramo_sig_id = $tramos_ids[$posicion_actual + 1];
        $tramo_siguiente = [
            'url' => get_permalink($tramo_sig_id),
            'titulo' => get_the_title($tramo_sig_id),
        ];
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($tramo_titulo . ' - ' . get_bloginfo('name')); ?></title>
    
    <!-- Preconnect Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <!-- Preconnect Google Maps -->
    <link rel="preconnect" href="https://maps.googleapis.com">
    <link rel="preconnect" href="https://maps.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://maps.googleapis.com">
    <link rel="dns-prefetch" href="https://maps.gstatic.com">
    
    <style>
        /* === RESET Y BASE === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Spectral', serif;
            background: #fff;
            color: #1a1a1a;
            overflow: hidden;
        }

        /* === FULLSCREEN LAYOUT === */
        .tramo-page {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #fff;
            z-index: 99999;
        }

        .tramo-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* === BREADCRUMBS === */
        .breadcrumbs {
            position: absolute;
            top: 0;
            left: 0;
            right: 50%;
            padding: 20px 40px;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.95rem;
            color: #6b7280;
            z-index: 10;
        }

        .breadcrumbs a {
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumbs a:hover {
            color: #dc2626;
        }

        .breadcrumbs .current {
            color: #1a1a1a;
            font-weight: 600;
        }

        .breadcrumbs .separator {
            margin: 0 8px;
            color: #d1d5db;
        }

        .breadcrumb-search {
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 400;
        }

        .breadcrumb-search:hover {
            color: #1a1a1a;
        }

        /* === COLUMNA DE INFORMACIÓN (50%) === */
        .info-column {
            width: 50%;
            height: 100vh;
            overflow-y: auto;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 100px 60px 80px;
            position: relative;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: #999 #ebebeb;
        }

        .info-column::-webkit-scrollbar {
            width: 10px;
        }

        .info-column::-webkit-scrollbar-track {
            background: #ebebeb;
        }

        .info-column::-webkit-scrollbar-thumb {
            background-color: #999;
            border-radius: 999px;
            border: 2px solid #ebebeb;
        }

        .info-column::-webkit-scrollbar-thumb:hover {
            background-color: #1a1a1a;
        }

        .info-content {
            max-width: 700px;
            width: 100%;
        }

        /* === BLOQUE CAPÍTULO (opcional) === */
        .bloque-capitulo {
            text-align: center;
            margin-bottom: 50px;
        }

        .nombre-capitulo {
            font-weight: 600;
            font-size: 1.2rem;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .capitulo-prefix {
            font-weight: 400;
            opacity: 0.7;
            margin-right: 8px;
        }

        .capitulo-metadatos {
            display: flex;
            justify-content: center;
            gap: 40px;
        }

        .capitulo-meta-col {
            text-align: center;
        }

        .capitulo-meta-label {
            font-size: 0.7rem;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .capitulo-meta-lugar,
        .capitulo-meta-fecha {
            font-style: italic;
            font-size: 0.9rem;
            color: #999;
            line-height: 1.4;
        }

        /* === TÍTULO DEL TRAMO === */
        .titulo-tramo {
            font-weight: 300;
            font-size: 3.5rem;
            color: #1a1a1a;
            text-align: center;
            line-height: 1.1;
            margin: 0 0 40px;
        }

        /* === METADATOS DEL TRAMO === */
        .metadatos-tramo {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-bottom: 40px;
        }

        .meta-col {
            text-align: center;
        }

        .meta-label {
            font-size: 0.75rem;
            color: #333;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1.5px;
            font-weight: 600;
            text-decoration: underline;
        }

        .meta-lugar {
            font-style: italic;
            font-size: 1.8rem;
            color: #1a1a1a;
            margin-bottom: 4px;
            font-weight: 400;
        }

        .meta-fecha {
            font-style: italic;
            font-size: 1.8rem;
            color: #1a1a1a;
            font-weight: 400;
        }

        /* === DESCRIPCIÓN === */
        .descripcion-tramo {
            font-weight: 400;
            font-size: 1.2rem;
            color: #555;
            line-height: 2;
            text-align: center;
            margin: 0 0 8px;
            max-height: 100px;
            overflow: hidden;
            position: relative;
            transition: max-height 0.4s ease;
        }

        .descripcion-tramo.expandido {
            max-height: 2000px;
        }

        .leer-mas-link {
            display: block;
            text-align: center;
            font-style: italic;
            font-size: 1.1rem;
            color: #2563eb;
            cursor: pointer;
            margin: 0 0 48px;
            transition: color 0.2s ease;
        }

        .leer-mas-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .leer-mas-link.oculto {
            display: none;
        }

        /* === BOTÓN CTA === */
        .boton-descubrir {
            display: inline-block;
            font-family: 'Spectral', serif;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1a1a1a;
            background: transparent;
            border: 2px solid #1a1a1a;
            padding: 16px 50px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 4px;
        }

        .boton-descubrir:hover {
            background: #1a1a1a;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .boton-descubrir:disabled,
        .boton-descubrir.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* === COLUMNA DEL MAPA (50%) === */
        .mapa-column {
            width: 50%;
            height: 100vh;
            position: relative;
        }

        #mapa-tramo {
            width: 100%;
            height: 100%;
        }

        .mapa-mensaje {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #999;
            background: #f5f5f5;
        }

        /* === BOTONES DE NAVEGACIÓN === */
        .nav-btn {
            position: fixed;
            bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: #1a1a1a;
            border: 1px solid #1a1a1a;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100000;
            font-family: 'Spectral', serif;
            color: #fff;
            border-radius: 6px;
        }

        .nav-btn:hover {
            background: transparent;
            transform: scale(1.05);
        }

        .nav-btn:hover .nav-label,
        .nav-btn:hover .nav-arrow {
            color: #1a1a1a;
        }

        .nav-btn-disabled {
            opacity: 0.35;
            cursor: not-allowed;
            pointer-events: none;
        }

        .nav-btn-prev {
            right: calc(50% + 8px);
        }

        .nav-btn-next {
            left: calc(50% + 8px);
        }

        .nav-label {
            display: flex;
            flex-direction: column;
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: 1.2px;
            line-height: 1.2;
            text-align: center;
        }

        .nav-arrow {
            font-size: 1.8rem;
            line-height: 1;
            color: #fff;
            transition: color 0.3s ease;
        }

        /* === PANEL AUTOPLAY === */
        .autoplay-panel {
            position: fixed;
            bottom: 80px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #1a1a1a;
            border-radius: 8px;
            padding: 12px;
            z-index: 100001;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .control-btn {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid #1a1a1a;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Spectral', serif;
        }

        .control-btn:hover:not(:disabled) {
            background: #1a1a1a;
            transform: scale(1.05);
        }

        .control-btn:hover:not(:disabled) .control-icon {
            color: #fff;
        }

        .control-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .control-icon {
            font-size: 18px;
            color: #1a1a1a;
            transition: color 0.2s ease;
        }

        .autoplay-status {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 0.75rem;
            color: #666;
            min-height: 20px;
        }

        #autoplay-counter {
            font-weight: 600;
            color: #dc2626;
        }

        /* === RESPONSIVE MÓVIL === */
        @media (max-width: 768px) {
            body {
                overflow: hidden;
            }

            .tramo-page {
                position: fixed;
                height: 100vh;
            }

            .tramo-container {
                flex-direction: column;
                height: 100vh;
            }

            .breadcrumbs {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                padding: 8px 15px;
                font-size: 0.7rem;
                z-index: 10;
            }

            .info-column {
                width: 100%;
                height: 60vh;
                min-height: auto;
                overflow-y: auto;
                padding: 45px 20px 80px 20px;
                justify-content: flex-start;
            }

            .mapa-column {
                width: 100%;
                height: 40vh;
                position: relative;
            }

            /* Bloque capítulo muy compacto */
            .bloque-capitulo {
                margin-bottom: 20px;
            }

            .nombre-capitulo {
                font-size: 0.8rem;
                margin-bottom: 8px;
            }

            .capitulo-metadatos {
                flex-direction: row;
                gap: 20px;
            }

            .capitulo-meta-label {
                font-size: 0.6rem;
                margin-bottom: 4px;
            }

            .capitulo-meta-lugar,
            .capitulo-meta-fecha {
                font-size: 0.7rem;
            }

            /* Título tramo más pequeño */
            .titulo-tramo {
                font-size: 1.6rem;
                margin-bottom: 20px;
            }

            /* Metadatos muy compactos */
            .metadatos-tramo {
                flex-direction: row;
                gap: 25px;
                margin-bottom: 20px;
            }

            .meta-label {
                font-size: 0.6rem;
                margin-bottom: 6px;
            }

            .meta-lugar {
                font-size: 1rem;
            }

            .meta-fecha {
                font-size: 0.85rem;
            }

            /* Descripción compacta */
            .descripcion-tramo {
                font-size: 0.9rem;
                line-height: 1.5;
                max-height: 80px;
                margin-bottom: 8px;
            }

            .leer-mas-link {
                font-size: 0.85rem;
                margin-bottom: 20px;
            }

            /* Botón descubrir más pequeño */
            .boton-descubrir {
                font-size: 0.7rem;
                padding: 10px 24px;
                letter-spacing: 1.5px;
            }

            /* Botones navegación */
            .nav-btn {
                position: fixed;
                bottom: 10px;
                padding: 8px 14px;
                gap: 6px;
                z-index: 100000;
            }

            .nav-btn-prev {
                right: calc(50% + 5px);
            }

            .nav-btn-next {
                left: calc(50% + 5px);
            }

            .nav-label {
                font-size: 0.6rem;
            }

            .nav-arrow {
                font-size: 1.3rem;
            }

            /* Panel autoplay */
            .autoplay-panel {
                bottom: 65px;
                right: 8px;
                padding: 6px;
                gap: 5px;
            }

            .control-btn {
                width: 34px;
                height: 34px;
            }

            .control-icon {
                font-size: 14px;
            }
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="tramo-page">
    <!-- BREADCRUMBS -->
    <div class="breadcrumbs">
        <a href="<?php echo esc_url(home_url('/timeline')); ?>">Timeline</a>
        <span class="separator">›</span>
        <span class="current"><?php echo esc_html($tramo_titulo); ?></span>
        <span class="separator">›</span>
        <a href="<?php echo esc_url(home_url('/buscador-global')); ?>" class="breadcrumb-search" title="Buscar en el viaje">🔍 Buscar</a>
    </div>

    <div class="tramo-container">
        <!-- COLUMNA IZQUIERDA: INFORMACIÓN -->
        <div class="info-column">
            <div class="info-content">
                <!-- BLOQUE CAPÍTULO (si existe) -->
                <?php if ($capitulo_nombre): ?>
                    <div class="bloque-capitulo">
                        <div class="nombre-capitulo">
                            <span class="capitulo-prefix">Capítulo:</span> <?php echo esc_html(strtoupper($capitulo_nombre)); ?>
                        </div>
                        <div class="capitulo-metadatos">
                            <div class="capitulo-meta-col">
                                <div class="capitulo-meta-label">INICIO</div>
                                <div class="capitulo-meta-lugar"><?php echo esc_html($capitulo_sitio_inicio); ?></div>
                                <div class="capitulo-meta-fecha"><?php echo esc_html($capitulo_fecha_inicio); ?></div>
                            </div>
                            <div class="capitulo-meta-col">
                                <div class="capitulo-meta-label">FIN</div>
                                <div class="capitulo-meta-lugar"><?php echo esc_html($capitulo_sitio_fin); ?></div>
                                <div class="capitulo-meta-fecha"><?php echo esc_html($capitulo_fecha_fin); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- TÍTULO DEL TRAMO -->
                <h1 class="titulo-tramo"><?php echo esc_html($tramo_titulo); ?></h1>

                <!-- METADATOS: INICIO/FIN -->
                <div class="metadatos-tramo">
                    <div class="meta-col">
                        <div class="meta-label">INICIO</div>
                        <div class="meta-lugar"><?php echo esc_html($sitio_inicio_tramo); ?></div>
                        <div class="meta-fecha"><?php echo esc_html($fecha_inicio_tramo); ?></div>
                    </div>
                    <div class="meta-col">
                        <div class="meta-label">FIN</div>
                        <div class="meta-lugar"><?php echo esc_html($sitio_fin_tramo); ?></div>
                        <div class="meta-fecha"><?php echo esc_html($fecha_fin_tramo); ?></div>
                    </div>
                </div>

                <!-- DESCRIPCIÓN -->
                <?php if ($descripcion_tramo): ?>
                    <div class="descripcion-tramo" id="descripcion-tramo">
                        <?php echo wp_kses_post(wpautop($descripcion_tramo)); ?>
                    </div>
                    <span id="leer-mas-link" class="leer-mas-link oculto">Leer más...</span>
                <?php endif; ?>

                <!-- BOTÓN DESCUBRIR -->
                <div style="text-align: center;">
                    <?php if ($primer_dia_url): ?>
                        <a href="<?php echo esc_url($primer_dia_url); ?>" class="boton-descubrir">Descubrir el Tour</a>
                    <?php else: ?>
                        <span class="boton-descubrir disabled">Descubrir el Tour</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: MAPA -->
        <div class="mapa-column">
            <?php if (!empty($dias_tramo)): ?>
                <div id="mapa-tramo"></div>
            <?php else: ?>
                <div class="mapa-mensaje">🗺️ Mapa no disponible</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BOTONES DE NAVEGACIÓN -->
    <?php if (!empty($tramo_anterior)): ?>
        <a href="<?php echo esc_url($tramo_anterior['url']); ?>" class="nav-btn nav-btn-prev" aria-label="Tramo anterior">
            <span class="nav-arrow" aria-hidden="true">↑</span>
            <div class="nav-label"><span>PREV</span><span>TRAMO</span></div>
        </a>
    <?php else: ?>
        <span class="nav-btn nav-btn-prev nav-btn-disabled" aria-label="Tramo anterior" aria-disabled="true">
            <span class="nav-arrow" aria-hidden="true">↑</span>
            <div class="nav-label"><span>PREV</span><span>TRAMO</span></div>
        </span>
    <?php endif; ?>

    <?php if (!empty($tramo_siguiente)): ?>
        <a href="<?php echo esc_url($tramo_siguiente['url']); ?>" class="nav-btn nav-btn-next" aria-label="Tramo siguiente">
            <div class="nav-label"><span>NEXT</span><span>TRAMO</span></div>
            <span class="nav-arrow" aria-hidden="true">↓</span>
        </a>
    <?php else: ?>
        <span class="nav-btn nav-btn-next nav-btn-disabled" aria-label="Tramo siguiente" aria-disabled="true">
            <div class="nav-label"><span>NEXT</span><span>TRAMO</span></div>
            <span class="nav-arrow" aria-hidden="true">↓</span>
        </span>
    <?php endif; ?>

    <!-- PANEL AUTOPLAY -->
    <div class="autoplay-panel">
        <button type="button" id="btn-play" class="control-btn" title="Reproducir (adelante)" aria-label="Play">
            <span class="control-icon">▶</span>
        </button>
        <button type="button" id="btn-stop" class="control-btn" disabled title="Detener" aria-label="Stop">
            <span class="control-icon">⏹</span>
        </button>
        <button type="button" id="btn-back" class="control-btn" title="Reproducir (atrás)" aria-label="Atrás">
            <span class="control-icon">◀</span>
        </button>
        <div class="autoplay-status">
            <span id="autoplay-counter"></span>
        </div>
    </div>
</div>

<script>
    // ==========================================
    // CONTROLES DE AUTOPLAY
    // ==========================================
    (function() {
        var btnPlay = document.getElementById('btn-play');
        var btnStop = document.getElementById('btn-stop');
        var btnBack = document.getElementById('btn-back');
        var autoplayCounter = document.getElementById('autoplay-counter');
        
        var autoplayTimer = null;
        var autoplayIntervalId = null;
        var autoplayInterval = 5000; // 5 segundos
        var isPlaying = false;
        var countdown = 0;
        var playDirection = 'forward'; // 'forward' o 'backward'
        
        function updateControlStates(playing) {
            isPlaying = playing;
            btnPlay.disabled = playing && playDirection === 'forward';
            btnBack.disabled = playing && playDirection === 'backward';
            btnStop.disabled = !playing;
        }
        
        function startAutoplay(direction) {
            if (isPlaying) return;
            
            playDirection = direction || 'forward';
            console.log('▶️ Autoplay iniciado (' + playDirection + ')');
            
            // Guardar estado en localStorage
            localStorage.setItem('autoTramosActive', 'true');
            localStorage.setItem('autoTramosDir', playDirection);
            
            updateControlStates(true);
            
            countdown = autoplayInterval / 1000;
            autoplayCounter.textContent = countdown + 's';
            
            // Countdown cada segundo
            autoplayIntervalId = setInterval(function() {
                countdown--;
                if (countdown > 0) {
                    autoplayCounter.textContent = countdown + 's';
                }
            }, 1000);
            
            autoplayTimer = setTimeout(function() {
                clearInterval(autoplayIntervalId);
                
                // Navegar según dirección
                var targetBtn = playDirection === 'forward' 
                    ? document.querySelector('.nav-btn-next:not(.nav-btn-disabled)')
                    : document.querySelector('.nav-btn-prev:not(.nav-btn-disabled)');
                
                if (targetBtn && targetBtn.href) {
                    console.log('➡️ Autoplay: navegando al siguiente tramo');
                    window.location.href = targetBtn.href;
                } else {
                    var mensaje = playDirection === 'forward' 
                        ? '✅ Has llegado al último tramo del viaje'
                        : '✅ Has llegado al primer tramo del viaje';
                    console.log('⏹️ Autoplay detenido: fin alcanzado');
                    stopAutoplay();
                    alert(mensaje);
                }
            }, autoplayInterval);
        }
        
        function stopAutoplay() {
            if (!isPlaying) return;
            
            console.log('⏹️ Autoplay detenido');
            
            // Limpiar localStorage
            localStorage.removeItem('autoTramosActive');
            localStorage.removeItem('autoTramosDir');
            
            clearTimeout(autoplayTimer);
            clearInterval(autoplayIntervalId);
            playDirection = 'forward';
            updateControlStates(false);
            autoplayCounter.textContent = '';
        }
        
        // Event listeners
        if (btnPlay) btnPlay.addEventListener('click', function() {
            startAutoplay('forward');
        });
        if (btnStop) btnStop.addEventListener('click', stopAutoplay);
        if (btnBack) btnBack.addEventListener('click', function() {
            startAutoplay('backward');
        });
        
        // Reanudar autoplay si estaba activo
        if (localStorage.getItem('autoTramosActive') === 'true') {
            var savedDirection = localStorage.getItem('autoTramosDir') || 'forward';
            console.log('🔄 Reanudando autoplay (' + savedDirection + ')');
            setTimeout(function() {
                startAutoplay(savedDirection);
            }, 500);
        }
    })();
</script>

<?php if (!empty($dias_tramo)): ?>
<script>
    // ==========================================
    // SISTEMA "LEER MÁS..." PARA DESCRIPCIÓN
    // ==========================================
    (function() {
        var descripcion = document.getElementById('descripcion-tramo');
        var leerMasLink = document.getElementById('leer-mas-link');
        
        if (!descripcion || !leerMasLink) return;
        
        // Verificar si el contenido excede la altura
        if (descripcion.scrollHeight > 100) {
            leerMasLink.classList.remove('oculto');
        }
        
        leerMasLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (descripcion.classList.contains('expandido')) {
                // Cerrar
                descripcion.classList.remove('expandido');
                leerMasLink.textContent = 'Leer más...';
                setTimeout(function() {
                    descripcion.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            } else {
                // Expandir
                descripcion.classList.add('expandido');
                leerMasLink.textContent = 'Cerrar';
            }
        });
    })();

    // ==========================================
    // INICIALIZACIÓN DEL MAPA
    // ==========================================
    window.diasTramoData = <?php echo wp_json_encode($dias_tramo); ?>;
    
    function initMapTramo() {
        var dias = window.diasTramoData;
        
        if (!dias || dias.length === 0) {
            console.error('❌ No hay días para mostrar en el mapa');
            return;
        }
        
        console.log('🗺️ Inicializando mapa con', dias.length, 'días del tramo');
        
        // Estilos del mapa
        var mapStyle = [
            { featureType: 'poi', stylers: [{ visibility: 'off' }] },
            { featureType: 'transit', stylers: [{ visibility: 'off' }] },
        ];
        
        // Crear mapa centrado en el primer día
        var map = new google.maps.Map(document.getElementById('mapa-tramo'), {
            zoom: 8,
            center: { lat: dias[0].lat, lng: dias[0].lng },
            mapTypeId: 'roadmap',
            disableDefaultUI: true,
            styles: mapStyle,
        });
        
        // Crear bounds para ajustar vista
        var bounds = new google.maps.LatLngBounds();
        
        // Icono para marcadores
        var iconoDia = {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: '#DC2626',
            fillOpacity: 1,
            strokeColor: '#FFFFFF',
            strokeWeight: 3,
        };
        
        // Crear marcadores para cada día
        dias.forEach(function(dia) {
            var marker = new google.maps.Marker({
                position: { lat: dia.lat, lng: dia.lng },
                map: map,
                icon: iconoDia,
                title: dia.title,
                optimized: false,
                cursor: 'pointer'
            });
            
            // Añadir InfoWindow al hacer hover
            var contenidoInfo = '<div style="font-family: Spectral, serif; min-width: 200px; max-width: 280px; text-align: center; padding: 10px;">';
            
            // Número de día
            contenidoInfo += '<div style="font-size: 0.7rem; font-weight: 600; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">';
            contenidoInfo += 'DÍA ' + dia.dia_numero;
            contenidoInfo += '</div>';
            
            // Nombre del sitio
            contenidoInfo += '<div style="font-size: 1.2rem; font-weight: 600; color: #1a1a1a; margin-bottom: 6px; padding-bottom: 6px; border-bottom: 2px solid #dc2626;">';
            contenidoInfo += dia.title;
            contenidoInfo += '</div>';
            
            // Fecha
            if (dia.fecha) {
                contenidoInfo += '<div style="font-size: 0.8rem; color: #666; margin-bottom: 8px; font-style: italic;">';
                contenidoInfo += '📅 ' + dia.fecha;
                contenidoInfo += '</div>';
            }
            
            // Hito del día
            if (dia.hito) {
                contenidoInfo += '<div style="font-size: 0.85rem; color: #555; line-height: 1.4; margin-bottom: 10px; padding: 8px; background: #f9fafb; border-radius: 4px;">';
                contenidoInfo += dia.hito;
                contenidoInfo += '</div>';
            }
            
            // Mensaje para click
            contenidoInfo += '<div style="font-size: 0.75rem; color: #2563eb; margin-top: 8px; font-style: italic; font-weight: 600;">';
            contenidoInfo += '👆 Click para ver detalles';
            contenidoInfo += '</div>';
            
            contenidoInfo += '</div>';
            
            var infoWindow = new google.maps.InfoWindow({
                content: contenidoInfo
            });
            
            marker.addListener('mouseover', function() {
                infoWindow.open(map, marker);
            });
            
            marker.addListener('mouseout', function() {
                infoWindow.close();
            });
            
            // Click para navegar al día
            marker.addListener('click', function() {
                if (dia.url) {
                    console.log('📍 Navegando a día:', dia.title, '(Día ' + dia.dia_numero + ')');
                    window.location.href = dia.url;
                } else {
                    console.warn('⚠️ No hay URL para el día:', dia.title);
                }
            });
            
            bounds.extend(marker.getPosition());
        });
        
        // Crear polyline conectando todos los días
        var pathCoords = dias.map(function(dia) {
            return { lat: dia.lat, lng: dia.lng };
        });
        
        var polyline = new google.maps.Polyline({
            path: pathCoords,
            geodesic: true,
            strokeColor: '#DC2626',
            strokeOpacity: 0.8,
            strokeWeight: 3,
            map: map,
        });
        
        // Ajustar vista para mostrar todos los marcadores
        if (dias.length > 1) {
            map.fitBounds(bounds, {
                top: 50,
                right: 50,
                bottom: 50,
                left: 50,
            });
            
            // Limitar zoom máximo
            google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
                var zoomActual = map.getZoom();
                if (zoomActual > 12) {
                    map.setZoom(12);
                }
            });
        } else {
            map.setCenter({ lat: dias[0].lat, lng: dias[0].lng });
            map.setZoom(10);
        }
        
        console.log('✅ Mapa inicializado correctamente');
    }
</script>
<script>
// ==========================================
// LAZY LOAD DE GOOGLE MAPS
// ==========================================
(function() {
    var mapaEl = document.getElementById('mapa-tramo');
    if (!mapaEl) return;
    
    var mapsLoaded = false;
    
    function cargarGoogleMaps() {
        if (mapsLoaded) return;
        mapsLoaded = true;
        
        console.log('🗺️ Cargando Google Maps (lazy load)...');
        
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAr-0laMZAuIYHZGPikz99ITdYvcC5ye0A&callback=initMapTramo';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }
    
    // Cargar cuando el mapa entre en viewport
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting) {
                cargarGoogleMaps();
                observer.disconnect();
            }
        }, {
            rootMargin: '200px' // Empezar a cargar 200px antes
        });
        
        observer.observe(mapaEl);
    } else {
        // Fallback: cargar después de 1 segundo
        setTimeout(cargarGoogleMaps, 1000);
    }
})();
</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
<?php
// ------------------------------
// HOOKS PARA LIMPIAR CACHÉ
// ------------------------------
// Limpiar caché cuando se actualiza un día o tramo
function limpiar_cache_tramos($post_id) {
    $post_type = get_post_type($post_id);
    
    if ($post_type === 'dia') {
        // Limpiar caché del tramo asociado
        $id_tramo = get_field('id_tramo', $post_id);
        if ($id_tramo) {
            delete_transient('dias_tramo_' . $id_tramo);
            error_log("🗑️ Caché eliminada para tramo: {$id_tramo}");
        }
    }
    
    if ($post_type === 'tramo') {
        // Limpiar caché de navegación
        delete_transient('todos_tramos_ids');
        error_log("🗑️ Caché eliminada para navegación tramos");
    }
}
add_action('save_post', 'limpiar_cache_tramos');
add_action('delete_post', 'limpiar_cache_tramos');
?>
// ------------------------------
// HOOKS PARA LIMPIAR CACHÉ
// ------------------------------
// Limpiar caché cuando se actualiza un día o tramo
function limpiar_cache_tramos($post_id) {
    $post_type = get_post_type($post_id);
    
    if ($post_type === 'dia') {
        // Limpiar caché del tramo asociado
        $id_tramo = get_field('id_tramo', $post_id);
        if ($id_tramo) {
            delete_transient('dias_tramo_' . $id_tramo);
            error_log("🗑️ Caché eliminada para tramo: {$id_tramo}");
        }
    }
    
    if ($post_type === 'tramo') {
        // Limpiar caché de navegación
        delete_transient('todos_tramos_ids');
        error_log("🗑️ Caché eliminada para navegación tramos");
    }
}
add_action('save_post', 'limpiar_cache_tramos');
add_action('delete_post', 'limpiar_cache_tramos');
?>