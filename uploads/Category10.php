<?php
class Category extends CI_Controller{
    private $name;
    private $userId;
    private $loggedIn;
    public $user;

    public function __construct() {
        parent::__construct();
        $this->load->model('Model_Category', 'category');
        $this->userId = $this->input->post('login');
        /** @var Model_User */
        $this->load->model('Model_User', 'user');

        $this->load->library('session');
        $this->loggedIn = $this->session->loggedIn;
        $this->userId = $this->user->checkIfRegistered($this->session->login, $this->session->password)[1];
    }

    public function index($list = '') {
        if ( $this->loggedIn == true) {
            if ($list == '') {
                $layoutVariables = array('buttonName' => 'Add');
                $this->callLayout('pages/addCategory', $layoutVariables);
            } elseif ($list == 'list') {
                $layoutVariables = array('categoryList' => $this->category->getCategoryList($this->userId));
                $this->callLayout('pages/categoryList', $layoutVariables);
            } elseif ($list == 'overview') {
                $layoutVariables = array('categoryList' => $this->category->getCategoryList($this->userId), '');
                $this->callLayout('pages/overview', $layoutVariables);
            };
        }
        else{
            $this->load->view('pages/forNotLoggedIn');
        }
    }

        public function formReader() {
            $this->name = $this->input->post('category_name');
            $layoutVariables = array('buttonName' => 'Add' );
            $this->callLayout('pages/addCategory', $layoutVariables);

            $result = $this->addCategory($this->name, $this->userId);

            if( $result ){
                $layoutVariables = array('categoryList' => $this->category->getCategoryList($this->userId) );
                $this->load->view('pages/categoryList',  $layoutVariables);
                echo 'Done';
            }
            else{
                echo "Something went wrong";
            }
        }

    public function addCategory($name, $userId){
        $result = $this->category->saveCategory($name, $userId);
        return $result;
    }

    private function callLayout($pageName, $parameters = null){
        $this->load->view('includes/header', array('buttonMain' => ($this->session->loggedIn ? 'Log out' : 'Log in') ));
        $this->load->view($pageName, $parameters);
        $this->load->view('includes/sessionPrintout', array('session' => $this->session->all_userdata()));
    }
}