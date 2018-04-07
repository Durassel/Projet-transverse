<?php
	$title = "Profil";
	$css = "<!-- Profil CSS --><link rel=\"stylesheet\" href=\"css/profil.css\"";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (!isset($_GET['id'])) {
		?><script type="text/javascript">javascript:history.back();</script><?php
	    header('Location: 404.php');
	    die();
	}

	$request = $db->prepare('SELECT utilisateur.id, utilisateur.nom AS nomUtilisateur, utilisateur.prenom, utilisateur.email, utilisateur.genre, utilisateur.idParent, statut.nom AS nomStatut FROM utilisateur, statut WHERE utilisateur.id = :id AND utilisateur.idStatut = statut.id');
	$request->execute(array(
		'id' => $_GET['id']
	));
	$data = $request->fetch();
	if (!$data) {
		?><script type="text/javascript">javascript:history.back();</script><?php
	    header('Location: 404.php');
	    die();
	}

	include_once "includes/header.php";
?>

<!-- Header Page -->
<div class="container-fluid background-texture">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="header-page">Profil</h2>
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
			if (isset($_GET['id']))
  				$fil['profil.php?id=' . $_GET['id']] = 'Profil : ' . $data['prenom'] . ' ' . $data['nomUtilisateur'];

		  	fil_ariane($fil);
			?>
		</div>
	</div>
</div>
<!-- End Breadcrumb -->

