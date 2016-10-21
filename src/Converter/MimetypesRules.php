<?php

namespace Solbeg\VueValidation\Converter;

use Solbeg\VueValidation\Wrappers\MimeTypeGuesserWrapper as Guesser;

/**
 * Class MimetypesRules
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class MimetypesRules extends AbstractRule
{
    /**
     * @var string[]|null
     */
    private $normalizedMimeTypes = null;

    /**
     * @inheritdoc
     */
    public function getVueRules()
    {
        return ['mimes' => $this->getNormalizedMimeTypes()];
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        foreach ($this->getNormalizedMimeTypes() as $mimeType) {
            if (strpos($mimeType, '*') !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isDateRule()
    {
        return false;
    }

    /**
     * @return string[]
     */
    protected function getNormalizedMimeTypes()
    {
        if ($this->normalizedMimeTypes !== null) {
            return $this->normalizedMimeTypes;
        }

        $result = [];
        $needGuess = [];
        foreach ($this->getLaravelParams() as $mimeType) {
            if (strpos($mimeType, '/') === false) {
                $needGuess[] = $mimeType;
            } else {
                $result[$mimeType] = true;
            }
        }

        foreach (Guesser::guessMimeTypeByExtensions($needGuess) as $mimeType) {
            $result[$mimeType] = true;
        }

        return $this->normalizedMimeTypes = array_keys($result);
    }
}
