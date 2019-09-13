<?php
/**
 * AssetPond plugin for Craft CMS 3.x
 *
 * Instant FilePond server that works with Craft Assets.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

namespace workingconcept\assetpond\controllers;

use workingconcept\assetpond\AssetPond;

use Craft;
use craft\elements\Asset;
use craft\web\Controller;
use workingconcept\assetpond\helpers\PostHelper;
use yii\web\HttpException;

/**
 * @author    Working Concept
 * @package   AssetPond
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = ['index'];


    // Protected Properties
    // =========================================================================

    public $enableCsrfValidation = false;


    // Public Methods
    // =========================================================================

    /**
     * @param $volumeId int  optional upload target volume
     *
     * @return mixed
     * @throws
     */
    public function actionIndex($volumeId = null)
    {
        return $this->_handleApiRequest($volumeId);
    }


    // Private Methods
    // =========================================================================

    /**
     * @param int $volumeId
     *
     * @return \yii\web\Response
     * @throws
     */
    private function _handleApiRequest($volumeId = null)
    {
        $inputs = ['filepond']; // TODO: allow changing input name

        // TODO: allow multiple inputs/fields

        $request = Craft::$app->getRequest();
        $requestMethod = $request->getMethod();
        $server = AssetPond::$plugin->server;

        // turn a single item into an array for uniformity
        if (is_string($inputs))
        {
            $inputs = [ $inputs ];
        }

        // loop over all set entry fields to find posted values
        foreach ($inputs as $input)
        {
            if ($requestMethod === 'POST')
            {
                if ( ! $this->_hasPostContents($input))
                {
                    // bail on this item if we don't have anything to work with
                    continue;
                }

                if ( ! $files = $this->_getFiles($input))
                {
                    // bail on this item if we don't have any files
                    continue;
                }

                // create Assets
                $result = $server->handleFileTransfer(
                    $files,
                    $this->_getMeta($input),
                    $volumeId
                );

                if ($result)
                {
                    // return plain text Asset ID
                    return $this->asRaw($result);
                }
            }

            if ($requestMethod === 'DELETE')
            {
                // delete Asset
                return $this->asRaw(
                    $server->handleRevertFileTransfer(
                        $request->getRawBody()
                    )
                );
            }

            if ($requestMethod === 'GET' || $requestMethod === 'HEAD')
            {
//                if (isset($_GET['fetch']))
//                {
//                    return $server->handle_fetch_remote_file();
//                }

                if (
                    isset($_GET['restore']) && 
                    $result = $server->handleRestoreFileTransfer($_GET['restore'])
                )
                {
                    return $this->_echoAsset($result);
                }

                if (
                    isset($_GET['load']) &&
                    $result = $server->handleLoadLocalFile($_GET['load'])
                )
                {
                    return $this->_echoAsset($result);
                }
            }
        }
    }

    // TODO: all of it
    private function _handleFormPost()
    {
        // see submit.php
        // 'FILE_OBJECTS' => 'handle_file_post',
        // 'BASE64_ENCODED_FILE_OBJECTS' => 'handle_base64_encoded_file_post',
        // 'TRANSFER_IDS' => 'handle_transfer_ids_post'
    }

    /**
     * Returns true if either binary or modeled file data exists.
     *
     * @param $input
     * @return bool
     */
    private function _hasPostContents($input)
    {
        return isset($_FILES[$input]) || isset($_POST[$input]);
    }

    /**
     * Returns an array of file objects. (See PostHelper.)
     *
     * @param $input
     * @return array
     */
    private function _getFiles($input)
    {
        return isset($_FILES[$input]) ?
            PostHelper::toArrayOfFiles($_FILES[$input]) :
            [];
    }

    /**
     * @param $input
     * @return array
     */
    private function _getMeta($input)
    {
        return isset($_POST[$input]) ?
            PostHelper::toArray($_POST[$input]) :
            [];
    }

    /**
     * Returns an HTTP response with the raw file contents.
     *
     * @param Asset $asset
     * @return \craft\web\Response|\yii\console\Response
     * @throws HttpException
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    private function _echoAsset($asset)
    {
        return Craft::$app->getResponse()->sendContentAsFile(
            $asset->getContents(),
            $asset->filename, 
            [
                'inline' => true,
                'mimeType' => $asset->mimeType,
                'contentLength' => $asset->size
            ]
        );
    }

}
