<?php
/**
 * PHP Version 5
 *
 * FHComplete
 */

/**
 * Ensures trait names are correct depending on the folder of the file.
 *
 */
class FHComplete_Sniffs_NamingConventions_ValidTraitNameSniff implements PHP_CodeSniffer_Sniff
{

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * If the constant is not defined, ignore because probably the PHP version
 * is under 5.4.0 and don't have traits in use
 *
 * @return array
 */
    public function register()
    {
        if (!defined('T_TRAIT')) {
            return array();
        }
        return array(T_TRAIT);
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
        $traitName = $tokens[$stackPtr + 2]['content'];

        if (substr($traitName, -5) !== 'Trait') {
            $error = 'Traits must have a "Trait" suffix.';
            $phpcsFile->addError($error, $stackPtr, 'InvalidTraitName', array());
        }
    }
}
