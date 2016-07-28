<?php
/**
 * PHP Version 5
 *
 * FHComplete
 */

/**
 * Ensure there is no whitespace before a semicolon.
 *
 */
class FHComplete_Sniffs_WhiteSpace_ObjectOperatorSpacingSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_OBJECT_OPERATOR);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param integer $stackPtr The position of the current token in the stack passed in $tokens.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextType = $tokens[($stackPtr + 1)]['code'];
        if (in_array($nextType, PHP_CodeSniffer_Tokens::$emptyTokens) === true) {
            $error = 'Space found after object operator';
            $phpcsFile->addError($error, $stackPtr, 'After');
        }
    }
}
