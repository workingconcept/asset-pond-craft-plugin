<?php
/**
 * AssetPond plugin for Craft CMS 3.x
 *
 * Instant FilePond server that works with Craft Assets.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

namespace workingconcept\assetpond\models;

use craft\base\Model;

/**
 * @author    Working Concept
 * @package   AssetPond
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var int
     */
    public $defaultVolumeId;

    /**
     * @var string POST field to check for base64-encoded FilePond files.
     */
    public $formUploadField = 'assetpond';


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['formUploadField', 'string'],
            ['defaultVolumeId', 'number', 'integerOnly' => true],
            ['defaultVolumeId', 'required'],
        ];
    }
}
