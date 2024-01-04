# Online table editor

### Version: 1.0, 2024-01-01

Author: Vladimir Kheifets <vladimir.kheifets@online.de>

Copyright &copy; 2023 Vladimir Kheifets All Rights Reserved

The online table editor allows to add and delete table rows and change the contents of cells.
Data is stored in the MySQL database. It is implemented in PHP, Java Script and CSS.
 
Demo:
[https://www.alto-booking.com/developer/table_editor/](https://www.alto-booking.com/developer/table_editor/)


### 1. File index.php

```php
<?
/*
  PHP script tableEditor
  Version: 1.0, 2024-01-01
  Author: Vladimir Kheifets (vladimir.kheifets@online.de)
  Copyright (c) 2024 Vladimir Kheifets All Rights Reserved
  Demo:
  https://www.alto-booking.com/developer/table_editor/
*/

$connect = new mysqli("127.0.0.1:3306","root","","test");
#####################################################################
/*
  myTable structure:

  ID primary key int(3)       No none AUTO_INCREMENT
   A text utf8mb4_unicode_ci Yes NULL
   B text utf8mb4_unicode_ci Yes NULL
   C text utf8mb4_unicode_ci Yes NULL
   D text utf8mb4_unicode_ci Yes NULL
   E text utf8mb4_unicode_ci Yes NULL
   F text utf8mb4_unicode_ci Yes NULL
*/
#####################################################################
$table = "myTable";
$query = "SHOW COLUMNS FROM $table";
$result = mysqli_query($connect,$query);
$tableHeader = [];
while($row = mysqli_fetch_assoc($result))
{
  $tableHeader[] = $row['Field'];
}
$feldsInsUpd = $tableHeader;
unset($feldsInsUpd[0]);
$columns = count($tableHeader);
#####################################################################
if(isset($_POST["checkRow"]))
{
  $buf = [];
  foreach($_POST["checkRow"] as $ID)
  {
    $query = "DELETE FROM $table WHERE ID = '$ID'";
    mysqli_query($connect, $query);
  }
}
else if(isset($_POST["iRow"]))
{
  foreach ($_POST["iRow"] as $iRow => $ID) {
    if($ID==0)
    {
      $query = "INSERT INTO $table (";
      $query .= "`".implode("`,`",$feldsInsUpd)."`";
      $query .= ") VALUES ('";
      $buf = [];
      for ($i=1; $i < $columns; $i++) {
        $buf[] = addslashes($_POST["col$i"][$iRow]);
      }
      $query .= implode("','",$buf);
      $query .= "')";
      mysqli_query($connect,$query);
    }
    else
    {
      $query = "UPDATE $table SET ";
      $buf = [];
      for ($i=1; $i < $columns; $i++) {
        $value = htmlentities($_POST["col$i"][$iRow]);
        $buf[] = "`{$feldsInsUpd[$i]}` = '$value'";
      }
      $query .= implode(",",$buf);
      $query .= " WHERE `{$tableHeader[0]}` = '$ID'";
      mysqli_query($connect, $query);
    }
  }
}
#####################################################################
$query = "SELECT * FROM $table";
$result = mysqli_query($connect, $query);
if(mysqli_num_rows($result)>0)
{
  $tableRows =[];
  while($row = mysqli_fetch_assoc($result))
  {
    $buf = [];
    foreach($tableHeader as $col => $colName)
    {
      if($col==0) $ID = $row[$colName];
        $buf[] = $row[$colName];
    }
    $tableRows[$ID] = $buf;
  }
}
else
  $tableRows[0] = array_fill(0, 7, '');
#####################################################################
function setTdTag($value, $iCol=null){
  if(isset($iCol))
    return <<<HTML
    <td><input type = "text" name="col{$iCol}[]" value="$value"></td>
    HTML;
  else
    return <<<HTML
    <td>$value</td>
    HTML;
}
#####################################################################
function setTableRow($rowArray, $inpRow=false, $iRow = 0){
  $outHtml = "<tr>";
  foreach($rowArray as $iCol => $value)
  {
    if($iCol == 0)
    {
      if($inpRow)
      {
        $outHtml .= "<td><input name='checkRow[]' type='checkbox' value='$iRow'>";
        $outHtml .= "<input type ='hidden' name = 'iRow[]' value='$iRow'></td>";
      }
      else
        $outHtml .= "<td>&nbsp;</td>";
    }
    else
    {
      if($inpRow)
        $outHtml .= setTdTag($value, $iCol);
      else
         $outHtml .= setTdTag($value);
    }
  }
  $outHtml .= "<tr>";
  return $outHtml;
}
#####################################################################
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Table editor</title>
<link rel="stylesheet" type="text/css" href="tableEditor.css" />
<script>
  columns = "<?=$columns?>";
  tableID = "<?=$table;?>";
</script>
<script type="text/javascript" src="tableEditor.js"></script>
</head>
<body>
<div align="center">
<form action="" method="post">
<table id="<?=$table?>" border="1">
<?
echo setTableRow($tableHeader,false);
foreach($tableRows as $iRow => $tableRow)
{
  echo setTableRow($tableRow, true, $iRow);
}
?>
</table>
<p>
<input type="submit" value="Save " />
<input type="button" value="Add row" />
<input type="button" value="Delete row" />
</p>
</form>
</div>
</body>
</html>
```
### 2. File tableEditor.js
```
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
```
### 3. File tableEditor.css

```css
/*
  CSS tableEditor
  Version: 1.0, 2024-01-01
  Author: Vladimir Kheifets (vladimir.kheifets@online.de)
  Copyright (c) 2024 Vladimir Kheifets All Rights Reserved
*/

table{
  margin-top: 40px;
  border-collapse: collapse;
}

td{
  padding:0 2 0 2;
  text-align: center;
  width: 50px;
}

td + td{
  width: 150px;
}

input[type="text"]{
  border-width:0px;
  border:none;
}

p input{
  width: 150px;
  margin: 20 20 0 20;
}
```