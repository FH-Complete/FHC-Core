<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

include('../config/vilesci.config.inc.php');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
?>

<window id="about-window" title="Kontakt"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        >
<vbox>
	<hbox style="margin-top: 20px">
		<spacer flex="1"/>
		<image src='<?php echo APP_ROOT; ?>skin/images/fh_complete_logo_400x61.png' width="400" height="61"/>
		<spacer flex="1"/>
	</hbox>
	<hbox style="margin-top: 20px">
		<spacer flex="1"/>
	</hbox>
	<hbox>
		<spacer flex="1"/>
		<label value="Copyright (C) 2007 FH Complete" />
		<spacer flex="1"/>
	</hbox>
	<hbox>
		<spacer flex="1"/>
		<groupbox>
			<caption label='GPL' />
			<description style="white-space: pre;">
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as
	published by the Free Software Foundation; either version 2 of the
	License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

	Authors: Christian Paminger &lt;christian.paminger@technikum-wien.at&gt;,
			 Andreas Oesterreicher &lt;andreas.oesterreicher@technikum-wien.at&gt;,
			 Rudolf Hangl &lt;rudolf.hangl@technikum-wien.at&gt;,
			 Gerald Raab &lt;gerald.raab@technikum-wien.at&gt; and
			 Gerald Simane-Sequens &lt;gerald.simane-sequence@technikum-wien.at&gt;
			 Manfred Kindl &lt;manfred.kindl@technikum-wien.at&gt;
		   	</description>
		</groupbox>
		<spacer flex="1"/>
	</hbox>
	<hbox>
		<spacer flex="1"/>
		<button oncommand="window.close()" label="Close" />
		<spacer flex="1"/>
	</hbox>
</vbox>
</window>