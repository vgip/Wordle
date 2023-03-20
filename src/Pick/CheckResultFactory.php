<?php

declare(strict_types=1);

namespace Vgip\Wordle\Pick;

use Vgip\Wordle\Pick\CheckResult;

/**
 * Description of LetterConfigFactory
 */
final class CheckResultFactory 
{
    public static function factoryResultCause(string $cause, string $checkName, bool $result): CheckResult
    {
        $checkResult = new CheckResult();
        
        $checkResult->setCheckName($checkName);
        $checkResult->setCause($cause);
        $checkResult->setResult($result);
        
        return $checkResult;
    }
}
