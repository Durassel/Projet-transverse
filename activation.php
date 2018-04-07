<?php
include_once "includes/db.php";
include_once "includes/functions.php";

if (connecte()) {
	header('Location: 404.php');
	die();
}

if (isset($_GET['id']) && isset($_GET['validationkey'])) {
	$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :id');
	$request->execute(array(
	    'id' => $_GET['id']
	));
	$result = $request->fetch();

	if($result && $result['validationkey'] == $_GET['validationkey']) {
		$request = $db->prepare('UPDATE utilisateur SET validationkey = NULL, active = NOW() WHERE id = :id');
		$request->execute(array(
		    'id' => $_GET['id']
		));
		$_SESSION['auth'] = $result;
		$_SESSION['flash']['success'] = "Votre compte a été validé avec succès.";
		header('Location: index.php');
		die();
	} else {
		$_SESSION['flash']['danger'] = "La clé d'activation de votre compte est incorrecte.";
		header('Location: inscription.php');
		die();
	}
} else {
	header('Location: 404.php');
	die();
}
?>