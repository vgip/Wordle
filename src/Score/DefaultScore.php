<?php

declare(strict_types=1);

namespace Vgip\Wordle\Score;

/**
 * Set score by default
 */
class DefaultScore 
{
    private int $defaultScroe = 150000;
    
    public function setDefaultScore(int $defaultScroe): void 
    {
        $this->defaultScroe = $defaultScroe;
    }
    
    public function getScore(array $candidateIn): array
    {
        $candidateOut = [];
        
        foreach ($candidateIn AS $word) {
            $candidateOut[$word] = $this->defaultScroe;
        }
        
        return $candidateOut;
    }
}
