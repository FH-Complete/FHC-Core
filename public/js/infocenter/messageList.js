/**
 * javascript file for displaying MessageList
 */

var MessageList = {

	initMessageList: function()
	{
		tinymce.remove();
		tinymce.init({
			menubar: false,
			toolbar: false,
			readonly: 1,
			selector: "#msgbody",
			statusbar: false,
			plugins: "autoresize",
			autoresize_bottom_margin: 10,
			max_height:495,
			autoresize_min_height: 140,
			autoresize_max_height: 495,
			//callback to avoid conflict with ajax (for getting body of first message)
			init_instance_callback: "MessageList._initMsgBody",
			responsive: true

		});
	},
	_initMsgBody: function()
	{
		var tblrows = $("#msgtable tbody tr");

		if (tblrows.length > 0)
		{
			//in the begging last sent message is shown
			var firstelement = tblrows.first();
			var id = firstelement.attr('id');

			MessageList._getMsgBody(id);
			firstelement.find("td").addClass("tablesort-active");

			//add click event on message table for message preview
			tblrows.click(
				function ()
				{
					$("#msgtable").find("td").removeClass("tablesort-active");
					$(this).find("td").addClass("tablesort-active");
					MessageList._getMsgBody(this.id);
				}
			);
		}
	},
	//retrieve message data from message and receiver id via AJAX
	_getMsgBody: function(id)
	{
		var msgid = id.substr(0, id.indexOf('_'));
		var recid = id.substr(id.indexOf('_') + 1);

		FHC_AjaxClient.ajaxCallGet(
			'system/messages/Messages/getMessageFromIds',
			{
				"msg_id": msgid,
				"receiver_id": recid
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					$("#msgsubject").text(data[0].subject);
					tinyMCE.get("msgbody").setContent(data[0].body);
				},
				veilTimeout: 0
			}
		);
	}

};
