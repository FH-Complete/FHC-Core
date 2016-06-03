<?php
/*
 * Signatur die an E-Mails angehängt wird, die vom System verschickt werden
 */
$this->phrasen['mail/signatur']="Mit freundlichen Grüßen\n\nIhre Hochschule\n";
/*
 * Mail, die vom Profil aus an den Einkauf bezüglich Betriebsmittel verschickt wird
 */
$this->phrasen['mail/profilBetriebsmittelKorrektur']="invalid@example.com";

$this->phrasen['mail/accountaktivierung']='<b><center>Account Information</center></b><br>
Name: %1$s %2$s<br>
Username: %3$s<br>
Aktivierungscode: %4$s<br>
%5$s<br>
E-Mail: %6$s<br>
<br>
<br>
<b><center>Account Mini FAQ Deutsch</center></b><br>
<br>
<span style="font-style:italic;">Wie aktiviere ich meinen Account?</span><br>
Öffnen Sie mit ihrem Web-Browser die Adresse <a href="%7$s">%7$s</a><br>
Tragen Sie in das Formular Ihren Usernamen und Aktivierungscode ein und vergeben Sie ein Passwort für den Account.
<br><br>
<span style="font-style:italic;">Wo erhalte ich weitere Informationen?</span><br>
Die primäre Anlaufstelle für Fragen rund um den Netzwerkbetrieb ist die Webseite<br>
<a href="%8$s">%8$s</a><br>
Verwenden Sie die Informationen auf dieser Seite, um einen Überblick über die vorhandenen Möglichkeiten zu erhalten.
<br><br>
<b><center>Account Mini FAQ English</center></b><br>
<br>
<span style="font-style:italic;">Account activation:</span><br>
Open your web browser and go to <a href="%7$s">%7$s</a><br>
Enter your user name and activation key. Enter a new password for your account.
<br><br>
<span style="font-style:italic;">Looking for further information?</span><br>
For questions concerning the network services, see<br>
<a href="%8$s">%8$s</a><br>
These pages will give you a detailed overview of all services available.
<br><br>
';

$this->phrasen['mail/incomingRegistrationEmail']='This is an automatic email!<br><br>
You have been successfully registrated to our system.
<br><br><br>With the UserID: <b>%s</b> you can login to <a href="'.APP_ROOT.'cis/public/incoming">our system</a> to complete your data.<br><br><br>
Best regards,
your University of Applied Sciences';

?>
