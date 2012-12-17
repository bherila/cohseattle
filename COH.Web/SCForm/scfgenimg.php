<?php
    session_start();

    header("Content-type: image/png");
    $image = imagecreate(60,20);
    // Fill in the background
    imagecolorallocate ($image, 219, 236, 255);
    // Foreground colour
    $blue = imagecolorallocate($image, 0, 90, 190);
    imagestring($image, 5, 8, 2, $_SESSION['majik_string'], $blue);
    imagepng($image);
    imagedestroy($image);
?>
