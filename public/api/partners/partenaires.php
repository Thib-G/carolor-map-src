<!doctype html>
<?php

include('../inc/conn.inc.php');

$query = <<<END
  SELECT
    m.`id` AS m_id,
    p.`id` AS p_id,
    p.`company` AS p_company,
    CONCAT(p.`street_1`, " ", p.`street_2`, " - ", p.`postal_code`, " ",  p.`city`) AS p_address,
    m.address AS m_address,
    p.`email` AS p_email,
    p.`phone` AS p_phone,
    p.`mobile` AS p_mobile,
    p.`postal_code` AS p_postal_code,
    p.`website` AS p_website,
    p.`contact_owner` AS p_contact_owner,
    u.`display_name` AS u_display_name,
    m_secteur_d_activit_.meta_value AS m_secteur_d_activit_,
    m_iban.meta_value AS m_iban,
    civilit_.meta_value AS civilit_,
    mobile_activit_.meta_value AS mobile_activit_,
    phone_number_responsable.meta_value AS phone_number_responsable
  FROM
    `mod248_erp_peoples` AS p
  JOIN
  (
    SELECT DISTINCT 
      people_id,
      people_types_id
    FROM
      `mod248_erp_people_type_relations`
    WHERE
      deleted_at IS NULL
      AND people_types_id = 2
  ) AS mapping
    ON p.id = mapping.people_id
  LEFT JOIN
    `mod248_users` u
    ON u.`ID` = p.contact_owner
  LEFT JOIN
    `mod248_mmp_markers` AS m
    ON m.`name` = REPLACE(p.`company`, '\\\\', '')
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_secteur_d_activit_
    ON p.id = m_secteur_d_activit_.erp_people_id AND m_secteur_d_activit_.meta_key = 'secteur_d_activit_'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_iban
    ON p.id = m_iban.erp_people_id AND m_iban.meta_key = 'iban'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS civilit_
    ON p.id = civilit_.erp_people_id AND civilit_.meta_key = 'civilit_'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS mobile_activit_
    ON p.id = mobile_activit_.erp_people_id AND mobile_activit_.meta_key = 'mobile_activi_'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS phone_number_responsable
    ON p.id = phone_number_responsable.erp_people_id AND phone_number_responsable.meta_key = 'phone_number_responsable'
  WHERE 
    p.`life_stage` = 'customer'
  ORDER BY
    m.`id` DESC;
END;

$res = $mysqli->query($query);

$arr = utf8ize($res->fetch_all(MYSQLI_ASSOC));

function utf8ize($d) {
  if (is_array($d)) {
      foreach ($d as $k => $v) {
          $d[$k] = utf8ize($v);
      }
  } else if (is_string ($d)) {
      return utf8_encode($d);
  }
  return $d;
}

?><html lang="en">
<head>
  <meta charset="utf-8">

  <title>Partenaires Carol'Or</title>
  <meta name="description" content="Partenaires Carol'Or">
  <meta name="author" content="carolor.org">

  <link rel="stylesheet" href="./css/theme.default.min.css">
  <style>
  body {
    font-family: Arial, Helvetica, sans-serif;
  }

  table {
    border-collapse: collapse;
    font-size: 10pt;
  }

  table, th, td {
    border: 1px solid black;
  }
  </style>
</head>

<body>
<table id="partners">
  <thead>
    <tr>
      <?php 
        foreach(array_keys($arr[0]) as $key) {
          echo '<th>'.$key.'</th>'.PHP_EOL;
        }
      ?>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($arr as $row) {
        echo '<tr>';
        foreach($row as $key => $val) {
          if ($key == 'p_id') {
            echo '<td><a href="https://carolor.org/wp-admin/admin.php?page=erp-crm&amp;section=companies&amp;action=view&amp;id='.$val.'">'.htmlentities(str_replace('\\', '', $val)).'</a></td>'.PHP_EOL;
          } elseif ($key == 'm_id') {
            echo '<td><a href="https://carolor.org/wp-admin/admin.php?page=mapsmarkerpro_marker&amp;id='.$val.'">'.htmlentities(str_replace('\\', '', $val)).'</a></td>'.PHP_EOL;
          } else {
            echo '<td>'.htmlentities(str_replace('\\', '', $val)).'</td>'.PHP_EOL;
          }
        }
        echo '</tr>';
      }
    ?>
  </tbody>
</table>
<script src="./js/jquery-3.4.1.min.js"></script>
<script src="./js/jquery.tablesorter.min.js"></script>
<script>
$(function() {
  $("#partners").tablesorter({ sortList: [[0,1]] });
});
</script>
</body>
</html>