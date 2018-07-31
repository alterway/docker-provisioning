<?php
/**
 * This sniff detects the maximum number of lines by class.
 *
 * PHP version 7
 *
 * @category  PHP_CodeSniffer
 * @package   PHPStormInspections
 * @subpackage Sniffs\CodeSmell
 */
declare(strict_types = 1);

namespace PHPStormInspections\Sniffs\CodeSmell;

use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_File;

class ClassLengthSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Return in warning or in error
     * @var string
     */
    public $isWarning = false;

    /**
     * The maximum number of lines a class
     * should have.
     * @var integer
     */
    public $maxLength = 200;

    /**
     * Returns the token types that this sniff is interested in.
     * @return array
     */
    public function register(): array
    {
        return [T_CLASS];
    }
    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        $openParenthesis = $tokens[$token['scope_opener']];
        $closedParenthesis = $tokens[$token['scope_closer']];
        $length = $closedParenthesis['line'] - $openParenthesis['line'];
        if ($length > $this->maxLength) {
            $error = "Class is {$length} lines. Must be {$this->maxLength} lines or fewer.";

            if ($this->isWarning) {
                $phpcsFile->addWarning($error, $stackPtr);
            } else {
                $phpcsFile->addError($error, $stackPtr);
            }
        }
    }
}