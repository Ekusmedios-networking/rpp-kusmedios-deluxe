<?php
defined( \'ABSPATH\' ) || exit;

add_action( \'admin_enqueue_scripts\', function( $hook ) {
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== \'radplapag_station\' ) return;
    wp_enqueue_style( \'rpkus-admin\', RPKUS_URL . \'assets/admin.css\', [\'radplapag-admin\'], RPKUS_VERSION );
    wp_enqueue_script( \'rpkus-admin-js\', RPKUS_URL . \'assets/admin.js\', [\'jquery\'], RPKUS_VERSION, true );
    wp_localize_script( \'rpkus-admin-js\', \'rpkusData\', [
        \'nonce\'    => wp_create_nonce( \'rpkus_np_nonce\' ),
        \'ajax_url\' => admin_url( \'admin-ajax.php\' ),
        \'strings\'  => [
            \'test_ok\'   => __( \'Conexión exitosa ✓\', \'rpp-kusmedios\' ),
            \'test_fail\' => __( \'Error al conectar. Revisa la URL.\', \'rpp-kusmedios\' ),
            \'testing\'   => __( \'Probando…\', \'rpp-kusmedios\' ),
            \'copied\'    => __( \'¡Copiado!\', \'rpp-kusmedios\' ),
            \'applied\'   => __( \'¡Aplicado!\', \'rpp-kusmedios\' ),
        ],
    ] );
} );

add_action( \'add_meta_boxes\', function() {
    add_meta_box(
        \'rpkus_platform_box\',
        \'🎙️ \' . __( \'Plataforma de Streaming – Kusmedios Deluxe\', \'rpp-kusmedios\' ),
        \'rpkus_platform_meta_box_html\',
        \'radplapag_station\',
        \'normal\',
        \'high\'
    );
} );

function rpkus_platform_meta_box_html( WP_Post $post ): void {
    $id       = $post->ID;
    $platform = get_post_meta( $id, \'_rpkus_platform\', true )          ?: \'\';
    $base_url = get_post_meta( $id, \'_rpkus_platform_base_url\', true ) ?: \'\';
    $mount    = get_post_meta( $id, \'_rpkus_azura_mount\', true )       ?: \'\';
    $azura_id = get_post_meta( $id, \'_rpkus_azura_station_id\', true )  ?: \'1\';
    $api_key  = get_post_meta( $id, \'_rpkus_api_key\', true )           ?: \'\';
    $zeno_id  = get_post_meta( $id, \'_rpkus_zeno_station_id\', true )   ?: \'\';
    $s_port   = get_post_meta( $id, \'_rpkus_sonic_port\', true )        ?: \'8000\';
    $sc_sid   = get_post_meta( $id, \'_rpkus_sc_sid\', true )            ?: \'1\';
    $ic_mount = get_post_meta( $id, \'_rpkus_ic_mount\', true )          ?: \'/stream\';
    $sync_m   = (bool) get_post_meta( $id, \'_rpkus_sync_metadata\', true );
    $sync_s   = (bool) get_post_meta( $id, \'_rpkus_sync_schedule\', true );
    $pwa      = (bool) get_post_meta( $id, \'_rpkus_pwa_enabled\', true );
    $chat_url = get_post_meta( $id, \'_rpkus_chat_url\', true )          ?: \'\';
    $req_url  = get_post_meta( $id, \'_rpkus_request_url\', true )       ?: \'\';
    $history  = (int) get_post_meta( $id, \'_rpkus_history_count\', true ) ?: 10;
    wp_nonce_field( \'rpkus_save_platform\', \'rpkus_nonce\' );
    include RPKUS_DIR . \'templates/metabox.php\';
}
