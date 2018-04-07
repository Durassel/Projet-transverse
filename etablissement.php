<?php
	$title = "Etablissement";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (isset($_GET['id'])) {
		$request = $db->prepare("SELECT * FROM etablissement WHERE id = :id");
		$request->execute(array(
			'id' => $_GET['id']
		));
		$data = $request->fetch();

		if (!$data) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'modifier' && $_GET['action'] != 'supprimer') || (isset($_GET['action']) && ($_GET['action'] == 'modifier' || $_GET['action'] == 'supprimer') && !isset($_GET['id']))) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: 404.php');
		die();
	}

	if (isset($_GET['action']) && !connecte()) {
		header('Location: 404.php');
		die();
	}

	if (connecte()) {
		$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
		$request->execute(array(
			'idStatut' => $_SESSION['auth']['idStatut']
		));
		$acces = $request->fetch();

		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
			if ($acces['ajouterEtablissement'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
			if ($acces['modifierEtablissement'] == '0') {
				header('Location: 404.php');
				die();
			}

			$request = $db->prepare('SELECT * FROM etablissement WHERE idDirecteur = :idUtilisateur AND id = :id');
			$request->execute(array(
				'idUtilisateur'		=> $_SESSION['auth']['id'],
				'id' 				=> $data['id']
			));
			$donnees = $request->fetch();

			if (!$donnees && !statut('administrateur')) {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
			if ($acces['supprimerEtablissement'] == '0') {
				header('Location: 404.php');
				die();
			}

			$request = $db->prepare('SELECT * FROM etablissement WHERE idDirecteur = :idUtilisateur AND id = :id');
			$request->execute(array(
				'idUtilisateur'		=> $_SESSION['auth']['id'],
				'id' 				=> $data['id']
			));
			$donnees = $request->fetch();

			if (!$donnees && !statut('administrateur')) {
				header('Location: 404.php');
				die();
			}
		}
	}
	

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['nom']) && !empty($_POST['adresse']) && !empty($_POST['ville']) && !empty($_POST['codePostal']) && !empty($_FILES['picture']) && !empty($_POST['description'])) {
			// Nom
			$request = $db->prepare("SELECT * FROM etablissement WHERE nom = :nom");
			$request->execute(array(
				'nom' => $_POST['nom']
			));
			$donnees = $request->fetch();

			if (strlen($_POST['nom']) > 255)
				$errors['tailleNom'] = "Le nom de l'établissement est trop long.";
			if (is_numeric($_POST['nom']))
				$errors['nomNombre'] = "Le nom de l'établissement n'est pas une chaine de caractères.";
			if (!preg_match('/^[a-zA-Z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
				$errors['nom'] = "Le nom de l'établissement n'est pas valide.";
			if ($donnees) {
				$errors['nomUtilise'] = "Le nom de l'établissement est déjà utilisé.";
			}

			// Adresse
			if (strlen($_POST['adresse']) > 255)
				$errors['tailleAdresse'] = "Votre adresse est trop longue.";
			if (is_numeric($_POST['adresse']))
				$errors['adresseNombre'] = "Votre adresse n'est pas une chaine de caractères.";

			// Ville
			if (strlen($_POST['ville']) > 255)
				$errors['tailleVille'] = "Le nom de votre ville est trop long.";
			if (is_numeric($_POST['ville']))
				$errors['villeNombre'] = "Le nom de votre ville n'est pas une chaine de caractères.";

			// Code postal
		    $codePostalType = array(
		      "France" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$"
		    );

		    if (array_key_exists("France", $codePostalType)) {
		      if (!preg_match("/" . $codePostalType["France"] . "/i", $_POST['codePostal']))
		        $errors['codePostal'] = "Votre code postal est invalide.";
		    }

			// Directeur
			if (!empty($_POST['directeur'])) { // Vérifier existance du directeur
				if (!statut('administrateur')) {
					if ($_POST['directeur'] != $_SESSION['auth']['id']) {
						$errors['directeur'] = "Vous devez être le directeur de l'établissement.";
					}
				} else {
				    $request = $db->prepare("SELECT * FROM utilisateur, statut WHERE utilisateur.id = :id AND utilisateur.idStatut = statut.id AND statut.nom = 'directeur'");
					$request->execute(array(
						'id' => $_POST['directeur']
					));
					$donnees = $request->fetch();

					if (!$donnees) {
						$errors['directeur'] = "Ce directeur n'existe pas.";
					}
				}
			} else {
				$_POST['directeur'] = '0';
			}

			// Picture
			if (!empty($_FILES['picture']['name']) AND $_FILES['picture']['error'] == 0) {
				if ($_POST['MAX_FILE_SIZE'] != 1048576)
					$_POST['MAX_FILE_SIZE'] = 1048576;

				if ($_FILES['picture']['size'] <= $_POST['MAX_FILE_SIZE']) {
					$ext = strtolower(substr($_FILES['picture']['name'], -3));
					$allow_ext = array('jpeg', 'jpg', 'png', 'gif', 'JPEG', 'JPG', 'PNG', 'GIF');

					if (in_array($ext, $allow_ext)) {
				        $request = $db->query('SELECT MAX(id) FROM etablissement');
				        $result = $request->fetch();
				        $result[0]++;
				        $pictureName = $result[0] . "." . $ext;

						if (!move_uploaded_file($_FILES['picture']['tmp_name'], "img/etablissements/" . strtolower($pictureName))) {
							$errors['imageUpload'] = "Une erreur est survenue lors du téléchargement.";
							unset($pictureName);
						}
					} else {
						$errors['imageExtension'] = "Votre fichier n'a pas la bonne extension.";
					}
				} else {
					$errors['tailleImage'] = "Votre fichier est trop volumineux.";
				}
			}

			// Description : rien à vérifier

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('INSERT INTO etablissement(nom, adresse, ville, codePostal, idDirecteur, description) VALUES(:nom, :adresse, :ville, :codePostal, :idDirecteur, :description)');
		      	$request->execute(array(
			        'nom' 			=> $_POST['nom'], 
			        'adresse' 		=> $_POST['adresse'],
			        'ville'			=> $_POST['ville'],
			        'codePostal' 	=> $_POST['codePostal'],
			        'idDirecteur' 	=> $_POST['directeur'],
			        'description'	=> $_POST['description']
		      	));

		      	$request = $db->query('SELECT MAX(id) AS idEtablissement FROM etablissement');
		      	$donnees = $request->fetch();


		      	$request = $db->prepare('INSERT INTO affiliation(idUtilisateur, idClasse, idEtablissement) VALUES(:idUtilisateur, :idClasse, :idEtablissement)');
		      	$request->execute(array(
			        'idUtilisateur' 	=> $_POST['directeur'], 
			        'idClasse' 			=> 0,
			        'idEtablissement'	=> $donnees['idEtablissement']
		      	));

				$_SESSION['flash']['success'] = "L'établissement a été ajouté avec succès.";
				header('Location: etablissement.php');
	    		die();
			} else {
			    if (isset($pictureName))
			      unlink("img/etablissements/" . $pictureName);
		    }
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_POST)) {
		$errors = array();
		$inputs = array();

		$inputs = $data;

		if (isset($_POST['submit'])) {
			// Nom
			if (!empty($_POST['nom']) && strcmp($_POST['nom'], $inputs['nom']) !== 0) {
				$request = $db->prepare("SELECT nom FROM theme WHERE nom = :nom");
				$request->execute(array(
					'nom' => $_POST['nom']
				));
				$donnees = $request->fetch();

				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Le nom du thème est trop long.";
				if (is_numeric($_POST['nom']))
					$errors['nomNombre'] = "Le nom du thème n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Le nom du thème n'est pas valide.";
				if ($donnees) {
					$errors['nomUtilise'] = "Le nom du thème est déjà utilisé.";
				}

				if (!isset($errors['tailleNom']) && !isset($errors['nomNombre']) && !isset($errors['nom']) && !isset($errors['nomUtilise'])) {
					$inputs['nom'] = $_POST['nom'];
				}
			}

			// Adresse
			if (!empty($_POST['adresse']) && strcmp($_POST['adresse'], $inputs['adresse']) !== 0) {
				if (strlen($_POST['adresse']) > 255)
					$errors['tailleAdresse'] = "Votre adresse est trop longue.";
				if (is_numeric($_POST['adresse']))
					$errors['adresseNombre'] = "Votre adresse n'est pas une chaine de caractères.";

				if (!isset($errors['tailleAdresse']) && !isset($errors['adresseNombre'])) {
					$inputs['adresse'] = $_POST['adresse'];
				}
			}

			// Ville
			if (!empty($_POST['ville']) && strcmp($_POST['ville'], $inputs['ville']) !== 0) {
				if (strlen($_POST['ville']) > 255)
					$errors['tailleVille'] = "Le nom de votre ville est trop long.";
				if (is_numeric($_POST['ville']))
					$errors['villeNombre'] = "Le nom de votre ville n'est pas une chaine de caractères.";

				if (!isset($errors['tailleVille']) && !isset($errors['villeNombre'])) {
					$inputs['ville'] = $_POST['ville'];
				}
			}

			// Code postal
			if (!empty($_POST['codePostal']) && strcmp($_POST['codePostal'], $inputs['codePostal']) !== 0) {
			    $codePostalType = array(
			      "France" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$"
			    );

			    if (array_key_exists("France", $codePostalType)) {
			      if (!preg_match("/" . $codePostalType["France"] . "/i", $_POST['codePostal']))
			        $errors['codePostal'] = "Votre code postal est invalide.";
			    }

			    if (!isset($errors['codePostal'])) {
					$inputs['codePostal'] = $_POST['codePostal'];
				}
			}

			// Directeur
			if (!empty($_POST['directeur'])) { // Vérifier existance du directeur
				if (!statut('administrateur')) {
					if (strcmp($_POST['directeur'], $inputs['idDirecteur']) !== 0) {
						if ($_POST['directeur'] != $_SESSION['auth']['id']) {
							$errors['directeur'] = "Vous devez être le directeur de l'établissement.";
						}

						if (!isset($errors['directeur'])) {
							$inputs['idDirecteur'] = $_POST['directeur'];
						}
					}
				} else {
					if (strcmp($_POST['directeur'], $inputs['idDirecteur']) !== 0) {
					    $request = $db->prepare("SELECT * FROM utilisateur, statut WHERE utilisateur.id = :id AND utilisateur.idStatut = statut.id AND statut.nom = 'directeur'");
						$request->execute(array(
							'id' => $_POST['directeur']
						));
						$donnees = $request->fetch();

						if (!$donnees) {
							$errors['directeur'] = "Ce directeur n'existe pas.";
						}

						if (!isset($errors['directeur'])) {
							$inputs['idDirecteur'] = $_POST['directeur'];
						}
					}
				}
			} else {
				$inputs['directeur'] = '0';
			}

			// Picture
			if (!empty($_FILES['picture']['name']) AND $_FILES['picture']['error'] == 0) {
				if ($_POST['MAX_FILE_SIZE'] != 1048576)
					$_POST['MAX_FILE_SIZE'] = 1048576;

				if ($_FILES['picture']['size'] <= $_POST['MAX_FILE_SIZE']) {
					$ext = strtolower(substr($_FILES['picture']['name'], -3));
					$allow_ext = array('jpeg', 'jpg', 'png', 'gif', 'JPEG', 'JPG', 'PNG', 'GIF');

					if (in_array($ext, $allow_ext)) {
						$pictureName =  $inputs['id'] . '.' . $ext;
						$currentExt;

						if (file_exists('img/etablissements/' . $inputs['id'] . '.jpg')) {
							$extension = explode('.', $inputs['id'] . '.jpg');
							$currentExt = $extension[1];
						} else if (file_exists('img/etablissements/' . $inputs['id'] . '.jpeg')) {
							$extension = explode('.', $inputs['id'] . '.jpeg');
							$currentExt = $extension[1];
						} else if (file_exists('img/etablissements/' . $inputs['id'] . '.png')) {
							$extension = explode('.', $inputs['id'] . '.png');
							$currentExt = $extension[1];
						} else if (file_exists('img/etablissements/' . $inputs['id'] . '.gif')) {
							$extension = explode('.', $inputs['id'] . '.gif');
							$currentExt = $extension[1];
						}

						if (!move_uploaded_file($_FILES['picture']['tmp_name'], "img/etablissements/" . strtolower($pictureName))) {
							$errors['imageUpload'] = "Une erreur est survenue lors du téléchargement.";
							unset($pictureName);
						} else {
							if (strcasecmp($currentExt, $ext) != 0) {
								unlink("img/etablissements/" . $inputs['id'] . '.' . $currentExt);
							}
						}
					} else {
						$errors['imageExtension'] = "Votre fichier n'a pas la bonne extension.";
					}
				} else {
					$errors['tailleImage'] = "Votre fichier est trop volumineux.";
				}
			}

			// Description
			if (!empty($_POST['description']) && strcmp($_POST['description'], $inputs['description']) !== 0) {
				if (!isset($errors['description'])) {
					$inputs['description'] = $_POST['description'];
				}
			}

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('UPDATE etablissement SET nom = :nom, adresse = :adresse, ville = :ville, codePostal = :codePostal, idDirecteur = :idDirecteur, description = :description WHERE id = :id');
		      	$request->execute(array(
			        'nom'  			=> $inputs['nom'],
			        'adresse'  		=> $inputs['adresse'],
			        'ville'  		=> $inputs['ville'],
			        'codePostal'  	=> $inputs['codePostal'],
			        'idDirecteur'  	=> $inputs['idDirecteur'],
			        'description'	=> $inputs['description'],
			        'id'			=> $inputs['id']
		      	));

		      	$request = $db->prepare('SELECT idUtilisateur AS idAncienUtilisateur FROM affiliation WHERE idEtablissement = :idEtablissement AND idClasse = 0');
		      	$request->execute(array(
			        'idEtablissement'  		=> $inputs['id']
		      	));
		      	$donnees = $request->fetch();

		      	$request = $db->prepare('UPDATE affiliation SET idUtilisateur = :idUtilisateur WHERE idEtablissement = :idEtablissement AND idUtilisateur = :idAncienUtilisateur');
		      	$request->execute(array(
			        'idUtilisateur'  		=> $inputs['idDirecteur'],
			        'idEtablissement'  		=> $inputs['id'],
			        'idAncienUtilisateur'  	=> $donnees['idAncienUtilisateur']
		      	));

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "L'établissement a été modifié avec succès.";
				header('Location: etablissement.php');
	    		die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression de l'établissement
			$request = $db->prepare("DELETE FROM etablissement WHERE id = :id");
			$request->execute(array(
				'id' => $data['id']
			));

			// Suppression dans la table "affiliation"
			$request = $db->prepare("DELETE FROM affiliation WHERE idEtablissement = :id");
			$request->execute(array(
				'id' => $data['id']
			));
			
			// Les élèves de l'établissement n'ont plus de classe
			$request = $db->prepare("SELECT id FROM classe WHERE idEtablissement = :id");
			$request->execute(array(
				'id' => $data['id']
			));
			while ($donnees = $request->fetch()) {
				$requete = $db->prepare("UPDATE utilisateur SET idClasse = 0 WHERE idClasse = :id");
				$requete->execute(array(
					'id' => $donnees['id']
				));
			}

			// Suppression des classes de l'établissement
			$request = $db->prepare("DELETE FROM classe WHERE idEtablissement = :id");
			$request->execute(array(
				'id' => $data['id']
			));

			// Les professeurs n'appartiennent plus à l'établissement
			$request = $db->prepare("DELETE FROM affiliation WHERE idEtablissement = :id");
			$request->execute(array(
				'id' => $data['id']
			));

			// Suppression de l'image de l'établissement
			if (pictureExist("etablissements", $data['id'] . '.jpg'))
				unlink("img/etablissements/" . $data['id'] . ".jpg");
			if (pictureExist("etablissements", $data['id'] . ".jpeg"))
				unlink("img/etablissements/" . $data['id'] . ".jpeg");
			if (pictureExist("etablissements", $data['id'] . ".png"))
				unlink("img/etablissements/" . $data['id'] . ".png");
			if (pictureExist("etablissements", $data['id'] . ".gif"))
				unlink("img/etablissements/" . $data['id'] . ".gif");

			header('Location: etablissement.php');
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: etablissement.php');
			die();
		}
	}

	include_once "includes/header.php";
