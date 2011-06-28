<?php
require_once(dirname(__FILE__).'/menu_addon.class.php');

class menu_addon_test extends menu_addon
{
	public function __construct()
	{
		//Liste mit Links
		$this->items[]=array('title'=>'Testlink 1',
							 'target'=>'content',
							 'link'=>'lesson.php',
							 'name'=>'tl1');
		$this->items[]=array('title'=>'Testlink 2',
							 'target'=>'content',
							 'link'=>'lesson.php',
							 'name'=>'tl2');
		$this->items[]=array('title'=>'Testlink 3',
							 'target'=>'content',
							 'link'=>'lesson.php',
							 'name'=>'tl3');
		$this->outputItems();
		
		
		// Eigener Codeblock
		$this->block='
			<form method="POST">
				<select name="stg_kz">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
				</select>
				<input type="submit" value="ok">
			</form>
			';
		if(isset($_POST['stg_kz']))
			$this->block.='KZ:'.$_POST['stg_kz'];
		$this->outputBlock();
	}	
}

new menu_addon_test();
?>