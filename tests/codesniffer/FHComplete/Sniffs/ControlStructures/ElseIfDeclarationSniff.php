<?php
/**
 * PHP Version 5
 *
 * FHComplete
 */

/**
 * Ensures that elseif is used instead of else if
 *
 */
class FHComplete_Sniffs_ControlStructures_ElseIfDeclarationSniff implements PHP_CodeSniffer_Sniff
{

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
    public function register()
    {
        return array(T_ELSE);
    }

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * Checks that ELSEIF is used instead of ELSE IF.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param integer              $stackPtr  The position of the current token in the
 *                                        stack passed in $tokens.
 * @return void
 */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$nextToken]['code'] !== T_IF) {
            return;
        }

        $error = 'Usage of ELSE IF not allowed; use ELSEIF instead';
        $phpcsFile->addError($error, $stackPtr, 'NotAllowed');
    }

}
