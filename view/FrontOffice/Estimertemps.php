<?php

header('Content-Type: application/json; charset=utf-8');

$type_service = trim($_POST['type_service'] ?? '');
$kilometrage  = (int)($_POST['kilometrage']  ?? 0);
$marque       = trim($_POST['marque']        ?? '');
$annee        = trim($_POST['annee']         ?? '');
$modele       = trim($_POST['modele']        ?? '');

if (empty($type_service)) {
    echo json_encode(['success' => false, 'message' => 'Le type de service est obligatoire.']);
    exit;
}

function estimerTemps(string $service, int $km, string $marque, string $annee, string $modele): array
{
    return fallbackEstimation($service, $marque, $km, $annee);
}

function fallbackEstimation(string $service, string $marque, int $kilometrage = 0, string $annee = ''): array
{
    $s = mb_strtolower($service);
    
    // Déterminer la durée selon le type de service
    $duree = match(true) {
        str_contains($s, 'lavage')        => 40,
        str_contains($s, 'vidange')       => 60,
        str_contains($s, 'frein')         => 90,
        str_contains($s, 'pneu')          => 45,
        str_contains($s, 'révision')      => 120,
        str_contains($s, 'courroie')      => 180,
        str_contains($s, 'climatisation') => 75,
        str_contains($s, 'diagnostic')    => 60,
        str_contains($s, 'embrayage')     => 240,
        str_contains($s, 'amortisseur')   => 120,
        default                           => 90,
    };
    
    // Ajuster selon le kilométrage
    $ajustement = '';
    $dureeAjustee = $duree;
    
    if ($kilometrage > 100000) {
        $dureeAjustee = (int)($duree * 1.2);
        $ajustement = " (véhicule haut kilométrage : +20%)";
    } elseif ($kilometrage > 50000 && $kilometrage <= 100000) {
        $dureeAjustee = (int)($duree * 1.1);
        $ajustement = " (kilométrage intermédiaire : +10%)";
    }
    
    // Ajuster selon la marque (simplifié)
    $marqueAjustement = '';
    $marquesPremium = ['mercedes', 'bmw', 'audi', 'porsche', 'volvo', 'lexus'];
    $marqueLower = mb_strtolower($marque);
    
    foreach ($marquesPremium as $mp) {
        if (str_contains($marqueLower, $mp)) {
            $dureeAjustee = (int)($dureeAjustee * 1.15);
            $marqueAjustement = " (marque premium : +15%)";
            break;
        }
    }
    
    // Déterminer la fourchette
    $minFourchette = $dureeAjustee - 15;
    $maxFourchette = $dureeAjustee + 30;
    $fourchette = "$minFourchette-" . ($maxFourchette) . " min";
    
    // Niveau de complexité
    $niveauComplexite = match(true) {
        $dureeAjustee >= 180 => 'Complexe',
        $dureeAjustee >= 90  => 'Modéré',
        default              => 'Simple'
    };
    
    // Explication détaillée
    $explication = "Estimation pour « $service »" . ($marque ? " sur $marque" : '') . 
                   ". Temps de base: $duree min$ajustement$marqueAjustement.";
    
    // Conseils personnalisés
    $conseils = match(true) {
        str_contains($s, 'vidange')    => "Prévoir un filtre à huile neuf.",
        str_contains($s, 'courroie')   => "Vérifier également la pompe à eau.",
        str_contains($s, 'révision')   => "Comprend vidange + filtres + contrôles.",
        str_contains($s, 'frein')      => "Vérifier l'état du liquide de frein.",
        str_contains($s, 'climatisation') => "Prévoir recharge de gaz si nécessaire.",
        default                        => "Prévoir un véhicule de prêt si besoin."
    };
    
    // Type normalisé
    $typeNormalise = match(true) {
        str_contains($s, 'vidange')    => 'Vidange',
        str_contains($s, 'frein')      => 'Freinage',
        str_contains($s, 'pneu')       => 'Pneumatiques',
        str_contains($s, 'courroie')   => 'Courroie distribution',
        str_contains($s, 'climatisation') => 'Climatisation',
        str_contains($s, 'révision')   => 'Révision complète',
        default                        => $service
    };
    
    return [
        'success'                => true,
        'duree_min'              => $dureeAjustee,
        'fourchette'             => $fourchette,
        'niveau_complexite'      => $niveauComplexite,
        'explication'            => $explication,
        'conseils'               => $conseils,
        'type_service_normalise' => $typeNormalise,
        'source'                 => '📊 Estimation standard',
        'duree_base'             => $duree,
        'ajustements'            => trim($ajustement . $marqueAjustement)
    ];
}

echo json_encode(
    estimerTemps($type_service, $kilometrage, $marque, $annee, $modele),
    JSON_UNESCAPED_UNICODE
);
exit;