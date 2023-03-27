<?php

declare(strict_types=1);

namespace Vgip\Wordle\Pick;

use Vgip\Wordle\Pick\LetterConfig;
use Vgip\Wordle\Pick\CheckResult;
use Vgip\Wordle\Pick\CheckResultFactory;
use InvalidArgumentException;

/**
 * Pick of words by condition
 * 
 * Example config getCandidate($wordList, $letterList)
 * $letterList = [];
 * $letterList['к'] = LetterConfigFactory::factory('undefined', [3, 2]);
 * $letterList['а'] = LetterConfigFactory::factory('defined', [5]);
 * $letterList['р'] = LetterConfigFactory::factory('undefined', [4, 5]);
 * $letterList['у'] = LetterConfigFactory::factory('undefined', [3]);
 * $letterList['о'] = false;
 * $letterList['т'] = false;
 */
class Pick 
{

    /**
     * List of all words.
     *  
     * @var array
     */
    private array $wordList;
    
    /**
     * Not exists letters in word
     * 
     * @var array
     */
    private array $letterNotExists = [];
    
    /**
     * Letter configuration in other format.
     * Use for private function.
     * 
     * ['defined' or 'undefined']
     *     [letter name: "a", "d", ...]
     *          [place number(s) letter in a word: [1, 4, ...]]
     * 
     * @var array
     */
    private array $letterPlaceList = [];
    
    /** 
     * List of all unique undefined letters.
     * Letters which must be in the word but their plase numbers are unknown. 
     * Known only place number where them must not be.
     * 
     * @var array - values are unique letter(s)
     */
    private array $letterUndefinedList = [];

    /**
     * Letter configuration in other format.
     * Use for private function.
     * 
     * @var array
     */
    private array $letterPlaceBusyList = [];
    
    /**
     * Log result of checks
     * 
     * @var array
     */
    private array $resultLog = [];
    
    /**
     * Skip words with duplicate letters
     * 
     * @var bool
     */
    private bool $skipWordDoubleLetter = true;
    
    /**
     * Turn on (true) or of (false) word check result log
     *  
     * @var bool
     */
    private bool $resultLogOn = false;
    
    /**
     * Number of letters in all words
     * 
     * @var array
     */
    private array $letterListNumber = [];
    
    /**
     * Set the ability to skip or leave words with duplicate letters
     * 
     * @param bool $skipWordDoubleLetter
     * @return $this
     */
    public function setSkipWordDoubleLetter(bool $skipWordDoubleLetter): void 
    {
        $this->skipWordDoubleLetter = $skipWordDoubleLetter;
    }
    
    /**
     * Turn on (true) or off (false) word check result log
     * 
     * @param bool $logOn
     * @return void
     */
    public function setResultLogOn(bool $logOn): void
    {
        $this->resultLogOn = (true === $logOn) ? true : false;
    }

    /**
     * Get result log
     * 
     * @return array
     */
    public function getResultLog(): array 
    {
        return $this->resultLog;
    }
    
    public function getLetterListNumber(): array 
    {
        return $this->letterListNumber;
    }

    /**
     * Get words matching conditions
     * 
     * @param array $wordList - directory of all words to check
     * @param array $letterList - conditions to check
     * @return array - values are candidates
     */
    public function getCandidate(array $wordList, array $letterList): array 
    {   
        $this->wordList = $wordList;
        
        $letterNotExists = $this->getLetterNotExists($letterList);
        $letterPlaceList = $this->getLetterPlaceList($letterList);
        $letterPlaceBusyList = $this->getLetterPlaceBusyList($letterPlaceList);
        $this->letterNotExists = $letterNotExists;
        $this->letterPlaceList = $letterPlaceList;
        $this->letterUndefinedList = array_keys($letterPlaceList['undefined']);
        $this->letterPlaceBusyList = $letterPlaceBusyList;

        $candidateList = $this->pickCandidate();

        return $candidateList;
    }
    
