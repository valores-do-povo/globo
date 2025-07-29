<?php
// Cabeçalhos
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Verifica se veio via POST e se o CPF foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['cpf'])) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'CPF não fornecido.']);
    exit;
}

// Limpa e valida o CPF
$cpf = preg_replace('/\D/', '', $_POST['cpf']);
if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'CPF inválido.']);
    exit;
}

// Dados da API externa
$token = 'ab34229f-cd38-4621-97a9-6c1c7cbd354b';
$apiUrl = "https://apela-api.tech?user={$token}&cpf={$cpf}";

// cURL para chamada segura
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Erro na requisição externa
if ($response === false || $httpCode !== 200) {
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => "Erro ao consultar a API externa: {$error}"
    ]);
    exit;
}

// Decodifica resposta
$data = json_decode($response, true);

// Retorna status e dados
if (isset($data['status']) && $data['status'] == 200) {
    echo json_encode(['status' => 200, 'data' => $data]);
} else {
    echo json_encode([
        'status' => $data['status'] ?? 404,
        'message' => $data['message'] ?? 'CPF não encontrado.'
    ]);
}
?>
