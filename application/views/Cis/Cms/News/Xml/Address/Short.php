<?php if ($obj->aktiv) { ?>
	<uid><![CDATA[ <?= $obj->uid; ?>]]></uid>
	<email><![CDATA[<?= $obj->uid; ?>@<?= DOMAIN; ?>]]></email>
	
	<name><![CDATA[ <?= $obj->titelpre . ' ' . $obj->vorname . ' ' . $obj->nachname . ' ' . $obj->titelpost; ?><?php if ($obj->bezeichnung != '' && $obj->bezeichnung != $obj->beschreibung) echo ' (' . $obj->bezeichnung . ')'; ?>]]></name>
<?php } ?>
