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
    
  $myQuery=<<<EOF
    SELECT * FROM models;
EOF;

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

  // variables
  // $_GET['geohash']   ->   points hash
  // $_GET['orcid']     ->   orcid
  // $_GET['doi']       ->   doi

  // first query gets all of the entries under the hash
  $myQuery = "SELECT * FROM signed_models WHERE GEOHASH = '" . $_GET['geohash'] . "';";

  $ret = $db->query($myQuery);

  $emptyString = "";

  $numrow = 0;
  while($row = $ret->fetchArray(SQLITE3_ASSOC) )
  {
    $numrow = $numrow + 1;
  }

  if( $numrow == 0 )
  {
    $myQuery = "SELECT * FROM signed_models WHERE DOI = '" . $_GET['doi'] . "';";

    $ret = $db->query($myQuery);

    $numrow = 0;
    while($row = $ret->fetchArray(SQLITE3_ASSOC) )
    {
      $numrow = $numrow + 1;
    }

    if( $numrow == 0 )
    {
      echo "No such file found. Cannot verify validity of file.\n";
    }
    else
    {
      echo "File found with different point network. Your file may be invalid!\n";
    }
  }
  else
  {
    // check for whether the geometry has a doi and an orcid or not
    if( strcmp($emptyString, $_GET['orcid']) == 0 || strcmp($emptyString, $_GET['doi']) == 0 )
    {
      while($row = $ret->fetchArray(SQLITE3_ASSOC) )
      {
        echo "File has been found, but with the following providence: ORCID ID (" . $row['ORCID'] . ") and DOI (" . $row['DOI'] . ")";
      }
    }
    else
    {
      while($row = $ret->fetchArray(SQLITE3_ASSOC) )
      {
        if( strcmp($_GET['orcid'], $row['ORCID']) == 0 && strcmp($_GET['doi'], $row['DOI']) == 0 )
        {
          echo "File has been found, and is valid!\n";
        }
        else
        {
          echo "File has been found, but with a different providence:\n" . "ORCID ID: " . $row['ORCID'] . "\nDOI: " . $row['DOI'] . "\n";
        }
      }
    }
  }
  
  if(!$ret){
    echo $db->lastErrorMsg();
    return;
  }

  $db->close();

?>