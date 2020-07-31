<?php

include(__DIR__.'/inc/conn.inc.php');

$mysqli->begin_transaction();

try {
  $did_nothing = True;

  $query = <<<SQL
  SELECT
    e.*,
    identit_.meta_value AS identit_,
    email.meta_value AS email,
    portable.meta_value AS portable,
    text_3.meta_value AS text_3,
    secteur_d_activit_.meta_value AS secteur_d_activit_,
    text_6.meta_value AS text_6,
    adresse.meta_value AS adresse,
    code_postal.meta_value AS code_postal,
    localit_.meta_value AS localit_,
    num_ro_d_entreprise.meta_value AS num_ro_d_entreprise,
    image.meta_value AS image,
    facebook.meta_value AS facebook,
    site.meta_value AS site,
    bourse.meta_value != '' AS bourse,
    ind_pendance.meta_value != '' AS ind_pendance,
    charte.meta_value != '' AS charte,
    circularit_.meta_value != '' AS circularit_,
    asbl.meta_value != '' AS asbl,
    rgpd.meta_value != '' AS rgpd,
    iban.meta_value AS iban
  FROM
    mod248_weforms_entries e
  LEFT JOIN mod248_weforms_entrymeta AS identit_
    ON e.id = identit_.weforms_entry_id AND identit_.meta_key = 'identit_'
  LEFT JOIN mod248_weforms_entrymeta AS email
    ON e.id = email.weforms_entry_id AND email.meta_key = 'email'
  LEFT JOIN mod248_weforms_entrymeta AS portable
    ON e.id = portable.weforms_entry_id AND portable.meta_key = 'portable'
  LEFT JOIN mod248_weforms_entrymeta AS text_3
    ON e.id = text_3.weforms_entry_id AND text_3.meta_key = 'text_3'
  LEFT JOIN mod248_weforms_entrymeta AS secteur_d_activit_
    ON e.id = secteur_d_activit_.weforms_entry_id AND secteur_d_activit_.meta_key = 'secteur_d_activit_'
  LEFT JOIN mod248_weforms_entrymeta AS text_6
    ON e.id = text_6.weforms_entry_id AND text_6.meta_key = 'text_6'
  LEFT JOIN mod248_weforms_entrymeta AS adresse
    ON e.id = adresse.weforms_entry_id AND adresse.meta_key = 'adresse'
  LEFT JOIN mod248_weforms_entrymeta AS code_postal
    ON e.id = code_postal.weforms_entry_id AND code_postal.meta_key = 'code_postal'
  LEFT JOIN mod248_weforms_entrymeta AS localit_
    ON e.id = localit_.weforms_entry_id AND localit_.meta_key = 'localit_'
  LEFT JOIN mod248_weforms_entrymeta AS num_ro_d_entreprise
    ON e.id = num_ro_d_entreprise.weforms_entry_id AND num_ro_d_entreprise.meta_key = 'num_ro_d_entreprise'
  LEFT JOIN mod248_weforms_entrymeta AS image
    ON e.id = image.weforms_entry_id AND image.meta_key = 'image'
  LEFT JOIN mod248_weforms_entrymeta AS facebook
    ON e.id = facebook.weforms_entry_id AND facebook.meta_key = 'facebook'
  LEFT JOIN mod248_weforms_entrymeta AS site
    ON e.id = site.weforms_entry_id AND site.meta_key = 'site'
  LEFT JOIN mod248_weforms_entrymeta AS bourse
    ON e.id = bourse.weforms_entry_id AND bourse.meta_key = 'bourse'
  LEFT JOIN mod248_weforms_entrymeta AS ind_pendance
    ON e.id = ind_pendance.weforms_entry_id AND ind_pendance.meta_key = 'ind_pendance'
  LEFT JOIN mod248_weforms_entrymeta AS charte
    ON e.id = charte.weforms_entry_id AND charte.meta_key = 'charte'
  LEFT JOIN mod248_weforms_entrymeta AS circularit_
    ON e.id = circularit_.weforms_entry_id AND circularit_.meta_key = 'circularit_'
  LEFT JOIN mod248_weforms_entrymeta AS asbl
    ON e.id = asbl.weforms_entry_id AND asbl.meta_key = 'asbl'
  LEFT JOIN mod248_weforms_entrymeta AS rgpd
    ON e.id = rgpd.weforms_entry_id AND rgpd.meta_key = 'rgpd'
  LEFT JOIN mod248_weforms_entrymeta AS iban
    ON e.id = iban.weforms_entry_id AND iban.meta_key = 'iban'
  WHERE
    e.form_id = 2082
    AND e.id NOT IN (SELECT other+0 AS id FROM mod248_erp_peoples WHERE other IS NOT NULL)
SQL;

  $insert_query_peoples = <<<SQL
  INSERT INTO mod248_erp_peoples (
    user_id,
    first_name,
    last_name,
    company,
    email,
    phone,
    mobile,
    other,
    website,
    fax,
    notes,
    street_1,
    street_2,
    city,
    state,
    postal_code,
    country,
    currency,
    life_stage,
    contact_owner,
    hash,
    created_by,
    created
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
  
  $insert_query_people_type_relations = 'INSERT INTO mod248_erp_people_type_relations (people_id, people_types_id) VALUES (?, 2);';
  $insert_query_peoplemeta = 'INSERT INTO mod248_erp_peoplemeta (erp_people_id, meta_key, meta_value) VALUES (?, ?, ?);';

  $stmt_peoples = $mysqli->prepare($insert_query_peoples);
  $stmt_people_type_relations = $mysqli->prepare($insert_query_people_type_relations);
  $stmt_peoplemeta = $mysqli->prepare($insert_query_peoplemeta);
  
  $result = $mysqli->query($query);
  
  while ($row = $result->fetch_assoc()) {
    $did_nothing = False;
    // Conditions to stay OK
    $ok = True;
    $notes = '';

    if ($row['bourse'] !== '1') {
      $ok = False;
      $notes .= 'Coche bourse manquante'.PHP_EOL;
    }
    if ($row['ind_pendance'] !== '1') {
      $ok = False;
      $notes .= 'Coche indépendance manquante'.PHP_EOL;
    }
    if ($row['charte'] !== '1') {
      $ok = False;
      $notes .= 'Coche charte manquante'.PHP_EOL;
    }
    if ($row['circularit_'] !== '1') {
      $ok = False;
      $notes .= 'Coche circularité manquante'.PHP_EOL;
    }
    if ($row['asbl'] !== '1') {
      $ok = False;
      $notes .= 'Coche ASBL manquante'.PHP_EOL;
    }
    if ($row['rgpd'] !== '1') {
      $ok = False;
      $notes .= 'Coche RGPD manquante'.PHP_EOL;
    }
    if ($row['secteur_d_activit_'] == 'Autre') {
      $ok = False;
      $notes .= 'Le secteur activité est Autre';
    }

    $responsable_pr_nom = '';
    $responsable_nom = '';
    $sep = '| | ';
    if (strpos($row['identit_'], $sep) !== false) {
      $first_last = explode($sep, $row['identit_']);
      $responsable_pr_nom = $first_last[0];
      $responsable_nom = $first_last[1];
    } 
    $user_id = 0;    // user_id,
    $first_name = $row['text_3'];   // first_name,
    $last_name = '(company)';    // last_name,
    $email = strtolower(trim($row['email']));    // company,
    $company = $row['text_3'];    // email,
    $phone = $row['text_6'];    // phone,
    $mobile = $row['portable'];    // mobile,
    $other = $row['id'];    // other,
    $website = $row['site'];    // website,
    $fax = '';    // fax,
    $notes = $notes;    // notes,
    $street_1 = $row['adresse'];    // street_1,
    $street_2 = '';    // street_2,
    $city = $row['localit_'];    // city,
    $state = '';    // state,
    $postal_code = $row['code_postal'];    // postal_code,
    $country = 'BE';    // country,
    $currency = null;    // currency,
    $life_stage = 'subscriber';    // life_stage,
    $contact_owner = $ok ? 25 : 26;    // contact_owner,
    $hash = sha1( microtime() . 'erp-unique-hash-id' . $email );    // hash,
    $created_by = 3;    // created_by,
    $created = current_time('mysql');    // created

    $image_id = '0';
    if (unserialize($row['image'])) {
      $image_unserialized = unserialize($row['image']);
      $image_id = $image_unserialized[0];
    }

    $stmt_peoples->bind_param('issssssssssssssssssssss', 
      $user_id,  // user_id,
      $first_name,  // first_name,
      $last_name,  // last_name,
      $company,  // company,
      $email,  // email,
      $phone,  // phone,
      $mobile,  // mobile,
      $other,  // other,
      $website,  // website,
      $fax,  // fax,
      $notes,  // notes,
      $street_1,  // street_1,
      $street_2,  // street_2,
      $city,  // city,
      $state,  // state,
      $postal_code,  // postal_code,
      $country,  // country,
      $currency,  // currency,
      $life_stage,  // life_stage,
      $contact_owner,  // contact_owner,
      $hash,  // hash,
      $created_by,  // created_by,
      $created  // created
    );
    $stmt_peoples->execute();
    $people_id = $mysqli->insert_id;
    echo 'Created ID: '.$people_id.PHP_EOL;
    $stmt_people_type_relations->bind_param('i', $people_id);
    $stmt_people_type_relations->execute();

    $keyval = array(
      'responsable_nom' => $responsable_nom,
      'responsable_pr_nom' => $responsable_pr_nom,
      'num_ro_entreprise' => $row['num_ro_d_entreprise'],
      'secteur_d_activit_' => $row['secteur_d_activit_'],
      'coche_bourse' => ($row['bourse'] === '1' ? 'a:1:{i:0;s:1:"1";}' : ''),
      'coche_ind_pendance' => ($row['ind_pendance'] === '1' ? 'a:1:{i:0;s:1:"1";}' : ''),
      'coche_charte' => ($row['charte'] === '1' ? 'a:1:{i:0;s:1:"1";}' : ''),
      'coche_circularit_' => ($row['circularit_'] === '1' ? 'a:1:{i:0;s:1:"1";}' : ''),
      'coche_asbl' => ($row['asbl'] === '1' ? 'a:1:{i:0;s:1:"1";}' : ''),
      'coche_rgpd' => ($row['rgpd'] === '1' ? 'a:1:{i:0;s:1:"1";}' : ''),
      'facebook' => $row['facebook'],
      'mmp_encoded_id' => '-1',
      'photo_id' => $image_id,
      'iban' => $row['iban']
    );
    foreach ($keyval as $k => $v) {
      $stmt_peoplemeta->bind_param('iss', $people_id, $k, $v);
      $stmt_peoplemeta->execute();
    }
  }
  $mysqli->commit();

  if ($did_nothing) {
    echo 'Aucun transfert de WeForms à WPERP à signaler'.PHP_EOL;
  }

} catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), "\n";
  $mysqli->rollback();
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
