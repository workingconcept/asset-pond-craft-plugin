<?php
/**
 * AssetPond plugin for Craft CMS 3.x
 *
 * Instant FilePond server that works with Craft Assets.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

namespace workingconcept\assetpond\services;

use craft\models\VolumeFolder;
use craft\web\UploadedFile;
use workingconcept\assetpond\AssetPond;

use Craft;
use craft\elements\Asset;
use craft\helpers\Assets;
use craft\base\Component;
use craft\errors\AssetException;

/**
 * @author    Working Concept
 * @package   AssetPond
 * @since     1.0.0
 */
class Server extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Saves uploaded files as Assets.
     *
     * @param array $files     file contents
     * @param array $meta      file metadata
     * @param int   $volumeId  folder ID for upload
     *
     * @return bool|int Asset ID or false
     */
    public function handleFileTransfer($files, $meta, $volumeId = null)
    {
        if (count($files) === 0)
        {
            return false;
        }

        try
        {
            $assets = Craft::$app->getAssets();

            if ($volumeId === null)
            {
                $volumeId = AssetPond::$plugin->getSettings()->defaultVolumeId;
            }

            $folder = $assets->getRootFolderByVolumeId($volumeId);

            // TODO: handle metadata, which can direct tranforms, etc.

            // only save the first file for now, since others would be variants
            $file = $files[0];

            $asset = $this->_getNewAssetFromFile(
                $file['tmp_name'],
                $file['name'],
                $folder
            );

            $result = Craft::$app->getElements()->saveElement($asset);

            if ( ! $result)
            {
                // TODO: handle errors
                $errors = $asset->getFirstErrors();
                return false;
            }

            return $asset->id;
        }
        catch (\Throwable $e)
        {
            Craft::error('An error occurred when saving an Asset: ' . $e->getMessage(), __METHOD__);
            Craft::$app->getErrorHandler()->logException($e);
            return false;
        }
    }

    /**
     * Deletes an Asset.
     *
     * @param int $assetId
     * @return bool
     * @throws
     */
    public function handleRevertFileTransfer($assetId): bool
    {
        $asset = Craft::$app->getAssets()->getAssetById($assetId);

        if (! $asset)
        {
            // doesn't exist
            return false;
        }

        //$this->_requirePermissionByAsset('deleteFilesAndFoldersInVolume', $asset);

        try {
            Craft::$app->getElements()->deleteElement($asset);
        } catch (AssetException $exception) {
            //return $this->asErrorJson($exception->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param int $assetId
     * @return Asset|false
     */
    public function handleRestoreFileTransfer($assetId)
    {
        $asset = Craft::$app->getAssets()->getAssetById($assetId);

        if (! $asset)
        {
            // doesn't exist
            return false;
        }

        return $asset;
    }

    /**
     * @param int $assetId
     * @return Asset|false
     */
    public function handleLoadLocalFile($assetId)
    {
        $asset = Craft::$app->getAssets()->getAssetById($assetId);

        if (! $asset)
        {
            // doesn't exist
            return false;
        }

        return $asset;
    }

    /**
     * Decodes base64-encoded files and turns them into Assets.
     * https://github.com/pqina/filepond-plugin-file-encode
     *
     * @param array $files
     * @param int   $volumeId
     *
     * Each item in the array should be in the following format:
     *  {
     *      "id": "iuhv2cpsu",
     *      "name": "picture.jpg",
     *      "type": "image/jpeg",
     *      "size": 20636,
     *      "metadata" : {...}
     *      "data": "/9j/4AAQSkZJRgABAQEASABIAA..."
     *  }
     *
     * @throws
     * @return array
     */
    public function handleBase64EncodedFilePost($files, $volumeId = null): array
    {
        if ($volumeId === null)
        {
            $volumeId = AssetPond::$plugin->getSettings()->defaultVolumeId;
        }

        $folder = Craft::$app->getAssets()->getRootFolderByVolumeId($volumeId);
        $result = [];

        foreach ($files as $file)
        {
            if ($asset = $this->base64FilePostToAsset($file, $folder))
            {
                $result[] = $asset;
            }
        }

        return $result;
    }

    /**
     * @param object       $file    uploaded, decoded file object
     * @param VolumeFolder $folder  destination folder for Asset
     * @return Asset|false
     */
    public function base64FilePostToAsset($file, $folder)
    {
        if ( ! $tempFile = self::base64FilePostToUploadedFile($file))
        {
            return false;
        }

        try
        {
            $filename = Assets::prepareAssetName($tempFile->name);

            $asset = $this->_getNewAssetFromFile(
                $tempFile->tempName,
                $filename,
                $folder
            );

            if (Craft::$app->getElements()->saveElement($asset))
            {
                return $asset;
            }
        }
        catch (\Throwable $e)
        {
            Craft::error('An error occurred when saving an Asset: ' . $e->getMessage(), __METHOD__);
            Craft::$app->getErrorHandler()->logException($e);

            return false;
        }

        return false;
    }

    /**
     * Saves base64-encoded file data as a local file and returns UploadedFile.
     *
     * @param string $data
     *
     * @return UploadedFile|false
     */
    public static function base64FilePostToUploadedFile($data)
    {
        // suppress error messages assuming file objects are valid
        $file = @json_decode($data, false);

        // skip files that failed to decode or don't have data
        if ( ! is_object($file) || empty($file->data))
        {
            return false;
        }

        $tempFilename = uniqid(pathinfo($file->name, PATHINFO_FILENAME), true) . '.' . pathinfo($file->name, PATHINFO_EXTENSION);
        $tempFilePath = Craft::$app->getPath()->tempPath . DIRECTORY_SEPARATOR . $tempFilename;

        // write the decoded data
        file_put_contents($tempFilePath, \base64_decode($file->data));

        $tempFile = new UploadedFile([
            'name'     => $file->name,
            'type'     => $file->type,
            'size'     => $file->size,
            'tempName' => $tempFilePath,
        ]);

        return $tempFile;
    }

    /*
    function handleTransferIdsPost($ids)
    {
        foreach ($ids as $id) {

            // create transfer wrapper around upload
            $transfer = FilePond\get_transfer(TRANSFER_DIR, $id);

            // transfer not found
            if (!$transfer) continue;

            // move files
            $files = $transfer->getFiles(defined('TRANSFER_PROCESSOR') ? TRANSFER_PROCESSOR : null);
            foreach($files as $file) {
                FilePond\move_file($file, UPLOAD_DIR);
            }
            // remove transfer directory
            FilePond\remove_transfer_directory(TRANSFER_DIR, $id);
        }
    }
    */


    // Private Methods
    // =========================================================================

    /**
     * Returns a new Asset Element prepped with the provided temporary
     * file path, target filename, and target folder.
     *
     * @param $filepath
     * @param $filename
     * @param $folder
     *
     * @return Asset
     */
    private function _getNewAssetFromFile($filepath, $filename, $folder): Asset
    {
        $asset = new Asset();

        $asset->tempFilePath = $filepath;
        $asset->filename = $filename;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $folder->volumeId;
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        return $asset;
    }

}
