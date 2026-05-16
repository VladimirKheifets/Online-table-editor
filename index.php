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