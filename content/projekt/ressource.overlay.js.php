<?php 
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
?>

 
function reloadRessourceTasks()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-projektmenue');
	
	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
		return;
	
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var path = '<?php echo APP_ROOT; ?>content/projekt/ressourcenauslastung.php?typ=task&projekt_kurzbz='+projekt_kurzbz+'&'+gettimestamp();
	
	document.getElementById('iframe-ressource-projekttask').setAttribute('src',path);
}

function reloadRessourcePhasen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-projektmenue');
	
	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
		return;
	
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var path = '<?php echo APP_ROOT; ?>content/projekt/ressourcenauslastung.php?typ=phase&projekt_kurzbz='+projekt_kurzbz+'&'+gettimestamp();
	
	document.getElementById('iframe-ressource-projektphase').setAttribute('src',path);
}

function RessourcePrintTask()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-projektmenue');
	
	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
		return;
	
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var path = '<?php echo APP_ROOT; ?>content/projekt/ressourcenauslastung.php?typ=task&projekt_kurzbz='+projekt_kurzbz+'&'+gettimestamp();
	
	var foo = window.open(path);
	foo.print();
}

function RessourcePrintPhasen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree=document.getElementById('tree-projektmenue');
	
	// Wenn auf die Ueberschrift geklickt wird, soll nix passieren
	if(tree.currentIndex==-1)
		return;
	
	var projekt_kurzbz=getTreeCellText(tree, "treecol-projektmenue-projekt_kurzbz", tree.currentIndex);
	var path = '<?php echo APP_ROOT; ?>content/projekt/ressourcenauslastung.php?typ=phase&projekt_kurzbz='+projekt_kurzbz+'&'+gettimestamp();
	var foo = window.open(path);
	foo.print();
}