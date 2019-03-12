<?php
namespace Bash\ExifOrientationHelper\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Bash\ExifOrientationHelper\Imaging\RawJpeg;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * @author Ruben Schmidmeister <ruben.schmidmeister@icloud.com
 */
class ExifOrientationService
{
    const JPEG_MIME_TYPE = 'image/jpeg';
    const IMG_FLIP_NONE = 0;

    private static $operations = [
        1 => [0, self::IMG_FLIP_NONE],
        2 => [0, IMG_FLIP_HORIZONTAL],
        3 => [180, self::IMG_FLIP_NONE],
        4 => [0, IMG_FLIP_VERTICAL],
        5 => [90, IMG_FLIP_VERTICAL],
        6 => [270, self::IMG_FLIP_NONE],
        7 => [90, IMG_FLIP_HORIZONTAL],
        8 => [90, self::IMG_FLIP_NONE],
    ];

    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * @param \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory
     */
    public function __construct(ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?: ResourceFactory::getInstance();
    }

    /**
     * Applies the orientation found in the EXIF data for $file and removes exif data.
     * Replaces $file with the processed output.
     *
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @api
     */
    public function applyOrientation(FileInterface $file)
    {
        if ($file->getMimeType() !== self::JPEG_MIME_TYPE) {
            return;
        }

        $storage = $file->getStorage();
        $path = $file->getForLocalProcessing(false);

        /** @var RawJpeg $image */
        $image = GeneralUtility::makeInstance(RawJpeg::class, $path);

        if (!$image->hasOrientation()) {
            return;
        }

        $orientation = $image->getOrientation();

        if (!isset(self::$operations[$orientation])) {
            return;
        }

        list($rotate, $flip) = self::$operations[$orientation];

        $image->rotate($rotate);

        if ($flip !== self::IMG_FLIP_NONE) {
            $image->flip($flip);
        }

        GeneralUtility::makeInstance(Dispatcher::class)
            ->dispatch(__CLASS__, 'process', [$file, $image]);

        $output = $this->writeImage($image);

        $storage->replaceFile($file, $output);
    }

    /**
     * Returns if the file has an EXIF Orientation that can be "applied".
     *
     * @api
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @return bool
     */
    public function canApplyOrientation(FileInterface $file)
    {
        if ($file->getMimeType() !== self::JPEG_MIME_TYPE) {
            return false;
        }

        $image = GeneralUtility::makeInstance(RawJpeg::class, $file->getForLocalProcessing(false));

        return $image->hasOrientation();
    }

    /**
     * @param \Bash\ExifOrientationHelper\Imaging\RawJpeg $image
     *
     * @return string
     */
    private function writeImage(RawJpeg $image)
    {
        $path = $this->getOutputFile();

        $image->write($path);

        return $path;
    }

    /**
     * @return string
     */
    private function getOutputFile()
    {
        return GeneralUtility::tempnam('exif-orientation-helper-', '.jpg');
    }
}
