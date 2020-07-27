<?php
echo "
<div class=\"container\" style=\"background-color: transparent; width: 966px; height:500px; padding-left: 0px;\" >
<div class=\"container\" style=\"background-color: transparent; width: 966px; height:80px; padding-top:10px;padding-bottom: 0px;\" >

<table class=\"table table\" style='font-family:\"Copperplate\", Copperplate ,monospace; font-size:90%;'>
    <thead>
      <tr class=\"d-flex\">
        <th class=\"col-2\" ><strong>Data</strong></th>
        <th class=\"col-2\"><strong>UŻYTKOWNIK<br>KONTO</strong></th>
        <th class=\"col-8\"><strong>OPIS<br>OBIEKT</strong></th>
      </tr>
    </thead>
</table>
</div>

<div class=\"container\" style=\"background-color: transparent; width: 966px; overflow-y: scroll; height:400px;padding-bottom: 0px;\" >
<table class=\"table table\" style='font-family:\"Copperplate\", Copperplate ,monospace; font-size:90%;'>
<tbody>";
$dane=$_;
    unset($dane['requesttoken']);
    if(isset($dane) && count($dane)>2){
        foreach($dane[2] as $data){
          echo "<tr class=\"d-flex\">";
          echo "<td class=\"col-2\">".str_replace(' ','<br>',$data['timestamp'])."</td>";
          echo "<td class=\"col-2\">".trim($data['user']).'<br>'.trim($data['account'])."</td>";
          echo "<td class=\"col-8\"style=\"white-space: normal;\">".trim($data['description']).'<br>'.trim($data['object'])."</td>";
          echo "</tr>";
        }
    }
    else
    {
      echo "<tr class=\"d-flex\">";
            echo "<td class=\"col-12\" colspan=\"12\">".'<center><b>Brak wyników. Wybierz kryteria wyszukiwania i spróbuj ponownie.</b></center>'."</td>";
            echo "</tr>";
    }

echo "
</tbody>
</table>
</div>
</div>
";

  ?>