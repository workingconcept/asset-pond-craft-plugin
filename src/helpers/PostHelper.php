<?php
/**
 * AssetPond plugin for Craft CMS 3.x
 *
 * Instant FilePond server that works with Craft Assets.
 *
 * @link      https://workingconcept.com
 * @copyright Copyright (c) 2019 Working Concept
 */

namespace workingconcept\assetpond\helpers;

/**
 * @author    Working Concept
 * @package   AssetPond
 * @since     1.0.0
 */
class PostHelper
{
    // Public Methods
    // =========================================================================

    public static function isEncodedFile($value): bool
    {
        $data = @json_decode($value, false);
        return is_object($data);
    }

    public static function isEncodedFileWithData($value): bool
    {
        $data = @json_decode($value, false);
        return is_object($data) && ! empty($data->data);
    }

    /**
     * Returns decoded file data from base64-encoded FilePond file object.
     *
     * @param $file
     * @return bool|object
     */
    public static function toDecodedFileData($file)
    {
        // suppress error messages assuming file objects are valid
        $file = @json_decode($file, false);

        // skip files that failed to decode or don't have data
        if ( ! is_object($file) || empty($file->data))
        {
            return false;
        }

        return $file;
    }

    public static function toArrayOfFiles($value): array
    {
        if (is_array($value['tmp_name']))
        {
            $results = [];

            foreach($value['tmp_name'] as $index => $tmpName)
            {
                $file = [
                    'tmp_name' => $value['tmp_name'][$index],
                    'name' => $value['name'][$index],
                    'size' => $value['size'][$index],
                    'error' => $value['error'][$index],
                    'type' => $value['type'][$index]
                ];

                $results[] = $file;
            }

            return $results;
        }

        return self::toArray($value);
    }

    public static function isAssociativeArray($arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function toArray($value): array
    {
        if (is_array($value) && ! self::isAssociativeArray($value))
        {
            return $value;
        }

        return isset($value) ? [$value] : [];
    }

}
