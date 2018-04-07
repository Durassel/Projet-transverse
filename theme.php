<?php
	$title = "Thèmes";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (isset($_GET['id'])) {
		$request = $db->prepare("SELECT * FROM theme WHERE id = :id");
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
			if ($acces['ajouterTheme'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
			if ($acces['modifierTheme'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
			if ($acces['supprimerTheme'] == '0') {
				header('Location: 404.php');
				die();
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['nom']) && !empty($_FILES['picture'])) {
			// Nom
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

			// Picture
			if (!empty($_FILES['picture']['name']) AND $_FILES['picture']['error'] == 0) {
				if ($_POST['MAX_FILE_SIZE'] != 1048576)
					$_POST['MAX_FILE_SIZE'] = 1048576;

				if ($_FILES['picture']['size'] <= $_POST['MAX_FILE_SIZE']) {
					$ext = strtolower(substr($_FILES['picture']['name'], -3));
					$allow_ext = array('jpeg', 'jpg', 'png', 'gif', 'JPEG', 'JPG', 'PNG', 'GIF');

					if (in_array($ext, $allow_ext)) {
						// Name of the picture = id company
				        $request = $db->query('SELECT MAX(id) FROM theme');
				        $result = $request->fetch();
				        $result[0]++;
				        $pictureName = $result[0] . "." . $ext;

						if (!move_uploaded_file($_FILES['picture']['tmp_name'], "img/themes/" . strtolower($pictureName))) {
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

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('INSERT INTO theme(nom) VALUES(:nom)');
		      	$request->execute(array(
			        'nom' => $_POST['nom']
		      	));

				$_SESSION['flash']['success'] = "Le thème a été ajouté avec succès.";
				header('Location: theme.php');
	    		die();
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

						if (file_exists('img/themes/' . $inputs['id'] . '.jpg')) {
							$extension = explode('.', $inputs['id'] . '.jpg');
							$currentExt = $extension[1];
						} else if (file_exists('img/themes/' . $inputs['id'] . '.jpeg')) {
							$extension = explode('.', $inputs['id'] . '.jpeg');
							$currentExt = $extension[1];
						} else if (file_exists('img/themes/' . $inputs['id'] . '.png')) {
							$extension = explode('.', $inputs['id'] . '.png');
							$currentExt = $extension[1];
						} else if (file_exists('img/themes/' . $inputs['id'] . '.gif')) {
							$extension = explode('.', $inputs['id'] . '.gif');
							$currentExt = $extension[1];
						}

						if (!move_uploaded_file($_FILES['picture']['tmp_name'], "img/themes/" . strtolower($pictureName))) {
							$errors['imageUpload'] = "Une erreur est survenue lors du téléchargement.";
							unset($pictureName);
						} else {
							if (strcasecmp($currentExt, $ext) != 0) {
								unlink("img/themes/" . $inputs['id'] . '.' . $currentExt);
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
				$request = $db->prepare('UPDATE theme SET nom = :nom WHERE id = :id');
		      	$request->execute(array(
			        'nom'  		=> $inputs['nom'],
			        'id'		=> $inputs['id']
		      	));

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "Le thème a été modifié avec succès.";
				header('Location: theme.php');
	    		die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression des chapitres
			$request = $db->prepare("SELECT chapitre.id FROM chapitre, cours WHERE cours.idTheme = :id AND chapitre.idCours = cours.id");
			$request->execute(array(
				'id' => $_GET['id']
			));
			while ($result = $request->fetch()) {
				$requete = $db->prepare("DELETE FROM chapitre WHERE id = :id");
				$requete->execute(array(
					'id' => $result['id']
				));
			}

			// Suppression des cours
			$request = $db->prepare("DELETE FROM cours WHERE idTheme = :id");
			$request->execute(array(
				'id' => $_GET['id']
			));

			// Suppression du thème
			$request = $db->prepare("DELETE FROM theme WHERE id = :id");
			$request->execute(array(
				'id' => $_GET['id']
			));

			// Suppression de l'image du thème
			if (pictureExist("themes", $data['id'] . '.jpg'))
				unlink("img/themes/" . $data['id'] . ".jpg");
			if (pictureExist("themes", $data['id'] . ".jpeg"))
				unlink("img/themes/" . $data['id'] . ".jpeg");
			if (pictureExist("themes", $data['id'] . ".png"))
				unlink("img/themes/" . $data['id'] . ".png");
			if (pictureExist("themes", $data['id'] . ".gif"))
				unlink("img/themes/" . $data['id'] . ".gif");

			header('Location: theme.php');
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: theme.php');
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
				<h2 class="header-page">Thèmes</h2>
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
			$fil = array('theme.php' => 'Thèmes');
		  	if (isset($_GET['action'])) {
		  		if ($_GET['action'] == 'ajouter')
		  			$fil['theme.php?action=ajouter'] = 'Ajout d\'un thème';
		  		else if ($_GET['action'] == 'modifier')
		  			$fil['theme.php?id=' . $_GET['id'] . '&action=modifier'] = 'Modification du thème : ' . $data['nom'];
		  		else if ($_GET['action'] == 'supprimer')
		  			$fil['theme.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression du thème : ' . $data['nom'];
		  	} else if (isset($_GET['id'])) {
		  		$fil['theme.php?id=' . $_GET['id']] = $data['nom'];
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
					<h3>Ajout d'un thème</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="theme.php?action=ajouter">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du thème</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du thème" class="form-control input-md" required="">
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
						<!-- Image du thème -->
						<div class="form-group <?php if (isset($errors['tailleImage']) || isset($errors['erreurImage']) || isset($errors['imageExtension']) || isset($errors['imageUpload']) || isset($errors['copieImage'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="picture">Image du thème : </label>
							<div class="col-md-5">
								<input type="hidden" name="MAX_FILE_SIZE" value="1048576">
								<input id="picture" name="picture" type="file" class="form-control input-md"  required="">
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
					<h3>Modification du thème : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="theme.php?id=<?php echo $data['id']; ?>&action=modifier">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du thème</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du thème" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['nom'])); ?>">
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
						<!-- Image du thème -->
						<div class="form-group <?php if (isset($errors['tailleImage']) || isset($errors['erreurImage']) || isset($errors['imageExtension']) || isset($errors['imageUpload']) || isset($errors['copieImage'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="picture">Image du thème : </label>
							<div class="col-md-5">
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
						<!-- Button -->
						<div class="form-group text-center">
							<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
								<button name="submit" class="btn btn-primary btn-block">Modifier</button>
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
					<h3>Suppression du thème : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'un thème entraine la suppression de l'ensemble des cours de ce thème !</p>
				<p>Êtes-vous sûr de vouloir supprimer ce thème ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="theme.php?id=<?php echo $data['id']; ?>&action=supprimer">
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
					<h3>Cours : <?php echo strip_tags(htmlspecialchars($data['nom'])); if (connecte() && $acces['ajouterCours'] == 1) echo '<a href="cours.php?id=' . $data['id'] . '&action=ajouter" class="btn btn-primary pull-right">Ajouter un cours</a>'; ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<table class="table">
					<thead>
						<tr>
							<th class="col-lg-2 text-center">Niveau</th>
							<th class="col-lg-8 text-center">Cours</th>
							<th class="col-lg-2 text-center"><?php if (connecte() && ($acces['modifierCours'] == 1 || $acces['supprimerCours'] == 1)) echo 'Actions'; ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$request = $db->prepare("SELECT COUNT(cours.id) AS nbCours FROM cours, niveau WHERE niveau.id = cours.idNiveau AND idTheme = :id");
						$request->execute(array(
							'id' => $_GET['id']
						));
						$result = $request->fetch();

						$nbCours = $result['nbCours'];
						$perPage = 20;
						$nbPage = ceil($nbCours/$perPage);

						if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbPage) {
							$cPage = $_GET['page'];
						} else {
							$cPage = 1;
						}

						$request = $db->prepare("SELECT cours.id, cours.nom, niveau.niveau FROM cours, niveau WHERE niveau.id = cours.idNiveau AND idTheme = :id ORDER BY id DESC LIMIT " . (($cPage - 1) * $perPage) . ", $perPage");
						$request->execute(array(
							'id' => $_GET['id']
						));
						while ($data = $request->fetch()) {
							echo '<tr>
							<td>' . strip_tags(htmlspecialchars($data['niveau'])) . '</td>
							<td><a href="cours.php?id=' . $data['id'] . '">' . strip_tags(htmlspecialchars($data['nom'])) . '</a></td><td>';

							if (connecte()) {
								$requete = $db->prepare('SELECT * FROM cours, affiliation WHERE cours.id = :id AND cours.idUtilisateur = :idUtilisateur');
								$requete->execute(array(
									'id' 				=> $data['id'],
									'idUtilisateur'		=> $_SESSION['auth']['id']
								));
								$result = $requete->fetch();

								if ($result || statut('administrateur')) {
									if ($acces['modifierCours'] == 1)
										echo '<a href="cours.php?id=' . $data['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a> ';
									if ($acces['supprimerCours'] == 1)
										echo ' <a href="cours.php?id=' . $data['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
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
                                echo '<li class="active"><a href="theme.php?id=' . $_GET['id'] . '&page=' . $i . '">' . $i . '</a></li>';
                            else
                                echo '<li><a href="theme.php?id=' . $_GET['id'] . '&page=' . $i . '">' . $i . '</a></li>';
                        }
                    ?>
                    </ul>
                </nav>
            </div>
		<?php
		} else {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Divers thèmes sont disponibles sur Brain'squiz !<?php if (connecte() && $acces['ajouterTheme'] == 1) echo '<a href="theme.php?action=ajouter" class="btn btn-primary pull-right">Ajouter un thème</a>'; ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<?php
				$request = $db->query("SELECT * FROM theme");
				while ($data = $request->fetch()) {
					echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 miniature">
						<div class="thumbnail">';

					if (connecte() && $acces['supprimerTheme'] == 1)
						echo '<a href="theme.php?id=' . $data['id'] . '&action=supprimer" class= "pull-right"><span class="glyphicon glyphicon-remove"></span></a>';
					if (connecte() && $acces['modifierTheme'] == 1)
						echo '<a href="theme.php?id=' . $data['id'] . '&action=modifier" class= "pull-right"><span class="glyphicon glyphicon-pencil"></span></a>';

					echo '<a href="theme.php?id=' . $data['id'] . '">';

					if (pictureExist("themes", $data['id'] . '.jpg'))
						echo '<img src="img/themes/' . $data['id'] . '.jpg" alt="' . strip_tags(htmlspecialchars($data['nom'])) . '" class="img-responsive">';
					else if (pictureExist("themes", $data['id'] . '.jpeg'))
						echo '<img src="img/themes/' . $data['id'] . '.jpeg" alt="' . strip_tags(htmlspecialchars($data['nom'])) . '" class="img-responsive">';
					else if (pictureExist("themes", $data['id'] . '.png'))
						echo '<img src="img/themes/' . $data['id'] . '.png" alt="' . strip_tags(htmlspecialchars($data['nom'])) . '" class="img-responsive">';
					else if (pictureExist("themes", $data['id'] . '.gif'))
						echo '<img src="img/themes/' . $data['id'] . '.gif" alt="' . strip_tags(htmlspecialchars($data['nom'])) . '" class="img-responsive">';
					else
						echo '<img src="img/themes/default.jpg" alt="" class="img-responsive">';
		
					echo '<p>' . strip_tags(htmlspecialchars($data['nom'])) . '</p></a>
						</div>
					</div>';
				}
				?>
			</div>
	<?php
	}
	?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>