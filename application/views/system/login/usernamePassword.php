<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Login',
			'jquery3' => true,
			'jqueryui1' => true,
			'bootstrap3' => true,
			'fontawesome4' => true,
			'sbadmintemplate3' => true,
			'ajaxlib' => true,
			'dialoglib' => true,
			'customCSSs' => 'public/css/Login.css',
			'customJSs' => 'public/js/Login.js'
		)
	);
?>

<body>
	<div class="login-form">

		<p class="text-center">
			<img src="<?php echo base_url('public/images/logo-300x160.png'); ?>" >
		</p>

		<br>

		<div class="form-group">
			<input id="username" type="text" class="form-control" placeholder="Username" required="required">
		</div>

		<div class="form-group">
			<input id="password" type="password" class="form-control" placeholder="Password" required="required">
		</div>

		<div class="form-group">
			<button id="btnLogin" ype="submit" class="btn btn-primary btn-block">Log in</button>
		</div>

		<p class="text-center"><a href="#">Forgot Password?</a></p>

	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
