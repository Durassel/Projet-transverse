<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Charset Meta Tag -->
    <meta charset="utf-8">
    <!-- IE Edge Meta Tag -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Title -->
    <title><?php if(isset($title)) { echo $title; } else { echo 'Brain\'squiz'; }?></title>
    <!-- Other Meta Tag -->
    <meta name="description" content="">
    <link rel="icon" href="img/favicon/favicon.ico">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <!-- Minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
    <!-- Font awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Default CSS -->
    <link rel="stylesheet" href="css/default.css">
    <?php if(isset($css)) { echo $css; }?>

    <!-- Optional IE8 Support -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="main">
      <!-- Navigation -->
      <nav class="navbar navbar-fixed-top" id="navigation" role="navigation">
        <div class="container">
          <div class="row">
            <div class="navbar-header">   
              <!-- Hamburger Button Responsive -->
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="glyphicon glyphicon-menu-hamburger"></span>
              </button>
              <!-- End Hamburger Button Responsive -->
              <!-- Logo -->
              <a class="navbar-brand" href="index.php">
                <div class="logo">
                  <img src="img/logo.png" alt="Logo Brain'squiz" class="responsive">
                </div>
              </a>
              <!-- End Logo -->
            </div>
            <!-- Nav -->
            <div class="collapse navbar-collapse">
              <!-- List -->
              <ul class="nav navbar-nav">
                <li> <a href="theme">Cours</a> </li>
                <li> <a href="quiz">Quiz</a> </li>
                <li> <a href="etablissement">Ecoles</a> </li>
                <li> <a href="classe">Classes</a> </li>
              </ul>
              <!-- End List -->
              <?php
              if (connecte()) {
              ?>
                <!-- Login user -->
                <ul class="nav navbar-nav navbar-right">
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <strong><?php echo $_SESSION['auth']['prenom'] . ' ' . $_SESSION['auth']['nom']; ?></strong>
                      <span class="glyphicon glyphicon-chevron-down"></span>
                    </a>
                    <ul class="dropdown-menu">
                      <li>
                        <div class="navbar-login">
                          <div class="row">
                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                              <p class="text-center">
                                <?php
                                if (pictureExist("avatars", $_SESSION['auth']['id'] . '.jpg'))
                                  echo '<img src="img/avatars/' . $_SESSION['auth']['id'] . '.jpg" alt="" class="img-responsive">';
                                else if (pictureExist("avatars", $_SESSION['auth']['id'] . '.jpeg'))
                                  echo '<img src="img/avatars/' . $_SESSION['auth']['id'] . '.jpeg" alt="" class="img-responsive">';
                                else if (pictureExist("avatars", $_SESSION['auth']['id'] . '.png'))
                                  echo '<img src="img/avatars/' . $_SESSION['auth']['id'] . '.png" alt="" class="img-responsive">';
                                else if (pictureExist("avatars", $_SESSION['auth']['id'] . '.gif'))
                                  echo '<img src="img/avatars/' . $_SESSION['auth']['id'] . '.gif" alt="" class="img-responsive">';
                                else
                                  echo '<img src="img/avatars/default.jpg" alt="" class="img-responsive">';
                                ?>
                              </p>
                            </div>
                            <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                              <p class="text-left"><strong><?php echo $_SESSION['auth']['prenom'] . ' ' . $_SESSION['auth']['nom']; ?></strong></p>
                              <p class="text-left small"><?php echo $_SESSION['auth']['email']; ?></p>
                              <p class="text-left">
                                <a href="profil-<?php echo $_SESSION['auth']['id']; ?>" class="btn btn-primary btn-block btn-sm">Profil</a>
                              </p>
                            </div>
                          </div>
                        </div>
                      </li>
                      <li class="divider navbar-login-session-bg"></li>
                      <?php if (!statut('élève')) { ?>
                        <li><a href="dashboard">Tableau de bord <span class="pull-right glyphicon glyphicon-blackboard"></span></a></li>
                        <li class="divider"></li>
                      <?php } ?>
                      <li><a href="parametres-<?php echo $_SESSION['auth']['id']; ?>">Paramètres <span class="glyphicon glyphicon-cog pull-right"></span></a></li>
                      <li class="divider"></li>
                      <li><a href="deconnexion">Déconnexion <span class="glyphicon glyphicon-log-out pull-right"></span></a></li>
                    </ul>
                  </li>
                </ul>
                <!-- End Login user -->
              <?php
              } else {
              ?>
                <!-- Register / Login -->
                <ul class="nav navbar-nav navbar-right">
                  <li><a href="inscription" ><span class="glyphicon glyphicon-user"></span> Inscription</a></li>
                  <li>
                    <button data-toggle="modal" data-target="#loginModal" class="btn btn-link"><span class="glyphicon glyphicon-log-in"></span> Connexion</button>
                  </li>
                </ul>
                <!-- End Register / Login -->
              <?php } ?>
            </div>
            <!-- End Nav -->
          </div>
        </div>
      </nav>
      <!-- End Navigation -->