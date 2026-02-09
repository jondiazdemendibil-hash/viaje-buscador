<?php
// === Cargar estilos/JS del tema hijo y Leaflet ===
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style('avada-child', get_stylesheet_uri(), [], '1.1');
  // Leaflet
  wp_enqueue_style('leaflet-css','https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',[], null);
  wp_enqueue_script('leaflet-js','https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',[], null, true);
});

add_theme_support('post-thumbnails');
add_theme_support('responsive-embeds');

// === CPTs y taxonomías ===
add_action('init', function(){

  // CPT Etapas (Barcelona→Estambul, etc.)
  register_post_type('etapa', [
    'label' => 'Etapas',
    'public' => true,
    'menu_icon' => 'dashicons-location',
    'supports' => ['title','editor','thumbnail','excerpt','custom-fields'],
    'rewrite' => ['slug' => 'etapas'],
    'show_in_rest' => true
  ]);

  // CPT Escalas (puertos/ciudades dentro de una etapa)
  register_post_type('escala', [
    'label' => 'Escalas',
    'public' => true,
    'menu_icon' => 'dashicons-location-alt',
    'supports' => ['title','editor','thumbnail','excerpt','custom-fields'],
    'rewrite' => ['slug' => 'escalas'],
    'show_in_rest' => true
  ]);

  // Taxonomía Regiones (Mediterráneo, Caribe, etc.)
  register_taxonomy('region', ['etapa','escala'], [
    'label' => 'Regiones',
    'hierarchical' => true,
    'rewrite' => ['slug' => 'region'],
    'show_in_rest' => true
  ]);
});

// === Shortcode: Mapa interactivo con Leaflet que carga un KML ===
// Uso: [itinerary_map kml="/wp-content/uploads/Regent_BCN_Est_Trieste_BCN_Miami.kml" height="600px" zoom="2"]
add_shortcode('itinerary_map', function($atts){
  $a = shortcode_atts([
    'kml'   => '',
    'height'=> '600px',
    'zoom'  => '2'
  ], $atts);
  $mapid = 'map_'.wp_generate_uuid4();
  ob_start(); ?>
  <div id="<?php echo esc_attr($mapid); ?>" style="width:100%; height:<?php echo esc_attr($a['height']); ?>; border-radius:12px; overflow:hidden;"></div>
  <script>
  (function(){
    const map = L.map('<?php echo esc_js($mapid); ?>', {worldCopyJump:true}).setView([20,0], <?php echo (int)$a['zoom']; ?>);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);

    const kmlUrl = '<?php echo esc_js($a['kml']); ?>';
    if(!kmlUrl){ return; }

    fetch(kmlUrl).then(r=>r.text()).then(str=>{
      const parser = new DOMParser();
      const xml = parser.parseFromString(str, "text/xml");

      // Dibujar LineStrings
      const coordsTags = xml.getElementsByTagName("coordinates");
      const layers = [];
      for (let i=0;i<coordsTags.length;i++){
        const raw = coordsTags[i].textContent.trim().split(/\s+/).map(p=>p.split(',').map(Number));
        const latlngs = raw.filter(c=>c.length>=2).map(c=>[c[1], c[0]]);
        if(latlngs.length>1){
          const pl = L.polyline(latlngs, {color:'#e11d48', weight:3, opacity:0.9});
          pl.addTo(map);
          layers.push(pl);
        }
      }

      // Waypoints (Placemark con Point)
      const placemarks = xml.getElementsByTagName("Placemark");
      for (let i=0;i<placemarks.length;i++){
        const name = placemarks[i].getElementsByTagName("name")[0]?.textContent || 'Punto';
        const desc = placemarks[i].getElementsByTagName("description")[0]?.textContent || '';
        const point = placemarks[i].getElementsByTagName("Point")[0];
        if(point){
          const cText = point.getElementsByTagName("coordinates")[0]?.textContent || '';
          const c = cText.trim().split(',').map(Number);
          if(c.length>=2){
            L.marker([c[1], c[0]]).addTo(map).bindPopup('<strong>'+name+'</strong><br>'+desc);
          }
        }
      }

      // Fit bounds
      let bounds = null;
      layers.forEach(l=>{
        bounds = bounds ? bounds.extend(l.getBounds()) : l.getBounds();
      });
      if(bounds) map.fitBounds(bounds.pad(0.2));
    }).catch(err=>{
      console.error('Error cargando KML', err);
    });
  })();
  </script>
  <?php return ob_get_clean();
});

