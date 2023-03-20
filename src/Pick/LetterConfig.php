<?php

declare(strict_types=1);

namespace Vgip\Wordle\Pick;

use InvalidArgumentException;

/**
 * Description of LetterConfig
 */
class LetterConfig 
{

    /**
     * Letter's position(s) is defined in the word.
     */
    const LETTER_DEFINED = 'defined';

    /**
     * Letter's position(s) is undefined in the word.
     */
    const LETTER_UNDEFINED = 'undefined';

    /**
     * Type of letter
     * 
     * @var string
     */
    private string $type;

    /**
     * There is a place's number in the word
     * @var array
     */
    private array $placeNumber;

    /**
     * Set data
     * 
     * @param string $type
     * @param array $placeNumber
     * @throws Exception
     */
    public function __construct(string $type, array $placeNumber) 
    {
        if (self::LETTER_DEFINED !== $type AND self::LETTER_UNDEFINED !== $type) {
            throw new InvalidArgumentException('Received letter position type does not exist, use only "' . self::LETTER_DEFINED . '" or "' . self::LETTER_UNDEFINED . '" position type');
        }
        
        $this->type = $type;
        $this->placeNumber = $placeNumber;
    }
    
    function getType(): string 
    {
        return $this->type;
    }

    function getPlaceNumber(): array 
    {
        return $this->placeNumber;
    }
}
