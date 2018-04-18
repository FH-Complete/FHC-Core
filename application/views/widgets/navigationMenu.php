<style>
	#collapseinicon {
		display: none;
		cursor: pointer;
		color: #337ab7;
		border-bottom: 1px solid #e7e7e7;
		border-right: 1px solid #e7e7e7;
		border-left: 1px solid #e7e7e7;
		position: absolute;
		width: 45px;
		height: 20px;
		background-color: #F8F8F8;
	}

	.nav > li > span > a:focus, .nav > li > span > a:hover {
		text-decoration: none;
	}

	.nav > li > span {
		position: relative;
		display: inline-block;
		padding-top: 15px;
		padding-bottom: 15px;
	}

	.menuSubscriptLink {
		font-size: 10px;
		padding-left: 0px !important;
		padding-right: 0px !important;
	}

	.sidebar ul li span a.active {
		background-color: transparent;
		font-weight: bold;
		text-decoration: underline;
	}

</style>

<script language="Javascript" type="text/javascript">

	function printNavItem(item, depth = 1)
	{
		strMenu = "";
		var expanded = typeof item['expand'] != 'undefined' && item['expand'] === true ? ' active' : '';

		strMenu += '<li class="' + expanded + '">';

		if (typeof item['subscriptLinkClass'] != 'undefined' && typeof item['subscriptDescription'] != 'undefined')
		{
			strMenu += '<span>';
		}

		strMenu += '<a href="' + item['link'] + '"' + expanded + '>';

		if (item['icon'] != 'undefined')
		{
			strMenu += '<i class="fa fa-' + item['icon'] + ' fa-fw"></i> ';
		}

		strMenu += item['description'];

		if (typeof item['children'] != 'undefined' && Object.keys(item['children']).length > 0)
		{
			strMenu += '<span class="fa arrow"></span>';
		}

		strMenu += '</a>';

		if (typeof item['subscriptLinkClass'] != 'undefined' && typeof item['subscriptDescription'] != 'undefined')
		{
			strMenu += '<a class="' + item['subscriptLinkClass'] + ' menuSubscriptLink" value="' + item['subscriptLinkValue'] + '" href="#"> (' + item['subscriptDescription'] + ')</a>';
			strMenu += '</span>';
		}

		if (typeof item['children'] != 'undefined' && Object.keys(item['children']).length > 0)
		{
			var level = '';
			if (depth === 1)
			{
				level = 'second';
			}
			else if (depth > 1)
			{
				level = 'third';
			}

			strMenu += '<ul class="nav nav-' + level + '-level" ' + expanded + '>';

			jQuery.each(item['children'], function(i, e) {
				strMenu += printNavItem(e, ++depth);
			});

			strMenu += '</ul>';
		}

		strMenu += '</li>';

		return strMenu;
	}

	function renderSideMenu()
	{
		$.ajax({
			url: "<?php echo site_url('system/Navigation/menu'); ?>",
			method: "GET",
			data: {
				navigation_widget_called: "<?php echo $this->router->directory.$this->router->class.'/'.$this->router->method; ?>"
			}
		})
		.done(function(data, textStatus, jqXHR) {

			if (data != null)
			{
				var strMenu = '';

				printCollapseIcon();

				jQuery.each(data, function(i, e) {
					strMenu += printNavItem(e);
				});

				$("#side-menu").html(strMenu);
				$("#side-menu").metisMenu();
			}

			if (typeof sideMenuHook == 'function')
			{
				sideMenuHook();
			}

		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});
	}


	function printCollapseIcon()
	{
		// Hiding/showing navigation menu - works only with sb admin 2 template!!
		if(!$("#collapseicon").length)
			$("#side-menu").parent().append('<div id="collapseicon" title="hide Menu" class="text-right" style="cursor: pointer; color: #337ab7"><i class="fa fa-angle-double-left fa-fw"></i></div>');

		$("#collapseicon").click(function() {
			$("#page-wrapper").css('margin-left', '0px');
			$("#side-menu").hide();
			$("#collapseicon").hide();
			$("#collapseinicon").show();
		});

		$("#collapseinicon").click(function() {
			$("#page-wrapper").css('margin-left', '250px');
			$("#side-menu").show();
			$("#collapseicon").show();
			$("#collapseinicon").hide();
		});
	}

	$(document).ready(function() {

		renderSideMenu();

	});

</script>

<div class="navbar-default sidebar" role="navigation">
	<div class="sidebar-nav navbar-collapse">
		<ul class="nav" id="side-menu"></ul>
	</div>
	<i id="collapseinicon" title="show Menu" class="fa fa-angle-double-right fa-fw"></i>
</div>
