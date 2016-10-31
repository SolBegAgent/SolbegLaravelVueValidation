<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class IntegerRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class IntegerRule extends AbstractNumberRule
{
    /**
     * @inheritdoc
     */
    protected function getNumberRegexp()
    {
        // The main problem why this regex very long it that we cannot use '|' char!
        return implode('', [ // concatenate parts

            // Start of string
            '^' .

                // The sign part (optional)
                '([\-\+](?=\d))?',

                    // Mutually exclusive parts:

                    // It requires single zero (`0`) char and followed end of string.
                    '(0(?=$))?',
                    // Or it may be one non-zero digit and followed any count of digits.
                    '([1-9]\d*$)?',

            // The end of string
            '$',
        ]);
    }
}
