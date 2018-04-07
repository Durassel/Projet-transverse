<?php
/* Connection to the database */
include_once "includes/db.php";

/* Clear tables */
$db->query("DROP TABLE action");
$db->query("DROP TABLE chapitre");
$db->query("DROP TABLE classe");
$db->query("DROP TABLE cours");
$db->query("DROP TABLE etablissement");
$db->query("DROP TABLE niveau");
$db->query("DROP TABLE parrainage");
$db->query("DROP TABLE affiliation");
$db->query("DROP TABLE question");
$db->query("DROP TABLE quiz");
$db->query("DROP TABLE reponse");
$db->query("DROP TABLE resultat");
$db->query("DROP TABLE statut");
$db->query("DROP TABLE theme");
$db->query("DROP TABLE utilisateur");

$db->query("CREATE TABLE IF NOT EXISTS `action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idStatut` int(11) NOT NULL,
  `modifierUtilisateur` int(2) NOT NULL,
  `supprimerUtilisateur` int(2) NOT NULL,
  `ajouterParent` int(2) NOT NULL,
  `supprimerParent` int(2) NOT NULL,
  `ajouterEtablissement` int(2) NOT NULL,
  `modifierEtablissement` int(2) NOT NULL,
  `supprimerEtablissement` int(2) NOT NULL,
  `ajouterClasse` int(2) NOT NULL,
  `modifierClasse` int(2) NOT NULL,
  `supprimerClasse` int(2) NOT NULL,
  `ajouterParrainage` int(2) NOT NULL,
  `supprimerParrainage` int(2) NOT NULL,
  `ajouterUtilisateurClasse` int(2) NOT NULL,
  `supprimerUtilisateurClasse` int(2) NOT NULL,
  `ajouterUtilisateurEtablissement` int(2) NOT NULL,
  `supprimerUtilisateurEtablissement` int(2) NOT NULL,
  `ajouterTheme` int(2) NOT NULL,
  `modifierTheme` int(2) NOT NULL,
  `supprimerTheme` int(2) NOT NULL,
  `ajouterCours` int(2) NOT NULL,
  `modifierCours` int(2) NOT NULL,
  `supprimerCours` int(2) NOT NULL,
  `ajouterChapitre` int(2) NOT NULL,
  `modifierChapitre` int(2) NOT NULL,
  `supprimerChapitre` int(2) NOT NULL,
  `ajouterNiveau` int(2) NOT NULL,
  `modifierNiveau` int(2) NOT NULL,
  `supprimerNiveau` int(2) NOT NULL,
  `ajouterStatut` int(2) NOT NULL,
  `modifierStatut` int(2) NOT NULL,
  `supprimerStatut` int(2) NOT NULL,
  `ajouterQuiz` int(2) NOT NULL,
  `modifierQuiz` int(2) NOT NULL,
  `supprimerQuiz` int(2) NOT NULL,
  `ajouterQuestion` int(2) NOT NULL,
  `modifierQuestion` int(2) NOT NULL,
  `supprimerQuestion` int(2) NOT NULL,
  `ajouterReponse` int(2) NOT NULL,
  `modifierReponse` int(2) NOT NULL,
  `supprimerReponse` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

$db->query("CREATE TABLE IF NOT EXISTS `chapitre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCours` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `texte` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `classe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `annee` year(4) NOT NULL,
  `idEtablissement` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `cours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTheme` int(11) NOT NULL,
  `idNiveau` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `etablissement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `ville` varchar(255) NOT NULL,
  `codePostal` int(11) NOT NULL,
  `idDirecteur` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `parrainage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idParrain` int(11) NOT NULL,
  `idFilleul` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `affiliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idClasse` int(11) NOT NULL,
  `idEtablissement` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idQuiz` int(11) NOT NULL,
  `question` text NOT NULL,
  `idReponse` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `idProfesseur` int(11) NOT NULL,
  `idTheme` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `reponse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idQuestion` int(11) NOT NULL,
  `reponse` text NOT NULL,
  `complement` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `resultat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUtilisateur` int(11) NOT NULL,
  `idQuiz` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `statut` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `niveau` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `niveau` varchar(255) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

$db->query("CREATE TABLE IF NOT EXISTS `theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prenom` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `idStatut` int(11) NOT NULL,
  `idParent` int(11) NOT NULL,
  `validationkey` varchar(60) NOT NULL,
  `active` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");

$db->query("ALTER TABLE action AUTO_INCREMENT = 1");
$db->query("ALTER TABLE chapitre AUTO_INCREMENT = 1");
$db->query("ALTER TABLE classe AUTO_INCREMENT = 1");
$db->query("ALTER TABLE cours AUTO_INCREMENT = 1");
$db->query("ALTER TABLE etablissement AUTO_INCREMENT = 1");
$db->query("ALTER TABLE messages AUTO_INCREMENT = 1");
$db->query("ALTER TABLE parrainage AUTO_INCREMENT = 1");
$db->query("ALTER TABLE affiliation AUTO_INCREMENT = 1");
$db->query("ALTER TABLE question AUTO_INCREMENT = 1");
$db->query("ALTER TABLE quiz AUTO_INCREMENT = 1");
$db->query("ALTER TABLE reponse AUTO_INCREMENT = 1");
$db->query("ALTER TABLE resultat AUTO_INCREMENT = 1");
$db->query("ALTER TABLE statut AUTO_INCREMENT = 1");
$db->query("ALTER TABLE theme AUTO_INCREMENT = 1");
$db->query("ALTER TABLE utilisateur AUTO_INCREMENT = 1");

$db->query("INSERT INTO `projet_transverse`.`statut` (`id`, `nom`, `level`) VALUES (NULL, 'élève', '1'), (NULL, 'parent', '2'), (NULL, 'professeur', '3'), (NULL, 'directeur', '4'), (NULL, 'administrateur', '5');");

$db->query("INSERT INTO `projet_transverse`.`action` (`id`, `idStatut`, `modifierUtilisateur`, `supprimerUtilisateur`, `ajouterParent`, `supprimerParent`, `ajouterEtablissement`,
  `modifierEtablissement`, `supprimerEtablissement`, `ajouterClasse`, `modifierClasse`, `supprimerClasse`, `ajouterParrainage`, `supprimerParrainage`, `ajouterUtilisateurClasse`,
  `supprimerUtilisateurClasse`, `ajouterUtilisateurEtablissement`, `supprimerUtilisateurEtablissement`, `ajouterTheme`, `modifierTheme`, `supprimerTheme`, `ajouterCours`, `modifierCours`,
  `supprimerCours`, `ajouterChapitre`, `modifierChapitre`, `supprimerChapitre`, `ajouterNiveau`, `modifierNiveau`, `supprimerNiveau`, `ajouterStatut`, `modifierStatut`, `supprimerStatut`,
  `ajouterQuiz`, `modifierQuiz`, `supprimerQuiz`, `ajouterQuestion`, `modifierQuestion`, `supprimerQuestion`, `ajouterReponse`, `modifierReponse`, `supprimerReponse`) VALUES 
(NULL, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(NULL, 2, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(NULL, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(NULL, 4, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(NULL, 5, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);");

$db->query("INSERT INTO `projet_transverse`.`theme` (`id`, `nom`) VALUES (NULL, 'Mathématiques'), (NULL, 'Physique'), (NULL, 'Chimie'), (NULL, 'SVT'), (NULL, 'Histoire'), (NULL, 'Géographie');");

$db->query("INSERT INTO `projet_transverse`.`niveau` (`id`, `niveau`, `level`) VALUES (NULL, 'Facile', '1'), (NULL, 'Moyen', '2'), (NULL, 'Difficile', '3');");