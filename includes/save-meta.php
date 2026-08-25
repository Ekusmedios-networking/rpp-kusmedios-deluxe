<?php
defined( \'ABSPATH\' ) || exit;

add_action( \'save_post_radplapag_station\', function( int $post_id ) {
    if ( ! isset( $_POST[\'rpkus_nonce\'] ) || ! wp_verify_nonce( $_POST[\'rpkus_nonce\'], \'rpkus_save_platform\' ) ) return;
    if ( defined( \'DOING_AUTOSAVE\' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( \'manage_options\' ) ) return;
    $text_fields = [
        \'_rpkus_platform\'          => \'rpkus_platform\',
        \'_rpkus_platform_base_url\' => \'rpkus_base_url\',
        \'_rpkus_azura_mount\'       => \'rpkus_azura_mount\',
        \'_rpkus_azura_station_id\'  => \'rpkus_azura_station_id\',
        \'_rpkus_api_key\'           => \'rpkus_api_key\',
        \'_rpkus_zeno_station_id\'   => \'rpkus_zeno_station_id\',
        \'_rpkus_sonic_port\'        => \'rpkus_sonic_port\',
        \'_rpkus_sc_sid\'            => \'rpkus_sc_sid\',
        \'_rpkus_ic_mount\'          => \'rpkus_ic_mount\',
        \'_rpkus_chat_url\'          => \'rpkus_chat_url\',
        \'_rpkus_request_url\'       => \'rpkus_request_url\',
        \'_rpkus_history_count\'     => \'rpkus_history_count\',
    ];
    foreach ( $text_fields as $meta => $post_key ) {
        if ( isset( $_POST[ $post_key ] ) ) {
            update_post_meta( $post_id, $meta, sanitize_text_field( $_POST[ $post_key ] ) );
        }
    }
    $bool_fields = [ \'_rpkus_sync_metadata\' => \'rpkus_sync_metadata\', \'_rpkus_sync_schedule\' => \'rpkus_sync_schedule\', \'_rpkus_pwa_enabled\' => \'rpkus_pwa_enabled\' ];
    foreach ( $bool_fields as $meta => $post_key ) {
        update_post_meta( $post_id, $meta, ! empty( $_POST[ $post_key ] ) ? \'1\' : \'\' );
    }
}, 10, 1 );
