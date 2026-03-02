# Informe operativo — Buscador (Screen 1/2) y migración a PHP

**Especificación funcional + decisiones cerradas (v1.1, consolidado v1.7)**

Fecha: 19/02/2026  ·  Versión: v1.7 (consolidado)

Ámbito: WordPress + Avada (XAMPP). Este documento fija decisiones de UX/UI y reglas técnicas v1 para ejecutar la migración sin improvisación.

---

## 1. Contexto y objetivo

Objetivo: diseñar y cerrar la lógica de resultados del buscador (Tabs) y dejar un plan operativo para migrar el borrador HTML a una plantilla PHP en WordPress/Avada, manteniendo el diseño y evitando roturas.

- **Screen 1:** búsqueda (tabs, inputs, recomendaciones).
- **Screen 2:** resultados + mapa.
- Botón de salida rápida: **“Volver a Buscar”** (vuelve a Screen 1 limpio).

Principio rector: **no saturar de fichas**.
- Para **facetas / experiencias / items**: salida máxima **≤ 12 fichas**.
- Para **listas de días**: **scroll infinito** (chunks) con ayudas de navegación, **sin separadores** ni headers adicionales.

**Nota v1 (importante para buscador + Cesium):**
- QA: **pais_dia no puede contener** “Océano/Mar/Sahara/Amazonia/…” (eso va en **zona_dia**). **sitio_dia no debe llevar coma**.
- Presentación UI (sin duplicar datos):
  - **Lugar mostrado** = **“{sitio_dia}, {pais_dia}”**.
  - Si **pais_dia = “Aguas internacionales”** y **zona_dia** no está vacía: **“{sitio_dia} — {zona_dia}”**.
- Campo nuevo (v1): **zona_dia** (opcional) para mar/océano/región natural/archipiélago/polar cuando aporte valor (ej.: “Océano Atlántico”, “Sahara”, “Amazonia”). **NO usar continentes en datos**.
- Canon de lugar (datos):
  - **sitio_dia = ciudad/etiqueta corta SIN país**.
  - **pais_dia = país canónico** (o **“Aguas internacionales”** en navegación/zonas sin país).

---

## 2. Diseño de resultados por Tabs (Screen 2)

Convención general de ficha (v1): **Título + Línea 1 + Línea 2**. En móvil, priorizar claridad y evitar densidad excesiva.

Convención general de orden: **cronológico del viaje** (ascendente).

### 2.1 Tab FECHA (sin OpenAI)

#### 2.1.1 Selector de fechas (Screen 1)

- Permite seleccionar un **día** (desde = hasta) o un **rango**.
- Validación mínima: **fecha_desde ≤ fecha_hasta**.

#### 2.1.2 Resultados (Screen 2)

Ficha día (resultado estándar):
- **Título:** fecha_dia
- **Línea 1:** sitio_display (**“{sitio_dia}, {pais_dia}”**; si pais_dia = “Aguas internacionales” y zona_dia no está vacía: **“{sitio_dia} — {zona_dia}”**)
- **Línea 2:** hito_dia

Comportamiento:
- Resultados en orden cronológico (ascendente).
- Scroll infinito por chunks para rangos largos.
- La barra de título puede mostrar el rango seleccionado y el número de días; **las migas y el contador de fichas se mantienen como están** (sin añadir elementos nuevos).

#### 2.1.3 Contexto de tramos por solape (automático, v1)

Regla (v1): **NO** se muestran **fichas de tramo** dentro del tab FECHA. Solo se añade **micro-contexto textual** si aplica.

Cómo se calcula: a partir de los días ya filtrados por el rango, obtener el conjunto de **id_tramo** distintos (si existen).

- Si no hay tramos: no mostrar nada.
- Si hay 1 tramo: **“En el tramo: {nombre_tramo}”** (opcional: enlace a single-tramo-custom.php del tramo).
- Si hay 2 tramos: **“Tramos: A, B”**.
- Si hay 3+ tramos: **“Tramos: A (+N)”**.

