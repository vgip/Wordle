<?php

declare(strict_types=1);

namespace Vgip\Wordle\Score;

/**
 * Description of LetterUsed
 *
 * @author User
 */
class LetterUsed 
{
    
    /**
     * Number of letters in all words
     * 
     * @var array
     */
    private array $letterListNumber = [];
    
    /**
     * Number max of occurrence letter
     * 
     * @var int
     */
    private int $maxOccurrence;
    
    public function getMaxOccurrence(): int 
    {
        return $this->maxOccurrence;
    }

    public function __construct(array $letterListNumber = [])
    {
        $this->letterListNumber = $letterListNumber;
        $this->maxOccurrence = max($letterListNumber);
    }
    
    /**
     * Get score for every word 
     * 
     * @param array $candidateIn
     * @return array
     */
    public function getScore(array $candidateIn): array
    {
        $candidateOut = [];
        
        foreach ($candidateIn as $word => $score) {
            $scoreWord = $this->getScoreForWord($word);
            $candidateOut[$word] = $score + $scoreWord;
        }
        
        return $candidateOut;
    }
    
    
    private function getScoreForWord(string $word): int
    {
        $letterList = mb_str_split($word, 1);
        
        $score = 0;
        foreach ($letterList AS $letter) {
            $score += $this->letterListNumber[$letter];
        }
        
        return $score;
    }
}
