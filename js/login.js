$(document).ready(function() {

  var isLoggedIn = sessionStorage.getItem('isLoggedIn');

  if(isLoggedIn) {
    window.location.href = 'dss.html';
  }


  $('#login-text b').click(showSignUp);
  $('#register-text b').click(showLogin);
  $('#authors').click(showAuthors);

  $('#close-modal').click(function() {
    $('#modal').removeClass('show');
  });

  $('#modal').click(function(e) {
    if($(e.target).is('div')) {
      $(this).removeClass('show');
    }
  });

  $('#login').submit(function() {
    var email = $('#email').val();
    var pass = $('#password').val();

    if (!isFieldEmpty(email) && !isFieldEmpty(pass)) {
      var userData = {
        email: email,
        pass: sha1(pass)
      };

      $.ajax({
        type: 'POST',
        url: 'php/controllers/login.php',
        data: JSON.stringify(userData),
        dataType: 'json',
        contentType: 'application/json',
        success: function(response) {
          sessionStorage.setItem('isLoggedIn', true);
          sessionStorage.setItem('name', response[0].res.nombreUsuario);
          sessionStorage.setItem('email', response[0].res.correoUsuario);
          window.location.href = 'dss.html';
        },
        error: function(XMLHttpRequest) {
          var err = $.parseJSON(XMLHttpRequest.responseText);
          alert(err[0].res);
        }
      });
    } else {
      alert('Faltan campos por completar');
    }
  });

  $('#register').submit(function() {
    var name = $('#regName').val();
    var email = $('#regEmail').val();
    var pass = $('#regPass').val();
    var confirmPass = $('#regConfirmPass').val();

    if (!isFieldEmpty(name) && !isFieldEmpty(email) && !isFieldEmpty(pass) && !isFieldEmpty(confirmPass)) {
      if(validateEmail(email)) {
        if (pass === confirmPass) {
          var userData = {
            name: name,
            email: email,
            pass: sha1(pass)
          };

          $.ajax({
            type: 'POST',
            url: 'php/controllers/signup.php',
            data: JSON.stringify(userData),
            dataType: 'json',
            contentType: 'application/json',
            success: function(response) {
              alert(response[0].res);
              showLogin();
            },
            error: function(XMLHttpRequest) {
              var err = $.parseJSON(XMLHttpRequest.responseText);
              alert(err[0].res);
            }
          });
        } else {
          alert('Las contraseñas no coinciden');
        }
      }
      else {
        alert('Ingresa un correo válido');
      }
    } else {
      alert('Faltan campos por completar');
    }
  });

  function isFieldEmpty(field) {
    if (field === '' || field === undefined || field === null) {
      return true;
    } else {
      return false;
    }
  }

  function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  function showLogin() {
    document.title = "Softy | Iniciar Sesión";
    $('.login').css('height', '380px');
    $('#header').text('Iniciar sesión');
    $('.login p').css('display', 'block');
    $('#login').css('display', 'block');
    $('#login').addClass('animated fadeIn');
    $('#register').removeClass('show-form');
    emptyFields();
  }

  function showSignUp() {
    document.title = "Softy | Registro"
    $('.login').css('height', '450px');
    $('#header').text('Registro');
    $('.login p').css('display', 'none');
    $('#login').css('display', 'none');
    $('#register').addClass('show-form');
    $('#register').addClass('animated fadeIn');
    emptyFields();
  }

  function showAuthors() {
    $('#modal').addClass('show');
  }

  function emptyFields() {
    $('#email').val('');
    $('#password').val('');
    $('#regName').val('');
    $('#regEmail').val('');
    $('#regPass').val('');
    $('#regConfirmPass').val('');
  }
});
