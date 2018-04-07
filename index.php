<?php
	$title = "Accueil";
	$css = "<!-- Index CSS --><link rel=\"stylesheet\" href=\"css/index.css\"><!-- Icons CSS --><link rel=\"stylesheet\" href=\"css/icons.css\">";
	$js = "<script src=\"js/index.js\"></script>";

	include_once "includes/db.php";
	include_once "includes/functions.php";
	include_once "includes/header.php";
?>

<!-- Caroussel -->
<div id="carousel" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
		<li data-target="#carousel" data-slide-to="0" class="active"></li>
		<li data-target="#carousel" data-slide-to="1"></li>
		<li data-target="#carousel" data-slide-to="2"></li>
	</ol>
	<div class="carousel-inner">
		<div class="item active"> 
			<img alt="" src="img/background/img1.jpg" class="responsive">
			<div class="carousel-caption">
				<div class="full-width text-center">
					<h1 class="text-uppercase">Entraîne-toi pour progresser !</h1>
					<h3>Défiez vos amis dans la joie et la connaissance !</h3>
				</div>
			</div>
		</div>
		<div class="item"> 
			<img alt="" src="img/background/img5.jpg" class="responsive">
			<div class="carousel-caption">
				<div class="full-width text-center">
					<h1 class="text-uppercase">Apprenez sur des thèmes divers et variés !</h1>
					<h3>De nombreux quiz sont disponibles pour tout type de niveaux !</h3>
				</div>
			</div>
		</div>
		<div class="item">
			<img alt="" src="img/background/img6.jpg" class="responsive">
			<div class="carousel-caption">
				<div class="full-width text-center">
					<h1 class="text-uppercase">Disponible pour tous et partout !</h1>
					<h3>Retrouvez Brain'squiz sur tous les supports possibles !</h3>
				</div>
			</div>
		</div>
	</div>
	<a class="left carousel-control" href="#carousel" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</a>
	<a class="right carousel-control" href="#carousel" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</a>
</div>
<!-- End Caroussel -->

<!-- Breadcrumb -->
<div class="container-fluid background-lightgrey">
	<div class="container">
		<div class="row">
			<?php
			$fil = array();
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
		<div class="col-lg-12 page-header">
			<h3>L'éducation, la priorité de tous</h3>
		</div>
	</div>
	<div class="row text-center">
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 services">
			<span class="glyphicon glyphicon-phone index-icon"></span>
			<h4>Utilisez Brain'squiz partout !</h4>
			<p>Depuis votre ordinateur, votre tablette ou votre smartphone, accédez facilement à tous nos quiz !</p>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 services">
			<span class="glyphicon glyphicon-user index-icon"></span>
			<h4>Affrontez vos amis !</h4>
			<p>Que ce soit en classe ou ailleurs, défiez vos amis sur les thèmes que vous souhaitez !</p>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 services">
			<span class="glyphicon glyphicon-education index-icon"></span>
			<h4>Apprenez et partagez la connaissance !</h4>
			<p>Seul ou à plusieurs, découvrez toujours plus de choses sur l'univers qui nous entoure !</p>
		</div>
	</div>
</div>

<div class="container-fluid background-lightgrey">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 page-header">
				<h3>Des thèmes divers et variés</h3>
			</div>
		</div>
		<div class="row text-center">
			<?php
			$request = $db->query("SELECT * FROM theme");
			while ($data = $request->fetch()) {
				echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 miniature">
					<div class="thumbnail">
						<a href="theme.php?id=' . $data['id'] . '">
							<img src="img/themes/' . $data['id'] . '.jpg" alt="' . strip_tags(htmlspecialchars($data['nom'])) . '" class="img-responsive">
							<p>' . strip_tags(htmlspecialchars($data['nom'])) . '</p>
						</a>
					</div>
				</div>';
			}
			?>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-lg-12 page-header">
			<h3>Pour tous les niveaux</h3>
		</div>
	</div>
	<div class="row text-center">
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 miniature">
			<div class="thumbnail">
				<img src="img/icones/primaire.jpg" alt="Primaire" class="img-responsive">
				<p>Primaire</p>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 miniature">
			<div class="thumbnail">
				<img src="img/icones/college.jpg" alt="Collège" class="img-responsive">
				<p>Collège</p>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 miniature">
			<div class="thumbnail">
				<img src="img/icones/lycee.jpg" alt="Lycée" class="img-responsive">
				<p>Lycée</p>
			</div>
		</div>
	</div>
</div>    
<!-- End Content -->

<?php include_once "includes/footer.php"; ?>