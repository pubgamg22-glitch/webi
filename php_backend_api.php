<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../models/Place.php';
require_once '../models/Picnic.php';
require_once '../models/Booking.php';

$database = new Database();
$db = $database->connect();

// Authentication Middleware
$authToken = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!validateToken($authToken)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Input Validation Functions
function validatePlaceData($data) {
    $errors = [];
    if (empty($data['name'])) $errors['name'] = 'Name is required';
    if (empty($data['location'])) $errors['location'] = 'Location is required';
    if (empty($data['type']) || !in_array($data['type'], ['park', 'beach', 'garden', 'forest', 'lakeside', 'indoor'])) {
        $errors['type'] = 'Invalid place type';
    }
    if (!isset($data['capacity']) || !is_numeric($data['capacity']) || $data['capacity'] < 1) {
        $errors['capacity'] = 'Capacity must be a positive number';
    }
    return $errors;
}

function validatePicnicData($data) {
    $errors = [];
    if (empty($data['name'])) $errors['name'] = 'Name is required';
    if (empty($data['date']) || !strtotime($data['date'])) $errors['date'] = 'Valid date is required';
    if (empty($data['time']) || !preg_match('/^\d{2}:\d{2}$/', $data['time'])) $errors['time'] = 'Valid time is required';
    if (empty($data['place_id']) || !is_numeric($data['place_id'])) $errors['place_id'] = 'Valid place is required';
    return $errors;
}

// API Routing
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$resource = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
$id = array_shift($request);

try {
    switch($resource) {
        case 'places':
            if ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $errors = validatePlaceData($data);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(['errors' => $errors]);
                    break;
                }
                $place = new Place($db);
                $result = $place->create($data);
                http_response_code(201);
                echo json_encode(['id' => $result, 'message' => 'Place created']);
            }
            // Other methods...
            break;
            
        case 'picnics':
            if ($method == 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $errors = validatePicnicData($data);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(['errors' => $errors]);
                    break;
                }
                $picnic = new Picnic($db);
                $result = $picnic->create($data);
                http_response_code(201);
                echo json_encode(['id' => $result, 'message' => 'Picnic created']);
            }
            // Other methods...
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function validateToken($token) {
    // In production, use JWT or similar
    $validToken = 'your_secure_token'; // Should be from environment
    return str_replace('Bearer ', '', $token) === $validToken;
}
?>
