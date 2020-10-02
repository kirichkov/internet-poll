$(function () {
  $.ajaxSetup({ cache: false });

  var lastPop;

  // Define map
  var map = L.map('mapid').setView($("body").data('location'), $("body").data('zoom'));
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?', { attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>' }).addTo(map);
  var ColorMarker = L.Icon.extend({
    options: {
      shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12.5, 41],
      popupAnchor: [1, -41],
      shadowSize: [35, 41],
    }
  });

  // Context click on map
  map.on('contextmenu', function (e) {
    var templateInsert = '<form id="popup-form-insert">\
        <table class="popup-table">\
        <tr class="popup-table-row">\
          <th class="popup-table-header"><label for="input-street">Straße:</label></th>\
          <td class="popup-table-data"><input id="input-street" class="popup-input" type="text" /></td>\
        </tr>\
        <tr class="popup-table-row">\
          <th class="popup-table-header"><label for="input-housenumber">Nummer:</label></th>\
          <td class="popup-table-data"><input id="input-housenumber" class="popup-input" type="text" /></td>\
        </tr>\
        <tr class="popup-table-row">\
          <th class="popup-table-header"><label for="input-status">Status:</label></th>\
          <td class="popup-table-data">\
          <select id="input-status">\
            <option value="unbekannt">Unbekannt</option>\
            <option value="ja">Ja</option>\
            <option value="nein">Nein</option>\
            <option value="vielleicht">Vielleicht</option>\
          </select>\
          </td>\
        </tr>\
        </table>\
        <input id="input-lnglat" class="popup-input" type="hidden" value="'+ e.latlng.lat + "," + e.latlng.lng + '" />\
        <button id="submit-insert" type="input" value="submit">Hinzufügen</button>\
      </form>';
    map.closePopup(lastPop);

    lastPop = L.popup()
      .setLatLng(e.latlng)
      .setContent(templateInsert)
      .addTo(map)
      .openOn(map);

    $("#popup-form-insert").on("submit", function (e) {
      e.preventDefault();

      var inputStreet = $("#input-street").val();
      var inputHousenumber = $("#input-housenumber").val();
      var inputStatus = $("#input-status option:selected").val();
      var inputLnglat = $("#input-lnglat").val();

      $.ajax({
        type: "POST",
        url: "manage.php",
        data: {
          inStreet: inputStreet,
          inHousenumber: inputHousenumber,
          inStatus: inputStatus,
          inType: "insert",
          inLatLng: inputLnglat,
          token: $("meta[name=csrf-token]").attr('content')
        },
        success: function(data, status, xhr) {
          map.closePopup(lastPop);
        }
      });

    });

  });

  // Define markers	
  var surveyMarker = L.Marker.extend({
    options: {
      surveyId: 0,
      surveyStreet: 0,
      surveyHousenumber: 0,
      surveyStatus: 0
    }
  });

  var green = new ColorMarker({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
  })

  var yellow = new ColorMarker({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png'
  });

  var red = new ColorMarker({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png'
  });

  var grey = new ColorMarker({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png'
  });


  // init
  $.getJSON('./points.json', function (data, status, xhr) {
    $.each(data, function (index, el) {
      switch (el.status) {
        case 'ja':
          icon = green;
          break;
        case 'nein':
          icon = red;
          break;
        case 'vielleicht':
          icon = yellow;
          break;
        case 'unknown':
          icon = grey;
          break;
      }

      var marker = new surveyMarker(el.latlong, {
        icon: icon,
        surveyId: el.id,
        surveyStreet: el.street,
        surveyHousenumber: el.housenumber,
        surveyStatus: el.status
      })
      //marker.bindTooltip('<b>' + el.street + " " + el.housenumber + "</b><br />\nEntscheidung: " + el.status);
      marker.addTo(map);

      marker.on('click', function onMarkerClick(e) {
        map.closePopup();
        var callMarker = e.target,
          callOptions = e.target.options;

        if (callMarker.hasOwnProperty('_popup')) {
          callMarker.unbindPopup();
        }

        var sJa = sNein = sVielleicht = sUnknown = "";
        switch (callOptions.surveyStatus) {
          case 'ja':
            sJa = ' selected';
            break;
          case 'nein':
            sNein = ' selected';
            break;
          case 'vielleicht':
            sVielleicht = ' selected';
            break;
          case 'unknown':
            sUnknown = ' selected';
            break;
        }

        var templateUpdate = '<form id="popup-form">\
        <table class="popup-table">\
        <tr class="popup-table-row">\
          <th class="popup-table-header"><label for="input-street">Straße:</label></th>\
          <td class="popup-table-data"><input id="input-street" class="popup-input" type="text" value="'+ callOptions.surveyStreet + '" /></td>\
        </tr>\
        <tr class="popup-table-row">\
          <th class="popup-table-header"><label for="input-housenumber">Nummer:</label></th>\
          <td class="popup-table-data"><input id="input-housenumber" class="popup-input" type="text" value="'+ callOptions.surveyHousenumber + '" /></td>\
        </tr>\
        <tr class="popup-table-row">\
          <th class="popup-table-header"><label for="input-status">Status:</label></th>\
          <td class="popup-table-data">\
          <select id="input-status">\
            <option value="unbekannt" '+ sUnknown + '>Unbekannt</option>\
            <option value="ja" '+ sJa + '>Ja</option>\
            <option value="nein" '+ sNein + '>Nein</option>\
            <option value="vielleicht" '+ sVielleicht + '>Vielleicht</option>\
          </select>\
          </td>\
        </tr>\
        </table>\
        <input id="input-id" class="popup-input" type="hidden" value="'+ callOptions.surveyId + '" />\
        <button id="submit-update" type="input" value="submit">Aktualisieren</button>\
      </form>';


        callMarker.bindPopup(templateUpdate);
        callMarker.openPopup();
        //$("#popup-form").on("submit", function(e) {			
        $("#popup-form").submit(function (e) {
          e.preventDefault();

          var inputId = $("#input-id").val();
          var inputStreet = $("#input-street").val();
          var inputHousenumber = $("#input-housenumber").val();
          var inputStatus = $("#input-status option:selected").val();

          $.ajax({
            type: "POST",
            url: "/manage.php",
            data: {
              inId: inputId,
              inStreet: inputStreet,
              inHousenumber: inputHousenumber,
              inStatus: inputStatus,
              inType: "update",
              token: $("meta[name=csrf-token]").attr('content')
            }
          });

          callMarker.options.surveyStatus = inputStatus;
          callMarker.options.surveyStreet = inputStreet;
          callMarker.options.surveyHousenumber = inputHousenumber;

          switch (inputStatus) {
            case 'ja':
              icon = green;
              break;
            case 'nein':
              icon = red;
              break;
            case 'vielleicht':
              icon = yellow;
              break;
            case 'unknown':
              icon = grey;
              break;
          }

          callMarker.setIcon(icon);

          callMarker.closePopup();
          e.returnValue = false;
        });
      });
    });
  });
})
