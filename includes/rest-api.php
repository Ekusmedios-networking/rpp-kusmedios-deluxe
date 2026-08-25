<?php
defined( \'ABSPATH\' ) || exit;

add_action( \'rest_api_init\', function() {
    register_rest_route( \'rpkus/v1\', \'/nowplaying/(?P<id>\\d+)\', [
        \'methods\'             => \'GET\',
        \'callback\'            => \'rpkus_rest_nowplaying\',
        \'permission_callback\' => \'__return_true\',
    ] );
    register_rest_route( \'rpkus/v1\', \'/history/(?P<id>\\d+)\', [
        \'methods\'             => \'GET\',
        \'callback\'            => \'rpkus_rest_history\',
        \'permission_callback\' => \'__return_true\',
    ] );
} );

function rpkus_rest_nowplaying( WP_REST_Request $request ): WP_REST_Response {
    $post_id  = (int) $request->get_param( \'id\' );
    $platform = get_post_meta( $post_id, \'_rpkus_platform\', true );
    $base_url = rtrim( get_post_meta( $post_id, \'_rpkus_platform_base_url\', true ), \'/\' );
    $api_key  = get_post_meta( $post_id, \'_rpkus_api_key\', true );
    if ( ! $platform || ( ! $base_url && $platform !== \'zenofm\' ) ) {
        return new WP_REST_Response( [ \'error\' => \'not_configured\' ], 404 );
    }
    $cache_key = \'rpkus_np_\' . md5( $post_id . $platform );
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) return new WP_REST_Response( $cached, 200 );
    [ $endpoint, $headers ] = rpkus_build_endpoint( $platform, $base_url, $post_id, $api_key );
    if ( ! $endpoint ) return new WP_REST_Response( [ \'error\' => \'unsupported_platform\' ], 400 );
    $resp = wp_remote_get( $endpoint, [ \'timeout\' => 8, \'headers\' => $headers ] );
    if ( is_wp_error( $resp ) ) return new WP_REST_Response( [ \'error\' => $resp->get_error_message() ], 502 );
    $data   = json_decode( wp_remote_retrieve_body( $resp ), true );
    $result = rpkus_normalize_nowplaying( $platform, $data );
    set_transient( $cache_key, $result, 13 );
    return new WP_REST_Response( $result, 200 );
}

function rpkus_rest_history( WP_REST_Request $request ): WP_REST_Response {
    $post_id  = (int) $request->get_param( \'id\' );
    $platform = get_post_meta( $post_id, \'_rpkus_platform\', true );
    $base_url = rtrim( get_post_meta( $post_id, \'_rpkus_platform_base_url\', true ), \'/\' );
    $count    = (int) get_post_meta( $post_id, \'_rpkus_history_count\', true ) ?: 10;
    if ( $platform !== \'azuracast\' ) return new WP_REST_Response( [ \'error\' => \'history_only_azuracast\' ], 400 );
    $azura_id = get_post_meta( $post_id, \'_rpkus_azura_station_id\', true ) ?: \'1\';
    $endpoint = $base_url . \'/api/station/\' . $azura_id . \'/history?rows=\' . $count;
    $api_key  = get_post_meta( $post_id, \'_rpkus_api_key\', true );
    $headers  = $api_key ? [ \'X-API-Key\' => $api_key ] : [];
    $cache_key = \'rpkus_hist_\' . md5( $post_id );
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) return new WP_REST_Response( $cached, 200 );
    $resp = wp_remote_get( $endpoint, [ \'timeout\' => 8, \'headers\' => $headers ] );
    if ( is_wp_error( $resp ) ) return new WP_REST_Response( [ \'error\' => $resp->get_error_message() ], 502 );
    $data = json_decode( wp_remote_retrieve_body( $resp ), true );
    $history = [];
    if ( is_array( $data ) ) {
        foreach ( array_slice( $data, 0, $count ) as $item ) {
            $song = $item[\'song\'] ?? $item;
            $history[] = [
                \'title\'   => $song[\'title\'] ?? \'\'  ,
                \'artist\'  => $song[\'artist\'] ?? \'\'  ,
                \'artwork\'  => $song[\'art\'] ?? \'\'  ,
                \'played_at\' => $item[\'played_at\'] ?? \'\',
            ];
        }
    }
    set_transient( $cache_key, $history, 60 );
    return new WP_REST_Response( $history, 200 );
}

function rpkus_build_endpoint( string $platform, string $base_url, int $post_id, string $api_key ): array {
    $headers = [];
    switch ( $platform ) {
        case \'azuracast\':
            $sid      = get_post_meta( $post_id, \'_rpkus_azura_station_id\', true ) ?: \'1\';
            $endpoint = $base_url . \'/api/nowplaying/\' . $sid;
            if ( $api_key ) $headers[\'X-API-Key\'] = $api_key;
            break;
        case \'zenofm\':
            $zeno_id  = get_post_meta( $post_id, \'_rpkus_zeno_station_id\', true );
            $endpoint = \'https://api.zeno.fm/mounts/icestats/sub/\' . urlencode( $zeno_id ) . \'/current\';
            break;
        case \'sonicpanel\':
            $port     = get_post_meta( $post_id, \'_rpkus_sonic_port\', true ) ?: \'8000\';
            $endpoint = $base_url . \':\' . $port . \'/stats?json=1\';
            if ( $api_key ) $headers[\'Authorization\'] = \'Bearer \' . $api_key;
            break;
        case \'shoutcast\':
            $sid      = get_post_meta( $post_id, \'_rpkus_sc_sid\', true ) ?: \'1\';
            $endpoint = $base_url . \'/statistics?json=1&sid=\' . $sid;
            break;
        case \'icecast\':
            $endpoint = $base_url . \'/status-json.xsl\';
            break;
        default:
            return [ \'\', [] ];
    }
    return [ $endpoint, $headers ];
}

function rpkus_normalize_nowplaying( string $platform, ?array $data ): array {
    $empty = [ \'artist\' => \'\', \'title\' => \'\', \'artwork\' => \'\', \'listeners\' => 0, \'is_live\' => false, \'dj_name\' => \'\', \'next_song\' => \'\'  ];
    if ( ! $data ) return $empty;
    switch ( $platform ) {
        case \'azuracast\':
            $np   = $data[\'now_playing\'] ?? [];
            $song = $np[\'song\'] ?? [];
            return [
                \'artist\'    => $song[\'artist\'] ?? \'\',
                \'title\'     => $song[\'title\'] ?? \'\',
                \'artwork\'   => $song[\'art\'] ?? \'\',
                \'listeners\' => $data[\'listeners\'][\'current\'] ?? 0,
                \'is_live\'   => ! empty( $data[\'live\'][\'is_live\'] ),
                \'dj_name\'   => $data[\'live\'][\'streamer_name\'] ?? \'\',
                \'next_song\' => ( $data[\'playing_next\'][\'song\'][\'text\'] ?? \'\' ),
            ];
        case \'zenofm\':
            $src   = $data[\'icestats\'][\'source\'] ?? [];
            $title = $src[\'title\'] ?? \'\';
            $parts = explode( \' - \', $title, 2 );
            return array_merge( $empty, [\'artist\' => $parts[0] ?? \'\', \'title\' => $parts[1] ?? $title, \'listeners\' => (int)( $src[\'listeners\'] ?? 0 )] );
        case \'shoutcast\':
            $title = $data[\'songtitle\'] ?? \'\';
            $parts = explode( \' - \', $title, 2 );
            return array_merge( $empty, [\'artist\' => $parts[0] ?? \'\', \'title\' => $parts[1] ?? $title, \'listeners\' => (int)( $data[\'currentlisteners\'] ?? 0 )] );
        case \'icecast\':
            $src   = $data[\'icestats\'][\'source\'] ?? [];
            $src   = isset( $src[0] ) ? $src[0] : $src;
            $title = $src[\'title\'] ?? \'\';
            $parts = explode( \' - \', $title, 2 );
            return array_merge( $empty, [\'artist\' => $parts[0] ?? \'\', \'title\' => $parts[1] ?? $title, \'listeners\' => (int)( $src[\'listeners\'] ?? 0 )] );
        default:
            return $empty;
    }
}

add_action( \'wp_ajax_rpkus_test_connection\', function() {
    check_ajax_referer( \'rpkus_np_nonce\', \'nonce\' );
    if ( ! current_user_can( \'manage_options\' ) ) wp_die( \'Forbidden\', 403 );
    $platform = sanitize_text_field( $_POST[\'platform\'] ?? \'\' );
    $base_url = esc_url_raw( $_POST[\'base_url\'] ?? \'\' );
    $extra    = sanitize_text_field( $_POST[\'extra\'] ?? \'\' );
    switch ( $platform ) {
        case \'azuracast\':
            $ep = rtrim( $base_url, \'/\' ) . \'/api/nowplaying/\' . ( $extra ?: \'1\' ); break;
        case \'zenofm\':
            $ep = \'https://api.zeno.fm/mounts/icestats/sub/\' . urlencode( $extra ) . \'/current\'; break;
        case \'sonicpanel\':
            $ep = rtrim( $base_url, \'/\' ) . \':\' . ( $extra ?: \'8000\' ) . \'/stats?json=1\'; break;
        case \'shoutcast\':
            $ep = rtrim( $base_url, \'/\' ) . \'/statistics?json=1&sid=1\'; break;
        case \'icecast\':
            $ep = rtrim( $base_url, \'/\' ) . \'/status-json.xsl\'; break;
        default: wp_send_json_error( \'Plataforma no reconocida\' ); return;
    }
    $r = wp_remote_get( $ep, [ \'timeout\' => 8 ] );
    if ( is_wp_error( $r ) ) { wp_send_json_error( $r->get_error_message() ); return; }
    $code = wp_remote_retrieve_response_code( $r );
    $code >= 200 && $code < 300 ? wp_send_json_success( [ \'code\' => $code, \'endpoint\' => $ep ] ) : wp_send_json_error( "HTTP $code" );
} );
