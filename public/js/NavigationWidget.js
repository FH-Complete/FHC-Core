/**
 * FH-Complete
 *
 * @package
 * @author
 * @copyright   Copyright (c) 2016 fhcomplete.org
 * @license GPLv3
 * @link    https://fhcomplete.org
 * @since	Version 1.0.0
 */

/**
 * FHC_NavigationWidget
 */
var FHC_NavigationWidget = {
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Renders the header menu (top horizontal menu)
	 */
	renderHeaderMenu: function() {

		// Retrives the header menu array
		FHC_AjaxClient.ajaxCallGet(
			'system/Navigation/header',
			{
				navigation_page: FHC_NavigationWidget._getNavigationWidgetCalled()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.hasData(data))
					{
						var strHeaderMenu = "";

						jQuery.each(FHC_AjaxClient.getData(data), function(i, e) {
							if (e != null) strHeaderMenu += FHC_NavigationWidget._buildHeaderMenuStructure(e);
						});

						$("#header-menu").html(strHeaderMenu);
					}
				}
			}
		);
	},

	/**
	 * Renders the side left menu
	 */
	renderSideMenu: function() {

		// Retrives the left menu array
		FHC_AjaxClient.ajaxCallGet(
			'system/Navigation/menu',
			{
				navigation_page: FHC_NavigationWidget._getNavigationWidgetCalled()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {

					if (FHC_AjaxClient.hasData(data))
					{
						FHC_NavigationWidget._printCollapseIcon(); // Applies bootstrap SB Admin 2 theme elements to the left menu

						var strLeftMenu = "";

						// Builds left menu
						jQuery.each(FHC_AjaxClient.getData(data), function(i, e) {
							if (e != null) strLeftMenu += FHC_NavigationWidget._buildLeftMenuStructure(e);
						});

						$("#side-menu").html(strLeftMenu); // render left menu
						$("#side-menu").metisMenu(); // call the Bootstrap SB Admin 2 theme renderer
					}

					// If this global function is present...
					if (typeof sideMenuHook == 'function')
					{
						sideMenuHook(); // ...then call it
					}
				}
			}
		);
	},

	/**
	 * Calls URL to retrive a refreshed menu array
	 */
	refreshSideMenuHook: function(url) {

		FHC_AjaxClient.ajaxCallGet(
			url,
			{
				navigation_page: FHC_NavigationWidget._getNavigationWidgetCalled()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					FHC_NavigationWidget.renderSideMenu();
				},
				errorCallback: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				}
			}
		);
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Applies bootstrap SB Admin 2 theme elements to the left menu
	 */
	_printCollapseIcon: function() {
		// Hiding/showing navigation menu - works only with sb admin 2 template!!
		if(!$("#collapseicon").length)
			$("#side-menu").parent().append(
				'<div id="collapseicon" title="hide Menu" class="text-right" style="cursor: pointer; color: #337ab7">' +
				' <i class="fa fa-angle-double-left fa-fw"></i>' +
				'</div>'
			);

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
	},

	/**
	 * Recursively builds the header menu structure
	 */
	_buildHeaderMenuStructure: function(item) {

		var strHeaderMenu = "";

		var icon = "";
		if (item["icon"] != null && item["icon"] != "")
		{
			icon = '<i class="navbar-brand-icon fa fa-' + item["icon"] + ' fa-fw"></i>';
		}

		var target = "";
		if (item["target"] != null) target = item["target"];

		strHeaderMenu += '<span class="dropdown">';

		if (item["children"] != null && Object.keys(item["children"]).length > 0)
		{
			strHeaderMenu += '<a class="dropdown-toggle header-menu-link-entry" data-toggle="dropdown" href="#">';
			strHeaderMenu += icon;
			strHeaderMenu += item["description"] + " ";
			strHeaderMenu += '<i class="fa fa-caret-down"></i>';
			strHeaderMenu += '</a>';

			strHeaderMenu += '<ul class="dropdown-menu">';

			jQuery.each(item["children"], function(i, e) {
				if (e != null)
				{
					var eTarget = "";
					if (e["target"] != null) eTarget = e["target"];

					var eIcon = "";
					if (e["icon"] != null && e["icon"] != "") eIcon += '<i class="fa fa-' + e["icon"] + ' fa-fw"></i>';

					strHeaderMenu += '<li>';
					strHeaderMenu += '<a href="' + e["link"] + '" target="' + eTarget + '">';
					strHeaderMenu += eIcon;
					strHeaderMenu += e["description"];
					strHeaderMenu += '</a>';
					strHeaderMenu += '</li>';
				}
			});

			strHeaderMenu += '</ul>';
		}
		else
		{
			strHeaderMenu += '<a class="header-menu-link-entry" href="' + item["link"] + '" target="' + target + '">' +
				icon +
				item["description"] +
			'</a>';
		}

		strHeaderMenu += '</span>';

		return strHeaderMenu;
	},

	/**
	 * Recursively builds the left menu structure
	 */
	_buildLeftMenuStructure: function(item, depth = 1) {

		strLeftMenu = "";
		var expanded = item["expand"] != null && item["expand"] === true ? ' active' : "";

		strLeftMenu += '<li class="' + expanded + '">';

		if (item["subscriptLinkClass"] != null && item["subscriptDescription"] != null)
		{
			strLeftMenu += '<span>';
		}

		var target = "";
		if (item["target"] != null) target = item["target"];

		strLeftMenu += '<a href="' + item["link"] + '"' + expanded + ' target="' + target + '">';

		if (item["icon"] != "undefined")
		{
			strLeftMenu += '<i class="fa fa-' + item["icon"] + ' fa-fw"></i> ';
		}

		strLeftMenu += item["description"];

		if (item["children"] != null && Object.keys(item["children"]).length > 0)
		{
			strLeftMenu += '<span class="fa arrow"></span>';
		}

		strLeftMenu += '</a>';

		if (item["subscriptLinkClass"] != null && item["subscriptDescription"] != null)
		{
			strLeftMenu += '<a class="' + item["subscriptLinkClass"] + ' menuSubscriptLink" value="' + item["subscriptLinkValue"] + '" href="#">' +
						' (' + item["subscriptDescription"] + ')' +
						'</a>';
			strLeftMenu += '</span>';
		}

		if (item["children"] != null && Object.keys(item["children"]).length > 0)
		{
			var level = "";
			if (depth === 1)
			{
				level = 'second';
			}
			else if (depth > 1)
			{
				level = 'third';
			}

			strLeftMenu += '<ul class="nav nav-' + level + '-level" ' + expanded + '>';

			jQuery.each(item["children"], function(i, e) {
				if (e != null) strLeftMenu += FHC_NavigationWidget._buildLeftMenuStructure(e, ++depth);
			});

			strLeftMenu += '</ul>';
		}

		strLeftMenu += '</li>';

		return strLeftMenu;
	},

	/**
	 * Returns the URI of the caller
	 */
	_getNavigationWidgetCalled: function() {
		return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
	}
};

/**
 * When JQuery is up
 */
$(document).ready(function() {

	FHC_NavigationWidget.renderHeaderMenu();

	FHC_NavigationWidget.renderSideMenu();

});
