<?php
/**
 * Plugin Name: LELE-Traccar
 * Description: Ein Plugin zur Integration von Traccar in WordPress für LELE Fleet.
 * Version: 1.0
 * Author: Leandro Ries - LELE Production GbR
 */

include('lele-traccar-post-meta.php');
require_once plugin_dir_path(__FILE__) . 'debug.php';
include_once('lele-traccar-api.php');
foreach ( glob( plugin_dir_path( __FILE__ ) . 'widgets/*.php' ) as $file ) {
    include_once $file;
}
// lele-traccar-plugin.php
// Funktion, die bei der Aktivierung des Plugins aufgerufen wird
function lele_traccar_activation() {
    // TODO: Logik für die Aktivierung
}
register_activation_hook(__FILE__, 'lele_traccar_activation');

// Funktion, die bei der Deaktivierung des Plugins aufgerufen wird
function lele_traccar_deactivation() {
    // TODO: Logik für die Deaktivierung
}
register_deactivation_hook(__FILE__, 'lele_traccar_deactivation');

// Funktion für die Erstellung der Admin-Menüseite
function lele_traccar_admin_menu() {
    add_menu_page(
        'LELE-Traccar Einstellungen',
        'LELE-Traccar',
        'manage_options',
        'lele_traccar',
        'lele_traccar_admin_page'
    );
}
add_action('admin_menu', 'lele_traccar_admin_menu');

// Funktion zum Registrieren der Einstellungen
function lele_traccar_register_settings() {
    register_setting('lele_traccar_einstellungen', 'traccar_server_url');
    register_setting('lele_traccar_einstellungen', 'traccar_username');
    register_setting('lele_traccar_einstellungen', 'traccar_password');
}
add_action('admin_init', 'lele_traccar_register_settings');

