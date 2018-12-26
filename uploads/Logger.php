<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/** Global Handler  */
include_once('../general/GlobalsHandler.php');
/** Author Controller  */
include_once('AuthorController.php');
/** UrlHelper */
include_once(__DIR__ . '/../general/url.php');
$globals = new GlobalsHandler();
$author = new AuthorController();

$login = $globals->getPost('login');
$password = hash('sha512', $globals->getPost('password'));

if($login && $password){
    $result = $author->getAuthorByCredentials($login, $password)[0];
    if($result){
        $_SESSION['loggedId'] = $result['id'];
        header("Location: " . getApplicationUrl() . "/updatePersonalInformation");
    }else{
        $_SESSION['unsuccessfulLogging'] = true;
        header("Location: " . getApplicationUrl() . "/logIn");
    }
}else{
    header("Location: " . getApplicationUrl() . "/logIn");
}