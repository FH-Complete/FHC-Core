
/**
 * To login via LDAP
 */
function loginLDAP()
{
	// Ajax call to login with LDAP
	FHC_AjaxClient.ajaxCallPost(
		"system/Login/loginLDAP",
		{
			username: $("#username").val(),
			password: $("#password").val()
		},
		{
			successCallback: function(data, textStatus, jqXHR) {

				if (FHC_AjaxClient.isError(data))
				{
					if (FHC_AjaxClient.getError(data) == 10)
					{
						FHC_DialogLib.alertError("Username not foud");
					}
					if (FHC_AjaxClient.getError(data) == 2)
					{
						FHC_DialogLib.alertError("Wrong password");
					}
				}
				else
				{
					$(location).attr("href", FHC_AjaxClient.getData(data));
				}
			},
			errorCallback: function(jqXHR, textStatus, errorThrown) {
				FHC_DialogLib.alertError(textStatus);
			}
		}
	);
}

/**
 * When JQuery is up
 */
$(document).ready(function() {

	$("#btnLogin").click(loginLDAP);

	$("#username").keydown(function(e) {
	    if (e.keyCode == 13) loginLDAP();
	})

	$("#password").keydown(function(e) {
	    if (e.keyCode == 13) loginLDAP();
	})

});
