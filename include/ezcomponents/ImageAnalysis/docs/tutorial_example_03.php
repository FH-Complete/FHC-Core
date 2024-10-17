<?php
require_once 'tutorial_autoload.php';
$tutorialPath = dirname( __FILE__ );

ezcImageAnalyzer::setHandlerClasses(
    array(
        'ezcImageAnalyzerImagemagickHandler' => array( 'binary' => '/usr/bin/identify' ),
    )
);

$image = new ezcImageAnalyzer( $tutorialPath.'/img/imageanalysis_example_03.jpg' );

echo "Image data:\n";
echo "MIME type:\t{$image->mime}\n";
echo "Width:\t\t{$image->data->width} px\n";
echo "Height:\t\t{$image->data->height} px\n";
echo "Filesize:\t{$image->data->size} b\n";

$comment = ( $image->data->comment == '' ) ? 'n/a' : $image->data->comment; 
echo "Comment:\t{$comment}\n";
?>
