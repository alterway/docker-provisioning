<?php
declare(strict_types = 1);

/**
 * This sniff oblige PHP 7 code to have the `declare(strict_types = 1);` as first statement for each files.
 *
 * PHP version 7
 *
 * @category  PHP_CodeSniffer
 * @package   PHPStormInspections
 * @subpackage Sniffs\General
 */

namespace PHPStormInspections\Sniffs\General;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

/**
 * This sniff oblige PHP 7 code to have the `declare(strict_types = 1);` as first statement for each files.
 *
 * An example of a declaration is:
 *
 * <code>
 *  //Nothing but PHP open tag and documentation before this line.
 *  declare(strict_types = 1);
 * </code>
 *
 * @category  PHP_CodeSniffer
 * @package   PHPStormInspections
 * @subpackage Sniffs\General
 */
class DeclareStrictTypesSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return int[]
     */
    public function register(): array
    {
        return [T_OPEN_TAG];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        //Execute this sniff only for PHP 7
        if (PHP_MAJOR_VERSION < 7) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $openTagStackPtr = $stackPtr;

        //Check the string in the declare as it must be exactly "strict_types = 1".
        $strictTypesDeclarationFound = false;
        do {
            if (false === ($declarePtr = $phpcsFile->findNext(T_DECLARE, $stackPtr))) {
                break;
            }
            $stackPtr = $phpcsFile->findNext(T_STRING, $declarePtr);
            if ('strict_types' !== $tokens[$stackPtr]['content']) {
                //This declare statement is not for "strict_types", so continue to find the good one.
                continue;
            }
            $strictTypesDeclarationFound = true;
        } while (false === $strictTypesDeclarationFound);

        if (false === $strictTypesDeclarationFound) {
            //Here, the declare statement exist in the file but not for strict_types.
            $this->missingStatementDeclareStrictTypes($phpcsFile, $openTagStackPtr);
            return;
        }

        $phpcsFile->recordMetric($stackPtr, 'Use of declare', 'declare');
        $strictTypesValuePtr = $phpcsFile->findNext(T_LNUMBER, $stackPtr);
        if ('1' === $tokens[$strictTypesValuePtr]['content']) {
            //We found it, so return
            return;
        }
        //Declare strict types value is not correct.
        $warning = 'Bad value for the declaration of strict_types.';
        $fix = $phpcsFile->addFixableWarning($warning, $strictTypesValuePtr, 'BadValue');
        if (true === $fix) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($strictTypesValuePtr, 1);
            for ($ptr = $strictTypesValuePtr; T_OPEN_TAG !== $tokens[$ptr]['code']; --$ptr) {
                (T_WHITESPACE !== $tokens[$ptr]['code'] || PHP_EOL !== $tokens[$ptr]['content'])
                    ?: $phpcsFile->fixer->replaceToken($ptr, '');
            }
            $phpcsFile->fixer->endChangeset();
        }
    }

    /**
     * Add the warning of missing statement `declare(strict_types = 1);` in the file.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param $stackPtr
     */
    private function missingStatementDeclareStrictTypes(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $warning = 'Missing statement "declare(strict_types = 1);" at start of file.';
        $fix = $phpcsFile->addFixableWarning($warning, $stackPtr, 'MissingStatement');
        if (true === $fix) {
            $phpcsFile->fixer->beginChangeset();
            for ($ptr = $stackPtr + 1; $tokens[$ptr]['code'] === T_WHITESPACE; ++$ptr) {
                $phpcsFile->fixer->replaceToken($ptr, '');
            }
            $phpcsFile->fixer->addContent($stackPtr, 'declare(strict_types = 1);' . PHP_EOL . PHP_EOL);
            $phpcsFile->fixer->endChangeset();
        }
    }
}
