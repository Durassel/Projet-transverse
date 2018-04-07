<?php
	$title = "Quiz";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (isset($_GET['id'])) {
		// Existence du quiz
		$request = $db->prepare("SELECT quiz.id AS idQuiz, quiz.nom AS nomQuiz, quiz.idTheme, utilisateur.id AS idProfesseur FROM utilisateur, quiz WHERE quiz.id = :id AND quiz.idProfesseur = utilisateur.id");
		$request->execute(array(
			'id'	=> $_GET['id']
		));
		$data = $request->fetch();
		if (!$data) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	}

	if (isset($_GET['id']) && isset($_GET['question'])) {
		// Vérifier que la question existe et corresponde au quiz
		$request = $db->prepare("SELECT theme.id AS idTheme, theme.nom AS nomTheme, quiz.id AS idQuiz, quiz.nom AS nomQuiz, quiz.idProfesseur, question.id AS idQuestion, question.question, question.idReponse, question.ordre FROM quiz, question, theme WHERE question.idQuiz = :idQuiz AND question.id = :id AND quiz.id = question.idQuiz AND quiz.idTheme = theme.id");
		$request->execute(array(
			'idQuiz'	=> $data['idQuiz'],
			'id'		=> $_GET['question']
		));
		$data = $request->fetch();
		if (!$data) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}

		// Si première question : création de la session du quiz
		// Vérifier que l'utilisateur n'a pas déjà répondu à des questions
		if (empty($_SESSION['quiz'][$_GET['id']])) {
			$_SESSION['quiz'] = array(
				$data['idQuiz'] => array()
			);
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
			if ($acces['ajouterQuiz'] == '0') {
				header('Location: quiz.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
			if ($acces['modifierQuiz'] == '0') {
				header('Location: quiz.php');
				die();
			}

			// Vérifier que le quiz appartient au prof
			if (!statut('administrateur')) {
				if ($data['idProfesseur'] != $_SESSION['auth']['id']) {
					$_SESSION['flash']['danger'] = "Le quiz ne vous appartient pas.";
					header('Location: quiz.php');
					die();
				}
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
			if ($acces['supprimerQuiz'] == '0') {
				header('Location: quiz.php');
				die();
			}

			// Vérifier que le quiz appartient au prof
			if (!statut('administrateur')) {
				if ($data['idProfesseur'] != $_SESSION['auth']['id']) {
					$_SESSION['flash']['danger'] = "Le quiz ne vous appartient pas.";
					header('Location: quiz.php');
					die();
				}
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (isset($_POST['submit'])) {
			// Nom
			if (strlen($_POST['nom']) > 255)
				$errors['tailleNom'] = "Le nom du quiz est trop long.";
			if (is_numeric($_POST['nom']))
				$errors['nomNombre'] = "Le nom du quiz n'est pas une chaine de caractères.";
			if (!preg_match('/^[a-zA-Z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
				$errors['nom'] = "Le nom du quiz n'est pas valide.";

			// Thème
			$request = $db->prepare("SELECT id FROM theme WHERE id = :id");
			$request->execute(array(
				'id' => $_POST['theme']
			));
			$donnees = $request->fetch();

			if (!$donnees) {
				$errors['theme'] = "Ce theme n'existe pas.";
			}

			// Professeur
			if (statut('administrateur')) {
				if (!isset($_POST['professeur'])) {
					$_POST['professeur'] = $_SESSION['auth']['id'];
				} else {
					$request = $db->prepare("SELECT utilisateur.id FROM utilisateur, statut WHERE utilisateur.id = :id AND utilisateur.idStatut = statut.id AND statut.nom = 'professeur'");
					$request->execute(array(
						'id' => $_POST['professeur']
					));
					$donnees = $request->fetch();

					if (!$donnees) {
						$errors['professeur'] = "Ce professeur n'existe pas.";
					}
				}
			}

			// Erreur
			if (empty($errors)) {
				if (statut('administrateur')) {
					$request = $db->prepare('INSERT INTO quiz(nom, idTheme, idProfesseur) VALUES(:nom, :idTheme, :idProfesseur)');
			      	$request->execute(array(
				        'nom'  			=> $_POST['nom'],
				        'idTheme'  		=> $_POST['theme'],
				        'idProfesseur'	=> $_POST['professeur']
			      	));
				} else {
					$request = $db->prepare('INSERT INTO quiz(nom, idTheme, idProfesseur) VALUES(:nom, :idTheme, :idProfesseur)');
			      	$request->execute(array(
				        'nom'  			=> $_POST['nom'],
				        'idTheme'  		=> $_POST['theme'],
				        'idProfesseur'	=> $_SESSION['auth']['id']
			      	));
				}

				$_SESSION['flash']['success'] = "Le quiz a été ajouté avec succès.";
				header('Location: quiz.php');
				die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_POST)) {
		$errors = array();
		$inputs = array();

		$inputs = $data;

		if (isset($_POST['submit'])) {
			// Nom
			if (!empty($_POST['nom']) && strcmp($_POST['nom'], $inputs['nomQuiz']) !== 0) {
				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Le nom du quiz est trop long.";
				if (is_numeric($_POST['nom']))
					$errors['nomNombre'] = "Le nom du quiz n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-Z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Le nom du quiz n'est pas valide.";

				if (!isset($errors['tailleNom']) && !isset($errors['nomNombre']) && !isset($errors['nom']) && !isset($errors['nomUtilise'])) {
					$inputs['nomQuiz'] = $_POST['nom'];
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
			if (!empty($_POST['professeur']) && strcmp($_POST['professeur'], $inputs['idProfesseur']) !== 0 && statut('administrateur')) {
				$request = $db->prepare("SELECT utilisateur.id FROM utilisateur, statut WHERE utilisateur.id = :id AND utilisateur.idStatut = statut.id AND statut.nom = 'professeur'");
				$request->execute(array(
					'id' => $_POST['professeur']
				));
				$donnees = $request->fetch();

				if (!$donnees) {
					$errors['professeur'] = "Ce professeur n'existe pas.";
				}

				if (!isset($errors['professeur'])) {
					$inputs['idProfesseur'] = $_POST['professeur'];
				}
			} else if (!isset($_POST['professeur'])) {
				$inputs['idProfesseur'] = $_SESSION['auth']['id'];
			}

			// Erreur
			if (empty($errors)) {
				if (statut('administrateur')) {
					$request = $db->prepare('UPDATE quiz SET nom = :nom, idTheme = :idTheme, idProfesseur = :idProfesseur WHERE id = :id');
			      	$request->execute(array(
				        'nom'  			=> $inputs['nomQuiz'],
				        'idTheme'  		=> $inputs['idTheme'],
				        'idProfesseur'	=> $inputs['idProfesseur'],
				        'id'			=> $data['idQuiz']
			      	));
				} else {
					$request = $db->prepare('UPDATE quiz SET idTheme = :idTheme, nom = :nom WHERE id = :id');
			      	$request->execute(array(
				        'idTheme'	=> $inputs['idTheme'],
				        'nom'  		=> $inputs['nomQuiz'],
				        'id'		=> $_GET['id']
			      	));
				}

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "Le quiz a été modifié avec succès.";
				//header('Location: quiz.php');
				//die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression des réponses
			$request = $db->prepare("SELECT id FROM question WHERE idQuiz = :idQuiz");
			$request->execute(array(
				'idQuiz'	=> $data['id']
			));
			while ($donnees = $request->fetch()) {
				$request = $db->prepare("DELETE FROM reponse WHERE idQuestion = :idQuestion");
				$request->execute(array(
					'idQuestion' => $donnees['id']
				));
			}

			// Suppression des questions
			$request = $db->prepare("DELETE FROM question WHERE idQuiz = :idQuiz");
			$request->execute(array(
				'idQuiz' => $data['id']
			));

			// Suppression du quiz
			$request = $db->prepare("DELETE FROM quiz WHERE id = :id");
			$request->execute(array(
				'id' => $data['id']
			));

			header('Location: quiz.php');
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: quiz.php');
			die();
		}
	} else if (isset($_GET['id']) && isset($_GET['question']) && !empty($_POST)) {
		$errors = array();

		if (empty($_POST['reponse'])) {
			$errors['reponse'] = "Veuillez sélectionner une réponse";
		} else {
			// Vérifier que la réponse appartienne à la question
			$request = $db->prepare("SELECT * FROM reponse WHERE id = :id AND idQuestion = :idQuestion");
			$request->execute(array(
				'id'			=> $_POST['reponse'],
				'idQuestion'	=> $data['idQuestion']
			));
			$donnees = $request->fetch();
			if (!$donnees) {
				$errors['reponse'] = "Cette réponse ne correspond pas à la question.";
			} else {
				// Enregistrement de la réponse
				$_SESSION['quiz'][$data['idQuiz']][$data['idQuestion']] = $_POST['reponse'];
			}
		}
	}

	// Si affichage d'un quiz
	if (isset($_GET['id']) && isset($_GET['question'])) {
		// Test si l'utilisateur a déjà répondu à une/des question(s) du quiz
		if (isset($_SESSION['quiz'])) {
			if (isset($_SESSION['quiz'][$_GET['id']])) {
				// Redirigez vers la première question non répondu
				$request = $db->prepare("SELECT COUNT(id) AS nbQuestion FROM question WHERE idQuiz = :idQuiz AND idReponse <> '0'");
				$request->execute(array(
					'idQuiz'	=> $data['idQuiz']
				));
				$donnees = $request->fetch();

				if (count($_SESSION['quiz'][$data['idQuiz']]) == $donnees['nbQuestion']) {
					echo 'Répondu à toutes les questions';
					header('Location: resultat.php?id=' . $data['idQuiz']);
					die();
				}


				$request = $db->prepare("SELECT id FROM question WHERE idQuiz = :idQuiz AND idReponse <> '0' ORDER BY ordre");
				$request->execute(array(
					'idQuiz'	=> $data['idQuiz']
				));
				while ($donnees = $request->fetch()) {
					if ($_GET['id'] != $data['idQuiz'] || $_GET['question'] != $donnees['id']) {
						if (!isset($_SESSION['quiz'][$data['idQuiz']][$donnees['id']])) {
							header('Location: quiz.php?id=' . $data['idQuiz'] .'&question=' . $donnees['id']);
							die();
						}
					} else if ($_GET['id'] == $data['idQuiz'] && $_GET['question'] == $donnees['id']) {
						// S'il a déjà répondu, on passe à la question suivante
						if (!isset($_SESSION['quiz'][$data['idQuiz']][$donnees['id']])) {
							break;
						}
					}
				}
			}
		}
	}

	include_once "includes/header.php";
?>

<!-- Header Page -->
<div class="container-fluid background-texture">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<h2 class="header-page">Quiz</h2>
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
			$fil = array('quiz.php' => 'Quiz');
			if (isset($_GET['id'])) {
				if (isset($_GET['action'])) {
					if ($_GET['action'] == 'modifier') {
						$fil['quiz.php?id=' . $_GET['id'] . '&action=modifier'] = 'Modification du quiz : ' . $data['nomQuiz'];
					} else if ($_GET['action'] == 'supprimer') {
						$fil['quiz.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression du quiz : ' . $data['nomQuiz'];
					}
				} else if (isset($_GET['question'])) {
					$fil['quiz.php?id=' . $data['idQuiz']] = $data['nomQuiz'];
					$fil['quiz.php?id=' . $data['idQuiz'] . '&question=' . $data['idQuestion']] = $data['question'];
				} else {
					$fil['quiz.php?id=' . $_GET['id']] = 'Quiz - ' . $data['nomQuiz'];
				}
			} else {
				if (isset($_GET['action'])) {
					if ($_GET['action'] == 'ajouter') {
						$fil['quiz.php?&action=ajouter'] = 'Ajout d\'un quiz';
					}
				}
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
					<h3>Ajout d'un quiz</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="quiz.php?action=ajouter">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du quiz</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du quiz" class="form-control input-md" required="">
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
						<!-- Thème -->
						<div class="form-group <?php if (isset($errors['theme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="theme">Thème :</label>
							<div class="col-md-5">
								<select id="theme" name="theme" class="form-control">
								<?php
									$request = $db->query('SELECT * FROM theme');
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '">' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
									if (isset($errors['theme']))
									echo strip_tags(htmlspecialchars($errors['theme']));
									?>
								</span>
							</div>
						</div>
						<!-- Professeur -->
						<?php if (statut('administrateur')) {
							$request = $db->query("SELECT utilisateur.id, utilisateur.prenom, utilisateur.nom FROM utilisateur, statut WHERE utilisateur.idStatut = statut.id AND statut.nom = 'professeur'");
							if ($request->rowCount() > 0) {
						?>
								<div class="form-group <?php if (isset($errors['professeur'])) echo "has-error"; ?>">
									<label class="col-md-4 control-label" for="professeur">Professeur :</label>
									<div class="col-md-5">
										<select id="professeur" name="professeur" class="form-control">
										<?php
											while ($donnees = $request->fetch()) {
												echo '<option value="' . $donnees['id'] . '">' . strip_tags(htmlspecialchars($donnees['prenom'])) . ' ' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
											}
										?>
										</select>
										<span class="help-block">
											<?php
											if (isset($errors['professeur']))
											echo strip_tags(htmlspecialchars($errors['professeur']));
											?>
										</span>
									</div>
								</div>
						<?php }
						}
						?>
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
					<h3>Modification d'un quiz</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="quiz.php?id=<?php echo $data['idQuiz']; ?>&action=modifier">
					<fieldset>
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du quiz</label>
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du quiz" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['nomQuiz'])); ?>">
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
						<!-- Thème -->
						<div class="form-group <?php if (isset($errors['theme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="theme">Thème :</label>
							<div class="col-md-5">
								<select id="theme" name="theme" class="form-control">
								<?php
									$request = $db->query('SELECT * FROM theme');
									while ($donnees = $request->fetch()) {
										echo '<option value="' . $donnees['id'] . '"';
										if ($donnees['id'] == $data['idTheme'])
											echo 'selected="selected"';
										echo '>' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
									}
								?>
								</select>
								<span class="help-block">
									<?php
									if (isset($errors['theme']))
									echo strip_tags(htmlspecialchars($errors['theme']));
									?>
								</span>
							</div>
						</div>
						<!-- Professeur -->
						<?php if (statut('administrateur')) {
							$request = $db->query("SELECT utilisateur.id, utilisateur.prenom, utilisateur.nom FROM utilisateur, statut WHERE utilisateur.idStatut = statut.id AND statut.nom = 'professeur'");
							if ($request->rowCount() > 0) {
						?>
								<div class="form-group <?php if (isset($errors['professeur'])) echo "has-error"; ?>">
									<label class="col-md-4 control-label" for="professeur">Professeur :</label>
									<div class="col-md-5">
										<select id="professeur" name="professeur" class="form-control">
										<?php
											while ($donnees = $request->fetch()) {
												echo '<option value="' . $donnees['id'] . '"';
												if ($donnees['id'] == $data['idProfesseur'])
													echo 'selected="selected"';
												echo '>' . strip_tags(htmlspecialchars($donnees['prenom'])) . ' ' . strip_tags(htmlspecialchars($donnees['nom'])) . '</option>';
											}
										?>
										</select>
										<span class="help-block">
											<?php
											if (isset($errors['professeur']))
											echo strip_tags(htmlspecialchars($errors['professeur']));
											?>
										</span>
									</div>
								</div>
						<?php }
						}
						?>
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
					<h3>Suppression du quiz : <?php echo strip_tags(htmlspecialchars($data['nomQuiz'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'un quiz entraine la suppression de l'ensemble des questions du quiz !</p>
				<p>Êtes-vous sûr de vouloir supprimer ce quiz ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="quiz.php?id=<?php echo $data['idQuiz']; ?>&action=supprimer">
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
		} else if(isset($_GET['id'])) {
			if (isset($_GET['question'])) {
				// Affichage de la question
				$request = $db->prepare("SELECT COUNT(id) AS nbQuestion FROM question WHERE idQuiz = :id AND idReponse <> '0'");
				$request->execute(array(
					'id'	=> $data['idQuiz']
				));
				$donnees = $request->fetch();
				?>
				<div class="row page-header">
					<h3>Question <?php echo $data['ordre'] . ' / ' . $donnees['nbQuestion'];
					if ($data['idProfesseur'] == $_SESSION['auth']['id'] || statut('administrateur'))
						echo ' <a href="question.php?id=' . $data['idQuiz'] . '" class="btn btn-primary pull-right">Liste des questions</a> ';
					?></h3>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
							<p>Thème : <?php echo '<a href="theme.php?id=' . $data['idTheme'] . '">' . strip_tags(htmlspecialchars($data['nomTheme'])) . '</a>'; ?></p>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<form enctype="multipart/form-data" class="form-horizontal" method="post" action="quiz.php?id=<?php echo $data['idQuiz']; ?>&question=<?php echo $data['idQuestion']; ?>">
								<fieldset>
									<div class="form-group <?php if (isset($errors['reponse'])) echo "has-error"; ?>">
										<label class="control-label" for="reponse"><h4>Question : <?php echo strip_tags(htmlspecialchars($data['question'])); ?></h4></label>  
										<?php
										$request = $db->prepare("SELECT * FROM reponse WHERE idQuestion = :idQuestion");
										$request->execute(array(
											'idQuestion'	=> $data['idQuestion']
										));
										while($result = $request->fetch()) {
											echo '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
													<p><input type="radio" id="reponse" name="reponse" value="' . $result['id'] . '"> ' . strip_tags(htmlspecialchars($result['reponse'])) . '</p>
												</div>';
										}
										?>
									  	<span class="help-block">
											<?php
								            if (isset($errors['reponse']))
												echo strip_tags(htmlspecialchars($errors['reponse']));
								            ?>
							            </span>
							         </div>
								</fieldset>
								<div class="col-xs-12 col-sm-offset-3 col-sm-6 col-md-offset-4 col-md-4 col-lg-offset-4 col-lg-4">
									<button name="submit" class="btn btn-primary btn-block">Valider</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php
			} else {
				// Vérifier s'il existe des questions au quiz
				$requete = $db->prepare("SELECT * FROM question WHERE idQuiz = :idQuiz AND idReponse <> '0' ORDER BY ordre");
				$requete->execute(array(
					'idQuiz'	=> $data['idQuiz']
				));
				$donnees = $requete->fetch();
				if ($donnees) {
				?>
					<div class="row">
						<div class="col-lg-12 page-header">
							<h3>Quiz : <?php echo strip_tags(htmlspecialchars($data['nomQuiz'])); 
							if ((connecte() && $acces['ajouterQuestion'] && $data['idProfesseur'] == $_SESSION['auth']['id']) || statut('administrateur'))
								echo ' <a href="question.php?id=' . $data['idQuiz'] . '&action=ajouter"><span class="glyphicon glyphicon-plus pull-right"></span></a> ';
							if ($data['idProfesseur'] == $_SESSION['auth']['id'] || statut('administrateur'))
								echo ' <a href="question.php?id=' . $data['idQuiz'] . '"><span class="glyphicon glyphicon-list pull-right"></span></a> ';
							?></h3>
						</div>
					</div>
					<div class="row">
						<p>Une fois la réponse validée, vous ne pourrez pas changer de réponse.</p>
						<form enctype="multipart/form-data" class="form-horizontal" method="post" action="quiz.php?id=<?php echo $data['idQuiz']; ?>&question=<?php echo $donnees['id']; ?>">
							<fieldset>
								<div class="col-xs-12 col-sm-offset-3 col-sm-6 col-md-offset-4 col-md-4 col-lg-offset-4 col-lg-4">
									<button class="btn btn-primary btn-block">Commencer le quiz</button>
								</div>
							</fieldset>
						</form>
					</div>
				<?php
				} else { // Pas de question dans le quiz
				?>
					<div class="row">
						<div class="col-lg-12 page-header">
							<h3>Quiz : <?php echo strip_tags(htmlspecialchars($data['nomQuiz']));
							if ((connecte() && $acces['ajouterQuestion'] && $data['idProfesseur'] == $_SESSION['auth']['id']) || statut('administrateur'))
								echo ' <a href="question.php?id=' . $data['idQuiz'] . '&action=ajouter"><span class="glyphicon glyphicon-plus pull-right"></span></a> ';
							if ($data['idProfesseur'] == $_SESSION['auth']['id'] || statut('administrateur'))
								echo ' <a href="question.php?id=' . $data['idQuiz'] . '"><span class="glyphicon glyphicon-list pull-right"></span></a> ';
							?></h3>
						</div>
					</div>
					<div class="row">
						<p>Ce quiz ne contient aucune question avec une bonne réponse.</p>
					</div>
			<?php }
			}
		} else { ?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Quiz : <?php if (connecte() && $acces['ajouterQuiz'] == 1) echo '<a href="quiz.php?action=ajouter" class="btn btn-primary pull-right">Ajouter un quiz</a>'; ?></h3>
				</div>
			</div>
			<div class="row">
				<table class="table text-center">
					<thead>
						<tr>
							<th class="col-lg-2 text-center">Thème</th>
							<th class="col-lg-8 text-center">Quiz</th>
							<th class="col-lg-2 text-center"><?php if (connecte() && ($acces['modifierQuiz'] == 1 || $acces['supprimerQuiz'] == 1)) echo 'Actions'; ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$request = $db->query("SELECT COUNT(quiz.id) AS nbQuiz FROM quiz");
						$result = $request->fetch();

						$nbQuiz = $result['nbQuiz'];
						$perPage = 20;
						$nbPage = ceil($nbQuiz/$perPage);

						if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbPage) {
							$cPage = $_GET['page'];
						} else {
							$cPage = 1;
						}

						$request = $db->query("SELECT quiz.id AS idQuiz, quiz.nom AS nomQuiz, theme.id AS idTheme, theme.nom AS nomTheme FROM quiz, theme WHERE quiz.idTheme = theme.id ORDER BY quiz.id DESC LIMIT " . (($cPage - 1) * $perPage) . ", $perPage");
						while ($donnees = $request->fetch()) {
							echo '<tr>
							<td><a href="theme.php?id=' . $donnees['idTheme'] . '">' . strip_tags(htmlspecialchars($donnees['nomTheme'])) . '</a></td>';
							echo '<td><a href="quiz.php?id=' . $donnees['idQuiz'] . '">' . strip_tags(htmlspecialchars($donnees['nomQuiz'])) . '</a></td><td>';

							if (connecte()) {
								$requete = $db->prepare('SELECT * FROM quiz WHERE idProfesseur = :idProfesseur AND id = :id');
								$requete->execute(array(
									'idProfesseur'	=> $_SESSION['auth']['id'],
									'id'			=> $donnees['idQuiz']
								));
								$result = $requete->fetch();

								if ($result || statut('administrateur')) {
									if ($acces['modifierQuiz'] == 1)
										echo '<a href="quiz.php?id=' . $donnees['idQuiz'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a> ';
									if ($acces['supprimerQuiz'] == 1)
										echo ' <a href="quiz.php?id=' . $donnees['idQuiz'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
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
                                echo '<li class="active"><a href="quiz.php?page=' . $i . '">' . $i . '</a></li>';
                            else
                                echo '<li><a href="quiz.php?page=' . $i . '">' . $i . '</a></li>';
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