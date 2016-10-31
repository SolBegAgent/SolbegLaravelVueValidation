<?php

namespace Solbeg\VueValidation\Converter;

/**
 * Class NumericRule
 *
 * @author Alexey Sejnov <alexey.sejnov@solbeg.com>
 */
class NumericRule extends AbstractNumberRule
{
    /**
     * @inheritdoc
     */
    protected function getNumberRegexp()
    {
        // The main problem why this regex very long it that we cannot use '|' char!
        return implode('', [ // concatenate parts

            // The start of string, a substring after which should match the following format: [-+]?[\.\d]+([eE][-+]?\d+)?
            // It imposes additional condition so at least one digit will be before exponential part.
            '^(?=([-+]?[\.\d]+([eE][-+]?\d+)?)?$)',

                // Number sign part (optional). It requires that only digit or dot may follow after sign.
                '([\-\+](?=[\d\.]))?',

                    // The following two parts are setting all possible variations with dot.
                    // It need to DISallows single dot (e.g `.`), but allow correct formats:

                    // - it allows `digit.digit` format (e.g. `5.3`)
                    // - it allows `digit.` format (e.g. `5.`)
                    '(\d+\.?(?=\d*([eE][-+]?\d+)?$))?',

                    // - it allows `.digit` format (e.g. `.5`)
                    '(\.(?=\d+([eE][-+]?\d+)?$))?',

                // Any number of digits may be after dot
                '\d*',

                // Exponential part (optional)
                '([eE][-+]?\d+)?',

            // The end of string
            '$',
        ]);
    }
}
