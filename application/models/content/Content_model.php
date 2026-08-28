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
	 * Sucht die content_id fuer den CIS4_Root Menu content
	 *
	 * @return integer|null			content_id of the Cis4_Root Menu
	 */
	public function getMenuContentID(){
		// early return if the CIS4_MENU_ENTRY constant is defined  
		if(defined('CIS4_MENU_ENTRY'))
		{
			return CIS4_MENU_ENTRY;
		}
		
		// load the CIS4 Menu content_id from the database using the column 'beschreibugn' of the campus.tbl_content table
		$CIS4_ROOT_CONTENT = $this->loadWhere(["beschreibung"=>"CIS4_ROOT"]);
		
		if(isError($CIS4_ROOT_CONTENT))
		{
			return null;
		}

		$CIS4_ROOT_CONTENT = getData($CIS4_ROOT_CONTENT);
		
		if(count($CIS4_ROOT_CONTENT) > 0)
		{
			return current($CIS4_ROOT_CONTENT)->content_id ?? null;
		}
		else
		{
			return null;
		}
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

		/*,
		{
			"content_id": 1000007,
			"template_kurzbz": "redirect",
			"titel": "Anrechnung",
			"content": "<content><url><![CDATA[' . site_url('/lehre/anrechnung/RequestAnrechnung') . ']]></url><target><![CDATA[]]></target></content>",
			"menu_open": false,
			"aktiv": true,
			"childs": []
		} 
		*/

		/* 
		{
			"content_id": 1000003,
			"template_kurzbz": "redirect",
			"titel": "COVID-19",
			"content": "<content><url><![CDATA[' . site_url('/CisVue/Cms/content/10012') . ']]></url><target><![CDATA[]]></target></content>",
			"menu_open": false,
			"aktiv": true,
			"childs": []
		},
		*/

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
						"content": "<content><url><![CDATA[' . site_url('/CisVue/Cms/news') . ']]></url><target><![CDATA[]]></target></content>",
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
								campus.tbl_contentsprache s1 ON c1.content_id=s1.content_id AND s1.sprache=? AND sichtbar=true
							WHERE
								c1.aktiv = true
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
				campus.tbl_contentchild k ON(m.content_id=k.content_id) and c.aktiv = true
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

	/**
	 * Root contents without a parent in tbl_contentchild. Excludes news.
	 * @return stdClass success with array of rows or error
	 */
	public function getRootContent()
	{
		$query = '
			SELECT *
			FROM (
				SELECT DISTINCT ON (content_id) *
				FROM campus.tbl_content
					LEFT JOIN campus.tbl_contentchild USING (content_id)
				WHERE tbl_content.template_kurzbz <> ?
					AND content_id NOT IN (
						SELECT child_content_id FROM campus.tbl_contentchild
						WHERE child_content_id = tbl_content.content_id)
			) AS a
			ORDER BY sort, content_id
		';

		return $this->execReadOnlyQuery($query, ['news']);
	}

	/**
	 * Recent news from the last two months, max 100 rows.
	 * @return stdClass success with array of rows or error
	 */
	public function getNewsContent()
	{
		$query = "
			SELECT *
			FROM campus.tbl_content
				JOIN campus.tbl_news USING (content_id)
			WHERE tbl_news.datum >= now() - '2 month'::interval
			ORDER BY datum DESC
			LIMIT 100
		";

		return $this->execReadOnlyQuery($query);
	}

	/**
	 * Search contents by content_id or titel. Excludes news. Returns content_ids only.
	 * @param array $searchItems search terms
	 * @return stdClass success with array of rows or error
	 */
	public function searchCms($searchItems)
	{
		if (empty($searchItems))
			return success([]);

		$conditions = [];
		$params = [];
		foreach ($searchItems as $term)
		{
			$conditions[] = '(content_id::text = ? OR lower(titel) LIKE lower(?))';
			$params[] = $term;
			$params[] = '%' . $term . '%';
		}

		$query = '
			SELECT DISTINCT tbl_content.content_id
			FROM campus.tbl_contentsprache
				JOIN campus.tbl_content USING (content_id)
			WHERE tbl_content.template_kurzbz <> ?
				AND (' . implode(' OR ', $conditions) . ')
			ORDER BY content_id
		';

		array_unshift($params, 'news');

		return $this->execReadOnlyQuery($query, $params);
	}

	/**
	 * All contents eligible as children: excludes ancestors, self, and news.
	 * @param int $content_id the content to find children for
	 * @param string $sprache language for the titel subselect
	 * @return stdClass success with array of rows or error
	 */
	public function getPossibleChilds($content_id, $sprache)
	{
		$query = '
			SELECT content_id, oe_kurzbz, template_kurzbz,
				(SELECT titel FROM campus.tbl_contentsprache
				 WHERE sprache = ? AND content_id = tbl_content.content_id
				 ORDER BY version LIMIT 1) AS titel
			FROM campus.tbl_content
			WHERE content_id NOT IN (
					WITH RECURSIVE parents(content_id, child_content_id) AS (
						SELECT content_id, child_content_id FROM campus.tbl_contentchild
						WHERE child_content_id = ?
						UNION ALL
						SELECT cc.content_id, cc.child_content_id
						FROM campus.tbl_contentchild cc, parents
						WHERE cc.child_content_id = parents.content_id)
					SELECT content_id FROM parents GROUP BY content_id)
				AND content_id <> ?
				AND template_kurzbz <> ?
			ORDER BY titel
		';

		return $this->execReadOnlyQuery($query, [$sprache, $content_id, $content_id, 'news']);
	}

	/**
	 * Where a content is referenced. Returns [{table, label}] for the delete dialog.
	 * Tolerates missing addon/testtool schemas.
	 * @param int $content_id
	 * @return stdClass success with flat array or error
	 */
	public function getUsage($content_id)
	{
		$coreQuery = "
			SELECT 'campus.tbl_infoscreen_content' AS \"table\",
				infoscreen_id::text AS label
			FROM campus.tbl_infoscreen_content WHERE content_id = ?
			UNION ALL
			SELECT 'campus.tbl_news', betreff
			FROM campus.tbl_news WHERE content_id = ?
			UNION ALL
			SELECT 'public.tbl_ort', ort_kurzbz
			FROM public.tbl_ort WHERE content_id = ?
			UNION ALL
			SELECT 'public.tbl_service', bezeichnung
			FROM public.tbl_service WHERE content_id = ?
			UNION ALL
			SELECT 'public.tbl_statistik', bezeichnung
			FROM public.tbl_statistik WHERE content_id = ?
		";
		$coreParams = [$content_id, $content_id, $content_id, $content_id, $content_id];

		$coreResult = $this->execReadOnlyQuery($coreQuery, $coreParams);
		$rows = [];

		if (!isError($coreResult) && getData($coreResult))
			$rows = getData($coreResult);

		// Optional schemas: testtool and addon may not exist. A failing query still writes a
		// db error into the response envelope, so check for the table before querying it.
		$optionalQueries = [
			[
				'testtool', 'tbl_ablauf_vorgaben',
				"SELECT 'testtool.tbl_ablauf_vorgaben' AS \"table\",
					ablauf_vorgaben_id::text AS label
				FROM testtool.tbl_ablauf_vorgaben WHERE content_id = ?"
			],
			[
				'addon', 'tbl_software',
				"SELECT 'addon.tbl_software' AS \"table\",
					software_id::text AS label
				FROM addon.tbl_software WHERE content_id = ?"
			]
		];

		foreach ($optionalQueries as $opt)
		{
			if (!$this->tableExists($opt[0], $opt[1]))
				continue;

			$result = $this->execReadOnlyQuery($opt[2], [$content_id]);
			if (!isError($result) && getData($result))
				$rows = array_merge($rows, getData($result));
		}

		return success($rows);
	}

	/**
	 * True if a base table exists. Guards the optional testtool and addon schemas.
	 * @param string $schema
	 * @param string $table
	 * @return bool
	 */
	private function tableExists($schema, $table)
	{
		$query = "
			SELECT 1 AS exists
			FROM information_schema.tables
			WHERE table_catalog = ? AND table_schema = ? AND table_name = ?
		";

		return hasData($this->execReadOnlyQuery($query, [DB_NAME, $schema, $table]));
	}

	/**
	 * Returns oe_kurzbz for a content as a string.
	 * @param int $content_id
	 * @return stdClass success with string or error
	 */
	public function getOeKurzbz($content_id)
	{
		$query = 'SELECT oe_kurzbz FROM campus.tbl_content WHERE content_id = ?';
		$result = $this->execReadOnlyQuery($query, [$content_id]);

		if (isError($result))
			return $result;

		$data = getData($result);
		if (empty($data))
			return error('Content not found');

		return success($data[0]->oe_kurzbz);
	}
}
