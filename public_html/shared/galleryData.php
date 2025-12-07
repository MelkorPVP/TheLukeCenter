<?php
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Gallery data is only available in the TEST environment.']);
