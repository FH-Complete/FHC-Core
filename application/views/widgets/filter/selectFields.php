<script language="Javascript" type="text/javascript">

	function dndSF()
	{
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
	}

	function resetEventsSF()
	{
		$("#addField").off('change');
		$(".remove-field").off('click');
	}

	function addEventsSF()
	{
		$("#addField").change(function(event) {

			$.ajax({
				url: "<?php echo base_url('index.ci.php/system/Filters/addSelectedFields'); ?>",
				method: "POST",
				data: {
					fieldName: $(this).val()
				}
			})
			.done(function(data, textStatus, jqXHR) {

				resetSelectedFields();
				renderSelectedFields();

				renderTableDataset();

			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			});

		});

		$(".remove-field").click(function(event) {

			$.ajax({
				url: "<?php echo base_url('index.ci.php/system/Filters/removeSelectedFields'); ?>",
				method: "POST",
				data: {
					fieldName: $(this).attr('fieldToRemove')
				}
			})
			.done(function(data, textStatus, jqXHR) {

				resetSelectedFields();
				renderSelectedFields();

				renderTableDataset();

			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			});

		});
	}

	function renderSelectedFields()
	{
		$.ajax({
			url: "<?php echo base_url('index.ci.php/system/Filters/selectFields'); ?>",
			method: "GET",
			data: {},
		    dataType: "json"
		})
		.done(function(data, textStatus, jqXHR) {

			resetEventsSF();

			if (data != null)
			{
				var arrayFieldsToDisplay = [];

				if (data.columnsAliases != null && $.isArray(data.columnsAliases))
				{
					arrayFieldsToDisplay = data.columnsAliases;
				}
				else if (data.selectedFields != null && $.isArray(data.selectedFields))
				{
					arrayFieldsToDisplay = data.selectedFields;
				}

				for (var i = 0; i < arrayFieldsToDisplay.length; i++)
				{
					var fieldToDisplay = arrayFieldsToDisplay[i];
					var fieldName = data.selectedFields[i];

					var strHtml = '<span class="filter-select-field-dnd-span">';

					strHtml += fieldToDisplay;
					strHtml += '<a class="remove-field" fieldToRemove="' + fieldName + '">X</a>';
					strHtml += '</span>';
					$("#filterSelectFieldsDnd").append(strHtml);
				}

				var strDropDown = '<option value="">Select a field to add...</option>';
				$("#addField").append(strDropDown);

				for (var i = 0; i < data.allSelectedFields.length; i++)
				{
					var fieldName = data.allSelectedFields[i];
					var fieldToDisplay = data.allSelectedFields[i];

					if (data.allColumnsAliases != null && $.isArray(data.allColumnsAliases))
					{
						fieldToDisplay = data.allColumnsAliases[i];
					}

					strDropDown = '<option value="' + fieldName + '">' + fieldToDisplay + '</option>';
					$("#addField").append(strDropDown);
				}
			}

			dndSF();
			addEventsSF();
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});
	}

	function resetSelectedFields()
	{
		$("#filterSelectFieldsDnd").html("");
		$("#addField").html("");
	}

	$(document).ready(function() {
		renderSelectedFields();
	});

</script>

	<div id="filterSelectFieldsDnd" class="filter-select-fields-dnd-div"></div>

	<div>
		<span>
			Add field:
		</span>

		<span>
			<select id="addField"></select>
		</span>
	</div>
