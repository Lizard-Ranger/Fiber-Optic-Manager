<?php
// populate_fiber_tubes.php
// Run this script from the command line: php populate_fiber_tubes.php

require_once __DIR__ . '/config/database.php';

// TIA-598-C tube and core color definitions
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

function getTubeAndCoreAssignments($core_count) {
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
    } else {
        // Flat assignment for other core counts (e.g., 4, 12, 16)
        $tube = [
            'name' => 'Blue',
            'color' => $tube_colors['Blue'],
            'cores' => []
        ];
        for ($c = 0; $c < $core_count; $c++) {
            $core_name = $core_color_names[$c % count($core_color_names)];
            $tube['cores'][] = [
                'name' => $core_name,
                'color' => $tube_colors[$core_name]
            ];
        }
        $tubes[] = $tube;
    }
    return $tubes;
}

try {
    // Fetch all connections
    $stmt = $conn->query("SELECT id, core_count FROM connections");
    $connections = $stmt->fetchAll();
    if (!$connections) {
        echo "No connections found.\n";
        exit(0);
    }
    $insert = $conn->prepare("INSERT INTO fiber_tubes (connection_id, tube_number, tube_name, tube_color, core_number, core_name, core_color) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $deleted = $conn->exec("DELETE FROM fiber_tubes");
    echo "Deleted $deleted existing fiber_tubes rows.\n";
    $total = 0;
    foreach ($connections as $conn_row) {
        $conn_id = $conn_row['id'];
        $core_count = (int)$conn_row['core_count'];
        $tubes = getTubeAndCoreAssignments($core_count);
        foreach ($tubes as $t_idx => $tube) {
            foreach ($tube['cores'] as $c_idx => $core) {
                $insert->execute([
                    $conn_id,
                    $t_idx + 1,
                    $tube['name'],
                    $tube['color'],
                    $c_idx + 1,
                    $core['name'],
                    $core['color']
                ]);
                $total++;
            }
        }
    }
    echo "Populated $total fiber_tubes rows for " . count($connections) . " connections.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 