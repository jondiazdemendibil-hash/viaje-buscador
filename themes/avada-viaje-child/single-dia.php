<?php
/**
 * Template Name: Single Día
 * Template Post Type: dia
 * 
 * Plantilla completa para mostrar un día de viaje con:
 * - Información detallada del día (sitio, fecha, hito, relato)
 * - Mapa principal de Google Maps con marcadores del día anterior/actual/siguiente
 * - Mini-mapa contextual mostrando la ruta completa (1001 días)
 * - Navegación cross-tramo con animación cinematográfica
 * - Controles de autoplay bidireccional (Play/Stop/Back)
 * - Responsive: diseño adaptado para desktop y móvil
 * 
 * OPTIMIZACIONES APLICADAS:
 * 
 * [MEJORA 1] Validación de coordenadas
 *   - Función validar_coordenadas() en línea ~26
 *   - Valida rangos geográficos: lat (-90/90), lng (-180/180)
 *   - Fallback a Madrid (40.4168, -3.7038) si coordenadas inválidas
 *   - Aplica a: día anterior, actual, siguiente, mini-mapa
 * 
 * [MEJORA 2] Preconnect Google Maps
 *   - Links en <head> (línea ~349)
 *   - Reduce latencia DNS/TCP/TLS en ~200-400ms
 *   - Dominios: maps.googleapis.com, maps.gstatic.com
 * 
 * [MEJORA 3] Timeout de seguridad animaciones
 *   - Variable animacionEnProgreso con timeout de 5s (línea ~1720)
 *   - Auto-recuperación si error durante navegación
 *   - Previene bloqueo permanente de botones
 * 
 * [MEJORA 4] Lazy loading mini-mapa
 *   - Mini-mapa se crea solo al expandir (línea ~2018)
 *   - Ahorra ~300-500ms en carga inicial
 *   - Desktop: expandido por defecto
 *   - Móvil: colapsado por defecto
 * 
 * [MEJORA 5] Debouncing de navegación
 *   - Variable animacionEnProgreso bloquea clics múltiples (línea ~1703)
 *   - Previene navegación errática
 *   - Feedback visual con clase .nav-btn-animating
 * 
 * [MEJORA 6] will-change CSS
 *   - Propiedades: transform, opacity, background-color, box-shadow
 *   - Optimiza rendering con GPU en animaciones hover/transitions
 *   - Aplica a: .nav-btn, .control-btn, .toggle-minimapa, .panel.active
 * 
 * @version 2.0
 * @last-updated 2025-12-22
 */

// ------------------------------
// Helpers
// ------------------------------
function viaje_parse_acf_date_to_timestamp($raw) {
    if (!$raw) {
        return 0;
    }

    // ACF date can be Ymd (e.g. 20260403) or Y-m-d.
    if (is_string($raw) && preg_match('/^\d{8}$/', $raw)) {
        $dt = DateTime::createFromFormat('Ymd', $raw);
        return $dt ? (int) $dt->getTimestamp() : 0;
    }

    $ts = strtotime((string) $raw);
    return $ts ? (int) $ts : 0;
}

// Función para validar coordenadas geográficas
function validar_coordenadas($lat, $lng) {
    $lat = (float) $lat;
    $lng = (float) $lng;
    
    // Validar que sean números válidos
    if (empty($lat) || empty($lng) || !is_numeric($lat) || !is_numeric($lng)) {
        return false;
    }
    
    // Validar rangos geográficos correctos
    // Latitud: -90 a 90, Longitud: -180 a 180
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return false;
    }
    
    return true;
}

// ------------------------------
// Obtener datos del día actual
// ------------------------------
$dia_id = get_the_ID();

$id_tramo = get_field('id_tramo', $dia_id);
$dia_numero = (int) get_field('dia', $dia_id);
$fecha_dia_raw = get_field('fecha_dia', $dia_id);
$sitio_dia = get_field('sitio_dia', $dia_id);
$pais_dia = get_field('pais_dia', $dia_id);
$hito_dia = get_field('hito_dia', $dia_id);
$relato_dia = get_field('relato_dia', $dia_id);
$latitud = get_field('latitud', $dia_id);
$longitud = get_field('longitud', $dia_id);
$altitud = get_field('altitud', $dia_id);
$tipo_dia = get_field('tipo_dia', $dia_id); // Tipo: 'navegacion' u otro

// Determinar emoji según tipo de día
$emoji_dia = ''; // Sin emoji por defecto
if ($tipo_dia === 'navegacion') {
    $emoji_dia = '⚓ '; // Ancla + espacio para días de navegación
}

$fecha_ts = viaje_parse_acf_date_to_timestamp($fecha_dia_raw);
$fecha_dia_formatted = $fecha_ts ? date_i18n('l, d \d\e F \d\e Y', $fecha_ts) : '';

$total_dias = 1001;
$porcentaje_progreso = ($dia_numero > 0 && $total_dias > 0) ? round(($dia_numero / $total_dias) * 100, 2) : 0;

// ------------------------------
// Obtener datos del tramo
// ------------------------------
$tramo_nombre = '';
$tramo_sitio_inicio = '';
$tramo_fecha_inicio = '';
$tramo_sitio_fin = '';
$tramo_fecha_fin = '';
$tramo_url = '#';

if ($id_tramo) {
    $tramo_posts = get_posts([
        'post_type' => 'tramo',
        'meta_key' => 'id_tramo',
        'meta_value' => $id_tramo,
        'posts_per_page' => 1,
        'post_status' => 'publish',
    ]);

    if ($tramo_posts) {
        $tramo_post = $tramo_posts[0];
        $tramo_nombre = $tramo_post->post_title;
        $tramo_url = get_permalink($tramo_post->ID);

        $tramo_sitio_inicio = get_field('sitio_inicio_tramo', $tramo_post->ID);
        $tramo_fecha_inicio_raw = get_field('fecha_inicio_tramo', $tramo_post->ID);
        $tramo_sitio_fin = get_field('sitio_fin_tramo', $tramo_post->ID);
        $tramo_fecha_fin_raw = get_field('fecha_fin_tramo', $tramo_post->ID);

        $tramo_inicio_ts = viaje_parse_acf_date_to_timestamp($tramo_fecha_inicio_raw);
        $tramo_fin_ts = viaje_parse_acf_date_to_timestamp($tramo_fecha_fin_raw);

        $tramo_fecha_inicio = $tramo_inicio_ts ? date_i18n('l, d \d\e F \d\e Y', $tramo_inicio_ts) : '';
        $tramo_fecha_fin = $tramo_fin_ts ? date_i18n('l, d \d\e F \d\e Y', $tramo_fin_ts) : '';
    }
}

// ------------------------------
// Día anterior / siguiente (por número absoluto del viaje)
// ------------------------------
$dia_anterior = [];
$dia_siguiente = [];

$show_prev = ($dia_numero > 1);
$show_next = ($dia_numero > 0 && $dia_numero < $total_dias);

if ($dia_numero > 0) {
    // ==========================================
    // OBTENER DÍA ANTERIOR (cross-tramo)
    // ==========================================
    if ($show_prev) {
    $dia_anterior_query = new WP_Query([
        'post_type' => 'dia',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'meta_query' => [
            ['key' => 'dia', 'value' => ($dia_numero - 1), 'type' => 'NUMERIC'],
        ],
    ]);

    if ($dia_anterior_query->have_posts()) {
        $dia_anterior_query->the_post();
        $fecha_anterior_raw = get_field('fecha_dia');
        $fecha_anterior_ts = viaje_parse_acf_date_to_timestamp($fecha_anterior_raw);
        $fecha_anterior_formatted = $fecha_anterior_ts ? date_i18n('l, d \d\e F \d\e Y', $fecha_anterior_ts) : '';
        
        $lat_ant = (float) get_field('latitud');
        $lng_ant = (float) get_field('longitud');
        
        // Validar coordenadas del día anterior
        if (!validar_coordenadas($lat_ant, $lng_ant)) {
            error_log("⚠️ DÍA ANTERIOR: Coordenadas inválidas - lat: {$lat_ant}, lng: {$lng_ant}");
            $lat_ant = 40.4168;
            $lng_ant = -3.7038;
        }
        
        $dia_anterior = [
            'url' => get_permalink(),
            'sitio' => get_field('sitio_dia'),
            'tramo' => get_field('nombre_tramo'),
            'hito' => get_field('hito_dia'),
            'fecha' => $fecha_anterior_formatted,
            'lat' => $lat_ant,
            'lng' => $lng_ant,
        ];
        wp_reset_postdata();
    }
    }

    // ==========================================
    // OBTENER DÍA SIGUIENTE (cross-tramo)
    // ==========================================
    if ($show_next) {
    $dia_siguiente_query = new WP_Query([
        'post_type' => 'dia',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'meta_query' => [
            ['key' => 'dia', 'value' => ($dia_numero + 1), 'type' => 'NUMERIC'],
        ],
    ]);

    if ($dia_siguiente_query->have_posts()) {
        $dia_siguiente_query->the_post();
        $fecha_siguiente_raw = get_field('fecha_dia');
        $fecha_siguiente_ts = viaje_parse_acf_date_to_timestamp($fecha_siguiente_raw);
        $fecha_siguiente_formatted = $fecha_siguiente_ts ? date_i18n('l, d \d\e F \d\e Y', $fecha_siguiente_ts) : '';
        
        $lat_sig = (float) get_field('latitud');
        $lng_sig = (float) get_field('longitud');
        
        // Validar coordenadas del día siguiente
        if (!validar_coordenadas($lat_sig, $lng_sig)) {
            error_log("⚠️ DÍA SIGUIENTE: Coordenadas inválidas - lat: {$lat_sig}, lng: {$lng_sig}");
            $lat_sig = 40.4168;
            $lng_sig = -3.7038;
        }
        
        $dia_siguiente = [
            'url' => get_permalink(),
            'sitio' => get_field('sitio_dia'),
            'tramo' => get_field('nombre_tramo'),
            'hito' => get_field('hito_dia'),
            'fecha' => $fecha_siguiente_formatted,
            'lat' => $lat_sig,
            'lng' => $lng_sig,
        ];
        wp_reset_postdata();
    }
    }
}

