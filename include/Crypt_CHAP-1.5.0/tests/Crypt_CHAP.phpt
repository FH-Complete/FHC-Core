--TEST--
Crypt_CHAP: simple test 
--SKIPIF--
<?php if (!extension_loaded("hash")) echo 'skip'; ?>
--FILE--
<?php
/*
Copyright (c) 2003-2007, Michael Bretterklieber <michael@bretterklieber.com>
All rights reserved.

Redistribution and use in source and binary forms, with or without 
modification, are permitted provided that the following conditions 
are met:

1. Redistributions of source code must retain the above copyright 
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright 
   notice, this list of conditions and the following disclaimer in the 
   documentation and/or other materials provided with the distribution.
3. The names of the authors may not be used to endorse or promote products 
   derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY 
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

This code cannot simply be copied and put under the GNU Public License or 
any other GPL-like (LGPL, GPL2) License.

    $Id: chaptest.php 231819 2007-03-14 07:39:07Z mbretter $
*/
chdir (dirname(__FILE__));
if (file_exists('../CHAP.php')) {
    require_once '../CHAP.php';
} else {
    require_once 'Crypt/CHAP.php';
}

echo "CHAP-MD5 TEST\n";
$crpt = new Crypt_CHAP_MD5;
$crpt->password = 'MyPw';
$crpt->chapid = 1;
$crpt->challenge = pack('H*', '102DB5DF085D3041');
printf ("ChallResp : %s\n", bin2hex($crpt->challengeResponse()));
echo "\n";

echo "CHAP-MD5 TEST 2\n";
$crpt = new Crypt_CHAP_MD5;
$crpt->password = 'sepp';
$crpt->chapid = 1;
$crpt->challenge = pack('H*', '102DB5DF085D3041');
printf ("ChallResp : %s\n", bin2hex($crpt->challengeResponse()));
echo "\n";

echo "MS-CHAPv1 str2unicode\n";
$crpt = new Crypt_CHAP_MSv1;
printf("Passed 123 as Number:%s\n", bin2hex($crpt->str2unicode(123)));
printf("Passed 123 as String:%s\n", bin2hex($crpt->str2unicode('123')));
echo "\n";

echo "MS-CHAPv1 TEST\n";
$crpt->password = 'MyPw';
$crpt->challenge = pack('H*', '102DB5DF085D3041');
$unipw = $crpt->str2unicode($crpt->password);
printf ("Unicode PW: %s\n", bin2hex($unipw));
printf ("NT HASH   : %s\n", bin2hex($crpt->ntPasswordHash()));
printf ("NT Resp   : %s\n", bin2hex($crpt->challengeResponse()));
printf ("LM HASH   : %s\n", bin2hex($crpt->lmPasswordHash()));
printf ("LM Resp   : %s\n", bin2hex($crpt->lmChallengeResponse()));
//printf ("Response  : %s\nexpected  : unknown\n", bin2hex($crpt->response()));
echo "\n";

echo "MS-CHAPv2 TEST\n";
$crpt = new Crypt_CHAP_MSv2;
$crpt->username = 'User';
$crpt->password = 'clientPass';
printf ("Username  : %s\n", bin2hex($crpt->username));
$crpt->authChallenge = pack('H*', '5b5d7c7d7b3f2f3e3c2c602132262628');
$crpt->peerChallenge = pack('H*', '21402324255E262A28295F2B3A337C7E');
$nthash = $crpt->ntPasswordHash();
printf ("NT HASH      : %s\n", bin2hex($nthash));
$nthashhash = $crpt->ntPasswordHashHash($nthash);
printf ("NT HASH-HASH : %s\n", bin2hex($nthashhash));
printf ("ChallResp    : %s\n", bin2hex($crpt->challengeResponse()));
printf ("Challenge    : %s\n", bin2hex($crpt->challenge));
echo "\n";
?>
--EXPECT--
CHAP-MD5 TEST
ChallResp : 8f028814450d66d94c72331ef455a172

CHAP-MD5 TEST 2
ChallResp : d39bfaf5d6855a948c8c81a85947502c

MS-CHAPv1 str2unicode
Passed 123 as Number:310032003300
Passed 123 as String:310032003300

MS-CHAPv1 TEST
Unicode PW: 4d00790050007700
NT HASH   : fc156af7edcd6c0edde3337d427f4eac
NT Resp   : 4e9d3c8f9cfd385d5bf4d3246791956ca4c351ab409a3d61
LM HASH   : 75ba30198e6d1975aad3b435b51404ee
LM Resp   : 91881d0152ab0c33c524135ec24a95ee64e23cdc2d33347d

MS-CHAPv2 TEST
Username  : 55736572
NT HASH      : 44ebba8d5312b8d611474411f56989ae
NT HASH-HASH : 41c00c584bd2d91c4017a2a12fa59f3f
ChallResp    : 82309ecd8d708b5ea08faa3981cd83544233114a3d85d6df
Challenge    : d02e4386bce91226

