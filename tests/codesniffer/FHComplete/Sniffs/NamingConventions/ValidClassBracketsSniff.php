<?php
/**
 * PHP Version 5
 *
 * FHComplete
 */

/**
 * Ensures curly brackets are on the same line as the Class declaration
 *
 */
class FHComplete_Sniffs_NamingConventions_ValidClassBracketsSniff implements PHP_CodeSniffer_Sniff
{
/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
    public function register()
    {
        return array(T_CLASS);
    }

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param integer $stackPtr  The position of the current token in the stack passed in $tokens.
 * @return void
 */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $found = $phpcsFile->findNext(T_OPEN_CURLY_BRACKET, $stackPtr);
        if ($tokens[$found - 1]['code'] != T_WHITESPACE) {
            $error = 'Expected 1 space after class declaration, found 0';
            $phpcsFile->addError($error, $found - 1, 'InvalidSpacing', array());
            return;
        }

        if (strlen($tokens[$found - 1]['content']) > 1 || $tokens[$found - 2]['code'] == T_WHITESPACE) {
            $error = 'Expected 1 space after class declaration, found ' . strlen($tokens[$found - 1]['content']);
            $phpcsFile->addError($error, $found - 1, 'InvalidSpacing', array());
        }
    }
}