Nota: la visualización de tramos como fichas (experiencias) se reserva para los tabs **ACTIVIDAD/TODO** cuando el usuario busca “crucero/safari/tren/polar…”.

---

### 2.2 Tab LUGAR (sin OpenAI)

#### 2.2.1 Entrada (Screen 1)

- Dropdown/autocomplete por lugares existentes (CPT/ACF).
- Tolerancia: tildes y mayúsculas mediante normalización.
- Typos: **alias + fuzzy matching (sin OpenAI)**.

#### 2.2.2 Resultados cuando el lugar existe (Screen 2)

- Barra de título: **“Lugar: {sitio_display}”**.
  - sitio_display = **“{sitio_dia}, {pais_dia}”** (o “{sitio_dia} — {zona_dia}” si aplica).

Definición de “estancia” (v1): bloque máximo de días consecutivos con el **MISMO lugar canónico**.
- sitio_key = **sitio_dia + "||" + pais_dia** (si pais_dia normal).
- Si pais_dia = “Aguas internacionales” y zona_dia existe: sitio_key puede tratarse como **zona_dia** (para consolidar navegación).

Se calcula desde los días filtrados, ordenados por fecha_dia.

- Si hay 1 estancia: mostrar directamente los días (ficha estándar de FECHA).
- Si hay 2+ estancias: mostrar primero fichas de estancia:
  - **Título:** fecha_desde – fecha_hasta
  - **Línea 1:** sitio_display
  - (sin Línea 2)
  - Al seleccionar una estancia, mostrar sus días.

#### 2.2.3 Resultados cuando el lugar NO existe (Screen 2)

Disparador: el usuario ejecuta la búsqueda en LUGAR (Enter o botón) con texto libre y el dropdown queda en blanco (no hay match exacto).

- No tocar migas de pan ni contador de fichas.

**Paso 1 — Respetar el término del usuario:** conservar query_original tal cual (p. ej., “Berna”).

**Paso 2 — Alias (solo si es inequívoco):** si query_original coincide con un alias inequívoco, resolver al canónico.
- Barra de título (Screen 2, 1 línea): **“Lugar: {query_original} → {sitio_canónico}”**.
- Luego mostrar resultados normales del sitio (estancias → días).

**Paso 3 — Si no hay alias inequívoco:** Google Geocoding + cercanos reales (modo “no lo tengo”).
- Barra de título (Screen 2, 1 línea): **“Lo siento, no tengo: {query_original}”**.
- Texto breve en área de resultados (no en la barra): **“Pero cerca de “{query_original}” sí tengo:”**.
- Mostrar 4 alternativas por proximidad (distancia real) contra tus sitios existentes:
  - Ficha alternativa:
    - **Título:** sitio_display
    - **Línea 1:** ~{km} km de “{query_original}”
    - (sin Línea 2)
  - Al clicar una alternativa: ejecutar el flujo normal de LUGAR para ese sitio.

**Paso 4 — Fallback si Google falla (cuota/timeout/vacío):**
- Mantener la barra “Lo siento, no tengo…”.
- Mostrar: **“No pude calcular cercanos ahora mismo. ¿Querías decir…?”** + 4 sugerencias por similitud de nombre (fuzzy), **sin km** y **sin prometer cercanía**.

**Diccionario de alias (v1) — mínimo para implementación:**
- Se consulta **SOLO** si el usuario ejecuta búsqueda sin match en dropdown.
- Matching determinista: normalizar (minúsculas, sin tildes/diacríticos, trim, colapsar espacios, quitar puntuación trivial) y comparar por igualdad.
- Un alias es **INEQUÍVOCO** si apunta a un único sitio canónico del dataset; si es **AMBIGUO** (p. ej., “Santiago”), **NO** se resuelve automáticamente.
- Si INEQUÍVOCO: se usa la barra “Lugar: alias → canónico”.
- Si AMBIGUO: se trata como “no encontrado” y pasa a Google cercanos/fallback.

