# Wordle

Pick words from directory according to conditions:
- the letter is in a word in a defined place;
- the letter is in a word in a undefined place;
- the letter is missing from a word.

```php

use Vgip\Wordle\Pick\Pick;
use Vgip\Wordle\Pick\LetterConfigFactory;

/** Get directory of words as array - see example/words_en_5_letter.txt for example */
$pathWords = join(DIRECTORY_SEPARATOR, [__DIR__, 'words_en_5_letter.txt']);
$wordList = file($pathWords);

/** Set conditions to pick of words */
$letterList = [];
$letterList['x'] = false; // The letter "x" is missing from a word.
$letterList['a'] = LetterConfigFactory::factory('defined', [2]); // Set that the letter "a" is in the word in 2nd place
$letterList['n'] = LetterConfigFactory::factory('undefined', [3, 5]); // Set that the letter "n" is not in the word in 3nd and 5nd places

$pick = new Pick();
$candidateList = $pick->getCandidate($wordList, $letterList);
$resultLog = $pick->getResultLog(); 
    
print_r($candidateList);
```


## setSkipWordDoubleLetter()

If set True will be skipped all words with duplicate letters. False set as default.

```php
$pick->setSkipWordDoubleLetter(true);
```


## setResultLogOn()
If set to true, all word selection actions will be logged. False set as default.

```php
$pick->setResultLogOn(true);
$candidateList = $pick->getCandidate($wordList, $letterList);
$resultLog = $pick->getResultLog();
print_r($resultLog);
```