<?php
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');

?>

function updateProjektRessource()
{
	if(projekt_ressource_id!='')
	{
		aufwand = document.getElementById("textbox-ressource-aufwand").value;
		if (document.getElementById("leitung").selected)
			funktion_kurzbz = 'Leitung';
		else
			funktion_kurzbz = 'Mitarbeiter';

		try
		{
			var soapBody = new SOAPObject("saveProjektRessource");
			var projektRessource = new SOAPObject("projektRessource");

			projektRessource.appendChild(new SOAPObject("projekt_ressource_id")).val(projekt_ressource_id);

			if(projekt_kurzbz != '')
			{
				projektRessource.appendChild(new SOAPObject("projektphase_id")).val('');
				projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val(projekt_kurzbz);
				var res_binding = window.opener.document.getElementById('box-projekt-ressourcen');
			}
			else if(projektphase_id != '')
			{
				projektRessource.appendChild(new SOAPObject("projektphase_id")).val(projektphase_id);
				projektRessource.appendChild(new SOAPObject("projekt_kurzbz")).val('');
				var res_binding = window.opener.document.getElementById('box-projekt-ressource-phase');
			}

			projektRessource.appendChild(new SOAPObject("ressource_id")).val(ressource_id);
			projektRessource.appendChild(new SOAPObject("funktion_kurzbz")).val(funktion_kurzbz);
			projektRessource.appendChild(new SOAPObject("beschreibung")).val(beschreibung);
			projektRessource.appendChild(new SOAPObject("aufwand")).val(aufwand);

			soapBody.appendChild(projektRessource);

			var sr = new SOAPRequest("saveProjektRessource",soapBody);
			SOAPClient.Proxy="<?php echo APP_ROOT;?>soap/ressource_projekt.soap.php?"+gettimestamp();

			function mycallb(obj, projekt_kurzbz, projektphase_id)
			{
				var ressourcebinding=obj;
				var projekt = projekt_kurzbz;
				var phase = projektphase_id;

				this.invoke=function (respObj)
				{
					try
					{
						var id = respObj.Body[0].saveProjektRessourceResponse[0].message[0].Text;
					}
					catch(e)
					{
						var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
						alert('Fehler: '+fehler);
						return;
					}
					// Ressourcen Tree aktualisieren
					ressourcebinding.LoadRessourceTree(projekt, phase);

					// Popup schließen
					window.close();
				}
			}

			// Callback fuer aktualisierung des Trees nach dem Speichern
			var cb=new mycallb(res_binding, projekt_kurzbz, projektphase_id);
			SOAPClient.SendRequest(sr,cb.invoke);
		}
		catch(e)
		{
			debug("Ressource load failed with exception: "+e);
		}
	}
}
