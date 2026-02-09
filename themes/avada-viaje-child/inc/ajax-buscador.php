<?php
/**
 * AJAX Handler para Buscador Global
 * Busca en todos los Custom Post Types: dia, tramo, actividad, hospedaje, restaurante, capitulo
 */

/**
 * Normalizar texto para búsqueda fuzzy
 * Quita acentos, convierte a minúsculas, elimina caracteres especiales
 */
function normalizar_para_busqueda($texto) {
    // Convertir a minúsculas
    $texto = strtolower($texto);
    
    // Quitar acentos
    $acentos = array(
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
        'ñ' => 'n', 'ç' => 'c'
    );
    
    $texto = strtr($texto, $acentos);
    
    // Eliminar caracteres especiales (dejar solo letras, números y espacios)
    $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);
    
    // Eliminar espacios múltiples
    $texto = preg_replace('/\s+/', ' ', $texto);
    
    return trim($texto);
}

/**
 * Obtener diccionario de sitios para corrección fuzzy
 */
function obtener_diccionario_sitio() {
    // Palabras clave FORZADAS (debe incluir cruceros)
    $palabras_clave_forzadas = array(
        'crucero', 
        'cruceros',   // Términos comunes de viajes
        'madrid', 
        'barcelona', 
        'tenerife', 
        'adeje',
        'costa',
        'playa',
        'museo', 
        'museos',
        'restaurante',
        'hotel',
        'villa',
        'aeropuerto'
    );
    
    error_log('🔑 Palabras clave forzadas: ' . implode(', ', $palabras_clave_forzadas));
    
    // Verificar si "cruceros" está en el array
    if (in_array('cruceros', $palabras_clave_forzadas)) {
        error_log('✅ "cruceros" ESTÁ en palabras clave');
    } else {
        error_log('❌ "cruceros" NO está en palabras clave');
    }
    
    // Extraer palabras únicas de campos de ubicación (cache por rendimiento)
    $cache_key = 'diccionario_sitios_v1';
    $otras_palabras = get_transient($cache_key);
    
    if (false === $otras_palabras) {
        $otras_palabras = array();
        
        // Obtener sitios únicos de diferentes post types
        $post_types = array('dia', 'tramo', 'actividad', 'hospedaje', 'restaurante');
        $campos = array('sitio_dia', 'sitio_inicio_tramo', 'sitio_fin_tramo', 'sitio_actividad', 'sitio_hospedaje', 'sitio_restaurante');
        
        foreach ($campos as $campo) {
            $valores = get_posts(array(
                'post_type' => $post_types,
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => $campo,
                        'compare' => 'EXISTS'
                    )
                )
            ));
            
            foreach ($valores as $post_id) {
                $valor = get_field($campo, $post_id);
                if (!empty($valor)) {
                    // Normalizar y separar palabras
                    $palabras = explode(' ', normalizar_para_busqueda($valor));
                    foreach ($palabras as $palabra) {
                        if (strlen($palabra) > 3) {  // Solo palabras de más de 3 letras
                            $otras_palabras[] = $palabra;
                        }
                    }
                }
            }
        }
        
        $otras_palabras = array_unique($otras_palabras);
        set_transient($cache_key, $otras_palabras, HOUR_IN_SECONDS);
    }
    
    $diccionario = array_unique(array_merge($palabras_clave_forzadas, $otras_palabras));
    
    // Verificar si "cruceros" está en el diccionario final
    if (in_array('cruceros', $diccionario)) {
        error_log('✅ "cruceros" ESTÁ en diccionario final (' . count($diccionario) . ' palabras)');
    } else {
        error_log('❌ "cruceros" NO está en diccionario final');
    }
    
    return $diccionario;
}

/**
 * Calcular distancia de Levenshtein (similitud entre palabras)
 */
function calcular_similitud($palabra1, $palabra2) {
    $distancia = levenshtein($palabra1, $palabra2);
    $longitud_max = max(strlen($palabra1), strlen($palabra2));
    
    if ($longitud_max === 0) {
        return 1.0;
    }
    
    return 1 - ($distancia / $longitud_max);
}

/**
 * Corregir palabra usando diccionario fuzzy
 */
function corregir_palabra_fuzzy($palabra, $umbral = 0.8) {
    $palabra_normalizada = normalizar_para_busqueda($palabra);
    $diccionario = obtener_diccionario_sitio();
    
    $mejor_coincidencia = null;
    $mejor_similitud = 0;
    
    foreach ($diccionario as $palabra_diccionario) {
        $similitud = calcular_similitud($palabra_normalizada, $palabra_diccionario);
        
        if ($similitud > $mejor_similitud && $similitud >= $umbral) {
            $mejor_similitud = $similitud;
            $mejor_coincidencia = $palabra_diccionario;
        }
    }
    
    if ($mejor_coincidencia) {
        error_log("🔧 Corrección: '$palabra' → '$mejor_coincidencia' (similitud: " . round($mejor_similitud * 100) . "%)");
        return $mejor_coincidencia;
    }
    
    return $palabra_normalizada;
}

/**
 * Calcular score de relevancia de un resultado
 * 
 * @param object $post El post object
 * @param string $query El término de búsqueda original
 * @return array Array con 'score' (int) y 'relevancia' (alta|media|baja)
 */
function calcular_relevancia_resultado($post, $query) {
    $score = 0;
    
    $query_lower = strtolower(trim($query));
    $query_normalizado = normalizar_para_busqueda($query);
    
    // ═══════════════════════════════════════
    // PUNTOS POR COINCIDENCIA EN TÍTULO
    // ═══════════════════════════════════════
    
    $titulo = get_the_title($post->ID);
    $titulo_lower = strtolower($titulo);
    $titulo_normalizado = normalizar_para_busqueda($titulo);
    
    // Coincidencia EXACTA en título
    if ($titulo_lower === $query_lower) {
        $score += 100;
    }
    // Título CONTIENE la búsqueda
    elseif (strpos($titulo_normalizado, $query_normalizado) !== false) {
        $score += 80;
        
        // BONUS: Título EMPIEZA con la búsqueda
        if (strpos($titulo_normalizado, $query_normalizado) === 0) {
            $score += 50;
        }
    }
    
    // ═══════════════════════════════════════
    // PUNTOS POR COINCIDENCIA EN UBICACIÓN
    // ═══════════════════════════════════════
    
    $post_type = get_post_type($post->ID);
    $ubicacion = '';
    
    switch ($post_type) {
        case 'dia':
            $ubicacion = get_field('sitio_dia', $post->ID);
            break;
        case 'tramo':
            $ubicacion = get_field('sitio_inicio_tramo', $post->ID);
            break;
        case 'actividad':
            $ubicacion = get_field('sitio_actividad', $post->ID);
            break;
        case 'hospedaje':
            $ubicacion = get_field('sitio_hospedaje', $post->ID);
            break;
        case 'restaurante': 
            $ubicacion = get_field('sitio_restaurante', $post->ID);
            break;
    }
    
    if ($ubicacion) {
        $ubicacion_normalizada = normalizar_para_busqueda($ubicacion);
        
        // Ubicación EXACTA
        if ($ubicacion_normalizada === $query_normalizado) {
            $score += 50;
        }
        // Ubicación CONTIENE
        elseif (strpos($ubicacion_normalizada, $query_normalizado) !== false) {
            $score += 30;
        }
    }
    
    // ═══════════════════════════════════════
    // PUNTOS POR TIPO DE POST
    // ═══════════════════════════════════════
    
    switch ($post_type) {
        case 'tramo':
        case 'capitulo':
            $score += 20;
            break;
        case 'dia': 
            $score += 15;
            break;
        case 'actividad':
            $score += 10;
            break;
        case 'hospedaje': 
            $score += 5;
            break;
        case 'restaurante':
            $score += 5;
            break;
    }
    
    // ═══════════════════════════════════════
    // CLASIFICAR RELEVANCIA
    // ═══════════════════════════════════════
    
    $relevancia = 'baja';
    
    if ($score >= 100) {
        $relevancia = 'alta';
    } elseif ($score >= 50) {
        $relevancia = 'media';
    }
    
    return array(
        'score' => $score,
        'relevancia' => $relevancia
    );
}

