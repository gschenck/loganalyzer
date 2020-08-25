<div class="container">
<table class="table table">
<tbody>
<?php
script('loganalyzer', 'print');
style('loganalyzer', 'print');
//print_r($_[2]);
foreach($_[2] as $data){

?>

<tr>
    <td>
        <?php echo $data['timestamp'];?>
    </td>
    <td>
        <?php echo $data['user'];?>
    </td>
    <td>
        <?php echo $data['description'];?>
    </td>
    <td>
        <?php echo '/'.$data['account'].''.$data['object'];?>
    </td>
</tr>

<?php  
}
?>
</tbody>
</table>
</div>