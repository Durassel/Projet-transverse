<?php
session_start();

try {
	$db = new PDO('mysql:host=localhost;dbname=projet_transverse;charset=utf8', 'root', '');
} catch (Exception $e) {
	die('Erreur : ' . $e->getMessage());
}

$grain = "5gwn9ci2eax";
$salt = "nd0fbw65tsv";

// Automatic login
if (isset($_COOKIE['auth']) && !isset($_SESSION['auth'])) {
	$auth = $_COOKIE['auth'];
	$auth = explode('----', $auth);
	
	$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :id');
	$request->execute(array(
		'id' => $auth[0]
	));
	$data = $request->fetch();

	$key = sha1($data['email'] . $data['password'] . $_SERVER['REMOTE_ADDR']);
	if ($key == $auth[1]) {
		$_SESSION['auth'] = $data;

		$request = $db->prepare('SELECT nom FROM statut WHERE id = :idStatut');
	    $request->execute(array(
	      'idStatut' => $data['idStatut']
	    ));
	    $idStatut = $request->fetch();
	    $_SESSION['auth']['statut'] = $idStatut['nom'];

		setcookie('auth', $key, time() + 3600 * 24 * 1, '/', 'localhost', false, true);
	}
}