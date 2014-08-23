var optionsPromoEmc = new Array();
function showHide(input, operator)
{
  $('config_'+operator).removeClassName('hidden');
  $('fields_'+operator).removeClassName('hidden');
  if($(input).getValue() == 0)
  {
    $('config_'+operator).addClassName('hidden');
    $('fields_'+operator).addClassName('hidden');
    $('quantity_'+operator).value =  '';
    $('fields_'+operator).innerHTML =  '';
  }
}

function showOthers(service)
{
  var nb = parseInt($('quantity_'+service).getValue());
  if(""+nb == "NaN")
  {
    alert("Veuillez indiquer un chiffre valide");
    return;
  }
  document.getElementById('fields_'+service).innerHTML = "";
  var templateTxt = "";
  for(var i = 0; i < nb; i++)
  {
    var params = {SERVICE : service, ID : i, IDinc : (i+1)}
    templateTxt = template.evaluate(params);
    var content = document.getElementById('fields_'+service).innerHTML;
    document.getElementById('fields_'+service).innerHTML = content + templateTxt;
  }
}

function fillUpFields(service)
{
  if(""+optionsPromoEmc[service] != "undefined")
  {
    for(var i = 0; i < optionsPromoEmc[service].length; i++)
    {
      var fields = optionsPromoEmc[service][i];
      document.getElementById('amount_from'+service+i).value = fields['amount_from'];
      document.getElementById('amount_to'+service+i).value = fields['amount_to'];
      document.getElementById('valid_from'+service+i).value = fields['valid_from'];
      document.getElementById('valid_to'+service+i).value = fields['valid_to'];
      document.getElementById('value'+service+i).value = fields['value'];
      var typeSelect = $('type_'+service+i);
      typeSelect.options[fields['type']].selected = true;
      if(""+document.getElementById('geo'+fields['geo']+'_'+service+i) != "null") $('geo'+fields['geo']+'_'+service+i).checked = true;
    }
  }
}

function deleteOffer(id, ref)
{
  $('table_'+id).removeClassName('hidden');
  if(ref.checked) $('table_'+id).addClassName('hidden');
}

function checkFormat(field)
{
  var separators = new Array();
  separators[0] = "-";
  separators[1] = "-";
  separators[2] = " ";
  separators[3] = ":";
  separators[4] = "";
  var acceptedFour = new Array();
  acceptedFour[0] = false;
  acceptedFour[1] = false;
  acceptedFour[2] = true;
  acceptedFour[3] = false;
  acceptedFour[4] = false;
  var format = "(\\d{1,})-(\\d{1,})-(\\d{1,}) (\\d{1,}):(\\d{1,})";
  var expression = new RegExp(format);
  var fields = expression.exec($(field).getValue());
  var newValue = "";
  var j = 0;
  for(var i=0; i < fields.length; i++)
  {
    if(fields[i] != $(field).getValue())
    {
      var fieldValue = fields[i];
      var len = fields[i].length;
      if(len == 1)
      {
        fieldValue = "0"+fields[i];
      }
      else if(len > 2 && !acceptedFour[j])
      {
        fieldValue = fieldValue.substring(0, 2);
      }
      newValue += fieldValue + separators[j];
      j++;
    }
  }
  document.getElementById(field).value = newValue;
}