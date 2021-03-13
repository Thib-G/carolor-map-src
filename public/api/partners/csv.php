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
    m.`updated_on` AS m_updated_on
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
    ON m.`name` = p.`company`;
END;

$res = $mysqli->query($query);

$filename = 'partners.csv';

// file creation
$file = fopen($filename,"w");

fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

$data = array();

while ($row = $res->fetch_assoc()) {
  $data[] = $row;
}

$data_utf8 = $data;

foreach ($data_utf8 as $line){
 fputcsv($file, $line);
}

fclose($file);

// download
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=".$filename);
header("Content-Type: application/csv; charset=utf-8"); 

readfile($filename);

// deleting file
unlink($filename);

exit();
