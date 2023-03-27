<?php

declare(strict_types=1);

use Vgip\Wordle\Pick\Pick;
use Vgip\Wordle\Pick\LetterConfigFactory;
use Vgip\Wordle\Score\DefaultScore;
use Vgip\Wordle\Score\WordUsed;
use Vgip\Wordle\Score\LetterUsed;

try {
    $pathWords = join(DIRECTORY_SEPARATOR, [__DIR__, 'words_en_5_letter.txt']);
    $wordList = file($pathWords);
    
    /** Get previously used words to lower the ranking of such words */
    $wordUsedList = [];
    $pathWordUsed = join(DIRECTORY_SEPARATOR, [__DIR__, 'used_words.csv']);
    $file = fopen($pathWordUsed, 'r');
    $c = 0;
    while (($line = fgetcsv($file)) !== false) {
        $c++;
        if (1 === $c) {
            continue;
        }
        $wordUsedList[$line[0]] = $line[2];
    }
    fclose($file);
    
    $letterList = [];
    $letterList['z'] = false;

    $pick = new Pick();
    $pick->setSkipWordDoubleLetter(true);
    $pick->setResultLogOn(true);
    $candidateList = $pick->getCandidate($wordList, $letterList);
    $letterListNumber = $pick->getLetterListNumber();
    $resultLog = $pick->getResultLog();
    
    arsort($letterListNumber);
    $maxOccurrence = max($letterListNumber);
    print_r($maxOccurrence);
    
    $defaultScore = new DefaultScore();
    $candidateScoreList1 = $defaultScore->getScore($candidateList);
    
    $wordUsed = new WordUsed($wordUsedList);
    $candidateScoreList2 = $wordUsed->getScore($candidateScoreList1);
    
    $letterUsed = new LetterUsed($letterListNumber);
    $candidateScoreList3 = $letterUsed->getScore($candidateScoreList2);
    
    arsort($candidateScoreList3);
    print_r($candidateScoreList3);
    
//    foreach ($resultLog AS $word => $checkResultList) {
//        foreach ($checkResultList AS $checkResult) {
//            $result = (true === $checkResult->getResult()) ? 'true' : 'false';
//            echo $word . ' - result: ' . $result . ' check name: ' . $checkResult->getCheckName() . '; ' . $checkResult->getCause() . "\n";
//        }
//    }
} catch (Throwable $e) {
    print_r($e);
}
