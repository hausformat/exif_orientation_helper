<?php
return [
    'exif_orientation_button' => [
        'path' => '/wizard/exif_orientation',
        'target' => \Bash\ExifOrientationHelper\Controller\ExifOrientationController::class . '::mainAction'
    ]
];
