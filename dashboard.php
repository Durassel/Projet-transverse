<?php
	$title = "Tableau de bord";
	$css = "<!-- Gallerie CSS --><link rel=\"stylesheet\" href=\"css/gallerie.css\"><!-- Icons CSS --><link rel=\"stylesheet\" href=\"css/icons.css\">";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (!connecte() || statut('élève')) {
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
				<h2 class="header-page">Tableau de bord</h2>
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
			$fil = array('dashboard.php' => 'Tableau de bord');

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
		if (statut("administrateur")) {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Que voulez-vous faire ?</h3>
				</div>
			</div>
			<div class="row text-center">
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/utilisateur.jpg" alt="Utilisateur" class="img-responsive">
						<p>Gestion des utilisateurs</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
								<h3>Gestion des utilisateurs</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="profil.php?id=<?php echo $_SESSION['auth']['id']; ?>">Modifier un utilisateur</a></li>
									<li><a href="profil.php?id=<?php echo $_SESSION['auth']['id']; ?>">Supprimer un utilisateur</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/college.jpg" alt="Etablissement" class="img-responsive">
						<p>Gestion des établissements</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
								<h3>Gestion des établissements :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="etablissement.php?action=ajouter">Ajouter un établissement</a></li>
									<li><a href="etablissement.php">Modifier un établissement</a></li>
									<li><a href="etablissement.php">Supprimer un établissement</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
								<h3>Gestion des classes :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="classe.php">Ajouter une classe</a></li>
									<li><a href="classe.php">Modifier une classe</a></li>
									<li><a href="classe.php">Supprimer une classe</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/cours.jpg" alt="Cours" class="img-responsive">
						<p>Gestion des cours</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des thèmes :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="theme.php?action=ajouter">Ajouter un thème</a></li>
									<li><a href="theme.php">Modifier un thème</a></li>
									<li><a href="theme.php">Supprimer un thème</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des cours :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="theme.php">Ajouter un cours</a></li>
									<li><a href="theme.php">Modifier un cours</a></li>
									<li><a href="theme.php">Supprimer un cours</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des chapitres :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="theme.php">Ajouter un chapitre</a></li>
									<li><a href="theme.php">Modifier un chapitre</a></li>
									<li><a href="theme.php">Supprimer un chapitre</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
			</div>
			<div class="row row-detail"></div>
			<div class="row text-center">
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/quiz.jpg" alt="Quiz" class="img-responsive">
						<p>Gestion des quiz</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des quiz :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="quiz.php?action=ajouter">Ajouter un quiz</a></li>
									<li><a href="quiz.php">Modifier un quiz</a></li>
									<li><a href="quiz.php">Supprimer un quiz</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des questions :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="quiz.php">Ajouter une question</a></li>
									<li><a href="quiz.php">Modifier une question</a></li>
									<li><a href="quiz.php">Supprimer une question</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des réponses :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="quiz.php">Ajouter une réponse</a></li>
									<li><a href="quiz.php">Modifier une réponse</a></li>
									<li><a href="quiz.php">Supprimer une réponse</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/statut.jpg" alt="Statut" class="img-responsive">
						<p>Gestion des statuts</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
								<h3>Gestion des statuts :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="statut.php?action=ajouter">Ajouter un statut</a></li>
									<li><a href="statut.php">Modifier un statut</a></li>
									<li><a href="statut.php">Supprimer un statut</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/niveaux.jpg" alt="Niveaux" class="img-responsive">
						<p>Gestion des niveaux</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
								<h3>Gestion des niveaux :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="niveau.php?action=ajouter">Ajouter un niveau</a></li>
									<li><a href="niveau.php">Modifier un niveau</a></li>
									<li><a href="niveau.php">Supprimer un niveau</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
			</div>
			<div class="row row-detail"></div>
		<?php
		} else if (statut("directeur")) {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Que voulez-vous faire ?</h3>
				</div>
			</div>
			<div class="row text-center">
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/utilisateur.jpg" alt="Utilisateur" class="img-responsive">
						<p>Gestion des utilisateurs</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des professeurs / élèves</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="classe.php">Ajouter un professeur / élève à une classe</a></li>
									<li><a href="classe.php">Supprimer un professeur / élève d'une classe</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/college.jpg" alt="Etablissement" class="img-responsive">
						<p>Gestion des établissements</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
								<h3>Gestion de l'établissement :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="etablissement.php">Modifier l'établissement</a></li>
									<li><a href="etablissement.php">Supprimer l'établissement</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
								<h3>Gestion des classes :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="classe.php">Ajouter une classe</a></li>
									<li><a href="classe.php">Modifier une classe</a></li>
									<li><a href="classe.php">Supprimer une classe</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
			</div>
			<div class="row row-detail"></div>
		<?php
		} else if (statut("professeur")) {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Que voulez-vous faire ?</h3>
				</div>
			</div>
			<div class="row text-center">
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/utilisateur.jpg" alt="Utilisateur" class="img-responsive">
						<p>Gestion des utilisateurs</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
								<h3>Gestion des élèves</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="classe.php">Ajouter un élève à une classe</a></li>
									<li><a href="classe.php">Supprimer un élève d'une classe</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/cours.jpg" alt="Cours" class="img-responsive">
						<p>Gestion des cours</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
								<h3>Gestion des cours :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="theme.php">Ajouter un cours</a></li>
									<li><a href="theme.php">Modifier un cours</a></li>
									<li><a href="theme.php">Supprimer un cours</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 text-center">
								<h3>Gestion des chapitres :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="theme.php">Ajouter un chapitre</a></li>
									<li><a href="theme.php">Modifier un chapitre</a></li>
									<li><a href="theme.php">Supprimer un chapitre</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/quiz.jpg" alt="Quiz" class="img-responsive">
						<p>Gestion des quiz</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des quiz :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="quiz.php?action=ajouter">Ajouter un quiz</a></li>
									<li><a href="quiz.php">Modifier un quiz</a></li>
									<li><a href="quiz.php">Supprimer un quiz</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des questions :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="quiz.php">Ajouter une question</a></li>
									<li><a href="quiz.php">Modifier une question</a></li>
									<li><a href="quiz.php">Supprimer une question</a></li>
								</ul>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 text-center">
								<h3>Gestion des réponses :</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="quiz.php">Ajouter une réponse</a></li>
									<li><a href="quiz.php">Modifier une réponse</a></li>
									<li><a href="quiz.php">Supprimer une réponse</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
			</div>
			<div class="row row-detail"></div>
		<?php
		} else if (statut("parent")) {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Que voulez-vous faire ?</h3>
				</div>
			</div>
			<div class="row text-center">
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 work miniature">
					<div class="thumbnail">
						<img src="img/icones/utilisateur.jpg" alt="Utilisateur" class="img-responsive">
						<p>Gestion des utilisateurs</p>
					</div>
					<div class="work-detail">
						<hr>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
								<h3>Gestion des enfants</h3>
								<p>Que voulez-vous faire ?</p>
								<ul class ="list-style-none">
									<li><a href="profil.php?id=<?php echo $_SESSION['auth']['id']; ?>">Modifier un enfant</a></li>
									<li><a href="profil.php?id=<?php echo $_SESSION['auth']['id']; ?>">Supprimer un enfant</a></li>
								</ul>
							</div>
						</div>
						<hr>
					</div>
				</div>
			</div>
			<div class="row row-detail"></div>
		<?php
		}
		?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>