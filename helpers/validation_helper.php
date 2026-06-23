<?php
function validateRequired(array $data, array $fields): ?string {
    foreach ($fields as $f) {
        if (!isset($data[$f]) || trim($data[$f]) === '') {
            return "Field '$f' wajib diisi";
        }
    }
    return null;
}

function getBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}
