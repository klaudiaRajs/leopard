<?php
/** Model */
include_once( "models/ArticleModel.php" );
/**
 * Class is responsible for communication betweeen view and models.
 */
class ArticleController{

    private $model;

    public function __construct(){
        $this->model = new ArticleModel();
    }

    /**
     * Method returns a list of articles
     */
    public function getListOfArticles(){
        return $this->model->getAllTheArticles();
    }

    /**
     * Method returns random article per author
     * @param $id - id of an author
     * @return array|bool|null
     */
    public function getRandomArticleByAuthor($id){
        if( !$this->model->getRandomArticleByTutor($id) ){
            return null;
        }
        return $this->model->getRandomArticleByTutor($id);
    }

    /**
     * Method returns list of articles by author id
     * @param $id - author id
     * @return array|bool|null
     */
    public function getArticleListByAuthorId( $id ){
        if( !$this->model->getArticlesByTutor($id) ){
            return null;
        }
        return $this->model->getArticlesByTutor($id);
    }

    /**
     * Method returns a model of an author
     * @param $id - author id
     * @return array|bool
     */
    public function getById( $id ){
        return $this->model->getById($id);
    }

}