// ------------------------------
// Actividades / hospedajes / restaurantes
// ------------------------------
$id_dia_meta = get_field('id_dia', $dia_id);

$actividades = [];
$hospedajes = [];
$restaurantes = [];

if ($id_dia_meta) {
    $actividades_query = new WP_Query([
        'post_type' => 'actividad',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [['key' => 'id_dia', 'value' => $id_dia_meta]],
        'orderby' => 'meta_value_num',
        'meta_key' => 'orden_dia',
        'order' => 'ASC',
    ]);

    if ($actividades_query->have_posts()) {
        while ($actividades_query->have_posts()) {
            $actividades_query->the_post();
            $actividades[] = [
                'titulo' => get_the_title(),
                'descripcion' => get_field('descripcion_actividad'),
                'url' => get_field('url_actividad'),
            ];
        }
        wp_reset_postdata();
    }

    $hospedajes_query = new WP_Query([
        'post_type' => 'hospedaje',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [['key' => 'id_dia', 'value' => $id_dia_meta]],
        'orderby' => 'meta_value_num',
        'meta_key' => 'orden_hospedaje',
        'order' => 'ASC',
    ]);

    if ($hospedajes_query->have_posts()) {
        while ($hospedajes_query->have_posts()) {
            $hospedajes_query->the_post();
            $hospedajes[] = [
                'nombre' => get_the_title(),
                'tipo' => get_field('tipo_hospedaje'),
                'categoria' => get_field('categoria_hospedaje'),
                'descripcion' => get_field('descripcion_hospedaje'),
                'url' => get_field('url_hospedaje'),
            ];
        }
        wp_reset_postdata();
    }

    $restaurantes_query = new WP_Query([
        'post_type' => 'restaurante',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [['key' => 'id_dia', 'value' => $id_dia_meta]],
        'orderby' => 'meta_value_num',
        'meta_key' => 'orden_restaurante',
        'order' => 'ASC',
    ]);

    if ($restaurantes_query->have_posts()) {
        while ($restaurantes_query->have_posts()) {
            $restaurantes_query->the_post();
            $restaurantes[] = [
                'nombre' => get_the_title(),
                'tipo' => get_field('tipo_comida'),
                'categoria' => get_field('categoria_restaurante'),
                'url' => get_field('url_restaurante'),
            ];
        }
        wp_reset_postdata();
    }
}

// ------------------------------
// MEJORA 1: VALIDAR COORDENADAS
// ------------------------------
// Validar coordenadas del día actual (función ya definida arriba)
$coordenadas_validas = validar_coordenadas($latitud, $longitud);

if (!$coordenadas_validas) {
    error_log("⚠️ DÍA {$dia_numero}: Coordenadas inválidas - lat: {$latitud}, lng: {$longitud}");
    // Usar coordenadas por defecto (centro de España) si fallan
    $latitud = 40.4168;
    $longitud = -3.7038;
}

$map_data = [
    'anterior' => $dia_anterior,
    'actual' => [
        'url' => get_permalink($dia_id),
        'sitio' => $sitio_dia,
        'tramo' => get_field('nombre_tramo', $dia_id),
        'hito' => $hito_dia,
        'fecha' => $fecha_dia_formatted,
        'lat' => (float) $latitud,
        'lng' => (float) $longitud,
    ],
    'siguiente' => $dia_siguiente,
];

// ------------------------------
// Obtener TODOS los días del viaje para el mini-mapa
// ------------------------------
$todos_dias_query = new WP_Query([
    'post_type' => 'dia',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'meta_value_num',
    'meta_key' => 'dia',
    'order' => 'ASC',
]);

$todos_dias_coords = [];
if ($todos_dias_query->have_posts()) {
    while ($todos_dias_query->have_posts()) {
        $todos_dias_query->the_post();
        $lat_dia = (float) get_field('latitud');
        $lng_dia = (float) get_field('longitud');
        $dia_num = (int) get_field('dia');
        
        // MICRO-MEJORA A: Validar coordenadas usando la función helper
        if (validar_coordenadas($lat_dia, $lng_dia)) {
            $todos_dias_coords[] = [
                'dia' => $dia_num,
                'lat' => $lat_dia,
                'lng' => $lng_dia,
            ];
        } else {
            error_log("⚠️ Mini-mapa: Día {$dia_num} tiene coordenadas inválidas (lat: {$lat_dia}, lng: {$lng_dia})");
        }
    }
    wp_reset_postdata();
}

