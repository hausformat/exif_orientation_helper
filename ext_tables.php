<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms_inline.php']['tceformsInlineHook']['exif_orientation_helper'] = \Bash\ExifOrientationHelper\Hooks\InlineRecord::class;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['fileList']['editIconsHook']['exif_orientation_helper'] = \Bash\ExifOrientationHelper\Hooks\FileListEditIconHook::class;
