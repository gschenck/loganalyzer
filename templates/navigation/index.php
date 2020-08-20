
<?php
 
$users=$_[0];
$category=$_[1];
unset($users['tescior']);
unset($users['system']);
array_multisort($users);
array_push($category, '');
array_multisort($category);
date_default_timezone_set("Europe/Warsaw");
?>
<form method='POST'  action="/index.php/apps/loganalyzer/db">
  <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
  <label for="date1"><b>OD:</b></label>
  <input type="datetime-local" id="date1" name="date1" value="<?php if($_POST['date1']) echo $_POST['date1']; else echo date("Y-m-d").'T'.date("H:i"); ?>" max="<?php echo date("Y-m-d"); ?>">
	<hr>
   <label for="date2"><b>DO:</b></label>
  <input type="datetime-local" id="date2" name="date2" value="<?php if($_POST['date2']) echo $_POST['date2']; else echo date("Y-m-d").'T'.date("H:i");  ?>" max="<?php echo date("Y-m-d"); ?>">
  <hr>
  <label for="users"><b>USER:</b></label>
  <select name="users">
  <?php
      foreach($users as $key => $value)
      {
        echo '<option value='.$key.''; 
        if($_POST['users']==$key) echo ' selected ';
        echo '>'.$value.'</option>';
      }
  ?>
  </select>
  <hr>
  <label for="category"><b>Typ:</b></label>
  <select name="category">
  <?php
      foreach($category as $key => $value)
      {
        echo '<option value='.$key.''; 
        if($_POST['category']==$key) echo ' selected ';
        echo '>'.$value.'</option>';
      }
  ?>
  </select>

  <hr>
  <input type="submit" value="Pokaż">
  <button type="submit" name="print" value="1" <?php if(!($_POST['date1']&&$_POST['date2'])) echo " disabled "; ?> >Drukuj</button>
</form>




