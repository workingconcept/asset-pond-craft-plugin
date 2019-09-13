<?php
/**
 * AssetPond plugin for Craft CMS 3.x
 *
 * Instant FilePond server that works with Craft Assets.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

namespace workingconcept\assetpond\variables;

use Craft;

/**
 * @author    Working Concept
 * @package   AssetPond
 * @since     1.0.0
 */
class AssetPondVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the URL for the controller that can receive frontend uploads
     * directly from FilePond.
     *
     * @param null $volumeId
     * @return string
     */
    public function endpoint($volumeId = null): string
    {
        return '/assetpond' . ($volumeId ? ('/' . $volumeId) : '');
    }
}
