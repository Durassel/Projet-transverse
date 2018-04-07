<?php
	$title = "Statuts";

	include_once "includes/db.php";
	include_once "includes/functions.php";

	if (isset($_GET['id'])) {
		$request = $db->prepare('SELECT * FROM statut, action WHERE statut.id = :id AND action.idStatut = :id');
		$request->execute(array(
			'id' 		=> $_GET['id']
		));
		$data = $request->fetch();

		if (!$data) {
		    header('Location: 404.php');
		    die();
		}
	}

	if ((isset($_GET['action']) && $_GET['action'] != 'ajouter' && $_GET['action'] != 'modifier' && $_GET['action'] != 'supprimer') || (isset($_GET['action']) && ($_GET['action'] == 'modifier' || $_GET['action'] == 'supprimer') && !isset($_GET['id']))) {
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
			if ($acces['ajouterStatut'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'modifier') {
			if ($acces['modifierStatut'] == '0') {
				header('Location: 404.php');
				die();
			}
		} else if (isset($_GET['action']) && $_GET['action'] == 'supprimer') {
			if ($acces['supprimerStatut'] == '0') {
				header('Location: 404.php');
				die();
			}
		}
	}

	if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && !empty($_POST)) {
		$errors = array();

		if (!empty($_POST['nom']) && !empty($_POST['level'])) {
			// Nom
			$request = $db->prepare("SELECT nom FROM statut WHERE nom = :nom");
			$request->execute(array(
				'nom' => $_POST['nom']
			));
			$donnees = $request->fetch();

			if (strlen($_POST['nom']) > 255)
				$errors['tailleNom'] = "Le nom du statut est trop long.";
			if (is_numeric($_POST['nom']))
				$errors['nomNombre'] = "Le nom du statut n'est pas une chaine de caractères.";
			if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
				$errors['nom'] = "Le nom du statut n'est pas valide.";
			if ($donnees) {
				$errors['nomUtilise'] = "Le nom du statut est déjà utilisé.";
			}

			// Level
			$request = $db->prepare("SELECT level FROM statut WHERE level = :level");
			$request->execute(array(
				'level' => $_POST['level']
			));
			$donnees = $request->fetch();

			if (!is_numeric($_POST['level']))
				$errors['levelNombre'] = "Le level n'est pas un entier.";
			if ($donnees)
				$errors['levelUtilise'] = "Le level est déjà utilisé.";

			// Actions
			if ($_POST['modifierUtilisateur'] != '0' && $_POST['modifierUtilisateur'] != '1')
				$errors['modifierUtilisateur'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerUtilisateur'] != '0' && $_POST['supprimerUtilisateur'] != '1')
				$errors['supprimerUtilisateur'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterParent'] != '0' && $_POST['ajouterParent'] != '1')
				$errors['ajouterParent'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerParent'] != '0' && $_POST['supprimerParent'] != '1')
				$errors['supprimerParent'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterEtablissement'] != '0' && $_POST['ajouterEtablissement'] != '1')
				$errors['ajouterEtablissement'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierEtablissement'] != '0' && $_POST['modifierEtablissement'] != '1')
				$errors['modifierEtablissement'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerEtablissement'] != '0' && $_POST['supprimerEtablissement'] != '1')
				$errors['supprimerEtablissement'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterClasse'] != '0' && $_POST['ajouterClasse'] != '1')
				$errors['ajouterClasse'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierClasse'] != '0' && $_POST['modifierClasse'] != '1')
				$errors['modifierClasse'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerClasse'] != '0' && $_POST['supprimerClasse'] != '1')
				$errors['supprimerClasse'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterParrainage'] != '0' && $_POST['ajouterParrainage'] != '1')
				$errors['ajouterParrainage'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerParrainage'] != '0' && $_POST['supprimerParrainage'] != '1')
				$errors['supprimerParrainage'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterUtilisateurClasse'] != '0' && $_POST['ajouterUtilisateurClasse'] != '1')
				$errors['ajouterUtilisateurClasse'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerUtilisateurClasse'] != '0' && $_POST['supprimerUtilisateurClasse'] != '1')
				$errors['supprimerUtilisateurClasse'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterUtilisateurEtablissement'] != '0' && $_POST['ajouterUtilisateurEtablissement'] != '1')
				$errors['ajouterUtilisateurEtablissement'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerUtilisateurEtablissement'] != '0' && $_POST['supprimerUtilisateurEtablissement'] != '1')
				$errors['supprimerUtilisateurEtablissement'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterTheme'] != '0' && $_POST['ajouterTheme'] != '1')
				$errors['ajouterTheme'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierTheme'] != '0' && $_POST['modifierTheme'] != '1')
				$errors['modifierTheme'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerTheme'] != '0' && $_POST['supprimerTheme'] != '1')
				$errors['supprimerTheme'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterCours'] != '0' && $_POST['ajouterCours'] != '1')
				$errors['ajouterCours'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerCours'] != '0' && $_POST['supprimerCours'] != '1')
				$errors['supprimerCours'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterChapitre'] != '0' && $_POST['ajouterChapitre'] != '1')
				$errors['ajouterChapitre'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierChapitre'] != '0' && $_POST['modifierChapitre'] != '1')
				$errors['modifierChapitre'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerChapitre'] != '0' && $_POST['supprimerChapitre'] != '1')
				$errors['supprimerChapitre'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterNiveau'] != '0' && $_POST['ajouterNiveau'] != '1')
				$errors['ajouterNiveau'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierNiveau'] != '0' && $_POST['modifierNiveau'] != '1')
				$errors['modifierNiveau'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerNiveau'] != '0' && $_POST['supprimerNiveau'] != '1')
				$errors['supprimerNiveau'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterStatut'] != '0' && $_POST['ajouterStatut'] != '1')
				$errors['ajouterStatut'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierStatut'] != '0' && $_POST['modifierStatut'] != '1')
				$errors['modifierStatut'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerStatut'] != '0' && $_POST['supprimerStatut'] != '1')
				$errors['supprimerStatut'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterQuiz'] != '0' && $_POST['ajouterQuiz'] != '1')
				$errors['ajouterQuiz'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierQuiz'] != '0' && $_POST['modifierQuiz'] != '1')
				$errors['modifierQuiz'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerQuiz'] != '0' && $_POST['supprimerQuiz'] != '1')
				$errors['supprimerQuiz'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterQuestion'] != '0' && $_POST['ajouterQuestion'] != '1')
				$errors['ajouterQuestion'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierQuestion'] != '0' && $_POST['modifierQuestion'] != '1')
				$errors['modifierQuestion'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerQuestion'] != '0' && $_POST['supprimerQuestion'] != '1')
				$errors['supprimerQuestion'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['ajouterReponse'] != '0' && $_POST['ajouterReponse'] != '1')
				$errors['ajouterReponse'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['modifierReponse'] != '0' && $_POST['modifierReponse'] != '1')
				$errors['modifierReponse'] = "Veuillez répondre par oui ou par non.";
			if ($_POST['supprimerReponse'] != '0' && $_POST['supprimerReponse'] != '1')
				$errors['supprimerReponse'] = "Veuillez répondre par oui ou par non.";
      
			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('INSERT INTO statut(nom, level) VALUES(:nom, :level)');
		      	$request->execute(array(
		      		'nom' 	=> $_POST['nom'],
		      		'level' 	=> $_POST['level']
		      	));

		      	$request = $db->prepare('INSERT INTO action(modifierUtilisateur, supprimerUtilisateur, ajouterEtablissement, modifierEtablissement, supprimerEtablissement, ajouterClasse, modifierClasse, supprimerClasse, ajouterEleve, supprimerEleve, ajouterProfesseur, supprimerProfesseur, ajouterTheme, modifierTheme, supprimerTheme, ajouterCours, supprimerCours, ajouterChapitre, modifierChapitre, supprimerChapitre, ajouterNiveau, modifierNiveau, supprimerNiveau, ajouterStatut, modifierStatut, supprimerStatut, ajouterQuiz, modifierQuiz, supprimerQuiz, ajouterQuestion, modifierQuestion, supprimerQuestion, ajouterReponse, modifierReponse, supprimerReponse) VALUES(:modifierUtilisateur, :supprimerUtilisateur, :ajouterEtablissement, :modifierEtablissement, :supprimerEtablissement, :ajouterClasse, :modifierClasse, :supprimerClasse, :ajouterEleve, :supprimerEleve, :ajouterProfesseur, :supprimerProfesseur, :ajouterTheme, :modifierTheme, :supprimerTheme, :ajouterCours, :supprimerCours, :ajouterChapitre, :modifierChapitre, :supprimerChapitre, :ajouterNiveau, :modifierNiveau, :supprimerNiveau, :ajouterStatut, :modifierStatut, :supprimerStatut, :ajouterQuiz, :modifierQuiz, :supprimerQuiz, :ajouterQuestion, :modifierQuestion, :supprimerQuestion, :ajouterReponse, :modifierReponse, :supprimerReponse)');
		      	$request->execute(array(
		      		'modifierUtilisateur' 		=> $_POST['modifierUtilisateur'], 
		      		'supprimerUtilisateur' 		=> $_POST['supprimerUtilisateur'],
		      		'ajouterEtablissement' 		=> $_POST['ajouterEtablissement'],
		      		'modifierEtablissement' 	=> $_POST['modifierEtablissement'],
		      		'supprimerEtablissement'	=> $_POST['supprimerEtablissement'],
		      		'ajouterClasse' 			=> $_POST['ajouterClasse'],
		      		'modifierClasse' 			=> $_POST['modifierClasse'],
		      		'supprimerClasse' 			=> $_POST['supprimerClasse'],
		      		'ajouterEleve' 				=> $_POST['ajouterEleve'],
		      		'supprimerEleve' 			=> $_POST['supprimerEleve'],
		      		'ajouterProfesseur' 		=> $_POST['ajouterProfesseur'],
		      		'supprimerProfesseur' 		=> $_POST['supprimerProfesseur'],
		      		'ajouterTheme' 				=> $_POST['ajouterTheme'],
		      		'modifierTheme' 			=> $_POST['modifierTheme'],
		      		'supprimerTheme' 			=> $_POST['supprimerTheme'],
		      		'ajouterCours' 				=> $_POST['ajouterCours'],
		      		'supprimerCours' 			=> $_POST['supprimerCours'],
		      		'ajouterChapitre' 			=> $_POST['ajouterChapitre'],
		      		'modifierChapitre' 			=> $_POST['modifierChapitre'],
		      		'supprimerChapitre' 		=> $_POST['supprimerChapitre'],
		      		'ajouterNiveau' 			=> $_POST['ajouterNiveau'],
		      		'modifierNiveau' 			=> $_POST['modifierNiveau'],
		      		'supprimerNiveau'			=> $_POST['supprimerNiveau'],
		      		'ajouterStatut' 			=> $_POST['ajouterStatut'],
		      		'modifierStatut' 			=> $_POST['modifierStatut'],
		      		'supprimerStatut' 			=> $_POST['supprimerStatut'],
		      		'ajouterQuiz' 				=> $_POST['ajouterQuiz'],
		      		'modifierQuiz' 				=> $_POST['modifierQuiz'],
		      		'supprimerQuiz' 			=> $_POST['supprimerQuiz'],
		      		'ajouterQuestion' 			=> $_POST['ajouterQuestion'],
		      		'modifierQuestion' 			=> $_POST['modifierQuestion'],
		      		'supprimerQuestion' 		=> $_POST['supprimerQuestion'],
		      		'ajouterReponse' 			=> $_POST['ajouterReponse'],
		      		'modifierReponse' 			=> $_POST['modifierReponse'],
		      		'supprimerReponse' 			=> $_POST['supprimerReponse']
		      	));

				$_SESSION['flash']['success'] = "Le statut a été ajouté avec succès.";
				header('Location: statut.php');
	    		die();
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'modifier' && !empty($_POST)) {
		$errors = array();
		$inputs = array();

		$inputs = $data;

		if (isset($_POST['submit'])) {
			// Niveau
			if (!empty($_POST['nom']) && strcmp($_POST['nom'], $inputs['nom']) !== 0) {
				$request = $db->prepare("SELECT nom FROM statut WHERE nom = :nom");
				$request->execute(array(
					'nom' => $_POST['nom']
				));
				$donnees = $request->fetch();

				if (strlen($_POST['nom']) > 255)
					$errors['tailleNom'] = "Le nom du statut est trop long.";
				if (is_numeric($_POST['nom']))
					$errors['nomNombre'] = "Le nom du statut n'est pas une chaine de caractères.";
				if (!preg_match('/^[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s]+$/', $_POST['nom']))
					$errors['nom'] = "Le nom du statut n'est pas valide.";
				if ($donnees) {
					$errors['nomUtilise'] = "Le nom du statut est déjà utilisé.";
				}

				if (!isset($errors['tailleNom']) && !isset($errors['nomNombre']) && !isset($errors['nom']) && !isset($errors['nomUtilise'])) {
					$inputs['nom'] = $_POST['nom'];
				}
			}

			// Level
			if (!empty($_POST['level']) && strcmp($_POST['level'], $inputs['level']) !== 0) {
				$request = $db->prepare("SELECT level FROM statut WHERE level = :level");
				$request->execute(array(
					'level' => $_POST['level']
				));
				$donnees = $request->fetch();

				if (!is_numeric($_POST['level']))
					$errors['levelNombre'] = "Le level n'est pas un entier.";
				if ($donnees)
					$errors['levelUtilise'] = "Le level est déjà utilisé.";

				if (!isset($errors['levelNombre']) && !isset($errors['levelUtilise'])) {
					$inputs['level'] = $_POST['level'];
				}
			}

			if (!empty($_POST['modifierUtilisateur']) && $_POST['modifierUtilisateur'] != $inputs['modifierUtilisateur']) {
				if ($_POST['modifierUtilisateur'] != '0' && $_POST['modifierUtilisateur'] != '1')
					$errors['modifierUtilisateur'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierUtilisateur'])) {
					$inputs['modifierUtilisateur'] = $_POST['modifierUtilisateur'];
				}
			}
			if (!empty($_POST['supprimerUtilisateur']) && $_POST['supprimerUtilisateur'] != $inputs['supprimerUtilisateur']) {
				if ($_POST['supprimerUtilisateur'] != '0' && $_POST['supprimerUtilisateur'] != '1')
					$errors['supprimerUtilisateur'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerUtilisateur'])) {
					$inputs['supprimerUtilisateur'] = $_POST['supprimerUtilisateur'];
				}
			}
			if (!empty($_POST['ajouterParent']) && $_POST['ajouterParent'] != $inputs['ajouterParent']) {
				if ($_POST['ajouterParent'] != '0' && $_POST['ajouterParent'] != '1')
					$errors['ajouterParent'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterParent'])) {
					$inputs['ajouterParent'] = $_POST['ajouterParent'];
				}
			}
			if (!empty($_POST['supprimerParent']) && $_POST['supprimerParent'] != $inputs['supprimerParent']) {
				if ($_POST['supprimerParent'] != '0' && $_POST['supprimerParent'] != '1')
					$errors['supprimerParent'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerParent'])) {
					$inputs['supprimerParent'] = $_POST['supprimerParent'];
				}
			}
			if (!empty($_POST['ajouterEtablissement']) && $_POST['ajouterEtablissement'] != $inputs['ajouterEtablissement']) {
				if ($_POST['ajouterEtablissement'] != '0' && $_POST['ajouterEtablissement'] != '1')
					$errors['ajouterEtablissement'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterEtablissement'])) {
					$inputs['ajouterEtablissement'] = $_POST['ajouterEtablissement'];
				}
			}
			if (!empty($_POST['modifierEtablissement']) && $_POST['modifierEtablissement'] != $inputs['modifierEtablissement']) {
				if ($_POST['modifierEtablissement'] != '0' && $_POST['modifierEtablissement'] != '1')
					$errors['modifierEtablissement'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierEtablissement'])) {
					$inputs['modifierEtablissement'] = $_POST['modifierEtablissement'];
				}
			}
			if (!empty($_POST['supprimerEtablissement']) && $_POST['supprimerEtablissement'] != $inputs['supprimerEtablissement']) {
				if ($_POST['supprimerEtablissement'] != '0' && $_POST['supprimerEtablissement'] != '1')
					$errors['supprimerEtablissement'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerEtablissement'])) {
					$inputs['supprimerEtablissement'] = $_POST['supprimerEtablissement'];
				}
			}
			if (!empty($_POST['ajouterClasse']) && $_POST['ajouterClasse'] != $inputs['ajouterClasse']) {
				if ($_POST['ajouterClasse'] != '0' && $_POST['ajouterClasse'] != '1')
					$errors['ajouterClasse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterClasse'])) {
					$inputs['ajouterClasse'] = $_POST['ajouterClasse'];
				}
			}
			if (!empty($_POST['modifierClasse']) && $_POST['modifierClasse'] != $inputs['modifierClasse']) {
				if ($_POST['modifierClasse'] != '0' && $_POST['modifierClasse'] != '1')
					$errors['modifierClasse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierClasse'])) {
					$inputs['modifierClasse'] = $_POST['modifierClasse'];
				}
			}
			if (!empty($_POST['supprimerClasse']) && $_POST['supprimerClasse'] != $inputs['supprimerClasse']) {
				if ($_POST['supprimerClasse'] != '0' && $_POST['supprimerClasse'] != '1')
					$errors['supprimerClasse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerClasse'])) {
					$inputs['supprimerClasse'] = $_POST['supprimerClasse'];
				}
			}
			if (!empty($_POST['ajouterParrainage']) && $_POST['ajouterParrainage'] != $inputs['ajouterParrainage']) {
				if ($_POST['ajouterParrainage'] != '0' && $_POST['ajouterParrainage'] != '1')
					$errors['ajouterParrainage'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterParrainage'])) {
					$inputs['ajouterParrainage'] = $_POST['ajouterParrainage'];
				}
			}
			if (!empty($_POST['supprimerParrainage']) && $_POST['supprimerParrainage'] != $inputs['supprimerParrainage']) {
				if ($_POST['supprimerParrainage'] != '0' && $_POST['supprimerParrainage'] != '1')
					$errors['supprimerParrainage'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerParrainage'])) {
					$inputs['supprimerParrainage'] = $_POST['supprimerParrainage'];
				}
			}
			if (!empty($_POST['ajouterUtilisateurClasse']) && $_POST['ajouterUtilisateurClasse'] != $inputs['ajouteajouterUtilisateurClasserEleve']) {
				if ($_POST['ajouterUtilisateurClasse'] != '0' && $_POST['ajouterEleve'] != '1')
					$errors['ajouterUtilisateurClasse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterUtilisateurClasse'])) {
					$inputs['ajouterUtilisateurClasse'] = $_POST['ajouterUtilisateurClasse'];
				}
			}
			if (!empty($_POST['supprimerUtilisateurClasse']) && $_POST['supprimerUtilisateurClasse'] != $inputs['supprimerUtilisateurClasse']) {
				if ($_POST['supprimerUtilisateurClasse'] != '0' && $_POST['supprimerUtilisateurClasse'] != '1')
					$errors['supprimerUtilisateurClasse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerUtilisateurClasse'])) {
					$inputs['supprimerUtilisateurClasse'] = $_POST['supprimerUtilisateurClasse'];
				}
			}
			if (!empty($_POST['ajouterUtilisateurEtablissement']) && $_POST['ajouterUtilisateurEtablissement'] != $inputs['ajouterUtilisateurEtablissement']) {
				if ($_POST['ajouterUtilisateurEtablissement'] != '0' && $_POST['ajouterUtilisateurEtablissement'] != '1')
					$errors['ajouterUtilisateurEtablissement'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterUtilisateurEtablissement'])) {
					$inputs['ajouterUtilisateurEtablissement'] = $_POST['ajouterUtilisateurEtablissement'];
				}
			}
			if (!empty($_POST['supprimerUtilisateurEtablissement']) && $_POST['supprimerUtilisateurEtablissement'] != $inputs['supprimerUtilisateurEtablissement']) {
				if ($_POST['supprimerUtilisateurEtablissement'] != '0' && $_POST['supprimerUtilisateurEtablissement'] != '1')
					$errors['supprimerUtilisateurEtablissement'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerUtilisateurEtablissement'])) {
					$inputs['supprimerUtilisateurEtablissement'] = $_POST['supprimerUtilisateurEtablissement'];
				}
			}
			if (!empty($_POST['ajouterTheme']) && $_POST['ajouterTheme'] != $inputs['ajouterTheme']) {
				if ($_POST['ajouterTheme'] != '0' && $_POST['ajouterTheme'] != '1')
					$errors['ajouterTheme'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterTheme'])) {
					$inputs['ajouterTheme'] = $_POST['ajouterTheme'];
				}
			}
			if (!empty($_POST['modifierTheme']) && $_POST['modifierTheme'] != $inputs['modifierTheme']) {
				if ($_POST['modifierTheme'] != '0' && $_POST['modifierTheme'] != '1')
					$errors['modifierTheme'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierTheme'])) {
					$inputs['modifierTheme'] = $_POST['modifierTheme'];
				}
			}
			if (!empty($_POST['supprimerTheme']) && $_POST['supprimerTheme'] != $inputs['supprimerTheme']) {
				if ($_POST['supprimerTheme'] != '0' && $_POST['supprimerTheme'] != '1')
					$errors['supprimerTheme'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerTheme'])) {
					$inputs['supprimerTheme'] = $_POST['supprimerTheme'];
				}
			}
			if (!empty($_POST['ajouterCours']) && $_POST['ajouterCours'] != $inputs['ajouterCours']) {
				if ($_POST['ajouterCours'] != '0' && $_POST['ajouterCours'] != '1')
					$errors['ajouterCours'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterCours'])) {
					$inputs['ajouterCours'] = $_POST['ajouterCours'];
				}
			}
			if (!empty($_POST['supprimerCours']) && $_POST['supprimerCours'] != $inputs['supprimerCours']) {
				if ($_POST['supprimerCours'] != '0' && $_POST['supprimerCours'] != '1')
					$errors['supprimerCours'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerCours'])) {
					$inputs['supprimerCours'] = $_POST['supprimerCours'];
				}
			}
			if (!empty($_POST['ajouterChapitre']) && $_POST['ajouterChapitre'] != $inputs['ajouterChapitre']) {
				if ($_POST['ajouterChapitre'] != '0' && $_POST['ajouterChapitre'] != '1')
					$errors['ajouterChapitre'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterChapitre'])) {
					$inputs['ajouterChapitre'] = $_POST['ajouterChapitre'];
				}
			}
			if (!empty($_POST['modifierChapitre']) && $_POST['modifierChapitre'] != $inputs['modifierChapitre']) {
				if ($_POST['modifierChapitre'] != '0' && $_POST['modifierChapitre'] != '1')
					$errors['modifierChapitre'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierChapitre'])) {
					$inputs['modifierChapitre'] = $_POST['modifierChapitre'];
				}
			}
			if (!empty($_POST['supprimerChapitre']) && $_POST['supprimerChapitre'] != $inputs['supprimerChapitre']) {
				if ($_POST['supprimerChapitre'] != '0' && $_POST['supprimerChapitre'] != '1')
					$errors['supprimerChapitre'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerChapitre'])) {
					$inputs['supprimerChapitre'] = $_POST['supprimerChapitre'];
				}
			}
			if (!empty($_POST['ajouterNiveau']) && $_POST['ajouterNiveau'] != $inputs['ajouterNiveau']) {
				if ($_POST['ajouterNiveau'] != '0' && $_POST['ajouterNiveau'] != '1')
					$errors['ajouterNiveau'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterNiveau'])) {
					$inputs['ajouterNiveau'] = $_POST['ajouterNiveau'];
				}
			}
			if (!empty($_POST['modifierNiveau']) && $_POST['modifierNiveau'] != $inputs['modifierNiveau']) {
				if ($_POST['modifierNiveau'] != '0' && $_POST['modifierNiveau'] != '1')
					$errors['modifierNiveau'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierNiveau'])) {
					$inputs['modifierNiveau'] = $_POST['modifierNiveau'];
				}
			}
			if (!empty($_POST['supprimerNiveau']) && $_POST['supprimerNiveau'] != $inputs['supprimerNiveau']) {
				if ($_POST['supprimerNiveau'] != '0' && $_POST['supprimerNiveau'] != '1')
					$errors['supprimerNiveau'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerNiveau'])) {
					$inputs['supprimerNiveau'] = $_POST['supprimerNiveau'];
				}
			}
			if (!empty($_POST['ajouterStatut']) && $_POST['ajouterStatut'] != $inputs['ajouterStatut']) {
				if ($_POST['ajouterStatut'] != '0' && $_POST['ajouterStatut'] != '1')
					$errors['ajouterStatut'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterStatut'])) {
					$inputs['ajouterStatut'] = $_POST['ajouterStatut'];
				}
			}
			if (!empty($_POST['modifierStatut']) && $_POST['modifierStatut'] != $inputs['modifierStatut']) {
				if ($_POST['modifierStatut'] != '0' && $_POST['modifierStatut'] != '1')
					$errors['modifierStatut'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierStatut'])) {
					$inputs['modifierStatut'] = $_POST['modifierStatut'];
				}
			}
			if (!empty($_POST['supprimerStatut']) && $_POST['supprimerStatut'] != $inputs['supprimerStatut']) {
				if ($_POST['supprimerStatut'] != '0' && $_POST['supprimerStatut'] != '1')
					$errors['supprimerStatut'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerStatut'])) {
					$inputs['supprimerStatut'] = $_POST['supprimerStatut'];
				}
			}
			if (!empty($_POST['ajouterQuiz']) && $_POST['ajouterQuiz'] != $inputs['ajouterQuiz']) {
				if ($_POST['ajouterQuiz'] != '0' && $_POST['ajouterQuiz'] != '1')
					$errors['ajouterQuiz'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterQuiz'])) {
					$inputs['ajouterQuiz'] = $_POST['ajouterQuiz'];
				}
			}
			if (!empty($_POST['modifierQuiz']) && $_POST['modifierQuiz'] != $inputs['modifierQuiz']) {
				if ($_POST['modifierQuiz'] != '0' && $_POST['modifierQuiz'] != '1')
					$errors['modifierQuiz'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierQuiz'])) {
					$inputs['modifierQuiz'] = $_POST['modifierQuiz'];
				}
			}
			if (!empty($_POST['supprimerQuiz']) && $_POST['supprimerQuiz'] != $inputs['supprimerQuiz']) {
				if ($_POST['supprimerQuiz'] != '0' && $_POST['supprimerQuiz'] != '1')
					$errors['supprimerQuiz'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerQuiz'])) {
					$inputs['supprimerQuiz'] = $_POST['supprimerQuiz'];
				}
			}
			if (!empty($_POST['ajouterQuestion']) && $_POST['ajouterQuestion'] != $inputs['ajouterQuestion']) {
				if ($_POST['ajouterQuestion'] != '0' && $_POST['ajouterQuestion'] != '1')
					$errors['ajouterQuestion'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterQuestion'])) {
					$inputs['ajouterQuestion'] = $_POST['ajouterQuestion'];
				}
			}
			if (!empty($_POST['modifierQuestion']) && $_POST['modifierQuestion'] != $inputs['modifierQuestion']) {
				if ($_POST['modifierQuestion'] != '0' && $_POST['modifierQuestion'] != '1')
					$errors['modifierQuestion'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierQuestion'])) {
					$inputs['modifierQuestion'] = $_POST['modifierQuestion'];
				}
			}
			if (!empty($_POST['supprimerQuestion']) && $_POST['supprimerQuestion'] != $inputs['supprimerQuestion']) {
				if ($_POST['supprimerQuestion'] != '0' && $_POST['supprimerQuestion'] != '1')
					$errors['supprimerQuestion'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerQuestion'])) {
					$inputs['supprimerQuestion'] = $_POST['supprimerQuestion'];
				}
			}
			if (!empty($_POST['ajouterReponse']) && $_POST['ajouterReponse'] != $inputs['ajouterReponse']) {
				if ($_POST['ajouterReponse'] != '0' && $_POST['ajouterReponse'] != '1')
					$errors['ajouterReponse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['ajouterReponse'])) {
					$inputs['ajouterReponse'] = $_POST['ajouterReponse'];
				}
			}
			if (!empty($_POST['modifierReponse']) && $_POST['modifierReponse'] != $inputs['modifierReponse']) {
				if ($_POST['modifierReponse'] != '0' && $_POST['modifierReponse'] != '1')
					$errors['modifierReponse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['modifierReponse'])) {
					$inputs['modifierReponse'] = $_POST['modifierReponse'];
				}
			}
			if (!empty($_POST['supprimerReponse']) && $_POST['supprimerReponse'] != $inputs['supprimerReponse']) {
				if ($_POST['supprimerReponse'] != '0' && $_POST['supprimerReponse'] != '1')
					$errors['supprimerReponse'] = "Veuillez répondre par oui ou par non.";

				if (!isset($errors['supprimerReponse'])) {
					$inputs['supprimerReponse'] = $_POST['supprimerReponse'];
				}
			}

			// Erreur
			if (empty($errors)) {
				$request = $db->prepare('UPDATE statut SET nom = :nom, level = :level WHERE id = :id');
		      	$request->execute(array(
			        'nom'		=> $inputs['nom'],
			        'level'  	=> $inputs['level'],
			        'id'		=> $_GET['id']
		      	));

		      	$request = $db->prepare('UPDATE action SET modifierUtilisateur = :modifierUtilisateur, supprimerUtilisateur = :supprimerUtilisateur, ajouterEtablissement = :ajouterEtablissement, modifierEtablissement = :modifierEtablissement, supprimerEtablissement = :supprimerEtablissement, ajouterClasse = :ajouterClasse, modifierClasse = :modifierClasse, supprimerClasse = :supprimerClasse, ajouterEleve = :ajouterEleve, supprimerEleve = :supprimerEleve, ajouterProfesseur = :ajouterProfesseur, supprimerProfesseur = :supprimerProfesseur, ajouterTheme = :ajouterTheme, modifierTheme = :modifierTheme, supprimerTheme = :supprimerTheme, ajouterCours = :ajouterCours, supprimerCours = :supprimerCours, ajouterChapitre = :ajouterChapitre, modifierChapitre = :modifierChapitre, supprimerChapitre = :supprimerChapitre, ajouterNiveau = :ajouterNiveau, modifierNiveau = :modifierNiveau, supprimerNiveau = :supprimerNiveau, ajouterStatut = :ajouterStatut, modifierStatut = :modifierStatut, supprimerStatut = :supprimerStatut, ajouterQuiz = :ajouterQuiz, modifierQuiz = :modifierQuiz, supprimerQuiz = :supprimerQuiz, ajouterQuestion = :ajouterQuestion, modifierQuestion = :modifierQuestion, supprimerQuestion = :supprimerQuestion, ajouterReponse = :ajouterReponse, modifierReponse = :modifierReponse, supprimerReponse = :supprimerReponse WHERE id = :idStatut');

		      	$request->execute(array(
		      		'modifierUtilisateur' 				=> $_POST['modifierUtilisateur'], 
		      		'supprimerUtilisateur' 				=> $_POST['supprimerUtilisateur'],
		      		'ajouterParent'						=> $_POST['ajouterParent'],
		      		'supprimerParent'					=> $_POST['supprimerParent'],
		      		'ajouterEtablissement' 				=> $_POST['ajouterEtablissement'],
		      		'modifierEtablissement' 			=> $_POST['modifierEtablissement'],
		      		'supprimerEtablissement'			=> $_POST['supprimerEtablissement'],
		      		'ajouterClasse' 					=> $_POST['ajouterClasse'],
		      		'modifierClasse' 					=> $_POST['modifierClasse'],
		      		'supprimerClasse' 					=> $_POST['supprimerClasse'],
		      		'ajouterParrainage'					=> $_POST['ajouterParrainage'],
		      		'supprimerParrainage'				=> $_POST['supprimerParrainage'],
		      		'ajouterUtilisateurClasse' 			=> $_POST['ajouterUtilisateurClasse'],
		      		'supprimerUtilisateurClasse'		=> $_POST['supprimerUtilisateurClasse'],
		      		'ajouterUtilisateurEtablissement'	=> $_POST['ajouterUtilisateurEtablissement'],
		      		'supprimerUtilisateurEtablissement' => $_POST['supprimerUtilisateurEtablissement'],
		      		'ajouterTheme' 						=> $_POST['ajouterTheme'],
		      		'modifierTheme' 					=> $_POST['modifierTheme'],
		      		'supprimerTheme' 					=> $_POST['supprimerTheme'],
		      		'ajouterCours' 						=> $_POST['ajouterCours'],
		      		'supprimerCours' 					=> $_POST['supprimerCours'],
		      		'ajouterChapitre' 					=> $_POST['ajouterChapitre'],
		      		'modifierChapitre' 					=> $_POST['modifierChapitre'],
		      		'supprimerChapitre' 				=> $_POST['supprimerChapitre'],
		      		'ajouterNiveau' 					=> $_POST['ajouterNiveau'],
		      		'modifierNiveau' 					=> $_POST['modifierNiveau'],
		      		'supprimerNiveau'					=> $_POST['supprimerNiveau'],
		      		'ajouterStatut' 					=> $_POST['ajouterStatut'],
		      		'modifierStatut' 					=> $_POST['modifierStatut'],
		      		'supprimerStatut' 					=> $_POST['supprimerStatut'],
		      		'ajouterQuiz' 						=> $_POST['ajouterQuiz'],
		      		'modifierQuiz' 						=> $_POST['modifierQuiz'],
		      		'supprimerQuiz' 					=> $_POST['supprimerQuiz'],
		      		'ajouterQuestion' 					=> $_POST['ajouterQuestion'],
		      		'modifierQuestion' 					=> $_POST['modifierQuestion'],
		      		'supprimerQuestion' 				=> $_POST['supprimerQuestion'],
		      		'ajouterReponse' 					=> $_POST['ajouterReponse'],
		      		'modifierReponse' 					=> $_POST['modifierReponse'],
		      		'supprimerReponse' 					=> $_POST['supprimerReponse'],
			        'idStatut'							=> $_GET['id']
		      	));

		      	$data = $inputs;
				$_SESSION['flash']['success'] = "Le statut a été modifié avec succès.";
			}
		}
	} else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'supprimer' && !empty($_POST)) {
		if (isset($_POST['oui'])) {
			$request = $db->prepare('SELECT * FROM statut WHERE level = :level');
			$request->prepare(array(
				'level' => $data['level'] - 1
			));
			$donnees = $request->fetch();

			// Suppression du statut
			$requete = $db->prepare("DELETE FROM statut WHERE id = :id");
			$requete->execute(array(
				'id' => $_GET['id']
			));

			$request = $db->prepare('UPDATE utilisateur SET idStatut = :nouveauIdStatut WHERE idStatut = :idStatut');
			$request->execute(array(
		        'nouveauIdStatut'	=> $donnees['id'],
		        'idStatut'			=> $_GET['id']
	      	));

			header('Location: statut.php');
			die();
		}

		if (isset($_POST['non'])) {
			header('Location: statut.php');
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
				<h2 class="header-page">Statuts</h2>
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
			$fil = array('statut.php' => 'Statuts');
		  	if (isset($_GET['action'])) {
		  		if ($_GET['action'] == 'ajouter')
		  			$fil['statut.php?action=ajouter'] = 'Ajout d\'un statut';
		  		else if ($_GET['action'] == 'modifier')
		  			$fil['statut.php?id=' . $_GET['id'] . '&action=modifier'] = 'Modification du statut : ' . $data['nom'];
		  		else if ($_GET['action'] == 'supprimer')
		  			$fil['statut.php?id=' . $_GET['id'] . '&action=supprimer'] = 'Suppression du statut : ' . $data['nom'];
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
					<h3>Ajout d'un statut</h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="statut.php?action=ajouter">
					<fieldset>
						<!-- Nom -->
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du statut</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du statut" class="form-control input-md" required="">
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
						<!-- Level -->
						<div class="form-group <?php if (isset($errors['levelUtilise']) || isset($errors['levelNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="level">Level</label>  
							<div class="col-md-5">
								<input id="level" name="level" type="text" placeholder="Level" class="form-control input-md" required="">
								<span class="help-block">
									<?php
									if (isset($errors['levelNombre']))
										echo strip_tags(htmlspecialchars($errors['levelNombre']));
									if (isset($errors['levelUtilise']))
										echo strip_tags(htmlspecialchars($errors['levelUtilise']));
									?>
								</span>
							</div>
						</div>			
						<!-- Actions possibles -->
						<div class="form-group">
							<p>Ce statut permet les actions suivantes :</p>
						</div>
						<!-- Utilisateur -->
						<div class="form-group <?php if (isset($errors['modifierUtilisateur'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierUtilisateur">Modifier les utilisateur :</label>
							<div class="col-md-5">
								<select id="modifierUtilisateur" name="modifierUtilisateur" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierUtilisateur']))
											echo strip_tags(htmlspecialchars($errors['modifierUtilisateur']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerUtilisateur'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerUtilisateur">Supprimer les utilisateur :</label>
							<div class="col-md-5">
								<select id="supprimerUtilisateur" name="supprimerUtilisateur" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerUtilisateur']))
											echo strip_tags(htmlspecialchars($errors['supprimerUtilisateur']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['ajouterParent'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterParent">Ajouter un parent :</label>
							<div class="col-md-5">
								<select id="ajouterParent" name="ajouterParent" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterParent']))
											echo strip_tags(htmlspecialchars($errors['ajouterParent']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerParent'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerParent">Supprimer un parent :</label>
							<div class="col-md-5">
								<select id="supprimerParent" name="supprimerParent" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerParent']))
											echo strip_tags(htmlspecialchars($errors['supprimerParent']));
									?>
								</span>
							</div>
						</div>
						<!-- Etablissement -->
						<div class="form-group <?php if (isset($errors['ajouterEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterEtablissement">Ajouter un établissement :</label>
							<div class="col-md-5">
								<select id="ajouterEtablissement" name="ajouterEtablissement" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterEtablissement']))
											echo strip_tags(htmlspecialchars($errors['ajouterEtablissement']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierEtablissement">Modifier un établissement :</label>
							<div class="col-md-5">
								<select id="modifierEtablissement" name="modifierEtablissement" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierEtablissement']))
											echo strip_tags(htmlspecialchars($errors['modifierEtablissement']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerEtablissement">Supprimer un établissement :</label>
							<div class="col-md-5">
								<select id="supprimerEtablissement" name="supprimerEtablissement" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerEtablissement']))
											echo strip_tags(htmlspecialchars($errors['supprimerEtablissement']));
									?>
								</span>
							</div>
						</div>
						<!-- Classe -->
						<div class="form-group <?php if (isset($errors['ajouterClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterClasse">Ajouter une classe :</label>
							<div class="col-md-5">
								<select id="ajouterClasse" name="ajouterClasse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterClasse']))
											echo strip_tags(htmlspecialchars($errors['ajouterClasse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierClasse">Modifier une classe :</label>
							<div class="col-md-5">
								<select id="modifierClasse" name="modifierClasse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierClasse']))
											echo strip_tags(htmlspecialchars($errors['modifierClasse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerClasse">Supprimer une classe :</label>
							<div class="col-md-5">
								<select id="supprimerClasse" name="supprimerClasse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerClasse']))
											echo strip_tags(htmlspecialchars($errors['supprimerClasse']));
									?>
								</span>
							</div>
						</div>
						<!-- Elève -->
						<div class="form-group <?php if (isset($errors['ajouterParrainage'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterParrainage">Ajouter un parrainage :</label>
							<div class="col-md-5">
								<select id="ajouterParrainage" name="ajouterParrainage" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterParrainage']))
											echo strip_tags(htmlspecialchars($errors['ajouterParrainage']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerParrainage'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerParrainage">Supprimer un parrainage :</label>
							<div class="col-md-5">
								<select id="supprimerParrainage" name="supprimerParrainage" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerParrainage']))
											echo strip_tags(htmlspecialchars($errors['supprimerParrainage']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['ajouterUtilisateurClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterUtilisateurClasse">Ajouter un utilisateur à une classe :</label>
							<div class="col-md-5">
								<select id="ajouterUtilisateurClasse" name="ajouterUtilisateurClasse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterUtilisateurClasse']))
											echo strip_tags(htmlspecialchars($errors['ajouterUtilisateurClasse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerUtilisateurClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerUtilisateurClasse">Supprimer un utilisateur d'une classe :</label>
							<div class="col-md-5">
								<select id="supprimerUtilisateurClasse" name="supprimerUtilisateurClasse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerUtilisateurClasse']))
											echo strip_tags(htmlspecialchars($errors['supprimerUtilisateurClasse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['ajouterUtilisateurEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterUtilisateurEtablissement">Ajouter un utilisateur à un établissement :</label>
							<div class="col-md-5">
								<select id="ajouterUtilisateurEtablissement" name="ajouterUtilisateurEtablissement" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterUtilisateurEtablissement']))
											echo strip_tags(htmlspecialchars($errors['ajouterUtilisateurEtablissement']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerUtilisateurEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerUtilisateurEtablissement">Supprimer un utilisateur d'un établissement :</label>
							<div class="col-md-5">
								<select id="supprimerUtilisateurEtablissement" name="supprimerUtilisateurEtablissement" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerUtilisateurEtablissement']))
											echo strip_tags(htmlspecialchars($errors['supprimerUtilisateurEtablissement']));
									?>
								</span>
							</div>
						</div>
						<!-- Thème -->
						<div class="form-group <?php if (isset($errors['ajouterTheme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterTheme">Ajouter un thème :</label>
							<div class="col-md-5">
								<select id="ajouterTheme" name="ajouterTheme" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterTheme']))
											echo strip_tags(htmlspecialchars($errors['ajouterTheme']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierTheme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierTheme">Modifier un thème :</label>
							<div class="col-md-5">
								<select id="modifierTheme" name="modifierTheme" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierTheme']))
											echo strip_tags(htmlspecialchars($errors['modifierTheme']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerTheme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerTheme">Supprimer un thème :</label>
							<div class="col-md-5">
								<select id="supprimerTheme" name="supprimerTheme" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerTheme']))
											echo strip_tags(htmlspecialchars($errors['supprimerTheme']));
									?>
								</span>
							</div>
						</div>
						<!-- Cours -->
						<div class="form-group <?php if (isset($errors['ajouterCours'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterCours">Ajouter un cours :</label>
							<div class="col-md-5">
								<select id="ajouterCours" name="ajouterCours" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterCours']))
											echo strip_tags(htmlspecialchars($errors['ajouterCours']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierCours'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierCours">Modifier un cours :</label>
							<div class="col-md-5">
								<select id="modifierCours" name="modifierCours" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierCours']))
											echo strip_tags(htmlspecialchars($errors['modifierCours']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerCours'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerCours">Supprimer un cours :</label>
							<div class="col-md-5">
								<select id="supprimerCours" name="supprimerCours" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerCours']))
											echo strip_tags(htmlspecialchars($errors['supprimerCours']));
									?>
								</span>
							</div>
						</div>
						<!-- Chapitre -->
						<div class="form-group <?php if (isset($errors['ajouterChapitre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterChapitre">Ajouter un chapitre :</label>
							<div class="col-md-5">
								<select id="ajouterChapitre" name="ajouterChapitre" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterChapitre']))
											echo strip_tags(htmlspecialchars($errors['ajouterChapitre']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierChapitre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierChapitre">Modifier un chapitre :</label>
							<div class="col-md-5">
								<select id="modifierChapitre" name="modifierChapitre" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierChapitre']))
											echo strip_tags(htmlspecialchars($errors['modifierChapitre']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerChapitre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerChapitre">Supprimer un chapitre :</label>
							<div class="col-md-5">
								<select id="supprimerChapitre" name="supprimerChapitre" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerChapitre']))
											echo strip_tags(htmlspecialchars($errors['supprimerChapitre']));
									?>
								</span>
							</div>
						</div>
						<!-- Niveau -->
						<div class="form-group <?php if (isset($errors['ajouterNiveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterNiveau">Ajouter un niveau :</label>
							<div class="col-md-5">
								<select id="ajouterNiveau" name="ajouterNiveau" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterNiveau']))
											echo strip_tags(htmlspecialchars($errors['ajouterNiveau']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierNiveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierNiveau">Modifier un niveau :</label>
							<div class="col-md-5">
								<select id="modifierNiveau" name="modifierNiveau" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierNiveau']))
											echo strip_tags(htmlspecialchars($errors['modifierNiveau']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerNiveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerNiveau">Supprimer un niveau :</label>
							<div class="col-md-5">
								<select id="supprimerNiveau" name="supprimerNiveau" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerNiveau']))
											echo strip_tags(htmlspecialchars($errors['supprimerNiveau']));
									?>
								</span>
							</div>
						</div>
						<!-- Statut -->
						<div class="form-group <?php if (isset($errors['ajouterStatut'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterStatut">Ajouter un statut :</label>
							<div class="col-md-5">
								<select id="ajouterStatut" name="ajouterStatut" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterStatut']))
											echo strip_tags(htmlspecialchars($errors['ajouterStatut']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierStatut'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierStatut">Modifier un statut :</label>
							<div class="col-md-5">
								<select id="modifierStatut" name="modifierStatut" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierStatut']))
											echo strip_tags(htmlspecialchars($errors['modifierStatut']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerStatut'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerStatut">Supprimer un statut :</label>
							<div class="col-md-5">
								<select id="supprimerStatut" name="supprimerStatut" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerStatut']))
											echo strip_tags(htmlspecialchars($errors['supprimerStatut']));
									?>
								</span>
							</div>
						</div>
						<!-- Quiz -->
						<div class="form-group <?php if (isset($errors['ajouterQuiz'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterQuiz">Ajouter un quiz :</label>
							<div class="col-md-5">
								<select id="ajouterQuiz" name="ajouterQuiz" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterQuiz']))
											echo strip_tags(htmlspecialchars($errors['ajouterQuiz']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierQuiz'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierQuiz">Modifier un quiz :</label>
							<div class="col-md-5">
								<select id="modifierQuiz" name="modifierQuiz" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierQuiz']))
											echo strip_tags(htmlspecialchars($errors['modifierQuiz']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerQuiz'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerQuiz">Supprimer un quiz :</label>
							<div class="col-md-5">
								<select id="supprimerQuiz" name="supprimerQuiz" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerQuiz']))
											echo strip_tags(htmlspecialchars($errors['supprimerQuiz']));
									?>
								</span>
							</div>
						</div>
						<!-- Question -->
						<div class="form-group <?php if (isset($errors['ajouterQuestion'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterQuestion">Ajouter une question :</label>
							<div class="col-md-5">
								<select id="ajouterQuestion" name="ajouterQuestion" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterQuestion']))
											echo strip_tags(htmlspecialchars($errors['ajouterQuestion']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierQuestion'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierQuestion">Modifier une question :</label>
							<div class="col-md-5">
								<select id="modifierQuestion" name="modifierQuestion" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierQuestion']))
											echo strip_tags(htmlspecialchars($errors['modifierQuestion']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerQuestion'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerQuestion">Supprimer une question :</label>
							<div class="col-md-5">
								<select id="supprimerQuestion" name="supprimerQuestion" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerQuestion']))
											echo strip_tags(htmlspecialchars($errors['supprimerQuestion']));
									?>
								</span>
							</div>
						</div>
						<!-- Réponse -->
						<div class="form-group <?php if (isset($errors['ajouterReponse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterReponse">Ajouter une réponse :</label>
							<div class="col-md-5">
								<select id="ajouterReponse" name="ajouterReponse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterReponse']))
											echo strip_tags(htmlspecialchars($errors['ajouterReponse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierReponse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierReponse">Modifier une réponse :</label>
							<div class="col-md-5">
								<select id="modifierReponse" name="modifierReponse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierReponse']))
											echo strip_tags(htmlspecialchars($errors['modifierReponse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerReponse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerReponse">Supprimer une réponse :</label>
							<div class="col-md-5">
								<select id="supprimerReponse" name="supprimerReponse" class="form-control">
									<option value="1">Oui</option>';
									<option value="0">Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerReponse']))
											echo strip_tags(htmlspecialchars($errors['supprimerReponse']));
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
					<h3>Modification du statut : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
				</div>
			</div>
			<div class="row">
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="statut.php?id=<?php echo $data['id']; ?>&action=modifier">
					<fieldset>
						<!-- Nom -->
						<div class="form-group <?php if (isset($errors['tailleNom']) || isset($errors['nomNombre']) || isset($errors['nomUtilise']) || isset($errors['nom'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="nom">Nom du statut</label>  
							<div class="col-md-5">
								<input id="nom" name="nom" type="text" placeholder="Nom du statut" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['nom'])); ?>">
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
						<!-- Level -->
						<div class="form-group <?php if (isset($errors['levelUtilise']) || isset($errors['levelNombre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="level">Level</label>  
							<div class="col-md-5">
								<input id="level" name="level" type="text" placeholder="Level" class="form-control input-md" value="<?php echo strip_tags(htmlspecialchars($data['level'])); ?>">
								<span class="help-block">
									<?php
									if (isset($errors['levelNombre']))
										echo strip_tags(htmlspecialchars($errors['levelNombre']));
									if (isset($errors['levelUtilise']))
										echo strip_tags(htmlspecialchars($errors['levelUtilise']));
									?>
								</span>
							</div>
						</div>
						<!-- Actions possibles -->
						<div class="form-group">
							<p>Ce statut permet les actions suivantes :</p>
						</div>
						<!-- Utilisateur -->
						<div class="form-group <?php if (isset($errors['modifierUtilisateur'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierUtilisateur">Modifier les utilisateur :</label>
							<div class="col-md-5">
								<select id="modifierUtilisateur" name="modifierUtilisateur" class="form-control">
									<option value="1" <?php if ($data['modifierUtilisateur'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierUtilisateur'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierUtilisateur']))
											echo strip_tags(htmlspecialchars($errors['modifierUtilisateur']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerUtilisateur'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerUtilisateur">Supprimer les utilisateur :</label>
							<div class="col-md-5">
								<select id="supprimerUtilisateur" name="supprimerUtilisateur" class="form-control">
									<option value="1"<?php if ($data['supprimerUtilisateur'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerUtilisateur'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerUtilisateur']))
											echo strip_tags(htmlspecialchars($errors['supprimerUtilisateur']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['ajouterParent'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterParent">Ajouter un parent :</label>
							<div class="col-md-5">
								<select id="ajouterParent" name="ajouterParent" class="form-control">
									<option value="1" <?php if ($data['ajouterParent'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterParent'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterParent']))
											echo strip_tags(htmlspecialchars($errors['ajouterParent']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerParent'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerParent">Supprimer un parent :</label>
							<div class="col-md-5">
								<select id="supprimerParent" name="supprimerParent" class="form-control">
									<option value="1" <?php if ($data['supprimerParent'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerParent'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerParent']))
											echo strip_tags(htmlspecialchars($errors['supprimerParent']));
									?>
								</span>
							</div>
						</div>
						<!-- Etablissement -->
						<div class="form-group <?php if (isset($errors['ajouterEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterEtablissement">Ajouter un établissement :</label>
							<div class="col-md-5">
								<select id="ajouterEtablissement" name="ajouterEtablissement" class="form-control">
									<option value="1"<?php if ($data['ajouterEtablissement'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterEtablissement'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterEtablissement']))
											echo strip_tags(htmlspecialchars($errors['ajouterEtablissement']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierEtablissement">Modifier un établissement :</label>
							<div class="col-md-5">
								<select id="modifierEtablissement" name="modifierEtablissement" class="form-control">
									<option value="1"<?php if ($data['modifierEtablissement'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierEtablissement'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierEtablissement']))
											echo strip_tags(htmlspecialchars($errors['modifierEtablissement']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerEtablissement">Supprimer un établissement :</label>
							<div class="col-md-5">
								<select id="supprimerEtablissement" name="supprimerEtablissement" class="form-control">
									<option value="1"<?php if ($data['supprimerEtablissement'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerEtablissement'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerEtablissement']))
											echo strip_tags(htmlspecialchars($errors['supprimerEtablissement']));
									?>
								</span>
							</div>
						</div>
						<!-- Classe -->
						<div class="form-group <?php if (isset($errors['ajouterClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterClasse">Ajouter une classe :</label>
							<div class="col-md-5">
								<select id="ajouterClasse" name="ajouterClasse" class="form-control">
									<option value="1"<?php if ($data['ajouterClasse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterClasse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterClasse']))
											echo strip_tags(htmlspecialchars($errors['ajouterClasse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierClasse">Modifier une classe :</label>
							<div class="col-md-5">
								<select id="modifierClasse" name="modifierClasse" class="form-control">
									<option value="1"<?php if ($data['modifierClasse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierClasse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierClasse']))
											echo strip_tags(htmlspecialchars($errors['modifierClasse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerClasse">Supprimer une classe :</label>
							<div class="col-md-5">
								<select id="supprimerClasse" name="supprimerClasse" class="form-control">
									<option value="1"<?php if ($data['supprimerClasse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerClasse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerClasse']))
											echo strip_tags(htmlspecialchars($errors['supprimerClasse']));
									?>
								</span>
							</div>
						</div>
						<!-- Elève -->
						<div class="form-group <?php if (isset($errors['ajouterParrainage'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterParrainage">Ajouter un parrainage :</label>
							<div class="col-md-5">
								<select id="ajouterParrainage" name="ajouterParrainage" class="form-control">
									<option value="1" <?php if ($data['ajouterParrainage'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterParrainage'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterParrainage']))
											echo strip_tags(htmlspecialchars($errors['ajouterParrainage']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerParrainage'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerParrainage">Supprimer un parrainage :</label>
							<div class="col-md-5">
								<select id="supprimerParrainage" name="supprimerParrainage" class="form-control">
									<option value="1" <?php if ($data['supprimerParrainage'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerParrainage'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerParrainage']))
											echo strip_tags(htmlspecialchars($errors['supprimerParrainage']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['ajouterUtilisateurClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterUtilisateurClasse">Ajouter un utilisateur à une classe :</label>
							<div class="col-md-5">
								<select id="ajouterUtilisateurClasse" name="ajouterUtilisateurClasse" class="form-control">
									<option value="1"<?php if ($data['ajouterUtilisateurClasse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterUtilisateurClasse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterUtilisateurClasse']))
											echo strip_tags(htmlspecialchars($errors['ajouterUtilisateurClasse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerUtilisateurClasse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerUtilisateurClasse">Supprimer un utilisateur d'une classe :</label>
							<div class="col-md-5">
								<select id="supprimerUtilisateurClasse" name="supprimerUtilisateurClasse" class="form-control">
									<option value="1"<?php if ($data['supprimerUtilisateurClasse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerUtilisateurClasse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerUtilisateurClasse']))
											echo strip_tags(htmlspecialchars($errors['supprimerUtilisateurClasse']));
									?>
								</span>
							</div>
						</div>
						<!-- Professeur -->
						<div class="form-group <?php if (isset($errors['ajouterUtilisateurEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterUtilisateurEtablissement">Ajouter un utilisateur à un établissement :</label>
							<div class="col-md-5">
								<select id="ajouterUtilisateurEtablissement" name="ajouterUtilisateurEtablissement" class="form-control">
									<option value="1"<?php if ($data['ajouterUtilisateurEtablissement'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterUtilisateurEtablissement'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterUtilisateurEtablissement']))
											echo strip_tags(htmlspecialchars($errors['ajouterUtilisateurEtablissement']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerUtilisateurEtablissement'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerUtilisateurEtablissement">Supprimer un utilisateur d'un établissement :</label>
							<div class="col-md-5">
								<select id="supprimerUtilisateurEtablissement" name="supprimerUtilisateurEtablissement" class="form-control">
									<option value="1"<?php if ($data['supprimerUtilisateurEtablissement'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerUtilisateurEtablissement'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerUtilisateurEtablissement']))
											echo strip_tags(htmlspecialchars($errors['supprimerUtilisateurEtablissement']));
									?>
								</span>
							</div>
						</div>
						<!-- Thème -->
						<div class="form-group <?php if (isset($errors['ajouterTheme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterTheme">Ajouter un thème :</label>
							<div class="col-md-5">
								<select id="ajouterTheme" name="ajouterTheme" class="form-control">
									<option value="1"<?php if ($data['ajouterTheme'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterTheme'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterTheme']))
											echo strip_tags(htmlspecialchars($errors['ajouterTheme']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierTheme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierTheme">Modifier un thème :</label>
							<div class="col-md-5">
								<select id="modifierTheme" name="modifierTheme" class="form-control">
									<option value="1"<?php if ($data['modifierTheme'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierTheme'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierTheme']))
											echo strip_tags(htmlspecialchars($errors['modifierTheme']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerTheme'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerTheme">Supprimer un thème :</label>
							<div class="col-md-5">
								<select id="supprimerTheme" name="supprimerTheme" class="form-control">
									<option value="1"<?php if ($data['supprimerTheme'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerTheme'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerTheme']))
											echo strip_tags(htmlspecialchars($errors['supprimerTheme']));
									?>
								</span>
							</div>
						</div>
						<!-- Cours -->
						<div class="form-group <?php if (isset($errors['ajouterCours'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterCours">Ajouter un cours :</label>
							<div class="col-md-5">
								<select id="ajouterCours" name="ajouterCours" class="form-control">
									<option value="1"<?php if ($data['ajouterCours'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterCours'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterCours']))
											echo strip_tags(htmlspecialchars($errors['ajouterCours']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierCours'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierCours">Modifier un cours :</label>
							<div class="col-md-5">
								<select id="modifierCours" name="modifierCours" class="form-control">
									<option value="1"<?php if ($data['modifierCours'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierCours'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierCours']))
											echo strip_tags(htmlspecialchars($errors['modifierCours']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerCours'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerCours">Supprimer un cours :</label>
							<div class="col-md-5">
								<select id="supprimerCours" name="supprimerCours" class="form-control">
									<option value="1"<?php if ($data['supprimerCours'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerCours'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerCours']))
											echo strip_tags(htmlspecialchars($errors['supprimerCours']));
									?>
								</span>
							</div>
						</div>
						<!-- Chapitre -->
						<div class="form-group <?php if (isset($errors['ajouterChapitre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterChapitre">Ajouter un chapitre :</label>
							<div class="col-md-5">
								<select id="ajouterChapitre" name="ajouterChapitre" class="form-control">
									<option value="1"<?php if ($data['ajouterChapitre'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterChapitre'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterChapitre']))
											echo strip_tags(htmlspecialchars($errors['ajouterChapitre']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierChapitre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierChapitre">Modifier un chapitre :</label>
							<div class="col-md-5">
								<select id="modifierChapitre" name="modifierChapitre" class="form-control">
									<option value="1"<?php if ($data['modifierChapitre'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierChapitre'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierChapitre']))
											echo strip_tags(htmlspecialchars($errors['modifierChapitre']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerChapitre'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerChapitre">Supprimer un chapitre :</label>
							<div class="col-md-5">
								<select id="supprimerChapitre" name="supprimerChapitre" class="form-control">
									<option value="1"<?php if ($data['supprimerChapitre'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerChapitre'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerChapitre']))
											echo strip_tags(htmlspecialchars($errors['supprimerChapitre']));
									?>
								</span>
							</div>
						</div>
						<!-- Niveau -->
						<div class="form-group <?php if (isset($errors['ajouterNiveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterNiveau">Ajouter un niveau :</label>
							<div class="col-md-5">
								<select id="ajouterNiveau" name="ajouterNiveau" class="form-control">
									<option value="1"<?php if ($data['ajouterNiveau'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterNiveau'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterNiveau']))
											echo strip_tags(htmlspecialchars($errors['ajouterNiveau']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierNiveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierNiveau">Modifier un niveau :</label>
							<div class="col-md-5">
								<select id="modifierNiveau" name="modifierNiveau" class="form-control">
									<option value="1"<?php if ($data['modifierNiveau'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierNiveau'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierNiveau']))
											echo strip_tags(htmlspecialchars($errors['modifierNiveau']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerNiveau'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerNiveau">Supprimer un niveau :</label>
							<div class="col-md-5">
								<select id="supprimerNiveau" name="supprimerNiveau" class="form-control">
									<option value="1"<?php if ($data['supprimerNiveau'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerNiveau'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerNiveau']))
											echo strip_tags(htmlspecialchars($errors['supprimerNiveau']));
									?>
								</span>
							</div>
						</div>
						<!-- Statut -->
						<div class="form-group <?php if (isset($errors['ajouterStatut'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterStatut">Ajouter un statut :</label>
							<div class="col-md-5">
								<select id="ajouterStatut" name="ajouterStatut" class="form-control">
									<option value="1"<?php if ($data['ajouterStatut'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterStatut'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterStatut']))
											echo strip_tags(htmlspecialchars($errors['ajouterStatut']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierStatut'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierStatut">Modifier un statut :</label>
							<div class="col-md-5">
								<select id="modifierStatut" name="modifierStatut" class="form-control">
									<option value="1"<?php if ($data['modifierStatut'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierStatut'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierStatut']))
											echo strip_tags(htmlspecialchars($errors['modifierStatut']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerStatut'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerStatut">Supprimer un statut :</label>
							<div class="col-md-5">
								<select id="supprimerStatut" name="supprimerStatut" class="form-control">
									<option value="1"<?php if ($data['supprimerStatut'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerStatut'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerStatut']))
											echo strip_tags(htmlspecialchars($errors['supprimerStatut']));
									?>
								</span>
							</div>
						</div>
						<!-- Quiz -->
						<div class="form-group <?php if (isset($errors['ajouterQuiz'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterQuiz">Ajouter un quiz :</label>
							<div class="col-md-5">
								<select id="ajouterQuiz" name="ajouterQuiz" class="form-control">
									<option value="1"<?php if ($data['ajouterQuiz'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterQuiz'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterQuiz']))
											echo strip_tags(htmlspecialchars($errors['ajouterQuiz']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierQuiz'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierQuiz">Modifier un quiz :</label>
							<div class="col-md-5">
								<select id="modifierQuiz" name="modifierQuiz" class="form-control">
									<option value="1"<?php if ($data['modifierQuiz'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierQuiz'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierQuiz']))
											echo strip_tags(htmlspecialchars($errors['modifierQuiz']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerQuiz'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerQuiz">Supprimer un quiz :</label>
							<div class="col-md-5">
								<select id="supprimerQuiz" name="supprimerQuiz" class="form-control">
									<option value="1"<?php if ($data['supprimerQuiz'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerQuiz'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerQuiz']))
											echo strip_tags(htmlspecialchars($errors['supprimerQuiz']));
									?>
								</span>
							</div>
						</div>
						<!-- Question -->
						<div class="form-group <?php if (isset($errors['ajouterQuestion'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterQuestion">Ajouter une question :</label>
							<div class="col-md-5">
								<select id="ajouterQuestion" name="ajouterQuestion" class="form-control">
									<option value="1"<?php if ($data['ajouterQuestion'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterQuestion'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterQuestion']))
											echo strip_tags(htmlspecialchars($errors['ajouterQuestion']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierQuestion'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierQuestion">Modifier une question :</label>
							<div class="col-md-5">
								<select id="modifierQuestion" name="modifierQuestion" class="form-control">
									<option value="1"<?php if ($data['modifierQuestion'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierQuestion'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierQuestion']))
											echo strip_tags(htmlspecialchars($errors['modifierQuestion']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerQuestion'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerQuestion">Supprimer une question :</label>
							<div class="col-md-5">
								<select id="supprimerQuestion" name="supprimerQuestion" class="form-control">
									<option value="1"<?php if ($data['supprimerQuestion'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerQuestion'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerQuestion']))
											echo strip_tags(htmlspecialchars($errors['supprimerQuestion']));
									?>
								</span>
							</div>
						</div>
						<!-- Réponse -->
						<div class="form-group <?php if (isset($errors['ajouterReponse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="ajouterReponse">Ajouter une réponse :</label>
							<div class="col-md-5">
								<select id="ajouterReponse" name="ajouterReponse" class="form-control">
									<option value="1"<?php if ($data['ajouterReponse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['ajouterReponse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['ajouterReponse']))
											echo strip_tags(htmlspecialchars($errors['ajouterReponse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['modifierReponse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="modifierReponse">Modifier une réponse :</label>
							<div class="col-md-5">
								<select id="modifierReponse" name="modifierReponse" class="form-control">
									<option value="1"<?php if ($data['modifierReponse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['modifierReponse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['modifierReponse']))
											echo strip_tags(htmlspecialchars($errors['modifierReponse']));
									?>
								</span>
							</div>
						</div>
						<div class="form-group <?php if (isset($errors['supprimerReponse'])) echo "has-error"; ?>">
							<label class="col-md-4 control-label" for="supprimerReponse">Supprimer une réponse :</label>
							<div class="col-md-5">
								<select id="supprimerReponse" name="supprimerReponse" class="form-control">
									<option value="1"<?php if ($data['supprimerReponse'] == 1) echo 'selected="selected"'; ?>>Oui</option>';
									<option value="0"<?php if ($data['supprimerReponse'] == 0) echo 'selected="selected"'; ?>>Non</option>';
								</select>
								<span class="help-block">
									<?php
										if (isset($errors['supprimerReponse']))
											echo strip_tags(htmlspecialchars($errors['supprimerReponse']));
									?>
								</span>
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
					<h3>Suppression du statut : <?php echo strip_tags(htmlspecialchars($data['nom'])); ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<p>La suppression d'un statut entraine le changement de statut des utilisateurs !</p>
				<p>Êtes-vous sûr de vouloir supprimer ce statut ?</p>
				<form enctype="multipart/form-data" class="form-horizontal" method="post" action="statut.php?id=<?php echo $data['id']; ?>&action=supprimer">
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
		} else {
		?>
			<div class="row">
				<div class="col-lg-12 page-header">
					<h3>Gestion des statuts <?php if (connecte() && $acces['ajouterStatut'] == 1) echo '<a href="statut.php?action=ajouter" class="btn btn-primary pull-right">Ajouter un statut</a>'; ?></h3>
				</div>
			</div>
			<div class="row">
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<h3>Niveaux :</h3>
				<ul>
					<?php
					$request = $db->query('SELECT * FROM statut ORDER BY level');
					while ($donnees = $request->fetch()) {
						echo '<li>' . strip_tags(htmlspecialchars($donnees['level'])) . ' : ' . strip_tags(htmlspecialchars($donnees['nom'])) . ' ';
						if (connecte() && $acces['modifierNiveau'] == 1)
							echo ' <a href="statut.php?id=' . $donnees['id'] . '&action=modifier"><span class="glyphicon glyphicon-pencil"></span></a> ';
						if (connecte() && $acces['supprimerNiveau'] == 1)
							echo ' <a href="statut.php?id=' . $donnees['id'] . '&action=supprimer"><span class="glyphicon glyphicon-remove"></span></a> ';

						echo '</li>';
					}
					?>
				</ul>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
				tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
				quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
				consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
				cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
				proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			</div>
		<?php
		}
		?>
	</div>
</div>
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>