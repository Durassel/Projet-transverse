      <!-- Footer -->
      <footer>
        <div class="container-fluid background-lightgrey">
          <div class="container">
            <div class="row">
              <div class="col-lg-12 footer-bottom">
                <span>Copyright © 2017 Brain'squiz. Tous droits réservés.</span>
                <i class="fa fa-linkedin pull-right" aria-hidden="true"></i>
                <i class="fa fa-twitter pull-right" aria-hidden="true"></i>
                <i class="fa fa-facebook-official pull-right" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </footer>
      <!-- End Footer -->
    </div>

    <!-- Login Modal Box -->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Fermer</span></button>
            <h3 class="modal-title" id="lineModalLabel">Connexion</h3>
          </div>
          <div class="modal-body">
            <form class="form" role="form" method="post" action="connexion" accept-charset="UTF-8" id="login-nav">
              <div class="form-group">
                 <label class="sr-only" for="email">Adresse e-mail</label>
                 <input type="email" class="form-control" id="email" name="email" placeholder="Adresse e-mail" required>
              </div>
              <div class="form-group">
                 <label class="sr-only" for="password">Mot de passe</label>
                 <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
              </div>
              <div class="checkbox">
                 <label><input type="checkbox" name="remember"> Se souvenir de moi</label>
              </div>
              <div class="form-group">
                 <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
              </div>
           </form>
          </div>
        </div>
      </div>
    </div>
    <!-- End Login Modal Box -->

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
    <!-- Minified JavaScript -->
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
    <script src="js/tinymce/tiny_mce_dev.js"></script>
    <script type="text/javascript">
    tinyMCE.init({
      mode : "specific_textareas",
      editor_selector : 'wysiwyg',
      theme : "advanced",
      plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,previez,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

      // Theme options
      theme_advanced_buttons1 : "bold,italic,underline,strikethrought,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,formatselect",
      theme_advanced_buttons2 : "",
      theme_advanced_buttons3 : "",
      theme_advanced_buttons4 : "",
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_resizing : true,

      // Skins options
      skin : "o2k7",
      skin_variant : "silver"
    });
    </script>
    <?php if(isset($js)) { echo $js; }?>
  </body>
</html>