---

### 2.3 Tab ACTIVIDAD (con OpenAI)

#### 2.3.0 Barra de título (Screen 2) — una sola línea

Regla: mostrar qué entendió el sistema y qué se está filtrando en una sola línea. Si no coincide, el usuario usa “Volver a Buscar”.

Formato: **“Actividad: {interpretación} · {ruta_de_filtros} · {nivel}”** (solo lo que aplique).

#### 2.3.1 Determinación de target_entity (v1)

- Familias macro (crucero/safari/polar/tren de lujo, etc.) → target_entity = **experiencia (tramo)**.
- Búsqueda específica (nombre/actividad clara) → target_entity = **item (actividad)**.
- Genéricos masivos (spa/museos/wellness/compras, etc.) → target_entity = **facet** (refinado) y, cuando sea manejable, **item**.

#### 2.3.2 Ficha de experiencia (crucero/safari/expedición)

- **Título:** nombre de la experiencia (si existe) o Crucero/Safari/Polar + etiqueta corta.
- **Línea 1:** fecha_desde – fecha_hasta
- **Línea 2:** Sitio_inicio → Sitio_fin (+N)

#### 2.3.3 Ficha item (actividad)

- **Título:** titulo_actividad
- **Línea 1:** sitio_display
- **Línea 2:** fecha_dia

Ordenación de items:
- fecha_dia ascendente; si hay varios items el mismo día, ordenar por **orden_dia** ascendente.

#### 2.3.4 Árboles mínimos (v1) para familias macro (experiencias)

Objetivo: responder a consultas tipo “crucero / safari / polar / tren…” **sin depender de inferencias**.

Fuente de verdad: tramos.csv / CPT tramo, columnas:
- **familia_tramo** (filtro principal macro)
- **subfamilia_tramo** (opcional)

Reglas v1:
- **No** crear taxonomías nuevas para mares/océanos/continentes en v1.
- Si target=experiencia y la query coincide con una familia: filtrar tramos por **familia_tramo**.
- Si un tramo no tiene familia_tramo: solo puede entrar por match directo de **nombre_tramo** (keyword fuerte) o por búsqueda explícita por nombre.
- Subfamilia (solo si hace falta):
  - Si dentro de una familia hay **>12** tramos, mostrar chips por **subfamilia_tramo** **SOLO** cuando esté rellenada.
  - Si subfamilia_tramo está vacía: ordenar cronológico y devolver ≤12 (con paginación “ver más”).

Estado inicial permitido: celdas vacías (relleno “just in time” al editar/curar tramos).

#### 2.3.5 Genéricos masivos (spa/museos/...) — flujo cerrado (v1)

Primera respuesta siempre **≤12 fichas** (modo facetas).

Refinado por geografía:
- **v1 (6A): Países → Lugares.**
  - Países se obtiene de **pais_dia** de los items candidatos (excluye “Aguas internacionales”).
  - Lugares se obtiene de **sitio_dia** dentro del país elegido.
- **v1.1 (6B, interruptor opcional): Continente → País → Lugar.**
  - Condición automática: si Países candidatos **> 8**, activar Continentes.
  - Si Países candidatos **≤ 8**, saltar Continente aunque el interruptor esté ON.
  - Continente se deriva **de forma determinista** desde pais_dia mediante tabla local país→continente (**sin IA y sin API**).
  - Países no mapeados → “Otros/Desconocido” (y se registran para ampliar la tabla).

Comportamiento adicional:
- Saltos automáticos de nivel si un nivel solo tiene 1 opción.
- Si el usuario ya incluye país/lugar en la query (p. ej. “spa París” o “museos Italia”): saltar al nivel más cercano y continuar.
- Paso final: mostrar items (ficha titulo_actividad / sitio_display / fecha_dia).
- Si aún quedan demasiados items en un mismo lugar (caso raro): insertar faceta temporal ligera por **estancias/periodos** del lugar y, tras elegir periodo, mostrar items.

