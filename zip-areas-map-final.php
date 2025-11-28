<?php
/**
 * Plugin Name: ZIP Areas Map Final (Click Focus)
 * Description: Display specific ZIP codes on a responsive Leaflet map. Clicking any ZIP marker centers and zooms to that location.
 * Version: 3.3
 * Author: Ali Hossain
 */

if (!defined('ABSPATH')) exit;

function zip_areas_map_final_enqueue_scripts() {
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null, true);

    // Responsive CSS + Label styling
    wp_add_inline_style('leaflet-css', '
        #zipMapFinal { height: 600px; width: 100%; }
        .leaflet-label { 
            font-weight: bold; 
            color: #c00; 
            background: white; 
            border: 1px solid #aaa; 
            padding: 2px 5px; 
            border-radius: 4px; 
            font-size: 14px;
        }
        @media(max-width:768px){ #zipMapFinal{height:450px;} .leaflet-label{font-size:12px;} }
        @media(max-width:480px){ #zipMapFinal{height:350px;} .leaflet-label{font-size:11px;} }
    ');
}
add_action('wp_enqueue_scripts', 'zip_areas_map_final_enqueue_scripts');

function zip_areas_map_final_shortcode() {
    $zips = [
        '92007','92008','92009','92010','92011','92014','92024','92025','92026','92027',
        '92029','92054','92056','92057','92058','92067','92075','92078','92081','92083',
        '92084','92091','92096','92127','92128','92129','92130'
    ];

    ob_start(); ?>
    <div id="zipMapFinal"></div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('zipMapFinal', { scrollWheelZoom: true }).setView([33.1, -117.2], 10);

        // Tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // ZIP data (random offset for visual spread)
        var zipData = [
            <?php foreach ($zips as $zip): ?>
            { zip: '<?php echo $zip; ?>', lat: 33.0 + Math.random() * 0.4, lng: -117.3 + Math.random() * 0.4 },
            <?php endforeach; ?>
        ];

        var markers = [];

        zipData.forEach(function(item) {
            var marker = L.marker([item.lat, item.lng]).addTo(map);
            
            // Label overlay
            var label = L.divIcon({ className: 'leaflet-label', html: item.zip, iconSize: [50, 20] });
            var labelMarker = L.marker([item.lat, item.lng], { icon: label, interactive: false }).addTo(map);

            // Click event — center + zoom
            marker.on('click', function() {
                map.setView([item.lat, item.lng], 14, { animate: true });
            });

            markers.push(marker);
        });

        // Fit all markers inside view on load
        var group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.25));
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('zip_map', 'zip_areas_map_final_shortcode');
