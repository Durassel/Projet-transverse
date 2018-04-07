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

	if (!isset($_GET['action'])) {
		?><script type="text/javascript">javascript:history.back();</script><?php
		header('Location: 404.php');
		die();
	}

	if (isset($_GET['id'])) {
		if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
			$request = $db->prepare("SELECT quiz.id AS idQuiz, quiz.nom AS nomQuiz, question.id AS idQuestion, question.question, question.idReponse, question.ordre FROM quiz, question WHERE question.id = :id AND question.idQuiz = quiz.id");
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
			$request = $db->prepare("SELECT quiz.nom AS nomQuiz, question.id AS idQuestion, question.idQuiz AS idQuiz, question.question, question.idReponse AS idBonneReponse, question.ordre, reponse.id AS idReponse, reponse.reponse, reponse.complement FROM quiz, question, reponse WHERE reponse.id = :id AND reponse.idQuestion = question.id AND question.idQuiz = quiz.id");
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
	}

	$request = $db->prepare('SELECT * FROM action WHERE idStatut = :idStatut');
	$request->execute(array(
		'idStatut' => $_SESSION['auth']['idStatut']
	));
	$acces = $request->fetch();

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter') {
		if ($acces['ajouterReponse'] == '0') {
			header('Location: 404.php');
			die();
		}
	} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
		if ($acces['modifierReponse'] == '0') {
			header('Location: 404.php');
			die();
		}
	} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
		if ($acces['supprimerReponse'] == '0') {
			header('Location: 404.php');
			die();
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		// Réponse

		// Complément de réponse


		if (empty($errors)) {
			$request = $db->prepare('INSERT INTO reponse(idQuestion, reponse, complement) VALUES(:idQuestion, :reponse, :complement)');
	      	$request->execute(array(
	      		'idQuestion' 	=> $data['idQuestion'],
	      		'reponse'		=> $_POST['reponse'],
	      		'complement'	=> $_POST['complement']
	      	));

	      	// Si la question n'a pas de bonne réponse, ajouter cette réponse par défaut
	      	if ($data['idReponse'] == '0') {
	      		// Récupérer l'id de la réponse que l'on vient d'ajouter
	      		$request = $db->prepare('SELECT MAX(id) AS idMax FROM reponse WHERE idQuestion = :idQuestion');
		      	$request->execute(array(
		      		'idQuestion' 	=> $data['idQuestion']
		      	));
		      	$donnees = $request->fetch();

	      		$request = $db->prepare('UPDATE question SET idReponse = :idReponse WHERE id = :id');
		      	$request->execute(array(
			        'idReponse' 	=> $donnees['idMax'],
			        'id'			=> $data['idQuestion']
		      	));
	      	}

			$_SESSION['flash']['success'] = "La réponse a été ajoutée avec succès.";
			header('Location: question.php?id=' . $data['idQuiz']);
    		die();
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_POST)) {
		$errors = array();
		$inputs = array();

		$inputs = $data;

		// Réponse
		if (!empty($_POST['reponse']) && strcmp($_POST['reponse'], $inputs['reponse']) !== 0) {
			if (!isset($errors['reponse'])) {
				$inputs['reponse'] = $_POST['reponse'];
			}
		}
		// Complément de réponse
		if (!empty($_POST['complement']) && strcmp($_POST['complement'], $inputs['complement']) !== 0) {
			if (!isset($errors['complement'])) {
				$inputs['complement'] = $_POST['complement'];
			}
		}

		if (empty($errors)) {
			$request = $db->prepare('UPDATE reponse SET reponse = :reponse, complement = :complement WHERE id = :id');
	      	$request->execute(array(
		        'reponse'		=> $inputs['reponse'],
		        'complement' 	=> $inputs['complement'],
		        'id'			=> $data['idReponse']
	      	));

	      	$data = $inputs;
			$_SESSION['flash']['success'] = "La réponse a été modifié avec succès.";
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			// Suppression de la réponse
			$request = $db->prepare("DELETE FROM reponse WHERE id = :id");
			$request->execute(array(
				'id' => $data['idReponse']
			));

			$_SESSION['flash']['success'] = "La réponse a été supprimée avec succès.";
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
				<h2 class="header-page">Réponse</h2>
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
						$fil['quiz.php?id=' . $data['idQuiz']] = $data['nomQuiz'];
						$fil['question.php?id=' . $data['idQuiz']] = 'Questions du quiz';
						$fil['reponse.php?id=' . $data['idReponse'] . '&action=ajouter'] = 'Ajout d\'une réponse à la question';
					} else if ($_GET['action'] == 'modifier') {
						$fil['quiz.php?id=' . $data['idQuiz']] = $data['nomQuiz'];
						$fil['question.php?id=' . $data['idQuiz']] = 'Questions du quiz';
						$fil['question.php?id=' . $data['idQuestion'] . '&action=modifier'] = 'Modification de la réponse';
					} else if ($_GET['action'] == 'supprimer') {
						$fil['quiz.php?id=' . $data['idQuiz']] = $data['nomQuiz'];
						$fil['question.php?id=' . $data['idQuiz']] = 'Questions du quiz';
						$fil['question.php?id=' . $data['idQuestion'] . '&action=supprimer'] = 'Suppression de la réponse';
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
					<h3>Ajout d'une réponse</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="reponse.php?id=<?php echo strip_tags(htmlspecialchars($data['idQuestion'])); ?>&action=ajouter">
					<fieldset>
						<!-- Réponse -->
						<div class="form-group">
							<label class="col-md-4 control-label" for="reponse">Réponse</label>  
							<div class="col-md-5">
								<textarea id="reponse" name="reponse" placeholder="Réponse" class="form-control input-md"></textarea>
					         </div>
						</div>
						<!-- Complément de réponse -->
						<div class="form-group">
							<label class="col-md-4 control-label" for="complement">Complément de réponse</label>  
							<div class="col-md-5">
								<textarea id="complement" name="complement" placeholder="Compélment de réponse" class="form-control input-md wysiwyg"></textarea>
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
					<h3>Modification d'une réponse</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="reponse.php?id=<?php echo strip_tags(htmlspecialchars($data['idReponse'])); ?>&action=modifier">
					<fieldset>
						<!-- Réponse -->
						<div class="form-group">
							<label class="col-md-4 control-label" for="reponse">Réponse</label>  
							<div class="col-md-5">
								<textarea id="reponse" name="reponse" placeholder="Réponse" class="form-control input-md"><?php echo strip_tags(htmlspecialchars($data['reponse'])); ?></textarea>
					         </div>
						</div>
						<!-- Complément de réponse -->
						<div class="form-group">
							<label class="col-md-4 control-label" for="complement">Complément de réponse</label>  
							<div class="col-md-5">
								<textarea id="complement" name="complement" placeholder="Compélment de réponse" class="form-control input-md wysiwyg"><?php echo strip_tags(htmlspecialchars($data['complement'])); ?></textarea>
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
					<h3>Suppression de la réponse : <?php echo strip_tags(htmlspecialchars($data['reponse'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>Êtes-vous sûr de vouloir supprimer cette réponse ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="reponse.php?id=<?php echo strip_tags(htmlspecialchars($data['idReponse'])); ?>&action=supprimer">
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
		<?php } ?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>