?>

<!-- Header Page -->
<div class="container-fluid background-texture">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="header-page">Etablissements</h2>
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
			$fil = array('etablissement.php' => 'Etablissements');
		  	if (isset($_GET['action'])) {
		  		if ($_GET['action'] == 'ajouter')
		  			$fil['etablissement.php?action=ajouter'] = 'Ajout d\'un établissement';
		  		else if ($_GET['action'] == 'modifier')
		  			$fil['etablissement.php?id=' . $_GET['id'] . '&action=modifier'] = 'Modification d\'un établissement : ' . $data['nom'];
		  		else if ($_GET['action'] == 'supprimer')
		  			$fil['classe.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression de l\'établissement : ' . $data['nom'];
		  	} else if (isset($_GET['id'])) {
		  		$fil['etablissement.php?id=' . $_GET['id']] = $data['nom'];
		  	}

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
		<?php
		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Ajout d'un établissement</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="etablissement.php?action=ajouter">
					<fieldset>
						<!-- Nom -->
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom de l'établissement</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom de l'établissement" class="form-control input-md" required="">
							  	<span class="help-block">
						            <?php
						            if (isset($errors['tailleNom']))
						            	echo strip_tags(htmlspecialchars($errors['tailleNom']));
 									if (isset($errors['nomNombre']))
						            	echo strip_tags(htmlspecialchars($errors['nomNombre']));
						            if (isset($errors['nomUtilise']))
						                echo strip_tags(htmlspecialchars($errors['nomUtilise']));
						            if (isset($errors['nom']))
						                echo strip_tags(htmlspecialchars($errors['nom']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Adresse -->
						<div class="form-group <?php if (isset($errors['tailleAdresse']) || isset($errors['adresseNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="adresse">Adresse</label>  
							<div class="col-md-5">
								<input id="adresse" name="adresse" type="text" placeholder="Adresse" class="form-control input-md" required="">
							  	<span class="help-block">
						            <?php
						            	if (isset($errors['tailleAdresse']))
						            		echo strip_tags(htmlspecialchars($errors['tailleAdresse']));
						            	if (isset($errors['adresseNombre']))
						            		echo strip_tags(htmlspecialchars($errors['adresseNombre']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Ville -->
						<div class="form-group <?php if (isset($errors['tailleVille']) || isset($errors['villeNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ville">Ville</label>  
							<div class="col-md-5">
								<input id="ville" name="ville" type="text" placeholder="Ville" class="form-control input-md" required="">
							  	<span class="help-block">
						            <?php
						            	if (isset($errors['tailleVille']))
						            		echo strip_tags(htmlspecialchars($errors['tailleVille']));
						            	if (isset($errors['villeNombre']))
						            		echo strip_tags(htmlspecialchars($errors['villeNombre']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Code postal -->
						<div class="form-group <?php if (isset($errors['codePostal'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="codePostal">Code postal</label>  
							<div class="col-md-5">
								<input id="codePostal" name="codePostal" type="text" placeholder="Code postal" class="form-control input-md" required="">
							  	<span class="help-block">
						            <?php
						            	if (isset($errors['codePostal']))
						            		echo strip_tags(htmlspecialchars($errors['codePostal']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Directeur -->
						<?php
						$request = $db->query('SELECT utilisateur.id AS idDirecteur, utilisateur.prenom AS prenomDirecteur, utilisateur.nom AS nomDirecteur FROM utilisateur, statut WHERE statut.nom = "directeur" AND utilisateur.idStatut = statut.id');
						if ($request->rowCount() > 0) {
						?>
							<div class="form-group <?php if (isset($errors['directeur'])) echo "has-error"; ?>">
								<label class="col-md-4 control-label" for="directeur">Directeur de l'établissement :</label>
								<div class="col-md-5">
									<select id="directeur" name="directeur" class="form-control">
									<?php
										while ($donnees = $request->fetch()) {
											if (!statut('administrateur') && $acces['ajouterEtablissement'] == '1') {
												if ($donnees['idDirecteur'] == $_SESSION['auth']['id'])
													echo '<option value="' . $donnees['idDirecteur'] . '">' . strip_tags(htmlspecialchars($donnees['prenomDirecteur'])) . ' ' . strip_tags(htmlspecialchars($donnees['nomDirecteur'])) . '</option>';
											} else {
												echo '<option value="' . $donnees['idDirecteur'] . '">' . strip_tags(htmlspecialchars($donnees['prenomDirecteur'])) . ' ' . strip_tags(htmlspecialchars($donnees['nomDirecteur'])) . '</option>';
											}
										}
									?>
									</select>
									<span class="help-block">
										<?php
											if (isset($errors['directeur']))
												echo strip_tags(htmlspecialchars($errors['directeur']));
										?>
									</span>
								</div>
							</div>
						<?php } ?>
						<!-- Avatar -->
						<div class="form-group <?php if (isset($errors['tailleImage']) || isset($errors['imageExtension']) || isset($errors['imageUpload'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="picture">Logo de l'établissement : </label>
							<div class="col-md-4">
								<input type="hidden" name="MAX_FILE_SIZE" value="1048576">
								<input id="picture" name="picture" type="file" class="form-control input-md">
								<span class="help-block">
								<?php
								if (isset($errors['tailleImage']))
									echo strip_tags(htmlspecialchars($errors['tailleImage']));
								if (isset($errors['imageExtension']))
									echo strip_tags(htmlspecialchars($errors['imageExtension']));
								if (isset($errors['imageUpload']))
									echo strip_tags(htmlspecialchars($errors['imageUpload']));
								?>
								</span>
							</div>
						</div>
						<!-- Description -->
						<div class="form-group ">
							<label class="col-md-4 control-label" for="description">Description</label>
							<div class="col-md-5">                     
								<textarea class="form-control wysiwyg" id="description" name="description"></textarea>
							</div>
				        </div>	
						<!-- Button -->
						<div class="form-group text-center">
							<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
								<button name="submit" class="btn btn-primary btn-block">Ajouter</button>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		<?php
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Modification de l'établissement : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="etablissement.php?id=<?php echo $data['id']; ?>&action=modifier">
					<fieldset>
						<!-- Nom -->
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom de l'établissement</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom de l'établissement" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['nom'])); ?>">
							  	<span class="help-block">
						            <?php
						            if (isset($errors['tailleNom']))
						            	echo strip_tags(htmlspecialchars($errors['tailleNom']));
 									if (isset($errors['nomNombre']))
						            	echo strip_tags(htmlspecialchars($errors['nomNombre']));
						            if (isset($errors['nomUtilise']))
						                echo strip_tags(htmlspecialchars($errors['nomUtilise']));
						            if (isset($errors['nom']))
						                echo strip_tags(htmlspecialchars($errors['nom']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Adresse -->
						<div class="form-group <?php if (isset($errors['adresse']) || isset($errors['tailleAdresse']) || isset($errors['adresseNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="adresse">Adresse</label>  
							<div class="col-md-5">
								<input id="adresse" name="adresse" type="text" placeholder="Adresse" class="form-control input-md"  value="<?php echo strip_tags(htmlspecialchars($data['adresse'])); ?>">
							  	<span class="help-block">
						            <?php
						           		if (isset($errors['adresse']))
						            		echo strip_tags(htmlspecialchars($errors['adresse']));
						            	if (isset($errors['tailleAdresse']))
						            		echo strip_tags(htmlspecialchars($errors['tailleAdresse']));
						            	if (isset($errors['adresseNombre']))
						            		echo strip_tags(htmlspecialchars($errors['adresseNombre']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Ville -->
						<div class="form-group <?php if (isset($errors['ville']) || isset($errors['tailleVille']) || isset($errors['villeNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ville">Ville</label>  
							<div class="col-md-5">
								<input id="ville" name="ville" type="text" placeholder="Ville" class="form-control input-md"  value="<?php echo strip_tags(htmlspecialchars($data['ville'])); ?>">
							  	<span class="help-block">
						            <?php
						           		if (isset($errors['ville']))
						            		echo strip_tags(htmlspecialchars($errors['ville']));
						            	if (isset($errors['tailleVille']))
						            		echo strip_tags(htmlspecialchars($errors['tailleVille']));
						            	if (isset($errors['villeNombre']))
						            		echo strip_tags(htmlspecialchars($errors['villeNombre']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Code postal -->
						<div class="form-group <?php if (isset($errors['codePostal'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="codePostal">Code postal</label>  
							<div class="col-md-5">
								<input id="codePostal" name="codePostal" type="text" placeholder="Code postal" class="form-control input-md"  value="<?php echo strip_tags(htmlspecialchars($data['codePostal'])); ?>">
							  	<span class="help-block">
						            <?php
						            	if (isset($errors['codePostal']))
						            		echo strip_tags(htmlspecialchars($errors['codePostal']));
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Directeur -->
						<?php
						$request = $db->query('SELECT utilisateur.id AS idDirecteur, utilisateur.prenom AS prenomDirecteur, utilisateur.nom AS nomDirecteur FROM utilisateur, statut WHERE statut.nom = "directeur" AND utilisateur.idStatut = statut.id');
						if ($request->rowCount() > 0) {
						?>
							<div class="form-group <?php if (isset($errors['directeur'])) echo "has-error"; ?>">
								<label class="col-md-4 control-label" for="directeur">Directeur de l'établissement :</label>
								<div class="col-md-5">
									<select id="directeur" name="directeur" class="form-control">
									<?php
										while ($donnees = $request->fetch()) {
											if (!statut('administrateur') && $acces['ajouterEtablissement'] == '1') {
												if ($donnees['idDirecteur'] == $_SESSION['auth']['id'])
													echo '<option value="' . $donnees['idDirecteur'] . '">' . $donnees['prenomDirecteur'] . ' ' . $donnees['nomDirecteur'] . '</option>';
											} else {
												echo '<option value="' . $donnees['idDirecteur'] . '"';
												if ($data['idDirecteur'] == $donnees['idDirecteur'])
													echo 'selected="selected"';
												echo '>' . $donnees['prenomDirecteur'] . ' ' . $donnees['nomDirecteur'] . '</option>';
											}
										}
									?>
									</select>
									<span class="help-block">
										<?php
											if (isset($errors['directeur']))
												echo strip_tags(htmlspecialchars($errors['directeur']));
										?>
									</span>
								</div>
							</div>
						<?php } ?>
						<!-- Avatar -->
						<div class="form-group <?php if (isset($errors['tailleImage']) || isset($errors['erreurImage']) || isset($errors['imageExtension']) || isset($errors['imageUpload']) || isset($errors['copieImage'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="picture">Logo de l'établissement : </label>
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
						<!-- Description -->
						<div class="form-group ">
							<label class="col-md-4 control-label" for="description">Description</label>
							<div class="col-md-5">                     
								<textarea class="form-control wysiwyg" id="description" name="description"><?php echo strip_tags(htmlspecialchars($data['description'])); ?></textarea>
							</div>
				        </div>	
						<!-- Button -->
						<div class="form-group text-center">
							<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
								<button name="submit" class="btn btn-primary btn-block">Ajouter</button>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		<?php
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Suppression de l'établissement : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'un thème entraine la suppression de l'ensemble des cours de ce thème !</p>
				<p>Êtes-vous sûr de vouloir supprimer ce thème ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="etablissement.php?id=<?php echo $data['id']; ?>&action=supprimer">
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
		} else if (isset($_GET['id']) && !isset($_GET['action'])) {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Etablissement : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?>
					<?php
					if (connecte()) {
						$requete = $db->prepare('SELECT * FROM etablissement WHERE id = :id AND idDirecteur = :idUtilisateur');
						$requete->execute(array(
							'id' => $data['id'],
							'idUtilisateur'		=> $_SESSION['auth']['id']
						));
						$result = $requete->fetch();

						if ($result || statut('administrateur')) {
							if ($acces['supprimerEtablissement'])
								echo ' <a href="etablissement.php?id=' . $data['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove pull-right"></span></a>';
							if ($acces['modifierEtablissement'])
								echo '<a href="etablissement.php?id=' . $data['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil pull-right"></span></a> ';
						}
					}
					?>
					</h3>
				</div>
			</div>
			<div class="row">
				<?php
				$request = $db->prepare("SELECT etablissement.id, etablissement.nom, etablissement.adresse, etablissement.ville, etablissement.codePostal, etablissement.description, utilisateur.id AS idDirecteur, utilisateur.nom AS nomDirecteur, utilisateur.prenom AS prenomDirecteur FROM etablissement, utilisateur WHERE etablissement.id = :idEtablissement AND utilisateur.id = etablissement.idDirecteur");
				$request->execute(array(
					'idEtablissement' => $_GET['id']
				));
				$donnees = $request->fetch();
				?>
				
				<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
					<div class="thumbnail pull-left col-xs-12 col-sm-4 col-md-12 col-lg-12">
						<?php
						if (pictureExist("etablissements", $_GET['id'] . '.jpg'))
							echo '<img src="img/etablissements/' . $_GET['id'] . '.jpg" alt="" class="img-responsive">';
						else if (pictureExist("etablissements", $_GET['id'] . '.jpeg'))
							echo '<img src="img/etablissements/' . $_GET['id'] . '.jpeg" alt="" class="img-responsive">';
						else if (pictureExist("etablissements", $_GET['id'] . '.png'))
							echo '<img src="img/etablissements/' . $_GET['id'] . '.png" alt="" class="img-responsive">';
						else if (pictureExist("etablissements", $_GET['id'] . '.gif'))
							echo '<img src="img/etablissements/' . $_GET['id'] . '.gif" alt="" class="img-responsive">';
						else
							echo '<img src="img/etablissements/default.jpg" alt="" class="img-responsive">';
						?>
					</div>
					<div class="col-xs-12 col-sm-8 col-md-12 col-lg-12">
						<h3><?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
						<p><b>Adresse</b> : <?php echo strip_tags(htmlspecialchars($data['adresse'])); ?></p>
						<p><b>Ville</b> : <?php echo strip_tags(htmlspecialchars($data['ville'])); ?></p>
						<p><b>Code postal</b> : <?php echo strip_tags(htmlspecialchars($data['codePostal'])); ?></p>
						<p><b>Directeur</b> : <a href="profil.php?id=<?php echo strip_tags(htmlspecialchars($donnees['idDirecteur'])); ?>"><?php echo strip_tags(htmlspecialchars($donnees['prenomDirecteur'])) . ' ' . strip_tags(htmlspecialchars($donnees['nomDirecteur'])); ?></a></p>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
					<h4><b>Description</b> : </h4>
					<?php echo $data['description']; ?>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
					<h4><b>Classes</b> : </h4>
					<?php
					$request = $db->prepare('SELECT * FROM classe WHERE idEtablissement = :idEtablissement ORDER BY annee DESC');
					$request->execute(array(
						'idEtablissement' => $data['id']
					));

					$i = 0;
					$annee = "";
					while($donnees = $request->fetch()) {
						if (strcmp($annee, $donnees['annee']) !== 0) {
							echo '</ul></div>';
							$annee = $donnees['annee'];
							$i = 0;
						}

						if ($i == 0) {
							echo '<div class="col-xs-6 col-sm-4 col-md-2 col-lg-2"><h5><b>Année</b> : ' . strip_tags(htmlspecialchars($donnees['annee'])) . '</h5>';
							echo '<ul>';
						}
						echo '<li><a href="classe.php?id=' . $donnees['id'] . '">' . strip_tags(htmlspecialchars($donnees['nom'])) . '</a></li>';

						$i++;
					}
					?>
				</div>
			</div>
		<?php
		} else {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Liste des établissements <?php if (connecte() && $acces['ajouterEtablissement'] == 1) echo '<a href="etablissement.php?action=ajouter" class="btn btn-primary pull-right">Ajouter un établissement</a>'; ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<table class="table">
					<thead>
						<tr>
							<th class="col-lg-2 text-center"></th>
							<th class="col-lg-8 text-center">Nom de l'établissement</th>
							<th class="col-lg-2 text-center"><?php if (connecte() && ($acces['modifierEtablissement'] == 1 || $acces['supprimerEtablissement'] == 1)) echo 'Actions'; ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$request = $db->query("SELECT COUNT(id) AS nbEtablissements FROM etablissement");
						$result = $request->fetch();

						$nbEtablissements = $result['nbEtablissements'];
						$perPage = 20;
						$nbPage = ceil($nbEtablissements/$perPage);

						if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbPage) {
							$cPage = $_GET['page'];
						} else {
							$cPage = 1;
						}

						$request = $db->query("SELECT * FROM etablissement ORDER BY id DESC LIMIT " . (($cPage - 1) * $perPage) . ", $perPage");
						while ($data = $request->fetch()) {
							echo '<tr>
							<th></th>
							<td><a href="etablissement.php?id=' . $data['id'] . '">' . strip_tags(htmlspecialchars($data['nom'])) . '</a></td>
							<td>';

							if (connecte()) {
								$requete = $db->prepare('SELECT * FROM etablissement WHERE id = :id AND idDirecteur = :idUtilisateur');
								$requete->execute(array(
									'id' => $data['id'],
									'idUtilisateur'		=> $_SESSION['auth']['id']
								));
								$result = $requete->fetch();

								if ($result || statut('administrateur')) {
									if ($acces['modifierEtablissement'] == 1)
										echo '<a href="etablissement.php?id=' . $data['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a> ';
									if ($acces['supprimerEtablissement'] == 1)
										echo ' <a href="etablissement.php?id=' . $data['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
								}
							}
							echo '</td></tr>';
						}
						?>
					</tbody>
				</table>
			</div>
			<div class="col-lg-12 text-center">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                    <?php
                        for ($i = 1; $i <= $nbPage; $i++) {
                            if ($i == $cPage)
                                echo '<li class="active"><a href="etablissement.php?page=' . $i . '">' . $i . '</a></li>';
                            else
                                echo '<li><a href="etablissement.php?page=' . $i . '">' . $i . '</a></li>';
                        }
                    ?>
                    </ul>
                </nav>
            </div>
	<?php
	}
	?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>