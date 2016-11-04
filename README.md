# exif_orientation_helper

This TYPO3 extension adds a button to files in the backend that will apply the orientation found in the EXIF metadata of a JPEG image.

## Screenshots

### Inline

![Imgur](http://i.imgur.com/HNZdAfV.png)

### File List

![Imgur](http://i.imgur.com/GMmixpV.png)

## API

The class `ExifOrientationService` exposes a single method `applyOrientation` for applying the EXIF orientation to a `\TYPO3\CMS\Core\Resource\File`.
Note that the file on disk will be replaced.

**Usage**
```php
/** @var \TYPO3\CMS\Core\Resource\File $file **/

$service = GeneralUtility::makeInstance(ExifOrientationService::class);

$service->applyOrientation($file);
```
