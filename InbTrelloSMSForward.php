<?php

   // Forwards a received SMS (inbound) to Trello Card

    $trello_key="8f428e2394c94f92a3fc7160196416c6";
    $trello_token="ATTA1693a7be50759b28080266476d0df8fee58910ebc2f50e66df38307d456fff6dF8928505";
    $leads_boardId="613fbf9cb9c35d3acc700d18";
    $white_boardId="639d2a9b83344d051bba0ede";
    
    //$file = "./InbTrelloSMSForward.log";
    //file_put_contents($file, "\n****\nBegin...\n", FILE_APPEND | LOCK_EX);
    
    ////////  Utilities ////////////////////////
    function parseDescription($file, $descField, $id) {
       //file_put_contents($file, "\nid: $id\nDesc:\n$descField\n\n", FILE_APPEND | LOCK_EX);
       $descArr = explode("\n", $descField);
       $agentPhone="";
       foreach ($descArr as $line) {
           $arrLine = explode(":", $line);
           $partialLine = strtolower($arrLine[0]);
           if (strpos($partialLine, "agent phone") !== false) {
                //file_put_contents($file, "Agent Phone Field: $arrLine[0]\n", FILE_APPEND | LOCK_EX);
                $agentPhone=$arrLine[1];
                //file_put_contents($file, "Agent Phone Value: $agentPhone\n", FILE_APPEND | LOCK_EX);
                return $agentPhone;
            }
       }
    }
    
    function trimPhone($phone){
        $ph=trim($phone);
        $ph=str_replace("-","",$ph);
        $ph=str_replace("(","",$ph);
        $ph=str_replace(")","",$ph);
        return $ph;
    }
    
    function comparePhone($file, $ph1, $ph2){
        $cph=trimPhone($ph1);
        $fph=trimPhone($ph2);
        
        if(substr($cph,0,1)=="1")
        { 
            $cph = substr($cph, 1);
        }
        
        if(substr($fph,0,1)=="1")
        { 
            $fph = substr($fph, 1);
        }
        
        //file_put_contents($file, "cph: $cph fph:$fph\n", FILE_APPEND | LOCK_EX);
        if (strcmp($cph, $fph)==0){
            //file_put_contents($file, "cph1: $cph fph1:$fph\n", FILE_APPEND | LOCK_EX);
            return 1;
        }
        return 0;
    }
    
    //////Utilities End/////////////////////////////////////////////////
    
    // Get request params
    $from_number = $_REQUEST["From"];         // Sender's phone number
    $to_number = $_REQUEST["To"];            // Receiver's phone number - Plivo number
    $text = $_REQUEST["Text"];               // The text received on Plivo number

    //file_put_contents($file, "Message received  to: $to_number from: $from_number: $text\n", FILE_APPEND | LOCK_EX);

    
    ///////// Search Leads Board for card and update trello card with the received text
    $url="https://api.trello.com/1/board/$leads_boardId/cards?key=$trello_key&token=$trello_token";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($ch);
    curl_close($ch);
    //file_put_contents($file, "\n****\\nResponse...\n$response", FILE_APPEND | LOCK_EX);
    
    $response = json_decode($response);
    $cardPhone="";
    $cardId="";
    $matchFound=0;
    
    foreach($response as $node) {
       //file_put_contents($file, "\nId: $node->id", FILE_APPEND | LOCK_EX);
       //file_put_contents($file, "\nDesc: $node->desc", FILE_APPEND | LOCK_EX);
       $cardId=$node->id;
       $cardPhone=parseDescription($file, $node->desc, $cardId);
       //file_put_contents($file, "CardPhone: $cardPhone\n", FILE_APPEND | LOCK_EX);
       
       if (comparePhone($file, $cardPhone, $from_number)){
           // Match found - update the card's comment
           //file_put_contents($file, "Found match CardId:$cardId cardPhone:$cardPhone From Num:$from_number \n", FILE_APPEND | LOCK_EX);
           
           // Update the matched trello Card with the text as a comment
           $matchFound=1;
           $url="https://api.trello.com/1/cards/$cardId/actions/comments";
           
           $args = array(
            'text' => $text,
            'key'   => $trello_key,
            'token' => $trello_token,
            );
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
            $response1 = curl_exec($ch);
            curl_close($ch);
            
            // Next set the 'Text Recd' label on the card
            $url="https://api.trello.com/1/cards/$cardId/idLabels";
            $args = array(
                'value' => '613fbf9cd0ddddc18b1b4abb',   // id label for 'Text Recd' label
                'key'   => $trello_key,
                'token' => $trello_token,
            );
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
            $response1 = curl_exec($ch);
            curl_close($ch);
            
            //file_put_contents($file, "response1: $response1\n", FILE_APPEND | LOCK_EX);
           break;
       }
    }
    
    ///////// If no match found, repeat search with Whiteboard Board for card and update trello card with the received text
    if ($matchFount==0){
        $url="https://api.trello.com/1/board/$white_boardId/cards?key=$trello_key&token=$trello_token";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($ch);
        curl_close($ch);
        //file_put_contents($file, "\n****\\nResponse...\n$response", FILE_APPEND | LOCK_EX);
    
        $response = json_decode($response);
        $cardPhone="";
        $cardId="";
    
        foreach($response as $node) {
            //file_put_contents($file, "\nId: $node->id", FILE_APPEND | LOCK_EX);
            //file_put_contents($file, "\nDesc: $node->desc", FILE_APPEND | LOCK_EX);
            $cardId=$node->id;
            $cardPhone=parseDescription($file, $node->desc, $cardId);
            //file_put_contents($file, "CardPhone: $cardPhone\n", FILE_APPEND | LOCK_EX);
       
            if (comparePhone($file, $cardPhone, $from_number)){
                // Match found - update the card's comment
                //file_put_contents($file, "Found match CardId:$cardId cardPhone:$cardPhone From Num:$from_number \n", FILE_APPEND | LOCK_EX);
           
                // Update the matched trello Card with the text as a comment
                $url="https://api.trello.com/1/cards/$cardId/actions/comments";
           
                $args = array(
                'text' => $text,
                'key'   => $trello_key,
                'token' => $trello_token,
                );
            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
                $response1 = curl_exec($ch);
                curl_close($ch);
            
                $matchFound=1;
                //file_put_contents($file, "response1: $response1\n", FILE_APPEND | LOCK_EX);
            break;
            }
        }
    }

    //file_put_contents($file, "...Done\n", FILE_APPEND | LOCK_EX);
?>
