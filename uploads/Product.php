<?php
class Product extends CI_Controller{
    private $userId;
    private $loggedIn;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        /** @var Model_Product */
        $this->load->model('Model_Product', 'product');
        $this->load->model('Model_Shop', 'shop');
        $this->load->model('Model_User', 'user');
        $this->load->model('Model_Category', 'category');
        $this->userId = $this->user->checkIfRegistered($this->session->login, $this->session->password)[1];
        $this->loggedIn = $this->session->loggedIn;
    }

    public function index($list = '') {
        if ( $this->loggedIn == true) {
            if ($list == '') {
                $layoutVariables = array(
                    'buttonName' => 'Add',
                    'shopList' => $this->shop->getShopList($this->userId),
                    'categoryList' => $this->category->getCategoryList($this->userId)
                );
                $this->callLayout('pages/addProduct', $layoutVariables);
            } elseif ($list == 'overview') {
                $layoutVariables = array('categoryList' => $this->category->getCategoryList($this->userId), '');
                $this->callLayout('pages/overview', $layoutVariables);
            };
        }
           else{
               $this->callLayout('pages/forNotLoggedIn');
        }
    }

    public function addProduct($name, $price, $amount, $shop, $category, $date, $comment, $fixed, $userId){
        if( !empty($name) && !empty($price) && !empty($amount) && !empty($shop) && !empty($category) && !empty($date) && !empty($comment) && !empty($fixed)){
            $data = array(
                'name' => $name,
                'price' => $price,
                'userId' => $userId,
                'shopId' => $shop,
                'categoryId' => $category,
                'date' => $date,
                'isFixed' => $fixed,
                'comment' => $comment,
                'amount' => $amount,
            );
            $result = $this->product->saveProduct($data);
            if( !$result ){
                throw new Exception("Something went wrong with saving your purchase!");
            }
        }
    }

    private function callLayout($pageName, $parameters = null){
        $this->load->view('includes/header', array('buttonMain' => ($this->session->loggedIn ? 'Log out' : 'Log in') ));
        $this->load->view($pageName, $parameters);
        $this->load->view('includes/sessionPrintout', array('session' => $this->session->all_userdata()));
    }
}