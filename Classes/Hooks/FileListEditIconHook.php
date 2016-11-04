<?php
namespace Bash\ExifOrientationHelper\Hooks
{
    use Bash\ExifOrientationHelper\Service\ExifOrientationService;
    use TYPO3\CMS\Backend\Utility\BackendUtility;
    use TYPO3\CMS\Core\Imaging\Icon;
    use TYPO3\CMS\Core\Imaging\IconFactory;
    use TYPO3\CMS\Core\Resource\File;
    use TYPO3\CMS\Core\Utility\GeneralUtility;
    use TYPO3\CMS\Filelist\FileListEditIconHookInterface;
    use TYPO3\CMS\Lang\LanguageService;

    class FileListEditIconHook implements FileListEditIconHookInterface
    {
        /**
         * Modifies edit icon array
         *
         * @param array $cells Array of edit icons
         * @param \TYPO3\CMS\Filelist\FileList $parentObject Parent object
         * @return void
         */
        public function manipulateEditIcons(&$cells, &$parentObject)
        {
            $fileOrFolderObject = $cells['__fileOrFolderObject'];

            if (!($fileOrFolderObject instanceof File)) {
                return;
            }

            $button = $this->getButton($fileOrFolderObject);

            if ($button === null) {
                $button = $parentObject->spaceIcon;
            }

            $cells = array_merge(['exif_orientation_helper' => $button], $cells);
        }

        protected function getButton(File $fileOrFolderObject)
        {
            if ($fileOrFolderObject->getMimeType() !== ExifOrientationService::JPEG_MIME_TYPE) {
                return null;
            }

            /** @var LanguageService $languageService */
            $languageService = $GLOBALS['LANG'];

            /** @var IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $icon = $iconFactory->getIcon('apply-exif-orientation', Icon::SIZE_SMALL);

            $url = BackendUtility::getModuleUrl('exif_orientation_helper', [
                'file_uid' => $fileOrFolderObject->getUid(),
                'return_url' => $this->getReturnUrl()
            ]);

            $title = $languageService->sL('LLL:EXT:exif_orientation_helper/Resources/Private/Language/locallang.xlf:button.tooltip');

            return '<a href="' . htmlentities($url) . '" title="' . htmlentities($title) . '" class="btn btn-default">' . $icon->render() . '</a>';
        }

        protected function getReturnUrl()
        {
            $arguments = GeneralUtility::_GET();

            $returnUrl = [
                'id' => $arguments['id'],
                'returnUrl' => $arguments['returnUrl'],
            ];

            return BackendUtility::getModuleUrl($arguments['M'], $returnUrl);
        }
    }
}