    /**
     * Get all not exist letters from conditions to pick
     * 
     * @param array $letterList
     * @return array
     */
    private function getLetterNotExists(array $letterList): array
    {
        $letterNotExists = [];
        
        foreach ($letterList AS $letter => $letterConfig) {
            if (false === $letterConfig) {
                $letterNotExists[] = $letter;
            }
        }
        
        return $letterNotExists;
    }
    
    /**
     * Letter configuration convert to the other format for internal use to speed up
     * 
     * @param array $letterList
     * @return array
     *     ['defined']
     *         [letter]
     *             [number of place]
     * @throws type InvalidArgumentException
     */
    private function getLetterPlaceList(array $letterList): array
    {
        $list = [
            'defined' => [], 
            'defined_count' => 0, 
            'undefined' => [],
            'undefined_count' => 0,
        ];
        
        foreach ($letterList AS $letter => $letterConfig) {
                
            /** If letter is not exists (false) or unknown (null) */
            if (null === $letterConfig or false === $letterConfig) {
                continue;
            }

            if (!($letterConfig instanceof LetterConfig)) {
                throw InvalidArgumentException('Letter configuration contain an incorrect value which is not null, false or instance of LetterConfig class');
            }
            
            $type = $letterConfig->getType();
            $placeNumber = $letterConfig->getPlaceNumber();

            if (!in_array($type, ['defined', 'undefined'], true)) {
                throw InvalidArgumentException('Letter configuration contain an incorrect type of place. Available places is only "defined" and "undefined"');
            }
            $list[$type][$letter] = $placeNumber;
        }
        
        $list['defined_count'] = count($list['defined']);
        $list['undefined_count'] = count($list['undefined']);
        
        return $list;
    }
    
    /**
     * Letter configuration convert to the other format
     * 
     * @param array $letterPlaceList
     * @return array
     *      ['defined'][number of letter place: 1, 3, ...][letter: a, g, t or ...] - one place can be contains only one letter
     *      ['undefined'][number of letter place: 1, 3, ...][numerical key][letter: a, g, t or ...] - one place can be contains some letters
     */
    private function getLetterPlaceBusyList(array $letterPlaceList): array
    {
        $list = ['defined' => [], 'undefined' => []];

        /** In a every place number can by only one letter */
        foreach ($letterPlaceList['defined'] AS $letter => $placeNumberList) {
            foreach ($placeNumberList AS $placeNumber) {
                if (array_key_exists($placeNumber, $list['defined'])) {
                    throw InvalidArgumentException('One place number "'.$placeNumber.'" cannot contains more than 1 letter');
                }
                $list['defined'][$placeNumber] = $letter;
            }
        }
        
        /** In a one place can be missing one or more letters */
        foreach ($letterPlaceList['undefined'] AS $letter => $placeNumberList) {
            foreach ($placeNumberList AS $placeNumber) {
                $list['undefined'][$placeNumber][] = $letter;
            }
        }
        
        return $list;
    }

    /**
     * Pick of words suitable for the conditions
     * 
     * @return array
     */
    private function pickCandidate(): array
    {
        $candidateList = [];
        
        foreach ($this->wordList AS $wordRaw) {
            
            $word = trim($wordRaw);
            
            if (true === $this->checkWord($word)) {
                $candidateList[] = $word;
            }
        }
        
        return $candidateList;
    }
    
