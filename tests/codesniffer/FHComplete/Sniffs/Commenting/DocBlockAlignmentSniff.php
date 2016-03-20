<?php
/**
 * FHComplete
 */

/**
 * Ensures doc block alignments.
 */
class FHComplete_Sniffs_Commenting_DocBlockAlignmentSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_DOC_COMMENT_OPEN_TAG);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param integer              $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $leftWall = array(
            T_CLASS,
            T_NAMESPACE,
            T_INTERFACE,
            T_TRAIT,
            T_USE
        );
        $oneIndentation = array(
            T_FUNCTION,
            T_VARIABLE,
            T_CONST
        );
        $allTokens = array_merge($leftWall, $oneIndentation);
        $notFlatFile = $phpcsFile->findNext(T_NAMESPACE, 0);
        $next = $phpcsFile->findNext($allTokens, $stackPtr + 1);

        if ($next && $notFlatFile) {
            $notWalled = (in_array($tokens[$next]['code'], $leftWall) && $tokens[$stackPtr]['column'] !== 1);
            $notIndented = (in_array($tokens[$next]['code'], $oneIndentation) && $tokens[$stackPtr]['column'] !== 5);
            if ($notWalled || $notIndented) {
                $phpcsFile->addError('Expected docblock to be aligned with code.', $stackPtr, 'NotAllowed');
            }
        }

        return;
    }
}
