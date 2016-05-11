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
		//$this->session->uid='admin';	// Should normaly be set through auth
		$this->load->model('person/Person_model');
		$this->Person_model->setUID('admin');	// Should normaly be set through auth
		$res = $this->Person_model->getPerson(null, 'asdf\' OR person_id=1; SELECT 1; --');
		var_dump($res->result_object());

		$this->load->model('crm/Prestudent_model');
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
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
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
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
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
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval;
		else
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval;*/
		
		// Load PreStudent
		$res = $this->Prestudent_model->load($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			var_dump($res->retval);

		// Insert PreStudentStatus
		$this->load->model('crm/Prestudentstatus_model');
		$data = array
		(
			'prestudent_id' => $id,
			'status_kurzbz' => 'Interessent',
			'studiensemester_kurzbz' => 'WS2001',
			'ausbildungssemester' => 1
		);
		$res = $this->Prestudentstatus_model->insert($data);
		var_dump($res->retval);
		
		// Load PreStudentStatus
		$res = $this->Prestudentstatus_model->load($data);
		var_dump($res->retval->result_object());
		$res = $this->Prestudentstatus_model->load(array($id,'Interessent', 'WS2001', 1));
		var_dump($res->retval->result_object());
		
		// Update PreStudentStatus
		$res = $this->Prestudentstatus_model->update($data, array
			(
				'prestudent_id' => $id,
				'status_kurzbz' => 'Bewerber',
				'studiensemester_kurzbz' => 'WS2011',
				'ausbildungssemester' => 2
			));
		var_dump($res->retval);
		$res = $this->Prestudentstatus_model->update(array($id,'Bewerber', 'WS2011', 2), $data );
		var_dump($res->retval);
		
		// Delete PreStudentStatus
		$res = $this->Prestudentstatus_model->delete($data);
		var_dump($res->retval);
		
		// Delete PreStudent
		$res = $this->Prestudent_model->delete($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
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
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id = $data['oe_kurzbz'];
		var_dump($res);

		// Update OE
		$data = array
		(
			'freigabegrenze' => 1234.56,
			'kurzzeichen' => 'TestOE',
			'lehre' => false
		);
		$res = $this->Organisationseinheit_model->update($id, $data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id=$res->retval;

		// Delete Organisationseinheit
		$res = $this->Organisationseinheit_model->delete($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			var_dump($res->retval);

		$this->load->model('system/Sprache_model');		
		// Insert Sprache
		$data = array
		(
			'sprache' => 'test',
			'bezeichnung' => "{'testsprache'}",
			'locale' => 'te_TE',
			'content' => false
		);
		$res = $this->Sprache_model->insert($data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id = $data['sprache'];
		var_dump($res);

		// Update Sprache
		$data = array
		(
			'index' => 4,
			'bezeichnung' => "{'TestSprache', 'TestLanguage', 'TestSpanisch'}",
			'content' => true
		);
		$res = $this->Sprache_model->update($id, $data);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			$id=$res->retval; //echo $id;

		// Load Sprache
		$res = $this->Sprache_model->load($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
		{
			$result = $res->retval->result_object();
			var_dump($result);
			var_dump($this->Sprache_model->pgArrayPhp($result[0]->bezeichnung));
			var_dump($this->Sprache_model->pgBoolPhp($result[0]->content));
		}

		// Load All Sprache
		$res = $this->Sprache_model->loadWhere();
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
		{
			var_dump($res->retval);			
			$result = $res->retval->result_object();
			var_dump($result);
		}

		// Delete Sprache
		$res = $this->Sprache_model->delete($id);
		if ($res->error)
			echo 'Error: ',$res->error, ', Code: ',$res->fhcCode,' -> ',$res->msg,': ',$res->retval,'<br/>';
		else
			var_dump($res->retval);
	}
}
