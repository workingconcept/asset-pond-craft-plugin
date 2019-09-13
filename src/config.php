<?php
/**
 * AssetPond plugin for Craft CMS 3.x
 *
 * Instant FilePond server that works with Craft Assets.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

/**
 * FilePond Server config.php
 *
 * This file exists only as a template for the FilePond Server settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'asset-pond.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [

    // fallback destination Volume
    'defaultVolumeId' => null,

    // POST field to be checked for base64-encoded FilePond files
    'formUploadField' => null,

];
