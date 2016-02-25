<?php
/* Copyright (C) 2016 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Moik <moik@technikum-wien.at>
 */

require_once(dirname(__FILE__)."/jquery.php");
echo '
<script>
	var TARGET = "";
	function _GET()
	{
		var url = window.location.href;

		if(url.slice(-1) === "#")
			url = url.slice(0,-1);

		var vars = {};
		var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value)
		{
			vars[key] = value;
		});
		return vars;
	}

	function AJAXCall(info, successfunction)
	{
		if(TARGET == "")
			die("Es wurde kein AJAX-Target angegeben");
		$.ajax(
		{
			url: TARGET,
			type: "POST",
			dataType: "html",
			data: info,
			timeout: 5000

		}).done(function(result)
		{
			try
			{
				var res = JSON.parse(result);
			}
			catch (e)
			{
				die(result);
				return false;
			}
				if(res.erfolg)
				{
					try
					{
						var ret = JSON.parse(res.info);
						successfunction(ret);
					}
					catch(e)
					{
						die(res.info);
					}
				}
				else
				{
					die(result);
				}

		}).fail(function(jqXHR, status)
		{
			die(status);
		});
	}

	function die(msg)
	{
		document.body.innerHTML = msg;
		throw new Error(msg);
	}

	function isObject(val)
	{
		if (val === null) { return false;}
		return ( (typeof val === "function") || (typeof val === "object") );
	}
</script>';

?>


