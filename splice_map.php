<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'includes/header.php';
require_once 'config/database.php';

// Get connection_id from query string
$connection_id = isset($_GET['connection_id']) ? (int)$_GET['connection_id'] : 0;
if ($connection_id <= 0) {
    echo '<div class="auth-container"><h2>No connection selected</h2><p>Please open this page from the Connections List.</p></div>';
    require_once 'includes/footer.php';
    exit();
}

// Handle OTDR results update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otdr_results_submit'])) {
    if (isset($_FILES['otdr_pdf']) && $_FILES['otdr_pdf']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/otdr/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file = $_FILES['otdr_pdf'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_extension !== 'pdf') {
            $error_message = "Please upload a .pdf file only.";
        } else {
            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                try {
                    $stmt = $conn->prepare("UPDATE connections SET otdr_results = ? WHERE id = ?");
                    $stmt->execute([$filepath, $connection_id]);
                    $success_message = "OTDR PDF uploaded successfully!";
                    // Re-fetch connection to reflect changes
                    $stmt = $conn->prepare("SELECT * FROM connections WHERE id = ?");
                    $stmt->execute([$connection_id]);
                    $connection = $stmt->fetch();
                } catch(PDOException $e) {
                    $error_message = "Error updating OTDR Results: " . $e->getMessage();
                }
            } else {
                $error_message = "Failed to save uploaded file.";
            }
        }
    } else {
        $error_message = "Please select a PDF file to upload.";
    }
}

// Handle reset (delete all links for this connection)
if (isset($_POST['reset_links']) && $_POST['reset_links'] === '1') {
    $stmt = $conn->prepare("DELETE FROM splice_links WHERE connection_id = ?");
    $stmt->execute([$connection_id]);
    header('Location: splice_map.php?connection_id=' . $connection_id);
    exit();
}

// Fetch connection info
$stmt = $conn->prepare("SELECT * FROM connections WHERE id = ?");
$stmt->execute([$connection_id]);
$connection = $stmt->fetch();
if (!$connection) {
    echo '<div class="auth-container"><h2>Connection not found</h2></div>';
    require_once 'includes/footer.php';
    exit();
}

$core_count = (int)($connection['core_count'] ?? 12);
$coresA = [];
$coresB = [];
for ($i = 1; $i <= $core_count; $i++) {
    $coresA[] = "A$i";
    $coresB[] = "B$i";
}

// TIA-598-C colors
$tube_colors = [
    'Blue' => '#1C6DD0',
    'Orange' => '#FF7F00',
    'Green' => '#009B48',
    'Brown' => '#A05A2C',
    'Slate' => '#708090',
    'White' => '#FFFFFF',
    'Red' => '#FF0000',
    'Black' => '#000000',
    'Yellow' => '#FFD700',
    'Violet' => '#8F00FF',
    'Rose' => '#FF66CC',
    'Aqua' => '#00FFFF',
];
$core_color_names = array_keys($tube_colors);

function getTubeAndCoreColors($core_count) {
    global $tube_colors, $core_color_names;
    $tubes = [];
    if ($core_count == 8) {
        // 2 tubes, 4 cores each
        $tube_names = ['Blue', 'Orange'];
        for ($t = 0; $t < 2; $t++) {
            $tube = [
                'name' => $tube_names[$t],
                'color' => $tube_colors[$tube_names[$t]],
                'cores' => []
            ];
            for ($c = 0; $c < 4; $c++) {
                $core_name = $core_color_names[$c];
                $tube['cores'][] = [
                    'name' => $core_name,
                    'color' => $tube_colors[$core_name]
                ];
            }
            $tubes[] = $tube;
        }
    } elseif ($core_count == 24) {
        // 4 tubes, 6 cores each
        $tube_names = ['Blue', 'Orange', 'Green', 'Brown'];
        for ($t = 0; $t < 4; $t++) {
            $tube = [
                'name' => $tube_names[$t],
                'color' => $tube_colors[$tube_names[$t]],
                'cores' => []
            ];
            for ($c = 0; $c < 6; $c++) {
                $core_name = $core_color_names[$c];
                $tube['cores'][] = [
                    'name' => $core_name,
                    'color' => $tube_colors[$core_name]
                ];
            }
            $tubes[] = $tube;
        }
    }
    return $tubes;
}
$tubesA = getTubeAndCoreColors($core_count);
$tubesB = getTubeAndCoreColors($core_count);

