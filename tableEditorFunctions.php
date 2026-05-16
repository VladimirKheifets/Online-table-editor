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