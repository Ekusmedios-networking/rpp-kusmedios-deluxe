<?php
defined( \'ABSPATH\' ) || exit;

add_action( \'save_post_radplapag_station\', function( int $post_id ) {
    if ( defined( \'DOING_AUTOSAVE\' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( \'manage_options\' ) ) return;
    $platform = get_post_meta( $post_id, \'_rpkus_platform\', true );
    $base_url = rtrim( get_post_meta( $post_id, \'_rpkus_platform_base_url\', true ), \'/\' );
    if ( ! $platform || ! $base_url ) return;
    $existing = get_post_meta( $post_id, \'radplapag_station_stream_url\', true );
    if ( $existing ) return;
    $url = \'\';
    switch ( $platform ) {
        case \'azuracast\':
            $mount = get_post_meta( $post_id, \'_rpkus_azura_mount\', true ) ?: \'/radio.mp3\';
            $sid   = get_post_meta( $post_id, \'_rpkus_azura_station_id\', true ) ?: \'1\';
            $url   = $base_url . \'/listen/\' . $sid . $mount;
            break;
        case \'zenofm\':
            $url = \'https://stream.zeno.fm/\' . get_post_meta( $post_id, \'_rpkus_zeno_station_id\', true );
            break;
        case \'sonicpanel\':
            $port = get_post_meta( $post_id, \'_rpkus_sonic_port\', true ) ?: \'8000\';
            $url  = $base_url . \':\' . $port . \'/stream\';
            break;
        case \'shoutcast\':
            $url = $base_url . \';stream.mp3\';
            break;
        case \'icecast\':
            $url = $base_url . ( get_post_meta( $post_id, \'_rpkus_ic_mount\', true ) ?: \'/stream\' );
            break;
    }
    if ( $url ) update_post_meta( $post_id, \'radplapag_station_stream_url\', esc_url_raw( $url ) );
}, 20, 1 );
