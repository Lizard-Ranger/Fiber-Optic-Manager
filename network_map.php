<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Fetch all connections with coordinates
try {
    $stmt = $conn->query("SELECT id, name, type, location, status, length_m, latitude, longitude, core_count, otdr_results, notes FROM connections WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $connections = $stmt->fetchAll();
} catch (PDOException $e) {
    $connections = [];
}
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.tooltip-row {
  position: relative;
}
.tooltip-bubble {
  display: none;
  position: absolute;
  left: 100%;
  top: 0;
  z-index: 10;
  background: #fff;
  color: #222;
  border: 1px solid #888;
  border-radius: 6px;
  padding: 10px;
  min-width: 220px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  font-size: 0.95em;
  white-space: pre-line;
}
.tooltip-row:hover .tooltip-bubble {
  display: block;
}
</style>
<div class="about-container">
    <div class="about-header">
        <h1>Network Map</h1>
        <p class="subtitle">Visualize your fiber-optic connections</p>
    </div>
    <div id="map" style="height:400px; width:100%; margin-bottom:2rem; border-radius:8px;"></div>
    <h2>Connections List</h2>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:8px; border:1px solid #ddd; cursor:pointer;" onclick="sortTable(0)">Name &#x25B2;&#x25BC;</th>
                <th style="padding:8px; border:1px solid #ddd; cursor:pointer;" onclick="sortTable(1)">Type &#x25B2;&#x25BC;</th>
                <th style="padding:8px; border:1px solid #ddd; cursor:pointer;" onclick="sortTable(2)">Location &#x25B2;&#x25BC;</th>
                <th style="padding:8px; border:1px solid #ddd;">Status</th>
                <th style="padding:8px; border:1px solid #ddd;">Length (m)</th>
                <th style="padding:8px; border:1px solid #ddd;">Core Count</th>
                <th style="padding:8px; border:1px solid #ddd;">Splice Mapping</th>
                <th style="padding:8px; border:1px solid #ddd;">OTDR Results</th>
                <th style="padding:8px; border:1px solid #ddd;">Latitude</th>
                <th style="padding:8px; border:1px solid #ddd;">Longitude</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($connections) === 0): ?>
                <tr><td colspan="9" style="text-align:center; padding:16px;">No connections with coordinates found.</td></tr>
            <?php else: ?>
                <?php $i = 0; // Initialize counter ?>
                <?php foreach ($connections as $conn): ?>
                    <?php
                    $rowColor = '';
                    $type = strtoupper($conn['type']);
                    if (strpos($type, 'OS2') !== false) $rowColor = 'background: #fff700;'; // yellow
                    elseif (strpos($type, 'OM2') !== false) $rowColor = 'background: #FF4500;'; // orange
                    elseif (strpos($type, 'OM3') !== false) $rowColor = 'background: #00FFFF;'; // aqua
                    elseif (strpos($type, 'OM4') !== false) $rowColor = 'background: #EE82EE;'; // violet
                    elseif (strpos($type, 'OM5') !== false) $rowColor = 'background: #ff66cc;'; // rose
                    $tooltip = "<b>" . htmlspecialchars($conn['name']) . "</b><br>" .
                        htmlspecialchars($conn['location']) . "<br>" .
                        "Type: " . htmlspecialchars($conn['type']) . "<br>" .
                        "Status: " . htmlspecialchars($conn['status']) . "<br>" .
                        "Length: " . htmlspecialchars($conn['length_m']) . " m<br>" .
                        "Cores: " . htmlspecialchars($conn['core_count']) . "<br>" .
                        (isset($conn['notes']) && $conn['notes'] ? "Notes: " . htmlspecialchars($conn['notes']) : "");
                    ?>
                    <tr class="tooltip-row" style="<?= $rowColor ?>">
                        <td colspan="100" style="display:none;"></td>
                        <div class="tooltip-bubble"><?= $tooltip ?></div>
                        <td style="padding:8px; border:1px solid #ddd;">
                            <a href="#" class="focus-marker" data-marker="<?= $i ?>"> <?= htmlspecialchars($conn['name']) ?> </a>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['type']) ?> </td>
                        <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['location']) ?> </td>
                        <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['status']) ?> </td>
                        <td style="padding:8px; border:1px solid #ddd;">
                            <?= htmlspecialchars($conn['length_m']) ?>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd; text-align:center;"> <?= htmlspecialchars($conn['core_count']) ?> </td>
                        <td style="padding:8px; border:1px solid #ddd; text-align:center;"><a href="splice_map.php?connection_id=<?= urlencode($conn['id']) ?>" class="btn btn-secondary" target="_blank">Splice Map</a></td>
                        <td style="padding:8px; border:1px solid #ddd; text-align:center;">
                            <?php if (!empty($conn['otdr_results'])): ?>
                                <a href="<?= htmlspecialchars($conn['otdr_results']) ?>" target="_blank" class="btn btn-info">View OTDR</a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>View OTDR</button>
                            <?php endif; ?>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['latitude']) ?> </td>
                        <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['longitude']) ?> </td>
                    </tr>
                <?php $i++; // Increment counter ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
    var connections = <?php echo json_encode($connections); ?>;
    var map = L.map('map').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    var markers = [];
    if (connections.length > 0) {
        var bounds = [];
        connections.forEach(function(conn, i) {
            if (conn.latitude && conn.longitude) {
                var popupContent = '<b>' + conn.name + '</b><br>' +
                                   conn.location + '<br>' +
                                   'Status: ' + conn.status + '<br>' +
                                   'Length: ' + conn.length_m + ' m';
                if (conn.notes) {
                    popupContent += '<br>Notes: ' + conn.notes;
                }
                if (conn.otdr_results) {
                    popupContent += '<br><a href="' + conn.otdr_results + '" target="_blank">View OTDR Results</a>';
                }
                var marker = L.marker([conn.latitude, conn.longitude]).addTo(map);
                marker.bindPopup(popupContent);
                markers.push(marker);
                bounds.push([conn.latitude, conn.longitude]);
            } else {
                markers.push(null);
            }
        });
        if (bounds.length > 0) {
            map.fitBounds(bounds, {padding: [30, 30]});
        }
    }
    // Link table names to markers
    document.querySelectorAll('.focus-marker').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var idx = parseInt(this.getAttribute('data-marker'));
            var marker = markers[idx];
            if (marker) {
                map.setView(marker.getLatLng(), 16);
                marker.openPopup();
                // Scroll to map
                document.getElementById('map').scrollIntoView({behavior: 'smooth', block: 'center'});
            }
        });
    });

    function sortTable(col) {
        var table = document.querySelector('table');
        var tbody = table.tBodies[0];
        var rows = Array.from(tbody.rows);
        var asc = table.getAttribute('data-sort-col') != col || table.getAttribute('data-sort-dir') !== 'asc';
        rows.sort(function(a, b) {
            var x = a.cells[col].innerText.trim().toLowerCase();
            var y = b.cells[col].innerText.trim().toLowerCase();
            if (x < y) return asc ? -1 : 1;
            if (x > y) return asc ? 1 : -1;
            return 0;
        });
        rows.forEach(row => tbody.appendChild(row));
        table.setAttribute('data-sort-col', col);
        table.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');
    }
</script>
<?php require_once 'includes/footer.php'; ?> 