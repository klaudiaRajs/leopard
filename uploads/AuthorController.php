<?php
/** AuthorModel */
include_once(__DIR__ . "/../models/AuthorModel.php");

/** Class serves as a communication between AuthorModel and views */
class AuthorController {
    private $model;

    public function __construct(){
        $this->model = new AuthorModel();
    }

    /**
     * Method initiates the process of updating author
     * @param $fields - field => value pairs
     * @param $id - author id
     * @return bool
     */
    public function update(array $fields, $id){
        $this->model->update($fields, $id);
    }

    /**
     * Method initiaties the process of deleting user account
     * @param $id - user id
     * @return bool
     */
    public function delete($id){
        $this->model->delete($id);
    }

    /**
     * Method returns model of author
     * @param $login - author login
     * @param $password - author's password
     * @return AuthorModel
     */
    public function getAuthorByCredentials($login, $password){
        if( $password == null ){
            return $this->model->getAuthorByLogin($login);
        }
        return $this->model->getAuthorByCredentials( $login, $password );
    }

    /**
     * Method returns list of all users
     * @return array
     */
    public function getListOfAllUsers() {
        return $this->model->getTutorsList();
    }

    /**
     * Method returns random tutors id
     * @return array
     */
    private function getRandomTutorsId() {
        $idsList = $this->model->getListOfAuthorsIds();
        $randomId = array_rand($idsList);
        return $idsList[$randomId]['id'];
    }

    /**
     * Method returns random tutor
     * @return array
     */
    public function getRandomTutor() {
        return $this->model->getTutorById($this->getRandomTutorsId());
    }

    /**
     * Method gets tutor by id
     * @param $id - author id
     * @return AuthorModel
     */
    public function getTutorById($id) {
        return $this->model->getTutorById($id);
    }

    /**
     * Method returns tutors picture
     * @param $id - author id
     * @return string
     */
    public function getTutorsPicture( $id ){
        $tutor = $this->model->getTutorById($id);
        $tutorsName = $tutor[0]['firstName'] . $tutor[0]['lastName'];
        if( file_exists( "assets/images/" . $tutorsName . ".png" ) ){
            return $tutorsName;
        }
        return "no_user_photo";
    }
}