// Endpoint AJAX para búsqueda global
function buscador_global_ajax_handler() {
    // Recibir y sanitizar parámetros
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'todo';
    $chip = isset($_POST['chip']) ? sanitize_text_field($_POST['chip']) : '';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    // ════════════════════════════════════════════════════════
    // RECIBIR FILTROS AVANZADOS
    // ════════════════════════════════════════════════════════
    $filtros = array(
        'presupuesto' => isset($_POST['filtro_presupuesto']) ? sanitize_text_field($_POST['filtro_presupuesto']) : '',
        'tipo' => isset($_POST['filtro_tipo']) ? sanitize_text_field($_POST['filtro_tipo']) : '',
        'duracion' => isset($_POST['filtro_duracion']) ? sanitize_text_field($_POST['filtro_duracion']) : '',
        'accesibilidad' => isset($_POST['filtro_accesibilidad']) ? sanitize_text_field($_POST['filtro_accesibilidad']) : ''
    );
    
    // Contar filtros activos
    $filtros_activos = array_filter($filtros, function($valor) {
        return !empty($valor);
    });
    
    if (!empty($filtros_activos)) {
        error_log('🎛️ Filtros recibidos: ' . print_r($filtros, true));
    }
    
    if (!empty($chip)) {
        $query = $chip;
    }
    
    // ════════════════════════════════════════════════════════
    // BÚSQUEDA MULTI-PALABRA: "spa relax" → busca "spa" O "relax"
    // ════════════════════════════════════════════════════════
    $palabras_busqueda = array();
    if (!empty($query)) {
        // Dividir query en palabras (espacios, comas, etc)
        $palabras = preg_split('/[\s,]+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);
        
        // Filtrar palabras muy cortas (menos de 2 caracteres)
        $palabras_busqueda = array_filter($palabras, function($palabra) {
            return strlen($palabra) >= 2;
        });
        
        error_log('🔍 BÚSQUEDA MULTI-PALABRA: ' . implode(' | ', $palabras_busqueda));
    }
    
    $resultados = array();
    $ids_encontrados = array();  // Para evitar duplicados globales
    
    // ============================================
    // BÚSQUEDA POR FECHA (caso especial)
    // ============================================
    
    if ($tab === 'fecha' && !empty($query)) {
        $resultados = buscador_buscar_por_fecha($query);
        
        wp_send_json_success(array(
            'success' => true,
            'resultados' => $resultados,
            'total' => count($resultados),
            'pagina_actual' => 1,
            'total_paginas' => 1,
            'query' => $query,
            'tab' => $tab
        ));
        return;
    }
    
    // ============================================
    // BÚSQUEDA GENERAL - DOS QUERIES SEPARADAS (título OR meta)
    // ============================================
    
    if (!empty($query)) {
        $query_original = $query;
        
        // ============================================
        // FUZZY SEARCH TEMPORALMENTE DESACTIVADO
        // ============================================
        // $query_corregida = corregir_palabra_fuzzy($query, 0.75);  // Umbral 75%
        // error_log("🔍 Búsqueda corregida: '$query_corregida'");
        // $query_busqueda = $query_corregida;
        
        // USAR SOLO EL QUERY ORIGINAL (sin fuzzy)
        $query_busqueda = $query_original;
        
        error_log("🔍 BÚSQUEDA SIMPLE (sin fuzzy): '$query_original'");
        
        // Definir post types según tab
        $post_types = buscador_get_post_types_by_tab($tab);
        
        // LOGGING DIAGNÓSTICO
        error_log('🔍 Tab: ' . $tab);
        error_log('📋 Post types a buscar: ' . implode(', ', $post_types));
        error_log('🔎 Query original: "' . $query_original . '"');
        error_log('🔎 Query para búsqueda: "' . $query_busqueda . '"');
        
        // PROCESAR CADA POST TYPE
        foreach ($post_types as $post_type) {
            
            error_log("  ➡️ Buscando en post_type: '$post_type'");
            
            // ============================================
            // QUERY 1: Buscar en TÍTULO y CONTENIDO
            // ============================================
            
            // Determinar si usar búsqueda de palabra completa (para evitar "spa" → "España")
            $usar_palabra_completa = (strlen($query_busqueda) <= 4);
            $ids_por_titulo = array();
            
            if ($usar_palabra_completa) {
                error_log("    🎯 Búsqueda de palabra COMPLETA: '$query_busqueda'");
                
                // Usar SQL directo con REGEXP para límites de palabra
                global $wpdb;
                
                // Construir condiciones OR para múltiples palabras
                $regexp_conditions = array();
                $palabras_a_buscar = !empty($palabras_busqueda) ? $palabras_busqueda : array($query_busqueda);
                
                foreach ($palabras_a_buscar as $palabra) {
                    $regexp_pattern = '[[:<:]]' . $wpdb->esc_like($palabra) . '[[:>:]]';
                    $regexp_conditions[] = $wpdb->prepare("post_title REGEXP %s OR post_content REGEXP %s", $regexp_pattern, $regexp_pattern);
                }
                
                $where_clause = '(' . implode(') OR (', $regexp_conditions) . ')';
                
                $ids_por_titulo = $wpdb->get_col($wpdb->prepare("
                    SELECT ID 
                    FROM {$wpdb->posts}
                    WHERE post_type = %s
                    AND post_status = 'publish'
                    AND (" . $where_clause . ")
                ", $post_type));
                
                error_log("    📝 IDs por título (palabra completa multi-palabra): " . count($ids_por_titulo) . " (" . implode(', ', array_slice($ids_por_titulo, 0, 10)) . ")");
                
            } else {
                // Búsqueda normal con 's' para palabras largas
                $args_titulo = array(
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    's' => $query_busqueda,
                    'fields' => 'ids'
                );
                
                $query_titulo = new WP_Query($args_titulo);
                $ids_por_titulo = $query_titulo->posts;
                
                error_log("    📝 IDs por título (búsqueda normal): " . count($ids_por_titulo) . " (" . implode(', ', $ids_por_titulo) . ")");
            }
            
            // ============================================
            // QUERY 2: Buscar en CAMPOS ACF
            // ============================================
            
            $ids_por_meta = array();
            
            // Para palabras cortas, usar REGEXP también en campos ACF
            if ($usar_palabra_completa) {
                error_log("    🔧 Búsqueda de palabra COMPLETA en campos ACF");
                
                // Determinar campos ACF según post_type
                $campos_acf = array();
                
                switch ($post_type) {
                    case 'dia':
                        $campos_acf = array('sitio_dia', 'pais_dia', 'hito_dia');
                        break;
                    case 'tramo':
                        $campos_acf = array('sitio_inicio_tramo', 'sitio_fin_tramo', 'pais_inicio_tramo', 'pais_fin_tramo', 'descripcion_tramo');
                        break;
                    case 'actividad':
                        $campos_acf = array('descripcion_actividad', 'sitio_actividad');
                        break;
                    case 'hospedaje':
                        $campos_acf = array('nombre_hospedaje', 'sitio_hospedaje', 'descripcion_hospedaje');
                        break;
                    case 'restaurante':
                        $campos_acf = array('nombre_restaurante', 'sitio_restaurante');
                        break;
                    case 'capitulo':
                        $campos_acf = array('nombre_capitulo', 'descripcion_capitulo');
                        break;
                }
                
                if (!empty($campos_acf)) {
                    // Construir condiciones SQL para cada campo y cada palabra
                    $condiciones = array();
                    $palabras_a_buscar = !empty($palabras_busqueda) ? $palabras_busqueda : array($query_busqueda);
                    
                    foreach ($campos_acf as $campo) {
                        foreach ($palabras_a_buscar as $palabra) {
                            $condiciones[] = $wpdb->prepare(
                                "(pm.meta_key = %s AND pm.meta_value REGEXP %s)",
                                $campo,
                                '[[:<:]]' . $wpdb->esc_like($palabra) . '[[:>:]]'
                            );
                        }
                    }
                    
                    $sql = "
                        SELECT DISTINCT p.ID
                        FROM {$wpdb->posts} p
                        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                        WHERE p.post_type = %s
                        AND p.post_status = 'publish'
                        AND (" . implode(' OR ', $condiciones) . ")
                    ";
                    
                    $ids_por_meta = $wpdb->get_col($wpdb->prepare($sql, $post_type));
                }
                
                error_log("    🔧 IDs por meta (palabra completa multi-palabra): " . count($ids_por_meta) . " (" . implode(', ', array_slice($ids_por_meta, 0, 10)) . ")");
                
            } else {
                // Búsqueda normal con LIKE para palabras largas
                $meta_query = array('relation' => 'OR');
                
                // Definir campos según post_type
                switch ($post_type) {
                    case 'dia':
                        $meta_query[] = array('key' => 'sitio_dia', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'pais_dia', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'hito_dia', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        break;
                        
                    case 'tramo':
                        $meta_query[] = array('key' => 'sitio_inicio_tramo', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'sitio_fin_tramo', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'pais_inicio_tramo', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'pais_fin_tramo', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'descripcion_tramo', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        break;
                        
                    case 'actividad':
                        $meta_query[] = array('key' => 'descripcion_actividad', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'sitio_actividad', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        break;
                        
                    case 'hospedaje':
                        $meta_query[] = array('key' => 'nombre_hospedaje', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'sitio_hospedaje', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'descripcion_hospedaje', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        break;
                        
                    case 'restaurante':
                        $meta_query[] = array('key' => 'nombre_restaurante', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'sitio_restaurante', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        break;
                        
                    case 'capitulo':
                        $meta_query[] = array('key' => 'nombre_capitulo', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        $meta_query[] = array('key' => 'descripcion_capitulo', 'value' => $query_busqueda, 'compare' => 'LIKE');
                        break;
                }
                
                $args_meta = array(
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => $meta_query,
                    'fields' => 'ids'
                );
                
                $query_meta = new WP_Query($args_meta);
                $ids_por_meta = $query_meta->posts;
                
                error_log("    🔧 IDs por meta (búsqueda normal): " . count($ids_por_meta) . " (" . implode(', ', $ids_por_meta) . ")");
            }
            
            // ============================================
            // COMBINAR IDs (UNIÓN - sin duplicados)
            // ============================================
            
            $ids_combinados = array_unique(array_merge($ids_por_titulo, $ids_por_meta));
            
            error_log("    ✅ IDs combinados: " . count($ids_combinados) . " (" . implode(', ', $ids_combinados) . ")");
            
            // ============================================
            // OBTENER POSTS COMPLETOS
            // ============================================
            
            if (!empty($ids_combinados)) {
                $args_final = array(
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'post__in' => $ids_combinados,
                    'posts_per_page' => -1,
                    'orderby' => 'post__in'  // Mantener orden de IDs
                );
                
                $query_final = new WP_Query($args_final);
                
                if ($query_final->have_posts()) {
                    while ($query_final->have_posts()) {
                        $query_final->the_post();
                        $post_id = get_the_ID();
                        
                        // Evitar duplicados globales
                        if (!in_array($post_id, $ids_encontrados)) {
                            $ids_encontrados[] = $post_id;
                            
                            // Formatear resultado
                            $resultado = buscador_formatear_resultado(get_post());
                            $resultado['id'] = $post_id;
                            
                            // CALCULAR RELEVANCIA
                            $relevancia_data = calcular_relevancia_resultado(get_post(), $query_original);
                            $resultado['score'] = $relevancia_data['score'];
                            $resultado['relevancia'] = $relevancia_data['relevancia'];
                            
                            $resultados[] = $resultado;
                        }
                    }
                    wp_reset_postdata();
                }
            }
        }
        
        // ════════════════════════════════════════
        // FILTRAR POR RELEVANCIA
        // ════════════════════════════════════════
        
        // Contar cuántos resultados hay por nivel de relevancia
        $count_alta = 0;
        $count_media = 0;
        $count_baja = 0;
        
        foreach ($resultados as $resultado) {
            $relevancia = isset($resultado['relevancia']) ? $resultado['relevancia'] : 'baja';
            
            if ($relevancia === 'alta') {
                $count_alta++;
            } elseif ($relevancia === 'media') {
                $count_media++;
            } else {
                $count_baja++;
            }
        }
        
        // Si hay resultados de alta o media relevancia, filtrar los de baja
        if ($count_alta > 0 || $count_media > 0) {
            $resultados_filtrados = array();
            
            foreach ($resultados as $resultado) {
                $relevancia = isset($resultado['relevancia']) ? $resultado['relevancia'] : 'baja';
                
                // Solo incluir alta y media
                if ($relevancia === 'alta' || $relevancia === 'media') {
                    $resultados_filtrados[] = $resultado;
                }
            }
            
            $resultados = $resultados_filtrados;
            
            error_log("🔍 Filtrado por relevancia: $count_alta alta, $count_media media (excluidos $count_baja baja)");
        } else {
            error_log("🔍 Sin filtrado por relevancia: solo hay $count_baja resultados de baja relevancia");
        }
        
        // ════════════════════════════════════════
        // APLICAR FILTROS AVANZADOS
        // ════════════════════════════════════════
        
        if (!empty($filtros_activos)) {
            $total_sin_filtrar = count($resultados);
            $resultados = aplicar_filtros_avanzados($resultados, $filtros);
            $total_filtrado = count($resultados);
            
            error_log("🎛️ Filtrado avanzado: $total_sin_filtrar → $total_filtrado resultados");
        }
        
        // Ordenar resultados por tipo
        $resultados = buscador_ordenar_resultados_por_tipo($resultados);
        
        // LOGGING FINAL
        error_log('✨ TOTAL RESULTADOS FINALES: ' . count($resultados));
        if (count($resultados) > 0) {
            error_log('📋 Títulos encontrados:');
            foreach ($resultados as $r) {
                error_log('   - ' . $r['titulo'] . ' (' . $r['tipo'] . ')');
            }
        }
    }
    
    // ════════════════════════════════════════
    // CALCULAR TOTAL FINAL (después de TODOS los filtros)
    // ════════════════════════════════════════
    $total_final = count($resultados);
    
    error_log("📊 TOTAL ENVIADO AL FRONTEND: $total_final resultados");
    
    // Devolver respuesta
    wp_send_json_success(array(
        'success' => true,
        'resultados' => $resultados,
        'total' => $total_final,
        'pagina_actual' => 1,
        'total_paginas' => 1,
        'query' => $query,
        'tab' => $tab
    ));
}

/**
 * ENDPOINT AJAX: Obtener sugerencias de autocompletado
 */
function buscador_sugerencias_ajax_handler() {
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    if (strlen($query) < 2) {
        wp_send_json_success(array('sugerencias' => array()));
        return;
    }
    
    $query_normalizado = normalizar_para_busqueda($query);
    $sugerencias = array();
    $ids_vistos = array();
    
    // BÚSQUEDA DE LUGARES (sitios únicos)
    $lugares_unicos = array();
    $campos_lugar = array(
        'dia' => 'sitio_dia',
        'tramo' => 'sitio_inicio_tramo',
        'actividad' => 'sitio_actividad',
        'hospedaje' => 'sitio_hospedaje',
        'restaurante' => 'sitio_restaurante'
    );
    
    foreach ($campos_lugar as $post_type => $campo) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => 50,
            'meta_query' => array(
                array(
                    'key' => $campo,
                    'value' => $query,
                    'compare' => 'LIKE'
                )
            )
        ));
        
        foreach ($posts as $post) {
            $lugar = get_field($campo, $post->ID);
            if ($lugar && !isset($lugares_unicos[strtolower($lugar)])) {
                $lugares_unicos[strtolower($lugar)] = array(
                    'texto' => $lugar,
                    'tipo' => 'Lugar',
                    'icono' => '📍',
                    'tipo_clase' => 'lugar'
                );
            }
        }
    }
    
    // Añadir lugares a sugerencias (máximo 3)
    $sugerencias = array_merge($sugerencias, array_slice(array_values($lugares_unicos), 0, 3));
    
    // BÚSQUEDA DE TRAMOS (por título)
    $tramos = get_posts(array(
        'post_type' => 'tramo',
        'posts_per_page' => 2,
        's' => $query
    ));
    
    foreach ($tramos as $tramo) {
        $sugerencias[] = array(
            'texto' => get_the_title($tramo->ID),
            'tipo' => 'Tramo',
            'icono' => '🗺️',
            'tipo_clase' => 'tramo',
            'post_id' => $tramo->ID
        );
    }
    
    // BÚSQUEDA DE ACTIVIDADES (por título o descripción)
    $actividades = get_posts(array(
        'post_type' => 'actividad',
        'posts_per_page' => 2,
        's' => $query
    ));
    
    foreach ($actividades as $actividad) {
        $sugerencias[] = array(
            'texto' => get_the_title($actividad->ID),
            'tipo' => 'Activ.',
            'icono' => '🎯',
            'tipo_clase' => 'actividad',
            'post_id' => $actividad->ID
        );
    }
    
    // Limitar a 5 sugerencias totales
    $sugerencias = array_slice($sugerencias, 0, 5);
    
    wp_send_json_success(array('sugerencias' => $sugerencias));
}

// Registrar acciones AJAX
add_action('wp_ajax_buscador_global', 'buscador_global_ajax_handler');
add_action('wp_ajax_nopriv_buscador_global', 'buscador_global_ajax_handler');
add_action('wp_ajax_buscador_sugerencias', 'buscador_sugerencias_ajax_handler');
add_action('wp_ajax_nopriv_buscador_sugerencias', 'buscador_sugerencias_ajax_handler');

/**
 * ════════════════════════════════════════════════════════
 * ENDPOINT REST API PARA SUGERENCIAS DE BÚSQUEDA (OPTIMIZADO)
 * ════════════════════════════════════════════════════════
 */

add_action('rest_api_init', function() {
    register_rest_route('viaje/v1', '/sugerencias', array(
        'methods' => 'GET',
        'callback' => 'obtener_sugerencias_busqueda_rest',
        'permission_callback' => '__return_true',
        'args' => array(
            'q' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
});

/**
 * Obtener sugerencias de búsqueda optimizadas (REST API)
 */
function obtener_sugerencias_busqueda_rest($request) {
    $query = $request->get_param('q');
    
    // Mínimo 2 caracteres
    if (strlen($query) < 2) {
        return array();
    }
    
    $sugerencias = array();
    
    global $wpdb;
    
    // Determinar si usar búsqueda de palabra completa
    $usar_palabra_completa = (strlen($query) <= 4);
    
    // ════════════════════════════════════════
    // PRIORIDAD 1: TRAMOS (10/10)
    // ════════════════════════════════════════
    
    if ($usar_palabra_completa) {
        // Búsqueda con REGEXP para palabra completa
        $tramo_ids = $wpdb->get_col($wpdb->prepare("
            SELECT ID 
            FROM {$wpdb->posts}
            WHERE post_type = 'tramo'
            AND post_status = 'publish'
            AND post_title REGEXP %s
            LIMIT 3
        ", '[[:<:]]' . $query . '[[:>:]]'));
        
        $tramos = array();
        foreach ($tramo_ids as $tramo_id) {
            $tramos[] = get_post($tramo_id);
        }
    } else {
        // Búsqueda normal
        $tramos = get_posts(array(
            'post_type' => 'tramo',
            'posts_per_page' => 3,
            's' => $query,
            'post_status' => 'publish',
            'orderby' => 'relevance'
        ));
    }
    
    foreach ($tramos as $tramo) {
        $sugerencias[] = array(
            'texto' => get_the_title($tramo),
            'tipo' => 'tramo',
            'tipo_label' => 'Tramo',
            'icono' => '🗺️',
            'prioridad' => 10,
            'url' => get_permalink($tramo)
        );
    }
    
    // ════════════════════════════════════════
    // PRIORIDAD 2: HITOS DEL DÍA (8/10) - Lo más destacado
    // ════════════════════════════════════════
    
    if ($usar_palabra_completa) {
        // Búsqueda con REGEXP para palabra completa
        $hitos = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, pm.meta_value as hito
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'dia'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'hito_dia'
            AND pm.meta_value REGEXP %s
            AND pm.meta_value != ''
            ORDER BY p.post_title ASC
            LIMIT 5
        ", '[[:<:]]' . $query . '[[:>:]]'));
    } else {
        // Búsqueda normal con LIKE
        $hitos = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, pm.meta_value as hito
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'dia'
            AND p.post_status = 'publish'
            AND pm.meta_key = 'hito_dia'
            AND pm.meta_value LIKE %s
            AND pm.meta_value != ''
            ORDER BY p.post_title ASC
            LIMIT 5
        ", '%' . $wpdb->esc_like($query) . '%'));
    }
    
    foreach ($hitos as $hito) {
        $sitio = get_field('sitio_dia', $hito->ID);
        $dia_numero = get_field('dia', $hito->ID);
        
        $subtexto_partes = array();
        if ($dia_numero) $subtexto_partes[] = "Día $dia_numero";
        if ($sitio) $subtexto_partes[] = "📍 $sitio";
        
        $sugerencias[] = array(
            'texto' => $hito->hito,
            'subtexto' => implode(' • ', $subtexto_partes),
            'tipo' => 'hito',
            'tipo_label' => 'Hito',
            'icono' => '🏆',
            'prioridad' => 8,
            'post_id' => $hito->ID,
            'url' => get_permalink($hito->ID)
        );
    }
    
    // ════════════════════════════════════════
    // PRIORIDAD 3: LUGARES ÚNICOS MÁS FRECUENTES (7/10)
    // ════════════════════════════════════════
    
    if ($usar_palabra_completa) {
        // Búsqueda con REGEXP para palabra completa
        $lugares = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT meta_value as texto, COUNT(*) as frecuencia
            FROM {$wpdb->postmeta}
            WHERE meta_key IN ('sitio_dia', 'sitio_inicio_tramo', 'sitio_fin_tramo', 'sitio_hospedaje', 'sitio_restaurante', 'sitio_actividad')
            AND meta_value REGEXP %s
            AND meta_value != ''
            GROUP BY meta_value
            ORDER BY frecuencia DESC, meta_value ASC
            LIMIT 8
        ", '[[:<:]]' . $query . '[[:>:]]'));
    } else {
        // Búsqueda normal con LIKE
        $lugares = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT meta_value as texto, COUNT(*) as frecuencia
            FROM {$wpdb->postmeta}
            WHERE meta_key IN ('sitio_dia', 'sitio_inicio_tramo', 'sitio_fin_tramo', 'sitio_hospedaje', 'sitio_restaurante', 'sitio_actividad')
            AND meta_value LIKE %s
            AND meta_value != ''
            GROUP BY meta_value
            ORDER BY frecuencia DESC, meta_value ASC
            LIMIT 8
        ", '%' . $wpdb->esc_like($query) . '%'));
    }
    
    error_log('🔍 Sugerencias de lugares para "' . $query . '": ' . count($lugares) . ' encontrados');
    foreach ($lugares as $lugar) {
        error_log("  - {$lugar->texto} (frecuencia: {$lugar->frecuencia})");
        $sugerencias[] = array(
            'texto' => $lugar->texto,
            'tipo' => 'lugar',
            'tipo_label' => 'Lugar',
            'icono' => '📍',
            'prioridad' => 7,
            'frecuencia' => (int)$lugar->frecuencia
        );
    }
    
    // ════════════════════════════════════════
    // PRIORIDAD 4: PAÍSES (6/10)
    // ════════════════════════════════════════
    
    if ($usar_palabra_completa) {
        // Búsqueda con REGEXP para palabra completa
        $paises = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT meta_value as texto, COUNT(*) as frecuencia
            FROM {$wpdb->postmeta}
            WHERE meta_key IN ('pais_dia', 'pais_inicio_tramo', 'pais_fin_tramo')
            AND meta_value REGEXP %s
            AND meta_value != ''
            GROUP BY meta_value
            ORDER BY frecuencia DESC
            LIMIT 3
        ", '[[:<:]]' . $query . '[[:>:]]'));
    } else {
        // Búsqueda normal con LIKE
        $paises = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT meta_value as texto, COUNT(*) as frecuencia
            FROM {$wpdb->postmeta}
            WHERE meta_key IN ('pais_dia', 'pais_inicio_tramo', 'pais_fin_tramo')
            AND meta_value LIKE %s
            AND meta_value != ''
            GROUP BY meta_value
            ORDER BY frecuencia DESC
            LIMIT 3
        ", '%' . $wpdb->esc_like($query) . '%'));
    }
    
    foreach ($paises as $pais) {
        $sugerencias[] = array(
            'texto' => $pais->texto,
            'tipo' => 'pais',
            'tipo_label' => 'País',
            'icono' => '🌍',
            'prioridad' => 6,
            'frecuencia' => (int)$pais->frecuencia
        );
    }
    
    // ════════════════════════════════════════
    // PRIORIDAD 5: ACTIVIDADES (5/10)
    // ════════════════════════════════════════
    
    if ($usar_palabra_completa) {
        // Búsqueda con REGEXP para palabra completa
        $actividad_ids = $wpdb->get_col($wpdb->prepare("
            SELECT ID 
            FROM {$wpdb->posts}
            WHERE post_type = 'actividad'
            AND post_status = 'publish'
            AND post_title REGEXP %s
            LIMIT 2
        ", '[[:<:]]' . $query . '[[:>:]]'));
        
        $actividades = array();
        foreach ($actividad_ids as $actividad_id) {
            $actividades[] = get_post($actividad_id);
        }
    } else {
        // Búsqueda normal
        $actividades = get_posts(array(
            'post_type' => 'actividad',
            'posts_per_page' => 2,
            's' => $query,
            'post_status' => 'publish',
            'orderby' => 'date'
        ));
    }
    
    foreach ($actividades as $actividad) {
        $sugerencias[] = array(
            'texto' => get_the_title($actividad),
            'tipo' => 'actividad',
            'tipo_label' => 'Activ.',
            'icono' => '🎯',
            'prioridad' => 5,
            'url' => get_permalink($actividad)
        );
    }
    
    // ════════════════════════════════════════
    // ELIMINAR DUPLICADOS
    // ════════════════════════════════════════
    
    $sugerencias_unicas = array();
    $textos_vistos = array();
    
    foreach ($sugerencias as $sug) {
        $texto_lower = strtolower($sug['texto']);
        if (!in_array($texto_lower, $textos_vistos)) {
            $sugerencias_unicas[] = $sug;
            $textos_vistos[] = $texto_lower;
        }
    }
    
    // Limitar a 8 sugerencias
    return array_slice($sugerencias_unicas, 0, 8);
}

/**
 * Búsqueda especializada por fecha
 * Devuelve TODOS los contenidos relacionados con una fecha específica
 */
function buscador_buscar_por_fecha($query) {
    $resultados = array();
    
    // Convertir DD/MM/YYYY → YYYYMMDD
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $query, $matches)) {
        return $resultados;
    }
    
    $fecha_ymd = $matches[3] . $matches[2] . $matches[1]; // 20260420
    
    // PASO 1: Buscar el DÍA con esa fecha exacta
    $dia_query = new WP_Query(array(
        'post_type' => 'dia',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'fecha_dia',
                'value' => $fecha_ymd,
                'compare' => '='
            )
        )
    ));
    
    $id_dia_encontrado = null;
    
    if ($dia_query->have_posts()) {
        while ($dia_query->have_posts()) {
            $dia_query->the_post();
            $id_dia_encontrado = get_field('id_dia');
            $resultados[] = buscador_formatear_resultado(get_post());
        }
        wp_reset_postdata();
    }
    
    // PASO 2: Si encontramos el día, buscar contenidos relacionados
    if ($id_dia_encontrado) {
        // Buscar ACTIVIDADES con ese id_dia
        $actividades_query = new WP_Query(array(
            'post_type' => 'actividad',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'id_dia',
                    'value' => $id_dia_encontrado,
                    'compare' => '='
                )
            )
        ));
        
        if ($actividades_query->have_posts()) {
            while ($actividades_query->have_posts()) {
                $actividades_query->the_post();
                $resultados[] = buscador_formatear_resultado(get_post());
            }
            wp_reset_postdata();
        }
        
        // Buscar HOSPEDAJES con ese id_dia
        $hospedajes_query = new WP_Query(array(
            'post_type' => 'hospedaje',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'id_dia',
                    'value' => $id_dia_encontrado,
                    'compare' => '='
                )
            )
        ));
        
        if ($hospedajes_query->have_posts()) {
            while ($hospedajes_query->have_posts()) {
                $hospedajes_query->the_post();
                $resultados[] = buscador_formatear_resultado(get_post());
            }
            wp_reset_postdata();
        }
        
        // Buscar RESTAURANTES con ese id_dia
        $restaurantes_query = new WP_Query(array(
            'post_type' => 'restaurante',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'id_dia',
                    'value' => $id_dia_encontrado,
                    'compare' => '='
                )
            )
        ));
        
        if ($restaurantes_query->have_posts()) {
            while ($restaurantes_query->have_posts()) {
                $restaurantes_query->the_post();
                $resultados[] = buscador_formatear_resultado(get_post());
            }
            wp_reset_postdata();
        }
    }
    
    // PASO 3: Buscar el TRAMO que contenga esa fecha
    $tramos_query = new WP_Query(array(
        'post_type' => 'tramo',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'fecha_inicio_tramo',
                'value' => $fecha_ymd,
                'compare' => '<=',
                'type' => 'NUMERIC'
            ),
            array(
                'key' => 'fecha_fin_tramo',
                'value' => $fecha_ymd,
                'compare' => '>=',
                'type' => 'NUMERIC'
            )
        )
    ));
    
    if ($tramos_query->have_posts()) {
        while ($tramos_query->have_posts()) {
            $tramos_query->the_post();
            $resultados[] = buscador_formatear_resultado(get_post());
        }
        wp_reset_postdata();
    }
    
    // Ordenar resultados según jerarquía de navegación
    $resultados = buscador_ordenar_resultados_por_tipo($resultados);
    
    return $resultados;
}

/**
 * ════════════════════════════════════════════════════════
 * APLICAR FILTROS AVANZADOS A RESULTADOS
 * ════════════════════════════════════════════════════════
 */
function aplicar_filtros_avanzados($resultados, $filtros) {
    $resultados_filtrados = array();
    
    foreach ($resultados as $resultado) {
        $cumple_filtros = true;
        
        // FILTRO: PRESUPUESTO
        if (!empty($filtros['presupuesto'])) {
            $precio_actividad = get_field('precio_actividad', $resultado['id']);
            $precio_hospedaje = get_field('precio_hospedaje', $resultado['id']);
            $precio = $precio_actividad ?: $precio_hospedaje;
            
            switch ($filtros['presupuesto']) {
                case 'gratis':
                    if (!empty($precio) && $precio > 0) {
                        $cumple_filtros = false;
                    }
                    break;
                    
                case 'economico':
                    if (empty($precio) || $precio < 0 || $precio > 50) {
                        $cumple_filtros = false;
                    }
                    break;
                    
                case 'moderado':
                    if (empty($precio) || $precio < 50 || $precio > 150) {
                        $cumple_filtros = false;
                    }
                    break;
                    
                case 'premium':
                    if (empty($precio) || $precio < 150) {
                        $cumple_filtros = false;
                    }
                    break;
            }
        }
        
        // FILTRO: TIPO DE ACTIVIDAD
        if (!empty($filtros['tipo'])) {
            $tipo_actividad = get_field('tipo_actividad', $resultado['id']);
            
            if (empty($tipo_actividad) || strtolower($tipo_actividad) !== strtolower($filtros['tipo'])) {
                $cumple_filtros = false;
            }
        }
        
        // FILTRO: DURACIÓN
        if (!empty($filtros['duracion'])) {
            $duracion = get_field('duracion_actividad', $resultado['id']);
            
            if (!empty($duracion)) {
                $duracion_horas = floatval($duracion);
                
                switch ($filtros['duracion']) {
                    case 'corta':
                        if ($duracion_horas >= 2) {
                            $cumple_filtros = false;
                        }
                        break;
                        
                    case 'media':
                        if ($duracion_horas < 2 || $duracion_horas >= 4) {
                            $cumple_filtros = false;
                        }
                        break;
                        
                    case 'larga':
                        if ($duracion_horas < 4 || $duracion_horas >= 8) {
                            $cumple_filtros = false;
                        }
                        break;
                        
                    case 'dia-completo':
                        if ($duracion_horas < 8 || $duracion_horas > 12) {
                            $cumple_filtros = false;
                        }
                        break;
                        
                    case 'varios-dias':
                        if ($duracion_horas <= 12) {
                            $cumple_filtros = false;
                        }
                        break;
                }
            } else {
                // Si no tiene duración definida, no cumple el filtro
                $cumple_filtros = false;
            }
        }
        
        // FILTRO: ACCESIBILIDAD
        if (!empty($filtros['accesibilidad'])) {
            $accesibilidad = get_field('accesibilidad_actividad', $resultado['id']);
            
            if (empty($accesibilidad) || strtolower($accesibilidad) !== strtolower($filtros['accesibilidad'])) {
                $cumple_filtros = false;
            }
        }
        
        // Agregar solo si cumple todos los filtros
        if ($cumple_filtros) {
            $resultados_filtrados[] = $resultado;
        }
    }
    
    return $resultados_filtrados;
}

/**
 * Ordenar resultados según la jerarquía de navegación
 * Jerarquía: Tramos → Días → Actividades → Hospedajes → Restaurantes
 */
function buscador_ordenar_resultados_por_tipo($resultados) {
    // Definir prioridades CORRECTAS según jerarquía de navegación
    $prioridades = array(
        'tramo' => 1,        // Primero: contexto macro
        'capitulo' => 1,     // Mismo nivel que tramo
        'dia' => 2,          // Segundo: jornada específica
        'actividad' => 3,    // Tercero: lo más importante (qué hiciste)
        'hospedaje' => 4,    // Cuarto: dónde dormiste
        'restaurante' => 5   // Quinto: dónde comiste
    );
    
    // Ordenar usando usort
    usort($resultados, function($a, $b) use ($prioridades) {
        $prioridad_a = isset($prioridades[$a['tipo']]) ? $prioridades[$a['tipo']] : 999;
        $prioridad_b = isset($prioridades[$b['tipo']]) ? $prioridades[$b['tipo']] : 999;
        
        // Primero ordenar por tipo
        if ($prioridad_a !== $prioridad_b) {
            return $prioridad_a - $prioridad_b;
        }
        
        // Si son del mismo tipo, ordenar por relevancia adicional
        
        // RESTAURANTES: ordenar por tipo de comida (Desayuno → Almuerzo → Cena)
        if ($a['tipo'] === 'restaurante' && $b['tipo'] === 'restaurante') {
            $orden_comidas = array('desayuno' => 1, 'almuerzo' => 2, 'comida' => 2, 'cena' => 3);
            $tipo_a = strtolower(isset($a['descripcion']) ? $a['descripcion'] : '');
            $tipo_b = strtolower(isset($b['descripcion']) ? $b['descripcion'] : '');
            
            $orden_a = 999;
            $orden_b = 999;
            
            foreach ($orden_comidas as $comida => $valor) {
                if (stripos($tipo_a, $comida) !== false) $orden_a = $valor;
                if (stripos($tipo_b, $comida) !== false) $orden_b = $valor;
            }
            
            if ($orden_a !== $orden_b) {
                return $orden_a - $orden_b;
            }
        }
        
        // ACTIVIDADES: ordenar por campo orden_dia si existe
        if ($a['tipo'] === 'actividad' && $b['tipo'] === 'actividad') {
            $orden_a = isset($a['orden']) ? $a['orden'] : 999;
            $orden_b = isset($b['orden']) ? $b['orden'] : 999;
            
            if ($orden_a !== $orden_b) {
                return $orden_a - $orden_b;
            }
        }
        
        // Si todo lo demás es igual, ordenar alfabéticamente
        return strcmp($a['titulo'], $b['titulo']);
    });
    
    return $resultados;
}

/**
 * Determinar qué post types buscar según el tab
 */
function buscador_get_post_types_by_tab($tab) {
    switch ($tab) {
        case 'actividad':
            return array('actividad');
        
        case 'lugar':
            return array('dia', 'tramo', 'hospedaje');
        
        case 'fecha':
            return array('dia');
        
        case 'todo':
        default:
            return array('dia', 'tramo', 'actividad', 'hospedaje', 'restaurante', 'capitulo');
    }
}

/**
 * Obtener campos ACF a buscar según los post types
 */
function buscador_get_campos_busqueda($post_types) {
    $campos = array();
    
    foreach ($post_types as $post_type) {
        switch ($post_type) {
            case 'dia':
                $campos = array_merge($campos, array(
                    'sitio_dia',
                    'pais_dia',
                    'hito_dia',
                    'relato_dia',
                    'fecha_dia'
                ));
                break;
            
            case 'tramo':
                $campos = array_merge($campos, array(
                    'nombre_tramo',
                    'descripcion_tramo',
                    'sitio_inicio_tramo',
                    'sitio_fin_tramo',
                    'pais_inicio_tramo',
                    'pais_fin_tramo'
                ));
                break;
            
            case 'actividad':
                $campos = array_merge($campos, array(
                    'titulo_actividad',
                    'descripcion_actividad',
                    'sitio_actividad'
                ));
                break;
            
            case 'hospedaje':
                $campos = array_merge($campos, array(
                    'nombre_hospedaje',
                    'sitio_hospedaje',
                    'descripcion_hospedaje'
                ));
                break;
            
            case 'restaurante':
                $campos = array_merge($campos, array(
                    'nombre_restaurante',
                    'sitio_restaurante',
                    'tipo_comida'
                ));
                break;
            
            case 'capitulo':
                $campos = array_merge($campos, array(
                    'nombre_capitulo',
                    'descripcion_capitulo'
                ));
                break;
        }
    }
    
    return array_unique($campos);
}

/**
 * Formatear un post como resultado de búsqueda
 */
function buscador_formatear_resultado($post) {
    $post_type = get_post_type($post);
    $resultado = array(
        'tipo' => $post_type,
        'titulo' => get_the_title($post),
        'url' => get_permalink($post),
        'descripcion' => '',
        'fecha' => '',
        'fecha_raw' => '', // Fecha en formato Ymd para ordenamiento
        'dia_numero' => '',
        'lugar' => '',
        'badges' => array(),
        'icono' => buscador_get_icono_tipo($post_type)
    );
    
    // Formatear según el tipo de post
    switch ($post_type) {
        case 'dia':
            $resultado['descripcion'] = get_field('hito_dia', $post->ID) ?: get_field('relato_dia', $post->ID);
            $fecha_raw = get_field('fecha_dia', $post->ID);
            $resultado['fecha_raw'] = $fecha_raw; // Guardar formato Ymd para ordenamiento
            $resultado['fecha'] = buscador_formatear_fecha_legible($fecha_raw);
            $resultado['dia_numero'] = get_field('dia', $post->ID);
            $resultado['lugar'] = get_field('sitio_dia', $post->ID) . ', ' . get_field('pais_dia', $post->ID);
            $resultado['badges'][] = 'Día ' . get_field('dia', $post->ID);
            // Coordenadas
            $resultado['latitud'] = get_field('latitud', $post->ID);
            $resultado['longitud'] = get_field('longitud', $post->ID);
            // DEBUG: Log de coordenadas
            error_log('DEBUG DIA #' . $post->ID . ': latitud=' . $resultado['latitud'] . ', longitud=' . $resultado['longitud']);
            break;
        
        case 'tramo':
            $resultado['descripcion'] = get_field('descripcion_tramo', $post->ID);
            $resultado['lugar'] = get_field('sitio_inicio_tramo', $post->ID) . ' → ' . get_field('sitio_fin_tramo', $post->ID);
            $resultado['badges'][] = 'Tramo';
            // Coordenadas (usar punto de inicio)
            $resultado['latitud'] = get_field('latitud_inicio_tramo', $post->ID);
            $resultado['longitud'] = get_field('longitud_inicio_tramo', $post->ID);
            break;
        
        case 'actividad':
            $resultado['descripcion'] = get_field('descripcion_actividad', $post->ID);
            $id_dia = get_field('id_dia', $post->ID);
            if ($id_dia) {
                $dia_post = get_posts(array(
                    'post_type' => 'dia',
                    'meta_key' => 'id_dia',
                    'meta_value' => $id_dia,
                    'posts_per_page' => 1
                ));
                if (!empty($dia_post)) {
                    // ✅ USAR URL DEL DÍA en lugar de la actividad
                    $resultado['url'] = get_permalink($dia_post[0]->ID);
                    $resultado['lugar'] = get_field('sitio_dia', $dia_post[0]->ID);
                    $fecha_raw = get_field('fecha_dia', $dia_post[0]->ID);
                    $resultado['fecha_raw'] = $fecha_raw; // Guardar formato Ymd
                    $resultado['fecha'] = buscador_formatear_fecha_legible($fecha_raw);
                    $resultado['badges'][] = 'Día ' . get_field('dia', $dia_post[0]->ID);
                    
                    error_log("✅ Actividad '{$post->post_title}' → Día ID: {$dia_post[0]->ID}, URL: {$resultado['url']}");
                } else {
                    error_log("⚠️ Actividad '{$post->post_title}' no tiene día asociado (id_dia: $id_dia)");
                }
            }
            $resultado['badges'][] = 'Actividad';
            // Coordenadas
            $resultado['latitud'] = get_field('latitud_actividad', $post->ID);
            $resultado['longitud'] = get_field('longitud_actividad', $post->ID);
            break;
        
        case 'hospedaje':
            $resultado['descripcion'] = get_field('descripcion_hospedaje', $post->ID);
            $resultado['lugar'] = get_field('sitio_hospedaje', $post->ID);
            
            // Obtener fecha desde el día relacionado
            $id_dia = get_field('id_dia', $post->ID);
            if ($id_dia) {
                $dia_post = get_posts(array(
                    'post_type' => 'dia',
                    'meta_key' => 'id_dia',
                    'meta_value' => $id_dia,
                    'posts_per_page' => 1
                ));
                if (!empty($dia_post)) {
                    $fecha_raw = get_field('fecha_dia', $dia_post[0]->ID);
                    $resultado['fecha_raw'] = $fecha_raw; // Guardar formato Ymd
                    $resultado['fecha'] = buscador_formatear_fecha_legible($fecha_raw);
                    $resultado['badges'][] = 'Día ' . get_field('dia', $dia_post[0]->ID);
                }
            }
            
            $resultado['badges'][] = get_field('tipo_hospedaje', $post->ID);
            $resultado['badges'][] = 'Hospedaje';
            // Coordenadas
            $resultado['latitud'] = get_field('latitud_hospedaje', $post->ID);
            $resultado['longitud'] = get_field('longitud_hospedaje', $post->ID);
            break;
        
        case 'restaurante':
            $resultado['descripcion'] = get_field('tipo_comida', $post->ID);
            
            // Obtener lugar desde el día relacionado
            $id_dia = get_field('id_dia', $post->ID);
            if ($id_dia) {
                $dia_post = get_posts(array(
                    'post_type' => 'dia',
                    'meta_key' => 'id_dia',
                    'meta_value' => $id_dia,
                    'posts_per_page' => 1
                ));
                if (!empty($dia_post)) {
                    $resultado['lugar'] = get_field('sitio_dia', $dia_post[0]->ID);
                    $fecha_raw = get_field('fecha_dia', $dia_post[0]->ID);
                    $resultado['fecha_raw'] = $fecha_raw; // Guardar formato Ymd
                    $resultado['fecha'] = buscador_formatear_fecha_legible($fecha_raw);
                    $resultado['badges'][] = 'Día ' . get_field('dia', $dia_post[0]->ID);
                }
            }
            
            $resultado['badges'][] = get_field('tipo_comida', $post->ID);
            $resultado['badges'][] = 'Restaurante';
            // Coordenadas
            $resultado['latitud'] = get_field('latitud_restaurante', $post->ID);
            $resultado['longitud'] = get_field('longitud_restaurante', $post->ID);
            break;
        
        case 'capitulo':
            $resultado['descripcion'] = get_field('descripcion_capitulo', $post->ID);
            $resultado['lugar'] = get_field('sitio_inicio_capitulo', $post->ID) . ' → ' . get_field('sitio_fin_capitulo', $post->ID);
            $resultado['badges'][] = 'Capítulo';
            // Coordenadas (usar punto de inicio)
            $resultado['latitud'] = get_field('latitud_inicio_capitulo', $post->ID);
            $resultado['longitud'] = get_field('longitud_inicio_capitulo', $post->ID);
            break;
    }
    
    // Truncar descripción
    if (strlen($resultado['descripcion']) > 200) {
        $resultado['descripcion'] = substr($resultado['descripcion'], 0, 200) . '...';
    }
    
    return $resultado;
}

/**
 * Obtener icono SVG según tipo de post
 */
function buscador_get_icono_tipo($post_type) {
    $iconos = array(
        'dia' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'tramo' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'actividad' => '<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>',
        'hospedaje' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'restaurante' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
        'capitulo' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'
    );
    
    return isset($iconos[$post_type]) ? $iconos[$post_type] : $iconos['dia'];
}

/**
 * Formatear fecha de Ymd (20260420) a formato legible español (20 Abr 2026)
 */
function buscador_formatear_fecha_legible($fecha_ymd) {
    if (empty($fecha_ymd) || strlen($fecha_ymd) !== 8) {
        return $fecha_ymd;
    }
    
    // Extraer componentes: 20260420 → año=2026, mes=04, dia=20
    $año = substr($fecha_ymd, 0, 4);
    $mes = substr($fecha_ymd, 4, 2);
    $dia = substr($fecha_ymd, 6, 2);
    
    // Nombres de meses en español (abreviados)
    $meses_cortos = array(
        '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
        '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
    );
    
    // Formato: 20 Abr 2026
    return intval($dia) . ' ' . $meses_cortos[$mes] . ' ' . $año;
}
