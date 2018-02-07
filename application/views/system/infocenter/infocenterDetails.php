<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'InfocenterDetails',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'jqueryui' => true,
		'tablesorter' => true,
		'tinymce' => true,
		'sbadmintemplate' => true,
		'customCSSs' => array('skin/admintemplate.css', 'skin/tablesort_bootstrap.css')
	)
);
?>
<body>
<div id="wrapper">
	<?php
	echo $this->widgetlib->widget(
		'NavigationWidget',
		array(
			'navigationHeader' => $navigationHeaderArray,
			'navigationMenu' => $navigationMenuArray
		)
	);
	?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">Infocenter
						Details: <?php echo $stammdaten->vorname.' '.$stammdaten->nachname ?>
					</h3>
				</div>
			</div>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center"><h4>Stammdaten</h4></div>
							<div class="panel-body">
								<div class="row">
									<?php $this->load->view('system/infocenter/stammdaten.php'); ?>
								</div> <!-- ./row -->
							</div> <!-- ./panel-body -->
						</div> <!-- ./panel -->
					</div> <!-- ./main column -->
				</div> <!-- ./main row -->
			</section>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<a name="DokPruef"></a><!-- anchor for jumping to the section -->
							<div class="panel-heading text-center"><h4>Dokumentenpr&uuml;fung</h4></div>
							<div class="panel-body">
								<?php $this->load->view('system/infocenter/dokpruefung.php'); ?>
							</div> <!-- ./panel-body -->
						</div> <!-- ./panel -->
					</div> <!-- ./column -->
				</div> <!-- ./row -->
			</section>
			<section>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center">
								<a name="ZgvPruef"></a>
								<h4>ZGV-Pr&uuml;fung</h4>
							</div>
							<div class="panel-body">
								<?php $this->load->view('system/infocenter/zgvpruefungen.php'); ?><!-- /.panel-group -->
							</div><!-- /.main panel body -->
						</div> <!-- /.main panel-->
					</div> <!-- /.column freigabe-->
				</div> <!-- /.row freigabe-->
			</section>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center">
								<a name="Nachrichten"></a>
								<h4 class="text-center">Nachrichten</h4>
							</div>
							<div class="panel-body">
								<div class="row">
										<?php
										$this->load->view('system/messageList.php', $messages);
										?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<section>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading text-center">
								<a name="NotizAkt"></a>
								<h4 class="text-center">Notizen &amp; Aktivit&auml;ten</h4>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-6">
										<?php $this->load->view('system/infocenter/addNotiz.php'); ?>
										<?php $this->load->view('system/infocenter/notizen.php'); ?>
									</div>
									<div class="col-lg-6">
										<?php $this->load->view('system/infocenter/logs.php'); ?>
									</div> <!-- ./column -->
								</div> <!-- ./row -->
							</div> <!-- ./panel-body -->
						</div> <!-- ./panel -->
					</div> <!-- ./main column -->
				</div> <!-- ./main row -->
			</section>
		</div> <!-- ./container-fluid-->
	</div> <!-- ./page-wrapper-->
</div> <!-- ./wrapper -->

<script>

	$(document).ready(
		function ()
		{
			//javascript bootstrap hack - not nice!
			$("select").addClass('form-control');
			$("table").addClass('table-condensed');

			//initialise table sorter
			addTablesorter("doctable", [[2, 1], [1, 0]], ["zebra"]);
			addTablesorter("nachgdoctable", [[2, 0], [1, 1]], ["zebra"]);
			addTablesorter("msgtable", [[0, 1], [2, 0]], ["zebra", "filter"]);
			addTablesorter("logtable", [[0, 1]], ["filter"]);
			addTablesorter("notiztable", [[0, 1]], ["filter"]);

			//add pager
			togglePager(23, "logtable", "logpager");
			togglePager(10, "notiztable", "notizpager");

			//initialise datepicker
			$.datepicker.setDefaults($.datepicker.regional['de']);
			$(".dateinput").datepicker({
				"dateFormat": "dd.mm.yy"
			});

			//add submit event to message send link
			$("#sendmsglink").click(
				function()
				{
					$("#sendmsgform").submit();
				}
			);

			//add click events to "formal geprüft" checkboxes
			<?php foreach($dokumente as $dokument): ?>

			if ($("#prchkbx<?php echo $dokument->akte_id; ?>"))
			{
				$("#prchkbx<?php echo $dokument->akte_id; ?>").click(function ()
				{
					window.location = "../saveFormalGeprueft?akte_id=<?php echo $dokument->akte_id; ?>&formal_geprueft=" + this.checked + "&person_id=<?php echo $stammdaten->person_id ?>";
				});
			}
			<?php endforeach ?>

			//prevent opening modal when Statusgrund not chosen
			$("#absageModal").on('show.bs.modal', function (e)
				{
					if ($("[name=statusgrund]").val() === "null")
					{
						$("#statusgrselect").addClass("has-error");
						return e.preventDefault();
					}
				}
			);

			$("[name=statusgrund]").change(function ()
				{
					$("#statusgrselect").removeClass("has-error");
				}
			);
		}
	);

	function addTablesorter(tableid, sortList, widgets)
	{
		$("#" + tableid).tablesorter(
			{
				theme: "default",
				dateFormat: "ddmmyyyy",
				sortList: sortList,
				widgets: widgets
			}
		);

		//hide filters if less than 2 datarows (+ 2 for headings and filter row itself)
		if ($("#" + tableid + " tr").length < 4)
		{
			$("#" + tableid + " tr.tablesorter-filter-row").hide();
		}
	}

	function togglePager(size, tableid, pagerid)
	{
		var html =
			'<div id="' + pagerid + '" class="pager"> ' +
			'<form class="form-inline">' +
			'<i class="fa fa-step-backward first"></i>&nbsp;' +
			'<i class="fa fa-backward prev"></i>' +
			'<span class="pagedisplay"></span>' +
			'<i class="fa fa-forward next"></i>&nbsp;' +
			'<i class="fa fa-step-forward last"></i>' +
			'</form>' +
			'</div>';

		var rowcount = $("#" + tableid + " tr").length;

		//not show pager if on first table page
		if (rowcount > size)
		{
			var table = $("#" + tableid);
			table.after(html);

			table.tablesorterPager(
				{
					container: $("#" + pagerid),
					size: size,
					cssDisabled: 'disabled',
					savePages: false,
					output: '{startRow} – {endRow} / {totalRows} Zeilen'
				}
			);
		}
	}
</script>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
