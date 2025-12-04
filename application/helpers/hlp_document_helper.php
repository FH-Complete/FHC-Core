<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Helper function to generate the default documentslist config for the
 * grades tab.
 *
 * The resulting array consists of elements which are associative arrays
 * that can have the following entries:
 * title			(required) on the first level this can be HTML code.
 * permissioncheck	(optional) an URL to an FHCAPI endpoint which returns
 * 						true or false.
 * link				(optional) an URL that will be called if "action" and
 * 						"children" are not defined.
 * action			(optional) an associative array that describes an
 * 						POST action that will be called if "children" is
 * 						not defined.
 * 						It can have the following entries:
 * - url			(required) an URL to an FHCAPI endpoint.
 * - post			(optional) an associative array with the POST data to
 * 						be sent.
 * - response		(optional) a string that will be displayed on success.
 * children			(optional) an array of child elements
 *
 * All strings that start with { and end with } in the URLs and the
 * actions post parameter will be replaced with the corresponding
 * attribute of the current dataset (e.G: {uid} will be replaced with the
 * uid of the current dataset)
 *
 * @return array
 */
function gradesDocumentsList()
{
	$ci =& get_instance();
	$ci->load->library('PhrasesLib', array('stv'), 'p');

	$permissioncheck = site_url("api/frontend/v1/documents/permissionAlternativeFormat/{studiengang_kz}");

	$title_ger = $ci->p->t("global", "deutsch");
	$title_eng = $ci->p->t("global", "englisch");
	$title_ff = $ci->p->t("stv", "document_certificate");
	$title_lv = $ci->p->t("stv", "document_coursecertificate");

	$link_ff = "documents/export/" .
		"zertifikat.rdf.php/" .
		"Zertifikat" .
		"?stg_kz={studiengang_kz_lv}" .
		"&uid={uid}" .
		"&ss={studiensemester_kurzbz}" .
		"&lvid={lehrveranstaltung_id}";
	$link_lv_ger = "documents/export/" .
		"lehrveranstaltungszeugnis.rdf.php/" .
		"LVZeugnis" .
		"?stg_kz={studiengang_kz}" .
		"&uid={uid}" .
		"&ss={studiensemester_kurzbz}" .
		"&lvid={lehrveranstaltung_id}";
	$link_lv_eng = "documents/export/" .
		"lehrveranstaltungszeugnis.rdf.php/" .
		"LVZeugnisEng" .
		"?stg_kz={studiengang_kz}" .
		"&uid={uid}" .
		"&ss={studiensemester_kurzbz}" .
		"&lvid={lehrveranstaltung_id}";

	$archive_url = "api/frontend/v1/documents/archiveSigned";
	$archive_response = $ci->p->t("stv", "document_signed_and_archived");
	$archive_post_ff = [
		"xml" => "zertifikat.rdf.php",
		"xsl" => "Zertifikat",
		"stg_kz" => "{studiengang_kz_lv}",
		"uid" => "{uid}",
		"ss" => "{studiensemester_kurzbz}",
		"lvid" => "{lehrveranstaltung_id}"
	];
	$archive_post_lv_ger = [
		"xml" => "lehrveranstaltungszeugnis.rdf.php",
		"xsl" => "LVZeugnis",
		"stg_kz" => "{studiengang_kz}",
		"uid" => "{uid}",
		"ss" => "{studiensemester_kurzbz}",
		"lvid" => "{lehrveranstaltung_id}"
	];
	$archive_post_lv_eng = [
		"xml" => "lehrveranstaltungszeugnis.rdf.php",
		"xsl" => "LVZeugnisEng",
		"stg_kz" => "{studiengang_kz}",
		"uid" => "{uid}",
		"ss" => "{studiensemester_kurzbz}",
		"lvid" => "{lehrveranstaltung_id}"
	];

	$list = [
		[
			'title' => '<i class="fa fa-download" title="' . $ci->p->t("stv", "document_download") . '"></i>',
			'children' => [
				[
					'title' => $title_ff,
					'link' => site_url($link_ff)
				],
				[
					'title' => $title_lv,
					'children' => [
						[
							'title' => $title_ger,
							'link' => site_url($link_lv_ger),
							'children' => [
								[
									'title' => 'PDF',
									'permissioncheck' => $permissioncheck,
									'link' => site_url($link_lv_ger)
								],
								[
									'title' => 'DOC',
									'permissioncheck' => $permissioncheck,
									'link' => site_url($link_lv_ger . "&output=doc")
								],
								[
									'title' => 'ODT',
									'permissioncheck' => $permissioncheck,
									'link' => site_url($link_lv_ger . "&output=odt")
								]
							]
						],
						[
							'title' => $title_eng,
							'link' => site_url($link_lv_eng),
							'children' => [
								[
									'title' => 'PDF',
									'permissioncheck' => $permissioncheck,
									'link' => site_url($link_lv_eng)
								],
								[
									'title' => 'DOC',
									'permissioncheck' => $permissioncheck,
									'link' => site_url($link_lv_eng . "&output=doc")
								],
								[
									'title' => 'ODT',
									'permissioncheck' => $permissioncheck,
									'link' => site_url($link_lv_eng . "&output=odt")
								]
							]
						]
					]
				]
			]
		],
		[
			'title' => '<i class="fas fa-archive" title="' . $ci->p->t("stv", "document_archive") . '"></i>',
			'children' => [
				[
					'title' => $title_ff,
					'action' => [
						'url' => site_url($archive_url),
						'post' => $archive_post_ff,
						'response' => $archive_response
					]
				],
				[
					'title' => $title_lv,
					'children' => [
						[
							'title' => $title_ger,
							'action' => [
								'url' => site_url($archive_url),
								'post' => $archive_post_lv_ger,
								'response' => $archive_response
							]
						],
						[
							'title' => $title_eng,
							'action' => [
								'url' => site_url($archive_url),
								'post' => $archive_post_lv_eng,
								'response' => $archive_response
							]
						]
					]
				]
			]
		]
	];

	return $list;
}
