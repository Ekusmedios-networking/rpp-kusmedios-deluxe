/* RPP Kusmedios Deluxe – Admin JS */
(function ($) {
  \'use strict\';
  var D = window.rpkusData || {};
  var S = D.strings || {};

  var platformHints = {
    azuracast:  \'https://tu-azuracast.com (sin /api)\',
    zenofm:     \'No aplica – solo ingresa tu Station ID\',
    sonicpanel: \'https://tu-servidor.com (sin puerto)\',
    shoutcast:  \'https://tu-servidor.com:8000\',
    icecast:    \'https://tu-servidor.com:8000\',
    manual:     \'Ingresa la URL completa del stream\',
  };

  function getExtraParam(platform) {
    if (platform === \'azuracast\') return $(\'#rpkus_azura_station_id\').val();
    if (platform === \'zenofm\')    return $(\'#rpkus_zeno_id\').val();
    if (platform === \'sonicpanel\') return $(\'#rpkus_sonic_port\').val();
    return \'\';
  }

  function buildStreamUrl(platform, base) {
    base = (base || \'\').replace(/\\/+$/, \'\');
    switch (platform) {
      case \'azuracast\':
        var sid   = $(\'#rpkus_azura_station_id\').val() || \'1\';
        var mount = $(\'#rpkus_azura_mount\').val() || \'/radio.mp3\';
        return base + \'/listen/\' + sid + mount;
      case \'zenofm\':
        return \'https://stream.zeno.fm/\' + ($(\'#rpkus_zeno_id\').val() || \'\');
      case \'sonicpanel\':
        var port = $(\'#rpkus_sonic_port\').val() || \'8000\';
        return base + \':\' + port + \'/stream\';
      case \'shoutcast\':
        return base + \';stream.mp3\';
      case \'icecast\':
        return base + ($(\'#rpkus_ic_mount\').val() || \'/stream\');
      default:
        return base;
    }
  }

  function updateUI() {
    var platform = $(\'#rpkus_platform\').val();
    // Show/hide platform-specific panels
    $(\'[data-platform]\').removeClass(\'rpkus-active\');
    if (platform) $(\'[data-platform="\' + platform + \'"]\'). addClass(\'rpkus-active\');
    // Show global panels when platform is selected
    var hasPlat = platform && platform !== \'\' && platform !== \'manual\';
    $(\'#rpkus-row-base-url, #rpkus-stream-section, #rpkus-extras\').toggle(!!platform);
    // ZenoFM: hide base URL row
    $(\'#rpkus-row-base-url\').toggle(platform !== \'zenofm\' && !!platform);
    // Hint
    $(\'#rpkus-hint-base\').text(platformHints[platform] || \'\');
    // Azuracast-only fields
    $(\'#rpkus-azura-ep\').text(buildAzuraEndpoint());
    if (platform === \'azuracast\') $(\'  .rpkus-azura-only\').addClass(\'rpkus-visible\');
    else $(\'  .rpkus-azura-only\').removeClass(\'rpkus-visible\');
    // Update stream preview
    updateStreamPreview();
  }

  function buildAzuraEndpoint() {
    var base = $(\'#rpkus_base_url\').val().replace(/\\/+$/, \'\');
    var sid  = $(\'#rpkus_azura_station_id\').val() || \'1\';
    return base ? base + \'/api/nowplaying/\' + sid : \'https://tu-servidor/api/nowplaying/1\';
  }

  function updateStreamPreview() {
    var platform = $(\'#rpkus_platform\').val();
    var base     = $(\'#rpkus_base_url\').val();
    if (!platform) { $(\'#rpkus-stream-out\').val(\'\'); return; }
    $(\'#rpkus-stream-out\').val(buildStreamUrl(platform, base));
  }

  $(document).ready(function () {
    updateUI();

    $(\'#rpkus_platform\').on(\'change\', updateUI);
    $(\'#rpkus_base_url, #rpkus_azura_station_id, #rpkus_azura_mount, #rpkus_zeno_id, #rpkus_sonic_port, #rpkus_ic_mount\').on(\'input change\', updateStreamPreview);
    $(\'#rpkus_azura_station_id\').on(\'input\', function () { $(\'#rpkus-azura-ep\').text(buildAzuraEndpoint()); });

    // Test connection
    $(\'#rpkus-test-btn\').on(\'click\', function () {
      var $btn    = $(this);
      var $result = $(\'#rpkus-test-result\');
      var platform = $(\'#rpkus_platform\').val();
      var base_url = $(\'#rpkus_base_url\').val();
      $btn.prop(\'disabled\', true).text(S.testing || \'Probando…\');
      $result.text(\'\').removeClass(\'ok fail\');
      $.post(D.ajax_url, {
        action:   \'rpkus_test_connection\',
        nonce:    D.nonce,
        platform: platform,
        base_url: base_url,
        extra:    getExtraParam(platform),
      }, function (resp) {
        $btn.prop(\'disabled\', false).text(\'Probar\');
        if (resp.success) {
          $result.text(S.test_ok).addClass(\'ok\');
        } else {
          $result.text((S.test_fail || \'Error\') + \' — \' + (resp.data || \'\') ).addClass(\'fail\');
        }
      }).fail(function () {
        $btn.prop(\'disabled\', false).text(\'Probar\');
        $result.text(S.test_fail).addClass(\'fail\');
      });
    });

    // Copy stream URL
    $(\'#rpkus-copy\').on(\'click\', function () {
      var val = $(\'#rpkus-stream-out\').val();
      if (!val) return;
      navigator.clipboard.writeText(val).then(function () {
        var $btn = $(\'#rpkus-copy\');
        $btn.text(S.copied || \'¡Copiado!\');
        setTimeout(function () { $btn.text(\'Copiar\'); }, 2000);
      });
    });

    // Apply stream URL to RPP field
    $(\'#rpkus-apply\').on(\'click\', function () {
      var url = $(\'#rpkus-stream-out\').val();
      if (!url) return;
      var $rppField = $(\'#radplapag_station_stream_url\');
      if ($rppField.length) {
        $rppField.val(url).trigger(\'input\');
        var $btn = $(\'#rpkus-apply\');
        $btn.text(S.applied || \'¡Aplicado!\');
        setTimeout(function () { $btn.text(\'Aplicar al Player\'); }, 2500);
      }
    });
  });
}(jQuery));
