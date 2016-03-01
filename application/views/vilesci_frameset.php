<frameset rows="55px,*" frameborder="0" framespacing="0">
  	<frame src="<?php echo base_url('vilesci/top.php')?>" id="top" name="top" scrolling="No"/>
  	<frameset border="4" frameborder="1" framespacing="0" cols="200px,*" >
  		<frame style="border-right: 3px; border-right-style:solid; border-right-color: grey;" src="<?php echo base_url('vilesci/left.php')?>" id="nav" name="nav" />
  		<frame frameborder="1" src="<?php echo base_url('vilesci/main.php')?>" id="main" name="main" />
  	</frameset>
	<noframes>
		<body bgcolor="#FFFFFF">
			This application works only with a frames-enabled browser.<br />
			<a href="<?php echo base_url('vilesci/main.php')?>">Use without frames</a>
		</body>
	</noframes>
</frameset>
