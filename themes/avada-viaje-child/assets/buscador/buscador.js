        /* ═══════════════════════════════════════════════════════════════════════════
           ██████████████████████████████████████████████████████████████████████████
           ██                                                                      ██
           ██   🚨🚨🚨 TAREAS PENDIENTES PARA MIGRACIÓN A PHP 🚨🚨🚨               ██
           ██                                                                      ██
           ██   ⚠️  LEER OBLIGATORIAMENTE ANTES DE MIGRAR                         ██
           ██   ⚠️  ESTE ARCHIVO ES UN BORRADOR HTML - NO PRODUCCIÓN              ██
           ██                                                                      ██
           ██████████████████████████████████████████████████████████████████████████
           
           ┌─────────────────────────────────────────────────────────────────────────┐
           │ ÍNDICE DE TAREAS PENDIENTES                                            │
           ├─────────────────────────────────────────────────────────────────────────┤
           │                                                                         │
           │ TODO #1: TAB "BUSCAR TODO" — Integración API OpenAI                    │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-1]                                          │
           │                                                                         │
           │ TODO #2: TAB "LUGAR" — Google Maps API para lugares no disponibles     │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-2]                                          │
           │                                                                         │
           │ TODO #3: TAB "FECHA" — Calendario mejorado con restricciones           │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-3]                                          │
           │                                                                         │
           │ TODO #4: TAB "ACTIVIDAD" — Integración API OpenAI                      │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-4]                                          │
           │                                                                         │
           │ TODO #5: SCREEN 2 — Mapa con pines de Google Maps                      │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-5]                                          │
           │                                                                         │
           │ TODO #6: CONEXIÓN BD — WordPress/ACF con datos reales                  │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-6]                                          │
           │                                                                         │
           │ TODO #7: MAPA PINS DINÁMICOS — Sincronización con fichas visibles      │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-7]                                          │
           │                                                                         │
           │ TODO #8: BOTÓN SCROLL-TOP — Volver arriba                              │
           │          Estado: ❌ NO IMPLEMENTADO                                     │
           │          Buscar: [TODO-PHP-8]                                          │
           │                                                                         │
           │ TODO #9: INFINITE SCROLL — Carga AJAX real desde WordPress             │
           │          Estado: ⚠️ SIMULADO (funciona con datos fake)                 │
           │          Buscar: [TODO-PHP-9]                                          │
           │                                                                         │
           │ TODO #10: FAVORITOS — Corazón en fichas + Login + BD                   │
           │           Estado: ❌ NO IMPLEMENTADO                                    │
           │           Buscar: [TODO-PHP-10]                                        │
           └─────────────────────────────────────────────────────────────────────────┘
           
           ═══════════════════════════════════════════════════════════════════════════ */
          /* ═══════════════════════════════════════════════════════════════════════════
              [TODO-PHP-1] 🔴 TAB "BUSCAR TODO" — INTEGRACIÓN API OPENAI
              ═══════════════════════════════════════════════════════════════════════════
           
              ⚠️ PENDIENTE DE IMPLEMENTAR — REQUIERE PHP + API KEY DE OPENAI
              💰 COSTE: Uso de API OpenAI (pago por uso)
           
              OBJETIVO:
              Permitir búsquedas en lenguaje natural combinando múltiples criterios.
           
              EJEMPLOS DE BÚSQUEDAS QUE DEBE ENTENDER:
              - "Museos en París"
              - "Restaurantes con estrella Michelin"
              - "Cruceros por el Mediterráneo"
              - "Aventuras en África en verano"
              - "Spa y relax en Asia"
              - "Viajes románticos en febrero"
           
              IMPLEMENTACIÓN:
              1. Usuario escribe en lenguaje natural en el input
              2. Enviar query a API OpenAI (GPT-4 o GPT-3.5-turbo)
              3. OpenAI interpreta y extrae:
                  - Lugares mencionados
                  - Actividades mencionadas
                  - Fechas/temporadas mencionadas
                  - Otros filtros implícitos
              4. Convertir respuesta de OpenAI en filtros para nuestra BD
              5. Ejecutar búsqueda combinada en WordPress/ACF
              6. Mostrar resultados
           
              PROMPT SUGERIDO PARA OPENAI:
              "Analiza esta búsqueda de viajes y extrae los filtros en formato JSON:
              - lugar: string o null
              - actividad: string o null  
              - fecha_desde: YYYY-MM-DD o null
              - fecha_hasta: YYYY-MM-DD o null
              - keywords: array de palabras clave adicionales
           
              Búsqueda del usuario: {query}"
           
              NOTAS:
              - Requiere manejo de errores si OpenAI no responde
              - Cachear búsquedas frecuentes para reducir costes
              - Fallback a búsqueda tradicional si falla la API
           
              ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
              [TODO-PHP-2] 🔴 TAB "LUGAR" — GOOGLE MAPS API PARA LUGARES NO DISPONIBLES
              ═══════════════════════════════════════════════════════════════════════════
           
              ⚠️ PENDIENTE DE IMPLEMENTAR — REQUIERE PHP
              ✅ API de Google Maps YA INSTALADA en el proyecto
              ✅ Todos los lugares tienen geolocalización en ACF (lat/lng)
           
              LÓGICA A IMPLEMENTAR:
           
              1. Usuario busca un lugar en el tab "Lugar" (ej: "Tokio")
           
              2. Sistema busca en nuestra BD:
                  a) SI encontramos el lugar → Mostrar resultados normalmente
                  b) SI NO encontramos el lugar → Continuar al paso 3
           
              3. Usar Google Maps Geocoding API para:
                  a) Validar que el término buscado es un lugar real
                  b) Obtener coordenadas (lat/lng) del lugar buscado
                  c) Si NO es un lugar válido → Mostrar "Sin resultados"
           
              4. Si es un lugar válido pero NO lo tenemos en BD:
                  a) Obtener TODOS nuestros lugares con sus coordenadas de ACF
                  b) Calcular distancia desde el lugar buscado a cada uno de nuestros lugares
                  c) Ordenar por cercanía geográfica (menor distancia primero)
                  d) Seleccionar los 3-4 lugares MÁS CERCANOS
           
              5. Mostrar mensaje y sugerencias:
                  ┌─────────────────────────────────────────────────────────┐
                  │ No tenemos viajes a Tokio, pero te pueden interesar:   │
                  │                                                         │
                  │ 📍 Osaka (850 km)                                       │
                  │ 📍 Kioto (870 km)                                       │
                  │ 📍 Seúl (1,150 km)                                      │
                  │ 📍 Shanghái (1,780 km)                                  │
                  └─────────────────────────────────────────────────────────┘
           
              FÓRMULA HAVERSINE (calcular distancia entre coordenadas):
           
              function haversineDistance($lat1, $lng1, $lat2, $lng2) {
                    $R = 6371; // Radio de la Tierra en km
                    $dLat = deg2rad($lat2 - $lat1);
                    $dLng = deg2rad($lng2 - $lng1);
                    $a = sin($dLat/2) * sin($dLat/2) +
                          cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                          sin($dLng/2) * sin($dLng/2);
                    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                    return $R * $c; // Distancia en km
              }
           
              LLAMADA A GOOGLE MAPS GEOCODING API:
              https://maps.googleapis.com/maps/api/geocode/json?address={lugar}&key={API_KEY}
           
              ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
              [TODO-PHP-3] 🔴 TAB "FECHA" — CALENDARIO MEJORADO CON RESTRICCIONES
              ═══════════════════════════════════════════════════════════════════════════
           
              ⚠️ PENDIENTE DE IMPLEMENTAR — REQUIERE PHP + LIBRERÍA DE CALENDARIO
           
              PROBLEMAS DEL CALENDARIO ACTUAL:
              - Calendario HTML5 básico (input type="date")
              - Sin restricciones de fechas
              - Permite seleccionar "hasta" anterior a "desde"
              - Permite fechas pasadas
              - Permite fechas muy lejanas (infinitas)
           
              MEJORAS A IMPLEMENTAR:
           
              1. REEMPLAZAR CALENDARIO HTML5 POR LIBRERÍA PROFESIONAL:
                  - Flatpickr (recomendado, ligero)
                  - O Pikaday
                  - O Air Datepicker
           
              2. RESTRICCIONES DE FECHA "DESDE":
                  - Fecha mínima: HOY (no permitir fechas pasadas)
                  - Fecha máxima: HOY + 2 años (o según disponibilidad real en BD)
           
              3. RESTRICCIONES DE FECHA "HASTA":
                  - Fecha mínima: Fecha "DESDE" seleccionada + 1 día
                  - Fecha máxima: Fecha "DESDE" + 1 año (viaje máximo razonable)
                  - Si usuario cambia "DESDE", actualizar mínimo de "HASTA" automáticamente
           
              4. VALIDACIONES ADICIONALES:
                  - Si "HASTA" < "DESDE" → Mostrar error y resetear "HASTA"
                  - Deshabilitar fechas sin disponibilidad (opcional, según BD)
                  - Resaltar fechas con más oferta (opcional)
           
              5. UX MEJORADA:
                  - Selector de rango en un solo calendario (desde-hasta visual)
                  - Mostrar duración del viaje calculada: "12 días"
                  - Botones rápidos: "Fin de semana", "1 semana", "2 semanas", "1 mes"
           
              EJEMPLO CON FLATPICKR:
           
              flatpickr("#fecha-desde", {
                    minDate: "today",
                    maxDate: new Date().fp_incr(730), // +2 años
                    dateFormat: "d/m/Y",
                    locale: "es",
                    onChange: function(selectedDates) {
                         // Actualizar mínimo de "hasta"
                         fechaHastaPicker.set("minDate", selectedDates[0]);
                    }
              });
           
              ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
              [TODO-PHP-4] 🔴 TAB "ACTIVIDAD" — INTEGRACIÓN API OPENAI
              ═══════════════════════════════════════════════════════════════════════════
           
              ⚠️ PENDIENTE DE IMPLEMENTAR — REQUIERE PHP + API KEY DE OPENAI
              💰 COSTE: Uso de API OpenAI (pago por uso)
           
              OBJETIVO:
              Mejorar búsqueda de actividades con comprensión de lenguaje natural.
              Similar a TODO #1 pero específico para actividades.
           
              EJEMPLOS DE BÚSQUEDAS QUE DEBE ENTENDER:
              - "algo romántico" → Cenas, Spa, Cruceros al atardecer
              - "con niños" → Parques temáticos, Zoos, Playas tranquilas
              - "aventura extrema" → Rafting, Paracaidismo, Escalada
              - "relax total" → Spa, Retiros, Playas paradisíacas
              - "cultural" → Museos, Tours históricos, Monumentos
              - "gastronómico" → Restaurantes, Tours de comida, Mercados
           
              IMPLEMENTACIÓN:
              1. Usuario escribe descripción de actividad deseada
              2. Enviar a OpenAI para interpretar intención
              3. OpenAI devuelve categorías de actividad que coinciden
              4. Buscar en BD actividades de esas categorías
              5. Ordenar por relevancia
           
              PROMPT SUGERIDO PARA OPENAI:
              "El usuario busca actividades de viaje. Analiza su búsqueda y devuelve
              las categorías que mejor coinciden de esta lista:
              [Museos, Restaurantes, Spa, Aventura, Playa, Cultural, Cruceros, 
                Parques, Deportes, Naturaleza, Compras, Vida nocturna, Familiar]
           
              Búsqueda: {query}
           
              Responde SOLO con un JSON: { categorias: [...], keywords: [...] }"
           
              NOTAS:
              - Compartir lógica con TODO #1 donde sea posible
              - Cachear interpretaciones frecuentes
           
              ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
              [TODO-PHP-5] 🔴 SCREEN 2 — MAPA CON PINES DE GOOGLE MAPS
              ═══════════════════════════════════════════════════════════════════════════
           
              ⚠️ PENDIENTE DE IMPLEMENTAR — REQUIERE PHP + GOOGLE MAPS JS API
              ✅ API de Google Maps YA INSTALADA en el proyecto
              ✅ Todos los lugares tienen geolocalización en ACF (lat/lng)
           
              LAYOUT DE SCREEN 2:
              ┌─────────────────────────────────────────────────────────────────────────┐
              │ Barra: "París" · 31 resultados · ↩ Volver a buscar                     │
              ├─────────────────────────────────┬───────────────────────────────────────┤
              │                                 │                                       │
              │   ┌───────┐  ┌───────┐          │                                       │
              │   │ Ficha │  │ Ficha │          │            MAPA                       │
              │   │   1   │  │   2   │          │        Google Maps                    │
              │   └───────┘  └───────┘          │                                       │
              │                                 │                                       │
              │   ┌───────┐  ┌───────┐          │         ● ●                           │
              │   │ Ficha │  │ Ficha │          │       ●     ●                         │
              │   │   3   │  │   4   │          │                                       │
              │   └───────┘  └───────┘          │                                       │
              │                                 │                                       │
              │      60% ancho                  │         40% ancho                     │
              │   (scroll vertical)             │      (mapa sticky/fijo)               │
              │                                 │                                       │
              └─────────────────────────────────┴───────────────────────────────────────┘
           
              ESPECIFICACIONES DEL MAPA:
           
              1. PINES/MARCADORES:
                  - Usar estilo moderno de Google Maps (círculos, no pins clásicos)
                  - Un pin por cada resultado/ficha mostrada
                  - Color del pin: Acorde a la paleta del sitio
           
              2. INTERACCIÓN FICHA ↔ MAPA:
                  - Hover en ficha → Resaltar pin correspondiente en mapa
                  - Click en pin → Scroll a la ficha correspondiente (o popup)
                  - Popup del pin: Mini preview de la ficha
           
              3. COMPORTAMIENTO DEL MAPA:
                  - Auto-zoom para mostrar todos los pines visibles
                  - Mapa sticky (fijo) mientras se hace scroll en fichas
                  - En móvil: Mapa colapsable o en tab separado
           
              4. CÓDIGO BASE GOOGLE MAPS:
           
              function initResultsMap(results) {
                    const map = new google.maps.Map(document.getElementById("resultsMap"), {
                         zoom: 4,
                         center: { lat: 40.416775, lng: -3.703790 }, // Default: Madrid
                         styles: [...] // Estilo personalizado opcional
                    });
               
                    const bounds = new google.maps.LatLngBounds();
               
                    results.forEach((result, index) => {
                         const marker = new google.maps.Marker({
                              position: { lat: result.lat, lng: result.lng },
                              map: map,
                              title: result.titulo,
                              icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: 10,
                                    fillColor: "#0066cc",
                                    fillOpacity: 1,
                                    strokeWeight: 2,
                                    strokeColor: "#ffffff"
                              }
                         });
                   
                         bounds.extend(marker.getPosition());
                   
                         // Interacción con fichas
                         marker.addListener("click", () => {
                              scrollToCard(index);
                         });
                    });
               
                    map.fitBounds(bounds);
              }
           
              ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
              [TODO-PHP-6] 🔴 CONEXIÓN BD — WORDPRESS/ACF CON DATOS REALES
              ═══════════════════════════════════════════════════════════════════════════
           
              ⚠️ PENDIENTE DE IMPLEMENTAR — REQUIERE MIGRACIÓN COMPLETA A PHP
           
              ESTADO ACTUAL:
              - Este archivo usa datos MOCK/SIMULADOS en JavaScript
              - Array "places" contiene datos de ejemplo hardcodeados
              - No hay conexión real con WordPress ni ACF
           
              MIGRACIÓN NECESARIA:
           
              1. REEMPLAZAR DATOS MOCK POR QUERIES WORDPRESS:
           
                  // En PHP (functions.php o plugin)
                  function get_travel_places() {
                        $args = array(
                             'post_type' => 'lugar',  // O el CPT que corresponda
                             'posts_per_page' => -1,
                             'post_status' => 'publish'
                        );
                  
                        $places = array();
                        $query = new WP_Query($args);
                  
                        while ($query->have_posts()) {
                             $query->the_post();
                             $places[] = array(
                                  'id' => get_the_ID(),
                                  'titulo' => get_the_title(),
                                  'lugar' => get_field('lugar'),
                                  'lat' => get_field('geolocalizacion')['lat'],
                                  'lng' => get_field('geolocalizacion')['lng'],
                                  'fecha' => get_field('fecha'),
                                  'actividades' => get_field('actividades'),
                                  'imagen' => get_the_post_thumbnail_url(),
                                  // ... más campos ACF
                             );
                        }
                        wp_reset_postdata();
                  
                        return $places;
                  }
           
              2. PASAR DATOS A JAVASCRIPT VÍA wp_localize_script:
           
                  wp_localize_script('buscador-js', 'buscadorData', array(
                        'places' => get_travel_places(),
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('buscador_nonce')
                  ));
           
              3. IMPLEMENTAR AJAX PARA BÚSQUEDAS DINÁMICAS:
           
                  add_action('wp_ajax_search_places', 'ajax_search_places');
                  add_action('wp_ajax_nopriv_search_places', 'ajax_search_places');
              
                  function ajax_search_places() {
                        check_ajax_referer('buscador_nonce', 'nonce');
                  
                        $query = sanitize_text_field($_POST['query']);
                        $tab = sanitize_text_field($_POST['tab']);
                  
                        // Lógica de búsqueda según tab...
                  
                        wp_send_json_success($results);
                  }
           
              CAMPOS ACF ESPERADOS POR LUGAR/VIAJE:
              - titulo (texto)
              - lugar (texto o taxonomía)
              - geolocalizacion (Google Maps field) → lat, lng
              - fecha_inicio (date picker)
              - fecha_fin (date picker)
              - actividades (relación o checkboxes)
              - imagen_destacada (imagen)
              - precio (número)
              - descripcion (textarea)
           
              ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
             [TODO-PHP-7] 🔴 MAPA PINS DINÁMICOS — SINCRONIZACIÓN CON FICHAS VISIBLES
             ═══════════════════════════════════════════════════════════════════════════
             
             ⚠️ PENDIENTE DE IMPLEMENTAR — REQUIERE PHP + GOOGLE MAPS JS API
             ✅ API de Google Maps YA INSTALADA en el proyecto
             
             OBJETIVO:
             El mapa de resultados debe mostrar SOLO los pins de las fichas
             actualmente visibles en el viewport. Al hacer scroll, los pins
             deben actualizarse dinámicamente.
             
             COMPORTAMIENTO ESPERADO:
             
             1. CARGA INICIAL:
                - Detectar qué fichas están visibles en pantalla
                - Mostrar SOLO los pins de esas fichas en el mapa
                - Auto-zoom para encuadrar los pins visibles
             
             2. AL HACER SCROLL:
                - Detectar fichas que ENTRAN en el viewport → AÑADIR sus pins
                - Detectar fichas que SALEN del viewport → ELIMINAR sus pins
                - Re-ajustar zoom del mapa si es necesario
             
             3. INTERACCIÓN BIDIRECCIONAL:
                - Hover en ficha → Resaltar pin correspondiente (cambiar color/tamaño)
                - Click en pin → Scroll suave hasta la ficha correspondiente
                - Hover en pin → Resaltar ficha correspondiente
             
             IMPLEMENTACIÓN TÉCNICA:
             
             // Usar Intersection Observer para detectar fichas visibles
             const observer = new IntersectionObserver((entries) => {
                 entries.forEach(entry => {
                     const fichaId = entry.target.dataset.id;
                     if (entry.isIntersecting) {
                         addPinToMap(fichaId);
                     } else {
                         removePinFromMap(fichaId);
                     }
                 });
             }, {
                 root: null,
                 rootMargin: '0px',
                 threshold: 0.1 // 10% visible = cuenta como visible
             });
             
             // Observar todas las fichas
             document.querySelectorAll('.destination-card').forEach(card => {
                 observer.observe(card);
             });
             
             FUNCIONES NECESARIAS:
             - addPinToMap(fichaId) → Crear marcador en mapa
             - removePinFromMap(fichaId) → Eliminar marcador del mapa
             - highlightPin(fichaId) → Cambiar estilo del pin (hover)
             - resetPinStyle(fichaId) → Restaurar estilo normal
             - fitMapToPins() → Ajustar zoom para mostrar todos los pins visibles
             
             ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
             [TODO-PHP-8] 🔴 BOTÓN SCROLL-TOP — VOLVER ARRIBA
             ═══════════════════════════════════════════════════════════════════════════
             
             ⚠️ PENDIENTE DE IMPLEMENTAR — JAVASCRIPT PURO (NO REQUIERE PHP)
             
             OBJETIVO:
             Botón flotante que aparece al hacer scroll y permite volver arriba.
             
             ESPECIFICACIONES DE DISEÑO (Estilo Avada):
             
             - Posición: fixed, bottom: 10px, right: 10px
             - Tamaño: 45px x 45px
             - Forma: Cuadrado con border-radius: 8px
             - Fondo: #1a1a1a (negro)
             - Icono: SVG chevron hacia arriba, color blanco
             - Sombra: box-shadow: 0 2px 10px rgba(0,0,0,0.2)
             - Z-index: 99999
             
             COMPORTAMIENTO:
             
             1. VISIBILIDAD:
                - Oculto por defecto (display: none)
                - Visible cuando scroll > 300px (display: flex)
                - Transición suave al aparecer/desaparecer
             
             2. HOVER:
                - Fondo cambia a blanco (#fff)
                - Icono cambia a negro (#1a1a1a)
                - Transición: 0.3s ease
             
             3. CLICK:
                - Scroll suave hacia arriba (top: 0)
                - Usar behavior: 'smooth' o fallback para navegadores antiguos
             
             CÓDIGO HTML:
             
             <div id="scrollTopBtn" 
                  onclick="window.scrollTo({top:0,behavior:'smooth'})"
                  onmouseenter="this.style.background='#fff';this.querySelector('svg').style.stroke='#1a1a1a';"
                  onmouseleave="this.style.background='#1a1a1a';this.querySelector('svg').style.stroke='#fff';">
                 <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                     <path d="M18 15l-6-6-6 6"/>
                 </svg>
             </div>
             
             CÓDIGO JS PARA MOSTRAR/OCULTAR:
             
             window.addEventListener('scroll', function() {
                 var btn = document.getElementById('scrollTopBtn');
                 if (window.pageYOffset > 300 || document.documentElement.scrollTop > 300) {
                     btn.style.display = 'flex';
                 } else {
                     btn.style.display = 'none';
                 }
             });
             
             ═══════════════════════════════════════════════════════════════════════════ */

          /* ═══════════════════════════════════════════════════════════════════════════
             [TODO-PHP-9] ⚠️ INFINITE SCROLL — REEMPLAZAR SIMULACIÓN POR AJAX REAL
             ═══════════════════════════════════════════════════════════════════════════
             
             ⚠️ ACTUALMENTE SIMULADO — REQUIERE PHP + AJAX PARA PRODUCCIÓN
             
             ESTADO ACTUAL:
             - El infinite scroll FUNCIONA visualmente
             - Carga fichas simuladas con datos fake
             - Detecta correctamente el scroll cerca del final
             - Muestra loader y mensaje de fin
             
             CAMBIOS NECESARIOS PARA PRODUCCIÓN:
             
             1. CREAR ENDPOINT AJAX EN WORDPRESS:
             
                // En functions.php o plugin
                add_action('wp_ajax_load_more_results', 'ajax_load_more_results');
                add_action('wp_ajax_nopriv_load_more_results', 'ajax_load_more_results');
                
                function ajax_load_more_results() {
                    check_ajax_referer('buscador_nonce', 'nonce');
                    
                    $page = intval($_POST['page']);
    
    
         $per_page = 8;
                    $filters = json_decode(stripslashes($_POST['filters']), true);
                    
                    $args = array(
                        'post_type' => 'viaje',
                        'posts_per_page' => $per_page,
                        'paged' => $page,
                        'orderby' => 'meta_value',
                        'meta_key' => 'fecha_inicio',
                        'order' => 'ASC',
                        // Aplicar filtros según $filters...
                    );
                    
                    $query = new WP_Query($args);
                    $results = array();
                    
                    while ($query->have_posts()) {
                        $query->the_post();
                        $results[] = array(
                            'id' => get_the_ID(),
                            'titulo' => get_the_title(),
                            'linea1' => get_field('linea1'),
                            'linea2' => get_field('linea2'),
                            'imagen' => get_the_post_thumbnail_url(null, 'medium'),
                            'lat' => get_field('geolocalizacion')['lat'],
                            'lng' => get_field('geolocalizacion')['lng'],
                        );
                    }
                    
                    wp_send_json_success(array(
                        'items' => $results,
                        'has_more' => $query->max_num_pages > $page
                    ));
                }
             
             2. REEMPLAZAR loadMoreResults() POR LLAMADA AJAX:
             
                async function loadMoreResults() {
                    if (isLoading || allItemsLoaded) return;
                    
                    isLoading = true;
                    loader.classList.add('visible');
                    
                    const formData = new FormData();
                    formData.append('action', 'load_more_results');
                    formData.append('nonce', buscadorData.nonce);
                    formData.append('page', currentPage);
                    formData.append('filters', JSON.stringify(currentFilters));
                    
                    try {
                        const response = await fetch(buscadorData.ajaxurl, {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();
                        
                        if (data.success && data.data.items.length > 0) {
                            data.data.items.forEach(item => {
                                const card = createCardFromData(item);
                                resultsGrid.appendChild(card);
                            });
                            currentPage++;
                            
                            if (!data.data.has_more) {
                                allItemsLoaded = true;
                                endMessage.classList.add('visible');
                            }
                        } else {
                            allItemsLoaded = true;
                            endMessage.classList.add('visible');
                        }
                    } catch (error) {
                        console.error('Error cargando resultados:', error);
                    }
                    
                    isLoading = false;
                    loader.classList.remove('visible');
                }
             
             3. FUNCIÓN PARA CREAR FICHA DESDE DATOS REALES:
             
                function createCardFromData(item) {
                    const card = document.createElement('div');
                    card.className = 'destination-card';
                    card.dataset.id = item.id;
                    card.dataset.lat = item.lat;
                    card.dataset.lng = item.lng;
                    card.innerHTML = `
                        <div class="card-image">
                            <img src="${item.imagen}" alt="${item.titulo}">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">${item.titulo}</h3>
                            <p class="card-line1">${item.linea1}</p>
                            <p class="card-line2">${item.linea2}</p>
                        </div>
                    `;
                    return card;
                }
             
             ORDENACIÓN POR FECHA:
             - Los resultados se ordenan por fecha_inicio ASC (más próximos primero)
             - Esto ya está configurado en el WP_Query del endpoint
             
             ═══════════════════════════════════════════════════════════════════════════ */

             /* ═══════════════════════════════════════════════════════════════════════════
                 [TODO-PHP-10] ❌ FAVORITOS — CORAZÓN EN FICHAS + LOGIN + BD
                 ═══════════════════════════════════════════════════════════════════════════
             
                 ❌ NO IMPLEMENTADO — REQUIERE LOGIN + PHP + BD
             
                 OBJETIVO:
                 Permitir a usuarios logueados guardar viajes como favoritos.
             
                 DISEÑO DEL CORAZÓN:
                 - Icono en esquina superior derecha de cada ficha
                 - Fondo: círculo blanco semitransparente (rgba(255,255,255,0.9))
                 - Tamaño: 36px x 36px
                 - Icono: SVG corazón, stroke negro, fill none
                 - Hover: scale(1.1), fondo blanco sólido
                 - Active (favorito): stroke rojo (#e53935), fill rojo
             
                 REQUISITOS:
             
                 1. SISTEMA DE LOGIN (previo):
                     - Acceso solo por invitación personal
                     - Sin registro público
             
                 2. TABLA EN BD:
                     wp_user_favorites (user_id, post_id, created_at)
             
                 3. ENDPOINTS AJAX:
                     - save_favorite: Guardar favorito
                     - remove_favorite: Eliminar favorito
                     - get_user_favorites: Cargar favoritos del usuario al iniciar
             
                 4. IMPLEMENTAR EN:
                     - Fichas del carrusel de recomendaciones
                     - Fichas del grid de resultados
                     - Fichas generadas por infinite scroll
             
                 ═══════════════════════════════════════════════════════════════════════════ */

          // ═══════════════════════════════════════════════════════
          // DEBUG MODE
          // ═══════════════════════════════════════════════════════
        const DEBUG = false; // Cambiar a true para activar logs de debug
        const log = (...args) => { if (DEBUG) console.log(...args); };
        const warn = (...args) => { if (DEBUG) console.warn(...args); };
        const error = (...args) => { if (DEBUG) console.error(...args); };

        // ═══════════════════════════════════════════════════════
        // FAVORITOS - LOCALSTORAGE (DEMO)
        // ═══════════════════════════════════════════════════════
        const FAVORITES_KEY = 'traveler_favorites_v1';
        let favoriteIds = new Set();

        function loadFavorites() {
            try {
                const raw = localStorage.getItem(FAVORITES_KEY);
                const parsed = raw ? JSON.parse(raw) : [];
                favoriteIds = new Set(Array.isArray(parsed) ? parsed : []);
            } catch (err) {
                favoriteIds = new Set();
                warn('⚠️ No se pudo cargar favoritos:', err);
            }
        }

        function saveFavorites() {
            try {
                localStorage.setItem(FAVORITES_KEY, JSON.stringify(Array.from(favoriteIds)));
            } catch (err) {
                warn('⚠️ No se pudo guardar favoritos:', err);
            }
        }

        function toFavSlug(text) {
            return String(text || '')
                .trim()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        }

        function syncFavoriteButtons() {
            document.querySelectorAll('.fav-btn').forEach((btn) => {
                const id = btn.dataset.favId || '';
                const isFav = id && favoriteIds.has(id);
                btn.classList.toggle('is-fav', isFav);
                btn.setAttribute('aria-pressed', isFav ? 'true' : 'false');
                btn.setAttribute('aria-label', isFav ? 'Quitar de favoritos' : 'Añadir a favoritos');
            });
        }

        document.addEventListener('click', (event) => {
            const btn = event.target.closest('.fav-btn');
            if (!btn) return;
            event.preventDefault();
            event.stopPropagation();

            const id = btn.dataset.favId;
            if (!id) return;

            if (favoriteIds.has(id)) {
                favoriteIds.delete(id);
            } else {
                favoriteIds.add(id);
            }
            saveFavorites();
            syncFavoriteButtons();
        });

        document.addEventListener('DOMContentLoaded', () => {
            loadFavorites();
            syncFavoriteButtons();
        });

          /* ═══════════════════════════════════════════════════════════════
              📍 DOCUMENTACIÓN: BÚSQUEDA POR LUGAR CON GOOGLE MAPS API
              ═══════════════════════════════════════════════════════════════
           
              CONTEXTO:
              - Todos nuestros lugares tienen geolocalización en ACF (lat/lng)
              - La API de Google Maps ya está instalada y configurada
           
              LÓGICA A IMPLEMENTAR EN MIGRACIÓN A PHP:
           
              1. Usuario busca un lugar en el tab "Lugar" (ej: "Tokio")
           
              2. Sistema busca en nuestra BD:
                  a) SI encontramos el lugar → Mostrar resultados normalmente
                  b) SI NO encontramos el lugar → Paso 3
           
              3. Usar Google Maps API para:
                  a) Validar que el término buscado es un lugar real
                  b) Obtener coordenadas (lat/lng) del lugar buscado
           
              4. Si es un lugar válido pero NO lo tenemos:
                  a) Calcular distancia desde el lugar buscado a TODOS nuestros lugares
                  b) Ordenar por cercanía geográfica
                  c) Ofrecer los 3-4 lugares MÁS CERCANOS que SÍ tenemos
           
              5. Mostrar mensaje tipo:
                  "No tenemos viajes a Tokio, pero te pueden interesar:"
                  - Osaka (850 km)
                  - Kioto (870 km)
                  - Seúl (1,150 km)
           
              FÓRMULA DE DISTANCIA:
              Usar fórmula Haversine para calcular distancia entre dos coordenadas:
           
              function haversineDistance(lat1, lng1, lat2, lng2) {
                    const R = 6371; // Radio de la Tierra en km
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLng = (lng2 - lng1) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                 Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                                 Math.sin(dLng/2) * Math.sin(dLng/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return R * c; // Distancia en km
              }
           
              NOTAS:
              - Esta lógica SOLO aplica al tab "Lugar"
              - El tab "Buscar todo" no usa esta funcionalidad
              - Requiere llamada AJAX a Google Maps Geocoding API
           
              ═══════════════════════════════════════════════════════════════ */

          // ═══════════════════════════════════════════════════════
          // DATOS FAKE
          // ═══════════════════════════════════════════════════════
        const __vwBuscadorRoot = document.querySelector('.vw-buscador');
        const __vwBuscadorEnabled = Boolean(__vwBuscadorRoot);
        const buscadorConfig = window.VW_Buscador || {};
        const buscadorEndpoints = buscadorConfig.endpoints || {};
        const buscadorNonce = buscadorConfig.nonce || '';

        if (__vwBuscadorEnabled) {
        const injectedData = (buscadorConfig.data && typeof buscadorConfig.data === 'object') ? buscadorConfig.data : null;
        const data = {
            lugares: [
                { nombre: 'París', count: 12 },
                { nombre: 'Barcelona', count: 8 },
                { nombre: 'Roma', count: 5 },
                { nombre: 'Marrakech', count: 10 },
                { nombre: 'Parc Güell', count: 1 },
                { nombre: 'Versalles', count: 3 },
                { nombre: 'Lisboa', count: 7 },
                { nombre: 'Madrid', count: 9 },
                { nombre: 'Berlín', count: 6 },
                { nombre: 'Ámsterdam', count: 4 }
            ],
            actividades:  [
                { nombre: 'Museo del Louvre', lugar: 'París' },
                { nombre: 'Spa Le Bristol', lugar: 'París' },
                { nombre: 'Torre Eiffel', lugar: 'París' },
                { nombre: 'Parque Güell', lugar: 'Barcelona' },
                { nombre: 'Sagrada Familia', lugar: 'Barcelona' },
                { nombre:  'Paracaidismo', lugar: 'Interlaken' },
                { nombre: 'Catedral de Notre Dame', lugar: 'París' },
                { nombre: 'Coliseo Romano', lugar: 'Roma' },
                { nombre: 'Museo del Prado', lugar: 'Madrid' },
                { nombre: 'Canales de Ámsterdam', lugar: 'Ámsterdam' }
            ],
            hospedajes: [
                { nombre: 'Mandarin Oriental Ritz', lugar: 'Madrid' },
                { nombre: 'Royal Mansour', lugar: 'Marrakech' },
                { nombre:  'Crucero Silver Ray', lugar: 'Lisboa' },
                { nombre: 'Fairmont Tazi Palace', lugar: 'Tánger' },
                { nombre: 'Hotel Arts Barcelona', lugar: 'Barcelona' }
            ],
            restaurantes:  [
                { nombre: 'DiverXO', categoria: '3 Estrellas Michelin', lugar: 'Madrid' },
                { nombre: 'Restaurante Coque', categoria: '1 Estrella Michelin', lugar: 'Madrid' },
                { nombre: 'Casa José', categoria: '1 Estrella Michelin', lugar: 'Aranjuez' },
                { nombre: 'Le Bernardin', categoria: '3 Estrellas Michelin', lugar: 'París' },
                { nombre: 'Osteria Francescana', categoria: '3 Estrellas Michelin', lugar: 'Roma' }
            ],
            dias: [
                { fecha: '02/05/2026', hito: 'Cierre del viaje en Sevilla', lugar: 'Sevilla' },
                { fecha: '26/04/2026', hito: 'Inicio del Tour: Tren Al-Andalus', lugar: 'Madrid' },
                { fecha: '15/06/2027', hito: 'Spa Le Bristol', lugar: 'París' }
            ]
        };

        const demoPlaceCoords = {
            'parís': { lat: 48.8566, lng: 2.3522 },
            'madrid': { lat: 40.4168, lng: -3.7038 },
            'sevilla': { lat: 37.3891, lng: -5.9845 },
            'versalles': { lat: 48.8049, lng: 2.1204 },
            'barcelona': { lat: 41.3851, lng: 2.1734 },
            'marrakech': { lat: 31.6295, lng: -7.9811 }
        };

        // ═══════════════════════════════════════════════════════
        // DEMO: FALLBACK GEOGRÁFICO
        // ═══════════════════════════════════════════════════════
        const DEMO_GEO_FALLBACK = true;
        
        // Mock de lugares cercanos por query normalizada (solo para demo)
        // En producción, esto vendría de geocoding + haversine contra BD
        const geoDemoFallback = {
            'valencia': ['Barcelona', 'Madrid', 'Lisboa'],
            'sevilla': ['Madrid', 'Lisboa', 'Marrakech'],
            'toledo': ['Madrid', 'Lisboa', 'Barcelona'],
            'granada': ['Madrid', 'Marrakech', 'Barcelona'],
            'bilbao': ['Madrid', 'Barcelona', 'París'],
            'zaragoza': ['Barcelona', 'Madrid', 'Berlín'],
            'malaga': ['Marrakech', 'Madrid', 'Lisboa'],
            'valencia': ['Barcelona', 'Madrid', 'Roma'],
            'tarragona': ['Barcelona', 'Madrid', 'Roma'],
            // Caso especial: Santiago tiene ambigüedad geográfica
            'santiago': null // null indica que debe mostrar mensaje de ambigüedad
        };
        
        // Opciones para casos ambiguos (Santiago tiene varios lugares con ese nombre)
        const geoAmbiguousOptions = {
            'santiago': [
                'Santiago, Chile',
                'Santiago de Compostela, España',
                'Santiago de Cuba, Cuba',
                'Santiago del Estero, Argentina',
                'Santiago de Querétaro, México'
            ]
        };

        // ═══════════════════════════════════════════════════════
        // VARIABLES
        // ═══════════════════════════════════════════════════════
        const searchInput = document.getElementById('searchInput');
        const clearButton = document.getElementById('clearButton');
        const suggestionsDropdown = document.getElementById('suggestionsDropdown');
        const tabButtons = document.querySelectorAll('.tab-button');
        const resultsGrid = document.querySelector('.results-grid');
        const resultsHeader = document.querySelector('.results-header');
        const emptyStateInitial = document.getElementById('emptyStateInitial');
        const emptyStateNoResults = document.getElementById('emptyStateNoResults');
        const defaultNoResultsCopy = emptyStateNoResults ? emptyStateNoResults.textContent.trim() : '';
        const DATE_NO_RESULTS_TEXT = 'Sin resultados para esa fecha.';
        const DATE_SELECT_PROMPT_TEXT = 'Selecciona una fecha.';
        const DATE_RANGE_HINT_TEXT = 'Hasta debe ser posterior o igual a Desde.';
        const countShowing = document.getElementById('countShowing');
        const countTotal = document.getElementById('countTotal');
        const searchLockedChip = document.getElementById('searchLockedChip');
        const searchLockedText = document.getElementById('searchLockedText');
        const searchLockedClose = document.getElementById('searchLockedClose');
        const headerBarOriginal = document.querySelector('.header-bar:not(.header-bar-results)');
        const headerBarResults = document.getElementById('headerBarResults');
        const resultsQueryEl = document.getElementById('resultsQuery');
        const resultsTotalBar = document.getElementById('resultsTotalBar');
        const btnBackSearch = document.getElementById('btnBackSearch');
        const mobileTabButton = document.getElementById('mobileTabButton');
        const mobileTabDropdown = document.getElementById('mobileTabDropdown');
        const mobileTabLabel = document.getElementById('mobileTabLabel');
        const mobileTabOptions = document.querySelectorAll('.mobile-tab-option');
        const mapToggleButton = document.getElementById('mapToggleButton');
        const mapMobileWrapper = document.getElementById('mapMobileWrapper');
        const mobileMapArea = document.getElementById('mobileMapArea');
        const fechaRangeHint = document.getElementById('fechaRangeHint');
        let currentTab = 'todo';
        let selectedIndex = -1;
        let queryLocked = false; // Flag para modo confirmado
        let confirmedSelection = null; // Almacena la selección confirmada {value, tab}
        let mapExpanded = false; // Estado del mapa en móvil
        let dateMessagePersistent = false;
        let dateMessageTimeout = null;
        let suppressRangeHint = false;
        let hasExplicitSelection = false;
        let explicitSelectedValue = '';
        let lastConfirmedQuery = '';

        // ═══════════════════════════════════════════════════════
        // MODO ESTRICTO: Selección robusta de pantallas + entrada
        // ═══════════════════════════════════════════════════════
        const SCREEN_SELECTORS = {
            screen1: [
                '#screen1',
                '.screen-1',
                '.screen1',
                '.vw-screen-1',
                '.buscador-screen-1',
                '[data-screen="1"]',
                '[data-screen="screen1"]',
                '[data-screen="search"]',
                '[data-screen="buscador"]'
            ],
            screen2: [
                '#screen2',
                '.screen-2',
                '.screen2',
                '.vw-screen-2',
                '.buscador-screen-2',
                '[data-screen="2"]',
                '[data-screen="screen2"]',
                '[data-screen="results"]',
                '[data-screen="resultados"]'
            ]
        };

        let __vwWarnedMissingScreens = false;

        function resolveScreenElement(selectors) {
            if (!selectors || selectors.length === 0) return null;
            for (const selector of selectors) {
                const scoped = __vwBuscadorRoot ? __vwBuscadorRoot.querySelector(selector) : null;
                if (scoped) return scoped;
                const global = document.querySelector(selector);
                if (global) return global;
            }
            return null;
        }

        const screen1El = resolveScreenElement(SCREEN_SELECTORS.screen1);
        const screen2El = resolveScreenElement(SCREEN_SELECTORS.screen2);
        const screensReady = Boolean(screen1El && screen2El);

        if (!screensReady && !__vwWarnedMissingScreens) {
            __vwWarnedMissingScreens = true;
            console.warn('[Buscador] No se pudieron localizar Screen 1 y/o Screen 2. Se omite el modo estricto.', {
                screen1Found: Boolean(screen1El),
                screen2Found: Boolean(screen2El)
            });
        }

        const screen1SearchInputs = screen1El
            ? Array.from(screen1El.querySelectorAll(
                '#searchInput, input[type="search"], input[type="text"], input[name*="search" i], input[id*="search" i], input[placeholder*="buscar" i]'
            )).filter((input) => input && input.type !== 'date' && input.type !== 'datetime-local')
            : [];

        const screen1SearchButton = screen1El
            ? screen1El.querySelector('#searchButton, .search-button, .btn-search, button[type="submit"], button[aria-label*="buscar" i], .search-submit')
            : null;

        // ═══════════════════════════════════════════════════════
        // SCREEN 2: Layout 60/40 (Resultados + Mapa)
        // ═══════════════════════════════════════════════════════
        let __vwScreen2LayoutReady = false;
        let __vwScreen2ResizeBound = false;
        let __vwScreen2ResizeTimer = null;

        function updateScreen2LayoutVars(screen2) {
            if (!screen2) return;
            // Medir el top real del #screen2 en el viewport (incluye header Avada, admin bar, titlebar, etc.)
            const rect = screen2.getBoundingClientRect();
            const topOffset = Math.ceil(Math.max(0, rect.top));
            document.documentElement.style.setProperty('--vw-screen2-titlebar-h', `${topOffset}px`);
        }

        function ensureScreen2Layout() {
            const screen2 = screen2El || resolveScreenElement(SCREEN_SELECTORS.screen2) || document.querySelector('#screen2');
            if (!screen2) return;

            const resultsGrid = screen2.querySelector('#resultsGrid') || screen2.querySelector('.results-grid');
            const mapEl = screen2.querySelector('#resultsMap') || screen2.querySelector('.results-map') || screen2.querySelector('.map-placeholder');

            if (!resultsGrid || !mapEl) {
                console.warn('[Buscador] No se pudo preparar Screen 2 (grid/mapa no encontrados).', {
                    gridFound: Boolean(resultsGrid),
                    mapFound: Boolean(mapEl)
                });
                return;
            }

            updateScreen2LayoutVars(screen2);

            if (!__vwScreen2ResizeBound) {
                __vwScreen2ResizeBound = true;
                window.addEventListener('resize', () => {
                    if (__vwScreen2ResizeTimer) window.clearTimeout(__vwScreen2ResizeTimer);
                    __vwScreen2ResizeTimer = window.setTimeout(() => {
                        updateScreen2LayoutVars(screen2);
                    }, 120);
                });
            }

            __vwScreen2LayoutReady = true;
        }

        function applyScreenState(showResults) {
            if (!screensReady) return;

            if (showResults) {
                if (screen1El) {
                    screen1El.style.display = 'none';
                    screen1El.setAttribute('aria-hidden', 'true');
                    screen1El.classList.remove('active');
                }
                if (screen2El) {
                    screen2El.style.display = 'block';
                    screen2El.removeAttribute('aria-hidden');
                    screen2El.removeAttribute('hidden');
                    screen2El.classList.add('active');
                }
            } else {
                if (screen2El) {
                    screen2El.style.display = 'none';
                    screen2El.setAttribute('aria-hidden', 'true');
                    screen2El.classList.remove('active');
                }
                if (screen1El) {
                    screen1El.removeAttribute('aria-hidden');
                    screen1El.removeAttribute('hidden');
                    screen1El.style.display = '';
                    if (window.getComputedStyle(screen1El).display === 'none') {
                        screen1El.style.display = 'block';
                    }
                    screen1El.classList.add('active');
                }
            }
        }

        function smoothScrollTo(targetY) {
            if (typeof targetY !== 'number' || Number.isNaN(targetY)) return;
            const top = Math.max(0, targetY);
            window.scrollTo({ top, behavior: 'smooth' });
        }

        function getHeaderOffset() {
            let offset = 0;

            const adminBar = document.getElementById('wpadminbar');
            if (adminBar) {
                const adminStyles = window.getComputedStyle(adminBar);
                if (adminStyles.position === 'fixed') {
                    offset += Math.round(adminBar.getBoundingClientRect().height || 0);
                }
            }

            const header = document.querySelector('.fusion-header-wrapper, .fusion-header');
            if (header) {
                const styles = window.getComputedStyle(header);
                if (styles.position === 'fixed' || styles.position === 'sticky') {
                    offset += Math.round(header.getBoundingClientRect().height || 0);
                }
            }

            return offset;
        }

        function getScreen2ScrollAnchor(screen2) {
            if (!screen2) return null;

            const titlebar = screen2.querySelector(
                '.vw-titlebar, .header-bar, .breadcrumbs, .vw-breadcrumbs, nav[aria-label*="breadcrumb" i]'
            );
            if (titlebar) return titlebar;

            return screen2;
        }

        let isTransitioningToResults = false;

        function goToResultsStrict() {
            if (isTransitioningToResults) return;
            isTransitioningToResults = true;
            const screen2 = screen2El || document.querySelector('#screen2');
            const screen1 = screen1El || document.querySelector('#screen1');

            if (!screen2) {
                console.warn('[Buscador] No se encontró Screen 2 para la transición a resultados.');
                isTransitioningToResults = false;
                return;
            }

            if (screen1) {
                screen1.classList.add('vw-fade-out');
            }

            screen2.classList.add('vw-fade-in');
            screen2.style.display = '';

            const query = (confirmedSelection && confirmedSelection.value)
                ? confirmedSelection.value
                : (searchInput ? searchInput.value.trim() : (screen1SearchInputs[0] ? screen1SearchInputs[0].value.trim() : ''));
            lastConfirmedQuery = query || '';
            const totalResults = Array.isArray(displayedResults)
                ? displayedResults.length
                : (Array.isArray(allResults) ? allResults.length : 0);

            showResultsScreen(query || '', totalResults, { skipScreenToggle: true });

            setTimeout(() => {
                if (screen1) {
                    screen1.style.display = 'none';
                    screen1.classList.remove('vw-fade-out');
                }
                screen2.classList.remove('vw-fade-in');
                // Scroll al tope: titlebar + screen2 caben en el viewport
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        window.scrollTo(0, 0);
                        screen2.classList.add('vw-show');
                        // Re-medir posición real tras transición completa
                        updateScreen2LayoutVars(screen2);
                    });
                });
                isTransitioningToResults = false;
            }, 200);
        }

        // Forzar estado inicial: Screen 1 visible / Screen 2 oculto (siempre)
        applyScreenState(false);

        /**
         * Mostrar Screen 2 (Resultados) con scroll suave
         */
        function showResultsScreen(query, totalResults, options = {}) {
            ensureScreen2Layout();
            // Actualizar contenido de la barra
            if (resultsQueryEl) {
                const cleanQuery = (typeof query === 'string' ? query.trim() : '') || lastConfirmedQuery || '';
                resultsQueryEl.textContent = cleanQuery ? `"${cleanQuery}"` : '';
            }
            if (resultsTotalBar) {
                resultsTotalBar.textContent = `${totalResults} resultados`;
            }
            
            // Cambiar barras
            if (headerBarOriginal) headerBarOriginal.classList.add('hidden');
            if (headerBarResults) headerBarResults.classList.add('visible');
            document.body.classList.add('vw-screen2-active');

            // Asegurar scroll al tope para que la barra de título sea visible
            window.scrollTo(0, 0);
            
            // Mostrar Screen 2 y ocultar Screen 1 (modo estricto)
            if (!options.skipScreenToggle) {
                applyScreenState(true);
            }

            // Renderizar fichas de resultados
            renderResultCards(displayedResults);

            // Re-medir la posición real de #screen2 ahora que todo está visible y en top:0
            requestAnimationFrame(() => {
                const s2 = screen2El || document.querySelector('#screen2');
                if (s2) updateScreen2LayoutVars(s2);
            });
            
            log('📺 Mostrando Screen 2:', query, totalResults);
        }

        /**
         * Volver a Screen 1 (Buscador)
         */
        function showSearchScreen() {
            // Cambiar barras
            if (headerBarOriginal) headerBarOriginal.classList.remove('hidden');
            if (headerBarResults) headerBarResults.classList.remove('visible');
            document.body.classList.remove('vw-screen2-active');
            
            // Mostrar Screen 1 y ocultar Screen 2 (modo estricto)
            applyScreenState(false);

            // Scroll al tope y bloquear scroll (Screen 1 no tiene scroll)
            window.scrollTo(0, 0);
            setScrollLock(true);
            
            // Focus en el input de búsqueda
            if (searchInput) {
                searchInput.focus();
            }
            
            log('📺 Volviendo a Screen 1');
        }

        /**
         * Generar fichas de resultados en el grid
         * [TODO-PHP-6] En PHP estos datos vendrán de WordPress/ACF
         */
        function renderResultCards(results) {
            const grid = document.getElementById('resultsGrid');
            if (!grid) return;
            
            grid.innerHTML = '';
            
            results.forEach((result, index) => {
                const card = document.createElement('article');
                card.className = 'result-card';
                card.setAttribute('data-index', index);
                card.style.backgroundImage = `url('${result.imagen || "https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80"}')`;

                const resultType = toFavSlug(result.tipo || result.type || 'item');
                const favSlug = toFavSlug(result.titulo || 'item');
                const favId = `res-${resultType}-${favSlug}`;
                
                card.innerHTML = `
                    <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="${favId}">
                        <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                          <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                        </svg>
                    </button>
                    <div class="result-card-content">
                        <h3 class="result-card-title">${result.titulo || 'Título'}</h3>
                        <p class="result-card-line">${result.linea1 || 'Línea 1'}</p>
                        <p class="result-card-line">${result.linea2 || 'Línea 2'}</p>
                    </div>
                `;
                
                // Click en ficha
                card.addEventListener('click', () => {
                    log('🃏 Click en ficha:', result.titulo);
                    // [TODO-PHP] Navegar a la página del tour/destino
                });
                
                grid.appendChild(card);
            });
            
            syncFavoriteButtons();
            log('🃏 Renderizadas', results.length, 'fichas');
        }

        function resetMobileMapToggle() {
            mapExpanded = false;
            if (mapMobileWrapper) {
                mapMobileWrapper.classList.remove('is-open');
            }
            if (mapToggleButton) {
                mapToggleButton.classList.remove('active');
                mapToggleButton.textContent = 'Ver mapa';
            }
        }

        function setFechaRangeHint(message = '') {
            if (!fechaRangeHint) return;
            fechaRangeHint.textContent = message || '';
        }

        function updateDropdownMaxHeight() {
            const dropdown = document.getElementById('suggestionsDropdown');
            const wrapper = document.getElementById('searchInputWrapper') || (dropdown ? dropdown.parentElement : null);
            if (!dropdown || !wrapper) return;

            if (window.matchMedia('(max-width: 900px)').matches === false) {
                dropdown.style.removeProperty('--dropdown-max-h');
                return;
            }

            const rect = wrapper.getBoundingClientRect();
            const vh = (window.visualViewport && window.visualViewport.height) || window.innerHeight;
            const safeBottom = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('env(safe-area-inset-bottom)')) || 0;
            const paddingBottom = 16;
            const available = Math.max(180, Math.floor(vh - rect.bottom - safeBottom - paddingBottom));
            const capped = Math.min(available, Math.floor(vh * 0.6));

            dropdown.style.setProperty('--dropdown-max-h', capped + 'px');
        }

        /**
         * Actualiza la visibilidad del botón de mapa en móvil
         * Solo visible en Tab Fecha cuando existen resultados y viewport ≤ 768px
         */
        function updateMapToggleVisibility() {
            if (!mapToggleButton || !mapMobileWrapper || !mobileMapArea) return;
            
            const isMobile = window.innerWidth <= 768;
            const hasDateResults = currentTab === 'fecha' && Array.isArray(displayedResults) && displayedResults.length > 0;

            if (isMobile && hasDateResults) {
                mobileMapArea.classList.add('visible');
                mapToggleButton.classList.add('visible');
                if (!mapExpanded) {
                    mapMobileWrapper.classList.remove('is-open');
                    mapToggleButton.classList.remove('active');
                    mapToggleButton.textContent = 'Ver mapa';
                }
            } else {
                mobileMapArea.classList.remove('visible');
                mapToggleButton.classList.remove('visible');
                resetMobileMapToggle();
            }
        }

        /**
         * Toggle del mapa en móvil
         */
        function toggleMap() {
            if (!mapMobileWrapper || !mapToggleButton) return;
            
            const isOpen = mapMobileWrapper.classList.toggle('is-open');
            mapExpanded = isOpen;
            mapToggleButton.classList.toggle('active', isOpen);
            mapToggleButton.textContent = isOpen ? 'Ocultar mapa' : 'Ver mapa';
        }

        // Función para actualizar el chip de búsqueda confirmada
        function updateLockedChip() {
            if (queryLocked && confirmedSelection && searchLockedChip) {
                searchLockedText.textContent = confirmedSelection.value;
                searchLockedChip.classList.add('visible');
            } else {
                searchLockedChip.classList.remove('visible');
            }
            
            // Actualizar visibilidad del botón de mapa en móvil
            updateMapToggleVisibility();
        }

        // Función centralizada para resetear completamente la UI de búsqueda
        function resetSearchUI() {
            // Desbloquear modo confirmado
            queryLocked = false;
            confirmedSelection = null;
            hasExplicitSelection = false;
            explicitSelectedValue = '';
            lastConfirmedQuery = '';
            
            // Limpiar input
            searchInput.value = '';
            clearButton.classList.remove('visible');
            
            // Ocultar chip
            if (searchLockedText) {
                searchLockedText.textContent = '';
            }
            updateLockedChip();
            
            // Cerrar dropdown
            suggestionsDropdown.classList.remove('visible');
            suggestionsDropdown.innerHTML = '';
            suggestionsDropdown.setAttribute('aria-expanded', 'false');
            selectedIndex = -1;

            // Limpiar barra de resultados
            if (resultsQueryEl) {
                resultsQueryEl.textContent = '';
            }
            if (resultsTotalBar) {
                resultsTotalBar.textContent = '';
            }
            
            // Resetear grid de resultados al estado vacío
            updateResultsVisibility('');
            setFechaRangeHint('');
            showSearchScreen();
            
            log('⟳ UI de búsqueda reseteada completamente');
        }

        // ═══════════════════════════════════════════════════════
        // LAZY LOADING DE RESULTADOS
        // ═══════════════════════════════════════════════════════
        let allResults = []; // Todos los resultados filtrados
        let displayedResults = []; // Resultados mostrados actualmente
        const RESULTS_PER_PAGE = 16;
        let resultsPage = 0;

        // Datos de demo - 40 resultados para probar lazy loading
        const demoResults = [
            { title: 'Spa Le Bristol', location: 'París', date: '15 Jun 2027', day: '72', type: 'spa', description: 'Spa de lujo en el corazón de París. Tratamientos de relajación y masajes terapéuticos.', badges: ['Jun 2027', 'París', 'Relax'], titulo: 'Spa Le Bristol', linea1: 'París, Francia', linea2: '15 Jun 2027', imagen: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80' },
            { title: 'Museo del Louvre', location: 'París', date: '12 Jun 2027', day: '69', type: 'museo', description: 'Visita al museo más famoso del mundo. Contempla la Mona Lisa, Venus de Milo.', badges: ['Jun 2027', 'París', 'Cultura'], titulo: 'Museo del Louvre', linea1: 'París, Francia', linea2: '12 Jun 2027', imagen: 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=800&q=80' },
            { title: 'Torre Eiffel', location: 'París', date: '10 Jun 2027', day: '67', type: 'monumento', description: 'Icono de París y símbolo de Francia. Sube a la cima para vistas panorámicas.', badges: ['Jun 2027', 'París', 'Monumento'], titulo: 'Torre Eiffel', linea1: 'París, Francia', linea2: '10 Jun 2027', imagen: 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=800&q=80' },
            { title: 'Palacio de Versalles', location: 'Versalles', date: '18 Jun 2027', day: '75', type: 'monumento', description: 'Visita al palacio más lujoso de Francia. Jardines y Galería de los Espejos.', badges: ['Jun 2027', 'Versalles', 'Historia'], titulo: 'Palacio de Versalles', linea1: 'Versalles, Francia', linea2: '18 Jun 2027', imagen: 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=800&q=80' },
            { title: 'Catedral de Notre Dame', location: 'París', date: '13 Jun 2027', day: '70', type: 'museo', description: 'Visita a la emblemática catedral gótica. Arquitectura medieval impresionante.', badges: ['Jun 2027', 'París', 'Religión'], titulo: 'Catedral de Notre Dame', linea1: 'París, Francia', linea2: '13 Jun 2027', imagen: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=800&q=80' },
            { title: 'Arco del Triunfo', location: 'París', date: '11 Jun 2027', day: '68', type: 'monumento', description: 'Monumento histórico en los Campos Elíseos. Vista privilegiada de las avenidas.', badges: ['Jun 2027', 'París', 'Monumento'], titulo: 'Arco del Triunfo', linea1: 'París, Francia', linea2: '11 Jun 2027', imagen: 'https://images.unsplash.com/photo-1470770903676-69b98201ea1c?auto=format&fit=crop&w=800&q=80' },
            { title: 'Museo d\'Orsay', location: 'París', date: '14 Jun 2027', day: '71', type: 'museo', description: 'Arte impresionista y postimpresionista en una antigua estación de tren.', badges: ['Jun 2027', 'París', 'Arte'] },
            { title: 'Jardines de Luxemburgo', location: 'París', date: '16 Jun 2027', day: '73', type: 'parque', description: 'Hermosos jardines en el corazón de París, perfectos para pasear.', badges: ['Jun 2027', 'París', 'Naturaleza'] },
            { title: 'Montmartre', location: 'París', date: '17 Jun 2027', day: '74', type: 'barrio', description: 'Barrio bohemio con la Basílica del Sacré-Cœur y vistas panorámicas.', badges: ['Jun 2027', 'París', 'Cultura'] },
            { title: 'Sainte-Chapelle', location: 'París', date: '19 Jun 2027', day: '76', type: 'monumento', description: 'Capilla gótica con impresionantes vitrales de colores.', badges: ['Jun 2027', 'París', 'Religión'] },
            { title: 'Panteón de París', location: 'París', date: '20 Jun 2027', day: '77', type: 'monumento', description: 'Monumento neoclásico que alberga tumbas de personajes ilustres franceses.', badges: ['Jun 2027', 'París', 'Historia'] },
            { title: 'Ópera Garnier', location: 'París', date: '21 Jun 2027', day: '78', type: 'teatro', description: 'Teatro de ópera y ballet con arquitectura impresionante.', badges: ['Jun 2027', 'París', 'Cultura'] },
            { title: 'Les Invalides', location: 'París', date: '22 Jun 2027', day: '79', type: 'museo', description: 'Complejo con museos militares y la tumba de Napoleón Bonaparte.', badges: ['Jun 2027', 'París', 'Historia'] },
            { title: 'Barrio Latino', location: 'París', date: '23 Jun 2027', day: '80', type: 'barrio', description: 'Zona estudiantil con cafés históricos y la Sorbona.', badges: ['Jun 2027', 'París', 'Cultura'] },
            { title: 'Campos Elíseos', location: 'París', date: '24 Jun 2027', day: '81', type: 'avenida', description: 'La avenida más famosa de París con tiendas de lujo y cafés.', badges: ['Jun 2027', 'París', 'Shopping'] },
            { title: 'Puente Alexandre III', location: 'París', date: '25 Jun 2027', day: '82', type: 'monumento', description: 'El puente más elegante de París con decoraciones doradas.', badges: ['Jun 2027', 'París', 'Arquitectura'] },
            { title: 'Museo Rodin', location: 'París', date: '26 Jun 2027', day: '83', type: 'museo', description: 'Museo dedicado al escultor Auguste Rodin con jardines hermosos.', badges: ['Jun 2027', 'París', 'Arte'] },
            { title: 'Place de la Concorde', location: 'París', date: '27 Jun 2027', day: '84', type: 'plaza', description: 'Plaza histórica con el Obelisco de Luxor y fuentes majestuosas.', badges: ['Jun 2027', 'París', 'Historia'] },
            { title: 'Marais', location: 'París', date: '28 Jun 2027', day: '85', type: 'barrio', description: 'Barrio histórico con mansiones, galerías de arte y boutiques.', badges: ['Jun 2027', 'París', 'Cultura'] },
            { title: 'Museo Picasso', location: 'París', date: '29 Jun 2027', day: '86', type: 'museo', description: 'Colección más grande de obras de Pablo Picasso en el mundo.', badges: ['Jun 2027', 'París', 'Arte'] },
            { title: 'Catacumbas de París', location: 'París', date: '30 Jun 2027', day: '87', type: 'atraccion', description: 'Red subterránea de túneles con osarios históricos.', badges: ['Jun 2027', 'París', 'Historia'] },
            { title: 'Palacio de Tokyo', location: 'París', date: '01 Jul 2027', day: '88', type: 'museo', description: 'Museo de arte contemporáneo con exposiciones innovadoras.', badges: ['Jul 2027', 'París', 'Arte Moderno'] },
            { title: 'Parque Monceau', location: 'París', date: '02 Jul 2027', day: '89', type: 'parque', description: 'Jardín inglés con estatuas y arquitectura pintoresca.', badges: ['Jul 2027', 'París', 'Naturaleza'] },
            { title: 'Museo del Prado', location: 'Madrid', date: '05 May 2026', day: '5', type: 'museo', description: 'Uno de los museos más importantes del mundo con obras maestras españolas.', badges: ['May 2026', 'Madrid', 'Arte'] },
            { title: 'Palacio Real de Madrid', location: 'Madrid', date: '06 May 2026', day: '6', type: 'monumento', description: 'Residencia oficial de la familia real española con salas lujosas.', badges: ['May 2026', 'Madrid', 'Historia'] },
            { title: 'Parque del Retiro', location: 'Madrid', date: '07 May 2026', day: '7', type: 'parque', description: 'Parque histórico con el Palacio de Cristal y jardines hermosos.', badges: ['May 2026', 'Madrid', 'Naturaleza'] },
            { title: 'Plaza Mayor', location: 'Madrid', date: '08 May 2026', day: '8', type: 'plaza', description: 'Plaza histórica en el centro de Madrid, perfecta para tapas.', badges: ['May 2026', 'Madrid', 'Cultura'] },
            { title: 'Mercado de San Miguel', location: 'Madrid', date: '09 May 2026', day: '9', type: 'gastronomia', description: 'Mercado gourmet con tapas y productos españoles.', badges: ['May 2026', 'Madrid', 'Gastronomía'] },
            { title: 'Templo de Debod', location: 'Madrid', date: '15 Oct 2026', day: '168', type: 'monumento', description: 'Antiguo templo egipcio reconstruido en Madrid con vistas del atardecer.', badges: ['Oct 2026', 'Madrid', 'Historia'] },
            { title: 'Museo Reina Sofía', location: 'Madrid', date: '16 Oct 2026', day: '169', type: 'museo', description: 'Museo de arte moderno con el Guernica de Picasso.', badges: ['Oct 2026', 'Madrid', 'Arte'] },
            { title: 'Estadio Santiago Bernabéu', location: 'Madrid', date: '17 Oct 2026', day: '170', type: 'estadio', description: 'Estadio del Real Madrid, tour del estadio y museo.', badges: ['Oct 2026', 'Madrid', 'Deporte'] },
            { title: 'Île de la Cité', location: 'París', date: '03 Jul 2027', day: '90', type: 'isla', description: 'Isla en el Sena con Notre-Dame y la Sainte-Chapelle.', badges: ['Jul 2027', 'París', 'Historia'] },
            { title: 'Museo de la Orangerie', location: 'París', date: '04 Jul 2027', day: '91', type: 'museo', description: 'Museo con los famosos Nenúfares de Claude Monet.', badges: ['Jul 2027', 'París', 'Impresionismo'] },
            { title: 'Parc des Buttes-Chaumont', location: 'París', date: '05 Jul 2027', day: '92', type: 'parque', description: 'Parque con colinas, cascadas y vistas panorámicas de la ciudad.', badges: ['Jul 2027', 'París', 'Naturaleza'] },
            { title: 'Grand Palais', location: 'París', date: '06 Jul 2027', day: '93', type: 'museo', description: 'Edificio histórico con exposiciones de arte y eventos culturales.', badges: ['Jul 2027', 'París', 'Cultura'] },
            { title: 'Petit Palais', location: 'París', date: '07 Jul 2027', day: '94', type: 'museo', description: 'Museo de Bellas Artes con colección permanente gratuita.', badges: ['Jul 2027', 'París', 'Arte'] },
            { title: 'Basílica de Saint-Denis', location: 'Saint-Denis', date: '08 Jul 2027', day: '95', type: 'monumento', description: 'Necrópolis real con tumbas de reyes y reinas de Francia.', badges: ['Jul 2027', 'Saint-Denis', 'Historia'] },
            { title: 'Museo Carnavalet', location: 'París', date: '09 Jul 2027', day: '96', type: 'museo', description: 'Museo de la historia de París desde la prehistoria hasta hoy.', badges: ['Jul 2027', 'París', 'Historia'] },
            { title: 'Parc de la Villette', location: 'París', date: '10 Jul 2027', day: '97', type: 'parque', description: 'Parque urbano con jardines temáticos y espacios culturales.', badges: ['Jul 2027', 'París', 'Cultura'] },
            { title: 'Fondation Louis Vuitton', location: 'París', date: '11 Jul 2027', day: '98', type: 'museo', description: 'Centro de arte contemporáneo con arquitectura futurista.', badges: ['Jul 2027', 'París', 'Arte Moderno'] },
            { title: 'Museo Marmottan Monet', location: 'París', date: '12 Jul 2027', day: '99', type: 'museo', description: 'Museo con la mayor colección de obras de Claude Monet.', badges: ['Jul 2027', 'París', 'Impresionismo'] },
            { title: 'La Défense', location: 'La Défense', date: '13 Jul 2027', day: '100', type: 'barrio', description: 'Distrito de negocios con el Grande Arche y arquitectura moderna.', badges: ['Jul 2027', 'La Défense', 'Moderno'] },
            { title: 'Château de Fontainebleau', location: 'Fontainebleau', date: '14 Jul 2027', day: '101', type: 'monumento', description: 'Palacio renacentista con jardines extensos y historia imperial.', badges: ['Jul 2027', 'Fontainebleau', 'Historia'] },
            { title: 'Disneyland Paris', location: 'Marne-la-Vallée', date: '15 Jul 2027', day: '102', type: 'parque', description: 'Parque temático de Disney con atracciones para toda la familia.', badges: ['Jul 2027', 'Disneyland', 'Diversión'] },
            { title: 'Castillo de Vaux-le-Vicomte', location: 'Maincy', date: '16 Jul 2027', day: '103', type: 'monumento', description: 'Castillo barroco con jardines diseñados por Le Nôtre.', badges: ['Jul 2027', 'Maincy', 'Arquitectura'] },
            { title: 'Provins', location: 'Provins', date: '17 Jul 2027', day: '104', type: 'ciudad', description: 'Ciudad medieval fortificada, Patrimonio de la Humanidad UNESCO.', badges: ['Jul 2027', 'Provins', 'Medieval'] },
            { title: 'Chartres', location: 'Chartres', date: '18 Jul 2027', day: '105', type: 'ciudad', description: 'Ciudad con la famosa Catedral de Chartres de estilo gótico.', badges: ['Jul 2027', 'Chartres', 'Religión'] },
            { title: 'Giverny', location: 'Giverny', date: '19 Jul 2027', day: '106', type: 'pueblo', description: 'Pueblo donde vivió Claude Monet con sus jardines inmortalizados.', badges: ['Jul 2027', 'Giverny', 'Arte'] }
        ];

        // ═══════════════════════════════════════════════════════
        // CALCULAR VENTANAS DE ESTANCIA
        // ═══════════════════════════════════════════════════════
        /**
         * Calcula las ventanas de estancia (períodos continuos) de un grupo de días
         * @param {Array} items - Array de resultados ya ordenados por fecha ascendente
         * @returns {Array} Array de ventanas [{start: Date, end: Date, count: number}]
         */
        function calculateStayWindows(items) {
            if (!items || items.length === 0) return [];
            
            const windows = [];
            let currentWindow = {
                start: parseUniversalDate(items[0].date),
                end: parseUniversalDate(items[0].date),
                count: 1
            };
            
            for (let i = 1; i < items.length; i++) {
                const prevDate = parseUniversalDate(items[i - 1].date);
                const currDate = parseUniversalDate(items[i].date);
                
                // Calcular diferencia en días
                const diffTime = currDate - prevDate;
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays <= 1) {
                    // Día consecutivo o el mismo día: extender ventana actual
                    currentWindow.end = currDate;
                    currentWindow.count++;
                } else {
                    // Salto > 1 día: cerrar ventana actual y comenzar nueva
                    windows.push(currentWindow);
                    currentWindow = {
                        start: currDate,
                        end: currDate,
                        count: 1
                    };
                }
            }
            
            // Añadir la última ventana
            windows.push(currentWindow);
            
            return windows;
        }
        
        /**
         * Formatea las ventanas de estancia para mostrar en cabecera
         * @param {Array} windows - Array de ventanas
         * @returns {string} HTML con el resumen de ventanas
         */
        function formatStayWindows(windows) {
            if (!windows || windows.length === 0) return '';
            
            const formatDate = (date) => {
                const d = new Date(date);
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                return `${day}/${month}/${year}`;
            };
            
            const formatWindow = (window) => {
                const start = formatDate(window.start);
                const end = formatDate(window.end);
                return start === end ? start : `${start} → ${end}`;
            };
            
            // Mostrar máximo 2 ventanas
            const displayWindows = windows.slice(0, 2);
            const windowsText = displayWindows.map(w => formatWindow(w)).join(' · ');
            
            if (windows.length > 2) {
                return ` · ${windowsText} · <span style="color:#999;">+${windows.length - 2} más</span>`;
            } else if (windows.length > 0) {
                return ` · ${windowsText}`;
            }
            
            return '';
        }

        /**
         * Parsea una fecha en formato 'dd/mm/yyyy' o 'dd MMM yyyy'
         * @param {string} dateStr - Fecha en formato string
         * @returns {Date} Objeto Date
         */
        function parseUniversalDate(dateStr) {
            // Formato dd/mm/yyyy
            if (dateStr.includes('/')) {
                const [day, month, year] = dateStr.split('/');
                return new Date(`${year}-${month}-${day}`);
            }
            
            // Formato 'dd MMM yyyy' (ej: '05 May 2026')
            const monthMap = {
                'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
                'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
            };
            const parts = dateStr.trim().split(' ');
            if (parts.length === 3) {
                const day = parts[0].padStart(2, '0');
                const month = monthMap[parts[1]] || '01';
                const year = parts[2];
                return new Date(`${year}-${month}-${day}`);
            }
            
            // Fallback: intentar parseo directo
            return new Date(dateStr);
        }

        /**
         * Formatea una fecha a formato largo español
         * @param {Date} date - Objeto Date
         * @returns {string} Fecha en formato "20 de junio de 2026"
         */
        function formatLongDate(date) {
            const months = [
                'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
            ];
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            return `${day} de ${month} de ${year}`;
        }

        function getDemoCoords(place) {
            if (!place) return null;
            const key = place.trim().toLowerCase();
            return demoPlaceCoords[key] || null;
        }

        function buildDateSelectionPayload(entry) {
            if (!entry) return null;
            const coords = getDemoCoords(entry.lugar);
            return {
                type: 'dia',
                fecha: entry.fecha,
                lugar: entry.lugar,
                hito: entry.hito,
                ...(coords ? { lat: coords.lat, lng: coords.lng } : {})
            };
        }

        function extractUniquePlaces(entries) {
            if (!entries || !entries.length) return [];
            const unique = [];
            const seen = new Set();

            entries.forEach(entry => {
                const key = (entry.lugar || '').trim().toLowerCase();
                if (!key || seen.has(key)) return;
                seen.add(key);
                const coords = getDemoCoords(entry.lugar);
                unique.push({
                    lugar: entry.lugar,
                    ...(coords ? { lat: coords.lat, lng: coords.lng } : {})
                });
            });

            return unique;
        }

        function parseISODateLocal(isoDate) {
            if (!isoDate) return null;
            const [year, month, day] = isoDate.split('-').map(Number);
            return new Date(year, (month || 1) - 1, day || 1);
        }

        function parseSpanishDateLocal(spanishDate) {
            if (!spanishDate) return null;
            const [day, month, year] = spanishDate.split('/').map(Number);
            return new Date(year || 0, (month || 1) - 1, day || 1);
        }

        function formatWeekdayLong(date) {
            const weekdays = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            const label = weekdays[date.getDay()] || '';
            const prettyWeekday = label ? label.charAt(0).toUpperCase() + label.slice(1) : '';
            return `${prettyWeekday}, ${formatLongDate(date)}`;
        }

        function normalizeDateRange(fromISO, toISO) {
            const start = parseISODateLocal(fromISO);
            const end = toISO ? parseISODateLocal(toISO) : parseISODateLocal(fromISO);
            if (!start || !end) {
                return { start: null, end: null };
            }
            return end < start ? { start: end, end: start } : { start, end };
        }

        function getDateTabResults(fromISO, toISO) {
            if (!fromISO || !(data.dias || []).length) {
                return [];
            }

            const { start, end } = normalizeDateRange(fromISO, toISO);
            if (!start || !end) {
                return [];
            }

            return (data.dias || [])
                .filter(entry => {
                    const entryDate = parseSpanishDateLocal(entry.fecha);
                    return entryDate && entryDate >= start && entryDate <= end;
                })
                .sort((a, b) => {
                    const dateA = parseSpanishDateLocal(a.fecha);
                    const dateB = parseSpanishDateLocal(b.fecha);
                    return dateA - dateB;
                });
        }

        function renderResults(results, append = false) {
            if (!append) {
                resultsGrid.innerHTML = '';
                displayedResults = [];
                resultsPage = 0;
            }

            // Obtener el término para resaltar según el modo
            const highlightTerm = queryLocked && confirmedSelection ? confirmedSelection.value : searchInput.value.trim();

            // Tab Lugar: Ordenar por fecha ascendente
            if (currentTab === 'lugar') {
                // Parsear fecha (soporta dd/mm/aaaa y dd MMM yyyy)
                results.sort((a, b) => {
                    return parseUniversalDate(a.date) - parseUniversalDate(b.date);
                });
            }

            // Tab Lugar + queryLocked + 2+ periodos: Renderizar con cajas de periodo
            if (currentTab === 'actividad' && queryLocked && results.length > 0) {
                // Tab Actividad: renderizado específico
                renderActividadResults(results);
                
                // PASO 4/6: Actualizar mapa con lugares únicos
                const uniquePlaces = [];
                const seenPlaces = new Set();
                
                results.forEach(entry => {
                    const lugar = (entry.location || '').trim();
                    if (lugar && !seenPlaces.has(lugar.toLowerCase())) {
                        seenPlaces.add(lugar.toLowerCase());
                        uniquePlaces.push({ lugar: lugar });
                    }
                });
                
                // Limitar a 25 lugares
                const placesToMap = uniquePlaces.slice(0, 25);
                if (uniquePlaces.length > 25) {
                    log(`⚠️ Mostrando 25 de ${uniquePlaces.length} lugares`);
                }
                
                log(`🗺️ MAPA ACTIVIDAD: ${placesToMap.length} lugar${placesToMap.length !== 1 ? 'es' : ''}`);
                updateMapForResults(placesToMap);
                
                return;
            }
            
            if (currentTab === 'lugar' && queryLocked && results.length > 0) {
                const windows = calculateStayWindows(results);
                
                if (windows.length >= 2) {
                    const totalPeriods = windows.length;  // N = total de periodos
                    
                    // Renderizar cada periodo como caja envolvente
                    windows.forEach((window, periodIndex) => {
                        // Formatear fechas en formato largo español
                        const startDateLong = formatLongDate(window.start);
                        const endDateLong = formatLongDate(window.end);
                        const dateRangeLong = window.start.getTime() === window.end.getTime() 
                            ? `El ${startDateLong}`
                            : `Desde el ${startDateLong} al ${endDateLong}`;
                        
                        // Crear caja envolvente del periodo
                        const periodBlock = document.createElement('section');
                        periodBlock.className = 'period-block';
                        
                        // Cabecera del periodo (dos líneas premium)
                        const periodHeader = document.createElement('div');
                        periodHeader.className = 'period-header';
                        periodHeader.innerHTML = `
                            <div class="period-title">PERIODO ${periodIndex + 1} de ${totalPeriods}</div>
                            <div class="period-date-long">${dateRangeLong}</div>
                        `;
                        periodBlock.appendChild(periodHeader);
                        
                        // Grid de cards del periodo
                        const periodGrid = document.createElement('div');
                        periodGrid.className = 'period-grid';
                        
                        // Filtrar días de este periodo
                        const periodDays = results.filter(r => {
                            const rDate = parseUniversalDate(r.date);
                            return rDate >= window.start && rDate <= window.end;
                        });
                        
                        // Renderizar cards de este periodo
                        periodDays.forEach(result => {
                            const card = document.createElement('div');
                            card.className = `result-card type-${result.type}`;
                            
                            const highlightedTitle = highlightMatchGrid(result.title, highlightTerm);
                            const highlightedLocation = highlightMatchGrid(result.location, highlightTerm);
                            const highlightedDate = highlightMatchGrid(result.date, highlightTerm);
                            const highlightedDay = highlightMatchGrid(result.day, highlightTerm);
                            const highlightedDescription = highlightMatchGrid(result.description, highlightTerm);

                            const resultType = toFavSlug(result.type || 'item');
                            const favSlug = toFavSlug(result.title || 'item');
                            const favId = `res-${resultType}-${favSlug}`;
                            
                            card.innerHTML = `
                                <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="${favId}">
                                    <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                      <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                    </svg>
                                </button>
                                <div class="result-title">${highlightedTitle}</div>
                                <div class="result-meta">
                                    <span>${highlightedLocation}</span>
                                    <span>${highlightedDate}</span>
                                    <span>Día ${highlightedDay}º</span>
                                </div>
                                <div class="result-description">${highlightedDescription}</div>
                            `;
                            periodGrid.appendChild(card);
                            displayedResults.push(result);
                        });
                        
                        periodBlock.appendChild(periodGrid);
                        resultsGrid.appendChild(periodBlock);
                    });
                    
                    // Salir para no ejecutar renderizado normal
                    updateCounter();
                    return;
                }
            }

            // Renderizado plano unificado (otros casos)
            const start = resultsPage * RESULTS_PER_PAGE;
            const end = Math.min(start + RESULTS_PER_PAGE, results.length);
            const toRender = results.slice(start, end);

            toRender.forEach(result => {
                    const card = document.createElement('div');
                    card.className = `result-card type-${result.type}`;
                    
                    const highlightedTitle = highlightMatchGrid(result.title, highlightTerm);
                    const highlightedLocation = highlightMatchGrid(result.location, highlightTerm);
                    const highlightedDate = highlightMatchGrid(result.date, highlightTerm);
                    const highlightedDay = highlightMatchGrid(result.day, highlightTerm);
                    const highlightedDescription = highlightMatchGrid(result.description, highlightTerm);

                    const resultType = toFavSlug(result.type || 'item');
                    const favSlug = toFavSlug(result.title || 'item');
                    const favId = `res-${resultType}-${favSlug}`;
                    
                    card.innerHTML = `
                        <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="${favId}">
                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                              <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                            </svg>
                        </button>
                        <div class="result-title">${highlightedTitle}</div>
                        <div class="result-meta">
                            <span>${highlightedLocation}</span>
                            <span>${highlightedDate}</span>
                            <span>Día ${highlightedDay}º</span>
                        </div>
                        <div class="result-description">${highlightedDescription}</div>
                    `;
                    resultsGrid.appendChild(card);
                    displayedResults.push(result);
                });

            resultsPage++;
            
            updateCounter();
            updateMapToggleVisibility();
            syncFavoriteButtons();
        }

        const refineHint = document.getElementById('refineHint');
        const lockedIndicator = document.getElementById('lockedIndicator');

        function updateCounter() {
            if (countShowing && countTotal) {
                countShowing.textContent = displayedResults.length;
                countTotal.textContent = allResults.length;

                // Mostrar mensaje de refinamiento si hay más de 40 resultados totales
                if (refineHint) {
                    if (allResults.length > 40) {
                        refineHint.classList.add('visible');
                    } else {
                        refineHint.classList.remove('visible');
                    }
                }

                // Mostrar/ocultar indicador de búsqueda fijada
                if (lockedIndicator) {
                    if (queryLocked) {
                        lockedIndicator.classList.add('visible');
                    } else {
                        lockedIndicator.classList.remove('visible');
                    }
                }
            }
        }

        // Filtrar resultados según el modo de búsqueda
        function filterResultsByMode(query) {
            if (!query || query.length < 2) {
                return [];
            }

            let results = [];

            // Modo confirmado: filtro exacto
            if (queryLocked && confirmedSelection) {
                const { value, tab } = confirmedSelection;
                
                // Filtrar según el tipo de selección
                if (tab === 'lugar') {
                    // Solo resultados de ese lugar exacto
                    results = demoResults.filter(r => r.location.toLowerCase() === value.toLowerCase());
                    
                    // Ordenar por fecha ascendente en tab Lugar
                    results.sort((a, b) => {
                        const dateA = new Date(a.date);
                        const dateB = new Date(b.date);
                        return dateA - dateB;
                    });
                } else if (tab === 'actividad') {
                    // Solo esa actividad exacta
                    results = demoResults.filter(r => r.title.toLowerCase() === value.toLowerCase());
                    
                    // Ordenar por fecha ascendente en tab Actividad
                    results.sort((a, b) => {
                        const dateA = new Date(a.date);
                        const dateB = new Date(b.date);
                        return dateA - dateB;
                    });
                } else if (tab === 'fecha') {
                    // Solo ese día exacto
                    results = demoResults.filter(r => r.date === value);
                } else if (tab === 'todo') {
                    // Buscar coincidencia exacta en location o title
                    results = demoResults.filter(r => 
                        r.location.toLowerCase() === value.toLowerCase() ||
                        r.title.toLowerCase() === value.toLowerCase()
                    );
                }

                // Ordenar por día ascendente en modo confirmado (excepto tab Lugar y Actividad, que ya ordenaron por fecha)
                if (tab !== 'lugar' && tab !== 'actividad') {
                    results.sort((a, b) => parseInt(a.day) - parseInt(b.day));
                }
                
                return results;
            }

            // Modo incremental: filtro por prefijo según tab activa
            const q = query.toLowerCase();
            
            if (currentTab === 'lugar') {
                // Tab Lugar: buscar SOLO en sitio_dia (location)
                results = demoResults.filter(r => 
                    r.location.toLowerCase().startsWith(q)
                );
            } else {
                // Otros tabs: búsqueda global en title, location, type
                results = demoResults.filter(r => 
                    r.title.toLowerCase().startsWith(q) ||
                    r.location.toLowerCase().startsWith(q) ||
                    r.type.toLowerCase().startsWith(q)
                );
            }

            // Ordenar en modo incremental
            if (currentTab === 'lugar') {
                // Tab Lugar: ordenar por fecha ascendente
                results.sort((a, b) => {
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    return dateA - dateB;
                });
            } else {
                // Otros tabs: por relevancia (prefijo) y desempate por día
                results.sort((a, b) => {
                    const aTitle = a.title.toLowerCase();
                    const bTitle = b.title.toLowerCase();
                    const aLocation = a.location.toLowerCase();
                    const bLocation = b.location.toLowerCase();
                    
                    // Prioridad 1: Coincidencia en título sobre ubicación
                    const aTitleMatch = aTitle.startsWith(q);
                    const bTitleMatch = bTitle.startsWith(q);
                    
                    if (aTitleMatch && !bTitleMatch) return -1;
                    if (!aTitleMatch && bTitleMatch) return 1;
                    
                    // Prioridad 2: Coincidencia en ubicación
                    const aLocationMatch = aLocation.startsWith(q);
                    const bLocationMatch = bLocation.startsWith(q);
                    
                    if (aLocationMatch && !bLocationMatch) return -1;
                    if (!aLocationMatch && bLocationMatch) return 1;
                    
                    // Desempate: por día ascendente
                    return parseInt(a.day) - parseInt(b.day);
                });
            }

            return results;
        }

        // Scroll event para lazy loading
        if (resultsGrid) {
            resultsGrid.addEventListener('scroll', () => {
                const { scrollTop, scrollHeight, clientHeight } = resultsGrid;
                // Si está cerca del final (50px antes)
                if (scrollTop + clientHeight >= scrollHeight - 50) {
                    if (displayedResults.length < allResults.length) {
                        renderResults(allResults, true);
                    }
                }
            });
        }

        // ═══════════════════════════════════════════════════════
        // FUNCIONES STUB PARA MAPA
        // ═══════════════════════════════════════════════════════
        /**
         * Actualizar el mapa cuando el usuario selecciona una sugerencia
         * @param {Object} selection - Objeto con la selección del usuario {value, tab}
         */
        function updateMapForSelection(selection) {
            log('🗺️ updateMapForSelection:', selection);
            // TODO: Aquí se integrará la lógica del mapa (Google Maps)
            // Ejemplo: centrar mapa en la ubicación seleccionada, añadir marcador, etc.
        }

        /**
         * Actualizar el mapa con los marcadores de los resultados
         * @param {Array} results - Array de resultados a mostrar en el mapa
         */
        function updateMapForResults(results) {
            log('🗺️ updateMapForResults:', results.length, 'resultados');
            // TODO: Aquí se integrará la lógica del mapa (Google Maps)
            // Ejemplo: añadir marcadores para cada resultado, ajustar bounds, etc.
        }

        // ═══════════════════════════════════════════════════════
        // CAMBIO DE TAB (función común para desktop y móvil)
        // ═══════════════════════════════════════════════════════
        function switchTab(newTab, tabLabel) {
            const previousTab = currentTab;
            // Actualizar tab actual
            currentTab = newTab;
            
            // Actualizar tabs desktop
            tabButtons.forEach(b => b.classList.remove('active'));
            const activeButton = document.querySelector(`.tab-button[data-tab="${newTab}"]`);
            if (activeButton) activeButton.classList.add('active');
            
            // Actualizar selector móvil
            mobileTabOptions.forEach(opt => opt.classList.remove('active'));
            const activeOption = document.querySelector(`.mobile-tab-option[data-tab="${newTab}"]`);
            if (activeOption) activeOption.classList.add('active');
            if (mobileTabLabel && tabLabel) mobileTabLabel.textContent = tabLabel;
            
            // Referencias a elementos
            const dateControls = document.getElementById('dateSearchControls');
            const dateHelpText = document.getElementById('dateHelpText');
            const secondarySearch = document.querySelector('.secondary-search');

            // Actualizar placeholder según tab activa
            if (newTab === 'lugar') {
                searchInput.setAttribute('placeholder', 'Escribe un lugar (ciudad/pueblo). País, opcional.');
            } else if (newTab === 'fecha') {
                searchInput.setAttribute('placeholder', 'Selecciona fechas abajo');
            } else if (newTab === 'actividad') {
                searchInput.setAttribute('placeholder', '¿Cuál es tu actividad soñada? ¿Remontar el Amazonas? ¿Montar en globo? ¿Acompañar a National Geographic en la Antártida?');
            } else {
                searchInput.setAttribute('placeholder', 'Museos en París, Cruceros por Polinesia, Aventuras Polares, Safaris en Botsuana...');
            }

            // PASO 6/6: Actualizar texto de estado inicial según tab
            if (emptyStateInitial) {
                if (newTab === 'actividad') {
                    emptyStateInitial.textContent = 'Escribe una actividad (mínimo 3 letras)';
                } else {
                    emptyStateInitial.textContent = 'Escribe para buscar (mínimo 2 letras)';
                }
            }

            // Gestión específica Tab Fecha
            if (newTab === 'fecha') {
                // Mostrar controles de fecha
                if (dateControls) dateControls.classList.add('visible');
                
                // Mostrar texto de ayuda
                if (dateHelpText) dateHelpText.classList.add('visible');
                
                // Ocultar input principal
                if (secondarySearch) secondarySearch.classList.add('date-active');
            } else {
                // Ocultar controles de fecha
                if (dateControls) dateControls.classList.remove('visible');
                
                // Ocultar texto de ayuda
                if (dateHelpText) dateHelpText.classList.remove('visible');
                
                // Mostrar input principal
                if (secondarySearch) secondarySearch.classList.remove('date-active');
            }

            if (emptyStateInitial) {
                if (newTab === 'fecha') {
                    emptyStateInitial.classList.add('hidden');
                } else {
                    emptyStateInitial.classList.remove('hidden');
                }
            }

            resetDateStateMessage();
            
            // PASO 6/6: Reset específico para tab Actividad al salir
            if (previousTab === 'actividad') {
                queryLocked = false;
                confirmedSelection = null;
                updateLockedChip();
                if (resultsGrid) {
                    resultsGrid.innerHTML = '';
                    resultsGrid.classList.remove('visible');
                }
                if (resultsHeader) resultsHeader.classList.remove('visible');
                if (emptyStateNoResults) emptyStateNoResults.classList.add('hidden');
                resetMobileMapToggle();
                if (mapToggleButton) mapToggleButton.classList.remove('visible');
                if (mobileMapArea) mobileMapArea.classList.remove('visible');
                log('🧹 Reset completo de tab Actividad');
            }

            // Resetear búsqueda
            searchInput.value = '';
            clearButton.classList.remove('visible');
            suggestionsDropdown.classList.remove('visible');
            selectedIndex = -1;
            hasExplicitSelection = false;
            explicitSelectedValue = '';
            showSearchScreen();
            searchInput.focus();
            
            if (newTab === 'fecha') {
                clearDateResults();
                handleDateInputsChange();
            } else if (previousTab === 'fecha') {
                clearDateResults();
                updateMapToggleVisibility();
            }
            
        }

        // Event listeners para tabs desktop
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabLabel = button.textContent.split('\n')[0].trim();
                switchTab(button.dataset.tab, tabLabel);
            });
        });

        // ═══════════════════════════════════════════════════════
        // BOTONES DE MAPA (delegación para móvil y tab Fecha)
        // ═══════════════════════════════════════════════════════
        document.addEventListener('click', (event) => {
            const toggleTarget = event.target.closest('#mapToggleButton');
            if (!toggleTarget) return;
            event.preventDefault();
            toggleMap();
        });

        // Inicializar visibilidad del botón
        updateMapToggleVisibility();

        // ═══════════════════════════════════════════════════════
        // SELECTOR MÓVIL DE TABS
        // ═══════════════════════════════════════════════════════
        if (mobileTabButton && mobileTabDropdown) {
            // Abrir/cerrar dropdown
            mobileTabButton.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = mobileTabDropdown.classList.contains('open');
                if (isOpen) {
                    mobileTabDropdown.classList.remove('open');
                    mobileTabButton.classList.remove('open');
                } else {
                    mobileTabDropdown.classList.add('open');
                    mobileTabButton.classList.add('open');
                }
            });

            // Seleccionar opción
            mobileTabOptions.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const newTab = option.dataset.tab;
                    const tabLabel = option.querySelector('span:first-child').textContent;
                    switchTab(newTab, tabLabel);
                    
                    // Cerrar dropdown
                    mobileTabDropdown.classList.remove('open');
                    mobileTabButton.classList.remove('open');
                });
            });

            // Cerrar dropdown al hacer click fuera
            document.addEventListener('click', (e) => {
                if (!mobileTabButton.contains(e.target) && !mobileTabDropdown.contains(e.target)) {
                    mobileTabDropdown.classList.remove('open');
                    mobileTabButton.classList.remove('open');
                }
            });
        }


        // Event listener para botón "Volver a buscar"
        if (btnBackSearch) {
            btnBackSearch.addEventListener('click', () => {
                resetSearchUI();
            });
        }

        // ═══════════════════════════════════════════════════════
        // CHIP DE BÚSQUEDA CONFIRMADA - BOTÓN CERRAR
        // ═══════════════════════════════════════════════════════
        if (searchLockedClose) {
            searchLockedClose.addEventListener('click', () => {
                resetSearchUI();
                searchInput.focus();
            });
        }

        // ═══════════════════════════════════════════════════════
        // CONTROLAR VISIBILIDAD DEL GRID DE RESULTADOS
        // ═══════════════════════════════════════════════════════
        function updateResultsVisibility(query) {
            if (!hasExplicitSelection) {
                showSearchScreen();
                return;
            }
            if (currentTab === 'fecha') {
                return;
            }

            // PASO 6/6: Tab Actividad requiere mínimo 3 letras
            const minLength = currentTab === 'actividad' ? 3 : 2;

            if (query && query.length >= minLength) {
                // Filtrar y renderizar resultados según el modo
                allResults = filterResultsByMode(query);
                renderResults(allResults);
                
                // Si hay resultados, mostrar grid y contador
                if (allResults.length > 0) {
                    if (resultsHeader) resultsHeader.classList.add('visible');
                    if (resultsGrid) resultsGrid.classList.add('visible');
                    if (emptyStateInitial) emptyStateInitial.classList.add('hidden');
                    if (emptyStateNoResults) emptyStateNoResults.classList.add('hidden');
                } else {
                    // Si NO hay resultados, mostrar mensaje de sin resultados
                    if (resultsHeader) resultsHeader.classList.remove('visible');
                    if (resultsGrid) resultsGrid.classList.remove('visible');
                    if (emptyStateInitial) emptyStateInitial.classList.add('hidden');
                    if (emptyStateNoResults) {
                        // Mensaje específico si es tab Actividad con búsqueda confirmada
                        if (currentTab === 'actividad' && queryLocked) {
                            emptyStateNoResults.textContent = 'Sin resultados para esa actividad.';
                        }
                        emptyStateNoResults.classList.remove('hidden');
                    }
                }
            } else {
                // Query vacía o < minLength: mostrar estado inicial
                if (resultsHeader) resultsHeader.classList.remove('visible');
                if (resultsGrid) {
                    resultsGrid.innerHTML = '';
                    resultsGrid.classList.remove('visible');
                }
                if (emptyStateInitial) emptyStateInitial.classList.remove('hidden');
                if (emptyStateNoResults) emptyStateNoResults.classList.add('hidden');

                // PASO 6/6: Reset adicional para tab Actividad
                if (currentTab === 'actividad') {
                    queryLocked = false;
                    confirmedSelection = null;
                    updateLockedChip();
                    resetMobileMapToggle();
                    if (mapToggleButton) mapToggleButton.classList.remove('visible');
                    if (mobileMapArea) mobileMapArea.classList.remove('visible');
                    log('🧹 Reset tab Actividad (input <3 letras)');
                }
            }
        }

        // ═══════════════════════════════════════════════════════
        // INPUT:  MOSTRAR/OCULTAR BOTÓN X
        // ═══════════════════════════════════════════════════════
        searchInput.addEventListener('input', (e) => {
            const value = e.target.value.trim();

            hasExplicitSelection = false;
            explicitSelectedValue = '';
            showSearchScreen();
            
            // Si el usuario modifica el input manualmente, volver a modo incremental
            if (queryLocked) {
                queryLocked = false;
                confirmedSelection = null;
                updateLockedChip();
                log('🔓 Modo incremental activado (usuario modificó el input)');
            }
            
            if (value.length > 0) {
                clearButton.classList.add('visible');
            } else {
                clearButton.classList.remove('visible');
            }

            selectedIndex = -1;
            filterSuggestions(value);
            
            // Controlar visibilidad del grid de resultados
            updateResultsVisibility(value);
        });

        // ═══════════════════════════════════════════════════════
        // FOCUS:  SI YA HAY TEXTO, MOSTRAR SUGERENCIAS
        // ═══════════════════════════════════════════════════════
        searchInput.addEventListener('focus', () => {
            const value = searchInput.value.trim();
            if (value.length > 0) {
                selectedIndex = -1;
                filterSuggestions(value);
                updateDropdownMaxHeight();
            }
        });

        // ═══════════════════════════════════════════════════════
        // BOTÓN X: LIMPIAR
        // ═══════════════════════════════════════════════════════
        clearButton.addEventListener('click', () => {
            resetSearchUI();
            searchInput.focus();
        });

        // ═══════════════════════════════════════════════════════
        // TAB FECHA: GESTIÓN DE INPUTS Y CONVERSIÓN DE FORMATO
        // ═══════════════════════════════════════════════════════
        const dateFromInput = document.getElementById('dateFrom');
        const dateToInput = document.getElementById('dateTo');

        // Asegurar que ambos inputs empiezan vacíos
        if (dateFromInput) dateFromInput.value = '';
        if (dateToInput) dateToInput.value = '';

        /**
         * Convierte fecha yyyy-mm-dd (input date) a dd/mm/aaaa
         * @param {string} isoDate - Fecha en formato ISO (yyyy-mm-dd)
         * @returns {string} Fecha en formato dd/mm/aaaa
         */
        function convertISOToSpanishDate(isoDate) {
            if (!isoDate) return '';
            const [year, month, day] = isoDate.split('-');
            return `${day}/${month}/${year}`;
        }

        /**
         * Convierte fecha dd/mm/aaaa a yyyy-mm-dd (input date)
         * @param {string} spanishDate - Fecha en formato dd/mm/aaaa
         * @returns {string} Fecha en formato ISO (yyyy-mm-dd)
         */
        function convertSpanishDateToISO(spanishDate) {
            if (!spanishDate) return '';
            const [day, month, year] = spanishDate.split('/');
            return `${year}-${month}-${day}`;
        }

        function clearDateResults() {
            if (resultsGrid) {
                resultsGrid.innerHTML = '';
                resultsGrid.classList.remove('visible');
            }
            if (resultsHeader) {
                resultsHeader.classList.remove('visible');
            }
            allResults = [];
            displayedResults = [];
            updateCounter();
            setFechaRangeHint('');
            resetMobileMapToggle();
            updateMapToggleVisibility();
        }

        function showDateStateMessage(message, options = {}) {
            if (!emptyStateNoResults || currentTab !== 'fecha') return;
            const { persistent = false, duration = null } = options;
            emptyStateNoResults.textContent = message;
            emptyStateNoResults.classList.remove('hidden');
            dateMessagePersistent = persistent;
            if (dateMessageTimeout) {
                clearTimeout(dateMessageTimeout);
                dateMessageTimeout = null;
            }
            if (duration) {
                dateMessageTimeout = setTimeout(() => {
                    if (currentTab === 'fecha' && emptyStateNoResults.textContent === message) {
                        resetDateStateMessage();
                    }
                }, duration);
            }
        }

        function resetDateStateMessage() {
            if (!emptyStateNoResults) return;
            if (currentTab === 'fecha') {
                emptyStateNoResults.textContent = DATE_NO_RESULTS_TEXT;
            } else {
                emptyStateNoResults.textContent = defaultNoResultsCopy;
            }
            emptyStateNoResults.classList.add('hidden');
            dateMessagePersistent = false;
            if (dateMessageTimeout) {
                clearTimeout(dateMessageTimeout);
                dateMessageTimeout = null;
            }
        }

        function renderActividadResults(matches) {
            if (!resultsGrid) return;

            resultsGrid.innerHTML = '';
            matches.forEach(entry => {
                // Parsear fecha para formato bonito
                const entryDate = entry.date ? new Date(entry.date) : null;
                const formattedDate = entryDate ? formatWeekdayLong(entryDate) : entry.date;
                
                const card = document.createElement('div');
                card.className = 'result-card';
                const favSlug = toFavSlug(entry.title || 'item');
                const favId = `res-actividad-${favSlug}`;
                card.innerHTML = `
                    <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="${favId}">
                        <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                          <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                        </svg>
                    </button>
                    <div class="result-title">${escapeHtml(entry.title)}</div>
                    <div class="result-location">${escapeHtml(entry.location)} · ${escapeHtml(formattedDate)}</div>
                `;
                resultsGrid.appendChild(card);
            });

            allResults = matches;
            displayedResults = [...matches];

            resultsGrid.classList.add('visible');
            if (resultsHeader) {
                resultsHeader.classList.add('visible');
            }
            if (emptyStateNoResults) {
                emptyStateNoResults.classList.add('hidden');
            }

            updateCounter();
        }

        function renderDateTabResults(matches) {
            if (!resultsGrid) return;

            resultsGrid.innerHTML = '';
            matches.forEach(entry => {
                const entryDate = parseSpanishDateLocal(entry.fecha);
                const formattedDate = entryDate ? formatWeekdayLong(entryDate) : entry.fecha;
                const card = document.createElement('div');
                card.className = 'result-card date-result-card';
                const favSlug = toFavSlug(entry.lugar || 'item');
                const favId = `res-fecha-${favSlug}`;
                card.innerHTML = `
                    <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="${favId}">
                        <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                          <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                        </svg>
                    </button>
                    <div class="result-title">${escapeHtml(entry.lugar)}</div>
                    <div class="date-result-line1">${escapeHtml(formattedDate)}</div>
                    <div class="date-result-line2">${escapeHtml(entry.hito)}</div>
                `;
                resultsGrid.appendChild(card);
            });

            allResults = matches;
            displayedResults = [...matches];

            resultsGrid.classList.add('visible');
            if (resultsHeader) {
                resultsHeader.classList.add('visible');
            }
            if (emptyStateNoResults && !dateMessagePersistent) {
                emptyStateNoResults.classList.add('hidden');
            }

            resetMobileMapToggle();
            updateCounter();
            syncFavoriteButtons();
            syncFavoriteButtons();
        }

        function handleDateInputsChange() {
            if (currentTab !== 'fecha') return;
            const fromISO = dateFromInput ? dateFromInput.value : '';
            let toISO = dateToInput ? dateToInput.value : '';

            if (!fromISO) {
                clearDateResults();
                showDateStateMessage(DATE_SELECT_PROMPT_TEXT);
                updateMapForResults([]);
                updateMapToggleVisibility();
                setFechaRangeHint('');
                return;
            }

            if (toISO && toISO < fromISO) {
                if (dateToInput) {
                    dateToInput.value = '';
                }
                toISO = '';
                if (!suppressRangeHint) {
                    setFechaRangeHint(DATE_RANGE_HINT_TEXT);
                }
            }

            const matches = getDateTabResults(fromISO, toISO);
            if (!matches.length) {
                clearDateResults();
                showDateStateMessage(DATE_NO_RESULTS_TEXT);
                updateMapForResults([]);
                updateMapToggleVisibility();
                return;
            }

            renderDateTabResults(matches);
            updateMapToggleVisibility();

            const isSingleDay = !toISO;
            if (isSingleDay) {
                const selectedEntry = matches[0];
                const payload = buildDateSelectionPayload(selectedEntry);
                if (payload) {
                    console.log('MAPA FECHA (día único):', payload);
                    updateMapForSelection(payload);
                }
            } else {
                const uniquePlaces = extractUniquePlaces(matches);
                console.log(`MAPA FECHA (rango): ${uniquePlaces.length} lugares únicos`, uniquePlaces);
                updateMapForResults(uniquePlaces);
            }
        }

        // Configurar min en "Hasta" cuando se selecciona "Desde"
        if (dateFromInput && dateToInput) {
            // Función auxiliar para configurar min en "Hasta" y autorrellenar si corresponde
            function updateMinDateTo() {
                const fromDate = dateFromInput.value;
                if (fromDate) {
                    // Calcular día siguiente a "Desde"
                    const fromDateObj = new Date(fromDate + 'T00:00:00');
                    const nextDay = new Date(fromDateObj);
                    nextDay.setDate(nextDay.getDate() + 1);
                    const nextDayISO = nextDay.toISOString().split('T')[0];
                    
                    // Establecer min en "Hasta" al día siguiente de "Desde"
                    dateToInput.min = nextDayISO;
                    
                    // Si "Hasta" está vacío o menor que min, fijar al día siguiente
                    if (!dateToInput.value || dateToInput.value < nextDayISO) {
                        dateToInput.value = nextDayISO;
                    }
                } else {
                    // Si no hay "Desde", limpiar min y valor de "Hasta"
                    dateToInput.removeAttribute('min');
                    dateToInput.value = '';
                }
            }

            // Ejecutar al cambiar o al ingresar fecha en "Desde"
            dateFromInput.addEventListener('change', () => {
                updateMinDateTo();
                setFechaRangeHint('');
                suppressRangeHint = true;
                handleDateInputsChange();
                suppressRangeHint = false;
            });

            dateFromInput.addEventListener('input', () => {
                updateMinDateTo();
                setFechaRangeHint('');
                suppressRangeHint = true;
                handleDateInputsChange();
                suppressRangeHint = false;
            });

            // Auto-poblar "Hasta" con día siguiente antes de abrir el picker (captura)
            dateToInput.addEventListener('pointerdown', () => {
                // Actualizar min y valor justo antes de que abra el picker nativo
                updateMinDateTo();
            }, true);

            // También manejar focus para teclado/tab
            dateToInput.addEventListener('focus', () => {
                if (dateFromInput && dateFromInput.value) {
                    updateMinDateTo();
                } else {
                    dateToInput.removeAttribute('min');
                }
            });

            dateToInput.addEventListener('change', () => {
                const fromDate = dateFromInput ? dateFromInput.value : '';
                const toDate = dateToInput.value;

                if (fromDate && toDate && toDate < fromDate) {
                    dateToInput.value = '';
                    setFechaRangeHint(DATE_RANGE_HINT_TEXT);
                } else {
                    setFechaRangeHint('');
                }

                handleDateInputsChange();
            });
        }

        // ═══════════════════════════════════════════════════════
        // FILTRAR SUGERENCIAS
        // ═══════════════════════════════════════════════════════
        /**
         * TODO (WP): TAB LUGAR – Fallback geográfico
         * Reglas acordadas:
         * - >=2 letras: sugiere solo sitio_dia
         * - >=3 y 0 matches: mensaje sutil
         * - >=5 y 0 matches: fallback geográfico por cercanía (lat/lng)
         * - Geocoding ambiguo: pedir "Lugar, País" + ofrecer 3-5 opciones
         * - Caché de geocoding
         * - Sugerencias cercanas: máximo 5
         * 
         * Implementar en WP con:
         * - Google Geocoding API (o alternativa)
         * - Sistema de caché (transients/opciones WP)
         * - Fórmula haversine contra sitios existentes (lat/lng en BD)
         */
        function filterSuggestions(query) {
            if (query.length === 0) {
                suggestionsDropdown.classList.remove('visible');
                selectedIndex = -1;
                return;
            }

            const fragment = document.createDocumentFragment();
            let hasResults = false;
            let suggestionIndex = 0;

            const createSuggestionItem = (value, extraClass) => {
                const item = document.createElement('div');
                item.className = `suggestion-item${extraClass ? ' ' + extraClass : ''}`;
                item.dataset.value = value;
                item.id = `suggestion-${currentTab}-${suggestionIndex}`;
                suggestionIndex++;
                return item;
            };

            const appendMetaSpan = (parent, text) => {
                const span = document.createElement('span');
                span.style.color = '#999';
                span.style.fontSize = '0.85rem';
                span.textContent = text;
                parent.appendChild(span);
            };

            const appendHighlightedSpan = (parent, text) => {
                const textSpan = document.createElement('span');
                renderHighlightedText(textSpan, text, query);
                parent.appendChild(textSpan);
            };

            const appendNoResults = (title, subtitle, containerStyle) => {
                const noResults = document.createElement('div');
                if (containerStyle) {
                    Object.assign(noResults.style, containerStyle);
                } else {
                    noResults.className = 'no-results';
                }

                const titleDiv = document.createElement('div');
                titleDiv.textContent = title;
                titleDiv.style.fontSize = '0.95rem';
                titleDiv.style.color = '#666';

                const subtitleDiv = document.createElement('div');
                subtitleDiv.textContent = subtitle;
                subtitleDiv.style.fontSize = '0.85rem';
                subtitleDiv.style.marginTop = '4px';
                subtitleDiv.style.color = '#999';

                noResults.appendChild(titleDiv);
                noResults.appendChild(subtitleDiv);
                fragment.appendChild(noResults);
            };

            if (currentTab === 'todo') {
                suggestionsDropdown.classList.add('mega');

                const q = query.toLowerCase();
                const sortAZ = (a, b) => (a.nombre || '').localeCompare((b.nombre || ''), 'es', { sensitivity: 'variant' });
                const starts = (s) => 
                (s || '').toLowerCase().startsWith(q);

                // Lugar
                const lugaresFiltered = data.lugares
                    .filter(item => starts(item.nombre))
                    .sort(sortAZ)
                    .slice(0, 6);

                // Actividad
                const actividadesFiltered = data.actividades
                    .filter(item => starts(item.nombre))
                    .sort(sortAZ)
                    .slice(0, 6);

                // Fecha (solo si el usuario escribe una fecha exacta dd/mm/aa o dd/mm/aaaa)
                const dateRegex = /^\s*(\d{1,2})\/(\d{1,2})\/(\d{2}|\d{4})\s*$/;
                let fechasFiltered = [];
                const dm = query.match(dateRegex);
                if (dm && (data.dias || []).length) {
                    const dd = dm[1].padStart(2, '0');
                    const mm = dm[2].padStart(2, '0');
                    const yy = dm[3].length === 2 ? ('20' + dm[3]) : dm[3];
                    const normalized = `${dd}/${mm}/${yy}`;

                    fechasFiltered = (data.dias || [])
                        .filter(d => d.fecha === normalized)
                        .slice(0, 3);
                }

                const megaGrid = document.createElement('div');
                megaGrid.className = 'mega-grid';

                const buildMegaCol = (title, items) => {
                    const col = document.createElement('div');
                    col.className = 'mega-col';

                    const titleDiv = document.createElement('div');
                    titleDiv.className = 'mega-title';
                    titleDiv.textContent = title;

                    const list = document.createElement('div');
                    list.className = 'mega-list';

                    if (items.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'mega-empty';
                        empty.textContent = '—';
                        list.appendChild(empty);
                    } else {
                        items.forEach(node => list.appendChild(node));
                    }

                    col.appendChild(titleDiv);
                    col.appendChild(list);
                    return col;
                };

                const lugarItems = lugaresFiltered.map(item => {
                    const itemNode = createSuggestionItem(item.nombre);
                    appendHighlightedSpan(itemNode, item.nombre);
                    itemNode.appendChild(document.createTextNode(' '));
                    appendMetaSpan(itemNode, `(${item.count} actividades)`);
                    return itemNode;
                });

                const actividadItems = actividadesFiltered.map(item => {
                    const itemNode = createSuggestionItem(item.nombre);
                    appendHighlightedSpan(itemNode, item.nombre);
                    itemNode.appendChild(document.createTextNode(' '));
                    appendMetaSpan(itemNode, `• ${item.lugar}`);
                    return itemNode;
                });

                const fechaItems = fechasFiltered.map(d => {
                    const itemNode = createSuggestionItem(d.fecha);
                    const strong = document.createElement('strong');
                    strong.textContent = d.fecha;
                    itemNode.appendChild(strong);
                    itemNode.appendChild(document.createTextNode(' '));
                    appendMetaSpan(itemNode, `• ${d.lugar}`);

                    const hito = document.createElement('div');
                    hito.style.color = '#666';
                    hito.style.fontSize = '0.85rem';
                    hito.style.marginTop = '2px';
                    hito.textContent = d.hito;
                    itemNode.appendChild(hito);
                    return itemNode;
                });

                megaGrid.appendChild(buildMegaCol('Lugar', lugarItems));
                megaGrid.appendChild(buildMegaCol('Fecha', fechaItems));
                megaGrid.appendChild(buildMegaCol('Actividad', actividadItems));

                fragment.appendChild(megaGrid);

                hasResults = (lugaresFiltered.length + actividadesFiltered.length + fechasFiltered.length) > 0;

            } else if (currentTab === 'lugar') {
                suggestionsDropdown.classList.remove('mega');

                // No mostrar dropdown con menos de 2 letras
                if (query.length < 2) {
                    suggestionsDropdown.classList.remove('visible');
                    return;
                }

                const q = query.toLowerCase();
                const filtered = data.lugares
                    .filter(item => (item.nombre || '').toLowerCase().startsWith(q))
                    .sort((a,b) => a.nombre.localeCompare(b.nombre,'es',{sensitivity:'variant'}))
                    .slice(0, 12); // Limitar a 12 sugerencias

                if (filtered.length > 0) {
                    // Hay coincidencias exactas por prefijo
                    filtered.forEach(item => {
                        const itemNode = createSuggestionItem(item.nombre);
                        appendHighlightedSpan(itemNode, item.nombre);
                        itemNode.appendChild(document.createTextNode(' '));
                        appendMetaSpan(itemNode, `(${item.count} actividades)`);
                        fragment.appendChild(itemNode);
                    });
                    hasResults = true;
                } else if (query.length >= 3 && query.length < 5) {
                    // Entre 3-4 letras sin coincidencias: mensaje sutil específico
                    appendNoResults('No encuentro ese lugar.', 'Prueba con otra ortografía.', {
                        padding: '16px',
                        textAlign: 'center',
                        color: '#666'
                    });
                    hasResults = true; // Marcar como true para evitar mensaje genérico
                } else if (DEMO_GEO_FALLBACK && query.length >= 5) {
                    // 5+ letras: intentar fallback geográfico (DEMO)
                    const queryNorm = q.trim().toLowerCase();
                    const fallbackPlaces = geoDemoFallback[queryNorm];
                    
                    if (fallbackPlaces === null) {
                        // Caso especial: ambigüedad geográfica (ej: "santiago")
                        const options = geoAmbiguousOptions[queryNorm] || [];

                        const header = document.createElement('div');
                        header.style.padding = '16px';
                        header.style.borderBottom = '1px solid #eee';

                        const headerText = document.createElement('div');
                        headerText.style.fontSize = '0.95rem';
                        headerText.style.color = '#666';
                        headerText.style.marginBottom = '12px';
                        headerText.appendChild(document.createTextNode('Hay varios lugares con ese nombre.'));
                        headerText.appendChild(document.createElement('br'));

                        const subText = document.createElement('span');
                        subText.style.color = '#999';
                        subText.style.fontSize = '0.85rem';
                        subText.textContent = "Indica país (ej.: 'Santiago, Chile').";
                        headerText.appendChild(subText);

                        header.appendChild(headerText);

                        const list = document.createElement('div');
                        list.style.padding = '8px 0';

                        options.forEach(option => {
                            const itemNode = createSuggestionItem(option, 'geo-ambiguous-option');
                            itemNode.style.cursor = 'pointer';
                            itemNode.textContent = option;
                            list.appendChild(itemNode);
                        });

                        fragment.appendChild(header);
                        fragment.appendChild(list);
                        hasResults = true;
                    } else if (fallbackPlaces && fallbackPlaces.length > 0) {
                        // Lugares cercanos (mock)
                        // Filtrar solo los que existan en el dataset
                        const nearbyExisting = fallbackPlaces
                            .map(name => data.lugares.find(l => l.nombre.toLowerCase() === name.toLowerCase()))
                            .filter(Boolean)
                            .slice(0, 5);
                        
                        if (nearbyExisting.length > 0) {
                            const header = document.createElement('div');
                            header.style.padding = '16px';
                            header.style.borderBottom = '1px solid #eee';

                            const headerLine = document.createElement('div');
                            headerLine.style.fontSize = '0.95rem';
                            headerLine.style.color = '#666';
                            headerLine.appendChild(document.createTextNode("No encuentro '"));

                            const strong = document.createElement('strong');
                            strong.textContent = query;
                            headerLine.appendChild(strong);
                            headerLine.appendChild(document.createTextNode("'."));

                            const headerNote = document.createElement('div');
                            headerNote.style.fontSize = '0.9rem';
                            headerNote.style.color = '#2c5aa0';
                            headerNote.style.marginTop = '8px';
                            headerNote.style.fontWeight = '500';
                            headerNote.textContent = 'Lugares cercanos en tu viaje:';

                            header.appendChild(headerLine);
                            header.appendChild(headerNote);

                            const list = document.createElement('div');
                            list.style.padding = '8px 0';

                            nearbyExisting.forEach(item => {
                                const itemNode = createSuggestionItem(item.nombre, 'geo-nearby');
                                itemNode.dataset.geoFallback = 'true';
                                itemNode.appendChild(document.createTextNode(item.nombre + ' '));
                                appendMetaSpan(itemNode, `(${item.count} actividades)`);
                                list.appendChild(itemNode);
                            });

                            fragment.appendChild(header);
                            fragment.appendChild(list);
                            hasResults = true;
                        } else {
                            // No hay lugares cercanos en el dataset
                            appendNoResults('No encuentro ese lugar.', 'Prueba con otra ortografía.', {
                                padding: '16px',
                                textAlign: 'center',
                                color: '#666'
                            });
                        }
                    } else {
                        // No hay fallback configurado para esta query
                        appendNoResults('No encuentro ese lugar.', 'Prueba con otra ortografía.', {
                            padding: '16px',
                            textAlign: 'center',
                            color: '#666'
                        });
                    }
                }

            } else if (currentTab === 'actividad') {
                suggestionsDropdown.classList.remove('mega');

                // Regla: mínimo 3 letras para mostrar dropdown
                if (query.length < 3) {
                    suggestionsDropdown.classList.remove('visible');
                    return;
                }

                const q = query.toLowerCase();
                const filtered = data.actividades
                    .filter(item => (item.nombre || '').toLowerCase().startsWith(q))
                    .sort((a,b) => a.nombre.localeCompare(b.nombre,'es',{sensitivity:'variant'}));

                if (filtered.length > 0) {
                    const MAX_SUGGESTIONS = 10;
                    const hasMore = filtered.length > MAX_SUGGESTIONS;
                    const toShow = filtered.slice(0, MAX_SUGGESTIONS);

                    toShow.forEach(item => {
                        const itemNode = createSuggestionItem(item.nombre);
                        appendHighlightedSpan(itemNode, item.nombre);
                        fragment.appendChild(itemNode);
                    });

                    if (hasMore) {
                        const moreItem = createSuggestionItem('Más…', 'more-indicator');
                        moreItem.dataset.action = 'show-all';
                        moreItem.style.color = '#999';
                        moreItem.style.cursor = 'pointer';
                        moreItem.textContent = 'Más…';
                        fragment.appendChild(moreItem);
                    }

                    hasResults = true;
                }

            } else if (currentTab === 'fecha') {
                suggestionsDropdown.classList.remove('mega');

                const dateRegex = /^\s*(\d{1,2})\/(\d{1,2})\/(\d{2}|\d{4})\s*$/;
                const dm = query.match(dateRegex);
                if (dm) {
                    const dd = dm[1].padStart(2, '0');
                    const mm = dm[2].padStart(2, '0');
                    const yy = dm[3].length === 2 ? ('20' + dm[3]) : dm[3];
                    const normalized = `${dd}/${mm}/${yy}`;

                    const filtered = (data.dias || []).filter(d => d.fecha === normalized);

                    if (filtered.length > 0) {
                        filtered.forEach(d => {
                            const itemNode = createSuggestionItem(d.fecha);
                            const strong = document.createElement('strong');
                            strong.textContent = d.fecha;
                            itemNode.appendChild(strong);
                            itemNode.appendChild(document.createTextNode(' '));
                            appendMetaSpan(itemNode, `• ${d.lugar}`);

                            const hito = document.createElement('div');
                            hito.style.color = '#666';
                            hito.style.fontSize = '0.85rem';
                            hito.style.marginTop = '2px';
                            hito.textContent = d.hito;
                            itemNode.appendChild(hito);

                            fragment.appendChild(itemNode);
                        });
                        hasResults = true;
                    }
                }

            } else {
                suggestionsDropdown.classList.remove('mega');
            }

            // Solo mostrar mensaje global de "No se encontraron resultados" si NO estamos en modo mega-dropdown
            // En mega-dropdown, cada columna muestra su propio estado vacío
            // En tab 'lugar', ya se manejan mensajes específicos, no usar el genérico
            if (!hasResults && !suggestionsDropdown.classList.contains('mega') && currentTab !== 'lugar') {
                const noResults = document.createElement('div');
                noResults.className = 'no-results';

                const title = document.createElement('div');
                title.className = 'no-results-title';
                title.textContent = 'No se encontraron resultados';

                const subtitle = document.createElement('div');
                subtitle.textContent = 'Prueba con otra búsqueda';

                noResults.appendChild(title);
                noResults.appendChild(subtitle);
                fragment.appendChild(noResults);
            }

            suggestionsDropdown.innerHTML = '';
            suggestionsDropdown.appendChild(fragment);
            suggestionsDropdown.classList.add('visible');
            updateDropdownMaxHeight();
            
            // Aplicar clase selected al item en selectedIndex
            updateSelectedItem();
        }

        // ═══════════════════════════════════════════════════════
        // ACTUALIZAR ITEM SELECCIONADO VISUALMENTE
        // ═══════════════════════════════════════════════════════
        function updateSelectedItem() {
            const items = suggestionsDropdown.querySelectorAll('.suggestion-item');
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('selected');
                    // Scroll automático para mantener visible el item seleccionado
                    item.scrollIntoView({ block: 'nearest', behavior: 'auto' });
                } else {
                    item.classList.remove('selected');
                }
            });
        }

        // ═══════════════════════════════════════════════════════
        // NAVEGACIÓN CON TECLADO
        // ═══════════════════════════════════════════════════════
        searchInput.addEventListener('keydown', (e) => {
            const items = suggestionsDropdown.querySelectorAll('.suggestion-item');
            
            if (!suggestionsDropdown.classList.contains('visible') || items.length === 0) {
                return;
            }

            // Flecha ABAJO: Mover al siguiente item
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex++;
                if (selectedIndex >= items.length) {
                    selectedIndex = items.length - 1;
                }
                updateSelectedItem();
            }
            
            // Flecha ARRIBA: Mover al item anterior
            else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex--;
                if (selectedIndex < 0) {
                    selectedIndex = 0;
                }
                updateSelectedItem();
            }
            
            // ENTER: Seleccionar item resaltado
            else if (e.key === 'Enter') {
                e.preventDefault();
                if (!hasExplicitSelection) {
                    return;
                }
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    const selectedItem = items[selectedIndex];
                    const value = selectedItem.dataset.value || selectedItem.textContent.split('•')[0].split('(')[0].trim();
                    searchInput.value = value;
                    suggestionsDropdown.classList.remove('visible');
                    selectedIndex = -1;
                    if (document.activeElement && typeof document.activeElement.blur === 'function') {
                        document.activeElement.blur();
                    }
                    
                    // Activar modo confirmado
                    queryLocked = true;
                    confirmedSelection = { value, tab: currentTab };
                    updateLockedChip();
                    log('🔒 Modo confirmado activado:', confirmedSelection);
                    
                    // Actualizar resultados con filtro exacto
                    updateResultsVisibility(value);
                    
                    // Actualizar mapa con la selección
                    updateMapForSelection({ value, tab: currentTab });
                    
                    // Actualizar mapa con los resultados mostrados
                    if (displayedResults.length > 0) {
                        updateMapForResults(displayedResults);
                        // En modo estricto, el cambio de pantalla solo ocurre por Enter o botón de búsqueda
                    }

                    goToResultsStrict();
                }
            }
            
            // ESC: Cerrar dropdown y desbloquear si está locked
            else if (e.key === 'Escape') {
                e.preventDefault();
                
                // Si la búsqueda está bloqueada, resetear completamente
                if (queryLocked) {
                    resetSearchUI();
                } else {
                    // Si no está bloqueada, solo cerrar dropdown
                    suggestionsDropdown.classList.remove('visible');
                    selectedIndex = -1;
                }
            }
        });

        // ═══════════════════════════════════════════════════════
        // HIGHLIGHT DE LETRAS COINCIDENTES
        // ═══════════════════════════════════════════════════════
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        /**
         * Renderiza texto con coincidencias resaltadas de forma segura
         * (sin innerHTML)
         */
        function renderHighlightedText(container, text, query) {
            container.textContent = '';

            if (!query || query.length < 2) {
                container.textContent = text;
                return;
            }

            const lowerText = text.toLowerCase();
            const lowerQuery = query.toLowerCase();
            let lastIndex = 0;
            let index = lowerText.indexOf(lowerQuery);

            while (index !== -1) {
                if (index > lastIndex) {
                    container.appendChild(
                        document.createTextNode(text.substring(lastIndex, index))
                    );
                }

                const mark = document.createElement('span');
                mark.className = 'highlight';
                mark.textContent = text.substring(index, index + query.length);
                container.appendChild(mark);

                lastIndex = index + query.length;
                index = lowerText.indexOf(lowerQuery, lastIndex);
            }

            if (lastIndex < text.length) {
                container.appendChild(
                    document.createTextNode(text.substring(lastIndex))
                );
            }
        }

        // Función específica para resaltar en el grid (monocromo)
        function highlightMatchGrid(text, query) {
            if (!query || query.length < 2) {
                return text;
            }
            const safeQuery = escapeRegExp(query);
            const regex = new RegExp(`(${safeQuery})`, 'gi');
            return text.replace(regex, '<span class="highlight-grid">$1</span>');
        }
        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }


        // ═══════════════════════════════════════════════════════
        // CERRAR DROPDOWN AL HACER CLICK FUERA
        // ═══════════════════════════════════════════════════════
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsDropdown.contains(e.target)) {
                suggestionsDropdown.classList.remove('visible');
                selectedIndex = -1;
            }
        });

        window.addEventListener('resize', () => {
            updateDropdownMaxHeight();
        });

        // ═══════════════════════════════════════════════════════
        // ATAJO DE TECLADO:  Ctrl+K
        // ═══════════════════════════════════════════════════════
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });

        // ═══════════════════════════════════════════════════════
        // CLICK EN SUGERENCIA
        // ═══════════════════════════════════════════════════════
        suggestionsDropdown.addEventListener('click', (e) => {
            if (typeof e.preventDefault === 'function') e.preventDefault();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
            const item = e.target.closest('.suggestion-item');
            if (!item) return;
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }

            // PASO 5/6: Manejar click en "Más..." de tab Actividad
            if (item.dataset.action === 'show-all' && currentTab === 'actividad') {
                const currentQuery = searchInput.value.trim();
                
                // Cerrar dropdown
                suggestionsDropdown.classList.remove('visible');
                selectedIndex = -1;
                
                // Confirmar búsqueda
                queryLocked = true;
                confirmedSelection = { value: currentQuery, tab: currentTab };
                updateLockedChip();
                log('🔒 Modo confirmado activado (Más...):', confirmedSelection);
                
                // Buscar TODOS los resultados que coinciden con el término
                const q = currentQuery.toLowerCase();
                const allMatches = demoResults
                    .filter(entry => {
                        const title = (entry.title || '').toLowerCase();
                        return title.includes(q);
                    })
                    .sort((a, b) => {
                        // Orden cronológico ascendente
                        const dateA = a.date ? new Date(a.date) : new Date(0);
                        const dateB = b.date ? new Date(b.date) : new Date(0);
                        return dateA - dateB;
                    });
                
                log('📋 Mostrando todos los resultados:', allMatches.length);
                renderActividadResults(allMatches);
                
                // Actualizar mapa
                const uniquePlaces = [];
                const seenPlaces = new Set();
                
                allMatches.forEach(entry => {
                    const lugar = (entry.location || '').trim();
                    if (lugar && !seenPlaces.has(lugar.toLowerCase())) {
                        seenPlaces.add(lugar.toLowerCase());
                        uniquePlaces.push({ lugar: lugar });
                    }
                });
                
                const placesToMap = uniquePlaces.slice(0, 25);
                if (uniquePlaces.length > 25) {
                    log(`⚠️ Mostrando 25 de ${uniquePlaces.length} lugares`);
                }
                
                log(`🗺️ MAPA ACTIVIDAD: ${placesToMap.length} lugar${placesToMap.length !== 1 ? 'es' : ''}`);
                updateMapForResults(placesToMap);

                goToResultsStrict();
                return;
            }

            // Verificar si es una sugerencia de geo-fallback o ambigüedad
            const isGeoFallback = item.dataset.geoFallback === 'true';
            const isAmbiguous = item.classList.contains('geo-ambiguous-option');
            
            // Usar data-value para obtener el valor exacto (especialmente importante para fechas)
            const value = item.dataset.value || item.textContent.split('•')[0].split('(')[0].trim();

            if (item.dataset.value) {
                explicitSelectedValue = item.dataset.value;
                hasExplicitSelection = true;
            }
            
            if (isAmbiguous) {
                // Caso ambiguo: rellenar input y volver a filtrar
                // (en este demo no hacemos nada más, solo mostramos la UX)
                searchInput.value = value;
                clearButton.classList.add('visible');
                suggestionsDropdown.classList.remove('visible');
                selectedIndex = -1;
                
                // Nota: En producción, aquí se haría geocoding con el término completo
                // Por ahora solo cerramos el dropdown
                log('ℹ️ Selección ambigua:', value, '(requiere geocoding en producción)');
                goToResultsStrict();
                return;
            }
            
            if (isGeoFallback) {
                log('📍 Selección de lugar cercano (geo-fallback):', value);
            }
            
            // Continuar con la confirmación normal
            searchInput.value = value;
            suggestionsDropdown.classList.remove('visible');
            selectedIndex = -1;
            
            // Activar modo confirmado
            queryLocked = true;
            confirmedSelection = { value, tab: currentTab };
            updateLockedChip();
            log('🔒 Modo confirmado activado:', confirmedSelection);
            
            // Actualizar resultados con filtro exacto
            updateResultsVisibility(value);
            
            // Actualizar mapa con la selección
            updateMapForSelection({ value, tab: currentTab });
            
            // Actualizar mapa con los resultados mostrados
            if (hasExplicitSelection && displayedResults.length > 0) {
                updateMapForResults(displayedResults);
            }

            goToResultsStrict();
        });

        function setScrollLock(locked) {
            if (locked) {
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${window.scrollY}px`;
            } else {
                const scrollY = document.body.style.top;
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
        }

        const DEBUG_SCROLL = false;
        let lastScrollToResultsAt = 0;

        function getScrollParent(element) {
            if (!element) return document.scrollingElement || document.documentElement;

            let parent = element.parentElement;
            while (parent) {
                const style = window.getComputedStyle(parent);
                const overflowY = style.overflowY;
                const canScroll = (overflowY === 'auto' || overflowY === 'scroll') && parent.scrollHeight > parent.clientHeight;
                if (canScroll) return parent;
                parent = parent.parentElement;
            }

            return document.scrollingElement || document.documentElement;
        }

        function animateScroll(container, to, durationMs = 1100) {
            const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const isWindowScroll = container === (document.scrollingElement || document.documentElement);

            if (prefersReducedMotion) {
                if (isWindowScroll) {
                    window.scrollTo(0, to);
                } else {
                    container.scrollTop = to;
                }
                return;
            }

            const start = isWindowScroll ? window.pageYOffset : container.scrollTop;
            const distance = to - start;
            const startTime = performance.now();

            const easeInOutCubic = (t) => {
                return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
            };

            function step(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / durationMs, 1);
                const eased = easeInOutCubic(progress);
                const next = start + distance * eased;

                if (isWindowScroll) {
                    window.scrollTo(0, next);
                } else {
                    container.scrollTop = next;
                }

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }

            requestAnimationFrame(step);
        }

        function scrollToResultsSection() {
            const now = Date.now();
            if (now - lastScrollToResultsAt < 250) {
                return;
            }
            lastScrollToResultsAt = now;

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    const target = screen2El || document.querySelector('#screen2');
                    if (!target) return;

                    const container = getScrollParent(target);
                    const targetRect = target.getBoundingClientRect();
                    let to = 0;

                    if (container === (document.scrollingElement || document.documentElement)) {
                        to = window.pageYOffset + targetRect.top;
                    } else {
                        const containerRect = container.getBoundingClientRect();
                        to = container.scrollTop + (targetRect.top - containerRect.top);
                    }

                    const current = container === (document.scrollingElement || document.documentElement)
                        ? window.pageYOffset
                        : container.scrollTop;
                    const distance = Math.abs(to - current);
                    const durationMs = distance < 250 ? 650 : 1100;

                    if (DEBUG_SCROLL) {
                        log('🔎 Scroll container:', container);
                        log('🔎 Scroll to:', to, 'current:', current, 'target:', target);
                    }

                    animateScroll(container, to, durationMs);
                });
            });
        }

        showSearchScreen();

        // ═══════════════════════════════════════════════════════
        // MODO ESTRICTO: ÚNICAS PUERTAS DE CAMBIO A SCREEN 2
        // ═══════════════════════════════════════════════════════
        if (screen1SearchInputs.length > 0) {
            screen1SearchInputs.forEach((input) => {
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        goToResultsStrict();
                    }
                });
            });
        }

        if (screen1SearchButton) {
            screen1SearchButton.addEventListener('click', (e) => {
                e.preventDefault();
                goToResultsStrict();
            });
        }

        /* ═══════════════════════════════════════════════════════════════════════════
           INFINITE SCROLL - Carga progresiva de resultados
           ═══════════════════════════════════════════════════════════════════════════ */

        (function() {
            // Configuración
            const ITEMS_PER_PAGE = 8; // Fichas a cargar cada vez
            let currentPage = 1;
            let isLoading = false;
            let allItemsLoaded = false;
            
            // Elementos DOM
            const resultsGrid = document.querySelector('.results-grid');
            const loader = document.getElementById('infiniteScrollLoader');
            const endMessage = document.getElementById('infiniteScrollEnd');
            
            if (!resultsGrid || !loader) return;
            
            // Función para simular carga de más resultados
            // TODO-PHP: Reemplazar por llamada AJAX real
            function loadMoreResults() {
                if (isLoading || allItemsLoaded) return;
                
                isLoading = true;
                loader.classList.add('visible');
                
                // Simular delay de red
                setTimeout(function() {
                    // TODO-PHP: Aquí va la llamada AJAX real
                    // const response = await fetch('/api/results?page=' + currentPage);
                    // const newItems = await response.json();
                    
                    // SIMULACIÓN: Crear fichas de ejemplo
                    const totalSimulatedItems = 31; // Total de resultados simulados
                    const loadedItems = (currentPage - 1) * ITEMS_PER_PAGE + document.querySelectorAll('.destination-card').length;
                    
                    if (loadedItems >= totalSimulatedItems) {
                        allItemsLoaded = true;
                        loader.classList.remove('visible');
                        endMessage.classList.add('visible');
                        isLoading = false;
                        return;
                    }
                    
                    // Añadir nuevas fichas (simulado)
                    const itemsToAdd = Math.min(ITEMS_PER_PAGE, totalSimulatedItems - loadedItems);
                    for (let i = 0; i < itemsToAdd; i++) {
                        const card = createSimulatedCard(loadedItems + i + 1);
                        resultsGrid.appendChild(card);
                    }

                    syncFavoriteButtons();
                    
                    currentPage++;
                    isLoading = false;
                    loader.classList.remove('visible');
                    
                    // Verificar si ya cargamos todo
                    if (document.querySelectorAll('.destination-card').length >= totalSimulatedItems) {
                        allItemsLoaded = true;
                        endMessage.classList.add('visible');
                    }
                }, 800);
            }
            
            // Crear ficha simulada (TODO-PHP: Eliminar en producción)
            function createSimulatedCard(index) {
                const card = document.createElement('div');
                card.className = 'destination-card';
                card.innerHTML = `
                    <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="res-sim-${index}">
                        <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                          <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                        </svg>
                    </button>
                    <div class="card-image">
                        <img src="https://picsum.photos/400/300?random=${index}" alt="Destino ${index}">
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Título ${index}</h3>
                        <p class="card-line1">Línea 1</p>
                        <p class="card-line2">Línea 2</p>
                    </div>
                `;
                return card;
            }
            
            // Detectar scroll cerca del final
            function handleScroll() {
                if (allItemsLoaded || isLoading) return;
                
                const scrollPosition = window.innerHeight + window.pageYOffset;
                const threshold = document.documentElement.scrollHeight - 300;
                
                if (scrollPosition >= threshold) {
                    loadMoreResults();
                }
            }
            
            // Escuchar scroll
            window.addEventListener('scroll', handleScroll);
            
            // Verificar estado inicial (por si la página es corta)
            handleScroll();
        })();
        }

/* ═══════════════════════════════════════════════════════════════════════
    CARRUSEL 3D DE RECOMENDACIONES — Motor de navegación
    ═══════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
     'use strict';

    var stack = document.getElementById('carouselStack');
    if (!stack) return; // Si no hay carrusel en la página, salir

    var cards = Array.from(stack.querySelectorAll('.carousel-card'));
    var dotsContainer = document.getElementById('carouselDots');
    var dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll('.carousel-dot')) : [];
    var counterEl = document.getElementById('carouselCounter');

    var total = cards.length;
    if (total === 0) return;

    var current = 0;

    // Parámetros de distribución 3D
    var stepX = 180;
    var stepY = 14;
    var stepRotate = 6;
    var depthStep = 200;

    var opacityByLevel = { 0: 1, 1: 0.7, 2: 0.5, 3: 0.2 };

    function normalize(index) {
        return ((index % total) + total) % total;
    }

    // Detectar si estamos en mobile
    function isMobile() {
        return window.innerWidth <= 1023;
    }

    // Controles existentes en el HTML
    var prevBtn = document.querySelector('.carousel-prev');
    var nextBtn = document.querySelector('.carousel-next');

    // Render: posicionar todas las cards
    function render() {
        var mobile = isMobile();
        
        cards.forEach(function (card, index) {
            var offset = (index - current + total) % total;
            var signed = offset > total / 2 ? offset - total : offset;
            var abs = Math.abs(signed);
            var dir = signed > 0 ? 1 : signed < 0 ? -1 : 0;

            var tx = 0, ty = 0, tz = 0, scale = 1, rotate = 0, opacity = 1, zIndex = 6;

            if (mobile) {
                // Modo 2D simplificado para mobile
                if (abs === 0) {
                    card.classList.add('is-active');
                    zIndex = 6;
                    opacity = 1;
                    scale = 1;
                } else if (abs === 1) {
                    card.classList.remove('is-active');
                    tx = 60 * dir;
                    ty = 8;
                    scale = 0.85;
                    opacity = 0.6;
                    zIndex = 4;
                } else {
                    card.classList.remove('is-active');
                    tx = 100 * dir;
                    ty = 12;
                    scale = 0.7;
                    opacity = 0.3;
                    zIndex = 2;
                }
                // Sin rotación ni profundidad en mobile
                card.style.transform = 'translate(-50%, -50%) translateX(' + tx + 'px) translateY(' + ty + 'px) scale(' + scale + ')';
            } else {
                // Modo 3D completo para desktop
                if (abs === 0) {
                    card.classList.add('is-active');
                    zIndex = 6;
                } else if (abs === 1) {
                    card.classList.remove('is-active');
                    tx = stepX * dir;
                    ty = stepY;
                    tz = -depthStep;
                    scale = 0.82;
                    rotate = stepRotate * dir;
                    opacity = opacityByLevel[1];
                    zIndex = 4;
                } else if (abs === 2) {
                    card.classList.remove('is-active');
                    tx = stepX * 1.6 * dir;
                    ty = stepY * 2;
                    tz = -depthStep * 1.6;
                    scale = 0.7;
                    rotate = stepRotate * 1.6 * dir;
                    opacity = opacityByLevel[2];
                    zIndex = 2;
                } else {
                    card.classList.remove('is-active');
                    tx = stepX * 2.1 * dir;
                    ty = stepY * 2.6;
                    tz = -depthStep * 2.1;
                    scale = 0.62;
                    rotate = stepRotate * 2 * dir;
                    opacity = opacityByLevel[3];
                    zIndex = 1;
                }
                card.style.transform = 'translate(-50%, -50%) translateX(' + tx + 'px) translateY(' + ty + 'px) translateZ(' + tz + 'px) scale(' + scale + ') rotate(' + rotate + 'deg)';
            }

            card.style.opacity = opacity;
            card.style.zIndex = zIndex;
            card.style.filter = abs === 0 ? 'none' : 'brightness(0.95)';
        });

        // Actualizar dots
        dots.forEach(function (dot, i) {
            dot.classList.toggle('active', i === current);
        });

        // Actualizar contador
        if (counterEl) {
            counterEl.textContent = (current + 1) + ' de ' + total;
        }
    }

    function goTo(index) {
        current = normalize(index);
        render();
    }

    // Click en flechas
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            goTo(current - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            goTo(current + 1);
        });
    }

    // Click en dots
    if (dotsContainer) {
        dotsContainer.addEventListener('click', function (e) {
            var dot = e.target.closest('.carousel-dot');
            if (!dot) return;
            var idx = parseInt(dot.getAttribute('data-index'), 10);
            if (!isNaN(idx)) goTo(idx);
        });
    }

    // Teclado (solo si el carrusel está visible)
    document.addEventListener('keydown', function (e) {
        var section = document.getElementById('screen1');
        if (section && section.style.display === 'none') return;
        if (e.key === 'ArrowLeft') goTo(current - 1);
        if (e.key === 'ArrowRight') goTo(current + 1);
    });

    // Swipe táctil
    var startX = 0;
    var startY = 0;
    var isSwiping = false;

    stack.addEventListener('touchstart', function (e) {
        var touch = e.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
        isSwiping = false;
    }, { passive: true });

    stack.addEventListener('touchmove', function (e) {
        var touch = e.touches[0];
        var dx = touch.clientX - startX;
        var dy = touch.clientY - startY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 18) {
            isSwiping = true;
            e.preventDefault();
        }
    }, { passive: false });

    stack.addEventListener('touchend', function (e) {
        if (!isSwiping) return;
        var touch = e.changedTouches[0];
        var dx = touch.clientX - startX;
        if (dx < -30) goTo(current + 1);
        if (dx > 30) goTo(current - 1);
    });

    // Re-render en resize
    window.addEventListener('resize', render);

    // Primer render
    render();
});
