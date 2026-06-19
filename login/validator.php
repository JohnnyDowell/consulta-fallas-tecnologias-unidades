<?php

$host =
    (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
    $_SERVER["SERVER_PORT"] == 443
        ? "https"
        : "http";
$host .= "://" . $_SERVER["HTTP_HOST"];
$host = "https://ses.lidcorp.mx";

$url = $host . "/Master-API/ses/System/ValidateReportLogin";

$user = $_POST["user"] ?? null;
$pass = $_POST["pass"] ?? null;

$reporte = "VerReportesDeFallasTecnologicas"; // reporte de login para fallas de tecnologia

$fields = [
    "user" => $user,
    "pass" => $pass,
    "reporte" => $reporte,
];

// Inicializar cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields)); // Convertir a formato form-urlencoded
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/x-www-form-urlencoded",
]);

// Ejecutar la solicitud cURL
$response = json_decode(curl_exec($ch));

$error = $response->error;

if (!$error) {
    session_start();
    $name = $response->data->name;
    $actualizar = !empty($response->data->actualizar);
    $_SESSION["usuario"] = $name;
    $_SESSION["nomina"] = $user;
    $_SESSION["permiso"] = $actualizar;
    header("Location: ./../");
} else {
    session_start();
    $message = $response->message;
    $_SESSION["error-message"] = $message;
    header("Location: ./../login/");
}
