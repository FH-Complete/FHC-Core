<?php
/**
 * Verifies that control statements conform to their coding standards.
 *
 * NOTE: It simply overrides the method process of the PHP_CodeSniffer\Standards\Squiz\Sniffs\ControlStructures\ControlSignatureSniff class
 *
 */

namespace PHP_CodeSniffer\Standards\FHComplete\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\ControlStructures\ControlSignatureSniff;

class FHCControlSignatureSniff extends ControlSignatureSniff
{
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * NOTE:
     * - Does not force to have whitespaces after the brackets
     * - Allows to have a bracket at newline after a control structure, ex:
     * 
     *     if (condition)
     *     {
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
        if ($nextNonEmpty === false) {
            return;
        }

        $isAlternative = false;
        if (isset($tokens[$stackPtr]['scope_opener']) === true
            && $tokens[$tokens[$stackPtr]['scope_opener']]['code'] === T_COLON
        ) {
            $isAlternative = true;
        }

        // Single newline after opening brace.
        if (isset($tokens[$stackPtr]['scope_opener']) === true) {
            $opener = $tokens[$stackPtr]['scope_opener'];
            for ($next = ($opener + 1); $next < $phpcsFile->numTokens; $next++) {
                $code = $tokens[$next]['code'];

                if ($code === T_WHITESPACE
                    || ($code === T_INLINE_HTML
                    && trim($tokens[$next]['content']) === '')
                ) {
                    continue;
                }

                // Skip all empty tokens on the same line as the opener.
                if ($tokens[$next]['line'] === $tokens[$opener]['line']
                    && (isset(Tokens::$emptyTokens[$code]) === true
                    || $code === T_CLOSE_TAG)
                ) {
                    continue;
                }

                // We found the first bit of a code, or a comment on the
                // following line.
                break;
            }//end for

            if ($tokens[$next]['line'] === $tokens[$opener]['line']) {
                $error = 'Newline required after opening brace';
                $fix   = $phpcsFile->addFixableError($error, $opener, 'NewlineAfterOpenBrace');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($opener + 1); $i < $next; $i++) {
                        if (trim($tokens[$i]['content']) !== '') {
                            break;
                        }

                        // Remove whitespace.
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->addContent($opener, $phpcsFile->eolChar);
                    $phpcsFile->fixer->endChangeset();
                }
            }//end if
        } else if ($tokens[$stackPtr]['code'] === T_WHILE) {
            // Zero spaces after parenthesis closer, but only if followed by a semicolon.
            $closer       = $tokens[$stackPtr]['parenthesis_closer'];
            $nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($closer + 1), null, true);
            if ($nextNonEmpty !== false && $tokens[$nextNonEmpty]['code'] === T_SEMICOLON) {
                $found = 0;
                if ($tokens[($closer + 1)]['code'] === T_WHITESPACE) {
                    if (strpos($tokens[($closer + 1)]['content'], $phpcsFile->eolChar) !== false) {
                        $found = 'newline';
                    } else {
                        $found = $tokens[($closer + 1)]['length'];
                    }
                }

                if ($found !== 0) {
                    $error = 'Expected 0 spaces before semicolon; %s found';
                    $data  = [$found];
                    $fix   = $phpcsFile->addFixableError($error, $closer, 'SpaceBeforeSemicolon', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->replaceToken(($closer + 1), '');
                    }
                }
            }
        }//end if

        // Only want to check multi-keyword structures from here on.
        if ($tokens[$stackPtr]['code'] === T_WHILE) {
            if (isset($tokens[$stackPtr]['scope_closer']) !== false) {
                return;
            }

            $closer = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
            if ($closer === false
                || $tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET
                || $tokens[$tokens[$closer]['scope_condition']]['code'] !== T_DO
            ) {
                return;
            }
        } else if ($tokens[$stackPtr]['code'] === T_ELSE
            || $tokens[$stackPtr]['code'] === T_ELSEIF
            || $tokens[$stackPtr]['code'] === T_CATCH
            || $tokens[$stackPtr]['code'] === T_FINALLY
        ) {
            if (isset($tokens[$stackPtr]['scope_opener']) === true
                && $tokens[$tokens[$stackPtr]['scope_opener']]['code'] === T_COLON
            ) {
                // Special case for alternate syntax, where this token is actually
                // the closer for the previous block, so there is no spacing to check.
                return;
            }

            $closer = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
            if ($closer === false || $tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET) {
                return;
            }
        } else {
            return;
        }//end if
    }//end process()
}//end class

