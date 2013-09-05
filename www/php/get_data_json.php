<?php
/*
 * Converts CSV to JSON
 * Example uses Google Spreadsheet CSV feed
 * csvToArray function I think I found on php.net
 */
    
    header('Content-type: application/json');
    
 
    // Set your CSV feed
    $feed = 'http://maison.coroller.com/php/get_data_csv.php?'.$_SERVER['QUERY_STRING'];
    
    // Arrays we'll use later
    $keys = array();
    $newArray = array();
    
    // Function to convert CSV into associative array
    function csvToArray($file, $delimiter) 
    { 
        if (($handle = fopen($file, 'r')) !== FALSE) 
        { 
            $i = 0; 
            while (($lineArray = fgetcsv($handle, 4000, $delimiter, '"')) !== FALSE) 
            { 
                for ($j = 0; $j < count($lineArray); $j++) 
                { 
                    if($lineArray[$j] != '')
                    {
                      $arr[$i][$j] = $lineArray[$j]; 
                    }
                } 
                $i++; 
             } 
            fclose($handle); 
        }
        return $arr; 
     } 
                                                    
     // Do it
     $data = csvToArray($feed, ',');
                                                    
     // Set number of elements (minus 1 because we shift off the first row)
     $count = count($data) - 1;
                                                      
     //Use first row for names  
     $labels = array_shift($data);  
                                                      
     foreach ($labels as $label) 
     {
       $keys[] = $label;
     }

     // Bring it all together
     for ($j = 0; $j < $count; $j++) 
     {
       $d = array_combine($keys, $data[$j]);
       $newArray[$j] = $d;
     }

     // Print it out as JSON
     echo json_encode($newArray);
                                                                
?>