// ------------------------------
// Inyectar estilos + fuente en <head>
// ------------------------------
add_action('wp_head', function () {
    ?>
    <!-- Preconnect para Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <!-- MEJORA 2: Preconnect para Google Maps API -->
    <link rel="preconnect" href="https://maps.googleapis.com">
    <link rel="preconnect" href="https://maps.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://maps.googleapis.com">
    <link rel="dns-prefetch" href="https://maps.gstatic.com">
    
    <style>
        /* Fullscreen overlay (sin espacios del tema) */
        .dia-page {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #fff;
            z-index: 99999;
        }

        .dia-page * {
            box-sizing: border-box;
        }

        .dia-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        .info-column {
            width: 60%;
            height: 100vh;
            overflow: hidden;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .breadcrumbs {
            padding: 20px 40px;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.95rem;
            color: #6b7280;
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

        .tabs-container {
            background: transparent;
            border-bottom: 1px solid #e5e5e5;
            padding: 0 50px;
            position: relative;
        }

        .tabs {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .tab {
            font-family: 'Spectral', serif;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            color: #666;
            background: #f5f5f5;
            border: none;
            border-top: 3px solid transparent;
            border-radius: 8px 8px 0 0;
            padding: 14px 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            bottom: -1px;
        }

        .tab:hover {
            background: #ebebeb;
            color: #333;
        }

        .tab.active {
            color: #1a1a1a;
            font-weight: 600;
            background: #fff;
            border-top: 3px solid #dc2626;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.06);
        }

        .tab:focus {
            outline: none;
        }

        /* Botón hamburguesa (oculto en desktop) */
        .tabs-hamburger {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 16px;
            background: #f5f5f5;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            font-family: 'Spectral', serif;
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 8px 0;
        }

        .tabs-hamburger:hover {
            background: #ebebeb;
        }

        .hamburger-icon {
            font-size: 1.2rem;
            line-height: 1;
        }

        /* Overlay para cerrar menú */
        .menu-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-overlay.active {
            display: block;
            opacity: 1;
        }

        .info-content-wrapper {
            flex: 1;
            overflow-y: auto;
            padding: 60px 50px 30px 50px;
            scroll-behavior: smooth;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: #999 #ebebeb;
        }

        .info-content-wrapper::-webkit-scrollbar {
            width: 10px;
        }

        .info-content-wrapper::-webkit-scrollbar-track {
            background: #ebebeb;
        }

        .info-content-wrapper::-webkit-scrollbar-thumb {
            background-color: #999;
            border-radius: 999px;
            border: 2px solid #ebebeb;
        }

        .info-content-wrapper::-webkit-scrollbar-thumb:hover {
            background-color: #1a1a1a;
        }

        .info-content {
            max-width: 700px;
            margin: 0 auto;
        }

        .panel {
            display: none;
        }

        .panel.active {
            display: block;
            animation: panelFadeIn 240ms ease-out both;
            will-change: opacity, transform;
        }

        @keyframes panelFadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .panel.active {
                animation: none;
            }
        }

        .cabecera-tramo {
            text-align: center;
            margin-bottom: 50px;
        }

        .nombre-tramo {
            font-weight: 600;
            font-size: 1.2rem;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .tour-prefix {
            font-weight: 400;
            opacity: 0.7;
            margin-right: 8px;
        }

        .tramo-metadatos {
            display: flex;
            justify-content: center;
            gap: 40px;
        }

        .tramo-meta-col {
            text-align: center;
        }

        .tramo-meta-label {
            font-size: 0.65rem;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .tramo-meta-lugar,
        .tramo-meta-fecha {
            font-style: italic;
            font-size: 0.85rem;
            color: #999;
        }

        .sitio-dia {
            font-weight: 300;
            font-size: 2.6rem;
            color: #1a1a1a;
            text-align: center;
            line-height: 1.1;
            margin: 0 0 24px;
        }

        .fecha-dia {
            font-weight: 400;
            font-size: 2rem;
            color: #1a1a1a;
            text-align: center;
            margin: 0 0 28px;
        }

        .numero-dia {
            font-weight: 400;
            font-size: 1rem;
            color: #777;
            text-align: center;
            margin: 0 0 16px;
        }

        .coordenadas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin: 0 0 40px;
            gap: 20px;
        }

        .coord-item {
            font-family: 'Spectral', serif;
            font-size: 0.8rem;
            font-weight: 400;
            color: #666;
            letter-spacing: 0.5px;
        }

        .coord-label {
            font-weight: 600;
            color: #1a1a1a;
            margin-right: 4px;
        }

        .hito-dia {
            font-weight: 600;
            font-size: 1.2rem;
            color: #333;
            text-align: center;
            margin: 0 0 28px;
            line-height: 1.5;
        }

        .relato-dia {
            font-weight: 400;
            font-size: 0.95rem;
            color: #555;
            line-height: 2.1;
            text-align: justify;
            margin: 0;
        }

        .content-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .content-item {
            background: #f9fafb;
            border-left: 3px solid #dc2626;
            padding: 20px;
            margin: 0 0 16px;
            border-radius: 0 6px 6px 0;
        }

        .content-item-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 8px;
        }

        .content-item-meta {
            font-size: 0.85rem;
            color: #666;
            margin: 0 0 12px;
        }

        .content-item-desc {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
            margin: 0 0 12px;
        }

        .content-item-link {
            display: inline-block;
            font-size: 0.85rem;
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .content-item-link:hover {
            color: #991b1b;
        }

        .panel-placeholder {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            font-size: 1.2rem;
        }

        .mapa-column {
            width: 40%;
            height: 100vh;
            position: relative;
        }

        #mapa-dia {
            width: 100%;
            height: 100%;
        }

        .nav-btn {
            position: fixed;
            bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: transparent;
            border: 1px solid #1a1a1a;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            will-change: transform, opacity;
            z-index: 100000;
            font-family: 'Spectral', serif;
            font-size: inherit;
            color: inherit;
        }
        
        .nav-btn:focus {
            outline: 2px solid #dc2626;
            outline-offset: 2px;
        }

        /* Panel de controles autoplay */
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
            will-change: transform, background-color;
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

        .control-btn:focus {
            outline: 2px solid #dc2626;
            outline-offset: 2px;
        }

        .control-icon {
            font-size: 18px;
            color: #1a1a1a;
            transition: color 0.2s ease;
        }

        .control-btn:disabled .control-icon {
            color: #999;
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

        @media (max-width: 768px) {
            /* Controles autoplay más pequeños en móvil */
            .autoplay-panel {
                bottom: 70px;
                right: 10px;
                padding: 6px;
                gap: 6px;
            }

            .control-btn {
                width: 36px;
                height: 36px;
            }

            .control-icon {
                font-size: 14px;
            }
            
            .autoplay-status {
                margin-top: 6px;
                padding-top: 6px;
                font-size: 0.7rem;
            }
        }

        /* Mini-mapa de contexto */
        #mini-mapa-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100002;
        }
        
        .mini-mapa-wrapper {
            background: white;
            border: 2px solid #1a1a1a;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .mini-mapa-wrapper.hidden {
            display: none;
        }
        
        .mini-mapa-wrapper.collapsed {
            width: 0;
            height: 0;
            opacity: 0;
            border: none;
            box-shadow: none;
        }

        #mini-mapa {
            width: 200px;
            height: 150px;
            transition: all 0.3s ease;
        }

        .toggle-minimapa {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            background: #374151;
            color: #fff;
            border: 2px solid #9CA3AF;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
            will-change: transform, background-color, box-shadow;
            z-index: 10;
        }

        .toggle-minimapa:hover {
            background: #1F2937;
            border-color: #D1D5DB;
            transform: scale(1.1);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }

        .toggle-minimapa:focus {
            outline: 2px solid #6B7280;
            outline-offset: 2px;
        }

        .toggle-minimapa .minimapa-icon {
            display: block;
            font-size: 1rem;
            font-weight: bold;
            line-height: 1;
            transition: transform 0.3s ease;
            will-change: transform;
        }

        /* Cuando el wrapper está hidden o collapsed, mostrar botón flotante con 🗺️ */
        .mini-mapa-wrapper.hidden ~ .toggle-minimapa,
        .mini-mapa-wrapper.collapsed ~ .toggle-minimapa {
            position: static;
            width: 50px;
            height: 50px;
            font-size: 1.6rem;
        }
        
        .mini-mapa-wrapper.hidden ~ .toggle-minimapa .minimapa-icon,
        .mini-mapa-wrapper.collapsed ~ .toggle-minimapa .minimapa-icon {
            display: none;
        }

        .mini-mapa-wrapper.hidden ~ .toggle-minimapa::before,
        .mini-mapa-wrapper.collapsed ~ .toggle-minimapa::before {
            content: '🗺️';
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @media (max-width: 768px) {
            /* Mini-mapa colapsado por defecto en móvil */
            #mini-mapa-container {
                top: 10px;
                right: 10px;
            }
            
            .mini-mapa-wrapper.collapsed {
                width: 0;
                height: 0;
            }
            
            .mini-mapa-wrapper:not(.collapsed):not(.hidden) {
                width: 180px;
                border-radius: 12px;
            }
            
            .mini-mapa-wrapper:not(.collapsed):not(.hidden) #mini-mapa {
                width: 180px;
                height: 130px;
            }
            
            /* Botón en móvil colapsado (flotante grande) */
            .mini-mapa-wrapper.collapsed ~ .toggle-minimapa {
                position: static;
                width: 45px;
                height: 45px;
                font-size: 1.4rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            }
            
            /* Botón cuando está expandido (dentro del mapa) */
            .mini-mapa-wrapper:not(.collapsed) ~ .toggle-minimapa {
                position: absolute;
                bottom: 8px;
                right: 8px;
                width: 28px;
                height: 28px;
                font-size: 0.9rem;
            }
        }

        .nav-btn-disabled {
            opacity: 0.35;
            cursor: default;
            pointer-events: none;
        }

        .nav-btn-animating {
            opacity: 0.5;
            cursor: wait;
            pointer-events: none;
        }

        .nav-btn-prev {
            right: calc(50% + 8px);
            border-radius: 6px;
        }

        .nav-btn-next {
            left: calc(50% + 8px);
            border-radius: 6px;
        }

        .nav-label {
            display: flex;
            flex-direction: column;
            font-family: 'Spectral', serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: 1.2px;
            line-height: 1.2;
            text-align: center;
        }

        .nav-arrow {
            font-size: 1.8rem;
            line-height: 1;
            color: #1a1a1a;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: #1a1a1a;
            transform: scale(1.05);
            z-index: 10;
        }

        .nav-btn:hover .nav-label,
        .nav-btn:hover .nav-arrow {
            color: #fff;
        }

        @media (max-width: 768px) {
            .dia-container {
                flex-direction: column;
            }

            .info-column {
                width: 100%;
                height: 60vh;
                overflow-y: auto;
            }

            .mapa-column {
                width: 100%;
                height: 40vh;
            }

            .info-content-wrapper {
                padding: 30px 20px 80px 20px;
            }

            .breadcrumbs {
                padding: 10px 15px;
                font-size: 0.7rem;
                white-space: nowrap;
                overflow-x: auto;
            }

            .coordenadas {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                margin: 0 0 28px;
            }

            .coord-item {
                width: 100%;
                background: #f9fafb;
                border: 1px solid #ebebeb;
                padding: 10px 12px;
                border-radius: 8px;
                font-size: 0.85rem;
                letter-spacing: 0.3px;
            }

            .coord-label {
                margin-right: 10px;
            }

            /* Mostrar hamburguesa, ocultar tabs horizontales */
            .tabs-hamburger {
                display: flex;
            }

            .tabs-container {
                padding: 0 15px;
                border-bottom: none;
            }

            .tabs {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                flex-direction: column;
                gap: 0;
                background: #fff;
                border: 1px solid #e5e5e5;
                border-radius: 8px;
                overflow: hidden;
                z-index: 99999;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
                max-height: 0;
                opacity: 0;
                pointer-events: none;
                transition: max-height 0.3s ease, opacity 0.3s ease;
                width: 90%;
                max-width: 400px;
            }

            .tabs.menu-open {
                max-height: 500px;
                opacity: 1;
                pointer-events: auto;
            }

            .tab {
                width: 100%;
                border: none;
                border-radius: 0;
                border-left: 4px solid transparent;
                padding: 16px 20px;
                text-align: left;
                background: #fff;
                bottom: 0;
            }

            .tab.active {
                background: #f9fafb;
                color: #dc2626;
                font-weight: 600;
                border-top: none;
                border-left: 3px solid #dc2626;
                box-shadow: none;
            }
        }

        /* Contenedor del Slider de Actividades */
        .actividades-slider-container {
            position: relative;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            overflow: visible;
            z-index: 1;
        }

        /* ===================================
           CONTENCIÓN COMPLETA DEL SLIDER 
           ================================= */
        
        /* Asegurar que la columna izquierda mantenga su ancho */
        .info-column {
            width: 60% !important;
            max-width: 60% !important;
            position: relative;
            overflow: hidden;
        }

        /* Asegurar que el slider no se salga del panel */
        #panel-actividades {
            position: relative !important;
            overflow: hidden !important;
            z-index: 1;
            max-width: 100% !important;
            width: 100% !important;
        }

        /* FORZAR que el slider respete el ancho de su contenedor (info-column 60%) */
        #panel-actividades .actividades-slider-container {
            position: relative !important;
            max-width: 100% !important;
            width: 100% !important;
            overflow: hidden !important;
        }

        /* Contener todos los wrappers del Revolution Slider */
        #panel-actividades .rev_slider_wrapper,
        #panel-actividades .rev_slider,
        #panel-actividades .forcefullwidth_wrapper_tp_banner,
        #panel-actividades .tp-fullwidth-forcer {
            max-width: 100% !important;
            width: 100% !important;
            position: relative !important;
            left: 0 !important;
            right: auto !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            transform: none !important;
        }

        /* Evitar que el slider use estilos inline fullwidth */
        #panel-actividades .tp-revslider-mainul,
        #panel-actividades .tp-revslider-slidesli {
            max-width: 100% !important;
            width: 100% !important;
            left: 0 !important;
        }

        /* Evitar que los layers/captions se centren en toda la pantalla */
        #panel-actividades .tp-caption,
        #panel-actividades .tp-layer,
        #panel-actividades .tp-parallax-wrap {
            max-width: 100% !important;
            position: relative !important;
        }

        /* Selector específico para mayor prioridad */
        .info-column #panel-actividades .rev_slider_wrapper {
            position: relative !important;
            max-width: 100% !important;
            width: 100% !important;
            left: 0 !important;
            margin: 0 !important;
        }

        /* Anular cualquier cálculo automático de fullwidth */
        body .info-column #panel-actividades .tp-fullwidth-forcer {
            display: none !important;
        }

        /* ===================================
           IFRAME CONTAINER & CONTROLS
           ================================= */
        
        #iframe-container {
            width: 100%;
            height: 100%;
            position: relative;
            background: #000;
            display: flex;
            flex-direction: column;
        }

        .iframe-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #1a1a1a;
            box-sizing: border-box;
        }

        .btn-cerrar-iframe,
        .btn-abrir-pestaña-header {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: transparent;
            color: #1a1a1a;
            border: 1px solid #1a1a1a;
            border-radius: 0;
            cursor: pointer;
            font-family: 'Spectral', serif;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .btn-cerrar-iframe:hover,
        .btn-abrir-pestaña-header:hover {
            background: #1a1a1a;
            color: #fff;
            transform: scale(1.05);
        }

        #iframe-embed {
            width: 100%;
            height: 100%;
            border: none;
            position: relative;
            z-index: 1;
        }

        #iframe-blocked {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            z-index: 100005;
            min-width: 320px;
            max-width: 500px;
        }

        #iframe-blocked p {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #DC2626;
            font-weight: 600;
        }

        .btn-primary {
            padding: 12px 24px;
            background: #DC2626;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #B91C1C;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        }
    </style>
    <?php
}, 20);

