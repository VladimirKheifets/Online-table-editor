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