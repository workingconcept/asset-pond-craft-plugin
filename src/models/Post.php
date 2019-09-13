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

use workingconcept\assetpond\helpers\PostHelper;

/**
 * @author    Working Concept
 * @package   AssetPond
 * @since     1.0.0
 */
class Post
{
    // Private Properties
    // =========================================================================

    private $_format;
    private $_values;

    // Public Methods
    // =========================================================================

    public function __construct($fieldName)
    {
        if (isset($_FILES[$fieldName]))
        {
            $this->_values = PostHelper::toArrayOfFiles($_FILES[$fieldName]);
            $this->_format = 'FILE_OBJECTS';
        }

        if (isset($_POST[$fieldName]))
        {
            $this->_values = PostHelper::toArray($_POST[$fieldName]);

            if (PostHelper::isEncodedFile($this->_values[0]))
            {
                $this->_format = 'BASE64_ENCODED_FILE_OBJECTS';
            }
            else
            {
                $this->_format = 'TRANSFER_IDS';
            }
        }
    }

    public function getFormat()
    {
        return $this->_format;
    }

    public function getValues()
    {
        return $this->_values;
    }

}
