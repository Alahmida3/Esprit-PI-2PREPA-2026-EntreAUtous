<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

$type_service = trim($_POST['type_service'] ?? '');
$kilometrage  = (int)($_POST['kilometrage'] ?? 0);
$marque       = trim($_POST['marque'] ?? '');

if (empty($type_service)) {
    echo json_encode(['success' => false, 'message' => 'Type de service requis']);
    exit;
}

function estimerTemps($service, $km, $marque) {
    $s = strtolower($service);
    $m = strtolower($marque);

    $regles = [
        'vidange'        => ['min' => 25, 'max' => 45, 'niveau' => 'Simple'],
        'vidange moteur' => ['min' => 25, 'max' => 45, 'niveau' => 'Simple'],
        'lavage'         => ['min' => 30, 'max' => 50, 'niveau' => 'Simple'],
        'révision'       => ['min' => 90, 'max' => 180, 'niveau' => 'Modéré'],
        'révision complète' => ['min' => 120, 'max' => 240, 'niveau' => 'Complexe'],
        'freins'         => ['min' => 60, 'max' => 120, 'niveau' => 'Modéré'],
        'freinage'       => ['min' => 60, 'max' => 120, 'niveau' => 'Modéré'],
        'pneus'          => ['min' => 30, 'max' => 60, 'niveau' => 'Simple'],
        'pneumatiques'   => ['min' => 30, 'max' => 60, 'niveau' => 'Simple'],
        'distribution'   => ['min' => 240,'max' => 420, 'niveau' => 'Complexe'],
        'courroie'       => ['min' => 180,'max' => 300, 'niveau' => 'Complexe'],
        'climatisation'  => ['min' => 45, 'max' => 90,  'niveau' => 'Modéré'],
        'diagnostic'     => ['min' => 30, 'max' => 60,  'niveau' => 'Simple'],
        'embrayage'      => ['min' => 120,'max' => 240, 'niveau' => 'Complexe'],
    ];

    $base = ['min' => 60, 'max' => 120, 'niveau' => 'Modéré'];
    foreach ($regles as $key => $val) {
        if (str_contains($s, $key)) {
            $base = $val;
            break;
        }
    }

    // Ajustements marque premium
    $premium = ['bmw','mercedes','audi','porsche','volvo','land rover'];
    foreach ($premium as $p) {
        if (str_contains($m, $p)) {
            $base['min'] = (int)($base['min'] * 1.25);
            $base['max'] = (int)($base['max'] * 1.25);
            break;
        }
    }

    // Kilométrage élevé
    if ($km > 200000) {
        $base['min'] += 30;
        $base['max'] += 40;
    } elseif ($km > 150000) {
        $base['min'] += 15;
        $base['max'] += 20;
    }

    return [
        'success'           => true,
        'duree_min'         => (int)(($base['min'] + $base['max']) / 2),
        'fourchette'        => $base['min'] . ' à ' . $base['max'] . ' minutes',
        'niveau_complexite' => $base['niveau'],
        'explication'       => "Estimation basée sur le service « {$service} »",
        'conseils'          => $km > 150000 
            ? "Inspection complète recommandée à ce kilométrage." 
            : "Vérifiez les niveaux de fluides et filtres.",
        'source'            => 'regles'
    ];
}

echo json_encode(estimerTemps($type_service, $kilometrage, $marque));
exit;