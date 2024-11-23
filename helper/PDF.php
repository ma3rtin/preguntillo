<?php
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

class PDF extends FPDF {
    public function agregarTitulo($titulo)
    {
        $this->AddPage();
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, $titulo, 0, 1, 'C');
        $this->Ln(10);
    }

    public function agregarEstadisticasGenerales($cantJugadores, $cantPartidas, $cantPreguntas)
    {
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, "Cantidad de jugadores: $cantJugadores", 0, 1);
        $this->Cell(0, 10, "Cantidad de partidas: $cantPartidas", 0, 1);
        $this->Cell(0, 10, "Cantidad de preguntas: $cantPreguntas", 0, 1);
        $this->Ln(10);
    }

    public function agregarTablaUsuarios($data)
    {
        $this->SetFont('Arial', 'B', 12);

        $this->Cell(30, 10, 'Usuario', 1, 0, 'C');
        $this->Cell(30, 10, 'Nombre', 1, 0, 'C');
        $this->Cell(70, 10, 'Cantidad de partidas jugadas', 1, 0, 'C');
        $this->Cell(60, 10, 'Porcentaje de aciertos', 1, 0, 'C');
        $this->Ln();

        $this->SetFont('Arial', '', 9);
        foreach ($data['usuarios'] as $usuario) {
            $this->Cell(30, 10, $usuario['usuario'], 1,0,'C');
            $this->Cell(30, 10, $usuario['nombre'], 1,0,'C');
            $this->Cell(70, 10, $usuario['cant_partidas'], 1,0,'C');
            $this->Cell(60, 10, $usuario['porcentaje_aciertos'], 1,0,'C');
            $this->Ln();
        }
    }

    public function generarReporte($filename = 'Reporte_Estadisticas.pdf')
    {
        $this->Output('I', $filename);
    }
}