#### 2.3.6 Límites y fallback

- Sin badges adicionales: la claridad se consigue con titulo_actividad + sitio_display + fecha_dia.
- Si OpenAI falla o baja confianza: fallback a búsqueda literal + facetas deterministas (manteniendo ≤12).

---

### 2.4 Tab TODO (con OpenAI)

#### 2.4.0 Barra de título (Screen 2) — una sola línea

Formato: **“Todo: {interpretación} · {target} · {ruta_de_filtros} · {nivel}”** (solo lo que aplique).

#### 2.4.1 Regla general

TODO amplía ACTIVIDAD y además resuelve:
- búsquedas cruzadas (atributos + lugar/tiempo)
- consultas tipo pregunta

El Intérprete decide target_entity; la ejecución es determinista.

#### 2.4.2 Guardarraíles (obligatorios)

- Target obligatorio: facet / experiencia / item / dia. **Prohibido mezclar listas.**
- Combinación por defecto: **AND**. OR solo si el usuario lo expresa explícitamente.
- Scope: los filtros se aplican al **mismo target** (no crean listados paralelos).
- Límite: si target ∈ {facet, experiencia, item} → ≤12 fichas. Días → scroll infinito.

Consultas tipo pregunta (intent=question):
- ¿Dónde dormimos …? → item:hospedaje
- ¿Dónde cenamos …? → item:restaurante
- ¿Qué hicimos …? → item:actividad (o día si es muy amplio)

Proximidad:
- soportar “cerca de {lugar}” solo si {lugar} existe en los sitios del viaje (coords)
- si no existe, aplicar flujo LUGAR no existe
- no soportar en v1 “cerca del hotel/donde dormimos” en el buscador global

#### 2.4.3 Unidades de resultado (v1)

Items (restaurante/hospedaje/actividad) en Screen 2:

- **Restaurante**
  - **Título:** nombre_restaurante (texto principal)
  - Opcional: **(tipo_comida)** como subtítulo pequeño/menos prominente
  - **Línea 1:** sitio_display
  - **Línea 2:** fecha_dia
  - Nota v1: **NO** mostrar categoria_restaurante ni video_url_restaurante en la ficha de resultados.
  - Orden: fecha_dia ascendente; luego **orden_restaurante** ascendente (1/2/3). Si faltase, fallback por tipo_comida con catálogo cerrado (Desayuno/Almuerzo/Cena) y después orden_restaurante.

- **Hospedaje**
  - **Título:** nombre_hospedaje
  - **Línea 1:** sitio_display
  - **Línea 2:** fecha_dia
  - Orden cronológico

- **Experiencia (tramo)**: ficha de experiencia (tramo) según 2.3.2.
- **Día**: ficha estándar de FECHA.

#### 2.4.4 Ejemplos de búsqueda cruzada (v1)

- “Cruceros en Navidad”: target=experiencia; filtro temporal por solape fecha_desde-fecha_hasta con Navidad.
- “Restaurantes Michelin en Grecia”: target=item:restaurante; filtro por categoria_restaurante que contenga “Michelin” (si el dato está relleno) + lugar=Grecia; si demasiados, facetas geográficas.
- “Tren de lujo en China”: target=experiencia; lugar=China.

---

## 3. Contrato del Intérprete (OpenAI) — reglas no negociables (v1)

La IA traduce lo que escribe el usuario a un plan (SearchPlan). El sistema ejecuta el plan con datos reales.

