<?php

declare(strict_types=1);

namespace Vgip\Wordle\Score;

/**
 * Set score by using words before
 */
class WordUsed
{
    /**
     * List of previously used words
     * 
     * @var array - key is word, value - quantity of usage
     */
    private array $usageWord = [];
    
    /**
     * Decrease coefficients for words which are doubles depending double quantity found
     * 
     * @var array
     */
    private array $doubleCoefficientList = [];

    /**
     * Constructor
     * 
     * @param array $usageWord - key is word, value - quantity of usage
     * @return void
     */
    public function __construct(array $usageWord = [])
    {
        $this->usageWord = $usageWord;
        
        $maxDoubleQuantity = 0;
        foreach ($usageWord as $word => $doubleQuantity) {
            $maxDoubleQuantity = ($maxDoubleQuantity < $doubleQuantity) ? $doubleQuantity : $maxDoubleQuantity;
        }
        
        for ($i = 1; $i <= $maxDoubleQuantity; $i++) {
            $coefficien = $maxDoubleQuantity - $i + 1;
            $this->doubleCoefficientList[$i] = $coefficien;
        }
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
        $coefficientDoubleNot = count($this->doubleCoefficientList) + 1;

        foreach ($candidateIn as $word => $score) {
            if (array_key_exists($word, $this->usageWord)) {
                $coefficient = $this->doubleCoefficientList[$this->usageWord[$word]];
            } else {
                $coefficient = $coefficientDoubleNot;
            }
            
            $candidateOut[$word] = $score * $coefficient;
        }
        
        return $candidateOut;
    }
}
