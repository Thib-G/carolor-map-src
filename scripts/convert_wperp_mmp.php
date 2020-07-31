<?php

include(__DIR__.'/inc/conn.inc.php');

$secteurs = get_csv(__DIR__.'/carolor-secteurs.csv');

$mysqli->begin_transaction();

try {
  $did_nothing = True;

  $query = <<<SQL
  SELECT
    p.`id` AS p_id,
    p.`user_id` AS p_user_id,
    REPLACE(p.`first_name`, '\\\\', '') AS p_first_name,
    p.`last_name` AS p_last_name,
    REPLACE(p.`company`, '\\\\', '') AS p_company,
    CONCAT(p.`street_1`, " ", p.`street_2`, ", ", p.`postal_code`, " ",  p.`city`) AS address,
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
    p_life_stage.meta_value AS p_life_stage,
    p_responsable_nom.meta_value AS p_responsable_nom,
    p_responsable_pr_nom.meta_value AS p_responsable_pr_nom,
    p_num_ro_entreprise.meta_value AS p_num_ro_entreprise,
    p_secteur_d_activit_.meta_value AS p_secteur_d_activit_,
    p_facebook.meta_value AS p_facebook,
    p_photo_id.meta_value AS p_photo_id,
    image_path.meta_value AS image_path,
    image_metadata.meta_value AS image_metadata
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
  `mod248_erp_peoplemeta` AS p_life_stage
    ON p.id = p_life_stage.erp_people_id AND p_life_stage.meta_key = 'life_stage'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS p_responsable_nom
    ON p.id = p_responsable_nom.erp_people_id AND p_responsable_nom.meta_key = 'responsable_nom'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS p_responsable_pr_nom
    ON p.id = p_responsable_pr_nom.erp_people_id AND p_responsable_pr_nom.meta_key = 'responsable_pr_nom'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS p_num_ro_entreprise
    ON p.id = p_num_ro_entreprise.erp_people_id AND p_num_ro_entreprise.meta_key = 'num_ro_entreprise'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS p_secteur_d_activit_
    ON p.id = p_secteur_d_activit_.erp_people_id AND p_secteur_d_activit_.meta_key = 'secteur_d_activit_'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS p_facebook
    ON p.id = p_facebook.erp_people_id AND p_facebook.meta_key = 'facebook'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS p_photo_id
    ON p.id = p_photo_id.erp_people_id AND p_photo_id.meta_key = 'photo_id'
  LEFT JOIN
    `mod248_erp_peoplemeta` AS p_mmp_encoded_id
    ON p.id = p_mmp_encoded_id.erp_people_id AND p_mmp_encoded_id.meta_key = 'mmp_encoded_id'
  LEFT JOIN
    `mod248_postmeta` AS image_path
    ON CAST(p_photo_id.meta_value AS SIGNED) = image_path.post_id
    AND image_path.meta_key = '_wp_attached_file'
  LEFT JOIN
    `mod248_postmeta` AS image_metadata
    ON CAST(p_photo_id.meta_value AS SIGNED) = image_metadata.post_id
    AND image_metadata.meta_key = '_wp_attachment_metadata'
  WHERE
    (
      contact_owner = 25
      AND p.life_stage = 'subscriber'
      AND p_mmp_encoded_id.meta_value = '-1'
      AND TRIM(p.postal_code) LIKE '60__'
    )
    OR
    (
      p.life_stage = 'customer'
      AND p_mmp_encoded_id.meta_value = '-1'
    )
    AND p_secteur_d_activit_.meta_value != 'Autre';
SQL;

  $insert_query_mmp = <<<SQL
  INSERT INTO mod248_mmp_markers (
    name,
    address,
    lat,
    lng,
    zoom,
    icon,
    popup,
    link,
    blank,
    created_by,
    created_on,
    updated_by,
    updated_on
  ) VALUES (
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?
  );
SQL;

  $insert_query_mmp_relationships = <<<SQL
  INSERT INTO mod248_mmp_relationships (
    map_id,
    type_id,
    object_id
  ) VALUES (
    ?,
    ?,
    ?
  );
SQL;

  $update_peoplemeta = <<<SQL
  UPDATE mod248_erp_peoplemeta
  SET meta_value = ?
  WHERE meta_key = 'mmp_encoded_id'
  AND erp_people_id = ?;
SQL;

  $stmt_insert_mmp = $mysqli->prepare($insert_query_mmp);
  $stmt_insert_mmp_relationships = $mysqli->prepare($insert_query_mmp_relationships);
  $stmt_update_peoplemeta = $mysqli->prepare($update_peoplemeta);

  $result = $mysqli->query($query) or die($mysqli->error);

  while ($row = $result->fetch_assoc()) {
    $row = str_replace('\\', '', $row);
    $did_nothing = False;

    $secteur = find_in_array($secteurs, 'category', $row['p_secteur_d_activit_']);

    if ($secteur) {
      $coords = get_geoloc($row['address'].', Belgique');

      // Columns
      $name = $row['p_company'];
      $address = $row['address'];
      if ($coords) {
        $lat = $coords->lat;
        $lng = $coords->lng;
      } else {
        $lat = 50.411653;
        $lng = 4.444562;
      }
      $zoom = 12;
      $icon = $secteur['icon'];
      $popup = '<h2>'.$row['p_company'].'</h2>'.PHP_EOL;
      if ($row['p_photo_id'] && $row['image_path'] && $row['image_metadata']) {
        $image_metadata = unserialize($row['image_metadata']);
        $popup .= '<img class="alignnone wp-image-'.$row['p_photo_id'].' size-full" src="/wp-content/uploads/'.$row['image_path'].'" alt="" width="'.$image_metadata['width'].'" height="'.$image_metadata['height'].'" >'.PHP_EOL;
      }
      if ($row['p_phone']) {
        $popup .= '<img class="alignnone wp-image-760" src="/wp-content/uploads/2019/03/icone-phone-bleu-clair-150x150.png" alt="" width="50" height="50"> '.$row['p_phone'].PHP_EOL;
      }
      if ($row['p_email']) {
        $popup .= '<p data-wp-editing="1"><img class="alignnone wp-image-98" src="/wp-content/uploads/2018/07/email-icon-150x150.png" alt="" width="50" height="50" /><a href="mailto:'.trim($row['p_email']).'" target="_blank" rel="noopener">'.htmlentities(trim($row['p_email'])).'</a></p>'.PHP_EOL;
      }
      if ($row['p_website']) {
        $popup .= '<img class="alignnone wp-image-759" src="/wp-content/uploads/2019/03/icone-adresse-png-150x150.png" alt="" width="50" height="51" /> <a href="'.trim($row['p_website']).'"  target="_blank" rel="noopener">'.htmlentities(trim($row['p_website'])).'</a>'.PHP_EOL;
      }
      if ($row['p_facebook']) {
        $popup .= '<img class="alignnone wp-image-708" src="/wp-content/uploads/2019/03/facebook-770688_960_720-150x150.png" alt="" width="50" height="50" /> <a href="'.trim($row['p_facebook']).'"  target="_blank" rel="noopener">'.htmlentities(trim($row['p_facebook'])).'</a>'.PHP_EOL;
      }
      $link = '';
      $blank = 1;
      $created_by = 'tgoe';
      $created_on = current_time('mysql');
      $updated_by = 'tgoe';
      $updated_on = current_time('mysql');

      $stmt_insert_mmp->bind_param('ssddisssissss',
        $name,
        $address,
        $lat,
        $lng,
        $zoom,
        $icon,
        $popup,
        $link,
        $blank,
        $created_by,
        $created_on,
        $updated_by,
        $updated_on
      );
      $stmt_insert_mmp->execute() or die($mysqli->error);
      $mmp_id = $mysqli->insert_id;

      $map_id = $secteur['map_id'];
      $type_id = 2;

      $stmt_insert_mmp_relationships->bind_param('iii',
        $map_id,
        $type_id,
        $mmp_id
      );
      $stmt_insert_mmp_relationships->execute() or die($mysqli->error);

      $stmt_update_peoplemeta->bind_param('si',
        $mmp_id,
        $row['p_id']
      );
      $stmt_update_peoplemeta->execute() or die($mysqli->error);

      echo 'People ID: '.$row['p_id'].PHP_EOL;
      echo 'MMP ID: '.$mmp_id.PHP_EOL;
    }
  }

  $mysqli->commit();

  if ($did_nothing) {
    echo 'Aucun transfert de WPERP à MapsMarkersPro à signaler'.PHP_EOL;
  }

} catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), "\n";
  $mysqli->rollback();
}

