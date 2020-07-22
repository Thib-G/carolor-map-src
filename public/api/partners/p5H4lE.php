<!doctype html>
<?php

include('../inc/conn.inc.php');

$query = <<<END
  SELECT
    p.`id` AS p_id,
    p.`user_id` AS p_user_id,
    p.`first_name` AS p_first_name,
    p.`last_name` AS p_last_name,
    p.`company` AS p_company,
    p.`email` AS p_email,
    p.`phone` AS p_phone,
    p.`mobile` AS p_mobile,
    p.`other` AS p_other,
    p.`website` AS p_website,
    p.`fax` AS p_fax,
    p.`notes` AS p_notes,
    p.`street_1` AS p_street_1,
    p.`street_2` AS p_street_2,
    p.`city` AS p_city,
    p.`state` AS p_state,
    p.`postal_code` AS p_postal_code,
    p.`country` AS p_country,
    p.`currency` AS p_currency,
    p.`life_stage` AS p_life_stage,
    p.`contact_owner` AS p_contact_owner,
    p.`hash` AS p_hash,
    p.`created_by` AS p_created_by,
    p.`created` AS p_created,
    m.`id` AS m_id,
    m.`name` AS m_name,
    m.`address` AS m_address,
    m.`lat` AS m_lat,
    m.`lng` AS m_lng,
    m.`zoom` AS m_zoom,
    m.`icon` AS m_icon,
    m.`popup` AS m_popup,
    m.`link` AS m_link,
    m.`blank` AS m_blank,
    m.`created_by` AS m_created_by,
    m.`created_on` AS m_created_on,
    m.`updated_by` AS m_updated_by,
    m.`updated_on` AS m_updated_on,    
    m_life_stage.meta_value AS m_life_stage,
    m_responsable_nom.meta_value AS m_responsable_nom,
    m_responsable_pr_nom.meta_value AS m_responsable_pr_nom,
    m_num_ro_entreprise.meta_value AS m_num_ro_entreprise,
    m_secteur_d_activit_.meta_value AS m_secteur_d_activit_,
    m_facebook.meta_value AS facebook,
    m_iban.meta_value AS m_iban
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
    `mod248_mmp_markers` AS m
    ON m.`name` = REPLACE(p.`company`, '\\\\', '')
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_life_stage
    ON p.id = m_life_stage.erp_people_id AND m_life_stage.meta_key = 'life_stage'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_responsable_nom
    ON p.id = m_responsable_nom.erp_people_id AND m_responsable_nom.meta_key = 'responsable_nom'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_responsable_pr_nom
    ON p.id = m_responsable_pr_nom.erp_people_id AND m_responsable_pr_nom.meta_key = 'responsable_pr_nom'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_num_ro_entreprise
    ON p.id = m_num_ro_entreprise.erp_people_id AND m_num_ro_entreprise.meta_key = 'num_ro_entreprise'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_secteur_d_activit_
    ON p.id = m_secteur_d_activit_.erp_people_id AND m_secteur_d_activit_.meta_key = 'secteur_d_activit_'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_facebook
    ON p.id = m_facebook.erp_people_id AND m_facebook.meta_key = 'facebook'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS m_iban
    ON p.id = m_iban.erp_people_id AND m_iban.meta_key = 'iban';
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
        foreach($row as $val) {
          echo '<td>'.htmlentities(str_replace('\\', '', $val)).'</td>'.PHP_EOL;
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