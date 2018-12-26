<?php
class Shop extends CI_Controller{
    private $name;
    private $userId;

    public function __construct() {
        parent::__construct();
        $this->load->view('includes/header', array( 'buttonMain' => 'Log in!'));
        /** @var Model_Shop */
        $this->load->model('Model_Shop', 'shopM');
        /** @var User */
        $this->load->model('Model_User', 'user');
        $this->load->library('session');
        $this->userId = $this->user->checkIfRegistered($this->session->login, $this->session->password)[1];
        $this->load->view('includes/sessionPrintout', array('session' => $this->session->all_userdata()));
    }


    public function index($list = '') {
        if ( $list == '') {
            $this->load->view('pages/addShop', array(
                'buttonName' => 'Add',
                    'loggedIn' => $this->session->loggedIn
                )
            );
        }
        else{
            $this->load->view('pages/shopList', (array('shopList' => $this->shopM->getShopList($this->userId))));
        }
    }

    public function formReader() {
        $this->name = $this->input->post('name');
            $this->load->view('pages/addShop', array('buttonName' => 'Add',
            'data' => $this->input->post(), 'userId' => $this->userId));
        $result = $this->addShop($this->name, $this->userId);
        if( $result ){
            $this->load->view('pages/shopList', array('shopList' => $this->shopM->getShopList($this->userId) ));
            echo 'Done';
        }
        else{
            echo "Something went wrong";
        }
    }

    public function addShop($name, $userId){
        $result = $this->shopM->saveUser($name, $userId);
        return $result;
    }
}