- Catálogo y reglas de familia_tramo/subfamilia_tramo: ver Handoff — Viaje con Excel (Anexo familias tramos).
- La IA **solo interpreta**, no busca: produce intención, filtros y siguiente paso (facetas o resultados).
- Una sola unidad objetivo (target) por consulta: facet / experiencia / item / dia.
- Límites: ≤12 fichas para facet/experiencia/item. Días: scroll infinito por chunks (per_page=21).
- AND por defecto. Cada filtro declara su scope y filtra el mismo target.
- Si duda o falla: fallback a facetas deterministas o búsqueda literal. **Nunca pantalla vacía.**
- Barra de título siempre honesta (una línea).
- Encaje por tabs: FECHA/LUGAR sin OpenAI; ACTIVIDAD/TODO usan OpenAI solo para interpretación.

---

## 4. Recomendación operativa — usar Claude Opus 4.6 para migrar sin romper nada (v1)

- Un objetivo por parche (máximo 1 cambio funcional + 1 limpieza asociada).
- Prohibido tocar lo no nombrado: delimitar qué archivos/zonas puede modificar.
- Salida siempre como diff/parche + checklist de verificación (antes/después).
- No mezclar refactors con migración: CSS/JS en parches dedicados.
- Rollback fácil: cada parche debe ser reversible sin efectos colaterales.

---

## 5. Ejecución de la migración (HTML → PHP) en pasos pequeños (v1)

### 5.0 Preparación

- Crear baseline (tag/snapshot) del estado actual.
- Definir plantilla destino en Avada child (p. ej. page-buscador.php o equivalente).
- Confirmar referencia visual: Screen 1/2 funcionan en HTML.

### 5.1 Paso 1 — Portado 1:1 (markup)

Copiar el HTML al esqueleto PHP sin lógica, manteniendo DOM/clases/IDs.
- Verificación: pantalla idéntica (sin datos dinámicos).

### 5.2 Paso 2 — Externalizar assets de forma segura

Mover CSS/JS a archivos encolados (enqueue) sin cambiar selectores.
- Verificación: 0 cambios visuales, 0 errores en consola.

### 5.3 Paso 3 — Datos mock controlados

Sustituir hardcode por un mock centralizado (no duplicar).
- Verificación: UI estable, aún independiente de WP.

### 5.4 Paso 4 — Conectar datos reales (lectura)

Conectar ACF/CPT: días, actividades, restaurantes, hospedajes, tramos.
- Verificación: un caso real por tab sin romper.

### 5.5 Paso 5 — Endpoints (REST) y paginación

Implementar /suggest y /search (contrato v1).
- Verificación: límites respetados (≤12 / 21 días) y scroll infinito estable.

### 5.6 Paso 6 — Integración OpenAI (solo interpretación)

Implementar SearchPlan server-side con cache + rate limit + fallback.
- Verificación: búsquedas cruzadas no mezclan listas; preguntas resuelven a hospedajes/restaurantes cuando proceda.

### 5.7 Paso 7 — Mapa por estados

Implementar comportamiento del mapa según 6.2.1.
- Verificación: pin único por lugar_key, sin mareos; estados correctos.

### 5.8 Paso 8 — Deep links por ID

Implementar URLs canónicas por id_actividad/id_restaurante/id_hospedaje con fallback seguro al día.
- Verificación: todos los clicks llegan al contenido correcto.

### 5.9 Paso 9 — QA final

Ejecutar matriz 6.2.8 completa. Si falla una misión, se corrige antes de avanzar.

---

## 6. Decisiones cerradas y ejecución (v1)

### 6.1 Resuelto

- Tabs cerrados: FECHA, LUGAR, ACTIVIDAD, TODO.
- Typos/tildes/mayúsculas sin OpenAI (normalización + alias + fuzzy + UX “Mostrando resultados para…”).
- LUGAR no existe: texto + 4 alternativas cercanas.
- Facetas geográficas (genéricos masivos): **v1=País→Lugar; v1.1=Continente→País→Lugar** como interruptor si >8 países, continente derivado por tabla país→continente.

### 6.2 Decisiones técnicas cerradas

#### 6.2.1 Mapa por estado (cerrado)

