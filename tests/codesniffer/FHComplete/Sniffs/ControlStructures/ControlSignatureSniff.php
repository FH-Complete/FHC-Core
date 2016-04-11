<?php
/**
 * Verifies that control statements conform to their coding standards.
 *
 * PHP version 5
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractPatternSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractPatternSniff not found');
}

/**
 * Verifies that control statements conform to their coding standards.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class FHComplete_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff
{

    /**
     * If true, comments will be ignored if they are found in the code.
     *
     * @var boolean
     */
    public $ignoreComments = true;


    /**
     * Returns the patterns that this test wishes to verify.
     *
     * @return string[]
     */
    protected function getPatterns()
    {
        return array(
            'try {EOL...}\s+catch (...)EOL...{EOL...EOL...}',
            'do+EOL...{EOL...EOL...} while (...);EOL',
            'while (...)EOL...{EOL',
            'for (...) {EOL',
            'if (...)EOL...{EOL',
            'foreach (...)EOL...{EOL',
            '}EOL...\s+else if (...)EOL...{EOL',
            '}EOL...\s+elseif (...)EOL...{EOL',
            '}EOL...\s+else+EOL...{EOL',
            'do+EOL...{EOL',
       );

    }//end getPatterns()


}//end class
