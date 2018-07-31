<?php
declare(strict_types = 1);

/**
 * This sniff detects if methods return null.
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

class ReturnNullSniff implements PHP_CodeSniffer_Sniff
{
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
        return [T_RETURN];
    }
    /**
     * Processes the tokens that this sniff is interested in.
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param integer              $stackPtr  The position in the stack where
     *                                    the token was found.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $returnTokenIndex = $stackPtr;
        $scope = array_slice($tokens, $returnTokenIndex, null, true);
        $semicolons = array_filter($scope, function ($token) {
            return $token['type'] === 'T_SEMICOLON';
        });
        $returnValueIndex = key($semicolons) - 1;
        if ($scope[$returnValueIndex]['type'] === 'T_NULL') {
            $error = "Return null value found.";

            if ($this->isWarning) {
                $phpcsFile->addWarning($error, $returnValueIndex);
            } else {
                $phpcsFile->addError($error, $returnValueIndex);
            }
        }
    }
}