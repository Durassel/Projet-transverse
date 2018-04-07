<?php
	$title = "Classe";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (isset($_GET['id'])) {
		$request = $db->prepare("SELECT * FROM classe WHERE id = :id");
		$request->execute(array(
			'id' => $_GET['id']
		));
		$data = $request->fetch();

		if (!$data) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404');
		    die();
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'modifier' && $_GET['action'] != 'supprimer') || (isset($_GET['action']) && ($_GET['action'] == 'modifier' || $_GET['action'] == 'supprimer') && !isset($_GET['id']))) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: 404');
		die();
	}

	if (isset($_GET['action']) && !connecte()) {
		header('Location: 404');
		die();
	}

	if (connecte()) {
		$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
		$request->execute(array(
			'idStatut' => $_SESSION['auth']['idStatut']
		));
		$acces = $request->fetch();

		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
			if ($acces['ajouterClasse'] == '0') {
				header('Location: 404');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
			if ($acces['modifierClasse'] == '0') {
				header('Location: 404');
				die();
			}

			$request = $db->prepare('SELECT * FROM affiliation WHERE idEtablissement = :idEtablissement AND idUtilisateur = :idUtilisateur');
			$request->execute(array(
				'idEtablissement' 	=> $data['idEtablissement'],
				'idUtilisateur'		=> $_SESSION['auth']['id']
			));
			$donnees = $request->fetch();

			if (!$donnees && !statut('administrateur')) {
				header('Location: 404');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
			if ($acces['supprimerClasse'] == '0') {
				header('Location: 404');
				die();
			}

			$request = $db->prepare('SELECT * FROM affiliation WHERE idEtablissement = :idEtablissement AND idUtilisateur = :idUtilisateur');
			$request->execute(array(
				'idEtablissement' 	=> $data['idEtablissement'],
				'idUtilisateur'		=> $_SESSION['auth']['id']
			));
			$donnees = $request->fetch();
			if (!$donnees && !statut('administrateur')) {
				header('Location: 404');
				die();
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['nom']) && !empty($_POST['annee']) && !empty($_POST['etablissement'])) {
			// Nom
			$request = $db->prepare("SELECT * FROM classe WHERE nom = :nom AND annee = :annee AND idEtablissement = :idEtablissement");
			$request->execute(array(
				'nom' 				=> $_POST['nom'],
				'annee'				=> $_POST['annee'],
				'idEtablissement'	=> $_POST['etablissement']
			));
			$donnees = $request->fetch();

			if (strlen($_POST['nom']) > 255)
				$errors['tailleNom'] = "Le nom de la classe est trop long.";
			if (!preg_match('/^[a-zA-Z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
				$errors['nom'] = "Le nom de la classe n'est pas valide.";
			if ($donnees) {
				$errors['nomUtilise'] = "Le nom de la classe est déjà utilisé pour cette année.";
			}

			// Promotion
			if (!is_numeric($_POST['annee']) || $_POST['annee'] < 2017)
    			$errors['annee'] = "L'année de promotion de la classe est invalide.";

			// Etablissement
			if (statut('administrateur')) {
			    $request = $db->prepare("SELECT * FROM etablissement WHERE id = :id");
				$request->execute(array(
					'id' => $_POST['etablissement']
				));
				$donnees = $request->fetch();

				if (!$donnees) {
					$errors['etablissement'] = "Cet établissement n'existe pas.";
				}
			}

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('INSERT INTO classe(nom, annee, idEtablissement) VALUES(:nom, :annee, :idEtablissement)');
		      	$request->execute(array(
			        'nom' 				=> $_POST['nom'], 
			        'annee' 			=> $_POST['annee'],
			        'idEtablissement'	=> $_POST['etablissement']
		      	));

				$_SESSION['flash']['success'] = "La classe a été ajouté avec succès.";
				header('Location: classe');
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
				$request = $db->prepare("SELECT * FROM classe WHERE nom = :nom AND annee = :annee");
				$request->execute(array(
					'nom' => $_POST['nom'],
					'annee' => $_POST['annee']
				));
				$donnees = $request->fetch();

				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Le nom de la classe est trop long.";
				if (!preg_match('/^[a-zA-Z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Le nom de la classe n'est pas valide.";
				if ($donnees) {
					$errors['nomUtilise'] = "Le nom de la classe est déjà utilisé pour cette année.";
				}

				if (!isset($errors['tailleNom']) && !isset($errors['nom']) && !isset($errors['nomUtilise'])) {
					$inputs['nom'] = $_POST['nom'];
				}
			}

			// Promotion
			if (!empty($_POST['annee']) && strcmp($_POST['annee'], $inputs['annee']) !== 0) {
				if (!is_numeric($_POST['annee']))
    				$errors['annee'] = "L'année de promotion de la classe est invalide.";

				if (!isset($errors['annee'])) {
					$inputs['annee'] = $_POST['annee'];
				}
			}
			
			// Etablissement
			if (statut('administrateur')) {
				if (!empty($_POST['etablissement']) && strcmp($_POST['etablissement'], $inputs['idEtablissement']) !== 0) {
				    $request = $db->prepare("SELECT * FROM etablissement WHERE id = :id");
					$request->execute(array(
						'id' => $_POST['etablissement']
					));
					$donnees = $request->fetch();

					if (!$donnees) {
						$errors['etablissement'] = "Cet établissement n'existe pas.";
					}

					if (!isset($errors['etablissement'])) {
						$inputs['idEtablissement'] = $_POST['etablissement'];
					}
				}
			}

			// Erreur
			if (empty($errors)) {
				if (statut('administrateur')) {
					$request = $db->prepare('UPDATE classe SET nom = :nom, annee = :annee, idEtablissement = :idEtablissement WHERE id = :id');
			      	$request->execute(array(
				        'nom'  				=> $inputs['nom'],
				        'annee'  			=> $inputs['annee'],
				        'idEtablissement'  	=> $inputs['idEtablissement'],
				        'id'				=> $inputs['id']
			      	));
			    } else {
			    	$request = $db->prepare('UPDATE classe SET nom = :nom, annee = :annee WHERE id = :id');
			      	$request->execute(array(
				        'nom'  				=> $inputs['nom'],
				        'annee'  			=> $inputs['annee'],
				        'id'				=> $inputs['id']
			      	));
			    }

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "La classe a été modifiée avec succès.";
				header('Location: classe');
	    		die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression de la classe
			$request = $db->prepare("DELETE FROM classe WHERE id = :id");
			$request->execute(array(
				'id' => $data['id']
			));

			// Suppression des étudiants en charge de cette classe (professionnel)
			$request = $db->prepare("DELETE FROM professionnel WHERE idClasse = :id");
			$request->execute(array(
				'id' => $data['id']
			));

			// Suppression des élèves de la classe
			$request = $db->prepare("UPDATE utilisateur SET idClasse = 0 WHERE idClasse = :id");
			$request->execute(array(
				'id' => $data['id']
			));

			header('Location: classe');
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: classe');
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
				<h2 class="header-page">Classes</h2>
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
			$fil = array('classe' => 'Classes');
		  	if (isset($_GET['action'])) {
		  		if ($_GET['action'] == 'ajouter')
		  			$fil['classe-ajouter'] = 'Ajout d\'une classe';
		  		else if ($_GET['action'] == 'modifier')
		  			$fil['classe-' . $_GET['id'] . '-modifier'] = 'Modification de la classe : ' . $data['nom'];
		  		else if ($_GET['action'] == 'supprimer')
		  			$fil['classe-' . $_GET['id'] . '-supprimer'] = 'Suppression de la classe : ' . $data['nom'];
		  	} else if (isset($_GET['id'])) {
		  		$request = $db->prepare('SELECT id, nom FROM etablissement WHERE id = :idEtablissement');
		  		$request->execute(array(
		  			'idEtablissement' => $data['idEtablissement']
		  		));
		  		$donnees = $request->fetch();
		  		$fil['etablissement-' . $donnees['id']] = $donnees['nom'];
		  		$fil['classe-' . $_GET['id']] = $data['nom'];
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
		<?php if (isset($_GET['action']) && $_GET['action'] == 'ajouter') { ?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Ajout d'une classe</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="classe-ajouter">
					<fieldset>
						<!-- Nom -->
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom de la classe</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom de la classe" class="form-control input-md" required="">
							  	<span class="help-block">
						            <?php
						            if (isset($errors['tailleNom']))
						            	echo $errors['tailleNom'];
 									if (isset($errors['nomUtilise']))
						            	echo $errors['nomUtilise'];
						            if (isset($errors['nom']))
						                echo $errors['nom'];
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Année -->
						<div class="form-group <?php if (isset($errors['annee'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="annee">Promotion</label>  
							<div class="col-md-5">
								<input id="annee" name="annee" type="text" placeholder="Année" class="form-control input-md" required="">
							  	<span class="help-block">
						            <?php
						            	if (isset($errors['annee']))
						            		echo $errors['annee'];
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Etablissement -->
						<div class="form-group <?php if (isset($errors['directeur'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="etablissement">Etablissement :</label>
							<div class="col-md-5">
								<select id="etablissement" name="etablissement" class="form-control">
								<?php
									if (statut('administrateur')) {
										$request = $db->query('SELECT * FROM etablissement');
									} else {
										$request = $db->prepare('SELECT * FROM etablissement WHERE idDirecteur = :id');
										$request->execute(array(
											'id' => $_SESSION['auth']['id']
										));
									}
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '">' . $donnees['nom'] . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['etablissement']))
											echo $errors['etablissement'];
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
					<h3>Modification de la classe : <?php echo htmlspecialchars($data['nom']); ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="classe-<?php echo $data['id']; ?>-modifier">
					<fieldset>
						<!-- Nom -->
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom de la classe</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom de la classe" class="form-control input-md" value="<?php echo $data['nom']; ?>">
							  	<span class="help-block">
						            <?php
						            if (isset($errors['tailleNom']))
						            	echo $errors['tailleNom'];
 									if (isset($errors['nomNombre']))
						            	echo $errors['nomNombre'];
						            if (isset($errors['nom']))
						                echo $errors['nom'];
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Année -->
						<div class="form-group <?php if (isset($errors['annee'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="annee">Promotion</label>  
							<div class="col-md-5">
								<input id="annee" name="annee" type="text" placeholder="Année" class="form-control input-md" value="<?php echo $data['annee']; ?>">
							  	<span class="help-block">
						            <?php
						            	if (isset($errors['annee']))
						            		echo $errors['annee'];
						            ?>
					            </span>
					         </div>
						</div>
						<!-- Etablissement -->
						<?php
						if (statut('administrateur')) {
						?>
						<div class="form-group <?php if (isset($errors['directeur'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="etablissement">Etablissement :</label>
							<div class="col-md-5">
								<select id="etablissement" name="etablissement" class="form-control">
								<?php
									$request = $db->query('SELECT * FROM etablissement');
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '"';
										if ($data['idEtablissement'] == $donnees['id'])
											echo 'selected="selected"';
										echo '>' . $donnees['nom'] . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['etablissement']))
											echo $errors['etablissement'];
									?>
								</span>
							</div>
						</div>
						<?php } ?>
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
					<h3>Suppression de la classe : <?php echo htmlspecialchars($data['nom']); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>Êtes-vous sûr de vouloir supprimer cette classe ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="classe-<?php echo $data['id']; ?>-supprimer">
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
		// Affichage d'une classe
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3><?php
						echo $data['nom'];

						if (connecte()) {
							$request = $db->prepare('SELECT * FROM affiliation WHERE idEtablissement = :idEtablissement AND idUtilisateur = :idUtilisateur');
							$request->execute(array(
								'idEtablissement' 	=> $data['idEtablissement'],
								'idUtilisateur'		=> $_SESSION['auth']['id']
							));
							$donnees = $request->fetch();
							if ($donnees || statut('administrateur')) {
								if ($acces['supprimerClasse']) {
									echo '<a href="classe-' . $data['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove pull-right"></span></a>';
								}
								if ($acces['modifierClasse']) {
									echo '<a href="classe-' . $data['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil pull-right"></span></a>';
								}
							}
						}
						?>
						<span class="small"> / Année : <?php echo $data['annee']; ?></span>
					</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
					<h4>Professeurs :</h4>
					<?php
					$request = $db->prepare("SELECT utilisateur.id, utilisateur.prenom, utilisateur.nom FROM affiliation, utilisateur, statut WHERE affiliation.idClasse = :id AND affiliation.idUtilisateur = utilisateur.id AND utilisateur.idStatut = statut.id AND statut.nom = 'professeur'");
					$request->execute(array(
						'id' => $data['id']
					));

					echo '<ul>';
					while ($donnees = $request->fetch()) {
						echo '<li><a href="profil-' . $donnees['id'] . '">' . $donnees['prenom'] . ' ' . $donnees['nom'] . '</a></li>';
					}
					echo '</ul>';
					?>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
					<h4>Elèves :</h4>
					<?php
					$request = $db->prepare("SELECT utilisateur.id, utilisateur.prenom, utilisateur.nom FROM affiliation, utilisateur, statut WHERE affiliation.idClasse = :id AND affiliation.idUtilisateur = utilisateur.id AND utilisateur.idStatut = statut.id AND statut.nom = 'élève'");
					$request->execute(array(
						'id' => $data['id']
					));

					echo '<ul>';
					while ($donnees = $request->fetch()) {
						echo '<li><a href="profil-' . $donnees['id'] . '">' . $donnees['prenom'] . ' ' . $donnees['nom'] . '</a></li>';
					}
					echo '</ul>';
					?>
				</div>
			</div>
		<?php
		} else {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Liste des classes<?php if (connecte() && $acces['ajouterClasse']) echo '<a href="classe-ajouter" class="btn btn-primary pull-right">Ajouter une classe</a>'; ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<table class="table">
					<thead>
						<tr>
							<th class="col-lg-3 text-center">Etablissement</th>
							<th class="col-lg-7 text-center">Nom de la classe</th>
							<th class="col-lg-7 text-center">Année</th>
							<th class="col-lg-2 text-center"><?php if (connecte() && ($acces['modifierClasse'] == 1 || $acces['supprimerClasse'] == 1)) echo 'Actions'; ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$request = $db->query("SELECT COUNT(id) AS nbClasses FROM classe");
						$result = $request->fetch();

						$nbClasses = $result['nbClasses'];
						$perPage = 20;
						$nbPage = ceil($nbClasses/$perPage);

						if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbPage) {
							$cPage = $_GET['page'];
						} else {
							$cPage = 1;
						}

						$request = $db->query("SELECT classe.id AS idClasse, classe.nom AS nomClasse, classe.annee, etablissement.id AS idEtablissement, etablissement.nom AS nomEtablissement FROM etablissement, classe WHERE classe.idEtablissement = etablissement.id ORDER BY classe.id DESC LIMIT " . (($cPage - 1) * $perPage) . ", $perPage");
						while ($donnees = $request->fetch()) {
							echo '<tr>
							<td><a href="etablissement-' . $donnees['idEtablissement'] . '">' . $donnees['nomEtablissement'] . '</a></td>
							<td><a href="classe-' . $donnees['idClasse'] . '">' . $donnees['nomClasse'] . '</a></td>
							<td>' . $donnees['annee'] . '</td>
							<td>';

							if (connecte()) {
								$requete = $db->prepare('SELECT * FROM affiliation WHERE idEtablissement = :idEtablissement AND idUtilisateur = :idUtilisateur');
								$requete->execute(array(
									'idEtablissement' 	=> $donnees['idEtablissement'],
									'idUtilisateur'		=> $_SESSION['auth']['id']
								));
								$result = $requete->fetch();
								if ($result || statut('administrateur')) {
									if ($acces['modifierClasse'] == 1) {
										echo '<a href="classe-' . $donnees['idClasse'] . '-modifier"><span class="glyphicon glyphicon-pencil"></span></a>';
									}
									if ($acces['supprimerClasse'] == 1) {
										echo '<a href="classe-' . $donnees['idClasse'] . '-supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
									}
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
	                            echo '<li class="active"><a href="classe-p' . $i . '">' . $i . '</a></li>';
	                        else
	                            echo '<li><a href="classe-p' . $i . '">' . $i . '</a></li>';
	                    }
	                ?>
	                </ul>
	            </nav>
	        </div>
	<?php } ?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>