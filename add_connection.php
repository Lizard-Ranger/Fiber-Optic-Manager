<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $core_count = isset($_POST['core_count']) ? (int)$_POST['core_count'] : 12;
    $length_m = trim($_POST['length_m']);
    $status = trim($_POST['status']);
    $location = trim($_POST['location']);
    $notes = trim($_POST['notes']);
    $latitude = isset($_POST['latitude']) ? trim($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? trim($_POST['longitude']) : null;
    $otdr_results = isset($_POST['otdr_results']) ? trim($_POST['otdr_results']) : null;

    if (empty($name) || empty($type) || empty($length_m) || empty($status) || empty($core_count)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO connections (name, type, length_m, status, location, notes, latitude, longitude, core_count, otdr_results) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $type, $length_m, $status, $location, $notes, $latitude, $longitude, $core_count, $otdr_results]);
            $success = "Connection added successfully!";
        } catch(PDOException $e) {
            $error = "Error adding connection: " . $e->getMessage();
        }
    }
}
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<div class="auth-container">
    <h2>Add New Fiber-Optic Connection</h2>
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST" action="" class="auth-form">
        <div class="form-group">
            <label for="name">Connection Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <select id="type" name="type" required>
                <option value="OS2">OS2</option>
                <option value="OM2">OM2</option>
                <option value="OM3">OM3</option>
                <option value="OM4">OM4</option>
                <option value="OM5">OM5</option>
            </select>
        </div>
        <div class="form-group">
            <label for="core_count">Core Count</label>
            <select id="core_count" name="core_count" required>
                <option value="4">4</option>
                <option value="8">8</option>
                <option value="16">16</option>
                <option value="24">24</option>
            </select>
        </div>
        <div class="form-group">
            <label for="length_m">Length (m)</label>
            <input type="number" step="1" id="length_m" name="length_m" required>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Maintenance">Maintenance</option>
            </select>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location">
        </div>
        <div class="form-group">
            <label>Pick Location on Map</label>
            <div id="map" style="height: 300px; width: 100%; margin-bottom: 1rem; border-radius: 8px;"></div>
        </div>
        <div class="form-group">
            <label for="latitude">Latitude</label>
            <input type="number" step="0.0000001" id="latitude" name="latitude" placeholder="e.g. -25.746111">
        </div>
        <div class="form-group">
            <label for="longitude">Longitude</label>
            <input type="number" step="0.0000001" id="longitude" name="longitude" placeholder="e.g. 28.188056">
        </div>
        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Connection</button>
    </form>
</div>
<script>
    var map = L.map('map').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    var marker;
    function setMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            marker.on('dragend', function(e) {
                var pos = marker.getLatLng();
                document.getElementById('latitude').value = pos.lat.toFixed(7);
                document.getElementById('longitude').value = pos.lng.toFixed(7);
            });
        }
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }
    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });
    // If lat/lng fields are filled, show marker
    window.onload = function() {
        var lat = parseFloat(document.getElementById('latitude').value);
        var lng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            map.setView([lat, lng], 12);
            setMarker(lat, lng);
        }
    };
</script>
<?php require_once 'includes/footer.php'; ?> 