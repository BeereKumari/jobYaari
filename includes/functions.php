<?php

function sanitize($input) {
    return trim($input);
}

function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

function truncate($text, $length = 120) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

function uploadImage($file, $uploadDir) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, and WebP allowed.'];
    }

    if ($file['size'] > $maxSize) {
        return ['error' => 'File size exceeds 2MB limit.'];
    }

    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['error' => 'Invalid image file.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('blog_', true) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        resizeImage($destination, $destination, 800);
        return ['success' => true, 'filename' => $filename];
    }

    return ['error' => 'Failed to upload image.'];
}

function resizeImage($source, $destination, $maxWidth) {
    $info = getimagesize($source);
    $width = $info[0];
    $height = $info[1];
    $type = $info[2];

    if ($width <= $maxWidth) {
        return;
    }

    $newHeight = ($height * $maxWidth) / $width;

    switch ($type) {
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($source);
            break;
        default:
            $image = imagecreatefromjpeg($source);
            break;
    }

    $newImage = imagecreatetruecolor($maxWidth, $newHeight);

    if ($type === IMAGETYPE_PNG) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);

    switch ($type) {
        case IMAGETYPE_PNG:
            imagepng($newImage, $destination, 6);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($newImage, $destination, 85);
            break;
        default:
            imagejpeg($newImage, $destination, 85);
            break;
    }

    imagedestroy($image);
    imagedestroy($newImage);
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