function get_csv($filename) {
  $csv = array_map('str_getcsv', file($filename));
  array_walk($csv, function(&$a) use ($csv) {
    $a = array_combine($csv[0], $a);
  });
  array_shift($csv); # remove column header
  return $csv;
}

function find_in_array($list, $column_name, $search_value) {
  //return array index of searched item
  $key = array_search($search_value, array_column($list, $column_name));
  return $list[$key]; //return array item
}

function get_geoloc($addr) {
  //$url = 'https://photon.mapsmarker.com/pro/api?q='.urlencode($addr).'&limit=10&lang=fr';
  $url = 'https://places-dsn.algolia.net/1/places/query?query='.urlencode($addr).'&hitsPerPage=10&language=frtrue';

  $options = array(
    'http' => array(
      'header' => 'Content-type: application/json\n',
      'method' => 'GET'
    ), 'ssl' => array(
      'verify_peer' => false
    )
  );

  $context = stream_context_create($options);
  $response = file_get_contents($url, false, $context);

  $json = json_decode($response);
  $coords = $json->hits[0]->_geoloc;
  return $coords;
}

function current_time( $type, $gmt = 0 ) {
  // Don't use non-GMT timestamp, unless you know the difference and really need to.
  if ( 'timestamp' === $type || 'U' === $type ) {
      return $gmt ? time() : time() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
  }

  if ( 'mysql' === $type ) {
      $type = 'Y-m-d H:i:s';
  }

  $timezone = new DateTimeZone( 'Europe/Brussels' );
  $datetime = new DateTime( 'now', $timezone );

  return $datetime->format( $type );
}
