<style>

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
		width: 90px;
	}

	#addField {
		display: inline;
		width: 400px;
	}

	#addFilter {
		display: inline;
		width: 400px;
	}

	#selectedFilters {
		margin-bottom: 20px;
	}

	#customFilterDescription {
		display: inline;
		width: 400px;
	}

</style>
<script language="Javascript" type="text/javascript">

	$(document).ready(function() {

		$("#removeFilterById").click(function() {
			$.ajax({
				url: "<?php echo base_url('index.ci.php/system/Filters/deleteCustomFilter'); ?>",
				method: "POST",
				data: {
					filter_id: $(this).attr('value')
				}
			})
			.done(function(data, textStatus, jqXHR) {
				alert("Filter successfully removed");
			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			});
		});

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
