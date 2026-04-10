<?php
/**
 * Image Helper Functions
 * File: helpers/ImageHelper.php
 */

function getAvatarUrl($user, $size = 'md') {
    if (!empty($user['photo']) && file_exists(ROOT_PATH . '/uploads/' . $user['photo'])) {
        return BASE_URL . '/uploads/' . $user['photo'];
    }
    
    // Return default avatar based on initials
    $initials = strtoupper(substr($user['firstname'] ?? '', 0, 1) . substr($user['lastname'] ?? '', 0, 1));
    if (empty($initials)) {
        $initials = 'U';
    }
    
    // You can use a service like ui-avatars.com for default avatars
    return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&size=128&background=377dff&color=fff";
}

function getCoverUrl($userId) {
    $coverPath = ROOT_PATH . '/uploads/users/cover_' . $userId . '.jpg';
    if (file_exists($coverPath)) {
        return BASE_URL . '/uploads/users/cover_' . $userId . '.jpg';
    }
    
    // Default cover images based on user role or random
    $defaultCovers = [
        1 => 'img2.jpg',
        2 => 'img1.jpg',
        3 => 'img1.jpg'
    ];
    $randomCover = $defaultCovers[array_rand($defaultCovers)];
    
    return ROOT_PATH . '/uploads/users/' . $randomCover;
}