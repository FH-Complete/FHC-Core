<?php
/**
 * PHP Version 5
 *
 * FHComplete
 */

/**
 * Makes sure there are spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
 */
class FHComplete_Sniffs_Strings_ConcatenationSpacingSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING_CONCAT);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param integer $stackPtr The position of the current token in the
     *    stack passed in $tokens.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[($stackPtr - 1)]['code'] == T_WHITESPACE) {
            $message = 'Expected 0 spaces before ., but 1 found';
            $phpcsFile->addError($message, $stackPtr, 'FoundBefore');
        }

        if ($tokens[($stackPtr + 1)]['code'] == ' ') 
		{
            $message = 'Expected 0 spaces after ., but 1 found';
            $phpcsFile->addError($message, $stackPtr, 'FoundAfter');
        } 
    }
}
