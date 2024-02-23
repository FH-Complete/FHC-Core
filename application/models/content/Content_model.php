<?php
class Content_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_content';
		$this->pk = 'content_id';
	}

	/**
	 * Laedt den Content in der angegebenen Sprache
	 * Sollte der Content in dieser Sprache nicht vorhanden sein, wird der Content in der Default Sprache geladen
	 *
	 * @param integer			$content_id
	 * @param string			$sprache optional
	 * @param integer			$version optional
	 * @param boolean | null	$sichtbar optional
	 *
	 * @return stdClass
	 */
	public function getContent($content_id, $sprache = DEFAULT_LANGUAGE, $version = null, $sichtbar = null, $load_default_language = false)
	{
		$this->load->model('content/Contentsprache_model', 'ContentspracheModel');
		$spracheExists = $this->ContentspracheModel->exists($content_id, $sprache, $version, $sichtbar);
		if (isError($spracheExists))
			return $spracheExists;

		if(!getData($spracheExists))
		{
			if($load_default_language)
				$sprache = DEFAULT_LANGUAGE;
			else
				return error('Der Content existiert in dieser Sprache nicht ');
		}

		$condition = ['content_id' => $content_id, 'sprache' => $sprache];

		if ($sichtbar === true || $sichtbar === false)
			$condition['sichtbar'] = $sichtbar;
		if ($version)
			$condition['version'] = $version;

		$this->addSelect([
			'*',
			'tbl_contentsprache.insertamum',
			'tbl_contentsprache.insertvon',
			'tbl_contentsprache.updateamum',
			'tbl_contentsprache.updatevon'
		]);
		$this->addJoin('campus.tbl_contentsprache', 'content_id');
		$this->addOrder('version', 'DESC');
		$this->addLimit(1);

		$result = $this->loadWhere($condition);

		if (isError($result))
			return $result;
		if (!getData($result))
			return error('Dieser Eintrag wurde nicht gefunden');

		return success(current(getData($result)));
	}

	/**
	 * Laedt alle Content Eintraege unterhalb eines Contents
	 * (Ohne Newseintraege)
	 *
	 * @param integer			$root_content_id
	 * @param string			$uid
	 * @param string			$sprache optional
	 *
	 * @return stdClass			on success an array with menu objects
	 */
	public function getMenu($root_content_id, $uid, $sprache = DEFAULT_LANGUAGE)
	{
		if ($root_content_id === null) {
			$res = json_decode('{
				"content_id": 1000000,
				"template_kurzbz": "contentmittitel",
				"titel": "CIS4",
				"content": "<content></content>",
				"menu_open": true,
				"aktiv": true,
				"childs": [
					{
						"content_id": 1000001,
						"template_kurzbz": "redirect",
						"titel": "News",
						"content": "<content><url><![CDATA[' . site_url('/CisHtml/Cms/news') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					},
					{
						"content_id": 1000002,
						"template_kurzbz": "redirect",
						"titel": "Profil",
						"content": "<content><url><![CDATA[' . site_url('/Cis/Profil') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					},
					{
						"content_id": 1000003,
						"template_kurzbz": "redirect",
						"titel": "COVID-19",
						"content": "<content><url><![CDATA[' . site_url('/CisHtml/Cms/content/10012') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					},
					{
						"content_id": 1000004,
						"template_kurzbz": "redirect",
						"titel": "Meine LV",
						"content": "<content><url><![CDATA[' . site_url('/Cis/MyLv') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					},
					{
						"content_id": 1000005,
						"template_kurzbz": "redirect",
						"titel": "Stundenplan",
						"content": "<content><url><![CDATA[' . site_url('/Cis/Stundenplan') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					},
					{
						"content_id": 1000006,
						"template_kurzbz": "redirect",
						"titel": "Dokumente",
						"content": "<content><url><![CDATA[' . site_url('/Cis/Documents') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					},
					{
						"content_id": 1000007,
						"template_kurzbz": "redirect",
						"titel": "Studierendenstatus",
						"content": "<content><url><![CDATA[' . site_url('/lehre/Studierendenantrag') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					},
					{
						"content_id": 1000007,
						"template_kurzbz": "redirect",
						"titel": "Anrechnung",
						"content": "<content><url><![CDATA[' . site_url('/lehre/anrechnung/RequestAnrechnung') . ']]></url><target><![CDATA[]]></target></content>",
						"menu_open": false,
						"aktiv": true,
						"childs": []
					}
				]
			}');
			return success($res);
		}
		$sql = "
		SELECT 
			c.content_id, 
			c.template_kurzbz, 
			s.titel,
			s.content, 
			c.menu_open, 
			c.aktiv, 
			k.child_content_id,
			k.sort FROM (
				SELECT 
					c.content_id, 
					s.contentsprache_id
				FROM 
					campus.tbl_content c
				JOIN (
					SELECT
						s5.content_id,
						s5.contentsprache_id
					FROM (
						SELECT
							content_id,
							sprache,
							MAX(version) AS version
						FROM (
							SELECT
								c1.content_id,
								COALESCE(s1.sprache, ?) AS sprache
							FROM
								campus.tbl_content c1
							LEFT JOIN
								campus.tbl_contentsprache s1 ON c1.content_id=s1.content_id AND s1.sprache=?
							WHERE
								sichtbar=true
						) s2
						LEFT JOIN
							campus.tbl_contentsprache s3 USING(content_id, sprache)
						WHERE
							sichtbar=true
						GROUP BY
							content_id,
							sprache
					) s4
					LEFT JOIN
						campus.tbl_contentsprache s5 USING(content_id, sprache, version)
					WHERE
						version IS NOT NULL
				) t USING (content_id)
				JOIN 
					campus.tbl_contentsprache s USING (contentsprache_id) 
				WHERE
					c.template_kurzbz<>'news'
				AND
					c.content_id IN (
						WITH RECURSIVE childs(content_id, child_content_id) as 
						(
							SELECT content_id, child_content_id FROM campus.tbl_contentchild 
							WHERE content_id=?
							UNION ALL
							SELECT cc.child_content_id, null FROM campus.tbl_contentchild cc, childs
							WHERE cc.content_id=childs.content_id
						)
						SELECT content_id
						FROM childs
						GROUP BY content_id
					)
				GROUP BY c.content_id, 
					s.contentsprache_id
			) m
			JOIN 
				campus.tbl_content c USING(content_id)
			JOIN 
				campus.tbl_contentsprache s USING(contentsprache_id)
			LEFT JOIN 
				campus.tbl_contentchild k ON(m.content_id=k.content_id)
			WHERE EXISTS (
				SELECT 1 
				FROM campus.tbl_contentgruppe 
				JOIN public.vw_gruppen USING(gruppe_kurzbz) 
				WHERE (
					tbl_contentgruppe.content_id=c.content_id
					OR NOT EXISTS (
						SELECT 1 
						FROM campus.tbl_contentgruppe 
						WHERE content_id=c.content_id
					)
				)
				AND vw_gruppen.uid=?
			)
			ORDER BY content_id, sort";

		$result = $this->execQuery($sql, [DEFAULT_LANGUAGE, $sprache, $root_content_id, $uid]);

		if (isError($result))
			return $result;

		$contents = getData($result) ?? [];
		$result = [];
		foreach ($contents as $content) {
			if (!isset($result[$content->content_id])) {
				$result[$content->content_id] = clone($content);
				unset($result[$content->content_id]->child_content_id);
				unset($result[$content->content_id]->sort);
				$result[$content->content_id]->childs = [];
			}
			if ($content->child_content_id !== null)
				$result[$content->content_id]->childs[] = $content->child_content_id;
		}
		foreach ($result as $content) {
			foreach ($content->childs as $k => $v) {
				if (isset($result[$v])) {
					$content->childs[$k] = $result[$v];
				} else {
					unset($content->childs[$k]);
				}
			}
		}

		return success(isset($result[$root_content_id]) ? $result[$root_content_id] : null);
	}
}
