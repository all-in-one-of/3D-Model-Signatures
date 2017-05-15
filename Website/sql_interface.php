<?php 

  class myDB extends SQLite3
  {
     function __construct()
     {
        $this->open('signatures.db');
     }
  }
  $db = new myDB();
  if(!$db){
     echo $db->lastErrorMsg();
     return;
  } 
    
  // order of precedence
  // 1) check geo hash against query
  //     - if orcid + doi is the same, it is signed + valid
  //     - if no orcid or doi on model, it is unsigned, return the query
  //     - if orcid + doi are different, it is signed + invalid
  // 2) no geohash present
  //     - check for same doi
  //       - signed and invalid (geometry has been changed)
  // 3) no geohash and no doi
  //     - item is not found -> unsigned, unsure validity
  $myQuery = "INSERT INTO signed_models (HASH,ORCID,DOI,PARADATA,GEOHASH)\n VALUES ('" . $_GET['hash'] . "', '" . $_GET['orcid'] . "', '" . $_GET['doi'] . "', '" . $_GET['paradata'] . "', '" . $_GET['geohash'] . "');";

  echo $myQuery;

  $ret = $db->exec($myQuery);
  if(!$ret){
    echo $db->lastErrorMsg();
  } else {
    echo "successfully inserted dummy data with ". $myQuery;
  }
  $db->close();

?>