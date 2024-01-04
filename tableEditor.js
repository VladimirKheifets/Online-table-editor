/*
  JS script tableEditor
  Version: 1.0, 2024-01-01
  Author: Vladimir Kheifets (vladimir.kheifets@online.de)
  Copyright (c) 2024 Vladimir Kheifets All Rights Reserved
*/
window.addEventListener("load", () => {
  msg1 = "Do you really want to delete the table rows?";
  msg2 = "Select the table rows you want to delete";
  //--------------------------------------------------------
  addRow = function(){
    table = document.getElementById(tableID);
    newRow = table.insertRow(-1);
    newCell = newRow.insertCell(-1);
    colHTML = "<input name='checkRow[]' type='checkbox' value='0'>";
    colHTML += "<input type ='hidden' name = 'iRow[]' value='0'>";
    newCell.innerHTML = colHTML;
    for (var i = 1; i < columns; i++) {
      newCell = newRow.insertCell(-1);
      newCell.innerHTML = '<input type = "text" name="col'+i+'[]" value="">';
    }
  }
  //--------------------------------------------------------
  delRows = function(){
    if(!confirm(msg1)) return;
    table = document.getElementById(tableID);
    checkEl = document.querySelectorAll("input[type='checkbox']");
    checkCount = checkEl.length;
    goSubmit = false;
    noCheked = true;
    for (i = 0; i < checkCount; i++) {
      if(checkEl[i].checked)
      {
        noCheked = false;
        r = checkEl[i].parentNode.parentNode.rowIndex;
        if(checkEl[i].value == 0)
          table.deleteRow(r);
        else
          goSubmit = true;
      }
    }

    if(noCheked)
    {
      alert(msg2);
      return;
    }
    else if(goSubmit)
    {
      table.parentNode.submit();
    }
  }
  //-----------------------------------------------------------------
  buttonAdd = document.querySelectorAll("input[value='Add row']")[0];
  buttonAdd.addEventListener("click", addRow);
  //-----------------------------------------------------------------
  buttonDel = document.querySelectorAll("input[value='Delete row']")[0];
  buttonDel.addEventListener("click", delRows);
  //-----------------------------------------------------------------
});