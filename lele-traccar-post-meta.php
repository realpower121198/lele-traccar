<?php
include 'lele-traccar-api.php';
// Hinzufügen von Meta-Boxen im WordPress-Adminbereich
function lele_add_traccar_meta_boxes() {
    add_meta_box(
        'lele_traccar_meta_box', // Meta-Box-ID
        'Traccar Geräteinformationen', // Titel der Meta-Box
        'lele_traccar_meta_box_callback', // Callback-Funktion
        'post', // Post-Typ
        'normal', // Position
        'high' // Priorität
    );
}
add_action('add_meta_boxes', 'lele_add_traccar_meta_boxes');

// Callback-Funktion für die Meta-Box
function lele_traccar_meta_box_callback($post) {
    // Nonce für die Sicherheit
    wp_nonce_field('lele_traccar_meta_box', 'lele_traccar_meta_box_nonce');

    // Abrufen der gespeicherten Meta-Daten
    $longitude = get_post_meta($post->ID, 'lele_longitude', true);
    $latitude = get_post_meta($post->ID, 'lele_latitude', true);
    $speed = get_post_meta($post->ID, 'lele_speed', true);
    $altitude = get_post_meta($post->ID, 'lele_altitude', true);
    $status = get_post_meta($post->ID, 'lele_status', true);
    $last_update_time = get_post_meta($post->ID, 'lele_last_update_time', true);

    // HTML für die Meta-Box
    echo '<label for="lele_longitude">Längengrad: </label>';
    echo '<input type="text" id="lele_longitude" name="lele_longitude" value="' . esc_attr($longitude) . '" readonly><br>';

    echo '<label for="lele_latitude">Breitengrad: </label>';
    echo '<input type="text" id="lele_latitude" name="lele_latitude" value="' . esc_attr($latitude) . '" readonly><br>';

    echo '<label for="lele_speed">Geschwindigkeit: </label>';
    echo '<input type="text" id="lele_speed" name="lele_speed" value="' . esc_attr($speed) . '" readonly><br>';

    echo '<label for="lele_altitude">Höhe: </label>';
    echo '<input type="text" id="lele_altitude" name="lele_altitude" value="' . esc_attr($altitude) . '" readonly><br>';

    echo '<label for="lele_status">Status: </label>';
    echo '<input type="text" id="lele_status" name="lele_status" value="' . esc_attr($status) . '" readonly><br>';

    echo '<label for="lele_last_update_time">Zeit des letzten Updates: </label>';
    echo '<input type="text" id="lele_last_update_time" name="lele_last_update_time" value="' . esc_attr($last_update_time) . '" readonly><br>';
}

// Speichern der Meta-Daten
function lele_save_traccar_meta_data($post_id) {
    // Überprüfen des Nonce
    if (!isset($_POST['lele_traccar_meta_box_nonce']) || !wp_verify_nonce($_POST['lele_traccar_meta_box_nonce'], 'lele_traccar_meta_box')) {
        return;
    }

    // Überprüfen der Berechtigungen
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Speichern der Meta-Daten
    update_post_meta($post_id, 'lele_unique_id', sanitize_text_field($_POST['lele_unique_id']));
    update_post_meta($post_id, 'lele_longitude', sanitize_text_field($_POST['lele_longitude']));
    update_post_meta($post_id, 'lele_latitude', sanitize_text_field($_POST['lele_latitude']));
    update_post_meta($post_id, 'lele_speed', sanitize_text_field($_POST['lele_speed']));
    update_post_meta($post_id, 'lele_altitude', sanitize_text_field($_POST['lele_altitude']));
    update_post_meta($post_id, 'lele_status', sanitize_text_field($_POST['lele_status']));
    update_post_meta($post_id, 'lele_last_update_time', sanitize_text_field($_POST['lele_last_update_time']));
    update_post_meta($post_id, 'lele_last_record_time', sanitize_text_field($_POST['lele_last_record_time']));
}
add_action('save_post', 'lele_save_traccar_meta_data');
// Meta-Box zum WordPress Admin-Bereich hinzufügen
add_action('add_meta_boxes', 'lele_meta_box_hinzufuegen');
function lele_meta_box_hinzufuegen() {
    add_meta_box('lele_position_aktualisieren', 'Position aktualisieren', 'lele_position_aktualisieren_callback', 'post', 'side', 'high');
}

// Callback-Funktion für die Meta-Box
function lele_position_aktualisieren_callback($post) {
    // Sicherheitsüberprüfung
    wp_nonce_field('lele_position_aktualisieren', 'lele_position_aktualisieren_nonce');
    
    echo '<input type="button" id="lele_position_aktualisieren_button" class="button button-primary" value="Position aktualisieren">';
}

// JavaScript für den Button-Click
add_action('admin_footer', 'lele_admin_footer_script');
function lele_admin_footer_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#lele_position_aktualisieren_button').click(function() {
                var data = {
                    'action': 'lele_position_aktualisieren',
                    'post_id': <?php echo get_the_ID(); ?>
                };
                
                $.post(ajaxurl, data, function(response) {
                    // Parse die Antwort als JSON
                    var parsedResponse = JSON.parse(response);

                    // Aktualisiere die Felder mit den neuen Daten
                    $('#lele_latitude').val(parsedResponse.latitude);
                    $('#lele_longitude').val(parsedResponse.longitude);
                    // ... (andere Felder)

                    alert('Position aktualisiert');
                });
            });
        });
    </script>
    <?php
}

//STATUS ERRECHNEN
function lele_update_status($post_id) {
    $last_update_time = get_post_meta($post_id, 'lele_last_update_time', true);
    $current_time = time();
    $last_record_time = strtotime($last_update_time);

    $time_difference = ($current_time - $last_record_time) / 60;

    $status = 'DEAKTIVIERT';
    if ($time_difference <= 1) {
        $status = 'LIVE';
    } elseif ($time_difference > 1 && $time_difference <= 15) {
        $status = 'AKTUELL';
    } elseif ($time_difference > 15) {
        $status = 'OFFLINE';
    }

    update_post_meta($post_id, 'lele_status', $status);
}

// AJAX-Handler
add_action('wp_ajax_lele_position_aktualisieren', 'lele_position_aktualisieren_ajax_handler');
function lele_position_aktualisieren_ajax_handler() {
    $post_id = $_POST['post_id'];
    $decoded_response = lele_get_device_position($post_id);  // Ihre vorhandene Funktion zum Abrufen der Position
	
	error_log("Debug: " . print_r($decoded_response, true));

    if ($decoded_response !== null) {
        // Zeit des letzten GPS-Records
        $last_record_time = strtotime($decoded_response['fixTime']);
        $current_time = time();

        // Zeitdifferenz in Minuten
        $time_difference = ($current_time - $last_record_time) / 60;

        // Status festlegen
        $status = 'DEAKTIVIERT';
        if ($time_difference <= 1) {
            $status = 'LIVE';
        } elseif ($time_difference > 1 && $time_difference <= 15) {
            $status = 'AKTUELL';
        } elseif ($time_difference > 15) {
            $status = 'OFFLINE';
        }

        // Status in der Meta-Box speichern
        update_post_meta($post_id, 'lele_status', $status);
    } else {
        // Wenn keine Daten vorhanden sind, setzen Sie den Status auf 'DEAKTIVIERT'
        update_post_meta($post_id, 'lele_status', 'DEAKTIVIERT');
    }
	
	lele_update_status($post_id);
    wp_die();
}