<?php
/** @var WP_Post $post */
/** @var string $platform, $base_url, $mount, $azura_id, $api_key, $zeno_id, $s_port, $sc_sid, $ic_mount */
/** @var bool $sync_m, $sync_s, $pwa */
/** @var string $chat_url, $req_url */
/** @var int $history */
defined( 'ABSPATH' ) || exit;
$platforms = rpkus_get_platforms();
?>
<div id="rpkus-wrapper">

  <!-- PLATFORM SELECTOR -->
  <div class="rpkus-row">
    <label for="rpkus_platform"><strong><?php esc_html_e( 'Plataforma', 'rpp-kusmedios' ); ?></strong></label>
    <select id="rpkus_platform" name="rpkus_platform" class="rpkus-select">
      <?php foreach ( $platforms as $val => $label ) : ?>
        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $platform, $val ); ?>><?php echo esc_html( $label ); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- BASE URL -->
  <div class="rpkus-row rpkus-show" id="rpkus-row-base-url">
    <label for="rpkus_base_url">
      <strong><?php esc_html_e( 'URL Base del Servidor', 'rpp-kusmedios' ); ?></strong>
      <span class="rpkus-hint" id="rpkus-hint-base"></span>
    </label>
    <div class="rpkus-input-row">
      <input type="url" id="rpkus_base_url" name="rpkus_base_url" class="rpkus-input" value="<?php echo esc_attr( $base_url ); ?>" placeholder="https://radio.ejemplo.com" />
      <button type="button" id="rpkus-test-btn" class="button"><?php esc_html_e( 'Probar', 'rpp-kusmedios' ); ?></button>
    </div>
    <span id="rpkus-test-result" class="rpkus-test-result"></span>
  </div>

  <!-- AZURACAST FIELDS -->
  <div class="rpkus-panel" data-platform="azuracast">
    <div class="rpkus-row">
      <label for="rpkus_azura_station_id"><strong><?php esc_html_e( 'Station ID', 'rpp-kusmedios' ); ?></strong> <span class="rpkus-hint">/station/<strong>1</strong></span></label>
      <input type="number" id="rpkus_azura_station_id" name="rpkus_azura_station_id" class="rpkus-short" value="<?php echo esc_attr( $azura_id ); ?>" min="1" />
    </div>
    <div class="rpkus-row">
      <label for="rpkus_azura_mount"><strong><?php esc_html_e( 'Mount Point', 'rpp-kusmedios' ); ?></strong></label>
      <input type="text" id="rpkus_azura_mount" name="rpkus_azura_mount" class="rpkus-input" value="<?php echo esc_attr( $mount ); ?>" placeholder="/radio.mp3" />
    </div>
    <div class="rpkus-row">
      <label for="rpkus_api_key"><strong><?php esc_html_e( 'API Key (opcional)', 'rpp-kusmedios' ); ?></strong></label>
      <input type="password" id="rpkus_api_key" name="rpkus_api_key" class="rpkus-input" value="<?php echo esc_attr( $api_key ); ?>" />
    </div>
    <div class="rpkus-info">
      <code id="rpkus-azura-ep"><?php echo esc_html( $base_url ? rtrim( $base_url, '/' ) . '/api/nowplaying/' . $azura_id : 'https://tu-servidor/api/nowplaying/1' ); ?></code>
    </div>
  </div>

  <!-- ZENOFM FIELDS -->
  <div class="rpkus-panel" data-platform="zenofm">
    <div class="rpkus-row">
      <label for="rpkus_zeno_id"><strong><?php esc_html_e( 'Station ID ZenoFM', 'rpp-kusmedios' ); ?></strong></label>
      <input type="text" id="rpkus_zeno_id" name="rpkus_zeno_station_id" class="rpkus-input" value="<?php echo esc_attr( $zeno_id ); ?>" placeholder="abc123" />
    </div>
    <div class="rpkus-info"><code>https://stream.zeno.fm/<?php echo esc_html( $zeno_id ?: 'TU-ID' ); ?></code></div>
  </div>

  <!-- SONICPANEL FIELDS -->
  <div class="rpkus-panel" data-platform="sonicpanel">
    <div class="rpkus-row">
      <label for="rpkus_sonic_port"><strong><?php esc_html_e( 'Puerto Stream', 'rpp-kusmedios' ); ?></strong></label>
      <input type="number" id="rpkus_sonic_port" name="rpkus_sonic_port" class="rpkus-short" value="<?php echo esc_attr( $s_port ); ?>" min="1" max="65535" />
    </div>
  </div>

  <!-- SHOUTCAST FIELDS -->
  <div class="rpkus-panel" data-platform="shoutcast">
    <div class="rpkus-row">
      <label for="rpkus_sc_sid"><strong><?php esc_html_e( 'Stream ID (SID)', 'rpp-kusmedios' ); ?></strong></label>
      <input type="number" id="rpkus_sc_sid" name="rpkus_sc_sid" class="rpkus-short" value="<?php echo esc_attr( $sc_sid ); ?>" min="1" />
    </div>
  </div>

  <!-- ICECAST FIELDS -->
  <div class="rpkus-panel" data-platform="icecast">
    <div class="rpkus-row">
      <label for="rpkus_ic_mount"><strong><?php esc_html_e( 'Mount Point', 'rpp-kusmedios' ); ?></strong></label>
      <input type="text" id="rpkus_ic_mount" name="rpkus_ic_mount" class="rpkus-input" value="<?php echo esc_attr( $ic_mount ); ?>" placeholder="/stream" />
    </div>
  </div>

  <!-- GENERATED STREAM URL -->
  <div class="rpkus-panel-global rpkus-show" id="rpkus-stream-section">
    <h4 class="rpkus-title">🔗 <?php esc_html_e( 'Stream URL generada', 'rpp-kusmedios' ); ?></h4>
    <div class="rpkus-input-row">
      <input type="text" id="rpkus-stream-out" class="rpkus-input" readonly />
      <button type="button" id="rpkus-copy" class="button"><?php esc_html_e( 'Copiar', 'rpp-kusmedios' ); ?></button>
      <button type="button" id="rpkus-apply" class="button button-primary"><?php esc_html_e( 'Aplicar al Player', 'rpp-kusmedios' ); ?></button>
    </div>
  </div>

  <!-- SYNC & EXTRAS -->
  <div class="rpkus-panel-global rpkus-show" id="rpkus-extras">
    <h4 class="rpkus-title">⚡ <?php esc_html_e( 'Funciones Deluxe', 'rpp-kusmedios' ); ?></h4>

    <label class="rpkus-check"><input type="checkbox" name="rpkus_sync_metadata" value="1" <?php checked( $sync_m ); ?> />
      <?php esc_html_e( 'Sincronizar metadatos Now Playing (canción, artista, artwork)', 'rpp-kusmedios' ); ?></label>

    <label class="rpkus-check rpkus-azura-only"><input type="checkbox" name="rpkus_sync_schedule" value="1" <?php checked( $sync_s ); ?> />
      <?php esc_html_e( 'Sincronizar programación desde Azuracast', 'rpp-kusmedios' ); ?></label>

    <label class="rpkus-check"><input type="checkbox" name="rpkus_pwa_enabled" value="1" <?php checked( $pwa ); ?> />
      <?php esc_html_e( 'Activar PWA (instalar como app)', 'rpp-kusmedios' ); ?></label>

    <div class="rpkus-row">
      <label for="rpkus_history_count"><strong><?php esc_html_e( 'Historial de canciones (número)', 'rpp-kusmedios' ); ?></strong></label>
      <input type="number" id="rpkus_history_count" name="rpkus_history_count" class="rpkus-short" value="<?php echo esc_attr( $history ); ?>" min="1" max="50" />
    </div>

    <div class="rpkus-row">
      <label for="rpkus_chat_url"><strong><?php esc_html_e( 'URL Chat Embed (Discord/Telegram/LiveChat)', 'rpp-kusmedios' ); ?></strong></label>
      <input type="url" id="rpkus_chat_url" name="rpkus_chat_url" class="rpkus-input" value="<?php echo esc_attr( $chat_url ); ?>" placeholder="https://discord.com/widget?id=..." />
    </div>

    <div class="rpkus-row">
      <label for="rpkus_request_url"><strong><?php esc_html_e( 'URL Solicitud de canciones (WhatsApp/Telegram/Form)', 'rpp-kusmedios' ); ?></strong></label>
      <input type="url" id="rpkus_request_url" name="rpkus_request_url" class="rpkus-input" value="<?php echo esc_attr( $req_url ); ?>" placeholder="https://wa.me/521..." />
    </div>
  </div>

</div>
