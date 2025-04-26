<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['file_path'])) {
    echo json_encode(["error" => "No file path provided"]);
    exit;
}

$file_path = escapeshellarg($data['file_path']);
$python_script = escapeshellarg(__DIR__ . "/process_data.py");

// Run Python script and capture errors
$command = "python $python_script $file_path 2>&1";
exec($command, $output, $return_code);

if ($return_code === 0) {
    echo json_encode([
        "success" => "Script executed successfully",
        "html_file" => "uploads/output.html"
    ]);
} else {
    echo json_encode([
        "error" => "Script execution failed",
        "details" => implode("\n", $output)
    ]);
}
?>
