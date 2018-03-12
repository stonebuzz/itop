<?php

// forbid direct calls of this file
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}


class PluginItopClientRest  {

   var $resultat;
   var $error;

   public function CallAPI(array $operation, PluginItopInstance $conn, $key) {

      $aData = [];
      $aData['auth_user'] = $conn->fields['login'];
      $aData['auth_pwd']  = Toolbox::decrypt($conn->fields['password'], GLPIKEY);
      $aData['json_data'] = json_encode($operation);
      $sUrl = $conn->fields['url'].'/webservices/rest.php?version='.$conn->fields['version'];

      $response = $this->DoPostRequest($sUrl, $aData, null);
      $aResults = json_decode($response, true);

      if ($aResults != false) {

         if (!isset($aResults[$key])) {
            Toolbox::logInFile('itop', 'L\'interfaçage distant n\'a retourné aucun '.$key.' Message de l\'API : '.$aResults['message']);
            $this->error = $aResults['message'];
            return false;
         }

         $this->resultat = $aResults;
         return true;

      } else {
         $this->error = $aResults['message'];
         return false;
      }

   }


   /**
    * Helper to execute an HTTP POST request
    * Source: http://netevil.org/blog/2006/nov/http-post-from-php-without-curl
    *         originaly named after do_post_request
    */
   public function DoPostRequest($sUrl, $aData, $sOptionnalHeaders = null) {
      // $sOptionnalHeaders is a string containing additional HTTP headers that you would like to send in your request.

      $sData = http_build_query($aData);

      $aParams = ['http' => [
                        'method' => 'POST',
                        'content' => $sData,
                        'header'=> "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($sData)."\r\n",
                        ]];
      if ($sOptionnalHeaders !== null) {
         $aParams['http']['header'] .= $sOptionnalHeaders;
      }
      $ctx = stream_context_create($aParams);

      $fp = fopen($sUrl, 'rb', false, $ctx);
      if (!$fp) {
         global $php_errormsg;
         if (isset($php_errormsg)) {
            Toolbox::logInFile('itop', "Problem with $sUrl, $php_errormsg");
            return false;
         } else {
            Toolbox::logInFile('itop', "Problem with $sUrl");
            return false;
         }
      }
      $response = stream_get_contents($fp);
      if ($response === false) {
         Toolbox::logInFile('itop', "Problem reading data from $sUrl, $php_errormsg");
      }
      return $response;
   }




   public function checkOQL(PluginItopInstance $conn, $OQL, $class, $key) {

      $aOperation = [
            'operation' => 'core/check_oql',
            'class'     => $class,
            'request'     => $OQL,
            'comment'   => $conn->fields["comment"]
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {

         if ($API->resultat[$key][$attriTop] == true) {
            return true;
         } else {
            return false;
         }

      }

      return false;;

   }

}