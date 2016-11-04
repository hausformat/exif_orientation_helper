<?php

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

$iconRegistry->registerIcon(
    'apply-exif-orientation',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:exif_orientation_helper/Resources/Public/Icons/apply-exif-orientation.svg']
);
