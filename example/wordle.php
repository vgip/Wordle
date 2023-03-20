<?php

declare(strict_types=1);

use Vgip\Wordle\Pick\Pick;
use Vgip\Wordle\Pick\LetterConfigFactory;

try {
    $pathWords = join(DIRECTORY_SEPARATOR, [__DIR__, 'words_en_5_letter.txt']);
    $wordList = file($pathWords);
    
    $letterList = [];
    $letterList['x'] = false;
    $letterList['a'] = LetterConfigFactory::factory('defined', [2]);
    $letterList['n'] = LetterConfigFactory::factory('undefined', [3, 5]);

    $pick = new Pick();
    $pick->setSkipWordDoubleLetter(true);
    $pick->setResultLogOn(true);
    $candidateList = $pick->getCandidate($wordList, $letterList);
    $resultLog = $pick->getResultLog(); 
    
    print_r($candidateList);
    
    foreach ($resultLog AS $word => $checkResultList) {
        foreach ($checkResultList AS $checkResult) {
            $result = (true === $checkResult->getResult()) ? 'true' : 'false';
            echo $word . ' - result: ' . $result . ' check name: ' . $checkResult->getCheckName() . '; ' . $checkResult->getCause() . "\n";
        }
    }
} catch (Throwable $e) {
    print_r($e);
}