- **Día:** 1 pin por **lugar_key** (si pais_dia normal → sitio_dia||pais_dia; si pais_dia = “Aguas internacionales” y zona_dia existe → zona_dia). No re-centrar si no cambia lugar_key; pan suave + zoom constante si cambia; opcional badge xN cuando varias fichas visibles comparten lugar_key.
- **Periodo/Visita:** 1 pin del lugar del periodo; sin rutas.
- **Experiencia (tramo):** 2 pins inicio/fin + fit bounds; sin rutas.
- **Facetas geográficas:** no mover mapa hasta que el usuario elija algo concreto.
- **No-results/sugerencias:** si hay 4 sugerencias, 4 pins + fit bounds; si no, sin cambios.

#### 6.2.2 Experiencia = tramo (cerrado)

Fuente de verdad: tramos.csv / CPT tramo.

Campos: id_tramo, nombre_tramo (fallback Tramo {id}), familia_tramo (opcional), subfamilia_tramo (opcional), fecha_inicio/fin, sitio_inicio/fin.

#### 6.2.3 Coincidencia semántica en títulos (cerrado)

Para búsquedas por tema en ACTIVIDAD/TODO, preferir items cuando existan (titulo_actividad / sitio_display / fecha_dia).

Genéricos masivos: facetas geográficas hasta ≤12, luego items; fallback a día solo si no hay items claros.

#### 6.2.4 Deep links por ID (cerrado)

URLs canónicas por id_actividad/id_restaurante/id_hospedaje desde ya.

Si no existe plantilla dedicada, fallback seguro al día (con ancla si se puede) o placeholder mínimo.

#### 6.2.5 API/Endpoint y paginación (cerrado)

WordPress REST API versionada (no admin-ajax).

Endpoints mínimos: /search (resultados + facetas + mapa), /suggest (autocomplete).

Límites: ≤12 para facetas/experiencias/items; per_page=21 para días.

#### 6.2.6 OpenAI en producción (cerrado)

Solo interpretación (SearchPlan) en TODO/ACTIVIDAD.

Key solo servidor. Cache (TTL recomendado 24h). Rate limit por usuario. Batch/offline para facetas/temas. Fallback determinista.

#### 6.2.7 Rendimiento (cerrado)

Virtualización de lista obligatoria. Paginación por chunks. Mapa con debounce/throttle. Imágenes lazy-load.

#### 6.2.8 QA de regresión (cerrado)

Misiones mínimas (examen final):
- M1 FECHA 1 día; M2 FECHA 7 días; M3 FECHA 30 días; M4 FECHA 180 días.
- M5 LUGAR existe (1 estancia); M6 LUGAR existe (2+ estancias); M7 LUGAR no existe (4 sugerencias).
- M8 ACTIVIDAD crucero; M9 ACTIVIDAD spa/museos; M10 TODO cruzado (Navidad/Michelin/China).

Reglas de aprobado:
- 0 errores rojos en consola
- nunca pantalla vacía
- ≤12 donde toca
- listas de días con scroll fluido
- mapa estable con 1 pin por lugar_key

### 6.3 Ejecución con control

Migración por parches pequeños + checklist + QA por paso. Si un test falla, se corrige antes de avanzar.

---

## 7. Checklist de consolidación (v1_4 → v1.7)

Confirmado que el consolidado incluye, como mínimo:
- 2.3.4 completo (familia_tramo/subfamilia_tramo; chips solo si >12; sin taxonomías nuevas).
- 2.3.5 con v1=País→Lugar y **interruptor v1.1** con umbral >8 países y derivación determinista país→continente.
- Normalización de lugar: **sitio_dia** sin país + **pais_dia** canónico + **zona_dia** para mar/bioma/región.
- Sustitución consistente de sitio_dia por **sitio_display** en fichas y barra de título.
- Mapa por estado usando **lugar_key** (incluye caso “Aguas internacionales”).
- Restaurantes: (tipo_comida) como subtítulo opcional; **no** se muestra categoria_restaurante ni video_url_restaurante.
- Contrato Intérprete: referencia al Handoff (anexo familias tramos) y guardarraíles de target único.
- Resumen en 6.1 con facetas geográficas v1/v1.1.



