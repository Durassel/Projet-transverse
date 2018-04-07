<?php
	$title = "Quiz";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (!connecte()) {
		header('Location: 404.php');
		die();
	}

	if (isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'modifier' && $_GET['action'] != 'supprimer') {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: 404.php');
		die();
	}

	if (isset($_GET['id'])) {
		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
			$request = $db->prepare("SELECT * FROM quiz WHERE id = :id");
			$request->execute(array(
				'id'	=> $_GET['id']
			));
			$data = $request->fetch();
			if (!$data) {
				?><script type="text/javascript">javascript:history.back();</script><?php
			    header('Location: 404.php');
			    die();
			}
		} else if (isset($_GET['action']) && ($_GET['action'] == 'modifier' || $_GET['action'] == 'supprimer')) {
			$request = $db->prepare("SELECT quiz.id AS idQuiz, quiz.nom AS nomQuiz, quiz.idProfesseur, question.id AS idQuestion, question.question, question.idReponse, question.ordre FROM quiz, question WHERE question.id = :id AND question.idQuiz = quiz.id");
			$request->execute(array(
				'id'	=> $_GET['id']
			));
			$data = $request->fetch();
			if (!$data) {
				?><script type="text/javascript">javascript:history.back();</script><?php
			    header('Location: 404.php');
			    die();
			}
		} else {
			$request = $db->prepare("SELECT * FROM quiz WHERE id = :id");
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

		if ($data['idProfesseur'] != $_SESSION['auth']['id'] && !statut('administrateur')) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	} else {
		?><script type="text/javascript">javascript:history.back();</script><?php
	    header('Location: 404.php');
	    die();
	}

	$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
	$request->execute(array(
		'idStatut' => $_SESSION['auth']['idStatut']
	));
	$acces = $request->fetch();

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
		if ($acces['ajouterQuestion'] == '0') {
			header('Location: 404.php');
			die();
		}
	} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
		if ($acces['modifierQuestion'] == '0') {
			header('Location: 404.php');
			die();
		}
	} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
		if ($acces['supprimerQuestion'] == '0') {
			header('Location: 404.php');
			die();
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		// Question : rien à vérifier
		// Ordre
		if (!isset($_POST['position'])) {
			$nouvellePosition = 1;
		}
		if (!empty($_POST['position']) && ($_POST['position'] == 'avant' || $_POST['position'] == 'apres')) {
			$nouvellePosition = $_POST['ordre'];
			if ($_POST['position'] == 'apres') {
				$nouvellePosition = $nouvellePosition + 1;
			}

			$request = $db->prepare("SELECT MAX(ordre) AS ordreMax FROM question WHERE idQuiz = :id");
			$request->execute(array(
				'id' => $data['id']
			));
			$result = $request->fetch();

			for ($i = $result['ordreMax']; $i >= $nouvellePosition; $i--) {
				$request = $db->prepare('UPDATE question SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idQuiz = :id');
				$request->execute(array(
			        'nouvelleOrdre'		=> $i + 1,
			        'ordre'				=> $i,
			        'id'  				=> $data['id']
		      	));
			}
		}
		// Bonne réponse => il faut d'abord ajouter des réponses pour sélectionner la bonne réponse => modification de la question
		if (empty($errors)) {
			$request = $db->prepare('INSERT INTO question(idQuiz, question, idReponse, ordre) VALUES(:id, :question, :idReponse, :ordre)');
	      	$request->execute(array(
	      		'id' 		=> $data['id'],
	      		'question'	=> $_POST['question'],
	      		'idReponse'	=> '0',
	      		'ordre' 	=> $nouvellePosition
	      	));

			$_SESSION['flash']['success'] = "La question a été ajoutée avec succès.";
			header('Location: question.php?id=' . $data['id']);
    		die();
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_POST)) {
		$errors = array();
		$inputs = array();

		$inputs = $data;

		// Question
		if (!empty($_POST['question']) && strcmp($_POST['question'], $inputs['question']) !== 0) {
			if (!isset($errors['question'])) {
				$inputs['question'] = $_POST['question'];
			}
		}

		// Bonne réponse
		if (!empty($_POST['bonneReponse']) && strcmp($_POST['bonneReponse'], $inputs['idReponse']) !== 0) {
			// Vérifier que la réponse appartienne à la question
			$request = $db->prepare("SELECT * FROM reponse WHERE id = :id AND idQuestion = :idQuestion");
			$request->execute(array(
				'id'			=> $_POST['bonneReponse'],
				'idQuestion'	=> $data['idQuestion']
			));
			$donnees = $request->fetch();
			if (!$donnees) {
				$errors['bonneReponse'] = "Cette réponse n'appartient pas à cette question.";
			}

			if (!isset($errors['bonneReponse'])) {
				$inputs['idReponse'] = $_POST['bonneReponse'];
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
					$request = $db->prepare('UPDATE question SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idQuiz = :idQuiz');
					$request->execute(array(
				        'nouvelleOrdre'		=> $i - 1,
				        'ordre'				=> $i,
				        'idQuiz'  		=> $inputs['idQuiz']
			      	));
				}
			} else if ($nouvellePosition < $anciennePosition) {
				for ($i = $anciennePosition - 1; $i >= $nouvellePosition; $i--) {
					$request = $db->prepare('UPDATE question SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idQuiz = :idQuiz');
					$request->execute(array(
				        'nouvelleOrdre'		=> $i + 1,
				        'ordre'				=> $i,
				        'idQuiz'  		=> $inputs['idQuiz']
			      	));
				}
			}
			
			$inputs['ordre'] = (string) $nouvellePosition;
		}

		if (empty($errors)) {
			$request = $db->prepare('UPDATE question SET ordre = :ordre, question = :question, idReponse = :idReponse WHERE id = :id');
	      	$request->execute(array(
		        'ordre'		=> $inputs['ordre'],
		        'question'  => $inputs['question'],
		        'idReponse'	=> $inputs['idReponse'],
		        'id'		=> $data['idQuestion']
	      	));

	      	$data = $inputs;
			$_SESSION['flash']['success'] = "La question a été modifié avec succès.";
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression des réponses
			$request = $db->prepare("DELETE FROM reponse WHERE idQuestion = :idQuestion");
			$request->execute(array(
				'idQuestion' => $data['idQuestion']
			));

			// Remettre dans l'ordre les questions suivantes
			$request = $db->prepare("SELECT ordre FROM question WHERE id = :id");
			$request->execute(array(
				'id' => $data['idQuestion']
			));
			$donnees = $request->fetch();
			$ordre = $donnees['ordre'];

			$request = $db->prepare("SELECT MAX(ordre) AS ordreMax FROM question WHERE idQuiz = :idQuiz");
			$request->execute(array(
				'idQuiz' => $data['idQuiz']
			));
			$donnees = $request->fetch();
			$ordreMax = $donnees['ordreMax'];

			// Suppression de la question
			$request = $db->prepare("DELETE FROM question WHERE id = :id");
			$request->execute(array(
				'id' => $data['idQuestion']
			));

			for ($i = $ordre; $i <= $ordreMax; $i++) {
				$request = $db->prepare('UPDATE question SET ordre = :nouvelleOrdre WHERE ordre = :ordre AND idQuiz = :idQuiz');
				$request->execute(array(
			        'nouvelleOrdre'		=> $i - 1,
			        'ordre'				=> $i,
			        'idQuiz'  			=> $data['idQuiz']
		      	));
			}

			header('Location: question.php?id=' . $data['idQuiz']);
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: question.php?id=' . $data['idQuiz']);
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
				<h2 class="header-page">Question</h2>
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
					if ($_GET['action'] == 'ajouter') {
						$fil['quiz.php?id=' . $data['id']] = $data['nom'];
						$fil['question.php?id=' . $data['id']] = 'Questions du quiz';
						$fil['question.php?id=' . $data['id'] . '&action=ajouter'] = 'Ajout d\'une question au quiz : ' . $data['nom'];
					} else if ($_GET['action'] == 'modifier') {
						$fil['quiz.php?id=' . $data['idQuiz']] = $data['nomQuiz'];
						$fil['question.php?id=' . $data['idQuiz']] = 'Questions du quiz';
						$fil['question.php?id=' . $data['idQuestion'] . '&action=modifier'] = 'Modification de la question : ' . $data['question'];
					} else if ($_GET['action'] == 'supprimer') {
						$fil['quiz.php?id=' . $data['idQuiz']] = $data['nomQuiz'];
						$fil['question.php?id=' . $data['idQuiz']] = 'Questions du quiz';
						$fil['question.php?id=' . $data['idQuestion'] . '&action=supprimer'] = 'Suppression de la question : ' . $data['question'];
					}
				} else {
					$fil['quiz.php?id=' . $data['id']] = $data['nom'];
					$fil['question.php?id=' . $data['id']] = 'Questions du quiz : ' . $data['nom'];
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
					<h3>Ajout d'une question</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="question.php?id=<?php echo $data['id']; ?>&action=ajouter">
					<fieldset>
						<p>En ajoutant des réponses à la question, vous pourrez sélectionner la bonne réponse à la question.</p>
						<div class="form-group">
							<label class="col-md-4 control-label" for="question">Question</label>  
							<div class="col-md-5">
								<textarea id="question" name="question" placeholder="Question" class="form-control input-md"></textarea>
					         </div>
						</div>
						<!-- Choix de la bonne réponse -->
						<!-- Ordre des questions -->
						<?php
							$request = $db->prepare('SELECT * FROM question WHERE idQuiz = :id ORDER BY ordre');
							$request->execute(array(
								'id' => $data['id']
							));
							$donnees = $request->fetch();
							if ($donnees) {
							?>
							<div class="form-group <?php if (isset($errors['ordre'])) echo "has-error"; ?>">
								<label class="col-md-4 control-label" for="ordre">Ordre de la question :</label>
								<div class="col-md-2">
									<select id="position" name="position" class="form-control">
										<option value="avant">Avant</option>
										<option value="apres">Après</option>
									</select>
								</div>
								<div class="col-md-3">
									<select id="ordre" name="ordre" class="form-control">
									<?php
										$request = $db->prepare('SELECT * FROM question WHERE idQuiz = :id ORDER BY ordre');
										$request->execute(array(
											'id' => $data['id']
										));
										while ($donnees = $request->fetch()) {
											echo '<option value="' . $donnees['ordre'] . '">' . trunc(strip_tags(htmlspecialchars($donnees['question'])), 15) . '</option>';
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
						<?php } ?>
						<!-- Button -->
						<div class="form-group text-center">
							<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
								<input type="submit" name="submit" class="btn btn-primary btn-block" value="Ajouter"></button>
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
					<h3>Modification d'une question</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="question.php?id=<?php echo $data['idQuestion']; ?>&action=modifier">
					<fieldset>
						<!-- Question -->
						<div class="form-group">
							<label class="col-md-4 control-label" for="question">Question</label>  
							<div class="col-md-5">
								<textarea id="question" name="question" placeholder="Nom du quiz" class="form-control input-md"><?php echo strip_tags(htmlspecialchars($data['question'])); ?></textarea>
					         </div>
						</div>

						<!-- Choix de la bonne réponse / Vérifier qu'il existe au moins une réponse à la question -->
						<?php
						$request = $db->prepare('SELECT * FROM reponse WHERE idQuestion = :id');
						$request->execute(array(
							'id'	=> $data['idQuestion']
						));
						if ($request->rowCount() > 0) {
						?>
						<div class="form-group <?php if (isset($errors['bonneReponse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="bonneReponse">Bonne réponse à la question :</label>
							<div class="col-md-5">
								<select id="bonneReponse" name="bonneReponse" class="form-control">
								<?php
								while ($donnees = $request->fetch()) {
									echo '<option value="' . $donnees['id'] . '"';
									if ($donnees['id'] == $data['idReponse'])
										echo ' selected="selected"';
									echo '>' . trunc(strip_tags(htmlspecialchars($donnees['reponse'])), 15) . '</option>';
								}
								?>
								</select>
								<span class="help-block">
									<?php
									if (isset($errors['bonneReponse']))
										echo strip_tags(htmlspecialchars($errors['bonneReponse']));
									?>
								</span>
							</div>
						</div>
						<?php
						}
						?>

						<!-- Ordre des questions -->
						<?php
						$request = $db->prepare('SELECT id, COUNT(id) AS nbReponses FROM question WHERE idQuiz = :idQuiz ORDER BY ordre');
						$request->execute(array(
							'idQuiz'	=> $data['idQuiz']
						));
						$donnees = $request->fetch();

						if (!($donnees['nbReponses'] == 1 && $donnees['id'] == $data['idQuestion'])) {
						?>
						<div class="form-group <?php if (isset($errors['ordre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ordre">Ordre de la question :</label>
							<div class="col-md-2">
								<select id="position" name="position" class="form-control">
									<option value="avant">Avant</option>
									<option value="apres">Après</option>
								</select>
							</div>
							<div class="col-md-3">
								<select id="ordre" name="ordre" class="form-control">
								<?php
									$request = $db->prepare('SELECT * FROM question WHERE idQuiz = :idQuiz ORDER BY ordre');
									$request->execute(array(
										'idQuiz'	=> $data['idQuiz']
									));
									while ($donnees = $request->fetch()) {
										if ($data['ordre'] != $donnees['ordre']) {
											echo '<option value="' . $donnees['ordre'] . '"';
											if ($data['ordre'] + 1 == $donnees['ordre'])
												echo 'selected="selected"';
											echo '>' . trunc(strip_tags(htmlspecialchars($donnees['question'])), 15) . '</option>';
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
						<?php } ?>
						<!-- Button -->
						<div class="form-group text-center">
							<div class="col-xs-12 col-sm-12 col-md-offset-4 col-md-5 col-lg-offset-4 col-lg-5">
								<input type="submit" name="submit" class="btn btn-primary btn-block" value="Modifier"></button>
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
					<h3>Suppression de la question : <?php echo strip_tags(htmlspecialchars($data['question'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'une question entraine la suppression de l'ensemble des réponses de la question !</p>
				<p>Êtes-vous sûr de vouloir supprimer cette question ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="question.php?id=<?php echo $data['idQuestion']; ?>&action=supprimer">
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
		} else { // Affichage des questions du quiz
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Questions du quiz : <?php echo strip_tags(htmlspecialchars($data['nom']));
					if ($acces['ajouterQuestion'])
						echo ' <a href="question.php?id=' . $data['id'] . '&action=ajouter"><span class="glyphicon glyphicon-plus pull-right"></span></a> ';
					?>
					</h3>
				</div>
			</div>
			<div class="row">
			<?php
				$request = $db->prepare("SELECT * FROM question WHERE idQuiz = :id ORDER BY ordre");
				$request->execute(array(
					'id'	=> $data['id']
				));

				echo '<ul>';
				while ($donnees = $request->fetch()) {
					echo '<li>Question : ' . strip_tags(htmlspecialchars($donnees['question']));
					if ($acces['modifierQuestion'] == '1')
						echo '<a href="question.php?id=' . $donnees['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a>';
					if ($acces['supprimerQuestion'] == '1')
						echo '<a href="question.php?id=' . $donnees['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';

					// Ajout d'une réponse
					if ($acces['ajouterReponse'] == '1') {
						echo '<a href="reponse.php?id=' . $donnees['id'] . '&action=ajouter"><span class="glyphicon glyphicon-plus"></span></a>';
					}

					// Réponses de la question
					$requete = $db->prepare("SELECT * FROM reponse WHERE idQuestion = :id");
					$requete->execute(array(
						'id'	=> $donnees['id']
					));

					$i = 0;
					while ($result = $requete->fetch()) {
						if ($i == 0)
							echo '<ul>';
						echo '<li>Réponse : ' . strip_tags(htmlspecialchars($result['reponse']));
						if ($acces['modifierReponse'] == '1')
						echo '<a href="reponse.php?id=' . $result['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a>';
						if ($acces['supprimerReponse'] == '1')
							echo '<a href="reponse.php?id=' . $result['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a>';
						echo '</li>';
						$i++;
					}
					if ($i > 0)
						echo '</ul>';

					echo '</li><br>';
				}
				echo '</ul>';
			?>
			</div>
		<?php } ?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>