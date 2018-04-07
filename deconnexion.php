<?php
	include_once "includes/db.php";
  	include_once "includes/functions.php"; 

	if (!connecte()) {
	  header('Location: 404.php');
	  die();
	}

	unset($_SESSION['auth']);
	unset($_SESSION['quiz']);
	$_SESSION['flash']['success'] = "Vous êtes maintenant déconnecté.";
	setcookie('auth', '', time() - 3600, '/', 'localhost', false, true); // supprimer cookie auth
	header('Location: index.php');
	die();
?>