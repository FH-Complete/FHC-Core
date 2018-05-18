/**
 * Used by the NavigationWidget
 */

var fhc_controller_id = FHC_Ajax_Client.getUrlParameter('fhc_controller_id');

/**
 *
 */
function printNavItem(item, depth = 1)
{
	strMenu = "";
	var expanded = typeof item['expand'] != 'undefined' && item['expand'] === true ? ' active' : '';

	strMenu += '<li class="' + expanded + '">';

	if (typeof item['subscriptLinkClass'] != 'undefined' && typeof item['subscriptDescription'] != 'undefined')
	{
		strMenu += '<span>';
	}

	// Handle fhc_controller_id
	if (fhc_controller_id != null && fhc_controller_id != '' && item['link'] != '#')
	{
		if (item['link'].indexOf('?') != -1)
		{
			item['link'] += '&';
		}
		else
		{
			item['link'] += '?';
		}

		item['link'] += 'fhc_controller_id=' + fhc_controller_id;
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
		url: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/" + 'system/Navigation/menu',
		method: "GET",
		data: {
			navigation_widget_called: FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method
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
		// alert(textStatus);
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

function renderHeaderMenu()
{
	$.ajax({
		url: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/" + 'system/Navigation/header',
		method: "GET",
		data: {
			navigation_widget_called: FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method
		}
	})
	.done(function(data, textStatus, jqXHR) {

		if (data != null)
		{
			jQuery.each(data, function(i, e) {
				$(".menu-header-items").append('<a class="navbar-brand" href="' + e + '">' + i + '</a>');
			});
		}

	}).fail(function(jqXHR, textStatus, errorThrown) {
		// alert(textStatus);
	});
}

$(document).ready(function() {

	renderHeaderMenu();

	renderSideMenu();

});
