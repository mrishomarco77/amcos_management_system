<?php
$images = [
    'sprayer' => 'https://raw.githubusercontent.com/your-repo/images/main/sprayer.jpg',
    'pesticide' => 'https://raw.githubusercontent.com/your-repo/images/main/pesticide.jpg',
    'cotton-seeds' => 'https://raw.githubusercontent.com/your-repo/images/main/cotton-seeds.jpg',
    'fertilizer' => 'https://raw.githubusercontent.com/your-repo/images/main/fertilizer.jpg',
    'hoe' => 'https://raw.githubusercontent.com/your-repo/images/main/hoe.jpg'
];

// Create uploads/inputs directory if it doesn't exist
if (!file_exists('uploads/inputs')) {
    mkdir('uploads/inputs', 0777, true);
}

// Download default images if they don't exist
foreach ($images as $name => $url) {
    $target_file = "uploads/inputs/{$name}.jpg";
    if (!file_exists($target_file)) {
        // For now, we'll copy a placeholder image
        copy('assets/img/default-profile.jpg', $target_file);
    }
}

echo "Images have been set up successfully!\n"; 