---

# Addendum — Respuestas a Opus (15) + Recomendaciones 3D + Favoritos

## A) Screen 1 — Carrusel 3D “Recomendaciones” (decisión v1)

- **Unidad:** TRAMOS (experiencias).
- **Selección:** siempre **distinta**. Se apoya en el plugin custom `traveler-recommendations.php` (recomendaciones + “seen”).
- **Persistencia recomendada:** crear **área de usuarios con login** para:
  - guardar “recomendaciones ya mostradas” (no repetir por usuario),
  - y sincronizar “favoritos”.
- **Mapa en ficha:** Google Maps **solo en la ficha activa** (1 instancia), **sin gestos** (no drag/zoom/scrollwheel). Las demás fichas muestran **placeholder borroso**.
- **Click:** abre `single-tramo-custom.php` del tramo.

## B) Favoritos (❤️) en TODAS las fichas (decisión v1)

- Todas las fichas muestran un icono ❤️ (día, estancia, tramo/experiencia, actividad, restaurante, hospedaje, sugerencias).
- **Comportamiento:** toggle on/off; no navega.
- **Persistencia v1:**
  - Invitado: `localStorage`.
  - Usuario logueado: user meta vía REST (recomendado; mismo área de login que arriba).

## C) Respuestas definitivas a las 15 preguntas de Opus

### C.1 Bloquean migración
1) **Recomendaciones pre-búsqueda:** ver Addendum A.
2) **“Buscar todo” (HTML) = Tab TODO (informe):** Sí.
3) **Fichas clicables v1:**
   - Día → `single-dia.php`.
   - Tramo/experiencia → `single-tramo-custom.php`.
   - Item (actividad/restaurante/hospedaje) → v1: link al **día correspondiente** (`single-dia.php`) con `?focus={tipo}&id={id}`.
   - Facetas (chips) → refinan resultados (no navegan).
4) **Modelo de datos mínimo:** CPT separados (`dia`, `tramo`, `actividad`, `restaurante`, `hospedaje`) con ACF (no repeaters dentro de día). Para keys exactas, usar el export ACF y los CSV.
5) **OpenAI en v1:** **NO**. v1 determinista (parser + reglas). OpenAI como feature flag en v1.1 usando el mismo SearchPlan.

### C.2 Necesarias (no bloquean primer parche)
6) **Schema JSON SearchPlan:** se adopta el schema mínimo ya documentado (intent/tab/target_entity/filters/facet_path/next_action/confidence).
7) **Mapa con facetas:** v1 mapa quieto durante facetas; solo actualiza con resultados concretos. Hover/scroll-sync v1.1.
8) **Coordenadas para proximidad:** vienen de tus datos (ACF lat/lng). En runtime se construye índice de lugares únicos (`sitio_dia||pais_dia`) con coords representativas.
9) **Alias:** fichero JSON versionado en repo (tema/plugin). Solo auto-resuelve inequívocos; ambiguos no.

### C.3 Post-v1
10) **Móvil — layout del mapa:** mantener el del borrador en v1; afinar v1.1.
11) **Secciones 6–7 del informe:** no reestructurar en v1; si hace falta, añadir apéndices (modelo de datos / contrato API).
12) **Versión del informe:** seguir v1.x durante migración; v2.0 cuando v1 esté estable.

### C.4 Confirmaciones menores
13) **Ficha de estancia:** Título=rango; L1=sitio_display; sin L2. Confirmado.
14) **Orden de tabs en UI:** mantener el del HTML: TODO / LUGAR / FECHA / ACTIVIDAD.
15) **Ficha hospedaje:** Título=post_title; subtítulo opcional (tipo_hospedaje si es corto); L1=sitio_display; L2=fecha_dia.

