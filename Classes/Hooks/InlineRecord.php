<?php
namespace Bash\ExifOrientationHelper\Hooks
{
    use Bash\ExifOrientationHelper\Service\ExifOrientationService;
    use TYPO3\CMS\Backend\Form\Element\InlineElementHookInterface;
    use TYPO3\CMS\Backend\Utility\BackendUtility;
    use TYPO3\CMS\Core\Imaging\Icon;
    use TYPO3\CMS\Core\Imaging\IconFactory;
    use TYPO3\CMS\Core\Resource\ResourceFactory;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    class InlineRecord implements InlineElementHookInterface
    {
        /**
         * Pre-processing to define which control items are enabled or disabled.
         *
         * @param string $parentUid The uid of the parent (embedding) record (uid or NEW...)
         * @param string $foreignTable The table (foreign_table) we create control-icons for
         * @param array $childRecord The current record of that foreign_table
         * @param array $childConfig TCA configuration of the current field of the child record
         * @param bool $isVirtual Defines whether the current records is only virtually shown and not physically part of the parent record
         * @param array &$enabledControls (reference) Associative array with the enabled control items
         * @return void
         */
        public function renderForeignRecordHeaderControl_preProcess($parentUid, $foreignTable, array $childRecord, array $childConfig, $isVirtual, array &$enabledControls)
        {
        }

        /**
         * Post-processing to define which control items to show. Possibly own icons can be added here.
         *
         * @param string $parentUid The uid of the parent (embedding) record (uid or NEW...)
         * @param string $foreignTable The table (foreign_table) we create control-icons for
         * @param array $childRecord The current record of that foreign_table
         * @param array $childConfig TCA configuration of the current field of the child record
         * @param bool $isVirtual Defines whether the current records is only virtually shown and not physically part of the parent record
         * @param array &$controlItems (reference) Associative array with the currently available control items
         * @return void
         */
        public function renderForeignRecordHeaderControl_postProcess($parentUid, $foreignTable, array $childRecord, array $childConfig, $isVirtual, array &$controlItems)
        {
            if ($foreignTable !== 'sys_file_reference') {
                return;
            }

            $uid = $childRecord['uid'];

            if (substr($uid, 0, 3) === 'NEW') {
                return;
            }

            $resourceFactory = ResourceFactory::getInstance();
            $fileReference = $resourceFactory->getFileReferenceObject($uid);

            if ($fileReference->getMimeType() !== ExifOrientationService::JPEG_MIME_TYPE) {
                return;
            }

            /** @var IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            $icon = $iconFactory->getIcon(
                'magic-fix-button',
                Icon::SIZE_SMALL,
                null
            );

            $url = BackendUtility::getModuleUrl('exif_orientation_button', [
                'file_reference_uid' => $childRecord['uid'],
                'return_url' => $this->getReturnUrl()
            ]);

            $controlItems['exif_orientation_button'] = '<a href="' . htmlentities($url) . '" class="btn btn-default">' . $icon->render() . '</a>';
        }

        protected function getReturnUrl(): string
        {
            $arguments = GeneralUtility::_GET();

            $returnUrl = [
                'edit' => $arguments['edit'],
                'returnUrl' => $arguments['returnUrl'],
            ];

            return BackendUtility::getModuleUrl('record_edit', $returnUrl);
        }
    }
}