// === Shortcode: Timeline de Etapas ===
// Ordena por meta _fecha_inicio (YYYY-MM-DD recomendado)
add_shortcode('itinerary_timeline', function(){
  $q = new WP_Query([
    'post_type' => 'etapa',
    'posts_per_page' => -1,
    'meta_key' => '_fecha_inicio',
    'orderby' => 'meta_value',
    'order' => 'ASC'
  ]);
  ob_start(); ?>
  <div class="itinerary-timeline">
    <?php while($q->have_posts()): $q->the_post();
      $fi = get_post_meta(get_the_ID(), '_fecha_inicio', true);
      $ff = get_post_meta(get_the_ID(), '_fecha_fin', true);
      $barco = get_post_meta(get_the_ID(), '_barco', true);
      $camarote = get_post_meta(get_the_ID(), '_camarote', true);
      $kml = get_post_meta(get_the_ID(), '_kml', true);
    ?>
    <div class="timeline-item" style="border-left:4px solid #e11d48; padding:12px 16px; margin:18px 0;">
      <h3 style="margin:0 0 4px;"><?php the_title(); ?></h3>
      <p style="margin:0 0 6px;"><strong><?php echo esc_html($fi); ?></strong> → <strong><?php echo esc_html($ff); ?></strong></p>
      <?php if($barco): ?><p style="margin:0 0 6px;">Barco: <?php echo esc_html($barco); ?><?php if($camarote) echo ' — Camarote: '.esc_html($camarote); ?></p><?php endif; ?>
      <div style="margin:6px 0;"><?php the_excerpt(); ?></div>
      <?php if($kml): echo do_shortcode('[itinerary_map kml="'.$kml.'" height="320px" zoom="3"]'); endif; ?>
      <p style="margin-top:6px;"><a href="<?php the_permalink(); ?>">Ver detalle</a></p>
    </div>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>
  <?php return ob_get_clean();
});

