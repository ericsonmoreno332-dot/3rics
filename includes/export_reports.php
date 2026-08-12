<?php

declare(strict_types=1);

/**
 * Intenta enviar un PDF con mPDF. Devuelve false si no está disponible o falla.
 *
 * @param list<array<string,mixed>> $rows
 */
function try_send_pdf_report(array $rows): bool
{
    if (!class_exists(\Mpdf\Mpdf::class)) {
        return false;
    }

    $root = dirname(__DIR__);
    $tmp = $root . '/storage/tmp';
    if (!is_dir($tmp)) {
        @mkdir($tmp, 0775, true);
    }

    // Embed logo if exists
    $logoPath = dirname(__DIR__) . '/public/municipio.jpg';
    $logoHtml = '';
    if (file_exists($logoPath)) {
        $logoHtml = '<img src="' . htmlspecialchars($logoPath) . '" style="height: 60px; margin-right: 15px; vertical-align: middle;">';
    }

    $html = '<div style="border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 20px; clear: both;">';
    $html .= $logoHtml;
    $html .= '<h1 style="color: #1e3a8a; margin: 0; padding-top: 10px; font-family: sans-serif;">Reporte de Asistencias</h1>';
    $html .= '<p style="color: #64748b; margin: 5px 0 0 0; font-family: sans-serif; font-size: 12px;">Generado: ' . htmlspecialchars(date('d/m/Y H:i')) . '</p>';
    $html .= '<div style="clear: both;"></div></div>';
    
    $html .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:11px;font-family:sans-serif;text-align:left;">';
    $html .= '<thead><tr style="background-color:#1e40af;color:#ffffff;text-transform:uppercase;">';
    $html .= '<th>Fecha</th><th>DNI</th><th>Nombre</th><th>Área</th><th>Institución</th>';
    $html .= '<th>Entrada</th><th>Salida</th><th>Horas</th><th>Estado</th><th>Obs.</th>';
    $html .= '</tr></thead><tbody>';
    
    $bg = false;
    foreach ($rows as $r) {
        $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null);
        $bgColor = $bg ? '#f8fafc' : '#ffffff';
        $bg = !$bg;
        $html .= '<tr style="background-color:' . $bgColor . ';">';
        $html .= '<td>' . htmlspecialchars((string) $r['fecha']) . '</td>';
        $html .= '<td>' . htmlspecialchars((string) $r['dni']) . '</td>';
        $html .= '<td>' . htmlspecialchars(nombre_completo($r['nombres'], $r['apellidos'])) . '</td>';
        $html .= '<td>' . htmlspecialchars((string) ($r['area_nombre'] ?? '')) . '</td>';
        $html .= '<td>' . htmlspecialchars((string) ($r['institucion_nombre'] ?? '')) . '</td>';
        $html .= '<td>' . htmlspecialchars($r['hora_entrada'] ? substr((string) $r['hora_entrada'], 0, 5) : '') . '</td>';
        $html .= '<td>' . htmlspecialchars($r['hora_salida'] ? substr((string) $r['hora_salida'], 0, 5) : '') . '</td>';
        $html .= '<td>' . htmlspecialchars($ht ? substr($ht, 0, 5) : '') . '</td>';
        $html .= '<td>' . htmlspecialchars((string) $r['estado']) . '</td>';
        $html .= '<td>' . htmlspecialchars((string) ($r['observacion'] ?? '')) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    try {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'tempDir' => $tmp,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
        ]);
        
        // Protección anti-fraude
        $mpdf->SetProtection(['print'], '', 'admin2026');
        $mpdf->SetWatermarkText('DOCUMENTO OFICIAL');
        $mpdf->watermark_font = 'sans-serif';
        $mpdf->showWatermarkText = true;
        
        $mpdf->WriteHTML($html);
        $mpdf->Output('reporte_asistencia.pdf', 'D');
    } catch (Throwable) {
        return false;
    }

    return true;
}

/**
 * Intenta enviar XLSX. Devuelve false si no está disponible o falla.
 *
 * @param list<array<string,mixed>> $rows
 */
