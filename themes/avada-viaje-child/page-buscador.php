<?php
/*
Template Name: Buscador
*/
get_header();

$referer = wp_get_referer();
if (!$referer) {
    $referer = home_url('/');
}

$tabs = [
    ['key' => 'todo', 'label' => esc_html__('Buscar todo', 'vw'), 'count' => 250],
    ['key' => 'lugar', 'label' => esc_html__('Lugar', 'vw'), 'count' => 25],
    ['key' => 'fecha', 'label' => esc_html__('Fecha', 'vw'), 'count' => 30],
    ['key' => 'actividad', 'label' => esc_html__('Actividad', 'vw'), 'count' => 80],
];

$carousel_items = [
    [
        'title' => 'Torre Eiffel',
        'place' => 'París',
        'date' => '10 Jun 2027',
        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-1',
    ],
    [
        'title' => 'Museo del Louvre',
        'place' => 'París',
        'date' => '12 Jun 2027',
        'image' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-2',
    ],
    [
        'title' => 'Sagrada Familia',
        'place' => 'Barcelona',
        'date' => '20 Jun 2027',
        'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-3',
    ],
    [
        'title' => 'Museo del Prado',
        'place' => 'Madrid',
        'date' => '5 May 2026',
        'image' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-4',
    ],
    [
        'title' => 'Royal Mansour',
        'place' => 'Marrakech',
        'date' => '9 Jul 2027',
        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-5',
    ],
    [
        'title' => 'Coliseo Romano',
        'place' => 'Roma',
        'date' => '15 Ago 2027',
        'image' => 'https://images.unsplash.com/photo-1470770903676-69b98201ea1c?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-6',
    ],
    [
        'title' => 'Playa de Tulum',
        'place' => 'México',
        'date' => '22 Mar 2027',
        'image' => 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-7',
    ],
    [
        'title' => 'Machu Picchu',
        'place' => 'Perú',
        'date' => '18 Sep 2027',
        'image' => 'https://images.unsplash.com/photo-1493246507139-91e8fad9978e?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-8',
    ],
    [
        'title' => 'Fiordos Noruegos',
        'place' => 'Noruega',
        'date' => '3 Jul 2027',
        'image' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
        'fav_id' => 'rec-card-9',
    ],
];
?>

