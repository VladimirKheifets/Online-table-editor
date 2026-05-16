# Online table editor

### Version: 2.0, 2026-05-16

Author: Vladimir Kheifets <vladimir.kheifets@online.de>

Copyright &copy; 2026 Vladimir Kheifets All Rights Reserved

The online table editor allows to add and delete table rows and change the contents of cells.
Data is stored in the MySQL database. It is implemented in PHP, Java Script and CSS.
 
Demo:
[https://www.alto-booking.com/developer/table_editor/](https://www.alto-booking.com/developer/table_editor/)


### 1. File index.php

```php

<?PHP
/*
  PHP script tableEditor
  Version: 2.0, 2026-05-16
  Author: Vladimir Kheifets (vladimir.kheifets@online.de)
  Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
  Demo:
  https://www.alto-booking.com/developer/table_editor/
*/

$inclFile = "../incl/db.php";
if(file_exists($inclFile))
  require_once($inclFile);
else
  $connect = new mysqli("127.0.0.1:3306","root","","test");

require_once("dbToolsClass.php");
require_once("tableEditorFunctions.php");
#####################################################################
$table = "myTable";
$dbt = new dbTools($connect, $table);
$tableHeader = $feldsInsUpd = $dbt -> fieldsName;
$columns = $dbt -> columns;
unset($feldsInsUpd[0]);
#####################################################################
if(isset($_POST["checkRow"]))
{
  foreach($_POST["checkRow"] as $ID)
  {
    $dbt -> delRow($ID);
  }
}
else if(isset($_POST["iRow"]))
{
  foreach ($_POST["iRow"] as $i =>$key)
  {
    $row = setRowToSave($key, $i, $columns);
    $dbt -> saveRow($row);
  }
}
#####################################################################
$selected = $dbt -> select();
$tableRows = getTableRows($selected, $tableHeader, $columns);
#####################################################################
?>
<html>
<head>
<title>Table editor</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

### 3. File dbToolsClass.php

```php
<?PHP
class dbTools{
  private $table;
  private $connect;
  private $keyName;
  public $fieldsName;
  public $columns;

  //-------------------------------------------------------------
   function __construct($connect, $table) {
    $this-> connect = $connect;
    $this -> table = $table;
    $this -> fieldsName = $this->getFieldsName();
  }
  //----------------------------------------------------------
  private function getFieldsName(){
    $connect = $this->connect;
    $table = $this->table;
    $query = "SHOW FIELDS FROM `$table`";
    $out = [];
    $stmt = $connect -> prepare($query);
    $stmt -> execute();
    $result = $stmt->get_result();
    foreach($result -> fetch_all(MYSQLI_NUM) as $row)
      $out[] = $row[0];
    $stmt->close();
    $this -> keyName =  $out[0];
    $this -> columns = count($out);
    return $out;
  }
  //----------------------------------------------------------
  public function delRow($key){
    $connect = $this->connect;
    $table = $this->table;
    $keyName = $this->keyName;
    $query = "DELETE FROM $table WHERE $keyName = ? ";
    $stmt = $connect -> prepare($query);
    $stmt -> execute([$key]);
    $stmt->close();
  }
  //-------------------------------------------------------
  public function select( $filter="",$filterValArr=null, $felds="*" ){
    $connect = $this->connect;
    $table = $this->table;
    $keyName = $this->keyName;

    if(strpos($filter,"?") !== false){
      if($filter == "?")
        $filter = "$keyName = ?";
      $filter = "WHERE $filter";
    }
    $query = "SELECT $felds FROM $table $filter";
    $stmt = $connect->prepare($query);
    $stmt->execute($filterValArr);
    $result = $stmt->get_result();
    $out = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $out;
  }
  //----------------------------------------------------------
  public function saveRow($values, $felds=null){
    $connect = $this->connect;
    $table = $this->table;
    $keyName = $this->keyName;

    $feldsArr = $felds?explode(",",$felds):$this->fieldsName;
    if($this->is_key($values[0]))
    {
      unset($feldsArr[0]);
      $set = implode("=?,",$feldsArr)."=?";
      $query = "UPDATE $table SET $set WHERE $keyName = ?";
      $keyVal = $values[0];
      unset($values[0]);
      $values=array_merge($values,[$keyVal]);
    }
    else
    {
      if(!$felds)
        $felds = implode(",",$feldsArr);
      $par = implode(",",array_fill(0, count($feldsArr),"?"));
      $query = "INSERT INTO $table ($felds) VALUES ($par)";
    }
    $stmt = $connect->prepare($query);
    $stmt->execute($values);
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
  }
  //----------------------------------------------------------
  private function is_key($value){
    $connect = $this->connect;
    $table = $this->table;
    $keyName = $this->keyName;
    $query = "SELECT count($keyName) as res FROM `$table` WHERE $keyName = ? ";
    $stmt = $connect->prepare($query);
    $stmt->execute([$value]);
    $stmt->bind_result($res);
    $stmt->fetch();
    $stmt->close();
    return $res;
  }
  //----------------------------------------------------------
}
```
### 4. File tableEditorFunctions.php

```php

<?PHP
#####################################################################
function getTableRows($selected, $tableHeader, $columns){
  if(count($selected)>0)
  {
    $tableRows =[];
    foreach($selected as $row)
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
    $tableRows[0] = array_fill(0, $columns, '');
    return $tableRows;
}
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
function setRowToSave($key, $i, $columns){
  $row = [];
  $row[] = $key;
  for($j=1; $j<$columns; $j++)
    $row[]= $_POST["col".$j][$i];
  return $row;
}
#####################################################################

```
### 5. File myTable.sql

```sql

  --
  -- Table structure for table `myTable`
  --

  CREATE TABLE `myTable` (
    `ID` int NOT NULL,
    `A` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `B` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `C` varchar(200) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

  ALTER TABLE `myTable`
    ADD UNIQUE KEY `request_id` (`ID`);

  ALTER TABLE `myTable`
    MODIFY `ID` int NOT NULL AUTO_INCREMENT;
  COMMIT;

```

### 6. File tableEditor.js
```js

/*
  JS script tableEditor
  Version: 2.0, 2026-05-16
  Author: Vladimir Kheifets (vladimir.kheifets@online.de)
  Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
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
        if(checkEl[i].value == 0){
          table.deleteRow(r);
          checkCount = checkEl.length;
          if(checkCount==1)
            addRow();
        }
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
### 7. File tableEditor.css

```css

/*
  CSS tableEditor
  Version: 2.0, 2026-05-16
  Author: Vladimir Kheifets (vladimir.kheifets@online.de)
  Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
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