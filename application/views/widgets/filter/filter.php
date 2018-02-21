<style>

	.filter-name-title {
		font-family: inherit;
		font-size: 20px;
		font-weight: bold;
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

</style>
<script language="Javascript" type="text/javascript">

	$(document).ready(function() {

		$(".filter-select-field-dnd-span").draggable({
			containment: "parent",
			cursor: "move",
			opacity: 0.4,
			revert: "invalid",
			revertDuration: 200
		});

		$(".filter-select-field-dnd-span").droppable({
			accept: ".filter-select-field-dnd-span",
			over: function(event, ui) {
				$(this).on("mousemove", function( event ) {
					var padding = 20;
					var elementCenter = $(this).offset().left + (padding + $(this).width() / 2);

					console.log(elementCenter);
					console.log(event.pageX);

					if (event.pageX > elementCenter)
					{
						$(this).addClass("selection-after");
						$(this).removeClass("selection-before");
					}
					else if (event.pageX < elementCenter)
					{
						$(this).addClass("selection-before");
						$(this).removeClass("selection-after");
					}
				});
			},
			out: function(event, ui) {
				$(this).off("mousemove");
				$(this).removeClass("selection-before");
				$(this).removeClass("selection-after");
			},
			drop: function(event, ui) {
				var padding = 20;
				var elementCenter = $(this).offset().left + (padding + $(this).width() / 2);

				if (event.pageX > elementCenter)
				{
					$(this).insertBefore(ui.draggable);
				}
				else if (event.pageX < elementCenter)
				{
					$(this).insertAfter(ui.draggable);
				}

				$(this).off("mousemove");
				$(this).removeClass("selection-before");
				$(this).removeClass("selection-after");
			}
		});

		$("#addField").change(function() {
			$("#filterForm").submit();
		});

		$(".remove-field").each(function() {
			$(this).click(function() {
				$("#rmField").val($(this).attr('fieldToRemove'));
				$("#filterForm").submit();
			});
		});

		$("#addFilter").change(function() {
			$("#filterForm").submit();
		});

		$(".remove-filter").each(function() {
			$(this).click(function() {
				$("#rmFilter").val($(this).attr('filterToRemove'));
				$("#filterForm").submit();
			});
		});

		$(".select-filter-operation").change(function() {
			$("#filterForm").submit();
		});

		$(".select-filter-operation-value").keydown(function(event) {
			if (event.which == 13)
			{
				$("#filterForm").submit();
			}
		});

		$("#saveCustomFilterButton").click(function() {
			$("#saveCustomFilter").val(true);
			$("#filterForm").submit();
		});

		$("#applyFilter").click(function() {
			$("#filterForm").submit();
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
		<form class="form-inline" id="filterForm" method="POST" action="<?php echo current_url(); ?>">

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
								<?php FilterWidget::loadViewSelectFields($listFields); ?>
							</div>

							<br>

							<div>
								<?php FilterWidget::loadViewSelectFilters($metaData); ?>
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
				<?php FilterWidget::loadViewTableDataset($dataset); ?>
			</div>

			<div id="datasetActionsBottom"></div>

		</form>
	</div>
</div>
