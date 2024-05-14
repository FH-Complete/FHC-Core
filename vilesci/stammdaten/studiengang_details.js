
$(function () {
	$("#bescheidvom,#titelbescheidvom").datepicker();
});

tinyMCE.init({
	mode: 'specific_textareas',
	editor_selector: "mceEditor",
	theme: "advanced",
	language: "de",
	file_browser_callback: "FHCFileBrowser",
	plugins: "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking",
	// Theme options
	theme_advanced_buttons1: "code, bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect",
	theme_advanced_buttons2: "", //tablecontrols,|,hr,removeformat,visualaid
	theme_advanced_buttons3: "",
	theme_advanced_toolbar_location: "top",
	theme_advanced_toolbar_align: "center",
	theme_advanced_statusbar_location: "bottom",
	theme_advanced_resizing: true,
	force_br_newlines: true,
	force_p_newlines: false,
	forced_root_block: '',
	editor_deselector: "mceNoEditor"
});

function unchanged()
{
	document.studiengangform.reset();
	document.studiengangform.schick.disabled = true;
	document.getElementById("submsg").style.visibility = "hidden";
	checkmail();
	checkdate(document.studiengangform.bescheidvom);
	checkdate(document.studiengangform.titelbescheidvom);
	checkrequired(document.studiengangform.kurzbz);
	checkrequired(document.studiengangform.bezeichnung);
	checkrequired(document.studiengangform.studiengang_kz);


}

function checkmail()
{
	/*
	 if((document.studiengangform.email.value != '')&&(!emailCheck(document.studiengangform.email.value)))
	 {
	 //document.studiengangform.schick.disabled = true;
	 document.studiengangform.email.className="input_error";
	 return false;

	 }
	 else
	 {
	 document.studiengangform.email.className = "input_ok";
	 //document.studiengangform.schick.disabled = false;
	 //document.getElementById("submsg").style.visibility="visible";
	 return true;
	 }*/
	return true;
}

function checkdate(feld)
{
	if ((feld.value != '') && (!dateCheck(feld)))
	{
		//document.studiengangform.schick.disabled = true;
		feld.className = "input_error";
		return false;
	}
	else
	{
		if (feld.value != '')
			feld.value = dateCheck(feld);

		feld.className = "input_ok";
		return true;
	}
}

function checkrequired(feld)
{
	if (feld.value == '')
	{
		feld.className = "input_error";
		return false;
	}
	else
	{
		feld.className = "input_ok";
		return true;
	}
}

function submitable()
{
	mail = checkmail();
	date1 = true;//checkdate(document.studiengangform.bescheidvom);
	date2 = true;//checkdate(document.studiengangform.titelbescheidvom);
	required1 = checkrequired(document.studiengangform.kurzbz);
	required2 = checkrequired(document.studiengangform.bezeichnung);
	required3 = checkrequired(document.studiengangform.studiengang_kz);

	if ((!mail) || (!date1) || (!date2) || (!required1) || (!required2) || (!required3))
	{
		document.studiengangform.schick.disabled = true;
		document.getElementById("submsg").style.visibility = "hidden";
	}
	else
	{
		document.studiengangform.schick.disabled = false;
		document.getElementById("submsg").style.visibility = "visible";

	}
}

function toggleOeParentDiv()
{
	if (document.getElementById("oe_kurzbz").value == "")
		document.getElementById("oe_parent_div").style.visibility = "visible";
	else
		document.getElementById("oe_parent_div").style.visibility = "hidden";
}
