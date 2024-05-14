<?php
/**
 * Ensures classes are in camel caps, and the first letter is capitalised.
 *
 * NOTE:
 * - it simply overrides the method process of the Standards\Squiz\Sniffs\Classes\ValidClassNameSniff class
 * - it contains a new implementation of PHP_CodeSniffer\Util\Common::isCamelCaps
 *
 */

namespace PHP_CodeSniffer\Standards\FHComplete\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes\ValidClassNameSniff;

class FHCValidClassNameSniff extends ValidClassNameSniff
{
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * NOTE: it does not check if the class name contains an underscore "_"
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being processed.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            $error = 'Possible parse error: %s missing opening or closing brace';
            $data  = [$tokens[$stackPtr]['content']];
            $phpcsFile->addWarning($error, $stackPtr, 'MissingBrace', $data);
            return;
        }

        // Determine the name of the class or interface. Note that we cannot
        // simply look for the first T_STRING because a class name
        // starting with the number will be multiple tokens.
        $opener    = $tokens[$stackPtr]['scope_opener'];
        $nameStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), $opener, true);
        $nameEnd   = $phpcsFile->findNext(T_WHITESPACE, $nameStart, $opener);
        if ($nameEnd === false) {
            $name = $tokens[$nameStart]['content'];
        } else {
            $name = trim($phpcsFile->getTokensAsString($nameStart, ($nameEnd - $nameStart)));
        }

        // Check for PascalCase format.
        $valid = $this->isCamelCaps($name);
        if ($valid === false) {
            $type  = ucfirst($tokens[$stackPtr]['content']);
            $error = '%s name "%s" is not in PascalCase format';
            $data  = [
                $type,
                $name,
            ];
            $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $data);
            $phpcsFile->recordMetric($stackPtr, 'PascalCase class name', 'no');
        } else {
            $phpcsFile->recordMetric($stackPtr, 'PascalCase class name', 'yes');
        }

    }//end process()

    /**
     * Returns true if the specified string is in the camel caps format.
     *
     * NOTE:
     * - it does not allow the string to start with an underscore "_"
     * - it does allow that the string contains an underscore "_"
     * - the string must starts with a capitol letter
     *
     * @param string  $string      The string the verify.
     *
     * @return boolean
     */
    private function isCamelCaps($string)
    {
        $legalFirstChar = '[A-Z]';

        if (preg_match("/^$legalFirstChar/", $string) === 0) {
            return false;
        }

        // Check that the name only contains legal characters.
        $legalChars = 'a-zA-Z0-9_';
        if (preg_match("|[^$legalChars]|", substr($string, 1)) > 0) {
            return false;
        }

        return true;

    }//end isCamelCaps()
}//end class

