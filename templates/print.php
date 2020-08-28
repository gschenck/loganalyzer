<?php 
    script('loganalyzer', 'print');
    style('loganalyzer', 'print');
    $lp=1; 
?>
<div class="container">
<table class="table">
    <tbody>
        <tr style="border-style:hidden;">
            <td class="righthead"></td>
            <td class="righthead"><?php echo date('Y-m-d');?></td>
        </tr>
    </tbody>
</table>
<center><h2>Raport operacji na plikach serwera: https://<?php echo $_SERVER['SERVER_NAME'];?> </h2></center>
<hr>
<table class="table">
    <thead>
        <tr>
            <th>
                LP.
            </th>
            <th>
                DATA
            </th>
            <th>
                OBIEKT
            </th>
            <th>
                OPERACJA
            </th>
    </thead>
<tbody>
<?php

//print_r($_[2]);
foreach($_[2] as $data){

?>

<tr class="d-flex">
    <td class="col-4">
        <?php echo $lp;$lp++;?>
    </td>
    <td class="col-2 datanowrap">
        <?php echo $data['timestamp'];?>
    </td>
    <td class="col-2">
        <?php echo '/'.$data['account'].''.$data['object'];?>
        
    </td>
    <td class="col-2">
        <?php echo $data['description'];?>
    </td>
</tr>
<?php  
}
?>
</tbody>
</table>
</div>