<!-- Content -->
<div class="container">
	<?php echo flash(); ?>
	<div class="row page-header">
		<h3 class="pull-left">Profil : <?php echo strip_tags(htmlspecialchars($data['prenom'])) . ' ' . strip_tags(htmlspecialchars($data['nomUtilisateur'])); ?></h3>
		<?php
		if (connecte()) {
			$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
			$request->execute(array(
				'idStatut' => $_SESSION['auth']['idStatut']
			));
			$acces = $request->fetch();

			$condition = false;
			if (!statut('administrateur') && $data['id'] != $_SESSION['auth']['id']) {
				$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :id AND idParent = :idParent');
				$request->execute(array(
					'id' 		=> $data['id'],
					'idParent'	=> $_SESSION['auth']['id']
				));
				$donnees = $request->fetch();

				if ($donnees) {
					$condition = true;
				}
			}

			if (statut('administrateur') || ($acces['modifierUtilisateur'] == 1 && $condition == true)) {
				echo '<a href="parametres.php?id=' . $data['id'] . '&action=supprimer" class="pull-right"><span class="glyphicon glyphicon-remove"></span></a>';
			}
			if (statut('administrateur') || ($acces['modifierUtilisateur'] == 1 && $condition == true) || $_GET['id'] == $_SESSION['auth']['id']) {
				echo '<a href="parametres.php?id=' . $data['id'] . '" class="pull-right"><span class="glyphicon glyphicon-pencil"></span></a>';
			}
		}
		?>
	</div>
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
			<div class="thumbnail pull-left col-xs-12 col-sm-4 col-md-12 col-lg-12">
				<?php
				if (pictureExist("avatars", $_GET['id'] . '.jpg'))
					echo '<img src="img/avatars/' . $_GET['id'] . '.jpg" alt="" class="img-responsive">';
				else if (pictureExist("avatars", $_GET['id'] . '.jpeg'))
					echo '<img src="img/avatars/' . $_GET['id'] . '.jpeg" alt="" class="img-responsive">';
				else if (pictureExist("avatars", $_GET['id'] . '.png'))
					echo '<img src="img/avatars/' . $_GET['id'] . '.png" alt="" class="img-responsive">';
				else if (pictureExist("avatars", $_GET['id'] . '.gif'))
					echo '<img src="img/avatars/' . $_GET['id'] . '.gif" alt="" class="img-responsive">';
				else
					echo '<img src="img/avatars/default.jpg" alt="" class="img-responsive">';
				?>
			</div>
			<div class="col-xs-12 col-sm-8 col-md-12 col-lg-12">
				<h3><?php echo strip_tags(htmlspecialchars($data['prenom'])) . ' ' . strip_tags(htmlspecialchars($data['nomUtilisateur'])); ?></h3>
				<p><b>Email</b> : <?php echo strip_tags(htmlspecialchars($data['email'])); ?></p>
				<p><b>Genre</b> : <?php echo strip_tags(htmlspecialchars($data['genre'])); ?></p>
			</div>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
			<h4><b>Statut</b> : <?php echo strip_tags(htmlspecialchars($data['nomStatut'])); ?></h4>
			<?php
				if ($data['nomStatut'] == 'élève') {
					// Parent
					$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :idParent');
					$request->execute(array(
						'idParent' => $data['idParent']
					));
					$donnees = $request->fetch();

					if ($donnees) {
						echo '<h4><b>Parent</b> : <a href="profil.php?id=' . $donnees['id'] . '">' . strip_tags(htmlspecialchars($donnees['prenom'])) . ' ' . strip_tags(htmlspecialchars($donnees['nom'])) . '</a>';
						if (connecte() && $acces['supprimerParent'] == 1) {
							echo ' <a href="parent.php?id=' . $data['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
						}
						echo '</h4>';
					} else {
						if (connecte() && $acces['ajouterParent'] == 1) {
							echo '<h4><b>Parent</b> : ';
							echo '<a href="parent.php?id=' . $data['id'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
						}
					}

					// Parrainage
					echo '<h4><b>Parrainage</b> : ';
					if (connecte()) {
						// Celui qui ajoute un parrainage doit appartenir à l'établissement de l'élève
						$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :id");
						$request->execute(array(
							'id' => $data['id']
						));
						$donnees = $request->fetch();

						$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :id");
						$request->execute(array(
							'id' => $_SESSION['auth']['id']
						));
						$result = $request->fetch();

						if (($donnees['idEtablissement'] == $result['idEtablissement'] && $acces['ajouterParrainage'] == 1) || statut('administrateur'))
							echo '<a href="parrainage.php?id=' . $data['id'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
					}

					// Filleul(s)
					$request = $db->prepare('SELECT * FROM parrainage WHERE idParrain = :idUtilisateur');
					$request->execute(array(
						'idUtilisateur' => $data['id']
					));
					$i = 0;
					while ($resultat = $request->fetch()) {
						$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :idFilleul');
						$request->execute(array(
							'idFilleul' => $resultat['idFilleul']
						));
						$res = $request->fetch();

						if ($i == 0) {
							echo '<ul><h5><b>Filleul</b> : ';
						}
						echo '<li><a href="profil.php?id=' . $res['id'] . '">' . strip_tags(htmlspecialchars($res['prenom'])) . ' ' . strip_tags(htmlspecialchars($res['nom'])) . '</a>';
						if ((connecte() && $donnees['idEtablissement'] == $result['idEtablissement'] && $acces['supprimerParrainage'] == 1) || statut('administrateur'))
							echo ' <a href="parrainage.php?id=' . $resultat['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
						echo '</li>';
						$i++;
					}
					if ($i > 0)
						echo '</h5></ul>';

					// Parrain(s)
					$request = $db->prepare('SELECT * FROM parrainage WHERE idFilleul = :idUtilisateur');
					$request->execute(array(
						'idUtilisateur' => $data['id']
					));
					$i = 0;
					while ($resultat = $request->fetch()) {
						$request = $db->prepare('SELECT * FROM utilisateur WHERE id = :idParrain');
						$request->execute(array(
							'idParrain' => $resultat['idParrain']
						));
						$res = $request->fetch();

						if ($i == 0) {
							echo '<ul><h5><b>Parrain</b> : ';
						}
						echo '<li><a href="profil.php?id=' . $res['id'] . '">' . strip_tags(htmlspecialchars($res['prenom'])) . ' ' . strip_tags(htmlspecialchars($res['nom'])) . '</a>';
						if ((connecte() && $donnees['idEtablissement'] == $result['idEtablissement'] && $acces['supprimerParrainage'] == 1) || statut('administrateur'))
							echo ' <a href="parrainage.php?id=' . $resultat['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
						echo '</li>';
						$i++;
					}
					if ($i > 0)
						echo '</h5></ul>';
					echo '</h4>';

					// Etablissement
					$request = $db->prepare("SELECT * FROM affiliation WHERE idUtilisateur = :id");
					$request->execute(array(
						'id' => $data['id']
					));
					$donnees = $request->fetch();
					if ($donnees) {
						echo '<h4><b>Etablissement</b> : </h4>';
					} else {
						echo '<h4><b>Etablissement</b> : ';
						if (connecte() && $acces['ajouterUtilisateurEtablissement'] == 1)
							echo '<a href="admission.php?id=' . $data['id'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
						echo '</h4>';
					}

					$request = $db->prepare("SELECT DISTINCT etablissement.id AS idEtablissement, etablissement.nom AS nomEtablissement FROM etablissement, affiliation WHERE affiliation.idUtilisateur = :id AND etablissement.id = affiliation.idEtablissement ORDER BY etablissement.id");
					$request->execute(array(
						'id' => $data['id']
					));
					$i = 0;
					echo '<ul>';
					while ($donnees = $request->fetch()) {
						if (connecte()) {
							$requete = $db->prepare('SELECT * FROM etablissement WHERE id = :idEtablissement AND idDirecteur = :idDirecteur');
							$requete->execute(array(
								'idEtablissement'	=> $donnees['idEtablissement'],
								'idDirecteur'		=> $_SESSION['auth']['id']
							));
							$result = $requete->fetch();

							echo '<li><h4><a href="etablissement.php?id=' . $donnees['idEtablissement'] . '">' . strip_tags(htmlspecialchars($donnees['nomEtablissement'])) . '</a>';
							if (($acces['supprimerUtilisateurEtablissement'] == 1 && $result && !statut('administrateur')) || ($acces['supprimerUtilisateurEtablissement'] == 1 && statut('administrateur')))
								echo ' <a href="admission.php?id=' . $data['id'] . '&etablissement=' . $donnees['idEtablissement'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
							echo '</h4></li>';
						}
						

						// Classe
						$requete = $db->prepare("SELECT classe.id AS idClasse, classe.nom AS nomClasse FROM classe, affiliation WHERE affiliation.idUtilisateur = :id AND affiliation.idEtablissement = :idEtablissement AND classe.id = affiliation.idClasse");
						$requete->execute(array(
							'id' => $data['id'],
							'idEtablissement' => $donnees['idEtablissement']
						));

						echo '<h5><b>Classe</b> : ';
						$i = 0;
						while ($resultat = $requete->fetch()) {
							if ($i == 0)
								echo '<ul>';
							echo '<li><a href="classe.php?id=' . $resultat['idClasse'] . '">' . strip_tags(htmlspecialchars($resultat['nomClasse'])) . '</a>';
							if ((connecte() && $acces['supprimerUtilisateurClasse'] == 1 && $result && !statut('administrateur')) || (connecte() && $acces['supprimerUtilisateurClasse'] == 1 && statut('administrateur')))
								echo ' <a href="admission.php?id=' . $data['id'] . '&classe=' . $resultat['idClasse'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
							echo '</li>';
							$i++;
						}
						if ($i == 0) {
							if ($acces['ajouterUtilisateurClasse'] == 1)
								echo ' <a href="admission.php?id=' . $data['id'] . '&etablissement=' . $donnees['idEtablissement'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
						} else
							echo '</ul>';
						echo '</h5>';
					}
					echo '</ul>';
				} else if ($data['nomStatut'] == 'parent') {
					// Enfant
					$request = $db->prepare('SELECT * FROM utilisateur WHERE idParent = :id');
					$request->execute(array(
						'id' => $data['id']
					));
					$i = 0;
					while ($donnees = $request->fetch()) {
						if ($i == 0) {
							echo '<h4><b>Enfant</b> : </h4><ul>';
						}
						echo '<li><a href="profil.php?id=' . $donnees['id'] . '">' . strip_tags(htmlspecialchars($donnees['prenom'])) . ' ' . strip_tags(htmlspecialchars($donnees['nom'])) . '</a></li>';
						$i++;
					}
					if ($i > 0) {
						echo '</ul>';
					}
				} else if ($data['nomStatut'] == 'professeur') {
					// Etablissement
					echo '<h4><b>Etablissement</b> : ';
					if (connecte() && $acces['ajouterUtilisateurEtablissement'] == 1)
						echo '<a href="admission.php?id=' . $data['id'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
					echo '</h4>';

					$request = $db->prepare("SELECT DISTINCT etablissement.id AS idEtablissement, etablissement.nom AS nomEtablissement FROM etablissement, affiliation WHERE affiliation.idUtilisateur = :id AND etablissement.id = affiliation.idEtablissement ORDER BY etablissement.id");
					$request->execute(array(
						'id' => $data['id']
					));
					$i = 0;
					echo '<ul>';
					while ($donnees = $request->fetch()) {
						if (connecte()) {
							$requete = $db->prepare('SELECT * FROM etablissement WHERE id = :idEtablissement AND idDirecteur = :idDirecteur');
							$requete->execute(array(
								'idEtablissement'	=> $donnees['idEtablissement'],
								'idDirecteur'		=> $_SESSION['auth']['id']
							));
							$result = $requete->fetch();

							echo '<li><h4><a href="etablissement.php?id=' . $donnees['idEtablissement'] . '">' . strip_tags(htmlspecialchars($donnees['nomEtablissement'])) . '</a>';
							if (($acces['supprimerUtilisateurEtablissement'] == 1 && $result && !statut('administrateur')) || ($acces['supprimerUtilisateurEtablissement'] == 1 && statut('administrateur')))
								echo ' <a href="admission.php?id=' . $data['id'] . '&etablissement=' . $donnees['idEtablissement'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
							echo '</h4></li>';
						}

						// Classe
						$requete = $db->prepare("SELECT classe.id AS idClasse, classe.nom AS nomClasse FROM classe, affiliation WHERE affiliation.idUtilisateur = :id AND affiliation.idEtablissement = :idEtablissement AND classe.id = affiliation.idClasse");
						$requete->execute(array(
							'id' => $data['id'],
							'idEtablissement' => $donnees['idEtablissement']
						));

						echo '<h5><b>Classe</b> : ';
						if (connecte() && $acces['ajouterUtilisateurClasse'] == 1)
							echo ' <a href="admission.php?id=' . $data['id'] . '&etablissement=' . $donnees['idEtablissement'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
						echo '<ul>';
						while ($resultat = $requete->fetch()) {
							echo '<li><a href="classe.php?id=' . $resultat['idClasse'] . '">' . strip_tags(htmlspecialchars($resultat['nomClasse'])) . '</a>';
							if ((connecte() && $acces['supprimerUtilisateurClasse'] == 1 && $result && !statut('administrateur')) || (connecte() && $acces['supprimerUtilisateurClasse'] == 1 && statut('administrateur')))
								echo ' <a href="admission.php?id=' . $data['id'] . '&classe=' . $resultat['idClasse'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
							echo '</li>';
						}
	
						echo '</ul></h5>';
					}
					echo '</ul>';
				} else if ($data['nomStatut'] == 'directeur') {
					// Etablissement
					echo '<h4><b>Etablissement</b> : ';
					if (connecte() && $acces['ajouterUtilisateurEtablissement'] == 1)
						echo '<a href="admission.php?id=' . $data['id'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
					echo '</h4>';

					$request = $db->prepare("SELECT DISTINCT etablissement.id AS idEtablissement, etablissement.nom AS nomEtablissement FROM etablissement, affiliation WHERE affiliation.idUtilisateur = :id AND etablissement.id = affiliation.idEtablissement ORDER BY etablissement.id");
					$request->execute(array(
						'id' => $data['id']
					));
					$i = 0;
					echo '<ul>';
					while ($donnees = $request->fetch()) {
						if (connecte()) {
							$requete = $db->prepare('SELECT * FROM etablissement WHERE id = :idEtablissement AND idDirecteur = :idDirecteur');
							$requete->execute(array(
								'idEtablissement'	=> $donnees['idEtablissement'],
								'idDirecteur'		=> $_SESSION['auth']['id']
							));
							$result = $requete->fetch();

							echo '<li><h4><a href="etablissement.php?id=' . $donnees['idEtablissement'] . '">' . strip_tags(htmlspecialchars($donnees['nomEtablissement'])) . '</a>';
							if (($acces['supprimerUtilisateurEtablissement'] == 1 && $result && !statut('administrateur')) || ($acces['supprimerUtilisateurEtablissement'] == 1 && statut('administrateur')))
								echo ' <a href="admission.php?id=' . $data['id'] . '&etablissement=' . $donnees['idEtablissement'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
							echo '</h4></li>';
						}

						// Classe
						$requete = $db->prepare("SELECT classe.id AS idClasse, classe.nom AS nomClasse FROM classe, affiliation WHERE affiliation.idUtilisateur = :id AND affiliation.idEtablissement = :idEtablissement AND classe.id = affiliation.idClasse");
						$requete->execute(array(
							'id' => $data['id'],
							'idEtablissement' => $donnees['idEtablissement']
						));

						echo '<h5><b>Classe</b> : ';
						if (connecte() && $acces['ajouterUtilisateurClasse'] == 1)
							echo ' <a href="admission.php?id=' . $data['id'] . '&etablissement=' . $donnees['idEtablissement'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
						echo '<ul>';
						while ($resultat = $requete->fetch()) {
							echo '<li><a href="classe.php?id=' . $resultat['idClasse'] . '">' . strip_tags(htmlspecialchars($resultat['nomClasse'])) . '</a>';
							if ((connecte() && $acces['supprimerUtilisateurClasse'] == 1 && $result && !statut('administrateur')) || (connecte() && $acces['supprimerUtilisateurClasse'] == 1 && statut('administrateur')))
								echo ' <a href="admission.php?id=' . $data['id'] . '&classe=' . $resultat['idClasse'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
							echo '</li>';
						}
	
						echo '</ul></h5>';
					}
					echo '</ul>';
				}
			?>
		</div>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>