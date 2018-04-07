<?php
	$title = "Niveaux";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (isset($_GET['id'])) {
		$request = $db->prepare('SELECT * FROM niveau WHERE id = :id');
		$request->execute(array(
			'id' => $_GET['id']
		));
		$data = $request->fetch();

		if (!$data) {
		    header('Location: 404.php');
		    die();
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'modifier' && $_GET['action'] != 'supprimer') || (isset($_GET['action']) && ($_GET['action'] == 'modifier' || $_GET['action'] == 'supprimer') && !isset($_GET['id']))) {
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
			if ($acces['ajouterNiveau'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
			if ($acces['modifierNiveau'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
			if ($acces['supprimerNiveau'] == '0') {
				header('Location: 404.php');
				die();
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['nom']) && !empty($_POST['level'])) {
			// Nom
			$request = $db->prepare("SELECT niveau FROM niveau WHERE niveau = :nom");
			$request->execute(array(
				'nom' => $_POST['nom']
			));
			$donnees = $request->fetch();

			if (strlen($_POST['nom']) > 255)
				$errors['tailleNom'] = "Le nom du niveau est trop long.";
			if (is_numeric($_POST['nom']))
				$errors['nomNombre'] = "Le nom du niveau n'est pas une chaine de caractères.";
			if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
				$errors['nom'] = "Le nom du niveau n'est pas valide.";
			if ($donnees) {
				$errors['nomUtilise'] = "Le nom du niveau est déjà utilisé.";
			}

			// Level
			$request = $db->prepare("SELECT level FROM niveau WHERE level = :level");
			$request->execute(array(
				'level' => $_POST['level']
			));
			$donnees = $request->fetch();

			if (!is_numeric($_POST['level']))
				$errors['levelNombre'] = "Le level n'est pas un entier.";
			if ($donnees)
				$errors['levelUtilise'] = "Le level est déjà utilisé.";

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('INSERT INTO niveau(niveau, level) VALUES(:niveau, :level)');
		      	$request->execute(array(
		      		'niveau' 	=> $_POST['nom'],
		      		'level' 	=> $_POST['level']
		      	));

				$_SESSION['flash']['success'] = "Le niveau a été ajouté avec succès.";
				header('Location: niveau.php');
	    		die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_POST)) {
		$errors = array();
		$inputs = array();

		$inputs = $data;

		if (isset($_POST['submit'])) {
			// Niveau
			if (!empty($_POST['nom']) && strcmp($_POST['nom'], $inputs['niveau']) !== 0) {
				$request = $db->prepare("SELECT niveau FROM niveau WHERE niveau = :nom");
				$request->execute(array(
					'nom' => $_POST['nom']
				));
				$donnees = $request->fetch();

				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Le nom du niveau est trop long.";
				if (is_numeric($_POST['nom']))
					$errors['nomNombre'] = "Le nom du niveau n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Le nom du niveau n'est pas valide.";
				if ($donnees) {
					$errors['nomUtilise'] = "Le nom du niveau est déjà utilisé.";
				}

				if (!isset($errors['tailleNom']) && !isset($errors['nomNombre']) && !isset($errors['nom']) && !isset($errors['nomUtilise'])) {
					$inputs['niveau'] = $_POST['nom'];
				}
			}

			// Level
			if (!empty($_POST['level']) && strcmp($_POST['level'], $inputs['level']) !== 0) {
				$request = $db->prepare("SELECT level FROM niveau WHERE level = :level");
				$request->execute(array(
					'level' => $_POST['level']
				));
				$donnees = $request->fetch();

				if (!is_numeric($_POST['level']))
					$errors['levelNombre'] = "Le level n'est pas un entier.";
				if ($donnees)
					$errors['levelUtilise'] = "Le level est déjà utilisé.";

				if (!isset($errors['levelNombre']) && !isset($errors['levelUtilise'])) {
					$inputs['level'] = $_POST['level'];
				}
			}

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('UPDATE niveau SET niveau = :niveau, level = :level WHERE id = :id');
		      	$request->execute(array(
			        'niveau'	=> $inputs['niveau'],
			        'level'  	=> $inputs['level'],
			        'id'		=> $_GET['id']
		      	));

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "Le niveau a été modifié avec succès.";
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			$request = $db->prepare('SELECT * FROM niveau WHERE level = :level');
			$request->prepare(array(
				'level' => $data['level'] - 1
			));
			$donnees = $request->fetch();

			// Suppression du niveau
			$requete = $db->prepare("DELETE FROM niveau WHERE id = :id");
			$requete->execute(array(
				'id' => $_GET['id']
			));

			$request = $db->prepare('UPDATE cours SET idNiveau = :nouveauIdNiveau WHERE idNiveau = :idNiveau');
			$request->execute(array(
		        'nouveauIdNiveau'	=> $donnees['id'],
		        'idNiveau'			=> $_GET['id']
	      	));

			header('Location: niveau.php');
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: niveau.php');
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
				<h2 class="header-page">Niveaux</h2>
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
			$fil = array('niveau.php' => 'Niveaux');
		  	if (isset($_GET['action'])) {
		  		if ($_GET['action'] == 'ajouter')
		  			$fil['niveau.php?action=ajouter'] = 'Ajout d\'un niveau';
		  		else if ($_GET['action'] == 'modifier')
		  			$fil['niveau.php?id=' . $_GET['id'] . '&action=modifier'] = 'Modification du niveau : ' . $data['niveau'];
		  		else if ($_GET['action'] == 'supprimer')
		  			$fil['niveau.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression du niveau : ' . $data['niveau'];
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
					<h3>Ajout d'un niveau</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="niveau.php?action=ajouter">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du niveau</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du niveau" class="form-control input-md" required="">
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
						<!-- Level -->
						<div class="form-group <?php if (isset($errors['levelUtilise']) || isset($errors['levelNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="level">Level</label>  
							<div class="col-md-5">
								<input id="level" name="level" type="text" placeholder="Level" class="form-control input-md" required="">
								<span class="help-block">
									<?php
									if (isset($errors['levelNombre']))
										echo strip_tags(htmlspecialchars($errors['levelNombre']));
									if (isset($errors['levelUtilise']))
										echo strip_tags(htmlspecialchars($errors['levelUtilise']));
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
					<h3>Modification du niveau : <?php echo strip_tags(htmlspecialchars($data['niveau'])); ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="niveau.php?id=<?php echo $data['id']; ?>&action=modifier">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du niveau</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du niveau" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['niveau'])); ?>">
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
						<!-- Level -->
						<div class="form-group <?php if (isset($errors['levelUtilise']) || isset($errors['levelNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="level">Level</label>  
							<div class="col-md-5">
								<input id="level" name="level" type="text" placeholder="Level" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['level'])); ?>">
								<span class="help-block">
									<?php
									if (isset($errors['levelNombre']))
										echo strip_tags(htmlspecialchars($errors['levelNombre']));
									if (isset($errors['levelUtilise']))
										echo strip_tags(htmlspecialchars($errors['levelUtilise']));
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
					<h3>Suppression du niveau : <?php echo strip_tags(htmlspecialchars($data['niveau'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'un niveau entraine le changement de niveau des cours impliqués !</p>
				<p>Êtes-vous sûr de vouloir supprimer ce niveau ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="niveau.php?id=<?php echo $data['id']; ?>&action=supprimer">
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
				<div class="col-lg-12 page-header">
					<h3>Des cours de niveaux divers et variés pour satisfaire tout le monde ! <?php if (connecte() && $acces['ajouterNiveau'] == 1) echo '<a href="niveau.php?action=ajouter" class="btn btn-primary pull-right">Ajouter un niveau</a>'; ?></h3>
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
				<h3>Niveaux :</h3>
				<ul>
					<?php
					$request = $db->query('SELECT * FROM niveau ORDER BY level');
					while ($donnees = $request->fetch()) {
						echo '<li>' . strip_tags(htmlspecialchars($donnees['level'])) . ' : ' . strip_tags(htmlspecialchars($donnees['niveau'])) . ' ';
						if (connecte() && $acces['modifierNiveau'] == 1)
							echo ' <a href="niveau.php?id=' . $donnees['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a> ';
						if (connecte() && $acces['supprimerNiveau'] == 1)
							echo ' <a href="niveau.php?id=' . $donnees['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a> ';

						echo '</li>';
					}
					?>
				</ul>
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