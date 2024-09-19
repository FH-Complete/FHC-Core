<?php
	$tableWidgetArray = array(
		'query' => '
			SELECT e.extension_id,
				e.name,
				e.description,
				e.server_kurzbz,
				e.version,
				e.license,
				e.url,
				e.core_version,
				e.dependencies,
				e.enabled
			  FROM system.tbl_extensions e
		      ORDER BY e.name ASC,
				e.server_kurzbz ASC,
				e.version ASC
		',
		'tableUniqueId' => 'extensionsListTableWidget',
		'requiredPermissions' => 'system/extensions',
		'datasetRepresentation' => 'tabulator',
		'additionalColumns' => array('Delete'),
		'columnsAliases' => array(
			'Extension ID',
			'Name',
			'Description',
			'Server',
			'Version',
			'License',
			'URL',
			'Core version',
			'Dependencies',
			'Enabled'
		),
		'formatRow' => function ($datasetRaw) {

			if ($datasetRaw->{'description'} == null)
			{
				$datasetRaw->{'description'} = '-';
			}

			if ($datasetRaw->{'server_kurzbz'} == null)
			{
				$datasetRaw->{'server_kurzbz'} = '-';
			}

			if ($datasetRaw->{'url'} == null)
			{
				$datasetRaw->{'url'} = '-';
			}

			if ($datasetRaw->{'license'} == null)
			{
				$datasetRaw->{'license'} = '-';
			}

			return $datasetRaw;
		},
		'datasetRepOptions' => '{
			height: "100%",
			layout: "fitColumns",
			persistentLayout: true,
			persistentSort: true,  
			persistentFilter: true,
			autoResize: false
		}',
		'datasetRepFieldsDefs' => '{
			extension_id: {visible: false},
			url: {
				formatter: "link"
			},
			enabled: {
				aligh: "center",
				headerSort: false,
				editor: true,
				formatter: "tickCross",
				cellEdited: function(cell) {
					if (cell.getValue() != cell.getOldValue()) toggleExtension(cell.getData().extension_id, cell.getValue());
				}
			},
			Delete: {
				headerSort: false,
				formatter: "buttonCross",
				width: 100,
				align: "center",
				cellClick: function(e, cell) {
					deleteExtension(cell.getData().extension_id, cell.getRow());
				}
			}
		}'
	);

	echo $this->widgetlib->widget('TableWidget', $tableWidgetArray);

