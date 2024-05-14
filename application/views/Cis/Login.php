<?php
	$includesArray = array(
		'title' => 'FH-Complete',
		'bootstrap5' => true,
		'fontawesome6' => true
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="login-form" class="login-form container">
		<div class="row justify-content-center">
			<div class="col-md-9 col-lg-7 col-xl-6">
				<?= form_open('Cis/Auth/login'); ?>
					<p class="text-center">
						<img src="<?php echo base_url('public/images/logo-300x160.png'); ?>" >
					</p>

					<br>

					<?= validation_errors('<div class="alert alert-danger" role="alert">', '</div>'); ?>

					<div class="mb-3">
						<?= form_input(['name' => 'username', 'class' => 'form-control', 'placeholder' => 'Username', 'required' => true]); ?>
					</div>

					<div class="mb-3">
						<?= form_password(['name' => 'password', 'class' => 'form-control', 'placeholder' => 'Password', 'required' => true]); ?>
					</div>

					<div class="d-grid mb-3">
						<?= form_button(['type' => 'submit', 'class' => 'btn btn-primary'], 'Log in'); ?>
					</div>

					<p class="text-center"><a href="#">Forgot Password?</a></p>
				<?= form_close(); ?>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

