<?php
	$title = "Admission";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (!connecte()) {
		header('Location: 404.php');
		die();
	}

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'supprimer')) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: 404.php');
		die();
	}

	if (!isset($_GET['action']) || !isset($_GET['id'])) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: 404.php');
		die();
	}

	if ($_GET['action'] == 'ajouter') {
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
	} else if ($_GET['action'] == 'supprimer') {
		$utilisateurs = array();
		// Vérifier l'existence du parrainage
		$request = $db->prepare("SELECT parrainage.id AS idParrainage, parrainage.idParrain, parrainage.idFilleul, utilisateur.id AS idUtilisateur, utilisateur.prenom, utilisateur.nom FROM parrainage, utilisateur WHERE parrainage.id = :id AND utilisateur.idParrainage = parrainage.id");
		$request->execute(array(
			'id'	=> $_GET['id']
		));
		$i = 0;
		while ($data = $request->fetch()) {
			if ($i == 0)
				$utilisateurs['idParrainage'] = $data['idParrainage'];
			$utilisateurs[$data['idUtilisateur']] = $data['prenom'] . ' ' . $data['nom'];
			$i++;
		}
			

		if ($i == 0) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	}

	$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
	$request->execute(array(
		'idStatut' => $_SESSION['auth']['idStatut']
	));
	$acces = $request->fetch();

	if ($_GET['action'] == 'ajouter') {
		if ($acces['ajouterParrainage'] == '0') {
			header('Location: profil.php?id' . $_GET['id']);
			die();
		}

		if ($data['nomStatut'] != 'élève') {
			header('Location: profil.php?id' . $_GET['id']);
			die();
		}

		// Si seul élève dans la classe
		$request = $db->prepare("SELECT idClasse FROM affiliation WHERE idUtilisateur = :id");
		$request->execute(array(
			'id'	=> $data['id']
		));
		$donnees = $request->fetch();

		$request = $db->prepare("SELECT COUNT(affiliation.id) AS nbEleves, affiliation.idUtilisateur FROM affiliation, utilisateur, statut WHERE affiliation.idClasse = :id AND affiliation.idUtilisateur = utilisateur.id AND statut.id = utilisateur.idStatut AND statut.nom = 'élève'");
		$request->execute(array(
			'id'	=> $donnees['idClasse']
		));
		$donnees = $request->fetch();

		if ($donnees['nbEleves'] == 1 && $donnees['idUtilisateur'] == $data['id']) {
			$_SESSION['flash']['danger'] = "L'élève est seul dans la classe.";
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}

		// Elève doit appartenir à une classe
		$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :id");
		$request->execute(array(
			'id'	=> $data['id']
		));
		$donnees = $request->fetch();
		if (!$donnees || $donnees['idClasse'] == '0') {
			$_SESSION['flash']['danger'] = "L'élève n'appartient à aucune classe.";
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}
	} else if ($_GET['action'] == 'supprimer') {
		if ($acces['supprimerParrainage'] == '0') {
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}
	}

	if ($_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['statut']) && !empty($_POST['eleve'])) {
			// Statut
			if ($_POST['statut'] != '1' && $_POST['statut'] != '0')
				$errors['statut'] = "Ce statut n'existe pas.";

			if ($_POST['eleve'] == $data['id'])
				$errors['eleve'] = "L'élève ne peut pas être parrainé par lui-même.";

			// Elève : doit appartenir à la classe
			$request = $db->prepare("SELECT idClasse FROM affiliation WHERE idUtilisateur = :idUtilisateur");
			$request->execute(array(
				'idUtilisateur'	=> $data['id']
			));
			$donnees = $request->fetch();

			$request = $db->prepare("SELECT * FROM affiliation WHERE idClasse = :idClasse AND idUtilisateur = :idUtilisateur");
			$request->execute(array(
				'idClasse'		=> $donnees['idClasse'],
				'idUtilisateur'	=> $_POST['eleve']
			));
			$donnees = $request->fetch();
			if (!$donnees)
				$errors['eleve'] = "L'élève n'appartient pas à la classe.";

			// Vérifier que le parrainage n'existe pas déjà
			if ($_POST['statut'] == '1') { // Parrain
				$parrain = $_POST['eleve'];
				$filleul = $data['id'];
			} else if ($_POST['statut'] == '0') { // Filleul
				$parrain = $data['id'];
				$filleul = $_POST['eleve'];
			}
			$request = $db->prepare("SELECT * FROM parrainage WHERE idParrain = :idParrain AND idFilleul = :idFilleul");
			$request->execute(array(
				'idParrain'	=> $parrain,
				'idFilleul'	=> $filleul
			));
			$donnees = $request->fetch();
			if ($donnees) {
				$errors['eleve'] = "Ce parrainage existe déjà.";
			}

			$request = $db->prepare("SELECT * FROM parrainage WHERE idParrain = :idParrain AND idFilleul = :idFilleul");
			$request->execute(array(
				'idParrain'	=> $filleul,
				'idFilleul'	=> $parrain
			));
			$donnees = $request->fetch();
			if ($donnees) {
				$errors['eleve'] = "Ce parrainage existe déjà.";
			}

			if (empty($errors)) {
				if ($_POST['statut'] == '1') { // Parrain
					$parrain = $_POST['eleve'];
					$filleul = $data['id'];
				} else if ($_POST['statut'] == '0') { // Filleul
					$parrain = $data['id'];
					$filleul = $_POST['eleve'];
				}

				$request = $db->prepare("INSERT INTO parrainage(idParrain, idFilleul) VALUES(:idParrain, :idFilleul)");
				$request->execute(array(
					'idParrain'	=> $parrain,
					'idFilleul'	=> $filleul
				));

				$_SESSION['flash']['success'] = "Le parrainage a été ajouté avec succès.";
				header('Location: profil.php?id=' . $data['id']);
	    		die();
			}
		}
	} else if ($_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression des idParrainage des élèves
			$request = $db->prepare("SELECT * FROM parrainage WHERE id = :id");
			$request->execute(array(
				'id'	=> $utilisateurs['idParrainage']
			));
			$donnees = $request->fetch();

			// Suppression du parrainage
			$request = $db->prepare("DELETE FROM parrainage WHERE id = :id");
			$request->execute(array(
				'id'	=> $utilisateurs['idParrainage']
			));

			$_SESSION['flash']['success'] = "Le parrainage a été supprimé avec succès.";
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
	  			$fil['parrainage.php?id=' . $_GET['id'] . '&action=ajouter'] = 'Ajout d\'un parrainage';
	  		else if ($_GET['action'] == 'supprimer')
	  			$fil['parrainage.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression du parrainage';

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
				<form enctype="multipart/form-data" class="form-horizontal" action="parrainage.php?id=<?php echo $data['id']; ?>&action=ajouter" method="post">
					<fieldset>
						<!-- Parrain / Filleul -->
						<div class="form-group <?php if (isset($errors['statut'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="statut">Statut :</label>
							<div class="col-md-5">
								<select id="statut" name="statut" class="form-control">
									<option value="1">Parrain</option>
									<option value="0">Filleul</option>
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['statut']))
											echo strip_tags(htmlspecialchars($errors['statut']));
									?>
								</span>
							</div>
						</div>
						<!-- Sélection de l'élève -->
						<div class="form-group <?php if (isset($errors['eleve'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="eleve">Elève :</label>
							<div class="col-md-5">
								<select id="eleve" name="eleve" class="form-control">
								<?php
									// Sélectionnez les élèves de la même classe
									$request = $db->prepare('SELECT idClasse FROM affiliation WHERE idUtilisateur = :id');
									$request->execute(array(
										'id' => $data['id']
									));
									$donnees = $request->fetch();

									$requete = $db->prepare("SELECT utilisateur.id, utilisateur.nom, utilisateur.prenom FROM utilisateur, statut, affiliation WHERE affiliation.idClasse = :idClasse AND affiliation.idUtilisateur = utilisateur.id AND utilisateur.idStatut = statut.id AND statut.nom = 'élève'");
									$requete->execute(array(
										'idClasse' => $donnees['idClasse'],

									));
									while ($result = $requete->fetch()) {
										if ($data['id'] != $result['id'])
											echo '<option value="' . $result['id'] . '">' . strip_tags(htmlspecialchars($result['prenom'])) . ' ' . strip_tags(htmlspecialchars($result['nom'])) . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['eleve']))
											echo strip_tags(htmlspecialchars($errors['eleve']));
									?>
								</span>
							</div>
						</div>
						<!-- Button -->
						<div class="form-group text-center">
							<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
								<input class="btn btn-primary btn-block" type="submit" value="Ajouter"/>
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
					<h3>Parrainage : <?php
					$i = 0;
					foreach ($utilisateurs as $id => $prenom) {
						if ($i > 0)
							echo '<a href="profil.php?id='. $id . '">' . strip_tags(htmlspecialchars($prenom)) . '</a> ';
						$i++;
						if (count($utilisateurs) > $i && $i > 1)
							echo '- ';
					}
					?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>Êtes-vous sûr de vouloir supprimer le parrainage ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="parrainage.php?id=<?php echo strip_tags(htmlspecialchars($utilisateurs['idParrainage'])); ?>&action=supprimer">
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