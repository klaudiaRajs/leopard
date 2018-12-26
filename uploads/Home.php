<?php

class Home extends CI_Controller{
    public function index() {
        $data = array( 'buttonMain' => 'Log in!');
        $this->load->view('includes/header', $data);
    }
}