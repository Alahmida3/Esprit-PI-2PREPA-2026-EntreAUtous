<?php
/**
 * View/PdfVenteView.php
 * Vue d'impression / export PDF des ventes d'une pièce.
 * Accessible via index.php?action=export_pdf_vente&id=<id_piece>
 * Pour un vrai PDF binaire, intégrer mPDF ou TCPDF.
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport Ventes – <?php echo htmlspecialchars($piece['nom_piece']); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a202c; background: #fff; padding: 40px; }

        /* ── Header ── */
        .pdf-header { display: flex; justify-content: space-between; align-items: flex-start;
                      border-bottom: 3px solid #3498db; padding-bottom: 18px; margin-bottom: 28px; }
        .pdf-header .logo-zone h1 { font-size: 1.6rem; color: #2c3e50; font-weight: 700; }
        .pdf-header .logo-zone p  { color: #718096; font-size: 0.85rem; margin-top: 4px; }
        .pdf-header .date-zone    { text-align: right; color: #718096; font-size: 0.82rem; }
        .pdf-header .date-zone strong { display: block; font-size: 1rem; color: #2c3e50; }

        /* ── Fiche pièce ── */
        .piece-info { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px;
                      padding: 18px 22px; margin-bottom: 28px; display: grid;
                      grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .piece-info .info-item label { display: block; font-size: 0.72rem; text-transform: uppercase;
                                       letter-spacing: .06em; color: #718096; margin-bottom: 3px; }
        .piece-info .info-item span  { font-weight: 600; font-size: 0.95rem; color: #2d3748; }

        /* ── Résumé stats ── */
        .stat-row { display: flex; gap: 16px; margin-bottom: 28px; }
        .stat-box { flex: 1; background: #ebf8ff; border: 1px solid #bee3f8; border-radius: 8px;
                    padding: 14px 18px; text-align: center; }
        .stat-box.green  { background: #f0fff4; border-color: #9ae6b4; }
        .stat-box.orange { background: #fffaf0; border-color: #fbd38d; }
        .stat-box label  { display: block; font-size: 0.72rem; text-transform: uppercase;
                           letter-spacing: .06em; color: #718096; margin-bottom: 5px; }
        .stat-box .val   { font-size: 1.5rem; font-weight: 700; color: #2b6cb0; }
        .stat-box.green .val  { color: #276749; }
        .stat-box.orange .val { color: #c05621; }

        /* ── Tableau ── */
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; margin-bottom: 28px; }
        thead tr { background: #2b6cb0; color: #fff; }
        thead th { padding: 10px 12px; text-align: left; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f7fafc; }
        tbody tr:hover { background: #ebf8ff; }
        tbody td { padding: 9px 12px; border-bottom: 1px solid #e2e8f0; color: #2d3748; }
        .badge-qty { background: #3498db; color: #fff; padding: 2px 8px; border-radius: 12px;
                     font-size: 0.8rem; font-weight: 600; }
        .text-right { text-align: right; }
        tfoot tr { background: #2c3e50; color: #fff; font-weight: 700; }
        tfoot td { padding: 10px 12px; }

        /* ── Footer ── */
        .pdf-footer { border-top: 1px solid #e2e8f0; padding-top: 12px;
                      display: flex; justify-content: space-between;
                      color: #a0aec0; font-size: 0.78rem; }

        /* ── Bouton (masqué à l'impression) ── */
        .no-print { text-align: center; margin-bottom: 28px; }
        .btn-print { background: #3498db; color: #fff; border: none; padding: 10px 28px;
                     border-radius: 6px; font-size: 0.95rem; cursor: pointer; font-weight: 600; }
        .btn-back  { background: #718096; color: #fff; border: none; padding: 10px 20px;
                     border-radius: 6px; font-size: 0.95rem; cursor: pointer; margin-right: 10px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 20px; }
        }
    </style>
</head>
<body>

<!-- Boutons d'action -->
<div class="no-print">
    <button class="btn-back" onclick="window.location='index.php?action=admin'">
        ← Retour Admin
    </button>
    <button class="btn-print" onclick="window.print()">
        🖨️ Imprimer / Sauvegarder en PDF
    </button>
</div>

<!-- En-tête -->
<div class="pdf-header">
    <div class="logo-zone">
        <h1>📋 Rapport des Ventes</h1>
        <p>Système de Gestion de Vente – Pièces Automobiles</p>
    </div>
    <div class="date-zone">
        <strong>Généré le</strong>
        <?php echo date('d/m/Y à H:i'); ?>
    </div>
</div>

<!-- Fiche pièce -->
<div class="piece-info">
    <div class="info-item">
        <label>Nom de la pièce</label>
        <span><?php echo htmlspecialchars($piece['nom_piece']); ?></span>
    </div>
    <div class="info-item">
        <label>Référence</label>
        <span><?php echo htmlspecialchars($piece['reference']); ?></span>
    </div>
    <div class="info-item">
        <label>Catégorie</label>
        <span><?php echo htmlspecialchars($piece['categorie'] ?? '—'); ?></span>
    </div>
    <div class="info-item">
        <label>Prix unitaire</label>
        <span><?php echo number_format($piece['prix'], 2, ',', ' '); ?> €</span>
    </div>
    <div class="info-item">
        <label>Stock actuel</label>
        <span><?php echo $piece['quantite_stock']; ?> unité(s)</span>
    </div>
    <div class="info-item">
        <label>Fournisseur</label>
        <span><?php echo htmlspecialchars($piece['fourniseur'] ?? '—'); ?></span>
    </div>
</div>

<!-- Résumé statistiques -->
<div class="stat-row">
    <div class="stat-box">
        <label>Nombre de ventes</label>
        <div class="val"><?php echo $stats['nb_ventes']; ?></div>
    </div>
    <div class="stat-box green">
        <label>Quantité totale vendue</label>
        <div class="val"><?php echo $stats['total_qte']; ?></div>
    </div>
    <div class="stat-box orange">
        <label>Chiffre d'affaires total</label>
        <div class="val"><?php echo number_format($totalCA, 2, ',', ' '); ?> €</div>
    </div>
</div>

<!-- Tableau des ventes -->
<?php if (count($ventes) > 0): ?>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>ID Vente</th>
            <th>Quantité</th>
            <th>Prix unit.</th>
            <th class="text-right">Sous-total</th>
            <th>Date de vente</th>
        </tr>
    </thead>
    <tbody>
        <?php $grandTotal = 0; $i = 1; foreach ($ventes as $v):
            $sous_total = $v['quantite'] * $v['prix'];
            $grandTotal += $sous_total;
        ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo $v['id']; ?></td>
            <td><span class="badge-qty"><?php echo $v['quantite']; ?></span></td>
            <td><?php echo number_format($v['prix'], 2, ',', ' '); ?> €</td>
            <td class="text-right"><?php echo number_format($sous_total, 2, ',', ' '); ?> €</td>
            <td><?php echo date('d/m/Y H:i', strtotime($v['date_vente'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">TOTAL</td>
            <td class="text-right"><?php echo number_format($grandTotal, 2, ',', ' '); ?> €</td>
            <td></td>
        </tr>
    </tfoot>
</table>
<?php else: ?>
<p style="color:#718096;text-align:center;padding:30px 0;">Aucune vente enregistrée pour cette pièce.</p>
<?php endif; ?>

<!-- Pied de page -->
<div class="pdf-footer">
    <span>Pièce : <?php echo htmlspecialchars($piece['nom_piece']); ?> (Réf. <?php echo htmlspecialchars($piece['reference']); ?>)</span>
    <span>Page 1 / 1</span>
</div>

</body>
</html>
