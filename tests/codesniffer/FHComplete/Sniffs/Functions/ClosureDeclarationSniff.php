<?php
/**
 * PHP Version 5
 *
 * FHComplete
 */

/**
 * Ensures there is a space after the function keyword for closures.
 *
 */
class FHComplete_Sniffs_Functions_ClosureDeclarationSniff implements PHP_CodeSniffer_Sniff {

    public function register()
    {
        return array(T_CLOSURE);
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $spaces = 0;

        if ($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE) {
            $spaces = strlen($tokens[($stackPtr + 1)]['content']);
        }

        if ($spaces !== 1) {
            $error = 'Expected 1 space after closure\'s function keyword; %s found';
            $data  = array($spaces);
            $phpcsFile->addError($error, $stackPtr, 'SpaceAfterFunction', $data);
        }
    }

}
