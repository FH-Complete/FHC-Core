<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Studiengang extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('studiengang_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $keyword = '';
        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'studiengang/index/';
        $config['total_rows'] = $this->studiengang_model->total_rows();
        $config['per_page'] = 10;
        $config['uri_segment'] = 3;
        $config['suffix'] = '.html';
        $config['first_url'] = base_url() . 'studiengang.html';
        $this->pagination->initialize($config);

        $start = $this->uri->segment(3, 0);
        $studiengang = $this->studiengang_model->index_limit($config['per_page'], $start);

        $data = array(
            'studiengang_data' => $studiengang,
            'keyword' => $keyword,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'start' => $start,
        );

        $this->load->view('tbl_studiengang_list', $data);
    }
    
    public function search() 
    {
        $keyword = $this->uri->segment(3, $this->input->post('keyword', TRUE));
        $this->load->library('pagination');
        
        if ($this->uri->segment(2)=='search') {
            $config['base_url'] = base_url() . 'studiengang/search/' . $keyword;
        } else {
            $config['base_url'] = base_url() . 'studiengang/index/';
        }

        $config['total_rows'] = $this->studiengang_model->search_total_rows($keyword);
        $config['per_page'] = 10;
        $config['uri_segment'] = 4;
        $config['suffix'] = '.html';
        $config['first_url'] = base_url() . 'studiengang/search/'.$keyword.'.html';
        $this->pagination->initialize($config);

        $start = $this->uri->segment(4, 0);
        $studiengang = $this->studiengang_model->search_index_limit($config['per_page'], $start, $keyword);

        $data = array(
            'studiengang_data' => $studiengang,
            'keyword' => $keyword,
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'start' => $start,
        );
        $this->load->view('tbl_studiengang_list', $data);
    }

    public function read($id) 
    {
        $row = $this->studiengang_model->get_by_id($id);
        if ($row) {
            $data = array(
	    );
            $this->load->view('tbl_studiengang_read', $data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('studiengang'));
        }
    }
    
    public function create() 
    {
        $data = array(
            'button' => 'Create',
            'action' => site_url('studiengang/create_action'),
	);
        $this->load->view('tbl_studiengang_form', $data);
    }
    
    public function create_action() 
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
	    );

            $this->studiengang_model->insert($data);
            $this->session->set_flashdata('message', 'Create Record Success');
            redirect(site_url('studiengang'));
        }
    }
    
    public function update($id) 
    {
        $row = $this->studiengang_model->get_by_id($id);

        if ($row) {
            $data = array(
                'button' => 'Update',
                'action' => site_url('studiengang/update_action'),
	    );
            $this->load->view('tbl_studiengang_form', $data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('studiengang'));
        }
    }
    
    public function update_action() 
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('', TRUE));
        } else {
            $data = array(
	    );

            $this->studiengang_model->update($this->input->post('', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('studiengang'));
        }
    }
    
    public function delete($id) 
    {
        $row = $this->studiengang_model->get_by_id($id);

        if ($row) {
            $this->studiengang_model->delete($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('studiengang'));
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('studiengang'));
        }
    }

    public function _rules() 
    {

	$this->form_validation->set_rules('', '', 'trim');
	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

};

/* End of file Studiengang.php */
/* Location: ./application/controllers/Studiengang.php */