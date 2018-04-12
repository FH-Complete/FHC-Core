<style>

	.filter-options-span {
		display: inline-block;
		width: 130px;
		font-weight: bold;
	}

	.filter-name-title {
		font-family: inherit;
		font-size: 16px;
		/*font-weight: bold;*/
		line-height: 1.1;
		color: black;
	}

	.filters-hidden-panel {
		margin: 0 10px 10px 10px;
	}

	.hidden-control {
		display: none !important;
	}

	.filter-select-fields-dnd-div {
		height: 50px;
	}

	.filter-select-field-dnd-span {
		border: 1px solid black;
		border-radius: 7px;
		margin-left: 3px;
		margin-right: 3px;
		padding: 10px;
		top: 10px;
	}

	.filter-select-field-dnd-span:hover {
		cursor: move;
	}

	.filter-select-field-dnd-span a {
		cursor: pointer;
	}

	.selection-before::before {
		content: "";
		position: absolute;
		top: 0;
		right: 100%;
		height: 100%;
		margin-right: 3px;
		border-left: 2px solid #428bca;
	}

	.selection-after::after {
		content: "";
		position: absolute;
		top: 0;
		left: 100%;
		height: 100%;
		margin-left: 3px;
		border-right: 2px solid #428bca;
	}

	.select-filter-operation {
		display: inline;
		width: 130px;
	}

	.select-filter-operation-value {
		display: inline;
		width: 400px;
	}

	.select-filter-option {
		display: inline;
		width: 100px;
	}

	#addField, #customFilterDescription, #addFilter {
		display: inline;
		width: 535px;
	}

	#addFilter {
		margin-right: 12px;
	}

	#selectedFilters {
		margin-bottom: 20px;
	}

	#applyFilter, #saveCustomFilterButton, .remove-selected-filter {
		font-weight: bold;
	}

	.remove-selected-filter {
		padding-bottom: 3px;
	}

	.panel-title {
		font-weight: bold;
		padding-top: 3px;
	}

	.remove-field {
		font-weight: bold;
	}

</style>
<script language="Javascript" type="text/javascript">

	function refreshSideMenu()
	{
		$.ajax({
			url: "<?php echo base_url('index.ci.php/system/infocenter/InfoCenter/setNavigationMenuArray'); ?>",
			method: "GET",
			data: {}
		})
		.done(function(data, textStatus, jqXHR) {

			renderSideMenu();

		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});
	}

	function sideMenuHook()
	{
		$(".remove-filter").click(function() {

			$.ajax({
				url: "<?php echo base_url('index.ci.php/system/Filters/deleteCustomFilter'); ?>",
				method: "POST",
				data: {
					filter_id: $(this).attr('value')
				}
			})
			.done(function(data, textStatus, jqXHR) {

				refreshSideMenu();

			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			});

		});
	}

	$(document).ready(function() {

		$("[data-toggle='collapse']").click(function() {

			var filterOptionsStatus = sessionStorage.getItem('filter-options-status');

			if (filterOptionsStatus != null && filterOptionsStatus == 'closed')
			{
				sessionStorage.setItem('filter-options-status', 'open');
			}
			else
			{
				sessionStorage.setItem('filter-options-status', 'closed');
			}

		});

		var filterOptionsStatus = sessionStorage.getItem('filter-options-status');
		if (filterOptionsStatus != null && filterOptionsStatus == 'open')
		{
			$('.collapse').collapse("show");
		}

	});
</script>
<div class="row">
	<div class="col-lg-12">

		<?php FilterWidget::displayFilterName(); ?>

		<div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#collapseFilterHeader">Filter options</a>
					</h4>
				</div>
				<div id="collapseFilterHeader" class="panel-collapse collapse">
					<div class="filters-hidden-panel">
						<div>
							<?php FilterWidget::loadViewSelectFields(); ?>
						</div>

						<br>

						<div>
							<?php FilterWidget::loadViewSelectFilters(); ?>
						</div>

						<br>

						<div>
							<?php FilterWidget::loadViewSaveFilter(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<br>

		<div id="datasetActionsTop"></div>

		<div>
			<?php FilterWidget::loadViewTableDataset(); ?>
		</div>

		<div id="datasetActionsBottom"></div>

	</div>
</div>
