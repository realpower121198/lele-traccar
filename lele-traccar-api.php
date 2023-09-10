<?php
// lele-traccar-api.php
include_once('lele-traccar-plugin.php');
foreach ( glob( plugin_dir_path( __FILE__ ) . 'widgets/*.php' ) as $file ) {
    include_once $file;
}
// Funktion, um Geräteinformationen abzurufen
function lele_get_traccar_data($post_id) {
    $traccar_device_id = get_post_meta($post_id, 'traccar_device_id', true);
    $traccar_url = get_option('traccar_server_url');
    $username = get_option('traccar_username');
    $password = get_option('traccar_password');

    if (empty($traccar_device_id) || empty($traccar_url) || empty($username) || empty($password)) {
        echo "Debug: Device ID, Benutzername, Passwort oder URL fehlt.<br>";
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $traccar_url . '/api/devices?uniqueId=' . $traccar_device_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if(curl_errno($ch)){
        echo 'Curl-Fehler: ' . curl_error($ch) . "<br>";
    }

    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Debug: JSON-Fehler: " . json_last_error_msg() . "<br>";
    }

    if ($http_code != 200) {
        echo "Debug: Anfrage fehlgeschlagen. Überprüfen Sie Device ID, Benutzername, Passwort und URL.<br>";
        return null;
    }

    return $decoded_response;
}

// Funktion, um die Position eines Geräts abzurufen und die Post-Meta-Felder zu aktualisieren
function lele_get_device_position($post_id) {
    $traccar_device_id = get_post_meta($post_id, 'traccar_device_id', true);
    $traccar_url = get_option('traccar_server_url');
    $username = get_option('traccar_username');
    $password = get_option('traccar_password');

    if (empty($traccar_device_id) || empty($traccar_url) || empty($username) || empty($password)) {
        echo "Debug: Device ID, Benutzername, Passwort oder URL fehlt.<br>";
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $traccar_url . '/api/positions?uniqueId=' . $traccar_device_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Debug: JSON-Fehler: " . json_last_error_msg() . "<br>";
        return null;
    }

    // Aktualisieren der Post-Meta-Felder
    update_post_meta($post_id, 'lele_longitude', sanitize_text_field($decoded_response[0]['longitude']));
    update_post_meta($post_id, 'lele_latitude', sanitize_text_field($decoded_response[0]['latitude']));
    update_post_meta($post_id, 'lele_speed', sanitize_text_field($decoded_response[0]['speed']));
    update_post_meta($post_id, 'lele_altitude', sanitize_text_field($decoded_response[0]['altitude']));
    update_post_meta($post_id, 'lele_last_update_time', sanitize_text_field($decoded_response[0]['fixTime']));
    update_post_meta($post_id, 'lele_device_status', sanitize_text_field($decoded_response[0]['attributes']));

    return $decoded_response;
}

// Funktion, um den aktuellen Status eines Geräts abzurufen
function lele_get_device_status($post_id) {
    $unique_id = get_post_meta($post_id, 'traccar_device_id', true); // Hier wird die uniqueId aus dem Post-Meta abgerufen
    $traccar_url = get_option('traccar_server_url');
    $username = get_option('traccar_username');
    $password = get_option('traccar_password');

    // Verwendung der Funktion lele_get_device_id_by_unique_id, um die tatsächliche deviceId zu erhalten
    $traccar_device_id = lele_get_device_id_by_unique_id($unique_id, $username, $password, $traccar_url);

    if (empty($traccar_device_id) || empty($traccar_url) || empty($username) || empty($password)) {
        echo "Debug: Device ID, Benutzername, Passwort oder URL fehlt.<br>";
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $traccar_url . "/api/devices/$traccar_device_id/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Debug: JSON-Fehler: " . json_last_error_msg() . "<br>";
        return null;
    }

    return $decoded_response;
}

// Funktion, um die Geofences für ein Gerät abzurufen
function lele_get_device_geofences($post_id) {
    $traccar_device_id = get_post_meta($post_id, 'traccar_device_id', true);
    $traccar_url = get_option('traccar_server_url');
    $username = get_option('traccar_username');
    $password = get_option('traccar_password');

    if (empty($traccar_device_id) || empty($traccar_url) || empty($username) || empty($password)) {
        echo "Debug: Device ID, Benutzername, Passwort oder URL fehlt.<br>";
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $traccar_url . "/api/geofences?uniqueId=$traccar_device_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Debug: JSON-Fehler: " . json_last_error_msg() . "<br>";
        return null;
    }

    return $decoded_response;
}
// Funktion, um das Fahrtenbuch für ein Gerät abzurufen
function lele_get_device_trip_log($post_id, $from, $to) {
    $traccar_device_id = get_post_meta($post_id, 'traccar_device_id', true);
    $traccar_url = get_option('traccar_server_url');
    $username = get_option('traccar_username');
    $password = get_option('traccar_password');

    if (empty($traccar_device_id) || empty($traccar_url) || empty($username) || empty($password)) {
        echo "Debug: Device ID, Benutzername, Passwort oder URL fehlt.<br>";
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $traccar_url . "/api/reports/trips?uniqueId=$traccar_device_id&from=$from&to=$to");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Debug: JSON-Fehler: " . json_last_error_msg() . "<br>";
        return null;
    }

    return $decoded_response;
}
// Funktion, um die letzten Ereignisse eines Geräts abzurufen
function lele_get_device_events($post_id) {
    $traccar_device_id = get_post_meta($post_id, 'traccar_unique_id', true);
    $traccar_url = get_option('traccar_server_url');
    $username = get_option('traccar_username');
    $password = get_option('traccar_password');

    if (empty($traccar_device_id) || empty($traccar_url) || empty($username) || empty($password)) {
        echo "Debug: Unique ID, Benutzername, Passwort oder URL fehlt.<br>";
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $traccar_url . "/api/events?uniqueId=$traccar_device_id"); // Änderung hier
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Debug: JSON-Fehler: " . json_last_error_msg() . "<br>";
        return null;
    }

    return $decoded_response;
}
function lele_get_device_id_by_unique_id($uniqueId, $username, $password, $server_url) {
    $url = $server_url . "/api/devices";
    $credentials = base64_encode("$username:$password");
    $headers = array(
        "Authorization: Basic $credentials"
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $devices = json_decode($response, true);
    foreach ($devices as $device) {
        if ($device['uniqueId'] == $uniqueId) {
            return $device['id'];
        }
    }
    return null;
}
function lele_get_device_id($post_id) {
    $traccar_device_id = get_post_meta($post_id, 'traccar_device_id', true);
    $traccar_url = get_option('traccar_server_url');
    $username = get_option('traccar_username');
    $password = get_option('traccar_password');

    if (empty($traccar_device_id) || empty($traccar_url) || empty($username) || empty($password)) {
        echo "Debug: Device ID, Benutzername, Passwort oder URL fehlt.<br>";
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $traccar_url . '/api/devices/' . $traccar_device_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded_response = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Debug: JSON-Fehler: " . json_last_error_msg() . "<br>";
        return null;
    }

    if (isset($decoded_response['id'])) {
        update_post_meta($post_id, 'lele_unique_id', $decoded_response['id']);
    }
}
