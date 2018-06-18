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
		//
		FHC_AjaxClient.ajaxCallGet(
			'system/Navigation/header',
			{
				navigation_page: FHC_NavigationWidget._getNavigationWidgetCalled()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						jQuery.each(FHC_AjaxClient.getData(data), function(i, e) {
							$(".menu-header-items").append('<a class="navbar-brand" href="' + e + '">' + i + '</a>');
						});
					}
				}
			}
		);
	},

	/**
	 * Renders the side left menu
	 */
	renderSideMenu: function() {
		//
		FHC_AjaxClient.ajaxCallGet(
			'system/Navigation/menu',
			{
				navigation_page: FHC_NavigationWidget._getNavigationWidgetCalled()
			},
			{
				successCallback: function(data, textStatus, jqXHR) {
					if (FHC_AjaxClient.hasData(data))
					{
						var strMenu = '';

						FHC_NavigationWidget._printCollapseIcon();

						jQuery.each(FHC_AjaxClient.getData(data), function(i, e) {
							if (e != null) strMenu += FHC_NavigationWidget._printNavItem(e);
						});

						$("#side-menu").html(strMenu);
						$("#side-menu").metisMenu();
					}

					if (typeof sideMenuHook == 'function')
					{
						sideMenuHook();
					}
				}
			}
		);
	},

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	_printCollapseIcon: function() {
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
	},

	/**
	 *
	 */
	_printNavItem: function(item, depth = 1) {

		strMenu = "";
		var expanded = typeof item['expand'] != 'undefined' && item['expand'] === true ? ' active' : '';

		strMenu += '<li class="' + expanded + '">';

		if (typeof item['subscriptLinkClass'] != 'undefined' && typeof item['subscriptDescription'] != 'undefined'
			&& item['subscriptLinkClass'] != null && item['subscriptDescription'] != null)
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

		if (typeof item['subscriptLinkClass'] != 'undefined' && typeof item['subscriptDescription'] != 'undefined'
			&& item['subscriptLinkClass'] != null && item['subscriptDescription'] != null)
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
				if (e != null) strMenu += FHC_NavigationWidget._printNavItem(e, ++depth);
			});

			strMenu += '</ul>';
		}

		strMenu += '</li>';

		return strMenu;
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