<div class="vw-buscador">
    <div class="container">
        <header class="header-bar" aria-label="<?php echo esc_attr__('Cabecera de búsqueda', 'vw'); ?>">
            <nav class="breadcrumbs" aria-label="<?php echo esc_attr__('Breadcrumbs', 'vw'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Home', 'vw'); ?></a>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url($referer); ?>"><?php echo esc_html__('Volver', 'vw'); ?></a>
                <span aria-hidden="true">/</span>
                <strong><?php echo esc_html__('Búsqueda Global', 'vw'); ?></strong>
            </nav>
            <h1 class="page-title"><?php echo esc_html__('Búsqueda Global del Viaje', 'vw'); ?></h1>
            <div class="header-spacer" aria-hidden="true"></div>
        </header>

        <header class="header-bar header-bar-results" id="headerBarResults" aria-hidden="true">
            <nav class="breadcrumbs" aria-label="<?php echo esc_attr__('Breadcrumbs de resultados', 'vw'); ?>">
                <div class="crumbs-inner">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Home', 'vw'); ?></a>
                    <span class="crumbs-sep">/</span>
                    <a href="<?php echo esc_url($referer); ?>"><?php echo esc_html__('Volver', 'vw'); ?></a>
                    <span class="crumbs-sep">/</span>
                    <strong><?php echo esc_html__('Resultados', 'vw'); ?></strong>
                    <span class="crumbs-sep">/</span>
                    <button type="button" class="crumb-action" id="btnBackSearch">
                        <span class="crumb-action-icon" aria-hidden="true">↩</span>
                        <?php echo esc_html__('Volver a buscar', 'vw'); ?>
                    </button>
                </div>
            </nav>

            <div class="header-title-stack">
                <h1 class="page-title" id="resultsQuery"><?php echo esc_html__('París', 'vw'); ?></h1>
                <div class="screen2-meta">
                    <span class="results-total" id="resultsTotalBar"><?php echo esc_html__('31 resultados', 'vw'); ?></span>
                </div>
            </div>

            <div class="header-spacer" aria-hidden="true"></div>
        </header>

        <main class="screen-1" id="screen1">
            <div class="screen-1-content">
                <div class="search-column">
                    <section class="search-section" aria-label="<?php echo esc_attr__('Buscador', 'vw'); ?>">
                        <div class="search-tabs" role="tablist" aria-label="<?php echo esc_attr__('Filtros de búsqueda', 'vw'); ?>">
                            <?php foreach ($tabs as $index => $tab) : ?>
                                <button
                                    class="tab-button<?php echo $index === 0 ? ' active' : ''; ?>"
                                    type="button"
                                    role="tab"
                                    aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                    aria-controls="tab-panel-<?php echo esc_attr($tab['key']); ?>"
                                    id="tab-<?php echo esc_attr($tab['key']); ?>"
                                    data-tab="<?php echo esc_attr($tab['key']); ?>"
                                >
                                    <?php echo esc_html($tab['label']); ?>
                                    <span class="tab-count"><?php echo esc_html($tab['count']); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <div class="mobile-tab-selector">
                            <button
                                class="mobile-tab-button"
                                id="mobileTabButton"
                                type="button"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                aria-controls="mobileTabDropdown"
                            >
                                <span id="mobileTabLabel"><?php echo esc_html__('Buscar todo', 'vw'); ?></span>
                                <span aria-hidden="true">▾</span>
                            </button>
                            <div class="mobile-tab-dropdown" id="mobileTabDropdown" role="listbox">
                                <?php foreach ($tabs as $index => $tab) : ?>
                                    <div
                                        class="mobile-tab-option<?php echo $index === 0 ? ' active' : ''; ?>"
                                        role="option"
                                        aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                        data-tab="<?php echo esc_attr($tab['key']); ?>"
                                    >
                                        <span><?php echo esc_html($tab['label']); ?></span>
                                        <span class="tab-count"><?php echo esc_html($tab['count']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="secondary-search" aria-live="polite">
                            <div class="fecha-instructions" id="dateHelpText">
                                <div><?php echo esc_html__('Elige “Desde”: un solo día, un solo resultado.', 'vw'); ?></div>
                                <div class="fecha-line2"><?php echo esc_html__('Añade “Hasta” y amplia la búsqueda a un periodo de tiempo.', 'vw'); ?></div>
                            </div>

                            <p class="search-help-text search-help-text--todo">
                                <?php echo esc_html__('Explora entre todas las posibilidades que te imagines. Escribe para filtrar tu búsqueda.', 'vw'); ?>
                            </p>
                            <p class="search-help-text search-help-text--lugar">
                                <?php echo esc_html__('¿A dónde quieres ir? Si no encontramos tu destino, te sugeriremos lugares cercanos.', 'vw'); ?>
                            </p>
                            <p class="search-help-text search-help-text--actividad">
                                <?php echo esc_html__('¿Qué te gustaría hacer? Museo, playa, spa, aventura... Escribe una actividad para encontrar destinos perfectos.', 'vw'); ?>
                            </p>

                            <div class="search-input-wrapper" id="searchInputWrapper">
                                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>

                                <label for="searchInput" class="sr-only"><?php echo esc_html__('Buscar', 'vw'); ?></label>
                                <input
                                    type="text"
                                    id="searchInput"
                                    class="keyword-input"
                                    placeholder="<?php echo esc_attr__('Museos en París, Cruceros por Polinesia, Aventuras Polares, Safaris en Botsuana...', 'vw'); ?>"
                                    autocomplete="off"
                                    aria-label="<?php echo esc_attr__('Buscar', 'vw'); ?>"
                                >

                                <button class="clear-button" id="clearButton" type="button" aria-label="<?php echo esc_attr__('Borrar búsqueda', 'vw'); ?>">×</button>

                                <div class="suggestions-dropdown" id="suggestionsDropdown" role="listbox" aria-label="<?php echo esc_attr__('Sugerencias de búsqueda', 'vw'); ?>">
                                    <!-- Se llena con JavaScript -->
                                </div>
                            </div>

                            <div class="search-locked-chip" id="searchLockedChip" aria-live="polite">
                                <span class="search-locked-chip-text" id="searchLockedText"></span>
                                <button class="search-locked-chip-close" id="searchLockedClose" type="button" aria-label="<?php echo esc_attr__('Quitar filtro', 'vw'); ?>">×</button>
                            </div>

                            <div class="date-search-controls" id="dateSearchControls">
                                <div class="date-inputs-wrapper">
                                    <div class="date-input-group">
                                        <label for="dateFrom"><?php echo esc_html__('Desde', 'vw'); ?></label>
                                        <input type="date" id="dateFrom" class="date-input">
                                    </div>
                                    <div class="date-input-group">
                                        <label for="dateTo"><?php echo esc_html__('Hasta', 'vw'); ?></label>
                                        <input type="date" id="dateTo" class="date-input">
                                        <div class="fecha-hint" id="fechaRangeHint" aria-live="polite"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="keyboard-hint">
                                <?php echo esc_html__('Atajo rápido:', 'vw'); ?>
                                <span class="kbd">Ctrl</span> + <span class="kbd">K</span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="carousel-column">
                    <p class="carousel-title"><?php echo esc_html__('¿O prefieres descubrir nuestras Recomendaciones? Déjate inspirar', 'vw'); ?></p>

                    <section class="carousel-3d-section" aria-label="<?php echo esc_attr__('Recomendaciones', 'vw'); ?>">
                        <div class="carousel-3d">
                            <button type="button" class="carousel-nav-btn carousel-prev" aria-label="<?php echo esc_attr__('Anterior', 'vw'); ?>">‹</button>
                            <button type="button" class="carousel-nav-btn carousel-next" aria-label="<?php echo esc_attr__('Siguiente', 'vw'); ?>">›</button>
                            <div class="carousel-stack" id="carouselStack">
                                <?php foreach ($carousel_items as $index => $item) : ?>
                                    <article
                                        class="carousel-card"
                                        data-index="<?php echo esc_attr($index); ?>"
                                        style="background-image: url('<?php echo esc_url($item['image']); ?>');"
                                        aria-label="<?php echo esc_attr($item['title']); ?>"
                                    >
                                        <button
                                            type="button"
                                            class="fav-btn"
                                            aria-label="<?php echo esc_attr__('Añadir a favoritos', 'vw'); ?>"
                                            aria-pressed="false"
                                            data-fav-id="<?php echo esc_attr($item['fav_id']); ?>"
                                        >
                                            <svg class="fav-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M12 21s-7-4.35-10-9.33C-0.34 7.28 2.4 3.5 6.2 3.5c1.96 0 3.46 1.02 4.3 2.09C11.34 4.52 12.84 3.5 14.8 3.5c3.8 0 6.54 3.78 4.2 8.17C19 16.65 12 21 12 21z"/>
                                            </svg>
                                        </button>
                                        <div class="carousel-card-content">
                                            <h2 class="carousel-card-title"><?php echo esc_html($item['title']); ?></h2>
                                            <p class="carousel-card-line"><?php echo esc_html($item['place']); ?></p>
                                            <p class="carousel-card-line"><?php echo esc_html($item['date']); ?></p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="carousel-pagination" id="carouselPagination">
                            <div class="carousel-dots" id="carouselDots">
                                <?php foreach ($carousel_items as $index => $item) : ?>
                                    <span class="carousel-dot<?php echo $index === 0 ? ' active' : ''; ?>" data-index="<?php echo esc_attr($index); ?>"></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel-counter" id="carouselCounter"><?php echo esc_html__('1 de 9', 'vw'); ?></div>
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <section class="screen-2" id="screen2" style="display: none;">
            <div class="results-layout vw-s2-layout">
                <div class="vw-s2-left">
                    <div class="results-grid-container">
                        <div class="results-grid" id="resultsGrid">
                            <!-- Fichas se generan dinámicamente con JS -->
                        </div>

                        <div id="infiniteScrollLoader" class="infinite-scroll-loader" aria-live="polite">
                            <div class="spinner" aria-hidden="true"></div>
                        </div>

                        <div id="infiniteScrollEnd" class="infinite-scroll-end">
                            <?php echo esc_html__('No hay más resultados', 'vw'); ?>
                        </div>
                    </div>
                </div>

                <aside class="vw-s2-right">
                    <div class="results-map-container">
                        <div class="results-map" id="resultsMap">
                            <div class="map-placeholder">
                                <span class="map-placeholder-icon" aria-hidden="true">🗺️</span>
                                <p><?php echo esc_html__('Mapa de resultados', 'vw'); ?></p>
                                <p class="map-placeholder-note"><?php echo esc_html__('Google Maps se integrará en PHP', 'vw'); ?></p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</div>

<?php
get_footer();
?>