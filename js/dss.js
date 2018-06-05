$(document).ready(function() {
  var isLoggedIn = sessionStorage.getItem('isLoggedIn');

  if (isLoggedIn === null || isLoggedIn === false) {
    window.location.href = 'index.html';
  }

  $('#name').text(sessionStorage.getItem('name'));
  $('#email').text(sessionStorage.getItem('email'));

  $('#logoutBtn').click(logout);

  $('#data').submit(function() {
    var clickedBtn = parseInt($(document.activeElement).attr('data'));

    var k = $('#k').val();
    var j = $('#j').val();
    var m = $('#m').val();
    var alpha = $('#alpha').val();
    var forecast = $('#forecast').val();
    var crime = $('#crime').val();
    var name = sessionStorage.getItem('name');
    var email = sessionStorage.getItem('email');

    var data = {
      k: k,
      j: j,
      m: m,
      alpha: alpha,
      forecast: forecast,
      crime: crime,
      name: name,
      email: email
    };

    if(!areFieldsEmpty(data)) {
      $('#no-data').css('display', 'none');
      $('#loader').css('display', 'block');

      setTimeout(function() {
        $.ajax({
          type: 'POST',
          url: 'php/controllers/analizarDatos.php',
          data: JSON.stringify(data),
          dataType: 'json',
          contentType: 'application/json',
          success: function(response) {

            switch(clickedBtn) {
              case 1:
                $('#table').empty();
                $('#container').css('display', 'none');
                showTable(response);
                break;
              case 2:
                $('#table').empty();
                showGraphics(response, data.forecast, data.crime);
                break;
              case 3:
                showErrors(response);
                break;
            }

            $('#loader').css('display', 'none');
          },
          error: function(XMLHttpRequest) {
            $('#loader').css('display', 'none');
            var err = $.parseJSON(XMLHttpRequest.responseText);
            alert(err[0].res);
          }
        });
      }, 1000);
    }
    else {
      alert('Por favor completa todos los campos');
    }
  });

  $('#close-modal').click(function() {
    $('#modal').removeClass('show');
  });

  $('#modal').click(function(e) {
    if($(e.target).is('div')) {
      $(this).removeClass('show');
    }
  });

  function areFieldsEmpty(data) {
    var count = 0;

    for(var key in data) {
      if(data[key] !== undefined && data[key] !== null && data[key] !== '') {
        count++;
      }
    }

    if(count !== 6) {
      return true;
    }
    else {
      return false;
    }
  }

  function showTable(response) {
    var className = '';

    var res = '<table id="tabla">' +
                          '<thead>' +
                            '<tr>' +
                              '<th>Periodo</th>' +
                              '<th>Frecuencia</th>' +
                              '<th>PS</th>' +
                              '<th>PMS(k)</th>' +
                              '<th>PMD(j)</th>' +
                              '<th>A</th>' +
                              '<th>B</th>' +
                              '<th>PMDA</th>' +
                              '<th>TMAC</th>' +
                              '<th>PTMAC</th>' +
                              '<th>PSE</th>' +
                            '</tr>' +
                          '</thead>' +
                          '<tbody>';

    for(var i = 0; i < response.length - 1; i++) {

      if(i % 2 != 0) {
        className = 'pair';
      }
      else {
        className = 'odd';
      }

      for(j = 0; j < response[i].length; j++) {
        if(response[i][j] === null) {
          response[i][j] = '';
        }
      }

      res += '<tr class="' + className + '">' +
                '<td>' + response[i][0] + '</td>' +
                '<td>' + response[i][1] + '</td>' +
                '<td>' + response[i][2] + '</td>' +
                '<td>' + response[i][4] + '</td>' +
                '<td>' + response[i][6] + '</td>' +
                '<td>' + response[i][8] + '</td>' +
                '<td>' + response[i][9] + '</td>' +
                '<td>' + response[i][10] + '</td>' +
                '<td>' + response[i][12] + '</td>' +
                '<td>' + response[i][13] + '</td>' +
                '<td>' + response[i][15] + '</td>' +
              '</tr>';
    }

    res += '</tbody>';

    $('#table').html(res);
  }

  function showGraphics(response, forecast, crime) {
    var title = '';

    switch(parseInt(crime)) {
      case 1:
        title = 'Robo';
        break;
      case 2:
        title = 'Lesiones';
        break;
    }

    $.when(getChartValues(response)).then(function(res) {
      console.log(res);
      var myChart = Highcharts.chart('container', {
          chart: {
              type: 'line'
          },
          title: {
              text: title
          },
          plotOptions: {
              series: {
                  connectNulls: true
              }
          },
          chart: {
            zoomType: "xy"
          },
          series: [res.freq, res.ps, res.pms, res.pmd, res.pmda, res.ptmac]
      });
    });

    $('#container').css('display', 'block');
  }

  function showErrors(response) {
    var lastElement = response.length - 1;

    var res = '<table>' +
                '<tr>' +
                  '<td><b>PS</b></td>' +
                  '<td>' + response[lastElement][3] + '</td>' +
                '</tr>' +
                '<tr>' +
                  '<td><b>PMS</b></td>' +
                  '<td>' + response[lastElement][5] + '</td>' +
                '</tr>' +
                '<tr>' +
                  '<td><b>PMD</b></td>' +
                  '<td>' + response[lastElement][7] + '</td>' +
                '</tr>' +
                '<tr>' +
                  '<td><b>PMDA</b></td>' +
                  '<td>' + response[lastElement][11] + '</td>' +
                '</tr>' +
                '<tr>' +
                  '<td><b>PTMAC</b></td>' +
                  '<td>' + response[lastElement][14] + '</td>' +
                '</tr>' +
                '<tr>' +
                  '<td><b>SE</b></td>' +
                  '<td>' + response[lastElement][16] + '</td>' +
                '</tr>' +
              '</table>';

    $('#errors').html(res);
    $('#modal').addClass('show');
  }

  function getChartValues(data, forecastCol) {
    var frequency = [];
    var ps = [];
    var pms = [];
    var pmd = [];
    var pmda = [];
    var ptmac = [];
    var returnData = {
      freq: {},
      ps: {},
      pms: {},
      pmd: {},
      pmda: {},
      ptmac: {}
    };

    for(var i = 0; i < data.length - 1; i++) {
      frequency.push(parseInt(data[i][1]));
      ps.push(parseFloat(data[i][2]));
      pms.push(parseFloat(data[i][4]));
      pmd.push(parseFloat(data[i][6]));
      pmda.push(parseFloat(data[i][10]));
      ptmac.push(parseFloat(data[i][13]));
    }

    returnData.freq.name = "Frecuencia";
    returnData.freq.data = frequency;
    returnData.ps.name = "PS";
    returnData.ps.data = ps;
    returnData.pms.name = "PMS";
    returnData.pms.data = pms;
    returnData.pmd.name = "PMD";
    returnData.pmd.data = pmd;
    returnData.pmda.name = "PMDA";
    returnData.pmda.data = pmda;
    returnData.ptmac.name = "PTMAC";
    returnData.ptmac.data = ptmac;

    return returnData;
  }

  function logout() {
    sessionStorage.removeItem('isLoggedIn');
    sessionStorage.removeItem('name');
    sessionStorage.removeItem('email');
    window.location.href = 'index.html';
  }
});
