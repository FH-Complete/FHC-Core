$(document).ready(function ()
{
	if(viewData?.checkPerson?.unruly?.length) {
		const unruly = document.getElementById('unruly')
		unruly.setAttribute('style', 'display: block;')
	}

	if(viewData?.checkPerson?.duplicate?.length) {
		const duplicate = document.getElementById('duplicate')
		duplicate.setAttribute('style', 'display: block;')
	}


});

var PersonCheck = {
	update: function(data)
	{
		// format date according to db
		if(data.gebdatum) {
			const [day, month, year] = data.gebdatum.split('.');
			data.gebdatum = year + '-' + month + '-' + day
		}

		FHC_AjaxClient.ajaxCallPost(
			'api/frontend/v1/checkperson/CheckPerson/checkUnruly',
			data,
			{
				successCallback: function(response, textStatus, jqXHR) {
					if (response?.meta?.status === 'success')
					{
						PersonCheck._updatedUnruly(response);
					}
					else
					{
						FHC_DialogLib.alertError('unruly error');
					}
				},
				errorCallback: function() {
					FHC_DialogLib.alertWarning("Fehler beim Speichern!");
				}
			}
		);

	},

	_updatedUnruly: function(response)
	{
		const unruly = document.getElementById('unruly')

		if(response?.data?.retval?.length) {
			viewData.checkPerson.unruly = response?.data?.retval

			// replace existing elements
			const unrulylist = document.getElementById('unrulylist')
			const newUnrulyPeople = []
			viewData.checkPerson.unruly.forEach(u => {
				newUnrulyPeople.push(document.createTextNode("Person ID: " + u.person_id))
				newUnrulyPeople.push(document.createElement('br'))
			})
			unrulylist.replaceChildren(...newUnrulyPeople)

			// and show it all
			unruly.setAttribute('style', 'display: block;')
		} else {
			// just hide everything
			unruly.setAttribute('style', 'display: none;')
		}

	},
}