function try_send_xlsx_report(array $rows): bool
{
    if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
        return false;
    }

    try {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencias');

        // Protección anti-fraude
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('admin2026');

        // Estilos de Cabecera
        $styleHeader1 = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B2A47']],
        ];
        $styleHeader2 = [
            'font' => ['color' => ['rgb' => '8F9BB3'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B2A47']],
            'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '2563EB']]]
        ];
        $styleCols = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ];
        $styleData = [
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]]
        ];

        // Filas 1 y 2
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', '🖨 MUNICIPALIDAD DE PISCO — REPORTE DE ASISTENCIA');
        $sheet->getStyle('A1:L1')->applyFromArray($styleHeader1);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'Sistema de Control de Asistencias · Formación Práctica en Empresa');
        $sheet->getStyle('A2:L2')->applyFromArray($styleHeader2);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $headers = ['Fecha', 'DNI', 'Apellidos', 'Nombres', 'Carrera', 'Área', 'Institución', 'Entrada', 'Salida', 'Horas Trab.', 'Estado', 'Observación'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '3', $h);
        }
        $sheet->getStyle('A3:L3')->applyFromArray($styleCols);
        $sheet->getRowDimension(3)->setRowHeight(25);

        $rowNum = 4;
        $tardanzas = 0;
        
        foreach ($rows as $r) {
            $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null);
            $estado = ucfirst((string) $r['estado']);
            
            // Si el estado original es completada, lo mostramos como Presente
            if (strtolower($estado) === 'completada') {
                $estado = 'Presente';
            }
            if (stripos($estado, 'tardanza') !== false || stripos((string)($r['observacion'] ?? ''), 'tardanza') !== false) {
                $tardanzas++;
                $estado = 'Tardanza'; // Normalizar para la vista Excel
            }
            
            $vals = [
                (string) $r['fecha'],
                (string) $r['dni'],
                (string) $r['apellidos'],
                (string) $r['nombres'],
                (string) $r['carrera'],
                (string) ($r['area_nombre'] ?? ''),
                (string) ($r['institucion_nombre'] ?? ''),
                $r['hora_entrada'] ? substr((string) $r['hora_entrada'], 0, 8) : '',
                $r['hora_salida']  ? substr((string) $r['hora_salida'], 0, 8)  : '',
                $ht ?? '',
                $estado,
                (string) ($r['observacion'] ?? ''),
            ];
            
            foreach ($vals as $i => $v) {
                $sheet->setCellValueExplicit($cols[$i] . $rowNum, $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $sheet->getStyle("A{$rowNum}:L{$rowNum}")->applyFromArray($styleData);

            // Condicional de color para "Estado"
            if ($estado === 'Tardanza') {
                $sheet->getStyle("K{$rowNum}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9C3']]
                ]);
            } elseif ($estado === 'Presente') {
                $sheet->getStyle("K{$rowNum}")->applyFromArray([
                    'font' => ['color' => ['rgb' => '16A34A'], 'bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']]
                ]);
            }
            
            ++$rowNum;
        }

        // Fila de Pie (Totales)
        $sheet->mergeCells("A{$rowNum}:I{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", "Total de registros: " . count($rows));
        $sheet->getStyle("A{$rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']]
        ]);

        $sheet->getStyle("J{$rowNum}")->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']]
        ]);
        
        $sheet->mergeCells("K{$rowNum}:L{$rowNum}");
        $sheet->setCellValue("K{$rowNum}", "⚠ Tardanzas: " . $tardanzas);
        $sheet->getStyle("K{$rowNum}:L{$rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9C3']]
        ]);
        $sheet->getStyle("A{$rowNum}:L{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CBD5E1'));
        $sheet->getRowDimension($rowNum)->setRowHeight(25);

        // Fila de Créditos
        $rowNum += 2;
        $sheet->mergeCells("A{$rowNum}:L{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", "Generado automáticamente · Sistema de Asistencia - Municipalidad de Pisco · " . date('Y'));
        $sheet->getStyle("A{$rowNum}")->applyFromArray([
            'font' => ['color' => ['rgb' => '94A3B8'], 'italic' => true, 'size' => 9],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Auto size
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="reporte_asistencia.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    } catch (Throwable) {
        return false;
    }

    return true;
}
