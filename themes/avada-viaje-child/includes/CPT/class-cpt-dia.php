<?php
declare(strict_types=1);

namespace Viaje\Core\CPT;

if (!defined('ABSPATH')) { exit; }

class Dia
{
    public static function register(): void
    {
        add_action('init', function () {
            $labels = [
                'name'               => 'Días',
                'singular_name'      => 'Día',
                'menu_name'          => 'Días',
                'name_admin_bar'     => 'Día',
                'add_new'            => 'Añadir nuevo',
                'add_new_item'       => 'Añadir nuevo día',
                'new_item'           => 'Nuevo día',
                'edit_item'          => 'Editar día',
                'view_item'          => 'Ver día',
                'all_items'          => 'Todos los días',
            ];
            $args = [
                'labels'             => $labels,
                'public'             => true,
                'show_in_menu'       => true,
                'supports'           => ['title', 'editor', 'thumbnail'],
                'has_archive'        => false,
                'rewrite'            => ['slug' => 'dia'],
            ];
            register_post_type('dia', $args);
        });
    }
}

// Al cargar este archivo, registramos el CPT en init.
add_action('init', [\Viaje\Core\CPT\CPT_Dia::class, 'register']);
