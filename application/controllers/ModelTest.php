<?php
defined('BASEPATH') || exit('No direct script access allowed');

class ModelTest extends FHC_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 * @return void
	 */
	public function index()
	{
		$this->session->uid='admin';	// Should normaly be set through auth
		$this->load->model('person/Prestudent_model');
		$id=null;
		
		// Insert PreStudent
		$data = array
		(
			'aufmerksamdurch_kurzbz' => 'k.A.',
			'person_id' => 1,
			'studiengang_kz' => 0
		);
		$res = $this->Prestudent_model->insert($data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id=$res->retval;

		// Update PreStudent
		$data = array
		(
			'zgvort' => 'Wien',
			'zgvdatum' => '2012-12-12',
			'facheinschlberuf' => true
		);
		$res = $this->Prestudent_model->update($id, $data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id=$res->retval;
		
		// Replace PreStudent
		/*$data = array
		(
			'prestudent_id' => $id,
			'zgvmaort' => 'Linz',
			'zgvmadatum' => '2011-11-11',
			'facheinschlberuf' => false
		);
		$res = $this->Prestudent_model->replace($data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval;
		else
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval;*/
		
		// Load PreStudent
		$res = $this->Prestudent_model->load($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			var_dump($res->retval);

		// Delete PreStudent
		$res = $this->Prestudent_model->delete($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			var_dump($res->retval);

		$this->load->model('organisation/Organisationseinheit_model');		
		// Insert OE
		$data = array
		(
			'oe_kurzbz' => 'testoe',
			'bezeichnung' => 'testoe',
			'organisationseinheittyp_kurzbz' => 'Institut',
			'standort_id' => null
		);
		$res = $this->Organisationseinheit_model->insert($data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id = $data['oe_kurzbz'];
		var_dump($res);

		// Update PreStudent
		$data = array
		(
			'freigabegrenze' => 1234.56,
			'kurzzeichen' => 'TestOE',
			'lehre' => false
		);
		$res = $this->Organisationseinheit_model->update($id, $data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id=$res->retval;
		
		// Delete PreStudent
		$res = $this->Organisationseinheit_model->delete($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->code,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			var_dump($res->retval);
	}
}
