<?php
class Kontakt extends FHC_Controller
{
	public function __construct()
    {
        parent::__construct();
        $this->load->model('kontakt/kontakt_model');
    }

    public function index()
    {
        $data['person'] = $this->person_model->getPersonen();
		$data['title'] = 'Personen Archiv';

		$this->load->view('templates/header', $data);
		$this->load->view('kontakt/index', $data);
		$this->load->view('templates/footer');
    }

    public function view($slug = null)
    {
        $data['person_item'] = $this->person_model->getPersonen($slug);
		if (empty($data['person_item']))
	    	show_404();

	    $data['title'] = $data['person_item']->titelpre;

	    $this->load->view('templates/header', $data);
	    $this->load->view('kontakt/view', $data);
	    $this->load->view('templates/footer');
    }
}
