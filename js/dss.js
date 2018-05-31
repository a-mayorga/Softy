$(document).ready(function() {
  var isLoggedIn = sessionStorage.getItem('isLoggedIn');

  if(isLoggedIn === null || isLoggedIn === false) {
    window.location.href = 'index.html';
  }

  $('#name').text(sessionStorage.getItem('name'));
  $('#email').text(sessionStorage.getItem('email'));

  $('#logoutBtn').click(logout);

  function logout() {
    sessionStorage.removeItem('isLoggedIn');
    sessionStorage.removeItem('name');
    sessionStorage.removeItem('email');
    window.location.href = 'index.html';
  }
});
