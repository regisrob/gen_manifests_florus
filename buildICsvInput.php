<?php
/* ================================================================
 *  
 *  @author : Régis Robineau
 *  @project : Equipex Biblissima
 *  @description : output a workable image sequence out of a basic 
 *  list of image urls. Retrieve w/h via info.json (IIIF Image API)
 *
 * ================================================================
*/

// Input file
$csvInputFile = "input/Lyon-0604-liens.csv";
// Output file
$csvOutputFile = "input/sequences_iiif/Lyon-0604-sequence.csv";

// Read csv input file
$csvInputData = readCSV( $csvInputFile );

// Create empty array for the list of images (url, label)
$imgData = array();

// Loop through csv data
foreach( $csvInputData as $item ) {
  
  // Set image label
  if ( $item[8] !== ".JPG.tif" ) {
    $rv = strtolower($item[8]);
    $label = $item[7] . $rv;
  } else {
    $label = $item[7];
  }
  
  $img = array();
  $img["label"] = $label;
  //array_push($img, $label);
  
  // Set image url
  $imgUrl = implode("", $item);
  $img["imgUrl"] = $imgUrl;
  //array_push($img, $imgUrl);
  
  array_push($imgData, $img);
}

// Create empty array for output data
$csvOuputData = array();

// Loop through image data
foreach( $imgData as $img ) {
  
  $url_array = explode("/", $img["imgUrl"]);
  $docId = $url_array[5]; // document id
  $imgId = $url_array[6]; // image id
  
  $prefix = "http://florus.bm-lyon.fr/fcgi-bin/iipsrv.fcgi?iiif=/var/www/florus/web/ms/"; // iiif url prefix
  $infoUrl = $prefix . $docId . "/" . $imgId . "/info.json"; // info.json url
  
  // Get info.json
  $infoArray = json_decode( request($infoUrl), true);
  
  // Set image @id and w/h
  $img["@id"] = $infoArray["@id"];
  $img["width"] = $infoArray["width"];
  $img["height"] = $infoArray["height"];
  
  array_push($csvOuputData, $img );
}

//echo "<pre>";
//print_r($csvOuputData);
//echo "</pre>";

/* ======================================
 * ## FUNCTIONS
 * ======================================
 */

// Function call to convert to csv 
convert_to_csv( $csvOuputData, $csvOutputFile, ',');

/* 
 * Read and retrieve CSV data
 */
function readCSV( $input ){
  $file = fopen($input,"r");
  $csv = array();
  while (($row = fgetcsv($file)) !== false) {
    $csv[] = $row;
  }
  fclose($file);
	return $csv;
}

/* 
 * Output to CSV file
 */
function convert_to_csv($input_array, $output_file_name, $delimiter)
{
  /* open raw memory as file */
  $f = fopen('php://memory', 'w');
  /* loop through array */
  foreach ($input_array as $line) {
      fputcsv($f, $line, $delimiter);
  }
  /* rewrind the "file" with the csv lines */
  fseek($f, 0); 
  /* modify headers to be downloadable csv file */
  header('Content-Type: application/csv');
  header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
  /* Send file to browser for download */
  fpassthru($f);
}

/* 
 * Request with Curl and return data
 */
function request( $url ) {
  // is curl installed?
  if (!function_exists('curl_init')){
    die('CURL is not installed!');
  }
  
  // get curl handle
  $ch = curl_init();
  
  // set request url
  curl_setopt($ch,
    CURLOPT_URL,
    $url);
  
  // return response, don't print/echo
  curl_setopt($ch,
    CURLOPT_RETURNTRANSFER,
    true);
  
  // More options for curl: http://www.php.net/curl_setopt	
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

?>