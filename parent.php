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

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'supprimer')) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: profil.php?id=' . $_GET['id']);
		die();
	}

	if (!isset($_GET['action'])) {
		?><script type="text/javascript">javascript:history.back();</script><?php
	    header('Location: 404.php');
	    die();
	}

	if (isset($_GET['action']) && !connecte()) {
		header('Location: 404.php');
		die();
	}

	if ($data['nomStatut'] != 'élève') {
		header('Location: profil.php?id' . $_GET['id']);
		die();
	}

	if (!statut('parent') && !statut('administrateur')) {
		header('Location: profil.php?id' . $_GET['id']);
		die();
	}

	$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
	$request->execute(array(
		'idStatut' => $_SESSION['auth']['idStatut']
	));
	$acces = $request->fetch();

	if ($_GET['action'] == 'ajouter') {
		if ($acces['ajouterParent'] == '0') {
			header('Location: profil.php?id' . $_GET['id']);
			die();
		}

		// Vérifier que l'élève n'a pas déjà un parent
		$request = $db->prepare("SELECT * FROM utilisateur WHERE id = :id");
		$request->execute(array(
			'id'		=> $_GET['id']
		));
		$donnees = $request->fetch();
		if ($donnees['idParent'] != '0') {
			$_SESSION['flash']['danger'] = "L'élève possède déjà un parent.";
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}
	} else if ($_GET['action'] == 'supprimer') {
		if ($acces['supprimerParent'] == '0') {
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}

		// Vérifier que c'est son enfant
		$request = $db->prepare("SELECT * FROM utilisateur WHERE id = :id AND idParent = :idParent");
		$request->execute(array(
			'id'		=> $_GET['id'],
			'idParent'	=> $_SESSION['auth']['id']
		));
		$donnees = $request->fetch();
		if (!$donnees && !statut('administrateur')) {
			$_SESSION['flash']['danger'] = "L'élève ne vous appartient pas.";
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}
	}

	if ($_GET['action'] == 'ajouter') {
		if (statut('administrateur')) {
			$errors = array();

			if (!empty($_POST['parent'])) {
				// Check si c'est un parent
				$request = $db->prepare("SELECT * FROM utilisateur, statut WHERE utilisateur.id = :idParent AND utilisateur.idStatut = statut.id AND statut.nom = 'parent'");
				$request->execute(array(
					'idParent'	=> $_POST['parent']
				));
				$donnees = $request->fetch();
				if (!$donnees) {
					$errors['parent'] = "L'utilisateur n'est pas un parent.";
				}

				if (empty($errors)) {
					$request = $db->prepare('UPDATE utilisateur SET idParent = :idParent WHERE id = :id');
			      	$request->execute(array(
				        'idParent'	=> $_POST['parent'],
				        'id'		=> $data['id']
			      	));

					$_SESSION['flash']['success'] = "Le parent a été supprimé avec succès.";
					header('Location: profil.php?id=' . $_GET['id']);
					die();
				}
			}
		} else {
			$request = $db->prepare('UPDATE utilisateur SET idParent = :idParent WHERE id = :id');
	      	$request->execute(array(
		        'idParent'	=> $_SESSION['auth']['id'],
		        'id'		=> $data['id']
	      	));

			$_SESSION['flash']['success'] = "Le parent a été ajouté avec succès.";
			header('Location: profil.php?id=' . $_GET['id']);
			die();
		}
	} else if ($_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			$request = $db->prepare('UPDATE utilisateur SET idParent = :idParent WHERE id = :id');
	      	$request->execute(array(
		        'idParent'	=> '0',
		        'id'		=> $data['id']
	      	));

			$_SESSION['flash']['success'] = "Le parent a été supprimé avec succès.";
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
					<h3>Parent de : <a href="profil.php?id=<?php echo $data['id'];?>"><?php echo strip_tags(htmlspecialchars($data['prenom'])) . ' ' . strip_tags(htmlspecialchars($data['nomUtilisateur'])); ?></a></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" action="parent.php?id=<?php echo $data['id']; ?>&action=ajouter" method="post">
					<fieldset>
						<!-- Sélection du parent -->
						<div class="form-group <?php if (isset($errors['parent'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="parent">Parent :</label>
							<div class="col-md-5">
								<select id="parent" name="parent" class="form-control">
								<?php
									// Sélectionnez les parents
									$request = $db->query("SELECT utilisateur.id, utilisateur.nom, utilisateur.prenom FROM utilisateur, statut WHERE utilisateur.idStatut = statut.id AND statut.nom = 'parent'");
									while ($donnees = $request->fetch()) {
											echo '<option value="' . $donnees['id'] . '">' . strip_tags(htmlspecialchars($donnees['prenom'])) . ' ' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['parent']))
											echo strip_tags(htmlspecialchars($errors['parent']));
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
					<h3>Parent de : <a href="profil.php?id=<?php echo $data['id'];?>"><?php echo strip_tags(htmlspecialchars($data['prenom'])) . ' ' . strip_tags(htmlspecialchars($data['nomUtilisateur'])); ?></a></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>Êtes-vous sûr de vouloir supprimer le parent ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="parent.php?id=<?php echo $_GET['id']; ?>&action=supprimer">
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