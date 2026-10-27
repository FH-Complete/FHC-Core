<?php

$config['usePoints'] = (defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE);
// Possible values: both|header|inline
$config['edit'] = 'both';

// Possible values: both|header|inline
$config['delete'] = 'inline';

// Possible values: both|header|inline
$config['documents'] = 'inline';

$config['documentslist'] = gradesDocumentsList();
