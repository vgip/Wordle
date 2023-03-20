<?php

declare(strict_types=1);

namespace Vgip\Wordle\Pick;

/**
 * Result of check for debug
 */
class CheckResult 
{
    private string $word;
    
    private string $cause;
    
    private string $checkName;
    
    private bool $result;
    
    public function getWord(): string 
    {
        return $this->word;
    }

    public function getCause(): string 
    {
        return $this->cause;
    }
    
    public function getCheckName(): string 
    {
        return $this->checkName;
    }

    public function getResult(): bool 
    {
        return $this->result;
    }

    public function setWord(string $word): void 
    {
        $this->word = $word;
    }

    public function setCause(string $cause): void 
    {
        $this->cause = $cause;
    }
    
    public function setCheckName(string $checkName): void 
    {
        $this->checkName = $checkName;
    }

    public function setResult(bool $result): void
    {
        $this->result = $result;
    }
}
