<?php
/**
 * FilePond Server plugin for Craft CMS 3.x
 *
 * Instant FilePond server that works with Craft Assets.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

namespace workingconcept\assetpond;

use craft\services\Users;
use workingconcept\assetpond\helpers\PostHelper;
use workingconcept\assetpond\services\Server;
use workingconcept\assetpond\variables\AssetPondVariable;
use workingconcept\assetpond\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\events\ElementEvent;
use craft\services\Assets;
use craft\events\AssetEvent;
use craft\events\UserEvent;

use yii\base\Event;

/**
 * Class AssetPond
 *
 * @author    Working Concept
 * @package   AssetPond
 * @since     1.0.0
 *
 * @property  Server $filePondServerService
 */
class AssetPond extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var AssetPond
     */
    public static $plugin;


    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            static function (RegisterUrlRulesEvent $event)
            {
                $event->rules['assetpond'] = 'asset-pond/default';
                $event->rules['assetpond/<volumeId:\d+>'] = 'asset-pond/default';
            }
        );

        $request = Craft::$app->getRequest();

        if ($request->getIsPost())
        {
            $this->_handlePost();
        }

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            static function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('assetpond', AssetPondVariable::class);
            }
        );

        Craft::info(
            Craft::t(
                'asset-pond',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }


    // Private Methods
    // =========================================================================

    /**
     * Writes base64-encoded FilePond uploads to files and populates $_FILES.
     *
     * Currently not working because Craft uses `is_uploaded_file()` to make
     * sure $_POST contents weren't manipulated—which is exactly what we're
     * trying to do here. Need another way to make this happen.
     */
    private function _handlePost()
    {
        $request = Craft::$app->getRequest();
        $filepondField = $this->getSettings()->formUploadField;

        if (empty($_POST[$filepondField]))
        {
            return;
        }

        if ($filepondFiles = $_POST[$filepondField])
        {
            foreach ($filepondFiles as $targetField => $file)
            {
                if ($tempFile = Server::base64FilePostToUploadedFile($file))
                {
                    $_FILES[$targetField]['name'] = $tempFile->name;
                    $_FILES[$targetField]['tmp_name'] = $tempFile->tempName;
                    $_FILES[$targetField]['size'] = $tempFile->size;
                    $_FILES[$targetField]['type'] = $tempFile->type;
                    $_FILES[$targetField]['error'] = $tempFile->error;
                }
            }
        }

        // we were never here
        unset($_POST[$filepondField]);
    }


    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'asset-pond/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
