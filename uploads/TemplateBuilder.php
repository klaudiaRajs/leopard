<?php

/** Class is responsible for template building  */
class TemplateBuilder {

    /**
     * Method gets home template
     */
    public function getHome() {
        include_once("views/header.php");
        include_once("views/home.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets articles list template
     */
    public function getArticles() {
        include_once("views/header.php");
        include_once("views/articles.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets article template
     */
    public function getArticle() {
        include_once("views/header.php");
        include_once("views/article.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets tutorial list template
     */
    public function getTutorials() {
        include_once("views/header.php");
        include_once("views/tutorials.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets tutor list template
     */
    public function getTutors() {
        include_once("views/header.php");
        include_once("views/tutors.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets registration template
     */
    public function getRegistration() {
        include_once("views/header.php");
        include_once("views/register.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets contact page template
     */
    public function getContact() {
        include_once("views/header.php");
        include_once("views/contact.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets tutors page template
     */
    public function getTutorsPage() {
        include_once("views/header.php");
        include_once("views/tutorsPage.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets logger template
     */
    public function getLogger() {
        include_once("views/header.php");
        include_once("views/logIn.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets log out template
     */
    public function getLogOut() {
        include_once("views/header.php");
        include_once("views/logOut.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets providers list template
     */
    public function getProviders() {
        include_once("views/header.php");
        include_once("views/providers.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets error page template
     */
    public function getError() {
        include_once("views/header.php");
        include_once("views/error.php");
        include_once("views/footer.php");
    }

    /**
     * Method gets update information template
     */
    public function getUpdateInformationPage() {
        include_once("views/header.php");
        include_once("views/updatePersonalInformation.php");
        include_once("views/footer.php");
    }
}