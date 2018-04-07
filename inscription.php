<?php
	$title = "Inscription";

	include_once "includes/db.php";
	include_once "includes/functions.php";
	include_once("PHPMailer/PHPMailerAutoload.php");

  if (connecte()) {
    ?><script type="text/javascript">javascript:history.back();</script><?php
    header('Location: 404.php');
    die();
  }

	/* Traitement du formulaire */
	$errors = array();

  // Formulaire complet
  if (!empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['email']) && !empty($_POST['confirm_email'])
     && !empty($_POST['password']) && !empty($_POST['confirm_password']) && !empty($_POST['genre']) && !empty($_POST['statut'])) {

    // Prénom
    if (strlen($_POST['prenom']) > 255)
      $errors['taillePrenom'] = "Votre prénom est trop long.";
    if (is_numeric($_POST['prenom']))
      $errors['prenomNombre'] = "Votre prénom n'est pas une chaine de caractères.";
    if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['prenom']))
      $errors['prenom'] = "Votre prénom n'est pas valide.";

    // Nom
    if (strlen($_POST['nom']) > 255)
      $errors['tailleNom'] = "Votre nom est trop long.";
    if (is_numeric($_POST['nom']))
      $errors['nomNombre'] = "Votre nom n'est pas une chaine de caractères.";
    if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
      $errors['nom'] = "Votre nom n'est pas valide.";

    // Email
    $result = $db->query('SELECT email FROM utilisateur');
    while ($data = $result->fetch()) {
      if (strcasecmp($data['email'], $_POST['email']) == 0)
        $errors['emailUtilise'] = "Votre email est déjà utilisé.";
    }
    if (strlen($_POST['email']) > 255)
      $errors['tailleEmail'] = "Votre email est trop long.";
    if (strcmp($_POST['email'], $_POST['confirm_email']) !== 0)
      $errors['emailDifferent'] = "Votre email et sa confirmation sont différents.";
    if (!preg_match("#^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $_POST['email']))
      $errors['email'] = "Votre email n'est pas un email.";

    // Password
    if (strlen($_POST['password']) > 255)
      $errors['taillePassword'] = "Votre mot de passe est trop long.";
    if (strcmp($_POST['password'], $_POST['confirm_password']) !== 0)
      $errors['passwordDifferent'] = "Votre mot de passe et sa confirmation sont différents.";

    // Gender
    if ($_POST['genre'] != "Homme" && $_POST['genre'] != "Femme")
      $errors['genre'] = "Ce genre n'existe pas.";

    // Statut
    $request = $db->prepare('SELECT * FROM statut WHERE id = :idStatut');
    $request->execute(array(
      'idStatut' => $_POST['statut']
    ));
    $donnees = $request->fetch();
    if (!$donnees)
      $errors['statut'] = "Ce statut n'existe pas.";

    
    // Erreur
    if (empty($errors)) {
      // Sauvegarde d'une image par défaut
      $randomString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      $randomString = str_shuffle(str_repeat($randomString, 60));
      $randomString = substr($randomString, 0, 60);

      // Récupération de l'id statut
      $request = $db->prepare('SELECT id FROM statut WHERE id = :statut');
      $request->execute(array(
        'statut'        => $_POST['statut']
      ));
      $idStatut = $request->fetch();

      $request = $db->prepare('INSERT INTO utilisateur(prenom, nom, email, mdp, genre, idStatut, validationkey) VALUES(:prenom, :nom, :email, :password, :genre, :statut, :validationkey)');
      $request->execute(array(
        'prenom'        => $_POST['prenom'],
        'nom'           => $_POST['nom'],
        'email'         => $_POST['email'],
        'password'      => sha1($grain.$_POST['password'].$salt),
        'genre'         => $_POST['genre'],
        'statut'        => $idStatut['id'],
        'validationkey' => $randomString
      ));

      // Id nouvelle utilisateur (= last user)
      $request = $db->query('SELECT MAX(id) AS idMax FROM utilisateur');
      $result = $request->fetch();

      // Send mail
      $mail = new PHPMailer();

      $subject = "Activation de votre compte Brain'squiz !";
      $body = 'Bienvenue sur notre plateforme Brain\'squiz !,
      Pour activer votre compte, cliquez sur le lien suivant :
      http://localhost/projet_transverse/activation.php?id=' . $result['idMax'] . '&validationkey=' . $randomString . '';

      $from_name = "Brain'squiz";
      $mail->Username = "faabs.brain.squiz@gmail.com";  
      $mail->Password = "azerty123456";           

      $mail->IsSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPDebug = 0;
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = 'ssl';
      $mail->Host = 'smtp.gmail.com';
      $mail->Port = 465; 
      $mail->SetFrom($mail->Username, $from_name);
      $mail->Subject = $subject;
      $mail->Body = $body;
      $mail->addAddress($_POST['email']);
      $mail->SMTPOptions = array(
          'ssl' => array(
              'verify_peer' => false,
              'verify_peer_name' => false,
              'allow_self_signed' => true
          )
      );
      $mail->send();

      $_SESSION['flash']['success'] = "Un mail de confirmation vous a été envoyé.";
    }
  } else if(isset($_POST['prenom']) || isset($_POST['nom']) || isset($_POST['email']) || isset($_POST['confirm_email'])
     || isset($_POST['password']) || isset($_POST['confirm_password']) || isset($_POST['genre']) || isset($_POST['statut'])) {
    $_SESSION['flash']['danger'] = "Le formulaire n'est pas complet.";
  }

  include_once "includes/header.php";
?>

<!-- Header Page -->
<div class="container-fluid background-texture">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="header-page">Inscription</h2>
			</div>
		</div>
	</div>
</div>
<!-- End Header Page -->

<!-- Breadcrumb -->
<div class="container-fluid background-lightgrey">
  <div class="container">
    <div class="row">
      <?php
        $fil = array('inscription.php' => 'Inscription');

        fil_ariane($fil);
      ?>
    </div>
  </div>
</div>
<!-- End Breadcrumb -->

<!-- Content -->
<div class="container">
  <?php echo flash(); ?>
	<div class="row">
		<form enctype="multipart/form-data" class="form-horizontal" method="post" action="inscription.php">
			<fieldset>
				<!-- Form Name -->
				<h4 class="page-header">Créez un compte en quelques clics !</h4>

				<!-- Prénom -->
				<div class="form-group <?php if (isset($errors['taillePrenom']) || isset($errors['prenomNombre']) || isset($errors['prenom'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="prenom">Prénom</label>  
					<div class="col-md-5">
						<input id="prenom" name="prenom" type="text" placeholder="Prénom" class="form-control input-md" required="">
					  <span class="help-block">
              <?php
              if (isset($errors['taillePrenom']))
                echo strip_tags(htmlspecialchars($errors['taillePrenom']));
              if (isset($errors['prenomNombre']))
                echo strip_tags(htmlspecialchars($errors['prenomNombre']));
              if (isset($errors['prenom']))
                echo strip_tags(htmlspecialchars($errors['prenom']));
              ?>
            </span>
          </div>
				</div>
				<!-- Nom -->
				<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nom'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="nom">Nom</label>  
					<div class="col-md-5">
						<input id="nom" name="nom" type="text" placeholder="Nom" class="form-control input-md" required="">
					  <span class="help-block">
            <?php
            if (isset($errors['tailleNom']))
              echo strip_tags(htmlspecialchars($errors['tailleNom']));
            if (isset($errors['nomNombre']))
              echo strip_tags(htmlspecialchars($errors['nomNombre']));
            if (isset($errors['nom']))
              echo strip_tags(htmlspecialchars($errors['nom']));
            ?>
            </span>
          </div>
				</div>
				<!-- Email-->
				<div class="form-group <?php if (isset($errors['tailleEmail']) || isset($errors['emailUtilise']) || isset($errors['email'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="email">Email</label>  
					<div class="col-md-5">
						<input id="email" name="email" type="email" placeholder="user@domain.com" class="form-control input-md" required="">
					  <span class="help-block">
            <?php
            if (isset($errors['tailleEmail']))
              echo strip_tags(htmlspecialchars($errors['tailleEmail']));
            if (isset($errors['emailUtilise']))
              echo strip_tags(htmlspecialchars($errors['emailUtilise']));
            if (isset($errors['email']))
              echo strip_tags(htmlspecialchars($errors['email']));
            ?>
            </span>
          </div>
				</div>
				<!-- Confirmation Email -->
				<div class="form-group <?php if (isset($errors['emailDifferent'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="confirm_email">Confirmation de l'Email</label>
					<div class="col-md-5">
						<input id="confirm_email" name="confirm_email" type="email" placeholder="Retapez email" class="form-control input-md" required="">
					  <span class="help-block">
            <?php
            if (isset($errors['emailDifferent']))
              echo strip_tags(htmlspecialchars($errors['emailDifferent']));
            ?>
            </span>
          </div>
				</div>
				<!-- Mot de passe -->
				<div class="form-group <?php if (isset($errors['taillePassword'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="password">Mot de passe</label>
					<div class="col-md-5">
						<input id="password" name="password" type="password" placeholder="Mot de passe" class="form-control input-md" required="">
					  <span class="help-block">
            <?php
            if (isset($errors['taillePassword']))
              echo strip_tags(htmlspecialchars($errors['taillePassword']));
            ?>
            </span>
          </div>
				</div>
				<!-- Confirmation mot de passe -->
				<div class="form-group <?php if (isset($errors['passwordDifferent'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="confirm_password">Confirmation du mot de passe</label>
					<div class="col-md-5">
						<input id="confirm_password" name="confirm_password" type="password" placeholder="Retapez le mot de passe" class="form-control input-md" required="">
					  <span class="help-block">
            <?php
            if (isset($errors['passwordDifferent']))
              echo strip_tags(htmlspecialchars($errors['passwordDifferent']));
            ?>
            </span>
          </div>
				</div>
				<!-- Genre -->
				<div class="form-group <?php if (isset($errors['genre'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="genre">Genre</label>
					<div class="col-md-4"> 
						<label class="radio-inline" for="genre-0">
							<input type="radio" name="genre" id="genre-0" value="Homme" checked="checked">Homme
						</label> 
						<label class="radio-inline" for="genre-1">
							<input type="radio" name="genre" id="genre-1" value="Femme">Femme
						</label>
            <span class="help-block">
            <?php
            if (isset($errors['genre']))
              echo strip_tags(htmlspecialchars($errors['genre']));
            ?>
            </span>
					</div>
				</div>
				<!-- Statut -->
				<div class="form-group <?php if (isset($errors['statut'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="statut">Êtes-vous :</label>
          <div class="col-md-5">
						<select id="statut" name="statut" class="form-control">
              <?php
              $request = $db->query("SELECT * FROM statut WHERE nom != 'administrateur'");
              while ($donnees = $request->fetch()) {
                echo '<option value="' . $donnees['id'] . '">' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
              }
              ?>
            </select>
            <span class="help-block">
            <?php
              if (isset($errors['statut']))
                echo strip_tags(htmlspecialchars($errors['statut']));
            ?>
            </span>
          </div>
				</div>

				<!-- Accept terms -->
				<!-- Captcha -->

				<!-- Button -->
				<div class="form-group text-center">
					<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
						<button name="submit" class="btn btn-primary btn-block">Submit</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>