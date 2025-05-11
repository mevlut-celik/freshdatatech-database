<?php
date_default_timezone_set("Europe/Istanbul");
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// VeritabanÄ± baÄŸlantÄ± bilgileri
$host = 'localhost';
$db   = 'iot_logs';
$user = 'iot_user';
$pass = 'pwdiot_0000';
$charset = 'utf8mb4';
$timestamp = date("Y-m-d H:i:s");
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 🔹 Frontend veri görüntüleme isteği
    try {
        $stmt = $pdo->query("SELECT * FROM temperature_logs ORDER BY timestamp DESC LIMIT 50");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Veri çekilemedi: " . $e->getMessage()]);
    }

} elseif ($method === 'POST') {
    // 🔹 Python'dan gelen veri
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['device_id']) || !isset($data['temperature'])) {
        echo json_encode(["status" => "error", "message" => "Eksik veri"]);
        exit;
    }

    // PHP ile timestamp oluştur
    $timestamp = date("Y-m-d H:i:s");

    try {
        $stmt = $pdo->prepare("INSERT INTO temperature_logs (device_id, temperature, timestamp) VALUES (?, ?, ?)");
        $stmt->execute([$data['device_id'], $data['temperature'], $timestamp]);
        echo json_encode(["status" => "success", "message" => "Veri kaydedildi", "timestamp" => $timestamp]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Veri kaydı başarısız: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Sadece GET veya POST destekleniyor"]);
}
?>