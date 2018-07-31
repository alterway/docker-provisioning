<?php
/**
 * This sniff detects the maximum number of lines by function/method.
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

class FunctionLengthSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Return in warning or in error
     * @var string
     */
    public $isWarning = false;

    /**
     * The maximum number of lines a function or method
     * should have.
     * @var integer
     */
    public $maxLength = 20;
    /**
     * Returns the token types that this sniff is interested in.
     * @return array
     */
    public function register(): array
    {
        return [T_FUNCTION];
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
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        // Skip function without body.
        if (isset($token['scope_opener']) === false) {
            return;
        }
        $firstToken = $tokens[$token['scope_opener']];
        $lastToken = $tokens[$token['scope_closer']];
        $length = $lastToken['line'] - $firstToken['line'];
        if ($length > $this->maxLength) {
            $tokenType = strtolower(substr($token['type'], 2));
            $error = "Function is {$length} lines. Must be {$this->maxLength} lines or fewer.";

            if ($this->isWarning) {
                $phpcsFile->addWarning($error, $stackPtr, sprintf('%sTooBig', ucfirst($tokenType)));
            } else {
                $phpcsFile->addError($error, $stackPtr, sprintf('%sTooBig', ucfirst($tokenType)));
            }
        }
    }
}