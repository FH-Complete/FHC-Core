<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/tools/unit_test.php,v 1.20 2005/07/16 16:23:28 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

include './common.php';
include './header.php';
echo "<pre>";

require_once realpath( 'functions.php' );

// test DN sorting
if( false ) {
    $dns = array( "ou=people,dc=example,dc=com", 
                  "cn=Admin,ou=People,dc=example,dc=com", 
                  "cn=Joe,ou=people,dc=example,dc=com",
                  "dc=example,dc=com",
                  "cn=Fred,ou=people,dc=example,dc=org",
                  "cn=Dave,ou=people,dc=example,dc=org",
                  "dc=com"
                  );
    usort( $dns, "pla_compare_dns" );
    foreach( $dns as $dn )
        echo pretty_print_dn( $dn ) . "<br>";
}

// test pla_verbose_error() using ldap_error_codes.txt
if( false) {
    for( $i=0; $i<255; $i++ ) {
        $num = "0x" . str_pad( dechex( $i ), 2, "0", STR_PAD_LEFT );
        var_dump( $num );
        print_r( pla_verbose_error( $num ) );
    }
}

// tests is_dn_string()
if( false ) {
    $dn_strs = array(     ' cn=joe,dc=example,dc=com', 
                          'cn = joe, dc= example, dc =com', 
                          '  cn=asdf asdf, ou= foo bar, o =foo bar, dc=com',
                          'cn=True!=False,dc=example,dc=com' );
    $not_dn_strs = array( ' asdf asdf ', 
                          '== asdf asdf ',
                          ' = = = = = = = =' );

    echo "All should be true:\n";
    foreach( $dn_strs as $str ) {
        echo "\"$str\"\n";
        var_dump( is_dn_string( $str ) );
    }
    echo "\nAll should be false:\n";
    foreach( $not_dn_strs as $str ) {
        echo "\"$str\"\n";
        var_dump( is_dn_string( $str ) );
    }
}

// tests pla_compare_dns()
if( false ) {
    echo "Should all be 0:<br>";
    $dns1 = array( 'cn=joe,dc=example,dc=com', 'cn=joe,dc=example,dc=com', 'cn = bob, dc= example,dc =com' );
    $dns2 = array( 'cn=joe,dc=example,dc=com', 'CN =joe,dc=Example,dc =com', 'cn= bob, dc= example,dc =com' );

    for( $i=0; $i<count($dns1); $i++ ) {
        var_dump( pla_compare_dns( $dns1[$i], $dns2[$i] ) );
        echo "\n";
    }

    echo "Should all be ! 0:<br>";
    $dns1 = array( 'dc=test,dc=example,dc=com', 'cn=Fred,cn=joe,dc=example,dc=com', 'cn=joe2,dc=example,dc=com', 'cn = bob, dc= example,dc =com' );
    $dns2 = array( 'dc=example, dc=com', 'cn=joe,dc=example,dc=com', 'CN =joe,dc=Example2,dc =com', 'cn= 2bob, dc= example,dc =com' );

    for( $i=0; $i<count($dns1); $i++ ) {
        var_dump( pla_compare_dns( $dns1[$i], $dns2[$i] ) );
        echo "\n";
    }
}

// testing get_rdn()
if( false ) {
    echo "Should be uid=bäb: ";
    echo get_rdn( "uid=bäb,ou=People-copy1,ou=People-copy2,ou=People2,dc=example,dc=com" );
    echo "<br>\n";
    echo "Should be dc=com: ";
    echo get_rdn( "dc=com" );
    echo "<br>\n";
    echo "Should be Fred: ";
    echo get_rdn( "Fred" );
    echo "<br>\n";
}

// testing get_container()
if( false ) {
    echo "Should be ou=People-copy1,ou=People-copy2,ou=People2,dc=example,dc=com: ";
    var_dump( get_container( "uid=bäb,ou=People-copy1,ou=People-copy2,ou=People2,dc=example,dc=com" ) );
    echo "<br>\n";
    echo "Should be null: ";
    var_dump( get_container( "dc=com" ) );
    echo "<br>\n";
    echo "Should be null: ";
    var_dump( get_container( "Fred" ) );
    echo "<br>\n";
}

// tests pla_explode_dn()
if( false ) {
    var_dump( pla_explode_dn( "cn=<stuff>,dc=example,dc=<com>" ) );
}

if( false ) {
    $password = 'asdf@sadf';
    foreach( array('md5','md5crypt','sha','ssha','smd5','crypt','clear') as $enc_type ) {
        $crypted_password = password_hash($password,$enc_type);
        print "[".$enc_type."] ".$crypted_password."<br />";
        print "  Test: " . (password_check($crypted_password,$password) ? "passed" : "failed" );
        print "\n";
        //unset($crypted_password);
        flush();
    }
}

if( true ) {
    $secret = "foobar";
    $passwords = array( 'fun!244A', 'asdf', 'dc=stuff,ou=things', 'y()ikes' );

    $passwords_encrypted = array();
    foreach( $passwords as $password ) {
        $passwords_encrypted[] = pla_blowfish_encrypt( $password, $secret );        
    }

    $passwords_decrypted = array();
    foreach( $passwords_encrypted as $password ) {
        $passwords_decrypted[] = pla_blowfish_decrypt( $password, $secret );
    }

    foreach( $passwords_decrypted as $i => $password ) {
        echo $passwords[$i] . ': ' . $passwords_encrypted[$i] . '<br />    ';
        if( $passwords[$i] == $passwords_decrypted[$i] )
            echo "passed<br />";
        else
            echo "<b>failed!</b></br />";
    }
}

print password_generate();
?>
