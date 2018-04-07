<?php
	$title = "Cours";
	$css = "<!-- Timeline CSS --><link rel=\"stylesheet\" href=\"css/timeline.css\">";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (isset($_GET['id'])) {
		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
			$request = $db->prepare("SELECT * FROM theme WHERE id = :id");
		} else {
			$request = $db->prepare("SELECT * FROM cours WHERE id = :id");
		}
		
		$request->execute(array(
			'id' => $_GET['id']
		));
		$data = $request->fetch();

		if (!$data) {
		    header('Location: 404.php');
		    die();
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'modifier' && $_GET['action'] != 'supprimer') || (isset($_GET['action']) && !isset($_GET['id']))) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: 404.php');
		die();
	}

	if (isset($_GET['action']) && !connecte()) {
		header('Location: 404.php');
		die();
	}

	if (connecte()) {
		$request = $db->prepare("SELECT * FROM action WHERE idStatut = :idStatut");
		$request->execute(array(
			'idStatut' => $_SESSION['auth']['idStatut']
		));
		$acces = $request->fetch();

		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
			if ($acces['ajouterCours'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
			if ($acces['modifierCours'] == '0') {
				header('Location: 404.php');
				die();
			}

			$request = $db->prepare("SELECT * FROM cours, affiliation WHERE cours.id = :id AND cours.idUtilisateur = :idUtilisateur");
			$request->execute(array(
				'id' => $_GET['id'],
				'idUtilisateur'		=> $_SESSION['auth']['id']
			));
			$donnees = $request->fetch();

			if (!$donnees && !statut('administrateur')) {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
			if ($acces['supprimerCours'] == '0') {
				header('Location: 404.php');
				die();
			}

			$request = $db->prepare("SELECT * FROM cours, affiliation WHERE cours.id = :id AND cours.idUtilisateur = :idUtilisateur");
			$request->execute(array(
				'id' => $_GET['id'],
				'idUtilisateur'		=> $_SESSION['auth']['id']
			));
			$donnees = $request->fetch();

			if (!$donnees && !statut('administrateur')) {
				header('Location: 404.php');
				die();
			}
		}
	}

	if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['nom']) && !empty($_POST['niveau'])) {
			// Nom
			if (strlen($_POST['nom']) > 255)
				$errors['tailleNom'] = "Le nom du cours est trop long.";
			if (is_numeric($_POST['nom']))
				$errors['nomNombre'] = "Le nom du cours n'est pas une chaine de caractères.";
			if (!preg_match("/^[a-zA-Z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/", $_POST['nom']))
				$errors['nom'] = "Le nom du cours n'est pas valide.";

			// Niveau du cours
			$request = $db->prepare("SELECT * FROM niveau WHERE id = :id");
			$request->execute(array(
				'id' => $_POST['niveau']
			));
			$donnees = $request->fetch();
			if (!$donnees) {
				$errors['niveau'] = "Ce niveau n'existe pas.";
			}

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare("INSERT INTO cours(idTheme, idNiveau, idUtilisateur, nom) VALUES(:idTheme, :idNiveau, :idUtilisateur, :nom)");
		      	$request->execute(array(
		      		'idTheme' 		=> $_GET['id'],
		      		'idNiveau' 		=> $_POST['niveau'],
		      		'idUtilisateur' => $_SESSION['auth']['id'],
			        'nom' 			=> $_POST['nom']
		      	));

				$_SESSION['flash']['success'] = "Le cours a été ajouté avec succès.";
				header('Location: theme.php?id=' . $_GET['id']);
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
				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Le nom du cours est trop long.";
				if (is_numeric($_POST['nom']))
					$errors['nomNombre'] = "Le nom du cours n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-Z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Le nom du cours n'est pas valide.";

				if (!isset($errors['tailleNom']) && !isset($errors['nomNombre']) && !isset($errors['nom']) && !isset($errors['nomUtilise'])) {
					$inputs['nom'] = $_POST['nom'];
				}
			}

			// Niveau du cours
			if (!empty($_POST['niveau']) && strcmp($_POST['niveau'], $inputs['idNiveau']) !== 0) {
				$request = $db->prepare("SELECT id FROM niveau WHERE id = :id");
				$request->execute(array(
					'id' => $_POST['niveau']
				));
				$donnees = $request->fetch();

				if (!$donnees) {
					$errors['niveau'] = "Ce niveau n'existe pas.";
				}

				if (!isset($errors['niveau'])) {
					$inputs['idNiveau'] = $_POST['niveau'];
				}
			}

			// Thème
			if (!empty($_POST['theme']) && strcmp($_POST['theme'], $inputs['idTheme']) !== 0) {
				$request = $db->prepare("SELECT id FROM theme WHERE id = :id");
				$request->execute(array(
					'id' => $_POST['theme']
				));
				$donnees = $request->fetch();

				if (!$donnees) {
					$errors['theme'] = "Ce theme n'existe pas.";
				}

				if (!isset($errors['theme'])) {
					$inputs['idTheme'] = $_POST['theme'];
				}
			}

			// Professeur
			if (!empty($_POST['professeur']) && strcmp($_POST['professeur'], $inputs['idUtilisateur']) !== 0 && statut('administrateur')) {
				$request = $db->prepare("SELECT utilisateur.id FROM utilisateur, statut WHERE utilisateur.id = :id AND utilisateur.idStatut = statut.id AND statut.nom = 'professeur'");
				$request->execute(array(
					'id' => $_POST['professeur']
				));
				$donnees = $request->fetch();

				if (!$donnees) {
					$errors['professeur'] = "Ce professeur n'existe pas.";
				}

				if (!isset($errors['professeur'])) {
					$inputs['idUtilisateur'] = $_POST['professeur'];
				}
			}

			// Erreur
			if (empty($errors)) {
				if (statut('administrateur')) {
					$request = $db->prepare("UPDATE cours SET idNiveau = :idNiveau, nom = :nom, idTheme = :idTheme, idUtilisateur = :idUtilisateur WHERE id = :id");
			      	$request->execute(array(
				        'idNiveau'		=> $inputs['idNiveau'],
				        'nom'  			=> $inputs['nom'],
				        'idTheme'		=> $inputs['idTheme'],
				        'idUtilisateur'	=> $inputs['idUtilisateur'],
				        'id'			=> $_GET['id']
			      	));
				} else {
					$request = $db->prepare("UPDATE cours SET idNiveau = :idNiveau, nom = :nom, idTheme = :idTheme WHERE id = :id");
			      	$request->execute(array(
				        'idNiveau'	=> $inputs['idNiveau'],
				        'nom'  		=> $inputs['nom'],
				        'idTheme'	=> $inputs['idTheme'],
				        'id'		=> $_GET['id']
			      	));
				}

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "Le cours a été modifié avec succès.";
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression des chapitres
			$request = $db->prepare("SELECT chapitre.id FROM chapitre, cours WHERE cours.id = :id AND chapitre.idCours = cours.id");
			$request->execute(array(
				'id' => $_GET['id']
			));
			while ($result = $request->fetch()) {
				$requete = $db->prepare("DELETE FROM chapitre WHERE id = :id");
				$requete->execute(array(
					'id' => $result['id']
				));
			}

			// Suppression du cours
			$request = $db->prepare("DELETE FROM cours WHERE id = :id");
			$request->execute(array(
				'id' => $_GET['id']
			));

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
				<h2 class="header-page">Cours</h2>
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
		  		$fil['cours.php?id=' . $data['id']] = $data['nom'];
		  		if ($_GET['action'] == 'ajouter')
		  			$fil['cours.php?action=ajouter'] = 'Ajout d\'un cours';
		  		else if ($_GET['action'] == 'modifier')
		  			$fil['cours.php?id=' . $_GET['id'] . '&action=modifier'] = 'Modification du cours : ' . $data['nom'];
		  		else if ($_GET['action'] == 'supprimer')
		  			$fil['cours.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression du cours : ' . $data['nom'];
		  	} else if (isset($_GET['id'])) {
		  		$request = $db->prepare("SELECT id, nom FROM theme WHERE id = :idTheme");
				$request->execute(array(
					'idTheme' => $data['idTheme']
				));
				$donnees = $request->fetch();
				$data['idTheme'] = $donnees['id'];
				$data['nomTheme'] = $donnees['nom'];

		  		$fil['theme.php?id=' . $data['idTheme']] = $data['nomTheme'];
		  		$fil['cours.php?id=' . $_GET['id']] = $data['nom'];
		  	} else {
		  		$fil['cours.php'] = 'Cours';
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
		if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'ajouter') {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Ajout d'un cours</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="cours.php?id=<?php echo $_GET['id']; ?>&action=ajouter">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du cours</label>
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du cours" class="form-control input-md" required="">
							  	<span class="help-block">
						            <?php
						            if (isset($errors['tailleNom']))
						            	echo $errors['tailleNom'];
 									if (isset($errors['nomNombre']))
						            	echo $errors['nomNombre'];
						            if (isset($errors['nomUtilise']))
						                echo $errors['nomUtilise'];
						            if (isset($errors['nom']))
						                echo $errors['nom'];
						            ?>
					            </span>
					         </div>
						</div>

						<!-- Statut -->
						<div class="form-group <?php if (isset($errors['niveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="niveau">Niveau du cours :</label>
							<div class="col-md-5">
								<select id="niveau" name="niveau" class="form-control">
								<?php
									$request = $db->query('SELECT * FROM niveau ORDER BY level');
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '">' . $donnees['niveau'] . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
									if (isset($errors['niveau']))
									echo $errors['niveau'];
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
					<h3>Modification du cours : <?php echo htmlspecialchars($data['nom']); ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="cours.php?id=<?php echo $data['id']; ?>&action=modifier">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du cours</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du cours" class="form-control input-md" value="<?php echo htmlspecialchars($data['nom']); ?>">
							  	<span class="help-block">
						            <?php
						            if (isset($errors['tailleNom']))
						            	echo $errors['tailleNom'];
 									if (isset($errors['nomNombre']))
						            	echo $errors['nomNombre'];
						            if (isset($errors['nomUtilise']))
						                echo $errors['nomUtilise'];
						            if (isset($errors['nom']))
						                echo $errors['nom'];
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Statut -->
						<div class="form-group <?php if (isset($errors['niveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="niveau">Niveau du cours :</label>
							<div class="col-md-5">
								<select id="niveau" name="niveau" class="form-control">
								<?php
									$request = $db->query("SELECT * FROM niveau ORDER BY level");
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '"';
										if ($data['idNiveau'] == $donnees['id'])
											echo 'selected="selected"';
										echo '>' . $donnees['niveau'] . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
									if (isset($errors['niveau']))
									echo $errors['niveau'];
									?>
								</span>
							</div>
						</div>
						<!-- Thème -->
						<div class="form-group <?php if (isset($errors['theme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="theme">Thème :</label>
							<div class="col-md-5">
								<select id="theme" name="theme" class="form-control">
								<?php
									$request = $db->query("SELECT * FROM theme");
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '"';
										if ($donnees['id'] == $data['idTheme'])
											echo 'selected="selected"';
										echo '>' .$donnees['nom'] . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
									if (isset($errors['theme']))
									echo $errors['theme'];
									?>
								</span>
							</div>
						</div>
						<!-- Professeur -->
						<?php if (statut('administrateur')) { ?>
						<div class="form-group <?php if (isset($errors['professeur'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="professeur">Professeur :</label>
							<div class="col-md-5">
								<select id="professeur" name="professeur" class="form-control">
								<?php
									$request = $db->query("SELECT utilisateur.id, utilisateur.prenom, utilisateur.nom FROM utilisateur, statut WHERE utilisateur.idStatut = statut.id AND statut.nom = 'professeur'");
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '"';
										if ($donnees['id'] == $data['idUtilisateur'])
											echo 'selected="selected"';
										echo '>' . $donnees['prenom'] . ' ' .$donnees['nom'] . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
									if (isset($errors['professeur']))
									echo $errors['professeur'];
									?>
								</span>
							</div>
						</div>
						<?php } ?>
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
					<h3>Suppression du cours : <?php echo htmlspecialchars($data['nom']); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'un cours entraine la suppression de l'ensemble des chapitres de ce cours !</p>
				<p>Êtes-vous sûr de vouloir supprimer ce cours ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="cours.php?id=<?php echo $data['id']; ?>&action=supprimer">
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
		} else if (isset($_GET['id'])) {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Cours : <?php echo $data['nom']; if (connecte() && $acces['ajouterChapitre']) echo '<a href="chapitre.php?id=' . $data['id'] . '&action=ajouter" class="btn btn-primary pull-right">Ajouter un chapitre</a>'; ?></h3>
				</div>
			</div>
			<div class="row">
				<h3>Plan du cours</h3>
				<ul>
				<?php
				$request = $db->prepare("SELECT * FROM chapitre WHERE idCours = :id ORDER BY ordre");
				$request->execute(array(
					'id' => $_GET['id']
				));

				$i = 0;
				while ($donnees = $request->fetch()) {
					echo '<li>
					Chapitre : <a href="chapitre.php?id=' . $donnees['id'] . '">' . $donnees['titre'] . '</a> ';

					$requete = $db->prepare("SELECT * FROM cours, affiliation WHERE cours.id = :id AND cours.idUtilisateur = :idUtilisateur");
					$requete->execute(array(
						'id' => $_GET['id'],
						'idUtilisateur'		=> $_SESSION['auth']['id']
					));
					$result = $requete->fetch();

					if ($result || statut('administrateur')) {
						if (connecte() && $acces['modifierChapitre'])
							echo '<a href="chapitre.php?id=' . $donnees['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a> ';
						if (connecte() && $acces['supprimerChapitre'])
							echo ' <a href="chapitre.php?id=' . $donnees['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
					}
					echo '</li>';
					$i++;
				}

				if ($i == 0) {
					echo '<p>Ce cours ne contient pas de chapitre.</p>';
				}
				?>
				</ul>
			</div>
		<?php
		} else {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Des cours tous plus passionnants les uns que les autres !</h3>
				</div>
			</div>
			<div class="row">
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			</div>
		<?php
		}
		?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>