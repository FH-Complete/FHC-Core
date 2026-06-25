<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

//Add column entwicklungs_id to bis.tbl_entwicklungsteam
if(!@$db->db_query("SELECT taglib FROM public.tbl_notiz_typ LIMIT 1"))
{
	$qry = 'ALTER TABLE public.tbl_notiz_typ ADD COLUMN taglib character varying(32);';

	if(!$db->db_query($qry))
		echo '<strong> public.tbl_notiz_typ '.$db->db_last_error().'</strong><br>';
	else
		echo '<br>public.tbl_notiz_typ: Neue Spalte taglib hinzugefügt';
}

//automatic tags anlegen
$tags = [
	[
		'typ_kurzbz' => 'wh_auto',
		'bezeichnung' => '{WH, R}',
		'style' => 'tag_dark_grey',
		'prioritaet' => 11,
		'taglib' => 'tags/CoreWiederholerTagLib'
	],
	[
		'typ_kurzbz' => 'dd_auto',
		'bezeichnung' => '{DD, DD}',
		'style' => 'tag_braun',
		'prioritaet' => 10,
		'taglib' => 'tags/CoreDoubleDegreeTagLib'
	],
	[
		'typ_kurzbz' => 'out_auto',
		'bezeichnung' => '{Out, Out}',
		'style' => 'tag_limette',
		'prioritaet' => 12,
		'taglib' => 'tags/CoreOutgoingTagLib'
	],
	[
		'typ_kurzbz' => 'prewh_auto',
		'bezeichnung' => '{Pre-WH, pre-R}',
		'style' => 'tag_light_grey',
		'prioritaet' => 13,
		'taglib' => 'tags/CorePrewiederholerTagLib'
	],
	[
		'typ_kurzbz' => 'zgv_auto',
		'bezeichnung' => '{ZGV offen, ZGV missing}',
		'style' => 'tag_beige',
		'prioritaet' => 14,
		'taglib' => 'tags/CoreMissingZgvTagLib'
	],
	[
		'typ_kurzbz' => 'unterbrecher_auto',
		'bezeichnung' => '{UB, I}',
		'style' => 'tag_turquoise',
		'prioritaet' => 15,
		'taglib' => 'tags/CoreUnterbrecherTagLib'
	],
	[
		'typ_kurzbz' => 'stbtr_erh_auto',
		'bezeichnung' => '{erh.Studienbeitrag, Incr. Tuition Fees}',
		'style' => 'tag_bordeaux',
		'prioritaet' => 16,
		'taglib' => 'tags/CoreStbErhoehtTagLib'
	],
	[
		'typ_kurzbz' => 'jgv_auto',
		'bezeichnung' => '{JGV, YGR}',
		'style' => 'tag_olive',
		'prioritaet' => 17,
		'taglib' => 'tags/CoreJgvTagLib'
	],

];

foreach ($tags as $tag) {

	$checkQry = "
        SELECT 1
        FROM public.tbl_notiz_typ
        WHERE typ_kurzbz = '".$tag['typ_kurzbz']."'
    ";

	if ($result = $db->db_query($checkQry)) {

		if ($db->db_num_rows($result) == 0) {

			$qry = "
                INSERT INTO public.tbl_notiz_typ
                (
                    typ_kurzbz,
                    bezeichnung_mehrsprachig,
                    automatisiert,
                    aktiv,
                    tag,
                    style,
                    vorrueckung,
                    prioritaet,
                    taglib
                )
                VALUES
                (
                    '".$tag['typ_kurzbz']."',
                    '".$tag['bezeichnung']."',
                    true,
                    true,
                    true,
                    '".$tag['style']."',
                    false,
                    '".$tag['prioritaet']."',
                    '".$tag['taglib']."'
                )
            ";

			if (!$db->db_query($qry))
			{
				echo '<strong>public.tbl_notiz_typ: '. $tag['typ_kurzbz']. ' '. $db->db_last_error().'</strong><br>';
			}
			else
			{
				echo '<br>public.tbl_notiz_typ: Automatic Tag '.$tag['typ_kurzbz'].' hinzugefuegt';
			}
		}
	}
}
