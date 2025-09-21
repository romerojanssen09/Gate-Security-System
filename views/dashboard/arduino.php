<?php
function sendToArduino($cmd) {
    $url = "http://127.0.0.1:5000/send"; // Python bridge API
    $data = http_build_query(['cmd' => $cmd]);
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $data
        ]
    ];
    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

// Check if command is provided via URL (GET)
if (isset($_GET['cmd'])) {
    $cmd = strtolower($_GET['cmd']); // normalize command
    if (in_array($cmd, ["open", "close", "unauthorized"])) {
        header('Content-Type: application/json');
        echo sendToArduino($cmd);
    } else {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Invalid command"]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["error" => "No command provided"]);
}
?>
