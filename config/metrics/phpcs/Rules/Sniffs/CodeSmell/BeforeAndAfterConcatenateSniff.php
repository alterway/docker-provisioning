<?php
/**
 * This sniff detects missing spaces surround the concatenate operator.
 *
 * PHP version 7
 *
 * @category  PHP_CodeSniffer
 * @package   PHPStormInspections
 * @subpackage Sniffs\CodeSmell
 */
declare(strict_types = 1);

namespace PHPStormInspections\Sniffs\CodeSmell;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * This sniff detects missing spaces surround the concatenate operator.
 *
 * Example
 *
 * <code>
 *  $var = $var."test";
 * </code>
 *
 * @category  PHP_CodeSniffer
 * @package   PHPStormInspections
 * @subpackage Sniffs\CodeSmell
 */
class BeforeAndAfterConcatenateSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return int[]
     */
    public function register(): array
    {
        return [T_STRING_CONCAT];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int $stackPtr The position in the stack where the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr + 1]['code'] === T_WHITESPACE && $tokens[$stackPtr - 1]['code'] === T_WHITESPACE) {
            return;
        }

        $error = 'You must surround the concat operator by one space.';
        $fix = $phpcsFile->addFixableError($error, $stackPtr);
        if (false === $fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        if ($tokens[$stackPtr - 1]['code'] !== T_WHITESPACE) {
            $phpcsFile->fixer->addContentBefore($stackPtr, ' ');
        }
        if ($tokens[$stackPtr + 1]['code'] !== T_WHITESPACE) {
            $phpcsFile->fixer->addContent($stackPtr, ' ');
        }
        $phpcsFile->fixer->endChangeset();
    }
}
