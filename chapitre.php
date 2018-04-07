<?php
	$title = "Chapitre";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (!isset($_GET['id']) && !isset($_GET['action'])) {
		header('Location: 404.php');
		die();
	}

	if (isset($_GET['id'])) {
		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
			$request = $db->prepare("SELECT * FROM cours WHERE id = :id");
		} else {
			$request = $db->prepare("SELECT chapitre.id, chapitre.idCours, chapitre.ordre, chapitre.titre, chapitre.texte, cours.nom, theme.id AS idTheme, theme.nom AS nomTheme FROM chapitre, cours, theme WHERE chapitre.id = :id AND cours.id = chapitre.idCours AND cours.idTheme = theme.id");
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
		header('Location: 404.php');
		die();
	}

	if (isset($_GET['action']) && !connecte()) {
		header('Location: 404.php');
		die();
	}

	$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
	$request->execute(array(
		'idStatut' => $_SESSION['auth']['idStatut']
	));
	$acces = $request->fetch();

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
		if ($acces['ajouterChapitre'] == '0') {
			header('Location: 404.php');
			die();
		}
	} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
		if ($acces['modifierChapitre'] == '0') {
			header('Location: 404.php');
			die();
		}

		$request = $db->prepare('SELECT * FROM chapitre, cours, affiliation WHERE chapitre.id = :id AND cours.id = chapitre.idCours AND cours.idUtilisateur = :idUtilisateur');
		$request->execute(array(
			'id' 				=> $_GET['id'],
			'idUtilisateur'		=> $_SESSION['auth']['id']
		));
		$donnees = $request->fetch();
		if (!$donnees && !statut('administrateur')) {
			header('Location: 404.php');
			die();
		}
	} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
		if ($acces['supprimerChapitre'] == '0') {
			header('Location: 404.php');
			die();
		}

		$request = $db->prepare('SELECT * FROM chapitre, cours, affiliation WHERE chapitre.id = :id AND cours.id = chapitre.idCours AND cours.idUtilisateur = :idUtilisateur');
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

	if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['nom']) && !empty($_POST['texte'])) {
			// Nom
			$request = $db->prepare("SELECT titre FROM chapitre WHERE titre = :nom");
			$request->execute(array(
				'nom' => $_POST['nom']
			));
			$donnees = $request->fetch();

			if (strlen($_POST['nom']) > 255)
				$errors['tailleNom'] = "Le titre du chapitre est trop long.";
			if (is_numeric($_POST['nom']))
				$errors['nomNombre'] = "Le titre du chapitre n'est pas une chaine de caractères.";
			if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
				$errors['nom'] = "Le titre du chapitre n'est pas valide.";
			if ($donnees) {
				$errors['nomUtilise'] = "Le titre du chapitre est déjà utilisé.";
			}


			// Ordre
			if (!isset($_POST['position'])) {
				$nouvellePosition = 1;
			}

			if (!empty($_POST['position']) && ($_POST['position'] == 'avant' || $_POST['position'] == 'apres')) {
				$nouvellePosition = $_POST['ordre'];
				if ($_POST['position'] == 'apres') {
					$nouvellePosition = $nouvellePosition + 1;
				}

				$request = $db->prepare("SELECT MAX(ordre) AS ordreMax FROM chapitre WHERE idCours = :id");
				$request->execute(array(
					'id' => $data['id']
				));
				$result = $request->fetch();

				for ($i = $result['ordreMax']; $i >= $nouvellePosition; $i--) {
					$request = $db->prepare('UPDATE chapitre SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idCours = :id');
					$request->execute(array(
				        'nouvelleOrdre'		=> $i + 1,
				        'ordre'				=> $i,
				        'id'  				=> $data['id']
			      	));
				}
			}


			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('INSERT INTO chapitre(idCours, ordre, titre, texte) VALUES(:id, :ordre, :titre, :texte)');
		      	$request->execute(array(
		      		'id' 		=> $data['id'],
		      		'ordre' 	=> $nouvellePosition,
			        'titre' 	=> $_POST['nom'],
			        'texte'		=> $_POST['texte']
		      	));

				$_SESSION['flash']['success'] = "Le chapitre a été ajouté avec succès.";
				header('Location: cours.php?id=' . $data['id']);
	    		die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_POST)) {
		$errors = array();
		$inputs = array();

		$inputs = $data;

		if (isset($_POST['submit'])) {
			// Nom
			if (!empty($_POST['nom']) && strcmp($_POST['nom'], $inputs['titre']) !== 0) {
				$request = $db->prepare("SELECT titre FROM chapitre WHERE titre = :titre");
				$request->execute(array(
					'titre' => $_POST['nom']
				));
				$donnees = $request->fetch();

				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Le titre du chapitre est trop long.";
				if (is_numeric($_POST['nom']))
					$errors['nomNombre'] = "Le titre du chapitre n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Le titre du chapitre n'est pas valide.";
				if ($donnees) {
					$errors['nomUtilise'] = "Le titre du chapitre est déjà utilisé.";
				}

				if (!isset($errors['tailleNom']) && !isset($errors['nomNombre']) && !isset($errors['nom']) && !isset($errors['nomUtilise'])) {
					$inputs['titre'] = $_POST['nom'];
				}
			}

			// Texte
			if (!empty($_POST['texte']) && strcmp($_POST['texte'], $inputs['texte']) !== 0) {
				if (!isset($errors['texte'])) {
					$inputs['texte'] = $_POST['texte'];
				}
			}

			// Ordre
			if (!empty($_POST['position']) && ($_POST['position'] == 'avant' || $_POST['position'] == 'apres')) {
				$anciennePosition = $data['ordre'];
				$nouvellePosition = $_POST['ordre'];
				
				if ($_POST['position'] == 'avant') {
					if ($nouvellePosition > 1 && $nouvellePosition > $anciennePosition) {
						$nouvellePosition = $nouvellePosition - 1;
					}
				} else if ($_POST['position'] == 'apres') {
					if ($nouvellePosition < $anciennePosition) {
						$nouvellePosition = $nouvellePosition + 1;
					}
				}

				if ($anciennePosition == $nouvellePosition) {
					$inputs['ordre'] = (string) $anciennePosition;
				}
			}

			if (!empty($_POST['position']) && ($_POST['position'] == 'avant' || $_POST['position'] == 'apres') && !empty($_POST['ordre']) && $anciennePosition != $nouvellePosition) {
				if ($nouvellePosition > $anciennePosition) {
					for ($i = $anciennePosition + 1; $i <= $nouvellePosition; $i++) {
						$request = $db->prepare('UPDATE chapitre SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idCours = :idCours');
						$request->execute(array(
					        'nouvelleOrdre'		=> $i - 1,
					        'ordre'				=> $i,
					        'idCours'  			=> $inputs['idCours']
				      	));
					}
				} else if ($nouvellePosition < $anciennePosition) {
					for ($i = $anciennePosition - 1; $i >= $nouvellePosition; $i--) {
						$request = $db->prepare('UPDATE chapitre SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idCours = :idCours');
						$request->execute(array(
					        'nouvelleOrdre'		=> $i + 1,
					        'ordre'				=> $i,
					        'idCours'  			=> $inputs['idCours']
				      	));
					}
				}
				
				$inputs['ordre'] = (string) $nouvellePosition;
			}

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('UPDATE chapitre SET ordre = :ordre, titre = :titre, texte = :texte WHERE id = :id');
		      	$request->execute(array(
			        'ordre'		=> $inputs['ordre'],
			        'titre'  	=> $inputs['titre'],
			        'texte'		=> $inputs['texte'],
			        'id'		=> $_GET['id']
		      	));

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "Le chapitre a été modifié avec succès.";
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			$request = $db->prepare("SELECT ordre FROM chapitre WHERE id = :id");
			$request->execute(array(
				'id' => $_GET['id']
			));
			$donnees = $request->fetch();
			$ordre = $donnees['ordre'];

			$request = $db->prepare("SELECT MAX(ordre) AS ordreMax FROM chapitre WHERE idCours = :id");
			$request->execute(array(
				'id' => $data['idCours']
			));
			$donnees = $request->fetch();
			$ordreMax = $donnees['ordreMax'];

			// Suppression du chapitre
			$requete = $db->prepare("DELETE FROM chapitre WHERE id = :id");
			$requete->execute(array(
				'id' => $_GET['id']
			));

			for ($i = $ordre; $i <= $ordreMax; $i++) {
				$request = $db->prepare('UPDATE chapitre SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idCours = :idCours');
				$request->execute(array(
			        'nouvelleOrdre'		=> $i - 1,
			        'ordre'				=> $i,
			        'idCours'  			=> $data['idCours']
		      	));
			}

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
			if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
				$request = $db->prepare("SELECT id, nom FROM theme WHERE id = :idTheme");
				$request->execute(array(
					'idTheme' => $data['idTheme']
				));
				$donnees = $request->fetch();
				$data['idCours'] = $data['id'];
				$data['idTheme'] = $donnees['id'];
				$data['nomTheme'] = $donnees['nom'];
			}

			$fil = array('theme.php' => 'Thèmes', 'theme.php?id=' . $data['idTheme'] => $data['nomTheme'], 'cours.php?id=' . $data['idCours'] => $data['nom']);
		  	if (isset($_GET['action'])) {
		  		if ($_GET['action'] == 'ajouter')
		  			$fil['chapitre.php?action=ajouter'] = 'Ajout d\'un chapitre';
		  		else if ($_GET['action'] == 'modifier')
		  			$fil['chapitre.php?id=' . $_GET['id'] . '&action=modifier'] = 'Modification du chapitre : ' . $data['titre'];
		  		else if ($_GET['action'] == 'supprimer')
		  			$fil['chapitre.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression du chapitre : ' . $data['titre'];
		  	} else if (isset($_GET['id'])) {
		  		$fil['chapitre.php?id=' . $_GET['id']] = $data['titre'];
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
					<h3>Ajout d'un chapitre</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="chapitre.php?id=<?php echo $_GET['id']; ?>&action=ajouter">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du chapitre</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du chapitre" class="form-control input-md" required="">
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
						<!-- Ordre -->
						<?php
							$request = $db->prepare('SELECT * FROM chapitre WHERE idCours = :id ORDER BY ordre');
							$request->execute(array(
								'id' => $data['id']
							));
							$donnees = $request->fetch();

							if ($donnees) {
						?>
							<div class="form-group <?php if (isset($errors['ordre'])) echo "has-error"; ?>">
								<label class="col-md-4 control-label" for="ordre">Ordre du chapitre :</label>
								<div class="col-md-2">
									<select id="position" name="position" class="form-control">
										<option value="avant">Avant</option>
										<option value="apres">Après</option>
									</select>
								</div>
								<div class="col-md-3">
									<select id="ordre" name="ordre" class="form-control">
									<?php
										$request = $db->prepare('SELECT * FROM chapitre WHERE idCours = :id ORDER BY ordre');
										$request->execute(array(
											'id' => $data['id']
										));
										while ($donnees = $request->fetch()) {
											echo '<option value="' . $donnees['ordre'] . '">' . strip_tags(htmlspecialchars($donnees['titre'])) . '</option>';
										}
									?>
									</select>
									<span class="help-block">
										<?php
										if (isset($errors['ordre']))
										echo strip_tags(htmlspecialchars($errors['ordre']));
										?>
									</span>
								</div>
							</div>
						<?php
						}
						?>
						<!-- Texte -->
						<div class="form-group ">
							<label class="col-md-4 control-label" for="texte">Texte</label>
							<div class="col-md-5">                     
								<textarea class="form-control wysiwyg" id="texte" name="texte"></textarea>
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
					<h3>Modification du chapitre : <?php echo strip_tags(htmlspecialchars($data['titre'])); ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="chapitre.php?id=<?php echo $data['id']; ?>&action=modifier">
					<fieldset>
						<!-- Titre -->
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Titre du chapitre</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Titre du chapitre" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['titre'])); ?>">
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
						<!-- Ordre -->
						<?php
							$request = $db->prepare('SELECT id, COUNT(id) AS nbReponses FROM chapitre WHERE idCours = :idCours ORDER BY ordre');
							$request->execute(array(
								'idCours'	=> $data['idCours']
							));
							$donnees = $request->fetch();

							if (!($donnees['nbReponses'] == 1 && $donnees['id'] == $data['id'])) {
							?>
							<div class="form-group <?php if (isset($errors['ordre'])) echo "has-error"; ?>">
								<label class="col-md-4 control-label" for="ordre">Ordre du chapitre :</label>
								<div class="col-md-2">
									<select id="position" name="position" class="form-control">
										<option value="avant">Avant</option>
										<option value="apres">Après</option>
									</select>
								</div>
								<div class="col-md-3">
									<select id="ordre" name="ordre" class="form-control">
									<?php
										$request = $db->prepare('SELECT * FROM chapitre WHERE idCours = :idCours ORDER BY ordre');
										$request->execute(array(
											'idCours'	=> $data['idCours']
										));
										while ($donnees = $request->fetch()) {
											if ($data['ordre'] != $donnees['ordre']) {
												echo '<option value="' . $donnees['ordre'] . '"';
												if ($data['ordre'] + 1 == $donnees['ordre'])
													echo 'selected="selected"';
												echo '>' . strip_tags(htmlspecialchars($donnees['titre'])) . '</option>';
											}
										}
									?>
									</select>
									<span class="help-block">
										<?php
										if (isset($errors['ordre']))
										echo strip_tags(htmlspecialchars($errors['ordre']));
										?>
									</span>
								</div>
							</div>
						<?php
						}
						?>
						<!-- Texte -->
						<div class="form-group ">
							<label class="col-md-4 control-label" for="texte">Texte</label>
							<div class="col-md-5">                     
								<textarea class="form-control wysiwyg" id="texte" name="texte"><?php echo $data['texte']; ?></textarea>
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
					<h3>Suppression du chapitre : <?php echo strip_tags(htmlspecialchars($data['titre'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'un chapitre est définitive !</p>
				<p>Êtes-vous sûr de vouloir supprimer ce chapitre ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="chapitre.php?id=<?php echo $data['id']; ?>&action=supprimer">
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
					<h3>Cours : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
					<?php
					$request = $db->prepare('SELECT * FROM chapitre, cours, affiliation WHERE chapitre.id = :id AND cours.id = chapitre.idCours AND cours.idUtilisateur = :idUtilisateur');
					$request->execute(array(
						'id' => $_GET['id'],
						'idUtilisateur'		=> $_SESSION['auth']['id']
					));
					$donnees = $request->fetch();

					if ($donnees || statut('administrateur')) {
						if ($acces['supprimerChapitre'] == '1') {
							echo '<a href="chapitre.php?id=' . $data['id'] . '&action=supprimer" class="pull-right"><span class="glyphicon glyphicon-remove"></span></a>';
						}
						if ($acces['modifierChapitre'] == '1') {
							echo '<a href="chapitre.php?id=' . $data['id'] . '&action=modifier" class="pull-right"><span class="glyphicon glyphicon-pencil"></span></a>';
						}
					}
					?>
				</div>
			</div>
			<div class="row">
				<h3>Chapitre : <?php echo strip_tags(htmlspecialchars($data['titre'])); ?></h3>
				<ul>
				<?php echo $data['texte']; ?>
				</ul>
			</div>
			
			<div class="row">
				<?php
				echo '<nav aria-label="chapitre"><ul class="pager">';

				$request = $db->prepare("SELECT COUNT(id) AS nbChapitres FROM chapitre WHERE idCours = :id");
				$request->execute(array(
					'id' => $data['idCours']
				));
				$result = $request->fetch();

				$request = $db->prepare("SELECT id, ordre FROM chapitre WHERE idCours = :id AND (ordre = :precedent OR ordre = :suivant)");
				$request->execute(array(
					'id'	=> $data['idCours'],
					'precedent' => $data['ordre'] - 1,
					'suivant'	=> $data['ordre'] + 1
				));

				while ($donnees = $request->fetch()) {
					if ($data['ordre'] == 1) {
						echo ' <li class="disabled"><a href="#"> Précédent </a></li> ';
					}

					if ($result['nbChapitres'] > 1) {
						if ($donnees['ordre'] > $data['ordre']) {
							echo ' <li><a href="chapitre.php?id=' . $donnees['id'] . '"> Suivant </a></li> ';
						} else if ($donnees['ordre'] < $data['ordre']) {
							echo ' <li><a href="chapitre.php?id=' . $donnees['id'] . '"> Précédent </a></li> ';
						}
					}
					

					if ($data['ordre'] == $result['nbChapitres']) {
						echo ' <li class="disabled"><a href="#"> Suivant </a></li> ';
					}
				}
				echo '</ul></nav>';
				?>
			</div>
		<?php
		}
		?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>