get_header();
?>

<div class="dia-page">
    <div class="dia-container">
        <!-- COLUMNA IZQUIERDA -->
        <div class="info-column">
            <!-- BREADCRUMBS -->
            <div class="breadcrumbs">
                <a href="<?php echo esc_url(home_url('/timeline')); ?>">Timeline</a>
                <span class="separator">›</span>
                <a href="<?php echo esc_url($tramo_url); ?>"><?php echo esc_html($tramo_nombre); ?></a>
                <span class="separator">›</span>
                <span class="current"><?php echo esc_html($sitio_dia . ' (Día ' . $dia_numero . ')'); ?></span>
                <span class="separator">›</span>
                <a href="<?php echo esc_url(home_url('/buscador-global')); ?>" class="breadcrumb-search" title="Buscar en el viaje">🔍 Buscar</a>
            </div>

            <!-- PESTAÑAS -->
            <div class="tabs-container">
                <!-- BOTÓN HAMBURGUESA (solo móvil) -->
                <button class="tabs-hamburger" id="menu-toggle" type="button" aria-label="Abrir menú de secciones">
                    <span class="hamburger-icon">☰</span>
                    <span id="menu-label">Información general</span>
                </button>

                <!-- OVERLAY (cerrar menú) -->
                <div class="menu-overlay" id="menu-overlay"></div>

                <div class="tabs">
                    <button class="tab active" type="button" data-panel="informacion">Información general</button>
                    <button class="tab" type="button" data-panel="actividades">Actividades</button>
                    <button class="tab" type="button" data-panel="hospedaje">Hospedaje</button>
                    <button class="tab" type="button" data-panel="restauracion">Restauración</button>
                    <button class="tab" type="button" data-panel="presupuesto">Presupuesto</button>
                </div>
            </div>

            <!-- CONTENIDO -->
            <div class="info-content-wrapper">
                <div class="info-content">
                    <!-- PANEL: INFORMACIÓN GENERAL -->
                    <div class="panel active" id="panel-informacion">
                        <?php if ($tramo_nombre): ?>
                            <div class="cabecera-tramo">
                                <div class="nombre-tramo">
                                    <span class="tour-prefix">Tour:</span> <?php echo esc_html(strtoupper($tramo_nombre)); ?>
                                </div>
                                <div class="tramo-metadatos">
                                    <div class="tramo-meta-col">
                                        <div class="tramo-meta-label">INICIO</div>
                                        <div class="tramo-meta-lugar"><?php echo esc_html($tramo_sitio_inicio); ?></div>
                                        <div class="tramo-meta-fecha"><?php echo esc_html($tramo_fecha_inicio); ?></div>
                                    </div>
                                    <div class="tramo-meta-col">
                                        <div class="tramo-meta-label">FIN</div>
                                        <div class="tramo-meta-lugar"><?php echo esc_html($tramo_sitio_fin); ?></div>
                                        <div class="tramo-meta-fecha"><?php echo esc_html($tramo_fecha_fin); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h1 class="sitio-dia"><?php echo esc_html($emoji_dia . $sitio_dia); ?></h1>
                        <div class="fecha-dia"><?php echo esc_html($fecha_dia_formatted); ?></div>
                        <div class="numero-dia">Día <?php echo esc_html($dia_numero . 'º de ' . $total_dias); ?> días.</div>

                        <?php if ($latitud && $longitud): ?>
                            <div class="coordenadas">
                                <span class="coord-item"><span class="coord-label">Latitud:</span> <?php echo esc_html($latitud); ?>°</span>
                                <span class="coord-item"><span class="coord-label">Longitud:</span> <?php echo esc_html($longitud); ?>°</span>
                                <?php if ($altitud): ?>
                                    <span class="coord-item"><span class="coord-label">Altitud:</span> <?php echo esc_html($altitud); ?> m</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($hito_dia): ?>
                            <h2 class="hito-dia"><?php echo esc_html($hito_dia); ?></h2>
                        <?php endif; ?>

                        <?php if ($relato_dia): ?>
                            <div class="relato-dia"><?php echo wpautop(wp_kses_post($relato_dia)); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- PANEL: ACTIVIDADES -->
                    <div class="panel" id="panel-actividades">
                        
                        <?php
                        // Guardar id_dia en cookie para el filtro del slider
                        $current_id_dia = get_field('id_dia');
                        if (!empty($current_id_dia)) {
                            setcookie('jdm_current_id_dia', $current_id_dia, 0, '/', '', false, true);
                        }
                        ?>
                        
                        <div class="actividades-slider-container">
                            <?php echo do_shortcode('[rev_slider alias="slider-2"]'); ?>
                        </div>
                    </div>

                    <!-- PANEL: HOSPEDAJE -->
                    <div class="panel" id="panel-hospedaje">
                        <?php if (!empty($hospedajes)): ?>
                            <ul class="content-list">
                                <?php foreach ($hospedajes as $hospedaje): ?>
                                    <li class="content-item">
                                        <h3 class="content-item-title"><?php echo esc_html($hospedaje['nombre']); ?></h3>
                                        <div class="content-item-meta"><?php echo esc_html(trim($hospedaje['tipo'] . ' - ' . $hospedaje['categoria'], ' -')); ?></div>
                                        <?php if (!empty($hospedaje['descripcion'])): ?>
                                            <div class="content-item-desc"><?php echo wp_kses_post($hospedaje['descripcion']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($hospedaje['url'])): ?>
                                            <a class="content-item-link" href="<?php echo esc_url($hospedaje['url']); ?>" target="_blank" rel="noopener">Visitar sitio web →</a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="panel-placeholder">🏨 No hay hospedajes registrados para este día</div>
                        <?php endif; ?>
                    </div>

                    <!-- PANEL: RESTAURACIÓN -->
                    <div class="panel" id="panel-restauracion">
                        <?php if (!empty($restaurantes)): ?>
                            <ul class="content-list">
                                <?php foreach ($restaurantes as $restaurante): ?>
                                    <li class="content-item">
                                        <h3 class="content-item-title"><?php echo esc_html($restaurante['nombre']); ?></h3>
                                        <div class="content-item-meta"><?php echo esc_html(trim($restaurante['tipo'] . ' - ' . $restaurante['categoria'], ' -')); ?></div>
                                        <?php if (!empty($restaurante['url'])): ?>
                                            <a class="content-item-link" href="<?php echo esc_url($restaurante['url']); ?>" target="_blank" rel="noopener">Reservar →</a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="panel-placeholder">🍽️ No hay restaurantes registrados para este día</div>
                        <?php endif; ?>
                    </div>

                    <!-- PANEL: PRESUPUESTO -->
                    <div class="panel" id="panel-presupuesto">
                        <div class="panel-placeholder">💰 Panel de Presupuesto<br>(Acceso restringido)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: MAPA + IFRAME -->
        <div class="mapa-column">
            <!-- VISTA 1: MAPA (activo por defecto) -->
            <div id="mapa-dia"></div>
            
            <!-- VISTA 2: IFRAME (oculto por defecto) -->
            <div id="iframe-container" style="display: none;">
                <div class="iframe-header">
                    <button type="button" id="btn-cerrar-iframe" class="btn-cerrar-iframe">
                        Volver al mapa
                    </button>
                    <button type="button" id="btn-abrir-pestaña-header" class="btn-abrir-pestaña-header">
                        Abrir en tu Navegador
                    </button>
                </div>
                <iframe 
                    id="iframe-embed" 
                    src="" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
                <!-- Fallback si el iframe está bloqueado -->
                <div id="iframe-blocked" style="display: none;">
                    <p>⚠️ Esta página no puede mostrarse aquí</p>
                    <button id="btn-abrir-pestaña" class="btn-primary">Abrir en nueva pestaña</button>
                </div>
            </div>
            
            <!-- MINI-MAPA DE CONTEXTO (existente) -->
            <div id="mini-mapa-container">
                <div id="mini-mapa-contexto" class="mini-mapa-wrapper">
                    <div id="mini-mapa"></div>
                </div>
                <button type="button" id="btn-toggle-minimapa" class="toggle-minimapa" title="Mostrar/Ocultar mini-mapa" aria-label="Toggle mini-mapa">
                    <span class="minimapa-icon">✕</span>
                </button>
            </div>
        </div>

        <!-- BOTONES DE NAVEGACIÓN -->
        <?php if ($show_prev): ?>
            <?php if (!empty($dia_anterior) && !empty($dia_anterior['url'])): ?>
                <button 
                    class="nav-btn nav-btn-prev" 
                    aria-label="Día anterior"
                    data-url="<?php echo esc_url($dia_anterior['url']); ?>"
                    data-lat="<?php echo esc_attr($dia_anterior['lat']); ?>"
                    data-lng="<?php echo esc_attr($dia_anterior['lng']); ?>"
                    data-sitio="<?php echo esc_attr($dia_anterior['sitio']); ?>">
                    <span class="nav-arrow" aria-hidden="true">↑</span>
                    <div class="nav-label"><span>PREV</span><span>DAY</span></div>
                </button>
            <?php else: ?>
                <span class="nav-btn nav-btn-prev nav-btn-disabled" aria-label="Día anterior" aria-disabled="true">
                    <span class="nav-arrow" aria-hidden="true">↑</span>
                    <div class="nav-label"><span>PREV</span><span>DAY</span></div>
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($show_next): ?>
            <?php if (!empty($dia_siguiente) && !empty($dia_siguiente['url'])): ?>
                <button 
                    class="nav-btn nav-btn-next" 
                    aria-label="Día siguiente"
                    data-url="<?php echo esc_url($dia_siguiente['url']); ?>"
                    data-lat="<?php echo esc_attr($dia_siguiente['lat']); ?>"
                    data-lng="<?php echo esc_attr($dia_siguiente['lng']); ?>"
                    data-sitio="<?php echo esc_attr($dia_siguiente['sitio']); ?>">
                    <div class="nav-label"><span>NEXT</span><span>DAY</span></div>
                    <span class="nav-arrow" aria-hidden="true">↓</span>
                </button>
            <?php else: ?>
                <span class="nav-btn nav-btn-next nav-btn-disabled" aria-label="Día siguiente" aria-disabled="true">
                    <div class="nav-label"><span>NEXT</span><span>DAY</span></div>
                    <span class="nav-arrow" aria-hidden="true">↓</span>
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- PANEL DE CONTROLES AUTOPLAY -->
        <div id="autoplay-controls" class="autoplay-panel">
            <button type="button" id="btn-play" class="control-btn" title="Reproducir automáticamente (adelante)" aria-label="Play">
                <span class="control-icon">▶</span>
            </button>
            <button type="button" id="btn-stop" class="control-btn" disabled title="Detener" aria-label="Stop">
                <span class="control-icon">⏹</span>
            </button>
            <button type="button" id="btn-back" class="control-btn" title="Reproducir automáticamente (atrás)" aria-label="Atrás">
                <span class="control-icon">◀</span>
            </button>
            <div class="autoplay-status">
                <span id="autoplay-counter"></span>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        // Tabs
        var tabs = document.querySelectorAll('.tab');
        var panels = document.querySelectorAll('.panel');
        var wrapper = document.querySelector('.info-content-wrapper');
        var menuToggle = document.getElementById('menu-toggle');
        var menuOverlay = document.getElementById('menu-overlay');
        var menuLabel = document.getElementById('menu-label');
        var tabsContainer = document.querySelector('.tabs');

        // Mapa de data-panel → texto legible
        var panelLabels = {
            'informacion': 'Información general',
            'actividades': 'Actividades',
            'hospedaje': 'Hospedaje',
            'restauracion': 'Restauración',
            'presupuesto': 'Presupuesto'
        };

        function closeMenu() {
            if (tabsContainer) {
                tabsContainer.classList.remove('menu-open');
            }
            if (menuOverlay) {
                menuOverlay.classList.remove('active');
            }
        }

        function openMenu() {
            if (tabsContainer) {
                tabsContainer.classList.add('menu-open');
            }
            if (menuOverlay) {
                menuOverlay.classList.add('active');
            }
        }

        // Toggle menú hamburguesa
        if (menuToggle) {
            menuToggle.addEventListener('click', function () {
                if (tabsContainer && tabsContainer.classList.contains('menu-open')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        }

        // Cerrar menú al clickear overlay
        if (menuOverlay) {
            menuOverlay.addEventListener('click', closeMenu);
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) {
                    t.classList.remove('active');
                });
                panels.forEach(function (p) {
                    p.classList.remove('active');
                });

                tab.classList.add('active');
                var panelId = 'panel-' + tab.getAttribute('data-panel');
                var panel = document.getElementById(panelId);
                if (panel) {
                    panel.classList.add('active');
                }

                // Actualizar label del botón hamburguesa
                var panelKey = tab.getAttribute('data-panel');
                if (menuLabel && panelLabels[panelKey]) {
                    menuLabel.textContent = panelLabels[panelKey];
                }

                // Cerrar iframe si está abierto (mejora UX)
                if (typeof window.cerrarIframe === 'function') {
                    window.cerrarIframe();
                }

                // Cerrar menú en móvil tras seleccionar tab
                closeMenu();

                if (wrapper) {
                    if (typeof wrapper.scrollTo === 'function') {
                        wrapper.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        wrapper.scrollTop = 0;
                    }
                }
            });
        });

        // Event listeners para botones de navegación PREV/NEXT
        var navButtons = document.querySelectorAll('.nav-btn:not(.nav-btn-disabled)');
        navButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var url = this.getAttribute('data-url');
                var lat = this.getAttribute('data-lat');
                var lng = this.getAttribute('data-lng');
                var sitio = this.getAttribute('data-sitio');
                
                if (url && lat && lng && typeof window.animarNavegacionMapa === 'function') {
                    window.animarNavegacionMapa(lat, lng, url, sitio);
                } else if (url) {
                    // Fallback si el mapa no está inicializado
                    window.location.href = url;
                }
            });
        });

        // ==========================================
        // CONTROLES DE AUTOPLAY
        // ==========================================
        var btnPlay = document.getElementById('btn-play');
        var btnStop = document.getElementById('btn-stop');
        var btnBack = document.getElementById('btn-back');
        var autoplayCounter = document.getElementById('autoplay-counter');
        
        var autoplayTimer = null;
        var autoplayIntervalId = null;
        var autoplayInterval = 3000; // 3 segundos
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
            
            // Guardar estado y dirección en localStorage
            localStorage.setItem('autoplayActive', 'true');
            localStorage.setItem('autoplayDirection', playDirection);
            
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
                
                if (targetBtn) {
                    var url = targetBtn.getAttribute('data-url');
                    var lat = targetBtn.getAttribute('data-lat');
                    var lng = targetBtn.getAttribute('data-lng');
                    var sitio = targetBtn.getAttribute('data-sitio');
                    
                    console.log('➡️ Autoplay: navegando a', sitio);
                    
                    // NO limpiar localStorage, se reanudará en la siguiente página
                    if (url && lat && lng && typeof window.animarNavegacionMapa === 'function') {
                        window.animarNavegacionMapa(lat, lng, url, sitio);
                    } else if (url) {
                        window.location.href = url;
                    }
                } else {
                    var mensaje = playDirection === 'forward' 
                        ? '✅ Has llegado al último día del viaje'
                        : '✅ Has llegado al primer día del viaje';
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
            localStorage.removeItem('autoplayActive');
            localStorage.removeItem('autoplayDirection');
            
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
        
        // ==========================================
        // REANUDAR AUTOPLAY AL CARGAR PÁGINA
        // ==========================================
        // Verificar si el autoplay estaba activo en la página anterior
        if (localStorage.getItem('autoplayActive') === 'true') {
            var savedDirection = localStorage.getItem('autoplayDirection') || 'forward';
            console.log('🔄 Reanudando autoplay desde página anterior (' + savedDirection + ')');
            // Pequeño delay para que el mapa se inicialice completamente
            setTimeout(function() {
                startAutoplay(savedDirection);
            }, 500);
        }
    })();
