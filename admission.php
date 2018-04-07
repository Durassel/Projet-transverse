<?php
	$title = "Admission";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	// L'utilisateur doit exister
	if (isset($_GET['id'])) {
		$request = $db->prepare("SELECT utilisateur.id, utilisateur.nom AS nomUtilisateur, utilisateur.prenom, utilisateur.idStatut, statut.nom AS nomStatut FROM utilisateur, statut WHERE utilisateur.id = :id AND statut.id = utilisateur.idStatut");
		$request->execute(array(
			'id' => $_GET['id']
		));
		$data = $request->fetch();

		if (!$data) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	} else {
		?><script type="text/javascript">javascript:history.back();</script><?php
	    header('Location: 404.php');
	    die();
	}

	// L'utilisateur doit appartenir à l'établissement
	if (isset($_GET['etablissement'])) {
		$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :id AND idEtablissement = :idEtablissement");
		$request->execute(array(
			'id' 				=> $_GET['id'],
			'idEtablissement'	=> $_GET['etablissement']
		));
		$result = $request->fetch();

		if (!$result) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	}

	if (isset($_GET['classe'])) {
		$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :id AND idClasse = :idClasse");
		$request->execute(array(
			'id' 		=> $_GET['id'],
			'idClasse'	=> $_GET['classe']
		));
		$result = $request->fetch();

		if (!$result) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'supprimer')) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: profil.php?id=' . $_GET['id']);
		die();
	}

	$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
	$request->execute(array(
		'idStatut' => $_SESSION['auth']['idStatut']
	));
	$acces = $request->fetch();

	if ($_GET['action'] == 'ajouter') {
		if ($acces['ajouterUtilisateurClasse'] == '0') {
			header('Location: profil.php?id' . $_GET['id']);
			die();
		}

		if ($acces['ajouterUtilisateurEtablissement'] == '0') {
			header('Location: profil.php?id' . $_GET['id']);
			die();
		}

		if ($data['nomStatut'] != 'élève' && $data['nomStatut'] != 'professeur' && $data['nomStatut'] != 'directeur') {
			header('Location: profil.php?id' . $_GET['id']);
			die();
		}

		if ($data['nomStatut'] == 'élève') {
			if (isset($_GET['etablissement'])) {
				// Ajout d'une classe à l'utilisateur
				$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :idUtilisateur AND idEtablissement = :idEtablissement");
				$request->execute(array(
					'idUtilisateur'		=> $data['id'],
					'idEtablissement'	=> $_GET['etablissement']
				));
				$donnees = $request->fetch();
				if ($donnees && $donnees['idClasse'] != '0') {
					$_SESSION['flash']['danger'] = "L'utilisateur possède déjà une classe.";
					header('Location: profil.php?id=' . $_GET['id']);
					die();
				}
			} else {
				// Ajout d'un établissement à l'utilisateur
				$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :idUtilisateur");
				$request->execute(array(
					'idUtilisateur'		=> $data['id']
				));
				$donnees = $request->fetch();
				if ($donnees) {
					$_SESSION['flash']['danger'] = "L'utilisateur possède déjà un établissement.";
					header('Location: profil.php?id=' . $_GET['id']);
					die();
				}
			}
		}
	} else if ($_GET['action'] == 'supprimer') {
		if ($acces['supprimerUtilisateurClasse'] == '0') {
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}

		if ($acces['supprimerUtilisateurEtablissement'] == '0') {
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}

		// Vérifier que l'établissement à supprimer appartienne à celui qui supprime
		if (isset($_GET['etablissement'])) {
			$request = $db->prepare('SELECT * FROM affiliation, etablissement WHERE affiliation.idUtilisateur = :idUtilisateur AND affiliation.idEtablissement = :idEtablissement AND etablissement.id = affiliation.idEtablissement AND etablissement.idDirecteur = :idDirecteur');
			$request->execute(array(
				'idUtilisateur'		=> $data['id'],
				'idEtablissement'	=> $_GET['etablissement'],
				'idDirecteur'		=> $_SESSION['auth']['id']
			));
		} else if (isset($_GET['classe'])) {
			$request = $db->prepare('SELECT * FROM affiliation, etablissement WHERE affiliation.idUtilisateur = :idUtilisateur AND affiliation.idClasse = :idClasse AND etablissement.id = affiliation.idEtablissement AND etablissement.idDirecteur = :idDirecteur');
			$request->execute(array(
				'idUtilisateur'		=> $data['id'],
				'idClasse'		=> $_GET['classe'],
				'idDirecteur'	=> $_SESSION['auth']['id']
			));
		}
		$donnees = $request->fetch();
		if (!$donnees && !statut('administrateur')) {
			$_SESSION['flash']['danger'] = "L'utilisateur n'appartient pas à votre établissement.";
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}

		// Pour supprimer la classe ou l'établissement d'un utilisateur, il faut qu'il en ait un
		$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :idUtilisateur");
		$request->execute(array(
			'idUtilisateur'	=> $data['id']
		));
		$donnees = $request->fetch();
		if (!$donnees) {
			$_SESSION['flash']['danger'] = "L'utilisateur n'appartient pas à un établissement.";
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}
	}

	// TRAITEMENT
	if ($_GET['action'] == 'ajouter') {
		if (isset($_POST['etablissement'])) {
			$ide = $_POST['etablissement'];
			if (isset($_POST['classe'])) {
				// Vérifier que la classe appartienne à l'établissement
				$request = $db->prepare("SELECT * FROM classe WHERE id = :idClasse AND idEtablissement = :idEtablissement");
				$request->execute(array(
					'idClasse'			=> $_POST['classe'],
					'idEtablissement'	=> $_POST['etablissement']
				));
				$donnees = $request->fetch();
				if (!$donnees) {
					unset($_POST['classe']);
				}
			}
		} else {
			$ide = null;
		}

		$errors = array();

		if (!empty($_POST['etablissement']) && !empty($_POST['classe'])) {
			// Vérifier que la classe appartienne à l'établissement
			$request = $db->prepare("SELECT * FROM classe WHERE id = :idClasse AND idEtablissement = :idEtablissement");
			$request->execute(array(
				'idClasse'			=> $_POST['classe'],
				'idEtablissement'	=> $_POST['etablissement']
			));
			$donnees = $request->fetch();
			if ($donnees) {
				// Ajout d'une classe
				if (isset($_GET['etablissement'])) {
					// Pour les élèves : modifier idClasse
					if ($data['nomStatut'] == 'élève') {
						// Vérifier que l'idClasse et l'idEtablissement conviennent
						$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :idUtilisateur");
						$request->execute(array(
							'idUtilisateur'	=> $data['id']
						));
						$donnees = $request->fetch();
						if ($donnees['idEtablissement'] != $_POST['etablissement']) {
							$errors['etablissement'] = "L'établissement choisi ne correspond pas à l'établissement de l'utilisateur.";
						}

						if (empty($errors)) {
							$request = $db->prepare("UPDATE affiliation SET idClasse = :classe WHERE idUtilisateur = :idUtilisateur AND idEtablissement = :idEtablissement");
							$request->execute(array(
								'classe'			=> $_POST['classe'],
								'idUtilisateur'		=> $data['id'],
								'idEtablissement'	=> $_POST['etablissement']
							));

							$_SESSION['flash']['success'] = "L'utilisateur a été ajouté avec succès à la classe.";
							header('Location: profil.php?id=' . $data['id']);
				    		die();
						}
					} else { // Pour les autres, vérifier qu'ils n'appartiennent pas déjà à la classe en question, sinon ajouter ligne
						$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :idUtilisateur");
						$request->execute(array(
							'idUtilisateur'	=> $data['id']
						));
						$donnees = $request->fetch();
						if ($donnees['idEtablissement'] == $_POST['etablissement'] && $donnees['idClasse'] == $_POST['classe']) {
							$errors['etablissement'] = "L'utilisateur appartient déjà à la classe de cet établissement.";
						}

						if (empty($errors)) {
							if ($donnees['idClasse'] == '0') {
								$request = $db->prepare("UPDATE affiliation SET idClasse = :idClasse WHERE idUtilisateur = :idUtilisateur AND idEtablissement = :idEtablissement");
								$request->execute(array(
									'idClasse'			=> $_POST['classe'],
									'idUtilisateur'		=> $data['id'],
									'idEtablissement'	=> $_POST['etablissement']
								));
							} else {
								$request = $db->prepare("INSERT INTO affiliation(idUtilisateur, idClasse, idEtablissement) VALUES(:idUtilisateur, :idClasse, :idEtablissement)");
								$request->execute(array(
									'idUtilisateur'		=> $data['id'],
									'idClasse'			=> $_POST['classe'],
									'idEtablissement'	=> $_POST['etablissement']
								));
							}
							

							$_SESSION['flash']['success'] = "L'utilisateur a été ajouté avec succès à l'établissement et à la classe.";
							header('Location: profil.php?id=' . $data['id']);
				    		die();
						}
					}
				} else { // Ajout d'un établissement et d'une classe
					// Vérifier que l'utilisateur n'appartienne pas déjà à la classe de d'établissement
					$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :idUtilisateur");
					$request->execute(array(
						'idUtilisateur'		=> $data['id']
					));
					$donnees = $request->fetch();
					if ($donnees['idEtablissement'] == $_POST['etablissement'] && $donnees['idClasse'] == $_POST['classe']) {
						$errors['etablissement'] = "L'utilisateur appartient déjà à l'établissement et à la classe.";
					}

					if (empty($errors)) {
						if ($donnees['idClasse'] == '0') {
							$request = $db->prepare("UPDATE affiliation SET idClasse = :classe WHERE idUtilisateur = :idUtilisateur AND idEtablissement = :idEtablissement");
							$request->execute(array(
								'classe'			=> $_POST['classe'],
								'idUtilisateur'		=> $data['id'],
								'idEtablissement'	=> $_POST['etablissement']
							));
						} else {
							$request = $db->prepare("INSERT INTO affiliation(idUtilisateur, idClasse, idEtablissement) VALUES(:idUtilisateur, :idClasse, :idEtablissement)");
							$request->execute(array(
								'idUtilisateur'		=> $data['id'],
								'idClasse'			=> $_POST['classe'],
								'idEtablissement'	=> $_POST['etablissement']
							));
						}

						$_SESSION['flash']['success'] = "L'utilisateur a été ajouté avec succès à l'établissement et à la classe.";
						header('Location: profil.php?id=' . $data['id']);
			    		die();
					}
				}
			} else {
				$errors['classe'] = 'Cette classe n\'appartient pas à l\'établissement';
			}
		} else if (!empty($_POST['etablissement']) && !isset($_POST['classe']) && !empty($_POST['valider'])) {
			// Vérifier que l'établissement ne possède aucune classe
			$request = $db->prepare("SELECT * FROM classe WHERE idEtablissement = :idEtablissement");
			$request->execute(array(
				'idEtablissement'	=> $_POST['etablissement']
			));
			$donnees = $request->fetch();
			if (!$donnees) {
				// Vérifier que l'utilisateur n'appartienne pas déjà à l'établissement
				$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :idUtilisateur AND idEtablissement = :idEtablissement");
				$request->execute(array(
					'idUtilisateur'		=> $data['id'],
					'idEtablissement'	=> $_POST['etablissement']
				));
				$donnees = $request->fetch();
				if ($donnees) {
					$errors['etablissement'] = "L'utilisateur appartient déjà à l'établissement.";
				}

				if (empty($errors)) {
					$request = $db->prepare("INSERT INTO affiliation(idUtilisateur, idClasse, idEtablissement) VALUES(:idUtilisateur, :idClasse, :idEtablissement)");
					$request->execute(array(
						'idUtilisateur'		=> $data['id'],
						'idClasse'			=> '0',
						'idEtablissement'	=> $_POST['etablissement']
					));

					$_SESSION['flash']['success'] = "L'utilisateur a été ajouté avec succès à l'établissement.";
					header('Location: profil.php?id=' . $data['id']);
		    		die();
				}
			}
		}
	} else if ($_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression de l'affiliation
			if (isset($_GET['etablissement'])) {
				$request = $db->prepare("DELETE FROM affiliation WHERE idUtilisateur = :idUtilisateur AND idEtablissement = :idEtablissement");
				$request->execute(array(
					'idUtilisateur' 	=> $data['id'],
					'idEtablissement'	=> $_GET['etablissement']
				));
			} else if (isset($_GET['classe'])) { // Suppression de la classe
				$request = $db->prepare("UPDATE affiliation SET idClasse = :classe WHERE idUtilisateur = :idUtilisateur AND idClasse = :idClasse");
				$request->execute(array(
					'classe'			=> '0',
					'idUtilisateur' 	=> $data['id'],
					'idClasse'			=> $_GET['classe']
				));
			}

			// Supprimer les parrainages de l'élève avec ceux de sa classe
			$request = $db->prepare("DELETE FROM parrainage WHERE idParrain = :idUtilisateur OR idFilleul = :idUtilisateur");
			$request->execute(array(
				'idUtilisateur' 	=> $data['id']
			));

			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: profil.php?id=' . $_GET['id']);
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
				<h2 class="header-page">Admission</h2>
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
			$fil = array('profil.php?id=' . $_GET['id'] => 'Profil');
	  		if ($_GET['action'] == 'ajouter')
	  			$fil['admission.php?id=' . $_GET['id'] . '&action=ajouter'] = 'Ajout d\'un établissement et d\'une classe';
	  		else if ($_GET['action'] == 'supprimer')
	  			$fil['admission.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression de l\'établissement et de la classe';

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
		if ($_GET['action'] == 'ajouter') {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3><?php echo '<a href="profil.php?id=' . $data['id'] . '">' . strip_tags(htmlspecialchars($data['prenom'])) . ' ' . strip_tags(htmlspecialchars($data['nomUtilisateur'])) . '</a>'; ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" action="admission-<?php echo $_GET['id']; ?>-<?php if (isset($_GET['classe'])) echo $_GET['classe'] .'-'; else if (isset($_GET['etablissement'])) echo $_GET['etablissement'] .'-'; echo $_GET['action']; ?>" method="post" name="changement">
					<fieldset>
						<!-- Etablissement -->
						<div class="form-group <?php if (isset($errors['etablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="etablissement">Etablissement :</label>
							<div class="col-md-5">
								<select id="etablissement" name="etablissement" class="form-control" onchange="changement.submit()">
									<option value="-1">Choisissez un établissement</option>
								<?php
									if ($_SESSION['auth']['statut'] == 'administrateur') {
										$request = $db->query('SELECT id, nom FROM etablissement');
									} else if ($_SESSION['auth']['statut'] == 'directeur') {
										$request = $db->prepare('SELECT DISTINCT etablissement.id, etablissement.nom FROM etablissement, affiliation WHERE affiliation.idUtilisateur = :id AND affiliation.idEtablissement = etablissement.id');
										$request->execute(array(
											'id' => $_SESSION['auth']['id']
										));
									}

									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '" ';
										if (isset($_GET['etablissement']) && $_GET['etablissement'] == $donnees['id']) {
											echo "selected=\"selected\"";
											$ide = $result['idEtablissement'];
										}
										if (isset($ide) && $ide == $donnees['id'])
											echo 'selected=\"selected\"';
										echo '>' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['etablissement']))
											echo strip_tags(htmlspecialchars($errors['etablissement']));
									?>
								</span>
							</div>
						</div>
						<!-- Classe -->
						<?php
						if (isset($ide) && $ide != -1) {
							$request = $db->prepare('SELECT id, nom FROM classe WHERE idEtablissement = :idEtablissement ORDER BY id');
							$request->execute(array(
								'idEtablissement' => $ide
							));

							if ($request->rowCount() > 0) {
							?>

								<div class="form-group <?php if (isset($errors['classe'])) echo "has-error"; ?>">
									<label class="col-md-4 control-label" for="classe">Classe :</label>
									<div class="col-md-5">
										<select id="classe" name="classe" class="form-control">
										<?php
											while ($donnees = $request->fetch()) {
											?>
												<option value="<?php echo $donnees['id']; ?>" <?php echo((isset($classe) && $classe == $donnees['id'])?" selected=\"selected\"":null); ?>><?php echo strip_tags(htmlspecialchars($donnees['nom'])); ?></option>
											<?php
											}
										?>
										</select>
										<span class="help-block">
											<?php
												if (isset($errors['classe']))
													echo strip_tags(htmlspecialchars($errors['classe']));
											?>
										</span>
									</div>
								</div>
						<?php }
						} ?>
						<!-- Button -->
						<div class="form-group text-center">
							<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
								<input class="btn btn-primary btn-block" type="submit" name="valider" value="Ajouter"/>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		<?php
		} else if ($_GET['action'] == 'supprimer') {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3><a href="profil.php?id=<?php echo $data['id']; ?>"><?php echo strip_tags(htmlspecialchars($data['prenom'])) . ' ' . strip_tags(htmlspecialchars($data['nomUtilisateur'])); ?></a></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>Êtes-vous sûr de vouloir supprimer l'utilisateur de <?php if (isset($_GET['etablissement'])) echo "l'établissement"; else if (isset($_GET['classe'])) echo "la classe"; ?> ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="admission.php?id=<?php echo $data['id']; ?>&<?php if (isset($_GET['etablissement'])) echo 'etablissement=' . strip_tags(htmlspecialchars($_GET['etablissement'])); else if (isset($_GET['classe'])) echo 'classe=' . strip_tags(htmlspecialchars($_GET['classe'])); ?>&action=supprimer">
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
		}
		?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>