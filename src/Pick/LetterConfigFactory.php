<?php

declare(strict_types=1);

namespace Vgip\Wordle\Pick;

use Vgip\Wordle\Pick\LetterConfig;

/**
 * Description of LetterConfigFactory
 */
final class LetterConfigFactory 
{
    public static function factory(string $type, array $placeNumber): LetterConfig
    {
        return new LetterConfig($type, $placeNumber);
    }
}