    /**
     * Checking a word for conditions
     * 
     * @param string $word
     * @return string|null
     */
    private function checkWord(string $word): bool
    {
        $res = false;
        
        $wordList = mb_str_split($word, 1);
        
        $successCounter = 0;
        $letterFound = [];
        $placeNumber = 0;
        $letterUndefinedList = $this->letterPlaceList['undefined'];
        foreach ($wordList as $placeNumberRaw => $letter) {
            
            $this->letterListNumber[$letter] = (array_key_exists($letter, $this->letterListNumber)) ? $this->letterListNumber[$letter] + 1 : 1;
            
            $placeNumber = $placeNumberRaw + 1;
            
            $checkResultIsLetterNotExists = $this->isLetterNotExists($placeNumber, $letter);
            $this->setResultLog($word, $checkResultIsLetterNotExists);
            if (false === $checkResultIsLetterNotExists->getResult()) {
                break;
            }
            
            $checkResultIsLetterInDefinedPlace = $this->isLetterInDefinedPlace($placeNumber, $letter);
            $this->setResultLog($word, $checkResultIsLetterInDefinedPlace);
            if (false === $checkResultIsLetterInDefinedPlace->getResult()) {
                break;
            }
            
            $checkResultIsNotLetterInUndefinedPlace = $this->isNotLetterInUndefinedPlace($placeNumber, $letter);
            $this->setResultLog($word, $checkResultIsNotLetterInUndefinedPlace);
            if (false === $checkResultIsNotLetterInUndefinedPlace->getResult()) {
                break;
            }
            
            $successCounter++;
            $letterFound[$letter] = (array_key_exists($letter, $letterFound)) ? $letterFound[$letter]++ : 1;
            
            if (array_key_exists($letter, $letterUndefinedList)) {
                unset($letterUndefinedList[$letter]);
            }
        }
        
        /** If all checks are success */
        if (($successCounter === $placeNumber)) {
            $checkResultIsExistsAllUndefinedPlaceLetter = $this->isExistsAllUndefinedPlaceLetter($letterUndefinedList);
            $this->setResultLog($word, $checkResultIsExistsAllUndefinedPlaceLetter);
            if ((true === $checkResultIsExistsAllUndefinedPlaceLetter->getResult())) {
                $res = true;
                
                $checkResultIsWordDoubleLetter = $this->isWordDoubleLetter($successCounter, $letterFound);
                $this->setResultLog($word, $checkResultIsWordDoubleLetter);
                if (false === $checkResultIsWordDoubleLetter->getResult()) {
                    $res = false;
                }
            }
        }
        
        return $res;
    }

    /**
     * A letter that must not be in the picking word is missing in the word 
     * being checked
     * 
     * Example
     * Letter "a" set as not exists and is not exists in word "house" - result is true 
     * Letter "a" set as not exists and is exists in word "brave" - result is false 
     * 
     * @param string $letter
     * @return CheckResult
     */
    private function isLetterNotExists(int $placeNumber, string $letter): CheckResult
    {
        $checkName = 'is_letter_not_exists';
        
        if (in_array($letter, $this->letterNotExists, true)) {
            $cause = 'contains a letter "' . $letter . '" that must not contain in the word, but this letter is found in "'.$placeNumber.'" place ';
            $res = CheckResultFactory::factoryResultCause($cause, $checkName, false);
        } else {
            $cause = 'contain a letter "' . $letter . '" that not set as forbidden';
            $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
        }
        
        return $res;
    }

    /**
     * Is letter with defined place number in defined place number in word
     * 
     * Example
     * It is set in the configuration that the third letter (place number 3) 
     * must necessarily be "a".
     * $letterList['a'] = LetterConfigFactory::factory('defined', [3]);
     * 
     * When check 3 letter in word "brAve" - result is true, 
     * When check 3 letter in word "hoUse" - result is false.
     * 
     * @param int $placeNumber
     * @param string $letter
     * @return CheckResult
     */
    private function isLetterInDefinedPlace(int $placeNumber, string $letter): CheckResult
    {
        $checkName = 'is_letter_in_defined_place';
        
        if (array_key_exists($placeNumber, $this->letterPlaceBusyList['defined'])) {
            if ($this->letterPlaceBusyList['defined'][$placeNumber] !== $letter) {
                $cause = 'place ' . $placeNumber . ' contains a letter "' . $letter . '" that is on place ' . $placeNumber . ' where must be letter "' . $this->letterPlaceBusyList['defined'][$placeNumber].'"';
                $res = CheckResultFactory::factoryResultCause($cause, $checkName, false);
            } else {
                $cause = 'place number ' . $placeNumber . ' contains a letter "' . $letter . '"';
                $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
            }
        } else {
            $cause = 'for place number ' . $placeNumber . ' where is letter "'.$letter.'" place number is not set';
            $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
        }
        
        return $res;
    }
    
