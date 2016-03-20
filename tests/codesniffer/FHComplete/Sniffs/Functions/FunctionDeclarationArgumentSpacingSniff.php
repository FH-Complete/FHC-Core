<?php
/**
 * PHP Version 5
 *
 * FHComplete
 */
if (class_exists('Squiz_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff', true) === false) {
    $error = 'Class Squiz_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Ensures the spacing of function declaration arguments is correct.
 *
 */
class FHComplete_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff extends
    Squiz_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff {

    /**
     * How many spaces should surround the equals signs.
     *
     * @var int
     */
    public $equalsSpacing = 1;

}
