<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NOTE="<?= $url; ?>/rdf#"
>
<?php if($lvs) { ?>
	<RDF:Seq about="<?= $url; ?>/liste">
		<?php foreach($lvs as $row) { ?>
			<?php $freigabedatum = new DateTime($row->freigabedatum); ?>
			<?php $benotungsdatum = new DateTime($row->insertamum); ?>
			<RDF:li>
					<RDF:Description  id="<?= $row->studierendenantrag_lehrveranstaltung_id; ?>/<?= $row->prestudent_id; ?>/<?= $row->studiensemester_kurzbz; ?>"  about="<?= $url; ?>/<?= $row->studierendenantrag_lehrveranstaltung_id; ?>/<?= $row->prestudent_id; ?>/<?= $row->studiensemester_kurzbz; ?>" >
						<NOTE:studierendenantrag_lehrveranstaltung_id><![CDATA[<?= $row->studierendenantrag_lehrveranstaltung_id; ?>]]></NOTE:studierendenantrag_lehrveranstaltung_id>
						<NOTE:lehrveranstaltung_id><![CDATA[<?= $row->lehrveranstaltung_id; ?>]]></NOTE:lehrveranstaltung_id>
						<NOTE:prestudent_id><![CDATA[<?= $row->prestudent_id; ?>]]></NOTE:prestudent_id>
						<NOTE:mitarbeiter_uid><![CDATA[<?= $row->insertvon; ?>]]></NOTE:mitarbeiter_uid>
						<NOTE:studiensemester_kurzbz><![CDATA[<?= $row->studiensemester_kurzbz; ?>]]></NOTE:studiensemester_kurzbz>
						<NOTE:note><![CDATA[<?= $row->note; ?>]]></NOTE:note>
						<NOTE:freigabedatum_iso><![CDATA[<?= $freigabedatum->format('c'); ?>]]></NOTE:freigabedatum_iso>
						<NOTE:freigabedatum><![CDATA[<?= $freigabedatum->format('d.m.Y'); ?>]]></NOTE:freigabedatum>
						<NOTE:benotungsdatum_iso><![CDATA[<?= $benotungsdatum->format('c'); ?>]]></NOTE:benotungsdatum_iso>
						<NOTE:benotungsdatum><![CDATA[<?= $benotungsdatum->format('d.m.Y'); ?>]]></NOTE:benotungsdatum>
						<NOTE:note_bezeichnung><![CDATA[<?= $row->note_bezeichnung; ?>]]></NOTE:note_bezeichnung>
						<NOTE:lehrveranstaltung_bezeichnung><![CDATA[<?= $row->lv_bezeichnung; ?>]]></NOTE:lehrveranstaltung_bezeichnung>
						<NOTE:studiengang><![CDATA[<?= $row->stg_bezeichnung; ?>]]></NOTE:studiengang>
					</RDF:Description>
			</RDF:li>
		<?php } ?>
	</RDF:Seq>
<?php } ?>
</RDF:RDF>
