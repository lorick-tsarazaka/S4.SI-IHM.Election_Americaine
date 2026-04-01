<?php

require_once __DIR__ . '/fpdf/fpdf.php';

$resultats = $resultatsTotal ?? [];

usort($resultats, function ($a, $b) {
	return (int) $b['total_grands_electeurs'] - (int) $a['total_grands_electeurs'];
});

$vainqueur = 'N/A';
foreach ($resultats as $resultat) {
	if ((int) $resultat['total_grands_electeurs'] > 0) {
		$vainqueur = (string) $resultat['candidat_nom'];
		break;
	}
}

if (ob_get_length() > 0) {
	ob_end_clean();
}

$toPdfText = static function (string $text): string {
	$converted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
	return $converted === false ? $text : $converted;
};

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Resultats election americaine', 0, 1, 'C');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Date: ' . date('d/m/Y H:i'), 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(120, 8, 'Candidat', 1, 0, 'L', true);
$pdf->Cell(70, 8, 'Nb Grand electeur', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 11);
foreach ($resultats as $resultat) {
	$nom = $toPdfText((string) $resultat['candidat_nom']);
	$nbGe = (int) $resultat['total_grands_electeurs'];

	$pdf->Cell(120, 8, $nom, 1, 0, 'L');
	$pdf->Cell(70, 8, (string) $nbGe, 1, 1, 'C');
}

$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, $toPdfText('Vainqueur : ' . $vainqueur), 0, 1, 'L');

$pdf->Output('I', 'resultats_election.pdf');
exit;