    /**
     * Checking the letter, for which is indicated as existing in the word, 
     * and places where it must not be located
     * 
     * Example
     * It is set in the configuration that the second letter  "a" (place number 2)
     * must necessarily be in the word but only must not on second place.
     * $letterList['a'] = LetterConfigFactory::factory('undefined', [2]);
     * 
     * pLAce 2nd is letter "L", "A" - is exists and have the place number 3 - result is true
     * bAker 2nd is letter "A" it doesn't equal the condition - result is false
     * 
     * @param int $placeNumber
     * @param string $letter
     * @return CheckResult
     */
    private function isNotLetterInUndefinedPlace(int $placeNumber, string $letter): CheckResult
    {
        $checkName = 'is_not_letter_in_undefined_place';
        
        $res = null;
        
        if (array_key_exists($placeNumber, $this->letterPlaceBusyList['undefined'])) {
            foreach ($this->letterPlaceBusyList['undefined'][$placeNumber] AS $letterUndefined) {
                if ($letterUndefined === $letter) {
                    $cause = 'place ' . $placeNumber . ' contains a letter "' . $letter . '" that is on place ' . $placeNumber . ' where must not be letter(s) ' . join(',', $this->letterPlaceBusyList['undefined'][$placeNumber]).'';
                    $res = CheckResultFactory::factoryResultCause($cause, $checkName, false);
                    break;
                }
            }
            if (!($res instanceof CheckResult)) {
                $cause = 'place number ' . $placeNumber . ' not contains letter(s) "' . join(', ', $this->letterPlaceBusyList['undefined'][$placeNumber]) . '"';
                $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
            }
        } else {
            $cause = 'for place number ' . $placeNumber . ' where is letter "'.$letter.'" place number is not set';
            $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
        }
        
        return $res;
    }
    
    /**
     * Pick words which not contain necessarillies letters.
     * 
     * Example
     * It is set in the configuration that the second letter  "a" (place number 2)
     * must necessarily be in the word but only must not on second place.
     * $letterList['a'] = LetterConfigFactory::factory('undefined', [2]);
     * 
     * brAve - contains letter "a" - return is ture
     * house - do not contaons letter "a" - return is false
     * 
     * @param array $letterUndefinedList - all unique letters of word as an array. 
     *                                     Key is letter.
     * @return CheckResult
     */
    private function isExistsAllUndefinedPlaceLetter(array $letterUndefinedList)
    {
        $checkName = 'is_exists_all_undefined_place_letter';
        
        if (0 === count($letterUndefinedList)) {
            $cause = 'all necessarilly letter found in word';
            $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
        } else {
            $letterUndefinedListValue = array_keys($letterUndefinedList);
            $cause = 'necessarilly letter "' . join(', ', $letterUndefinedListValue) . '" does not found in word';
            $res = CheckResultFactory::factoryResultCause($cause, $checkName, false);
        }
        
        return $res;
    }
    
    /**
     * Check duplicate letter in a word
     * 
     * @param int $successCounter
     * @param array $letterFound
     * @return CheckResult
     */
    private function isWordDoubleLetter(int $successCounter, array $letterFound): CheckResult
    {
        $checkName = 'is_word_double_letter';
        
        if (true === $this->skipWordDoubleLetter) {
            if ($successCounter !== count($letterFound)) {
                $cause = 'contains duplicate letters, duplicate letter checker turned on';
                $res = CheckResultFactory::factoryResultCause($cause, $checkName, false);
            } else {
                $cause = 'does not contains duplicate letters, duplicate letter checker turned off';
                $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
            }
        } else {
            $cause = 'duplicate letter checker turned off';
            $res = CheckResultFactory::factoryResultCause($cause, $checkName, true);
        }
        
        return $res;
    }
    
    /**
     * Save result log to $this->resultLog
     * 
     * @param string $word
     * @param CheckResult $checkResult
     * @return void
     */
    private function setResultLog(string $word, CheckResult $checkResult): void 
    {
        if (true === $this->resultLogOn) {
            $this->resultLog[$word][] = $checkResult;
        }
    }
}
