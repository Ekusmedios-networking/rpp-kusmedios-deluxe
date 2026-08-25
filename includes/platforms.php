<?php
defined( \'ABSPATH\' ) || exit;

function rpkus_get_platforms(): array {
    return [
        \'\'          => __( \'— Selecciona plataforma —\', \'rpp-kusmedios\' ),
        \'azuracast\' => \'Azuracast\',
        \'zenofm\'    => \'ZenoFM\',
        \'sonicpanel\'=> \'SonicPanel\',
        \'shoutcast\' => \'Shoutcast v2\',
        \'icecast\'   => \'Icecast\',
        \'manual\'    => __( \'URL Manual / Otro\', \'rpp-kusmedios\' ),
    ];
}
