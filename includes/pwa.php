<?php
defined( \'ABSPATH\' ) || exit;

/**
 * Outputs PWA manifest link on station player pages when enabled.
 */
add_action( \'wp_head\', function() {
    global $post;
    if ( ! $post ) return;
    $stations = function_exists( \'radplapag_get_stations\' ) ? radplapag_get_stations() : [];
    foreach ( $stations as $station ) {
        if ( (int) ( $station[\'player_page\'] ?? 0 ) === (int) $post->ID ) {
            $post_id = $station[\'id\'];
            if ( get_post_meta( $post_id, \'_rpkus_pwa_enabled\', true ) ) {
                $manifest_url = add_query_arg( [ \'rpkus_pwa_manifest\' => $post_id ], home_url( \'/\' ) );
                echo \'<link rel="manifest" href="\' . esc_url( $manifest_url ) . \'">\';
                echo \'<meta name="theme-color" content="#e50000">\';
            }
            break;
        }
    }
} );

add_action( \'init\', function() {
    if ( ! isset( $_GET[\'rpkus_pwa_manifest\'] ) ) return;
    $post_id    = (int) $_GET[\'rpkus_pwa_manifest\'];
    $title      = get_the_title( $post_id ) ?: get_bloginfo( \'name\' );
    $logo_id    = (int) get_post_meta( $post_id, \'radplapag_station_logo_id\', true );
    $logo_url   = $logo_id ? wp_get_attachment_image_url( $logo_id, \'full\' ) : \'\' ;
    $player_url = get_permalink( (int) get_post_meta( $post_id, \'radplapag_station_player_page\', true ) );
    $manifest   = [
        \'name\'             => $title,
        \'short_name\'       => mb_substr( $title, 0, 12 ),
        \'start_url\'        => $player_url ?: home_url( \'/\' ),
        \'display\'          => \'standalone\',
        \'background_color\' => \'#0a0a0a\',
        \'theme_color\'      => \'#e50000\',
        \'icons\'            => $logo_url ? [
            [ \'src\' => $logo_url, \'sizes\' => \'192x192\', \'type\' => \'image/png\' ],
            [ \'src\' => $logo_url, \'sizes\' => \'512x512\', \'type\' => \'image/png\' ],
        ] : [],
    ];
    header( \'Content-Type: application/manifest+json; charset=utf-8\' );
    echo wp_json_encode( $manifest );
    exit;
} );
