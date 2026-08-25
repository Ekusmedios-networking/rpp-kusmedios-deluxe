<?php
/**
 * Plugin Name: RPP Kusmedios Deluxe
 * Description: Extiende Radio Player Page con selector de plataforma de streaming (Azuracast, ZenoFM, SonicPanel, Shoutcast, Icecast), Now Playing en tiempo real, historial de canciones, PWA, chat embed y branding personalizado para estacionkusmedios.org
 * Version: 1.0.0
 * Author: Kusmedios
 * Author URI: https://estacionkusmedios.org
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * Text Domain: rpp-kusmedios
 * Requires Plugins: radio-player-page
 */

defined( \'ABSPATH\' ) || exit;

define( \'RPKUS_VERSION\', \'1.0.0\' );
define( \'RPKUS_FILE\',    __FILE__ );
define( \'RPKUS_DIR\',     plugin_dir_path( __FILE__ ) );
define( \'RPKUS_URL\',     plugin_dir_url( __FILE__ ) );

require_once RPKUS_DIR . \'includes/platforms.php\';
require_once RPKUS_DIR . \'includes/admin-metabox.php\';
require_once RPKUS_DIR . \'includes/rest-api.php\';
require_once RPKUS_DIR . \'includes/save-meta.php\';
require_once RPKUS_DIR . \'includes/stream-autofill.php\';
require_once RPKUS_DIR . \'includes/pwa.php\';
