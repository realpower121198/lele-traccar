<?php
// Registrieren Sie das Widget
add_action('elementor/widgets/widgets_registered', function($widgets_manager){
    class Map_Widget extends \Elementor\Widget_Base {

        public function get_name() {
            return 'map_widget';
        }

        public function get_title() {
            return __('Karten Widget', 'plugin-name');
        }

        public function get_icon() {
            return 'fa fa-map';
        }

        public function get_categories() {
            return ['general'];
        }

        protected function _register_controls() {
            // Hier können Sie die Steuerelemente für das Widget hinzufügen
        }

        protected function render() {
        // Holen Sie die Geräteposition mit der bereits erstellten Funktion
        $post_id = get_the_ID(); // Beispiel für Post-ID
        $position_data = lele_get_device_position($post_id);

        if ($position_data && isset($position_data[0]['latitude']) && isset($position_data[0]['longitude'])) {
            $latitude = $position_data[0]['latitude'];
            $longitude = $position_data[0]['longitude'];
        } else {
            $latitude = 0;
            $longitude = 0;
        }

        // Frontend-Ausgabe des Widgets
        echo '<div id="map" style="height: 400px;"></div>';
        echo '<script>
            var map = L.map("map").setView([' . $latitude . ', ' . $longitude . '], 15);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "© OpenStreetMap contributors"
            }).addTo(map);
            L.marker([' . $latitude . ', ' . $longitude . ']).addTo(map);
            </script>';
    }

        protected function _content_template() {
            // Hier können Sie den Code für die Ausgabe des Widgets im Editor hinzufügen
        }
    }

    // Widget zum Elementor hinzufügen
    $widgets_manager->register_widget_type(new Map_Widget());
});
