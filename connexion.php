<?php
include_once "includes/db.php";
include_once "includes/functions.php";

if (connecte()) {
  ?><script type="text/javascript">javascript:history.back();</script><?php
  header('Location: 404.php');
  die();
}

if (!empty($_POST['email']) && !empty($_POST['password'])) {
  $email = $_POST['email'];
  $password = sha1($grain.$_POST['password'].$salt);

  $request = $db->prepare('SELECT * FROM utilisateur WHERE email = :email AND mdp = :password AND active IS NOT NULL');
  $request->execute(array(
    'email' => $email,
    'password' => $password
  ));
  $data = $request->fetch();

  if ($data) {
    if (isset($_POST['remember'])) {
      setcookie('auth', $data['id'] . "----" . sha1($data['email'] . $data['mdp'] . $_SERVER['REMOTE_ADDR']), time() + 3600 * 24 * 1, '/', 'localhost', false, true);
    }

    $_SESSION['auth'] = $data;

    $request = $db->prepare('SELECT nom FROM statut WHERE id = :idStatut');
    $request->execute(array(
      'idStatut' => $data['idStatut']
    ));
    $idStatut = $request->fetch();
    $_SESSION['auth']['statut'] = $idStatut['nom'];

    $_SESSION['flash']['success'] = "Vous êtes maintenant connecté.";
    ?><script type="text/javascript">javascript:history.back();</script><?php
    header('Location: index.php');
    die();
  } else {
    $_SESSION['flash']['danger'] = "Erreur lors de votre authentification.";
    ?><script type="text/javascript">javascript:history.back();</script><?php
    header('Location: index.php');
    die();
  }
} else {
    $_SESSION['flash']['danger'] = "Veuillez remplir tous les champs.";
    ?><script type="text/javascript">javascript:history.back();</script><?php
    header('Location: index.php');
    die();
  }
?>