</script>

<?php
// ------------------------------
// Mapa (JS + Google Maps)
// ------------------------------
add_action('wp_footer', function () use ($map_data, $todos_dias_coords, $dia_numero) {
    $map_json = wp_json_encode($map_data);
    $todos_dias_json = wp_json_encode($todos_dias_coords);
    $dia_actual = (int) $dia_numero;
    ?>
    <script>
        window.mapDataDia = <?php echo $map_json; ?>;
        window.todosDiasCoords = <?php echo $todos_dias_json; ?>;
        window.diaActualNumero = <?php echo $dia_actual; ?>;

        function initMapDia() {
            var mapData = window.mapDataDia;
            
            // Variable de control para debouncing de animaciones
            var animacionEnProgreso = false;
            
            // DEBUG: Verificar datos recibidos
            console.log('=== DEBUG MAPA ===');
            console.log('mapData completo:', mapData);
            console.log('Anterior:', mapData.anterior);
            console.log('Actual:', mapData.actual);
            console.log('Siguiente:', mapData.siguiente);
            
            if (!mapData || !mapData.actual || !mapData.actual.lat || !mapData.actual.lng) {
                console.error('ERROR: Faltan datos del día actual');
                return;
            }

            var mapStyle = [
                { featureType: 'poi', stylers: [{ visibility: 'off' }] },
                { featureType: 'transit', stylers: [{ visibility: 'off' }] },
            ];

            var map = new google.maps.Map(document.getElementById('mapa-dia'), {
                zoom: 8,
                center: { lat: mapData.actual.lat, lng: mapData.actual.lng },
                mapTypeId: 'roadmap',
                disableDefaultUI: true,
                styles: mapStyle,
            });

            var iconoRojo = {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 12,
                fillColor: '#DC2626',
                fillOpacity: 1,
                strokeColor: '#FFFFFF',
                strokeWeight: 4,
            };

            var iconoGris = {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: '#9CA3AF',
                fillOpacity: 1,
                strokeColor: '#FFFFFF',
                strokeWeight: 3,
            };

            var infoWindow = new google.maps.InfoWindow();
            var offsetAplicado = 0.004; // ~440m de separación visual

            // Función global para animar navegación con zoom (con debouncing)
            function animarNavegacion(lat, lng, url, sitio) {
                // DEBOUNCING: Bloquear si ya hay una animación en progreso
                if (animacionEnProgreso) {
                    console.log('⚠️ Animación bloqueada - ya hay una en progreso');
                    return;
                }
                
                animacionEnProgreso = true;
                
                // Deshabilitar visualmente todos los botones de navegación
                var todosNavBtns = document.querySelectorAll('.nav-btn');
                todosNavBtns.forEach(function(btn) {
                    btn.classList.add('nav-btn-animating');
                });
                
                // Deshabilitar controles de autoplay durante la animación
                var controlBtns = document.querySelectorAll('.control-btn');
                controlBtns.forEach(function(btn) {
                    btn.disabled = true;
                });
                
                // MEJORA 3: TIMEOUT DE SEGURIDAD (5 segundos)
                // Si la navegación no ocurre en 5s, desbloquear automáticamente
                var timeoutSeguridad = setTimeout(function() {
                    console.warn('⚠️ Timeout de seguridad: Desbloqueando sistema después de 5s');
                    animacionEnProgreso = false;
                    
                    // Re-habilitar botones de navegación
                    todosNavBtns.forEach(function(btn) {
                        btn.classList.remove('nav-btn-animating');
                    });
                    
                    // Re-habilitar controles de autoplay
                    controlBtns.forEach(function(btn) {
                        btn.disabled = false;
                    });
                }, 5000);
                
                console.log('🎯 Iniciando animación de navegación hacia:', sitio);
                
                var position = { lat: parseFloat(lat), lng: parseFloat(lng) };
                
                // Cerrar InfoWindow si está abierto
                infoWindow.close();
                
                // Pan suave hacia el destino
                map.panTo(position);
                
                // Zoom progresivo y dramático
                var zoomInicial = map.getZoom();
                var zoomFinal = 16;
                var pasos = 20;
                var paso = 0;
                
                var zoomInterval = setInterval(function() {
                    paso++;
                    // Ease-in-out para animación más natural
                    var progreso = paso / pasos;
                    var easing = progreso < 0.5 
                        ? 2 * progreso * progreso 
                        : 1 - Math.pow(-2 * progreso + 2, 2) / 2;
                    
                    var zoomActual = zoomInicial + ((zoomFinal - zoomInicial) * easing);
                    map.setZoom(Math.round(zoomActual));
                    
                    if (paso >= pasos) {
                        clearInterval(zoomInterval);
                        console.log('✅ Animación completada, navegando...');
                        setTimeout(function() {
                            // Cancelar timeout de seguridad (navegación exitosa)
                            clearTimeout(timeoutSeguridad);
                            // Mantener el bloqueo hasta que la página cambie
                            window.location.href = url;
                        }, 600);
                    }
                }, 70);
            }
            
            // Exponer función globalmente para uso de botones PREV/NEXT
            window.animarNavegacionMapa = animarNavegacion;

            function crearMarcador(dia, icono, esDiaActual, aplicarOffset, esDiaAnterior) {
                if (!dia || !dia.lat || !dia.lng || isNaN(dia.lat) || isNaN(dia.lng)) {
                    console.warn('❌ Coordenadas inválidas o faltantes:', dia);
                    return null;
                }

                var lat = parseFloat(dia.lat);
                var lng = parseFloat(dia.lng);

                // Si NO es día actual Y tiene coordenadas idénticas, aplicar offset para separar visualmente
                if (!esDiaActual && mapData.actual && 
                    Math.abs(lat - mapData.actual.lat) < 0.0001 && 
                    Math.abs(lng - mapData.actual.lng) < 0.0001) {
                    
                    if (esDiaAnterior) {
                        // Día anterior: desplazar hacia ARRIBA-IZQUIERDA
                        lat += offsetAplicado;
                        lng -= offsetAplicado;
                        console.log('📍 Offset ANTERIOR aplicado a', dia.sitio, ':', lat, lng);
                    } else {
                        // Día siguiente: desplazar hacia ABAJO-DERECHA
                        lat -= offsetAplicado;
                        lng += offsetAplicado;
                        console.log('📍 Offset SIGUIENTE aplicado a', dia.sitio, ':', lat, lng);
                    }
                }

                console.log('✅ Creando marcador:', dia.sitio, 'en', lat, lng);

                var position = { lat: lat, lng: lng };

                var marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    icon: icono,
                    title: dia.sitio || '',
                    optimized: false,
                    zIndex: esDiaActual ? 1000 : 999
                });

                marker.addListener('mouseover', function () {
                    var sitio = dia.sitio || '';
                    var tramo = dia.tramo || '';
                    var hito = dia.hito || '';
                    var fecha = dia.fecha || '';
                    
                    var contenido = '<div style="font-family: Spectral, serif; min-width: 220px; max-width: 320px; text-align: center;">';
                    
                    // Nombre del tramo
                    if (tramo) {
                        contenido += '<div style="font-size: 0.75rem; font-weight: 600; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">';
                        contenido += 'TRAMO: ' + tramo;
                        contenido += '</div>';
                    }
                    
                    // Nombre del sitio
                    contenido += '<div style="font-size: 1.3rem; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 2px solid #dc2626;">';
                    contenido += sitio;
                    contenido += '</div>';
                    
                    // Distancia desde día anterior (solo para día actual)
                    if (esDiaActual && mapData.anterior && mapData.anterior.lat && mapData.anterior.lng) {
                        console.log('📏 Calculando distancia para día actual');
                        console.log('Anterior:', mapData.anterior.lat, mapData.anterior.lng);
                        console.log('Actual:', mapData.actual.lat, mapData.actual.lng);
                        
                        // Verificar que la librería geometry esté cargada
                        if (typeof google.maps.geometry === 'undefined') {
                            console.error('❌ Librería geometry NO cargada');
                        } else {
                            console.log('✅ Librería geometry cargada');
                            
                            var latAnterior = parseFloat(mapData.anterior.lat);
                            var lngAnterior = parseFloat(mapData.anterior.lng);
                            var latActual = parseFloat(mapData.actual.lat);
                            var lngActual = parseFloat(mapData.actual.lng);
                            
                            console.log('Coordenadas parseadas:', latAnterior, lngAnterior, latActual, lngActual);
                            
                            var puntoAnterior = new google.maps.LatLng(latAnterior, lngAnterior);
                            var puntoActual = new google.maps.LatLng(latActual, lngActual);
                            var distanciaMetros = google.maps.geometry.spherical.computeDistanceBetween(puntoAnterior, puntoActual);
                            var distanciaKm = (distanciaMetros / 1000).toFixed(1);
                            
                            console.log('Distancia calculada:', distanciaKm, 'km');
                            
                            contenido += '<div style="font-size: 0.85rem; color: #dc2626; margin-bottom: 8px; font-weight: 500;">';
                            contenido += '📏 ' + distanciaKm + ' km desde ' + mapData.anterior.sitio;
                            contenido += '</div>';
                        }
                    }
                    
                    // Fecha del día
                    if (fecha) {
                        contenido += '<div style="font-size: 0.85rem; color: #666; margin-bottom: 10px; font-style: italic;">';
                        contenido += '📅 ' + fecha;
                        contenido += '</div>';
                    }
                    
                    // Hito del día
                    if (hito) {
                        contenido += '<div style="font-size: 0.95rem; color: #555; line-height: 1.5; padding: 8px 0;">';
                        contenido += hito;
                        contenido += '</div>';
                    }
                    
                    contenido += '</div>';
                    
                    infoWindow.setContent(contenido);
                    infoWindow.open(map, marker);
                });

                marker.addListener('mouseout', function () {
                    infoWindow.close();
                });

                if (!esDiaActual && dia.url) {
                    marker.addListener('click', function () {
                        animarNavegacion(lat, lng, dia.url, dia.sitio);
                    });
                    marker.setOptions({ cursor: 'pointer' });
                }

                return marker;
            }

            // Crear marcadores
            if (mapData.anterior && mapData.anterior.lat) {
                crearMarcador(mapData.anterior, iconoGris, false, true, true); // esDiaAnterior = true
            }

            crearMarcador(mapData.actual, iconoRojo, true, false, false);

            if (mapData.siguiente && mapData.siguiente.lat) {
                crearMarcador(mapData.siguiente, iconoGris, false, true, false); // esDiaAnterior = false
            }

            // ========================================
            // CREAR LÍNEAS DE CONEXIÓN (Polylines)
            // ========================================
            console.log('=== CREANDO LÍNEAS DE CONEXIÓN ===');

            // Línea ROJA: Día anterior → Día actual
            if (mapData.anterior && mapData.anterior.lat && mapData.anterior.lng &&
                mapData.actual && mapData.actual.lat && mapData.actual.lng) {
                
                // Calcular coordenadas con offset si aplica
                var latAnterior = parseFloat(mapData.anterior.lat);
                var lngAnterior = parseFloat(mapData.anterior.lng);
                
                // Si día anterior tiene mismas coordenadas que actual, aplicar offset
                if (Math.abs(latAnterior - mapData.actual.lat) < 0.0001 && 
                    Math.abs(lngAnterior - mapData.actual.lng) < 0.0001) {
                    var offset = 0.004;
                    latAnterior += offset;
                    lngAnterior -= offset;
                    console.log('🔴 Línea ROJA con offset aplicado');
                }
                
                new google.maps.Polyline({
                    path: [
                        { lat: latAnterior, lng: lngAnterior },
                        { lat: parseFloat(mapData.actual.lat), lng: parseFloat(mapData.actual.lng) }
                    ],
                    geodesic: true,
                    strokeColor: '#DC2626',
                    strokeOpacity: 0.8,
                    strokeWeight: 4,
                    map: map,
                    zIndex: 100
                });
                
                console.log('✅ Línea ROJA creada:', mapData.anterior.sitio, '→', mapData.actual.sitio);
            } else {
                console.log('⚠️ Línea ROJA omitida (falta día anterior)');
            }

            // Línea GRIS: Día actual → Día siguiente
            if (mapData.siguiente && mapData.siguiente.lat && mapData.siguiente.lng &&
                mapData.actual && mapData.actual.lat && mapData.actual.lng) {
                
                // Calcular coordenadas con offset si aplica
                var latSiguiente = parseFloat(mapData.siguiente.lat);
                var lngSiguiente = parseFloat(mapData.siguiente.lng);
                
                // Si día siguiente tiene mismas coordenadas que actual, aplicar offset
                if (Math.abs(latSiguiente - mapData.actual.lat) < 0.0001 && 
                    Math.abs(lngSiguiente - mapData.actual.lng) < 0.0001) {
                    var offset = 0.004;
                    latSiguiente -= offset;
                    lngSiguiente += offset;
                    console.log('⚪ Línea GRIS con offset aplicado');
                }
                
                new google.maps.Polyline({
                    path: [
                        { lat: parseFloat(mapData.actual.lat), lng: parseFloat(mapData.actual.lng) },
                        { lat: latSiguiente, lng: lngSiguiente }
                    ],
                    geodesic: true,
                    strokeColor: '#9CA3AF',
                    strokeOpacity: 0.7,
                    strokeWeight: 3,
                    map: map,
                    zIndex: 99
                });
                
                console.log('✅ Línea GRIS creada:', mapData.actual.sitio, '→', mapData.siguiente.sitio);
            } else {
                console.log('⚠️ Línea GRIS omitida (falta día siguiente)');
            }

            console.log('=== FIN CREACIÓN LÍNEAS ===');

            // ==========================================
            // AJUSTE DE BOUNDS (AL FINAL, después de crear TODO)
            // ==========================================
            var bounds = new google.maps.LatLngBounds();
            var puntosAnadidos = 0;

            // Añadir TODOS los puntos válidos al bounds
            [mapData.anterior, mapData.actual, mapData.siguiente].forEach(function(dia) {
                if (dia && dia.lat && dia.lng && 
                    !isNaN(dia.lat) && !isNaN(dia.lng)) {
                    bounds.extend({lat: parseFloat(dia.lat), lng: parseFloat(dia.lng)});
                    puntosAnadidos++;
                    console.log('✅ Punto añadido a bounds:', dia.sitio, dia.lat, dia.lng);
                }
            });

            console.log('📍 Total puntos en bounds:', puntosAnadidos);

            // Aplicar bounds solo si hay al menos 2 puntos
            if (puntosAnadidos >= 2) {
                console.log('🗺️ Aplicando fitBounds() con padding optimizado');
                
                // fitBounds con padding reducido para maximizar visibilidad de pins
                map.fitBounds(bounds, {
                    top: 50,      // margen superior mínimo
                    right: 50,    // margen derecho mínimo
                    bottom: 50,   // margen inferior mínimo
                    left: 50      // margen izquierdo mínimo
                });

                // Limitar zoom máximo para no acercarse demasiado
                google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
                    var zoomActual = map.getZoom();
                    console.log('📏 Zoom después de fitBounds:', zoomActual);

                    if (zoomActual > 13) {
                        map.setZoom(13);
                        console.log('📏 Zoom ajustado a 13 (máximo)');
                    }
                });
            } else if (puntosAnadidos === 1) {
                // Si solo hay 1 punto, centrar y usar zoom fijo
                console.log('📍 Solo 1 punto, usando zoom fijo');
                map.setCenter({lat: mapData.actual.lat, lng: mapData.actual.lng});
                map.setZoom(10);
            }

            console.log('=== FIN INIT MAPA ===');
            
            // ==========================================
            // MINI-MAPA DE CONTEXTO - LAZY LOADING
            // ==========================================
            setupMiniMapaLazy();
        }
        
        function setupMiniMapaLazy() {
            var btnToggle = document.getElementById('btn-toggle-minimapa');
            var wrapper = document.getElementById('mini-mapa-contexto');
            var miniMapaEl = document.getElementById('mini-mapa');
            
            if (!btnToggle || !wrapper || !miniMapaEl) {
                console.warn('⚠️ Mini-mapa: elementos no encontrados');
                return;
            }
            
            var miniMapaCreado = false;
            var miniMapa = null;
            var esMobil = window.innerWidth <= 768;
            var miniMapaExpandido = !esMobil; // Desktop expandido, móvil colapsado
            
            // Estado inicial diferente para desktop y móvil
            if (esMobil) {
                wrapper.classList.add('collapsed');
                console.log('📱 Mini-mapa: modo móvil (colapsado)');
            } else {
                wrapper.classList.remove('collapsed');
                wrapper.classList.remove('hidden');
                console.log('🖥️ Mini-mapa: modo desktop (expandido)');
                
                // En desktop, crear el mini-mapa inmediatamente
                setTimeout(function() {
                    crearMiniMapa();
                }, 100);
            }
            
            // Función para crear el mini-mapa (solo se ejecuta una vez)
            function crearMiniMapa() {
                if (miniMapaCreado || !window.todosDiasCoords || window.todosDiasCoords.length === 0) {
                    return;
                }
                
                console.log('🗺️ Creando mini-mapa (lazy load) con', window.todosDiasCoords.length, 'días');
                
                // Calcular centro del viaje
                var latSum = 0, lngSum = 0;
                window.todosDiasCoords.forEach(function(dia) {
                    latSum += dia.lat;
                    lngSum += dia.lng;
                });
                var centerLat = latSum / window.todosDiasCoords.length;
                var centerLng = lngSum / window.todosDiasCoords.length;
                
                // Crear mini-mapa
                miniMapa = new google.maps.Map(miniMapaEl, {
                    center: { lat: centerLat, lng: centerLng },
                    zoom: 5,
                    mapTypeId: 'roadmap',
                    disableDefaultUI: true,
                    gestureHandling: 'none',
                    zoomControl: false,
                    styles: [
                        { featureType: 'all', elementType: 'labels', stylers: [{ visibility: 'off' }] }
                    ]
                });
                
                // Crear línea del viaje completo
                var rutaCompleta = window.todosDiasCoords.map(function(dia) {
                    return { lat: dia.lat, lng: dia.lng };
                });
                
                new google.maps.Polyline({
                    path: rutaCompleta,
                    geodesic: true,
                    strokeColor: '#9CA3AF',
                    strokeOpacity: 0.6,
                    strokeWeight: 2,
                    map: miniMapa
                });
                
                // Marcar día actual
                var diaActual = window.todosDiasCoords.find(function(dia) {
                    return dia.dia === window.diaActualNumero;
                });
                
                if (diaActual) {
                    new google.maps.Marker({
                        position: { lat: diaActual.lat, lng: diaActual.lng },
                        map: miniMapa,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 6,
                            fillColor: '#DC2626',
                            fillOpacity: 1,
                            strokeColor: '#FFFFFF',
                            strokeWeight: 2
                        },
                        title: 'Estás aquí (Día ' + window.diaActualNumero + ')'
                    });
                }
                
                // Ajustar bounds al viaje completo
                var bounds = new google.maps.LatLngBounds();
                window.todosDiasCoords.forEach(function(dia) {
                    bounds.extend({ lat: dia.lat, lng: dia.lng });
                });
                miniMapa.fitBounds(bounds);
                
                miniMapaCreado = true;
                console.log('✅ Mini-mapa creado exitosamente (lazy load)');
            }
            
            // Toggle expandir/colapsar con lazy loading
            btnToggle.addEventListener('click', function() {
                // Primera vez que se expande: crear el mapa
                if (!miniMapaCreado) {
                    crearMiniMapa();
                }
                
                miniMapaExpandido = !miniMapaExpandido;
                
                if (miniMapaExpandido) {
                    wrapper.classList.remove('collapsed');
                    wrapper.classList.remove('hidden');
                } else {
                    if (esMobil) {
                        wrapper.classList.add('collapsed');
                    } else {
                        wrapper.classList.add('hidden');
                    }
                }
            });
            
            // Redetectar móvil al redimensionar ventana
            window.addEventListener('resize', function() {
                var nuevoEsMobil = window.innerWidth <= 768;
                if (nuevoEsMobil !== esMobil) {
                    esMobil = nuevoEsMobil;
                    if (esMobil && !miniMapaExpandido) {
                        wrapper.classList.add('collapsed');
                        wrapper.classList.remove('hidden');
                    } else if (!esMobil && !miniMapaExpandido) {
                        wrapper.classList.remove('collapsed');
                        wrapper.classList.add('hidden');
                    }
                }
            });
            
            console.log('✅ Mini-mapa lazy loading configurado (' + (esMobil ? 'móvil colapsado' : 'desktop expandido') + ')');
        }
        
        // ==========================================
        // SISTEMA DE IFRAME TOGGLE (Mapa ↔ Iframe)
        // ==========================================
        function setupIframeToggle() {
            var mapaDiv = document.getElementById('mapa-dia');
            var iframeContainer = document.getElementById('iframe-container');
            var iframeEmbed = document.getElementById('iframe-embed');
            var miniMapaContainer = document.getElementById('mini-mapa-container');
            var btnCerrarIframe = document.getElementById('btn-cerrar-iframe');
            var btnAbrirPestanaHeader = document.getElementById('btn-abrir-pestaña-header');
            var iframeBlocked = document.getElementById('iframe-blocked');
            var btnAbrirPestana = document.getElementById('btn-abrir-pestaña');
            
            var urlActual = '';
            var vistaActual = 'mapa'; // 'mapa' o 'iframe'
            
            // Función global para abrir iframe desde cualquier parte (slider de actividades)
            window.abrirIframe = function(url) {
                if (!url || url.trim() === '') {
                    alert('Esta actividad no tiene URL externa configurada');
                    return;
                }
                
                console.log('🔗 Cargando iframe con URL:', url);
                
                var mapaDiv = document.getElementById('mapa-dia');
                var iframeContainer = document.getElementById('iframe-container');
                var iframeEmbed = document.getElementById('iframe-embed');
                var miniMapaContainer = document.getElementById('mini-mapa-container');
                var blockedDiv = document.getElementById('iframe-blocked');
                var btnAbrirPestana = document.getElementById('btn-abrir-pestaña');
                
                // Guardar URL globalmente para el fallback
                window.currentIframeURL = url;
                
                // Ocultar mapa
                mapaDiv.style.display = 'none';
                
                // Ocultar mini-mapa
                if (miniMapaContainer) {
                    miniMapaContainer.style.display = 'none';
                }
                
                // Ocultar botones de navegación (PREV/NEXT DAY)
                var navButtons = document.querySelectorAll('.nav-btn');
                navButtons.forEach(function(btn) {
                    btn.style.display = 'none';
                });
                
                // Ocultar panel de autoplay
                var autoplayPanel = document.getElementById('autoplay-controls');
                if (autoplayPanel) {
                    autoplayPanel.style.display = 'none';
                }
                
                // Mostrar iframe container
                iframeContainer.style.display = 'flex';
                blockedDiv.style.display = 'none';
                
                // Resetear iframe
                iframeEmbed.src = '';
                
                console.log('🔄 Configurando detección de bloqueo (timeout: 3s)');
                
                // Detección de bloqueo con timeout
                var loadTimeout = setTimeout(function() {
                    console.warn('⚠️ Iframe bloqueado o timeout alcanzado - Mostrando fallback');
                    
                    // Mostrar mensaje de fallback
                    blockedDiv.style.display = 'block';
                    console.log('📦 Fallback visible:', blockedDiv.style.display);
                    
                    // Configurar botón de abrir en pestaña
                    if (btnAbrirPestana) {
                        btnAbrirPestana.onclick = function() {
                            console.log('🔗 Abriendo en nueva pestaña:', window.currentIframeURL);
                            window.open(window.currentIframeURL, '_blank', 'noopener,noreferrer');
                        };
                        console.log('✅ Botón "Abrir en pestaña" configurado');
                    } else {
                        console.error('❌ Botón "Abrir en pestaña" no encontrado');
                    }
                }, 3000);
                
                // Si carga correctamente, cancelar el timeout
                iframeEmbed.onload = function() {
                    clearTimeout(loadTimeout);
                    console.log('✅ Iframe cargado correctamente - Timeout cancelado');
                };
                
                // Detectar error de carga
                iframeEmbed.onerror = function() {
                    clearTimeout(loadTimeout);
                    console.error('❌ Error al cargar iframe - Mostrando fallback inmediatamente');
                    blockedDiv.style.display = 'block';
                };
                
                // Cargar URL DESPUÉS de configurar listeners
                iframeEmbed.src = url;
            };
            
            // Función global para cerrar iframe y volver al mapa
            window.cerrarIframe = function() {
                console.log('🗺️ Volviendo al mapa');
                
                // Mostrar mapa
                if (mapaDiv) mapaDiv.style.display = 'block';
                
                // Mostrar mini-mapa
                if (miniMapaContainer) miniMapaContainer.style.display = 'block';
                
                // Mostrar botones de navegación (PREV/NEXT DAY)
                var navButtons = document.querySelectorAll('.nav-btn');
                navButtons.forEach(function(btn) {
                    // Restaurar display según si es button o span
                    if (btn.tagName === 'BUTTON') {
                        btn.style.display = 'flex';
                    } else {
                        btn.style.display = 'flex'; // Los spans disabled también usan flex
                    }
                });
                
                // Mostrar panel de autoplay
                var autoplayPanel = document.getElementById('autoplay-controls');
                if (autoplayPanel) {
                    autoplayPanel.style.display = 'flex';
                }
                
                // Ocultar iframe
                if (iframeContainer) iframeContainer.style.display = 'none';
                
                // Limpiar iframe (detener reproducción de videos)
                if (iframeEmbed) iframeEmbed.src = '';
                
                // Ocultar mensaje de bloqueo
                if (iframeBlocked) iframeBlocked.style.display = 'none';
                
                // Trigger resize event para re-renderizar el mapa
                if (typeof google !== 'undefined' && google.maps && map) {
                    google.maps.event.trigger(map, 'resize');
                }
                
                vistaActual = 'mapa';
                urlActual = '';
                console.log('✅ Vista cambiada a: MAPA');
            }
            
            // Event listener: Botón cerrar iframe
            if (btnCerrarIframe) {
                btnCerrarIframe.addEventListener('click', window.cerrarIframe);
            }
            
            // Event listener: Botón abrir en pestaña (header - siempre visible)
            if (btnAbrirPestanaHeader) {
                btnAbrirPestanaHeader.addEventListener('click', function() {
                    if (window.currentIframeURL) {
                        console.log('🔗 Abriendo en nueva pestaña desde header:', window.currentIframeURL);
                        window.open(window.currentIframeURL, '_blank', 'noopener,noreferrer');
                    } else {
                        console.warn('⚠️ No hay URL disponible');
                        alert('No hay URL disponible para abrir');
                    }
                });
                console.log('✅ Botón "Abrir en pestaña" (header) configurado');
            } else {
                console.error('❌ Botón "btn-abrir-pestaña-header" no encontrado');
            }
            
            // Event listener: Botón abrir en nueva pestaña (fallback)
            if (btnAbrirPestana) {
                btnAbrirPestana.addEventListener('click', function() {
                    if (urlActual) {
                        window.open(urlActual, '_blank', 'noopener,noreferrer');
                        console.log('🔗 Abriendo en nueva pestaña:', urlActual);
                    }
                });
            }
            
            // Detectar tecla ESC para cerrar iframe
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    var iframeContainer = document.getElementById('iframe-container');
                    if (iframeContainer && iframeContainer.style.display === 'flex') {
                        window.cerrarIframe();
                    }
                }
            });
            
            console.log('✅ Sistema de iframe toggle configurado');
        }
        
        // Llamar después de que el mapa se inicialice
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupIframeToggle);
        } else {
            setupIframeToggle();
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAr-0laMZAuIYHZGPikz99ITdYvcC5ye0A&libraries=geometry&callback=initMapDia"></script>
    <?php
}, 100);

get_footer();
