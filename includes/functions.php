<?php
include_once "includes/db.php";

function flash() {
	if (isset($_SESSION['flash'])) {
		foreach ($_SESSION['flash'] as $type => $message) {
			echo '<div class="alert alert-' . $type . '" role="alert">' . $message . '</div>';
		}
		unset($_SESSION['flash']);
	}
}


function connecte() {
	if (isset($_SESSION['auth'])) {
		return true;
	} else {
		return false;
	}
}

function statut($nom) {
	if (!connecte()) {
		return false;
	}

	if ($_SESSION['auth']['statut'] == $nom) {
		return true;
	} else {
		return false;
	}
}

function pictureExist($folder, $name) {
	if (file_exists('img/' . $folder . '/' . $name)) {
		return true;
	} else {
		return false;
	}
}

// Short description
function trunc($description, $max_words)
{
   $phrase_array = explode(' ',$description);
   if(count($phrase_array) > $max_words && $max_words > 0)
      $description = implode(' ',array_slice($phrase_array, 0, $max_words)).'...'; 
   return $description;
}

function str_to_noaccent($str)
{
    $url = $str;
    $url = preg_replace('#Ç#', 'C', $url);
    $url = preg_replace('#ç#', 'c', $url);
    $url = preg_replace('#è|é|ê|ë#', 'e', $url);
    $url = preg_replace('#È|É|Ê|Ë#', 'E', $url);
    $url = preg_replace('#à|á|â|ã|ä|å#', 'a', $url);
    $url = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $url);
    $url = preg_replace('#ì|í|î|ï#', 'i', $url);
    $url = preg_replace('#Ì|Í|Î|Ï#', 'I', $url);
    $url = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $url);
    $url = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $url);
    $url = preg_replace('#ù|ú|û|ü#', 'u', $url);
    $url = preg_replace('#Ù|Ú|Û|Ü#', 'U', $url);
    $url = preg_replace('#ý|ÿ#', 'y', $url);
    $url = preg_replace('#Ý#', 'Y', $url);
     
    return ($url);
}

function fil_ariane($array_fil) {
	$fil = '<ol class="breadcrumb">';
    $fil .= '<li><a href="index">Accueil</a></li>';

    $last_key = end($array_fil);
    foreach($array_fil as $url => $lien) {
        $fil .= ' ';
        if($url == 'final') {
            $fil .= strip_tags(htmlspecialchars($lien));
            break;
        }

        if($lien === $last_key)
        	$fil .= '<li class="active">' . strip_tags(htmlspecialchars($lien)) . '</li>';
        else
        	$fil .= '<li><a href="' . $url . '">' . strip_tags(htmlspecialchars($lien)) . '</a></li>';
    }
    $fil .= '</ol>';
    echo $fil;
}
?>