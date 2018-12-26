<?php
/** ProviderModel */
include_once("models/ProviderModel.php");
/** Class is responsible for communicating provider model with views */
class ProvidersController{

    /** @var ProviderModel  */
    private $model;

    public function __construct(){
        $this->model = new ProviderModel();
    }

    /**
     * Method gets providers picture
     * @param $id - provider id
     * @return string
     */
    public function getProvidersPicture( $id ){
        $provider = $this->model->getProviderById($id);
        $providersName = $provider[0]['fullName'];
        if( file_exists( "assets/images/" . $providersName . ".png" ) ){
            return $providersName;
        }
        return "no_user_photo";
    }

    /**
     * Method gets provider model
     * @param $id - providerid
     * @return ProviderModel
     */
    public function getProviderById($id) {
        return $this->model->getProviderById($id);
    }

    /**
     * Method gets list of all providers
     * @return array
     */
    public function getListOfAllProviders() {
        return $this->model->getProvidersList();
    }
}