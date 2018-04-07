<?php
	$title = "Résultat";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	// Vérifier exitence du quiz
	if (isset($_GET['id'])) {
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

	// Vérifier que toutes les questions du quiz ont été répondues
	$request = $db->prepare("SELECT COUNT(id) AS nbQuestion FROM question WHERE idQuiz = :idQuiz AND idReponse <> '0'");
	$request->execute(array(
		'idQuiz'	=> $data['id']
	));
	$donnees = $request->fetch();

	if (isset($_SESSION['quiz'][$data['id']])) {
		if (count($_SESSION['quiz'][$data['id']]) != $donnees['nbQuestion']) {
			?><script type="text/javascript">javascript:history.back();</script><?php
		    header('Location: 404.php');
		    die();
		}
	} else {
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
				<h2 class="header-page">Résultat du quiz</h2>
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
			$fil = array('quiz.php' => 'Quiz', 'quiz.php?id=' . $data['id'] => $data['nom'], 'resultat.php?id=' . $data['id'] => 'Résutats');
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
		
		?>
		<div class="row">
			<div class="col-lg-12 page-header">
				<h3>Résultats du quiz : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
			</div>
		</div>
		<div class="row">
			<?php
			$note = 0;
			$cpt = 0;

			// Afficher les réponses (bonnes ou mauvaises avec le complément d'informations)
			$request = $db->prepare("SELECT * FROM question WHERE idQuiz = :idQuiz AND idReponse <> '0'");
			$request->execute(array(
				'idQuiz'	=> $data['id']
			));

			while($donnees = $request->fetch()) {
				$cpt++;
				// Comparer la bonne réponse à celle de l'utilisateur
				$requete = $db->prepare("SELECT * FROM reponse WHERE id = :idReponse");
				$requete->execute(array(
					'idReponse'	=> $donnees['idReponse']
				));
				$result = $requete->fetch();

				echo '<h4>Question : ' . strip_tags(htmlspecialchars($donnees['question'])) . '</h4>';
				$req = $db->prepare("SELECT * FROM reponse WHERE id = :id");
				$req->execute(array(
					'id'	=> $_SESSION['quiz'][$data['id']][$donnees['id']]
				));
				$reponse = $req->fetch();
				?>
				<div class="alert <?php if ($_SESSION['quiz'][$data['id']][$donnees['id']] == $donnees['idReponse']) { echo "alert-success"; $note++; } else echo "alert-danger"; ?>">
					<span class="sr-only">Error:</span>
					<p>La bonne réponse était : <?php echo strip_tags(htmlspecialchars($result['reponse'])); ?>. Vous avez répondu : <?php echo strip_tags(htmlspecialchars($reponse['reponse'])); ?>.</p>
					<p><span class="glyphicon glyphicon-info"></span>  <?php echo $result['complement']; ?></p>
				</div>
			<?php
			}
			$note = $note * 20 / $cpt;
			$note = round($note, 1);
			?>
		</div>
		<div class="row text-center">
			<h3>Note : <?php echo $note . ' / 20'; ?></h3>
		</div>

		<?php
		// Enregistrement du résultat
		if (connecte()) {
			$request = $db->prepare("INSERT INTO resultat(idUtilisateur, idQuiz, note, date) VALUES(:idUtilisateur, :idQuiz, :note, NOW())");
			$request->execute(array(
				'idUtilisateur'	=> $_SESSION['auth']['id'],
				'idQuiz'		=> $data['id'],
				'note'			=> $note
			));

			unset($_SESSION['quiz'][$data['id']]);
		}
		?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>