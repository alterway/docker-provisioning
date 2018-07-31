<?php
declare(strict_types = 1);

/**
 * This sniff detects if  "else" or "elseif" is used.
 *
 * PHP version 7
 *
 * @category  PHP_CodeSniffer
 * @package   PHPStormInspections
 * @subpackage Sniffs\CodeStyle
 */

namespace PHPStormInspections\Sniffs\CodeStyle;

use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_File;

/**
 * Do not use "else" or "elseif".
 */
class NoElseSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The operator tokens we're lookin for.
     * @var array
     */
    public $operators = [T_ELSE, T_ELSEIF];

    /**
     * Return in warning or in error
     * @var string
     */
    public $isWarning = false;

    /**
     * Returns the token types that this sniff is interested in.
     * @return array
     */
    public function register(): array
    {
        return $this->operators;
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param integer              $stackPtr  The position in the stack where
     *                                    the token was found.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $error = 'Do not use "else" or "elseif"';
        if ($this->isWarning) {
            $phpcsFile->addWarning($error, $stackPtr);
        } else {
            $phpcsFile->addError($error, $stackPtr);
        }
    }
}