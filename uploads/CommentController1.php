<?php
/** CommentModel */
include_once("models/CommentModel.php");
/** Global Handler */
include_once("general/GlobalsHandler.php");

class CommentController{

    /** @var CommentModel  */
    private $model;
    /** @var GlobalsHandler  */
    private $globals;
    /** @var array  */
    private $errors = [];

    public function __construct(){
        $this->model = new CommentModel();
        $this->globals = new GlobalsHandler();
        $this->saveCommentToDb();
    }
     /**
      * Method gets all comments per article
      * @param $articleId - articleId
      * @return CommentModel
      */
    public function getAllCommentsPerArticle($articleId){
        return $this->model->getCommentsPerArticle($articleId);
    }

    /**
     * Method saves comment to db
     * @return array|bool
     */
    public function saveCommentToDb(){
        $name = $this->globals->getPost('commentAuthorName');

        $email = $this->globals->getPost('commentAuthorEmail');
        $message = $this->globals->getPost('message');
        $authorId = $this->globals->getPost('authorId');
        $articleId = $this->globals->getPost('articleId');
        if($name && $email && $message){
            if(!$this->model->saveComment($name, $email, $message, $authorId, $articleId)){
                $this->errors[] = 'No correct data provided';
                return $this->errors;
            }
        }
        return true;
    }
}