// Callback-Funktion für die Anzeige der Admin-Seite
function lele_traccar_admin_page() {
    ?>
    <div class="wrap">
        <h2>LELE Traccar Einstellungen</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('lele_traccar_einstellungen');
            do_settings_sections('lele_traccar');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Traccar Server URL</th>
                    <td><input type="text" name="traccar_server_url" value="<?php echo esc_attr(get_option('traccar_server_url')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Traccar Username</th>
                    <td><input type="text" name="traccar_username" value="<?php echo esc_attr(get_option('traccar_username')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Traccar Password</th>
                    <td><input type="password" name="traccar_password" value="<?php echo esc_attr(get_option('traccar_password')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Shortcode, um Traccar-Gerätedaten anzuzeigen
function lele_show_traccar_devices($atts) {
    // Lese die Post-ID aus den Shortcode-Attributen
    $post_id = isset($atts['post_id']) ? $atts['post_id'] : get_the_ID();

    // Debug: Zeige die Post-ID
    echo "Debug: Post-ID ist $post_id<br>";

    // Rufe Traccar-Daten ab
    $devices = lele_get_traccar_data($post_id);

    // Debug: Zeige den erhaltenen Token und die Server-URL
    $traccar_token = get_post_meta($post_id, 'traccar_token', true);
    $traccar_url = get_option('traccar_server_url');
    echo "Debug: Traccar-Token ist $traccar_token<br>";
    echo "Debug: Traccar-Server-URL ist $traccar_url<br>";

    // Überprüfe, ob Daten vorhanden sind
    if ($devices === null) {
        // Debug: Keine Daten erhalten
        echo "Debug: Keine Traccar-Daten erhalten<br>";
        return 'Keine Traccar-Daten verfügbar.';
    }

    // Debug: Zeige die erhaltenen Gerätedaten
    echo "Debug: Erhaltene Gerätedaten:<br>";
    var_dump($devices);

    // Erstelle die Ausgabe
    $output = '<ul>';
    foreach ($devices as $device) {
        $output .= '<li>' . $device['name'] . ' (' . $device['status'] . ')</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('lele_traccar_devices', 'lele_show_traccar_devices');

// Funktion zum Hinzufügen der Meta-Box
function lele_add_traccar_device_id_meta_box() {
    add_meta_box(
        'lele_traccar_device_id_meta_box',
        'Traccar Device ID',
        'lele_display_traccar_device_id_meta_box',
        'post'
    );
}
add_action('add_meta_boxes', 'lele_add_traccar_device_id_meta_box');

// Callback-Funktion zum Anzeigen der Meta-Box
function lele_display_traccar_device_id_meta_box($post) {
    $traccar_device_id = get_post_meta($post->ID, 'traccar_device_id', true);
    ?>
    <label for="traccar_device_id">Traccar Device ID:</label>
    <input type="text" id="traccar_device_id" name="traccar_device_id" value="<?php echo esc_attr($traccar_device_id); ?>">
    <?php
}

// Funktion zum Speichern der Meta-Box-Daten
function lele_save_traccar_device_id_meta_box_data($post_id) {
    if (array_key_exists('traccar_device_id', $_POST)) {
        update_post_meta(
            $post_id,
            'traccar_device_id',
            sanitize_text_field($_POST['traccar_device_id'])
        );
    }
}
add_action('save_post', 'lele_save_traccar_device_id_meta_box_data');
function lele_show_traccar_device_status($atts) {
    $post_id = isset($atts['post_id']) ? $atts['post_id'] : get_the_ID();
    $device_status = lele_get_device_status($post_id);
    if ($device_status === null) {
        return 'Keine Statusdaten verfügbar.';
    }
    return 'Status: ' . $device_status['status'];
}
add_shortcode('lele_traccar_device_status', 'lele_show_traccar_device_status');
function lele_show_traccar_device_position($atts) {
    $post_id = isset($atts['post_id']) ? $atts['post_id'] : get_the_ID();
    $device_position = lele_get_device_position($post_id);
    if ($device_position === null) {
        return 'Keine Positionsdaten verfügbar.';
    }
    return 'Position: ' . $device_position['latitude'] . ', ' . $device_position['longitude'];
}
add_shortcode('lele_traccar_device_position', 'lele_show_traccar_device_position');
function lele_show_traccar_device_events($atts) {
    $post_id = isset($atts['post_id']) ? $atts['post_id'] : get_the_ID();
    $device_events = lele_get_device_events($post_id);
    if ($device_events === null) {
        return 'Keine Ereignisdaten verfügbar.';
    }
    // Hier können Sie die Darstellung der Ereignisse anpassen
    return 'Letzte Ereignisse: ' . json_encode($device_events);
}
add_shortcode('lele_traccar_device_events', 'lele_show_traccar_device_events');
function lele_show_traccar_device_trip_log($atts) {
    $post_id = isset($atts['post_id']) ? $atts['post_id'] : get_the_ID();
    $from = isset($atts['from']) ? $atts['from'] : '2023-01-01T00:00:00Z';
    $to = isset($atts['to']) ? $atts['to'] : '2023-12-31T23:59:59Z';
    $trip_log = lele_get_device_trip_log($post_id, $from, $to);
    if ($trip_log === null) {
        return 'Keine Fahrtenbuchdaten verfügbar.';
    }
    // Hier können Sie die Darstellung des Fahrtenbuchs anpassen
    return 'Fahrtenbuch: ' . json_encode($trip_log);
}
add_shortcode('lele_traccar_device_trip_log', 'lele_show_traccar_device_trip_log');
add_action( 'elementor/widgets/widgets_registered', function($widgets_manager){
    $widgets_manager->register_widget_type( new Map_Widget() );
} );
// Funktion zum Einreihen der Leaflet-Assets
function enqueue_leaflet_assets() {
    // Enqueue Leaflet CSS
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');

    // Enqueue Leaflet JS
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true);
}

// Hook in WordPress, um die Assets einzubinden
add_action('wp_enqueue_scripts', 'enqueue_leaflet_assets');

// Funktion zum Hinzufügen der Debugging-Option im WordPress-Admin-Bereich
function lele_add_debug_option() {
    add_settings_field(
        'lele_enable_debug',
        'Enable Debugging',
        'lele_debug_option_callback',
        'general'
    );
    register_setting('general', 'lele_enable_debug');
}

// Callback-Funktion für die Debugging-Option
function lele_debug_option_callback() {
    echo '<input type="checkbox" id="lele_enable_debug" name="lele_enable_debug" value="yes" ' . checked('yes', get_option('lele_enable_debug'), false) . '>';
}

add_action('admin_init', 'lele_add_debug_option');

?>