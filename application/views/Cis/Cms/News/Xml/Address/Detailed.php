<?php if ($obj->aktiv) { ?>
	<uid><![CDATA[ <?= $obj->uid; ?>]]></uid>
	<name><![CDATA[ <?= $obj->titelpre . ' ' . $obj->vorname . ' ' . $obj->nachname . ' ' . $obj->titelpost; ?>]]></name>
	<email><![CDATA[<?= $obj->alias ?: $obj->uid; ?>@<?= DOMAIN; ?>]]></email>
	<?php if ($obj->telefonklappe !== null) { ?>
		<telefon><![CDATA[<?= $obj->kontakt ?: ''; ?> - <?= $obj->telefonklappe; ?>]]></telefon>
	<?php } ?>
	<?php if ($obj->planbezeichnung) { ?>
		<ort><![CDATA[<?= $obj->planbezeichnung; ?>]]></ort>
	<?php } ?>
<?php } ?>