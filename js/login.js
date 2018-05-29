$(document).ready(function() {
  $('#login-text b').click(function() {
    document.title = "Softy | Registro"
    $('.login').css('height', '450px');
    $('#header').text('Registro');
    $('.login p').css('opacity', '0');
    $('#login').css('opacity', '0');
    $('#register').addClass('show-form');
    emptyFields();
  });

  $('#register-text b').click(function() {
    document.title = "Softy | Iniciar Sesión";
    $('.login').css('height', '380px');
    $('#header').text('Iniciar sesión');
    $('.login p').css('opacity', '1');
    $('#login').css('opacity', '1');
    $('#register').toggleClass('show-form');
    emptyFields();
  });

  $('#login').submit(function() {
    var email = $('#email').val();
    var pass = $('#password').val();

    if (!isFieldEmpty(email) && !isFieldEmpty(pass)) {
      var userData = {
        email: email,
        pass: sha1(pass)
      };

      console.log(userData);
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

          console.log(userData);
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

  function emptyFields() {
    $('#email').val('');
    $('#password').val('');
    $('#regName').val('');
    $('#regEmail').val('');
    $('#regPass').val('');
    $('#regConfirmPass').val('');
  }
});
