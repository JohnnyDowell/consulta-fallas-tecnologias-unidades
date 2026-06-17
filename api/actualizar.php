<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'message' => 'Método no permitido. Use POST.']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$resolucion = isset($_POST['resolucion']) ? trim($_POST['resolucion']) : '';
$tipoFalla = isset($_POST['tipoFalla']) ? trim($_POST['tipoFalla']) : '';

if (!$id) {
    echo json_encode(['error' => true, 'message' => 'El ID del reporte es requerido.']);
    exit;
}

if ($resolucion === '') {
    echo json_encode(['error' => true, 'message' => 'La resolución es requerida.']);
    exit;
}

if ($tipoFalla !== 'Falla' && $tipoFalla !== 'Daño') {
    echo json_encode(['error' => true, 'message' => 'El tipo de falla debe ser "Falla" o "Daño".']);
    exit;
}

$filePath = __DIR__ . '/../data/reportes.json';

if (!file_exists($filePath)) {
    echo json_encode(['error' => true, 'message' => 'No se encontró el archivo de datos.']);
    exit;
}

$jsonData = file_get_contents($filePath);
$dataArray = json_decode($jsonData, true);

if (!$dataArray || !isset($dataArray['data'])) {
    echo json_encode(['error' => true, 'message' => 'Error al decodificar los datos del JSON.']);
    exit;
}

$found = false;
foreach ($dataArray['data'] as &$reporte) {
    if ((int)$reporte['Id'] === $id) {
        $reporte['Resolucion'] = $resolucion;
        $reporte['TipoFalla'] = $tipoFalla;
        $reporte['FechaCerrado'] = date('Y-m-d H:i:s');
        $reporte['Estado'] = 'Atendido';
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['error' => true, 'message' => 'No se encontró ningún reporte con el ID especificado.']);
    exit;
}

// Guardar de nuevo en el archivo
$newJsonData = json_encode($dataArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (file_put_contents($filePath, $newJsonData) === false) {
    echo json_encode(['error' => true, 'message' => 'Error al escribir los cambios en el archivo.']);
    exit;
}

echo json_encode(['error' => false, 'message' => 'Seguimiento guardado correctamente y reporte cerrado.']);
exit;