// Handle batch save of new links (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['links'])) {
    header('Content-Type: application/json');
    $links = json_decode($_POST['links'], true);
    if (is_array($links)) {
        $stmt = $conn->prepare("INSERT INTO splice_links (core_a, core_b, connection_id) VALUES (?, ?, ?)");
        try {
            foreach ($links as $link) {
                if (isset($link['a'], $link['b'])) {
                    $stmt->execute([$link['a'], $link['b'], $connection_id]);
                }
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }
}

// Fetch all links for this connection
$stmt = $conn->prepare("SELECT * FROM splice_links WHERE connection_id = ?");
$stmt->execute([$connection_id]);
$saved_links = $stmt->fetchAll();
?>
<style>
.splice-map-container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 0;
    margin: 2rem 0;
    position: relative;
    min-width: 400px;
}
.splice-col {
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-width: 60px;
    align-items: center;
}
.core {
    background: #f0f0f0;
    border: 2px solid #2196f3;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    cursor: pointer;
    position: relative;
    z-index: 2;
}
.core.selected {
    background: #2196f3;
    color: #fff;
}
#splice-svg {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}
.tube-group { display: flex; flex-direction: column; align-items: center; margin: 0 8px; }
.tube-label { font-size: 0.9em; margin-bottom: 4px; font-weight: bold; }
.tube-border { border: 3px solid; border-radius: 12px; padding: 6px 2px 6px 2px; margin-bottom: 8px; }
.core.tia { border: 2px solid #888; margin: 2px 0; }
.legend-table { margin-top: 1.5em; border-collapse: collapse; }
.legend-table td { padding: 4px 10px; font-size: 0.95em; }
</style>
<div class="about-container" style="position:relative;">
    <h2>Splice Map for <?= htmlspecialchars($connection['name']) ?></h2>
    <p>Click a core on Side A, then a core on Side B to create a link. Click "Save" to store all new links.</p>
    <div class="splice-map-container" id="splice-map">
        <svg id="splice-svg"></svg>
        <div class="splice-col" id="colA">
            <?php if ($core_count == 8 || $core_count == 24): ?>
                <?php foreach ($tubesA as $tube): ?>
                    <div class="tube-group">
                        <div class="tube-label" style="color:<?= $tube['color'] ?>;">Tube: <?= $tube['name'] ?></div>
                        <div class="tube-border" style="border-color:<?= $tube['color'] ?>;">
                            <?php foreach ($tube['cores'] as $i => $core): ?>
                                <div class="core tia" data-core="A-<?= $tube['name'] ?>-<?= $i+1 ?>" data-col="A" style="background:<?= $core['color'] ?>; color:<?= ($core['name']==='White'||$core['name']==='Yellow'||$core['name']==='Aqua'||$core['name']==='Rose')?'#222':'#fff'; ?>;">
                                    <?= htmlspecialchars($core['name']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($coresA as $i => $core): ?>
                    <div class="core" data-core="A-<?= $i+1 ?>" data-col="A"> <?= htmlspecialchars($core) ?> </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="splice-col" id="colB">
            <?php if ($core_count == 8 || $core_count == 24): ?>
                <?php foreach ($tubesB as $tube): ?>
                    <div class="tube-group">
                        <div class="tube-label" style="color:<?= $tube['color'] ?>;">Tube: <?= $tube['name'] ?></div>
                        <div class="tube-border" style="border-color:<?= $tube['color'] ?>;">
                            <?php foreach ($tube['cores'] as $i => $core): ?>
                                <div class="core tia" data-core="B-<?= $tube['name'] ?>-<?= $i+1 ?>" data-col="B" style="background:<?= $core['color'] ?>; color:<?= ($core['name']==='White'||$core['name']==='Yellow'||$core['name']==='Aqua'||$core['name']==='Rose')?'#222':'#fff'; ?>;">
                                    <?= htmlspecialchars($core['name']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($coresB as $i => $core): ?>
                    <div class="core" data-core="B-<?= $i+1 ?>" data-col="B"> <?= htmlspecialchars($core) ?> </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <button id="save-links" class="btn btn-primary" style="margin-top:1rem;">Save</button>
    <form method="POST" style="display:inline; margin-left:1rem;" onsubmit="return confirm('Are you sure you want to reset all links?');">
        <input type="hidden" name="reset_links" value="1">
        <button type="submit" class="btn btn-secondary">Reset All Links</button>
    </form>
    <div id="save-status" style="margin-top:1rem;"></div>
    <h3>Existing Links</h3>
    <ul>
        <?php foreach ($saved_links as $link): ?>
            <li><?= htmlspecialchars($link['core_a']) ?> &rarr; <?= htmlspecialchars($link['core_b']) ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>OTDR Results</h3>
    <?php if (isset($success_message)): ?>
        <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="auth-form" style="margin-top:1rem;">
        <div class="form-group">
            <label for="otdr_pdf">Upload OTDR Results (PDF)</label>
            <input type="file" id="otdr_pdf" name="otdr_pdf" accept=".pdf" <?php if (empty($connection['otdr_results'])) echo 'required'; ?>>
        </div>
        <button type="submit" name="otdr_results_submit" class="btn btn-primary"><?php echo empty($connection['otdr_results']) ? 'Upload OTDR PDF' : 'Replace OTDR PDF'; ?></button>
        <?php if (!empty($connection['otdr_results'])): ?>
            <div style="margin-top:1rem;">
                <a href="<?= htmlspecialchars($connection['otdr_results']) ?>" target="_blank" class="btn btn-info">View Current PDF</a>
            </div>
        <?php else: ?>
            <div style="margin-top:1rem; color:#888;">No OTDR PDF uploaded yet.</div>
        <?php endif; ?>
    </form>
</div>
<!-- TIA-598-C Color Legend -->
<table class="legend-table">
    <tr>
        <?php foreach ($tube_colors as $name => $color): ?>
            <td><span style="display:inline-block;width:18px;height:18px;background:<?= $color ?>;border:1px solid #888;vertical-align:middle;margin-right:4px;"></span><?= $name ?></td>
        <?php endforeach; ?>
    </tr>
</table>
<script>
const colA = document.getElementById('colA');
const colB = document.getElementById('colB');
const svg = document.getElementById('splice-svg');
let selectedA = null;
let selectedB = null;
let pendingLinks = [];
const savedLinks = <?php echo json_encode(array_map(function($l){return [$l['core_a'],$l['core_b']];}, $saved_links)); ?>;

function getCenter(el, container) {
    const rect = el.getBoundingClientRect();
    const crect = container.getBoundingClientRect();
    return {
        x: rect.left + rect.width / 2 - crect.left,
        y: rect.top + rect.height / 2 - crect.top
    };
}

function drawLinks() {
    svg.innerHTML = '';
    const container = document.getElementById('splice-map');
    svg.setAttribute('width', container.offsetWidth);
    svg.setAttribute('height', container.offsetHeight);
    // Draw saved links
    savedLinks.forEach(function(link) {
        drawLink(link[0], link[1], '#2196f3');
    });
    // Draw pending links
    pendingLinks.forEach(function(link) {
        drawLink(link.a, link.b, '#ff9800');
    });
}

function drawLink(aCore, bCore, color) {
    const a = document.querySelector('.core[data-core="' + aCore + '"][data-col="A"]');
    const b = document.querySelector('.core[data-core="' + bCore + '"][data-col="B"]');
    const container = document.getElementById('splice-map');
    if (a && b) {
        const ac = getCenter(a, container);
        const bc = getCenter(b, container);
        const path = document.createElementNS('http://www.w3.org/2000/svg','path');
        path.setAttribute('d', `M${ac.x},${ac.y} C${ac.x+60},${ac.y} ${bc.x-60},${bc.y} ${bc.x},${bc.y}`);
        path.setAttribute('stroke', color);
        path.setAttribute('stroke-width', '3');
        path.setAttribute('fill', 'none');
        svg.appendChild(path);
    }
}

function clearSelection() {
    document.querySelectorAll('.core.selected').forEach(el => el.classList.remove('selected'));
    selectedA = null;
    selectedB = null;
}

colA.querySelectorAll('.core').forEach(core => {
    core.addEventListener('click', function() {
        clearSelection();
        this.classList.add('selected');
        selectedA = this.getAttribute('data-core');
    });
});
colB.querySelectorAll('.core').forEach(core => {
    core.addEventListener('click', function() {
        if (selectedA) {
            selectedB = this.getAttribute('data-core');
            this.classList.add('selected');
            // Add to pending links if not already present
            if (!pendingLinks.some(l => l.a === selectedA && l.b === selectedB) && !savedLinks.some(l => l[0] === selectedA && l[1] === selectedB)) {
                pendingLinks.push({a: selectedA, b: selectedB});
            }
            setTimeout(() => { clearSelection(); drawLinks(); }, 150); // Briefly show selection
            drawLinks();
        }
    });
});
window.addEventListener('DOMContentLoaded', function() {
    drawLinks();
    window.addEventListener('resize', drawLinks);
});

document.getElementById('save-links').addEventListener('click', function() {
    if (pendingLinks.length === 0) {
        document.getElementById('save-status').innerHTML = '<span style="color:#c62828;">No new links to save.</span>';
        return;
    }
    fetch('splice_map.php?connection_id=<?= $connection_id ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'links=' + encodeURIComponent(JSON.stringify(pendingLinks))
    }).then(r => r.json()).then(resp => {
        if (resp.success) {
            document.getElementById('save-status').innerHTML = '<span style="color:#2e7d32;">Links saved!</span>';
            setTimeout(() => { location.reload(); }, 800);
        } else {
            document.getElementById('save-status').innerHTML = '<span style="color:#c62828;">Error saving links: ' + (resp.error || 'Unknown error') + '</span>';
        }
    }).catch(() => {
        document.getElementById('save-status').innerHTML = '<span style="color:#c62828;">Error saving links (network or server error).</span>';
    });
});
</script>
<?php require_once 'includes/footer.php'; ?> 