<?php

    //ini_set("include_path", '/home1/frontier/php:' . ini_get("include_path")  );

    $file = "./ChangeCoverColor.log";
    
    file_put_contents($file, "****\nBegin...\n", FILE_APPEND | LOCK_EX);
    
    $trello_key="8f428e2394c94f92a3fc7160196416c6";
    $trello_token="ATTA1693a7be50759b28080266476d0df8fee58910ebc2f50e66df38307d456fff6dF8928505";

    // List ids
    define("LEADS",          "613fbf9cb9c35d3acc700d19");
    define("ATEMPT-CONTACT", "63a346d2004ef003ff63d895");
    define("MAKE-OFFER",     "639e218672712f11be20593e");
    define("OFFER-SENT",     "639e2155567c1f0141e01ca0");
    define("TEXT-DRIP",      "6636feea2467009a47327326");
    define("REJECTED",       "639e3f7e1daf1a04f1fcdc6a");
    
    
    
    
    
    //Get the card id
    $cardid = $_REQUEST["id"];
    file_put_contents($file, "cardid:$cardid\n", FILE_APPEND | LOCK_EX);
    
    
    // Get the List id
    //$args = array(
    //    'key'   => $trello_key,
    //    'token' => $trello_token,
    //);
    $curlURL="https://api.trello.com/1/cards/$cardid/list?key=$trello_key&token=$trello_token";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curlURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents($file, "response: $response\n", FILE_APPEND | LOCK_EX);
    
    $response = json_decode($response);

    $listId = $response->{'id'};
    file_put_contents($file, "listId: $listId\n", FILE_APPEND | LOCK_EX);
    
    
    //file_put_contents($file, "to: $to\n", FILE_APPEND | LOCK_EX);
    //file_put_contents($file, "sms_text_final: $sms_text_final\n", FILE_APPEND | LOCK_EX);


   
  
    
  
    ////////  Utilities ///////////////////////////////////////////////////////////////////
        function parseResponseForId($file, $descField) {
        //file_put_contents($file, "\nDesc:\n$descField\n\n", FILE_APPEND | LOCK_EX);
        $descArr = explode("\n", $descField);
        $text="";
        $total=0;
        $arrText=array();
        $cnt=0;
        foreach ($descArr as $line) {
            // Ignore blank lines
            //if ($line =="")
            //    continue;
            $line=trim($line);
        
            $arrLine = explode(":", $line);
            if ($line =="" || count($arrLine)<2 || $arrLine[0]==""){
                continue;
            }
        
            //echo nl2br("arrLine cnt: " . count($arrLine)."\n");
        
            //Look for the max text value
            $partialLine = strtolower($arrLine[0]);
            if (strpos($partialLine, "total") !== false) {
                $total=trim($arrLine[1]);
            }
            else if ($arrLine[0]=="" ){
                continue;   
            }
            else if ($arrLine[1] != "") {
                $arrText[$cnt]=$arrLine[1];
                $cnt++;
            }
        }

        //Generate a randon number to choose a text
        $rand=rand(0,$cnt-1);
        //print_r($arrText);
        //echo nl2br("\nrand: $rand\n");
        $text=$arrText[$rand];
        //echo nl2br("\ntext: $text\n");
        return trim($text);
    }

   
   
  
    
?>
