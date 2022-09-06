<?php
/**
 * Verifies that properties are declared correctly.
 *
 * NOTE: It simply overrides the method processMemberVar of the Standards\PSR2\Sniffs\Classes\PropertyDeclarationSniff class
 *
 */

namespace PHP_CodeSniffer\Standards\FHComplete\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Classes\PropertyDeclarationSniff;

class FHCPropertyDeclarationSniff extends PropertyDeclarationSniff
{
    /**
     * Processes the function tokens within the class.
     *
     * NOTE: it does not check if the property name starts with an underscore "_"
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Detect multiple properties defined at the same time. Throw an error
        // for this, but also only process the first property in the list so we don't
        // repeat errors.
        $find   = Tokens::$scopeModifiers;
        $find[] = T_VARIABLE;
        $find[] = T_VAR;
        $find[] = T_SEMICOLON;
        $find[] = T_OPEN_CURLY_BRACKET;

        $prev = $phpcsFile->findPrevious($find, ($stackPtr - 1));
        if ($tokens[$prev]['code'] === T_VARIABLE) {
            return;
        }

        if ($tokens[$prev]['code'] === T_VAR) {
            $error = 'The var keyword must not be used to declare a property';
            $phpcsFile->addError($error, $stackPtr, 'VarUsed');
        }

        $next = $phpcsFile->findNext([T_VARIABLE, T_SEMICOLON], ($stackPtr + 1));
        if ($next !== false && $tokens[$next]['code'] === T_VARIABLE) {
            $error = 'There must not be more than one property declared per statement';
            $phpcsFile->addError($error, $stackPtr, 'Multiple');
        }

        try {
            $propertyInfo = $phpcsFile->getMemberProperties($stackPtr);
            if (empty($propertyInfo) === true) {
                return;
            }
        } catch (\Exception $e) {
            // Turns out not to be a property after all.
            return;
        }

        if ($propertyInfo['type'] !== '') {
            $typeToken = $propertyInfo['type_end_token'];
            $error     = 'There must be 1 space after the property type declaration; %s found';
            if ($tokens[($typeToken + 1)]['code'] !== T_WHITESPACE) {
                $data = ['0'];
                $fix  = $phpcsFile->addFixableError($error, $typeToken, 'SpacingAfterType', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->addContent($typeToken, ' ');
                }
            } else if ($tokens[($typeToken + 1)]['content'] !== ' ') {
                $next = $phpcsFile->findNext(T_WHITESPACE, ($typeToken + 1), null, true);
                if ($tokens[$next]['line'] !== $tokens[$typeToken]['line']) {
                    $found = 'newline';
                } else {
                    $found = $tokens[($typeToken + 1)]['length'];
                }

                $data = [$found];

                $nextNonWs = $phpcsFile->findNext(Tokens::$emptyTokens, ($typeToken + 1), null, true);
                if ($nextNonWs !== $next) {
                    $phpcsFile->addError($error, $typeToken, 'SpacingAfterType', $data);
                } else {
                    $fix = $phpcsFile->addFixableError($error, $typeToken, 'SpacingAfterType', $data);
                    if ($fix === true) {
                        if ($found === 'newline') {
                            $phpcsFile->fixer->beginChangeset();
                            for ($x = ($typeToken + 1); $x < $next; $x++) {
                                $phpcsFile->fixer->replaceToken($x, '');
                            }

                            $phpcsFile->fixer->addContent($typeToken, ' ');
                            $phpcsFile->fixer->endChangeset();
                        } else {
                            $phpcsFile->fixer->replaceToken(($typeToken + 1), ' ');
                        }
                    }
                }
            }//end if
        }//end if

        if ($propertyInfo['scope_specified'] === false) {
            $error = 'Visibility must be declared on property "%s"';
            $data  = [$tokens[$stackPtr]['content']];
            $phpcsFile->addError($error, $stackPtr, 'ScopeMissing', $data);
        }

        if ($propertyInfo['scope_specified'] === true && $propertyInfo['is_static'] === true) {
            $scopePtr  = $phpcsFile->findPrevious(Tokens::$scopeModifiers, ($stackPtr - 1));
            $staticPtr = $phpcsFile->findPrevious(T_STATIC, ($stackPtr - 1));
            if ($scopePtr < $staticPtr) {
                return;
            }

            $error = 'The static declaration must come after the visibility declaration';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'StaticBeforeVisibility');
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();

                for ($i = ($scopePtr + 1); $scopePtr < $stackPtr; $i++) {
                    if ($tokens[$i]['code'] !== T_WHITESPACE) {
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->replaceToken($scopePtr, '');
                $phpcsFile->fixer->addContentBefore($staticPtr, $propertyInfo['scope'].' ');

                $phpcsFile->fixer->endChangeset();
            }
        }//end if

    }//end processMemberVar()
}//end class

