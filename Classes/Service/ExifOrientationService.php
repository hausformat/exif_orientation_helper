<?php
namespace Bash\ExifOrientationHelper\Service
{
    use Bash\ExifOrientationHelper\Imaging\RawJpeg;
    use TYPO3\CMS\Core\Resource\File;
    use TYPO3\CMS\Core\Resource\ResourceFactory;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    class ExifOrientationService
    {
        const JPEG_MIME_TYPE = 'image/jpeg';

        /**
         * @var ResourceFactory
         */
        private $resourceFactory;

        public function __construct(ResourceFactory $resourceFactory)
        {
            $this->resourceFactory = $resourceFactory;
        }

        public function applyRotation(File $file)
        {
            if ($file->getMimeType() !== self::JPEG_MIME_TYPE) {
                return;
            }

            $storage = $file->getStorage();
            $path = $file->getForLocalProcessing();

            $image = GeneralUtility::makeInstance(RawJpeg::class, $path);

            if (!$image->hasOrientation()) {
                return;
            }

            $this->rotateImage($image);
            $this->flipImage($image);

            $output = $this->writeImage($image);

            $storage->replaceFile($file, $output);
        }

        private function writeImage(RawJpeg $image): string
        {
            $path = $this->getOutputFile();

            $image->write($path);

            return $path;
        }

        private function rotateImage(RawJpeg $image)
        {
            if ($image->getOrientation() > 4) {
                $image->rotate(90);
            }
        }

        private function flipImage(RawJpeg $image)
        {
            $orientation = $image->getOrientation();

            if ($orientation === 3 || $orientation === 6) {
                $image->flip(IMG_FLIP_BOTH);
            }

            if ($orientation === 2 || $orientation === 5) {
                $image->flip(IMG_FLIP_VERTICAL);
            }

            if ($orientation === 4 || $orientation === 7) {
                $image->flip(IMG_FLIP_HORIZONTAL);
            }
        }

        private function getOutputFile(): string
        {
            return GeneralUtility::tempnam('exif-orientation-helper-', '.jpg');
        }
    }
}
