<div class="col-lg-8">
	<table id="msgtable" class="table table-bordered table-condensed tablesort-hover tablesort-active">
		<thead>
		<tr>
			<th>Datum</th>
			<th>Sender</th>
			<th>Empf&auml;nger</th>
			<th>Betreff</th>
			<th>Gelesen am</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($messages as $message): ?>
			<tr id="<?php echo $message->message_id.'_'.$message->repersonid ?>" style="cursor: pointer">
				<td><?php echo isset($message->insertamum) ? date_format(date_create($message->insertamum), 'd.m.Y H:i:s') : '' ?></td>
				<td><?php echo $message->sevorname.' '.$message->senachname ?></td>
				<td><?php echo $message->revorname.' '.$message->renachname ?></td>
				<td><?php echo $message->subject ?></td>
				<td><?php echo isset($message->statusamum) ? date_format(date_create($message->statusamum), 'd.m.Y H:i:s') : '' ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<div class="col-lg-4">
	<br>
	<div class="text-center"><label for="msgbody" id="msgsubject"></label></div>
	<div>
		<textarea id="msgbody"></textarea>
	</div>
</div>
<script>
	tinymce.init({
		menubar: false,
		toolbar: false,
		readonly: 1,
		selector: "#msgbody",
		statusbar: false,
		height: 300,
		//callback to avoid conflict with ajax (getting first message body)
		init_instance_callback: "initMsgBody"
	});

	function initMsgBody()
	{
		var tblrows = $("#msgtable tbody tr");

		if (tblrows.length > 0)
		{
			//in the begging last sent message is shown
			var firstelement = tblrows.first();
			var id = firstelement.attr('id');

			getMsgBody(id);
			firstelement.find("td").addClass("tablesort-active");

			//add click event on message table for message preview
			tblrows.click(
				function ()
				{
					$("#msgtable").find("td").removeClass("tablesort-active");
					$(this).find("td").addClass("tablesort-active");
					getMsgBody(this.id);
				}
			);
		}
	}

	//retrieve message data from message and reiver id via AJAX
	function getMsgBody(id)
	{
		var msgid = id.substr(0, id.indexOf('_'));
		var recid = id.substr(id.indexOf('_') + 1);

		$.ajax(
			{
				dataType: "json",
				url: "<?php echo base_url("/index.ci.php/system/Messages/getMessageFromIds/") ?>" + msgid + "/" + recid,
				success: function (data, textStatus, jqXHR)
				{
					$("#msgsubject").text(data[0].subject);
					tinyMCE.get("msgbody").setContent(data[0].body);
				},
				error: function (jqXHR, textStatus, errorThrown)
				{
					alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
				}
			}
		)
	}
</script>