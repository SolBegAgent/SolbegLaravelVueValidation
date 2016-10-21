<?php

namespace Solbeg\VueValidation\Wrappers;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser as BaseGuesser;

/**
 * Class MimeTypeGuesserWrapper
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class MimeTypeGuesserWrapper extends BaseGuesser
{
    /**
     * @param string|array $extensions
     * @return string[]
     */
    public static function guessMimeTypeByExtensions($extensions)
    {
        return (new static)->guessPossibleMimeTypes($extensions);
    }

    /**
     * @param string|string[] $extensions
     * @return string[]
     */
    public function guessPossibleMimeTypes($extensions)
    {
        if (!$extensions) {
            return [];
        }

        $result = [];
        $extensionsAsKeys = array_fill_keys((array) $extensions, true);
        foreach ($this->defaultExtensions as $mimeType => $extension) {
            if (isset($extensionsAsKeys[$extension])) {
                $result[] = $mimeType;
            }
        }
        return $result;
    }
}
