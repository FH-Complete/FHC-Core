<?php
class Person extends MY_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->model('person/person_model');
        }

        public function index()
        {
                $data['person'] = $this->person_model->getPersonen();
				$data['title'] = 'Personen Archiv';

				$this->load->view('templates/header', $data);
				$this->load->view('person/index', $data);
				$this->load->view('templates/footer');
        }

        public function view($slug = NULL)
        {
            $data['person_item'] = $this->person_model->getPersonen($slug);
			if (empty($data['person_item']))
		    {
		            show_404();
		    }

		    $data['title'] = $data['person_item']->titelpre;

		    $this->load->view('templates/header', $data);
		    $this->load->view('person/view', $data);
		    $this->load->view('templates/footer');
        }
}
