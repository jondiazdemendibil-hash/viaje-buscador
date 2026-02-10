<?php
/*
Template Name: Buscador
*/
get_header();
?>

<div class="vw-buscador">
    <div class="container">
                <!-- Cabecera: Breadcrumbs + Título -->
                <div class="header-bar">
                    <div class="breadcrumbs">
                        <a href="#">Home</a> / <a href="#">Volver</a> / <strong>Búsqueda Global</strong>
                    </div>

                    <h1 class="page-title">Búsqueda Global del Viaje</h1>

                    <div class="header-spacer" aria-hidden="true"></div>
                </div>

                <!-- Barra de resultados (Screen 2) -->
                <div class="header-bar header-bar-results" id="headerBarResults">
                    <div class="breadcrumbs">
                        <div class="crumbs-inner">
                            <a href="#">Home</a>
                            <span class="crumbs-sep">/</span>
                            <a href="#">Volver</a>
                            <span class="crumbs-sep">/</span>
                            <strong>Resultados</strong>
                            <span class="crumbs-sep">/</span>
                            <a href="#" class="crumb-action" id="btnBackSearch">
                                <span class="crumb-action-icon">↩</span>
                                Volver a buscar
                            </a>
                        </div>
                    </div>

                    <div class="header-title-stack">
                        <h1 class="page-title" id="resultsQuery">París</h1>
                        <div class="screen2-meta">
                            <span class="results-total" id="resultsTotalBar">31 resultados</span>
                        </div>
                    </div>

                    <div class="header-spacer" aria-hidden="true"></div>
                </div>

                <div class="screen-1" id="screen1">
                    <div class="screen-1-content">
                        <div class="search-column">
                            <!-- BUSCADOR (IZQUIERDA) -->
                            <div class="search-section">
                        <!-- TABS -->
                        <div class="search-tabs">
                            <button class="tab-button active" data-tab="todo">
                                Buscar todo
                                <span class="tab-count">250</span>
                            </button>
                            <button class="tab-button" data-tab="lugar">
                                Lugar
                                <span class="tab-count">25</span>
                            </button>
                            <button class="tab-button" data-tab="fecha">
                                Fecha
                                <span class="tab-count">30</span>
                            </button>
                            <button class="tab-button" data-tab="actividad">
                                Actividad
                                <span class="tab-count">80</span>
                            </button>
                        </div>

                        <!-- SELECTOR MÓVIL DE TABS -->
                        <div class="mobile-tab-selector">
                            <button class="mobile-tab-button" id="mobileTabButton">
                                <span id="mobileTabLabel">Buscar todo</span>
                                <span>▾</span>
                            </button>
                            <div class="mobile-tab-dropdown" id="mobileTabDropdown">
                                <div class="mobile-tab-option active" data-tab="todo">
                                    <span>Buscar todo</span>
                                    <span class="tab-count">250</span>
                                </div>
                                <div class="mobile-tab-option" data-tab="lugar">
                                    <span>Lugar</span>
                                    <span class="tab-count">25</span>
                                </div>
                                <div class="mobile-tab-option" data-tab="fecha">
                                    <span>Fecha</span>
                                    <span class="tab-count">30</span>
                                </div>
                                <div class="mobile-tab-option" data-tab="actividad">
                                    <span>Actividad</span>
                                    <span class="tab-count">80</span>
                                </div>
                            </div>
                        </div>

                        <!-- BÚSQUEDA SECUNDARIA -->
                        <div class="secondary-search">
                            <!-- Texto de ayuda para Tab Fecha -->
                            <div class="fecha-instructions" id="dateHelpText">
                                <div>Elige <span class="no-italic">“Desde”</span>: un solo día, un solo resultado.</div>
                                <div class="fecha-line2">Añade <span class="no-italic">“Hasta”</span> y amplia la búsqueda a un periodo de tiempo.</div>
                            </div>

                            <p class="search-help-text search-help-text--todo">Explora entre todas las posibilidades que te imagines.<br>Escribe para filtrar tu búsqueda.</p>
                            <p class="search-help-text search-help-text--lugar">¿A dónde quieres ir?<br><span style="white-space: nowrap;">Si no encontramos tu destino, te sugeriremos lugares cercanos.</span></p>
                            <p class="search-help-text search-help-text--actividad">¿Qué te gustaría hacer? Museo, playa, spa, aventura...<br>Escribe una actividad para encontrar destinos perfectos.</p>

                            <div class="search-input-wrapper" id="searchInputWrapper">
                                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <input
                                    type="text"
                                    id="searchInput"
                                    class="keyword-input"
                                    placeholder="Museos en París, Cruceros por Polinesia, Aventuras Polares, Safaris en Botsuana..."
                                    autocomplete="off"
                                >
                                <button class="clear-button" id="clearButton">×</button>

                                <!-- DROPDOWN DE SUGERENCIAS -->
                                <div class="suggestions-dropdown" id="suggestionsDropdown">
                                    <!-- Se llena con JavaScript -->
                                </div>
                            </div>

                            <!-- Chip de búsqueda confirmada -->
                            <div class="search-locked-chip" id="searchLockedChip">
                                <span class="search-locked-chip-text" id="searchLockedText"></span>
                                <button class="search-locked-chip-close" id="searchLockedClose" aria-label="Quitar filtro">×</button>
                            </div>


                            <!-- TAB FECHA: Selectores de calendario -->
                            <div class="date-search-controls" id="dateSearchControls">
                                <div class="date-inputs-wrapper">
                                    <div class="date-input-group">
                                        <label for="dateFrom">Desde</label>
                                        <input
                                            type="date"
                                            id="dateFrom"
                                            class="date-input"
                                        >
                                    </div>
                                    <div class="date-input-group">
                                        <label for="dateTo">Hasta</label>
                                        <input
                                            type="date"
                                            id="dateTo"
                                            class="date-input"
                                        >
                                        <div class="fecha-hint" id="fechaRangeHint" aria-live="polite"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- HINT DE TECLADO -->
                            <div class="keyboard-hint">
                                Atajo rápido: <span class="kbd">Ctrl</span> + <span class="kbd">K</span>
                            </div>
                        </div>

                            </div>
                        </div>

                        <div class="carousel-column">
                            <p class="carousel-title">¿O prefieres descubrir nuestras Recomendaciones? Déjate inspirar</p>
                            <!-- CARRUSEL 3D DE FICHAS -->
                            <div class="carousel-3d-section">
                            <div class="carousel-3d">
                                <div class="carousel-stack" id="carouselStack">
                                    <!-- Card 1: Torre Eiffel -->
                                    <article class="carousel-card" data-index="0" style="background-image: url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80');">
                                        <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="rec-torre-eiffel">
                                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                              <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                            </svg>
                                        </button>
                                        <div class="carousel-card-content">
                                            <h2 class="carousel-card-title">Torre Eiffel</h2>
                                            <p class="carousel-card-line">París</p>
                                            <p class="carousel-card-line">10 Jun 2027</p>
                                        </div>
                                    </article>

                                    <!-- Card 2: Museo del Louvre -->
                                    <article class="carousel-card" data-index="1" style="background-image: url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80');">
                                        <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="rec-museo-del-louvre">
                                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                              <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                            </svg>
                                        </button>
                                        <div class="carousel-card-content">
                                            <h2 class="carousel-card-title">Museo del Louvre</h2>
                                            <p class="carousel-card-line">París</p>
                                            <p class="carousel-card-line">12 Jun 2027</p>
                                        </div>
                                    </article>

                                    <!-- Card 3: Sagrada Familia -->
                                    <article class="carousel-card" data-index="2" style="background-image: url('https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80');">
                                        <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="rec-sagrada-familia">
                                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                              <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                            </svg>
                                        </button>
                                        <div class="carousel-card-content">
                                            <h2 class="carousel-card-title">Sagrada Familia</h2>
                                            <p class="carousel-card-line">Barcelona</p>
                                            <p class="carousel-card-line">20 Jun 2027</p>
                                        </div>
                                    </article>

                                    <!-- Card 4: Museo del Prado -->
                                    <article class="carousel-card" data-index="3" style="background-image: url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80');">
                                        <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="rec-museo-del-prado">
                                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                              <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                            </svg>
                                        </button>
                                        <div class="carousel-card-content">
                                            <h2 class="carousel-card-title">Museo del Prado</h2>
                                            <p class="carousel-card-line">Madrid</p>
                                            <p class="carousel-card-line">5 May 2026</p>
                                        </div>
                                    </article>

                                    <!-- Card 5: Royal Mansour -->
                                    <article class="carousel-card" data-index="4" style="background-image: url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80');">
                                        <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="rec-royal-mansour">
                                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                              <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                            </svg>
                                        </button>
                                        <div class="carousel-card-content">
                                            <h2 class="carousel-card-title">Royal Mansour</h2>
                                            <p class="carousel-card-line">Marrakech</p>
                                            <p class="carousel-card-line">9 Jul 2027</p>
                                        </div>
                                    </article>

                                    <!-- Card 6: Coliseo Romano -->
                                    <article class="carousel-card" data-index="5" style="background-image: url('https://images.unsplash.com/photo-1470770903676-69b98201ea1c?auto=format&fit=crop&w=1200&q=80');">
                                        <button type="button" class="fav-btn" aria-label="Añadir a favoritos" aria-pressed="false" data-fav-id="rec-coliseo-romano">
                                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                              <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                            </svg>
                                        </button>
                                        <div class="carousel-card-content">
                                            <h2 class="carousel-card-title">Coliseo Romano</h2>
                                            <p class="carousel-card-line">Roma</p>
                                            <p class="carousel-card-line">15 Ago 2027</p>
                                        </div>
                                    </article>
                                </div>
                            </div>
                            <div class="carousel-pagination" id="carouselPagination">
                                <div class="carousel-dots" id="carouselDots">
                                    <span class="carousel-dot active" data-index="0"></span>
                                    <span class="carousel-dot" data-index="1"></span>
                                    <span class="carousel-dot" data-index="2"></span>
                                    <span class="carousel-dot" data-index="3"></span>
                                    <span class="carousel-dot" data-index="4"></span>
                                    <span class="carousel-dot" data-index="5"></span>
                                </div>
                                <div class="carousel-counter" id="carouselCounter">1 de 6</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <section class="screen-2" id="screen2" style="display: none;">

                <div class="results-layout vw-s2-layout">

                    <!-- Panel izquierdo: Grid de fichas (60%) -->
                    <div class="vw-s2-left">
                        <div class="results-grid-container">
                            <div class="results-grid" id="resultsGrid">
                                <!-- Fichas se generan dinámicamente con JS -->
                            </div>

                            <!-- Infinite Scroll: Loader -->
                            <div id="infiniteScrollLoader" class="infinite-scroll-loader">
                                <div class="spinner"></div>
                            </div>

                            <!-- Infinite Scroll: Mensaje fin -->
                            <div id="infiniteScrollEnd" class="infinite-scroll-end">
                                No hay más resultados
                            </div>
                        </div>
                    </div>

                    <!-- Panel derecho: Mapa (40%) -->
                    <aside class="vw-s2-right">
                        <div class="results-map-container">
                            <div class="results-map" id="resultsMap">
                                <div class="map-placeholder">
                                    <span class="map-placeholder-icon">🗺️</span>
                                    <p>Mapa de resultados</p>
                                    <p class="map-placeholder-note">Google Maps se integrará en PHP</p>
                                </div>
                            </div>
                        </div>
                    </aside>

                </div>

            </section>

        </div>
    </div>
</div>

<?php
get_footer();
?>
