<?php
	$title = "Paramètres";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (!connecte()) {
		header('Location: 404.php');
		die();
	}

	$request = $db->prepare("SELECT utilisateur.id, utilisateur.prenom, utilisateur.nom, utilisateur.email, utilisateur.mdp, utilisateur.genre, utilisateur.idStatut, utilisateur.idParent, statut.nom AS statut FROM utilisateur, statut WHERE utilisateur.id = :id AND utilisateur.idStatut = statut.id");
	$request->execute(array(
		'id' => $_GET['id']
	));
	$data = $request->fetch();

	if (!$data) {
		?><script type="text/javascript">javascript:history.back();</script><?php
	    header('Location: 404.php');
		die();
	}

	$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
	$request->execute(array(
		'idStatut' => $_SESSION['auth']['idStatut']
	));
	$acces = $request->fetch();

	if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
		if ($acces['supprimerUtilisateur'] == '0') {
			header('Location: 404.php');
			die();
		}

		if ($_GET['id'] != $_SESSION['auth']['id']) {
			$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :id AND idParent = :idParent');
			$request->execute(array(
				'id' 		=> $data['id'],
				'idParent'	=> $_SESSION['auth']['id']
			));
			$donnees = $request->fetch();

			if (!$donnees && !statut('administrateur')) {
				header('Location: 404.php');
				die();
			}
		} else {
			header('Location: 404.php');
			die();
		}
	} else {
		if ($acces['modifierUtilisateur'] == '0' && $_GET['id'] != $_SESSION['auth']['id']) {
			header('Location: 404.php');
			die();
		}

		if ($_GET['id'] != $_SESSION['auth']['id']) {
			$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :id AND idParent = :idParent');
			$request->execute(array(
				'id' 		=> $data['id'],
				'idParent'	=> $_SESSION['auth']['id']
			));
			$donnees = $request->fetch();

			if (!$donnees && !statut('administrateur')) {
				header('Location: 404.php');
				die();
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == "supprimer") {
		if (isset($_POST['oui'])) {
			// Suppression d'un utilisateur
			$request = $db->prepare("SELECT nom FROM statut WHERE id = :idStatut");
			$request->execute(array(
				'idStatut' => $data['idStatut']
			));
			$donnees = $request->fetch();

			if ($donnees['nom'] == 'administrateur') {
				$request = $db->query("SELECT COUNT(utilisateur.id) AS nbAdministrateur FROM utilisateur, statut WHERE utilisateur.idStatut = statut.id AND statut.nom = 'administrateur'");
				$donnees = $request->fetch();

				if ($donnees['nbAdministrateur'] > 1) {
					$request = $db->prepare("DELETE FROM utilisateur WHERE id = :id");
					$request->execute(array(
						'id' => $data['id']
					));
				} else {
					$_SESSION['flash']['danger'] = "Vous ne pouvez pas supprimer cet administrateur.";
					header('Location: profil.php?id=' . $data['id']);
					die();
				}
			} else {
				// Suppression de l'utilisateur (cours, quiz, établissement, classe ...)
				$request = $db->prepare("DELETE FROM utilisateur WHERE id = :id");
				$request->execute(array(
					'id' => $data['id']
				));

				$request = $db->prepare("DELETE FROM affiliation WHERE idUtilisateur = :id");
				$request->execute(array(
					'id' => $data['id']
				));

				// ... A voir
			}

			header('Location: index.php');
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: profil.php?id=' . $data['id']);
			die();
		}
	} else {
		/* Traitement du formulaire */
		$errors = array();
		$inputs = array();

		if ($_GET['id'] == $_SESSION['auth']['id']) {
			$inputs = $_SESSION['auth'];
		} else {
			$inputs = $data;
		}

	  	// Formulaire complet
		if (isset($_POST['submit'])) {
			// Prénom
			if (!empty($_POST['prenom']) && strcmp($_POST['prenom'], $inputs['prenom']) !== 0) {
				if (strlen($_POST['prenom']) > 255)
					$errors['taillePrenom'] = "Votre prénom est trop long.";
				if (is_numeric($_POST['prenom']))
					$errors['prenomNombre'] = "Votre prénom n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['prenom']))
					$errors['prenom'] = "Votre prénom n'est pas valide.";

				if (!isset($errors['taillePrenom']) && !isset($errors['prenomNombre']) && !isset($errors['prenom'])) {
					$inputs['prenom'] = $_POST['prenom'];
				}
			}

			// Nom
			if (!empty($_POST['nom']) && strcmp($_POST['nom'], $inputs['nom']) !== 0) {
				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Votre nom est trop long.";
				if (is_numeric($_POST['nom']))
					$errors['nomNombre'] = "Votre nom n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Votre nom n'est pas valide.";

				if (!isset($errors['tailleNom']) && !isset($errors['nomNombre']) && !isset($errors['nom'])) {
					$inputs['nom'] = $_POST['nom'];
				}
			}

			// Email
			if (!empty($_POST['email']) && strcmp($_POST['email'], $inputs['email']) !== 0) {
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

				if (!isset($errors['emailUtilise']) && !isset($errors['tailleEmail']) && !isset($errors['emailDifferent']) && !isset($errors['email'])) {
		        	$inputs['email'] = $_POST['email'];
		      	}
			}

			// Password
			if (!empty($_POST['password']) && strcmp(sha1($grain.$_POST['password'].$salt), $inputs['mdp']) !== 0) {
				if (strlen($_POST['password']) > 255)
					$errors['taillePassword'] = "Votre mot de passe est trop long.";
				if (strcmp($_POST['password'], $_POST['confirm_password']) !== 0)
					$errors['passwordDifferent'] = "Votre mot de passe et sa confirmation sont différents.";

				if (!isset($errors['taillePassword']) && !isset($errors['passwordDifferent'])) {
		        	$inputs['mdp'] = sha1($grain.$_POST['password'].$salt);
		      	}
			}

			// Gender
			if (!empty($_POST['genre']) && strcmp($_POST['genre'], $inputs['genre']) !== 0) {
				if ($_POST['genre'] != "Homme" && $_POST['genre'] != "Femme")
					$errors['genre'] = "Ce genre n'existe pas.";

				if (!isset($errors['genre'])) {
	        		$inputs['genre'] = $_POST['genre'];
	     		}
			}

			// Statut
			if (statut('administrateur')) {
				if (!empty($_POST['statut']) && strcmp($_POST['statut'], $inputs['idStatut']) !== 0) {
					$result = $db->prepare('SELECT * FROM statut WHERE id = :id');
					$request->execute(array(
						'id' => $_POST['statut']
					));
					$donnees = $request->fetch();

					if (!$donnees) {
						$errors['statut'] = "Ce statut n'existe pas.";
					}

					if (!isset($errors['statut'])) {
		       			$inputs['idStatut'] = $_POST['statut'];
		      		}
				}
			}
			

			// Picture
			if (!empty($_FILES['picture']['name']) AND $_FILES['picture']['error'] == 0) {
				if ($_POST['MAX_FILE_SIZE'] != 1048576)
					$_POST['MAX_FILE_SIZE'] = 1048576;

				if ($_FILES['picture']['size'] <= $_POST['MAX_FILE_SIZE']) {
					$ext = strtolower(substr($_FILES['picture']['name'], -3));
					$allow_ext = array('jpeg', 'jpg', 'png', 'gif', 'JPEG', 'JPG', 'PNG', 'GIF');

					if (in_array($ext, $allow_ext)) {
						$pictureName = $inputs['id'] . "." . $ext;
						$currentExt;
						$suppression = true;

						if (file_exists('img/avatars/' . $inputs['id'] . '.jpg')) {
							$extension = explode('.', $inputs['id'] . '.jpg');
							$currentExt = $extension[1];
						} else if (file_exists('img/avatars/' . $inputs['id'] . '.jpeg')) {
							$extension = explode('.', $inputs['id'] . '.jpeg');
							$currentExt = $extension[1];
						} else if (file_exists('img/avatars/' . $inputs['id'] . '.png')) {
							$extension = explode('.', $inputs['id'] . '.png');
							$currentExt = $extension[1];
						} else if (file_exists('img/avatars/' . $inputs['id'] . '.gif')) {
							$extension = explode('.', $inputs['id'] . '.gif');
							$currentExt = $extension[1];
						} else {
							$suppression = false;
						}

						if (!move_uploaded_file($_FILES['picture']['tmp_name'], "img/avatars/" . strtolower($pictureName))) {
							$errors['imageUpload'] = "Une erreur est survenue lors du téléchargement.";
							unset($pictureName);
						} else {
							if ($suppression == true && strcasecmp($currentExt, $ext[1]) != 0) {
								unlink("img/avatars/" . $inputs['id'] . '.' . $currentExt);
							}
						}
					} else {
						$errors['imageExtension'] = "Votre fichier n'a pas la bonne extension.";
					}
				} else {
					$errors['tailleImage'] = "Votre fichier est trop volumineux.";
				}
			}

			// Erreur
			if (empty($errors)) {
				if ($_GET['id'] == $_SESSION['auth']['id']) {
					$request = $db->prepare('UPDATE utilisateur SET prenom = :prenom, nom = :nom, email = :email, mdp = :mdp, genre = :genre WHERE id = :id');
			      	$request->execute(array(
				        'prenom'  		=> $inputs['prenom'],
				        'nom'    		=> $inputs['nom'],
				        'email'       	=> $inputs['email'],
				        'mdp'			=> $inputs['mdp'],
				        'genre'     	=> $inputs['genre'],
				        'id'         	=> $_SESSION['auth']['id']
			      	));
			    } else {
			    	$request = $db->prepare('UPDATE utilisateur SET prenom = :prenom, nom = :nom, email = :email, genre = :genre, idStatut = :idStatut WHERE id = :id');
			      	$request->execute(array(
				        'prenom'  		=> $inputs['prenom'],
				        'nom'    		=> $inputs['nom'],
				        'email'       	=> $inputs['email'],
				        'genre'     	=> $inputs['genre'],
						'idStatut'		=> $inputs['idStatut'],
				        'id'         	=> $inputs['id']
			      	));
			    }

		      	if ($_GET['id'] == $_SESSION['auth']['id']) {
		      		$_SESSION['auth'] = $inputs;
		      		$data = $inputs;
		      	} else {
		      		$data = $inputs;
		      	}

				$_SESSION['flash']['success'] = "Les informations ont été modifiées avec succès.";
			} else {
				$data = $inputs;
			}
		}
	}

	include_once "includes/header.php";
?>

<!-- Header Page -->
<div class="container-fluid background-texture">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="header-page">Paramètres</h2>
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
			if (isset($_GET['id'])) {
				$fil['profil.php?id=' . $_GET['id']] = 'Profil : ' . $data['prenom'] . ' ' . $data['nom'];
  				$fil['parametres.php?id=' . $_GET['id']] = 'Paramètres : ' . $data['prenom'] . ' ' . $data['nom'];
  			}
		  	fil_ariane($fil);
			?>
		</div>
	</div>
</div>
<!-- End Breadcrumb -->

<!-- Content -->
<div class="container">
	<?php echo flash();
	if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
	?>
		<div class="row">
			<div class="col-lg-12 page-header">
				<h3>Suppression de : <?php echo strip_tags(htmlspecialchars($data['prenom'])) . ' ' . strip_tags(htmlspecialchars($data['nom'])); ?></h3>
			</div>
		</div>
		<div class="row text-center">
			<p>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
			<form enctype="multipart/form-data" class="form-horizontal" method="post" action="parametres.php?id=<?php echo $data['id']; ?>&action=supprimer">
				<fieldset>
					<div class="form-group text-center">
						<div class="col-xs-6 col-sm-offset-2 col-sm-4 col-md-offset-3 col-md-3 col-lg-offset-3 col-lg-3">
							<button name="oui" class="btn btn-primary btn-block">Oui</button>
						</div>
						<div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
							<button name="non" class="btn btn-primary btn-block">Non</button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
		<?php
	} else {
	?>
	<div class="row">
		<form class="form-horizontal" method="post" action="parametres.php?id=<?php echo $data['id']; ?>" enctype="multipart/form-data">
			<fieldset>
				<!-- Form Name -->
				<h4 class="page-header">Modification des informations personnelles : <?php echo strip_tags(htmlspecialchars($data['prenom'])) . ' ' . $data['nom']; ?></h4>

				<!-- Prénom -->
				<div class="form-group <?php if (isset($errors['taillePrenom']) || isset($errors['prenomNombre']) || isset($errors['prenom'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="prenom">Prénom</label>  
					<div class="col-md-5">
						<input id="prenom" name="prenom" type="text" placeholder="Prénom" class="form-control input-md"  value="<?php echo strip_tags(htmlspecialchars($data['prenom'])); ?>">
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
						<input id="nom" name="nom" type="text" placeholder="Nom" class="form-control input-md"  value="<?php echo strip_tags(htmlspecialchars($data['nom'])); ?>">
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
						<input id="email" name="email" type="email" placeholder="user@domain.com" class="form-control input-md"  value="<?php echo strip_tags(htmlspecialchars($data['email'])); ?>">
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
						<input id="confirm_email" name="confirm_email" type="email" placeholder="Retapez email" class="form-control input-md">
					  	<span class="help-block">
				            <?php
				            if (isset($errors['emailDifferent']))
				              echo strip_tags(htmlspecialchars($errors['emailDifferent']));
				            ?>
			            </span>
			        </div>
				</div>
				<?php
				if ($_GET['id'] == $_SESSION['auth']['id']) {
				?>
				<!-- Mot de passe -->
				<div class="form-group <?php if (isset($errors['taillePassword'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="password">Mot de passe</label>
					<div class="col-md-5">
						<input id="password" name="password" type="password" placeholder="Mot de passe" class="form-control input-md">
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
						<input id="confirm_password" name="confirm_password" type="password" placeholder="Retapez le mot de passe" class="form-control input-md">
					  	<span class="help-block">
				            <?php
				            if (isset($errors['passwordDifferent']))
				              echo strip_tags(htmlspecialchars($errors['passwordDifferent']));
				            ?>
			            </span>
			        </div>
				</div>
				<?php } ?>
				<!-- Genre -->
				<div class="form-group <?php if (isset($errors['genre'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="genre">Genre</label>
					<div class="col-md-4"> 
						<label class="radio-inline" for="genre-0">
							<input type="radio" name="genre" id="genre-0" value="Homme" <?php if ($data['genre'] == 'Homme') echo 'checked="checked"'; ?>>Homme
						</label> 
						<label class="radio-inline" for="genre-1">
							<input type="radio" name="genre" id="genre-1" value="Femme" <?php if ($data['genre'] == 'Femme') echo 'checked="checked"'; ?>>Femme
						</label>
			            <span class="help-block">
				            <?php
				            if (isset($errors['genre']))
				              echo strip_tags(htmlspecialchars($errors['genre']));
				            ?>
			            </span>
					</div>
				</div>
				<!-- Avatar -->
				<div class="form-group <?php if (isset($errors['tailleImage']) || isset($errors['erreurImage']) || isset($errors['imageExtension']) || isset($errors['imageUpload']) || isset($errors['copieImage'])) echo "has-error"; ?>">
					<label class="col-md-4 control-label" for="picture">Image de profil : </label>
					<div class="col-md-4">
						<input type="hidden" name="MAX_FILE_SIZE" value="1048576">
						<input id="picture" name="picture" type="file" class="form-control input-md">
						<span class="help-block">
						<?php
						if (isset($errors['tailleImage']))
							echo strip_tags(htmlspecialchars($errors['tailleImage']));
						if (isset($errors['erreurImage']))
							echo strip_tags(htmlspecialchars($errors['erreurImage']));
						if (isset($errors['imageExtension']))
							echo strip_tags(htmlspecialchars($errors['imageExtension']));
						if (isset($errors['imageUpload']))
							echo strip_tags(htmlspecialchars($errors['imageUpload']));
						if (isset($errors['copieImage']))
							echo strip_tags(htmlspecialchars($errors['copieImage']));
						?>
						</span>
					</div>
				</div>

				<?php
				if (statut('administrateur')) {
					// Modifier le statut
				?>
					<div class="form-group <?php if (isset($errors['statut'])) echo "has-error"; ?>">
						<label class="col-md-4 control-label" for="statut">Statut de l'utilisateur :</label>
						<div class="col-md-5">
							<select id="statut" name="statut" class="form-control">
							<?php
								$request = $db->query('SELECT * FROM statut ORDER BY level');
								while ($donnees = $request->fetch()) {
									echo '<option value="' . $donnees['id'] . '"';
									if ($data['idStatut'] == $donnees['id'])
										echo 'selected="selected"';
									echo '>' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
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
				<?php
				}
				?>

				<!-- Accept terms -->
				<!-- Captcha -->

				<!-- Button -->
				<div class="form-group text-center">
					<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
						<button name="submit" class="btn btn-primary btn-block">Modifier</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
	<?php } ?>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>