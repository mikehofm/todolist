<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <link rel="stylesheet" href="css/bootstrap.min.css">
        <style>
            body {
                padding-top: 50px;
                padding-bottom: 20px;
            }
        </style>
        <link rel="stylesheet" href="css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="css/main.css">

        <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
        <script src="js/vendor/bootstrap.js"></script>
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">To-do list</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <div class="navbar-right">
            <div class="navbar-form">
                <div class="form-group">
                    <?php if (User::isLoggedIn()): ?>
                    <button class="btn btn-default logout">Log out</button>
                    <?php else: ?>
                    <button class="btn btn-success" data-toggle="modal" data-target="#signup-modal">Sign up</button>
                    <?php endif; ?>
                </div>
            </div>
          </div>
          <?php if (!User::isLoggedIn()): ?>
          <form class="navbar-form navbar-right login-form" role="form" action="process-user.php" method="post">
            <input type="hidden" name="action" value="login" />
            <div class="form-group">
              <input type="text" name="name" placeholder="Email" class="form-control">
            </div>
            <div class="form-group">
              <input type="password" name="password" placeholder="Password" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Log in</button>
          </form>
          <?php endif; ?>
        </div><!--/.navbar-collapse -->
      </div>
    </nav>
    
    <div class="modal fade" id="signup-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create your account</h4>
                </div>
                <form action="process-user.php" method="post" class="signup-form" autocomplete="off">
                    <input type="hidden" name="action" value="create" />
					<!-- fake fields are a workaround for chrome autofill putting your login credentials into the signup form -->
					<input class="hidden" type="text" name="fakeusernameremembered"/>
					<input class="hidden" type="password" name="fakepasswordremembered"/>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="signupEmail">Email address</label>
                            <input type="text" name="createname" placeholder="Email" class="form-control" id="signupEmail" autocomplete="off" />
                        </div>
                        <div class="form-group">
                            <label for="signupPassword">Password</label>
                            <input type="password" name="createpassword" placeholder="Password" class="form-control" id="signupPassword" autocomplete="off" />
                            <small>For maximum security, please use at least 4 digits, 3 symbols and 2 Klingon characters.</small>
                        </div>
                        <div class="alert alert-danger collapse" ></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Sign Up</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    
    <script>
        $('#signup-modal').on('shown.bs.modal', function() {
            $('#signupEmail').focus();
        }); 
        
        $('.signup-form').submit(function(e) {
           e.preventDefault();
           var form = $(this);
           form.find('.alert-danger').removeClass('in');
           
           $.ajax(form.attr('action'), { type: this.method, data: form.serialize() })
              .done(function(response, textStatus) {
                  location.reload();
              })
              .fail(function(jqXHR, textStatus, error) {
                  form.find('.alert-danger').text(jqXHR.responseText).collapse('show');
              });
        });
        
        $('.login-form').submit(function(e) {
           e.preventDefault();
           var form = $(this);
           var passwordInput = form.find('.form-group').has('input[type=password]');
           passwordInput.popover('destroy');
                   
           $.ajax(form.attr('action'), { type: this.method, data: form.serialize() })           
              .done(function(response, textStatus) {
                  location.reload();
              })
              .fail(function(jqXHR, textStatus, error) {
                  passwordInput.addClass('has-error');
                  passwordInput.popover({ container: 'body', placement: 'bottom', trigger: 'focus', content: 'Wrong username or password' });
                  passwordInput.popover('show');
              });
        });
        
        $('.logout').click(function() {
            $.get('process-user.php', { action: 'logout' }, function() {
                location.reload();
            });
        })
    </script>