// === Shortcode: Ficha de Etapa por ID ===
// Uso: [stage_detail id="123"]
add_shortcode('stage_detail', function($atts){
  $a = shortcode_atts(['id'=>0], $atts);
  $p = get_post($a['id']);
  if(!$p) return '';
  setup_postdata($p);
  $fi = get_post_meta($p->ID, '_fecha_inicio', true);
  $ff = get_post_meta($p->ID, '_fecha_fin', true);
  $barco = get_post_meta($p->ID, '_barco', true);
  $camarote = get_post_meta($p->ID, '_camarote', true);
  $hotel = get_post_meta($p->ID, '_hotel', true);
  $kml = get_post_meta($p->ID, '_kml', true);
  ob_start(); ?>
  <article class="stage-detail">
    <h2><?php echo esc_html(get_the_title($p)); ?></h2>
    <p><strong>Fechas:</strong> <?php echo esc_html($fi); ?> → <?php echo esc_html($ff); ?></p>
    <?php if($barco): ?><p><strong>Barco:</strong> <?php echo esc_html($barco); ?><?php if($camarote) echo ' — <strong>Camarote:</strong> '.esc_html($camarote); ?></p><?php endif; ?>
    <?php if($hotel): ?><p><strong>Hotel/Resort:</strong> <?php echo esc_html($hotel); ?></p><?php endif; ?>
    <div><?php echo apply_filters('the_content', $p->post_content); ?></div>
    <?php if($kml): echo do_shortcode('[itinerary_map kml="'.$kml.'" height="420px" zoom="3"]'); endif; ?>
  </article>
  <?php
  wp_reset_postdata();
  return ob_get_clean();
});
// Shortcode para renderizar el layout de tramo
function render_tramo_layout_shortcode() {
    ob_start();
    ?>
    <style>
        html {
            margin: 0 !important;
            padding: 0 !important;
            height: 100vh !important;
            max-height: 100vh !important;
            overflow: hidden !important;
            margin-top: 0 !important;
        }
        
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
            height: 100vh !important;
            max-height: 100vh !important;
        }
        
        #wpadminbar {
            display: none !important;
        }
        
        .fusion-header-wrapper,
        .fusion-header,
        .fusion-secondary-header,
        .fusion-header-v1,
        .fusion-header-v2,
        .fusion-header-v3,
        .fusion-header-v4,
        .fusion-header-v5,
        .fusion-header-v6,
        .fusion-header-v7 {
            display: none !important;
        }
        
        .fusion-footer,
        .fusion-footer-widget-area,
        .fusion-footer-copyright-area {
            display: none !important;
        }
        
        .fusion-logo {
            display: none !important;
        }
        
        .fusion-sliding-bar {
            display: none !important;
        }
        
        #main {
            padding: 0 !important;
            margin: 0 !important;
            padding-top: 0 !important;
            margin-top: 0 !important;
            height: 100vh !important;
            max-height: 100vh !important;
            overflow: hidden !important;
        }
        
        .fusion-footer,
        .fusion-header {
            display: none !important;
        }
        
        .tramo-wrapper {
            width: 100vw !important;
            max-width: none !important;
            margin-left: calc(-50vw + 50%) !important;
            margin-right: calc(-50vw + 50%) !important;
        }
        
        .tramo-container-full {
            display: flex !important;
            flex-direction: row !important;
            height: 100vh !important;
            min-height: 100vh !important;
            width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Spectral', serif;
            position: relative;
        }
        
        .tramo-ficha {
            width: 50% !important;
            min-width: 50% !important;
            max-width: 50% !important;
            flex: 0 0 50% !important;
            flex-shrink: 0 !important;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 25px;
            background: #FFFFFF;
            overflow-y: hidden !important;
            height: 100vh !important;
        }
        
        .tramo-ficha-content {
            max-width: 600px;
            text-align: center;
            max-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .tramo-titulo {
            font-weight: 300;
            font-size: 2.5rem;
            color: #1a1a1a;
            line-height: 1.1;
            margin-bottom: 20px;
        }
        
        .tramo-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .tramo-meta-col {
            text-align: center;
        }
        
        .tramo-meta-label {
            font-size: 0.75rem;
            color: #999999;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .tramo-meta-lugar,
        .tramo-meta-fecha {
            font-style: italic;
            font-size: 1.15rem;
            color: #555555;
        }
        
        .tramo-descripcion {
            font-size: 1rem;
            color: #444444;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .tramo-boton {
            display: inline-block;
            font-size: 0.85rem;
            color: #FFFFFF;
            background: #333333;
            border: 1px solid #333333;
            padding: 12px 35px;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .tramo-boton:hover {
            background: transparent;
            color: #333333;
        }
        
        .tramo-mapa {
            width: 50% !important;
            min-width: 50% !important;
            max-width: 50% !important;
            flex: 0 0 50% !important;
            flex-shrink: 0 !important;
            height: 100vh !important;
        }
        
        #mapa-tramo-custom {
            width: 100%;
            height: 100%;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <div class="tramo-wrapper">
        <div class="tramo-container-full">
            <div class="tramo-ficha">
                <div class="tramo-ficha-content">
                <?php
                // Obtener datos del capítulo relacionado
                $id_capitulo = get_field('id_capitulo');
                $mostrar_capitulo = false;
                if ($id_capitulo) {
                    $capitulo_query = new WP_Query([
                        'post_type' => 'capitulo',
                        'meta_key' => 'id_capitulo',
                        'meta_value' => $id_capitulo,
                        'posts_per_page' => 1
                    ]);
                    if ($capitulo_query->have_posts()) {
                        $capitulo_query->the_post();
                        $mostrar_capitulo = true;
                        $nombre_cap = strtoupper(get_the_title());
                        $sitio_inicio_cap = get_field('sitio_inicio_capitulo');
                        $pais_inicio_cap = get_field('pais_inicio_capitulo');
                        $fecha_inicio_cap = get_field('fecha_inicio_capitulo');
                        $sitio_fin_cap = get_field('sitio_fin_capitulo');
                        $pais_fin_cap = get_field('pais_fin_capitulo');
                        $fecha_fin_cap = get_field('fecha_fin_capitulo');
                        wp_reset_postdata();
                    }
                }
                ?>
                
                <?php if ($mostrar_capitulo): ?>
                <div class="tramo-capitulo" style="margin-bottom: 20px;">
                    <div style="font-size: 1.05rem; color: #999999; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 18px;">
                        <?php echo $nombre_cap; ?>
                    </div>
                    <div style="display: flex; justify-content: center; gap: 35px;">
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #999999; text-transform: uppercase; margin-bottom: 8px;">INICIO</div>
                            <div style="font-style: italic; font-size: 1.05rem; color: #999999;">
                                <?php echo $sitio_inicio_cap . ($pais_inicio_cap ? ', ' . $pais_inicio_cap : ''); ?>
                            </div>
                            <div style="font-style: italic; font-size: 1.05rem; color: #999999;">
                                <?php echo $fecha_inicio_cap ? date_i18n('l, j \d\e F \d\e Y', strtotime($fecha_inicio_cap)) : ''; ?>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #999999; text-transform: uppercase; margin-bottom: 8px;">FIN</div>
                            <div style="font-style: italic; font-size: 1.05rem; color: #999999;">
                                <?php echo $sitio_fin_cap . ($pais_fin_cap ? ', ' . $pais_fin_cap : ''); ?>
                            </div>
                            <div style="font-style: italic; font-size: 1.05rem; color: #999999;">
                                <?php echo $fecha_fin_cap ? date_i18n('l, j \d\e F \d\e Y', strtotime($fecha_fin_cap)) : ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <h1 class="tramo-titulo"><?php echo get_the_title(); ?></h1>
                
                <div class="tramo-meta">
                    <div class="tramo-meta-col">
                        <div class="tramo-meta-label">INICIO</div>
                        <div class="tramo-meta-lugar">
                            <?php 
                            $sitio_inicio = get_field('sitio_inicio_tramo');
                            $pais_inicio = get_field('pais_inicio_tramo');
                            echo $sitio_inicio . ($pais_inicio ? ', ' . $pais_inicio : '');
                            ?>
                        </div>
                        <div class="tramo-meta-fecha">
                            <?php 
                            $fecha_inicio = get_field('fecha_inicio_tramo');
                            echo $fecha_inicio ? date_i18n('l, j \d\e F \d\e Y', strtotime($fecha_inicio)) : '';
                            ?>
                        </div>
                    </div>
                    <div class="tramo-meta-col">
                        <div class="tramo-meta-label">FIN</div>
                        <div class="tramo-meta-lugar">
                            <?php 
                            $sitio_fin = get_field('sitio_fin_tramo');
                            $pais_fin = get_field('pais_fin_tramo');
                            echo $sitio_fin . ($pais_fin ? ', ' . $pais_fin : '');
                            ?>
                        </div>
                        <div class="tramo-meta-fecha">
                            <?php 
                            $fecha_fin = get_field('fecha_fin_tramo');
                            echo $fecha_fin ? date_i18n('l, j \d\e F \d\e Y', strtotime($fecha_fin)) : '';
                            ?>
                        </div>
                    </div>
                </div>
                
                <?php
                // 1. Calcular duración del tramo
                $fecha_inicio_tramo = get_field('fecha_inicio_tramo');
                $fecha_fin_tramo = get_field('fecha_fin_tramo');
                $duracion = 0;
                if ($fecha_inicio_tramo && $fecha_fin_tramo) {
                    $inicio = new DateTime($fecha_inicio_tramo);
                    $fin = new DateTime($fecha_fin_tramo);
                    $duracion = $inicio->diff($fin)->days + 1;
                }
                
                // 2. Obtener fecha de inicio del viaje completo (primer día D000001)
                $primer_dia_query = new WP_Query([
                    'post_type' => 'dia',
                    'meta_key' => 'id_dia',
                    'meta_value' => 'D000001',
                    'posts_per_page' => 1
                ]);
                
                $fecha_inicio_viaje = null;
                $dia_inicio_ordinal = 0;
                $dia_fin_ordinal = 0;
                
                if ($primer_dia_query->have_posts()) {
                    $primer_dia_query->the_post();
                    $fecha_inicio_viaje = get_field('fecha_dia');
                    wp_reset_postdata();
                    
                    // 3. Calcular día ordinal de inicio del tramo
                    if ($fecha_inicio_viaje && $fecha_inicio_tramo) {
                        $inicio_viaje = new DateTime($fecha_inicio_viaje);
                        $inicio_tramo = new DateTime($fecha_inicio_tramo);
                        $dias_diferencia = $inicio_viaje->diff($inicio_tramo)->days;
                        $dia_inicio_ordinal = $dias_diferencia + 1;
                        $dia_fin_ordinal = $dia_inicio_ordinal + $duracion - 1;
                    }
                }
                
                // Función para ordinales españoles
                function ordinal_es($num) {
                    return $num . 'º';
                }
                ?>
                
                <?php if ($duracion > 0): ?>
                <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
                    <div style="font-family: 'Spectral', serif; font-size: 0.95rem; color: #777777; margin-bottom: 6px;">
                        Tour de <?php echo $duracion; ?> día<?php echo $duracion > 1 ? 's' : ''; ?> de duración
                    </div>
                    <?php if ($dia_inicio_ordinal > 0): ?>
                    <div style="font-family: 'Spectral', serif; font-size: 0.95rem; color: #777777;">
                        Del <?php echo ordinal_es($dia_inicio_ordinal); ?> día al <?php echo ordinal_es($dia_fin_ordinal); ?> día del Gran Viaje
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="tramo-descripcion">
                    <?php echo get_field('descripcion_tramo'); ?>
                </div>
                
                <a href="#" class="tramo-boton">DESCUBRIR EL TOUR</a>
            </div>
        </div>
        
        <div class="tramo-mapa">
            <div id="mapa-tramo-custom"></div>
        </div>
    </div>
    
    <script>
        function initMapTramo() {
            const madrid = { lat: 40.4168, lng: -3.7038 };
            const mapStyle = [
                { "featureType": "poi", "stylers": [{ "visibility": "off" }] },
                { "featureType": "transit", "stylers": [{ "visibility": "off" }] }
            ];
            const map = new google.maps.Map(document.getElementById("mapa-tramo-custom"), {
                zoom: 12,
                center: madrid,
                mapTypeId: 'roadmap',
                disableDefaultUI: true,
                styles: mapStyle
            });
            new google.maps.Marker({ position: madrid, map: map });
        }
        if (typeof google !== 'undefined') {
            initMapTramo();
        } else {
            window.initMapTramo = initMapTramo;
        }
    </script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAr-0laMZAuIYHZGPikz99ITdYvcC5ye0A&callback=initMapTramo"></script>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('tramo_layout_full', 'render_tramo_layout_shortcode');
// ==========================================
// FORZAR PLANTILLA PARA SINGLE TRAMO
// ==========================================
add_filter('template_include', function($template) {
    if (is_singular('tramo')) {
        $custom_template = get_stylesheet_directory() . '/single-tramo-custom.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}, 99);

// Incluir AJAX handler del buscador global
require_once get_stylesheet_directory() . '/inc/ajax-buscador.php';

// Localizar script para AJAX
function buscador_enqueue_scripts() {
    if (is_page_template('page-buscador.php')) {
        // CSS del buscador
        wp_enqueue_style(
            'vw-buscador-css',
            get_stylesheet_directory_uri() . '/assets/buscador/buscador.css',
            [],
            filemtime(get_stylesheet_directory() . '/assets/buscador/buscador.css')
        );
        // JS del buscador (en footer, después de jQuery)
        wp_enqueue_script(
            'vw-buscador-js',
            get_stylesheet_directory_uri() . '/assets/buscador/buscador.js',
            ['jquery'],
            filemtime(get_stylesheet_directory() . '/assets/buscador/buscador.js'),
            true
        );
        // AJAX localization
        wp_localize_script('vw-buscador-js', 'buscadorAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('buscador_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'buscador_enqueue_scripts');
