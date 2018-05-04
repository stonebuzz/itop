<?php
/*
   ------------------------------------------------------------------------
   MIT License

   Copyright (c) 2017 Teclib'

   Permission is hereby granted, free of charge, to any person obtaining a copy
   of this software and associated documentation files (the "Software"), to deal
   in the Software without restriction, including without limitation the rights
   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   copies of the Software, and to permit persons to whom the Software is
   furnished to do so, subject to the following conditions:

   The above copyright notice and this permission notice shall be included in all
   copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
   SOFTWARE.

   ------------------------------------------------------------------------

   @package   iTop Plugin
   @author    Teclib'
   @copyright Copyright (c) 2017 Teclib'
   @license   MIT
              https://opensource.org/licenses/MIT
   @link      https://github.com/pluginsGLPI/itop
   @since     2017

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginItopToolbox {

   static function filterData($values, $fields = ['name']) {
      $filteredData = [];

      foreach ($values as $data) {
         foreach ($fields as $field) {
            if (isset($data[$field])) {
               if ($data[$field] == '') {
                  continue;
               } else if ($field != 'org_id'
                     && preg_match("/[0-9]-[a-zA-Z]+-0/", $data[$field])) {
                  $data[$field] = '';
               }
            }
         }

         $filteredData[] = $data;
      }
      return $filteredData;
   }

   static public function findOrCreate($itemtype, $reconciliationParams, $params) {
      $reconciliationString = '';

      foreach ($reconciliationParams as $param) {
         if (!isset($params[$param])) {
            echo 'Error : '.$param.' is not present in params array';
            return false;
         }

         $reconciliationString .= $param.' = "'.$params[$param].'" AND ';
      }

      $obj = new $itemtype();
      $result = $obj->find(rtrim($reconciliationString, " AND "), '', 1);

      switch (count($result)) {
         case 0:
            // no object found, create a new one
            $id = $obj->add($params);
            $result = $obj;
            break;

         case 1:
            // one object found, retrieving id and update with new vales
            $currobj = array_shift($result);
            $params['id'] = $currobj['id'];

            if ($obj->update($params)) {
               $result = $obj;
            }
            break;
      }
      return $result;
   }

   static public function readParameter($sParamName, $defaultValue = '') {
      global $argv;

      $retValue = $defaultValue;
      foreach ($argv as $iArg => $sArg) {
         if (preg_match('/^--'.$sParamName.'=(.*)$/', $sArg, $aMatches)) {
            $retValue = $aMatches[1];
         }
      }
      return $retValue;
   }

   static function readConfiguration() {
      //config-local.ini is a user defined configuration file
      //if present it is loaded instead of the dfault config.ini file
      if (file_exists(GLPI_ROOT.'/plugins/itop/config/config-local.ini')) {
         return self::readConfigurationFile('config-local.ini');
      } else {
         return self::readConfigurationFile('config.ini');
      }
   }

   static function readItopConfiguration() {
      return self::readConfigurationFile('itop.ini');
   }

   static function readConfigurationFile($file) {
      $config = parse_ini_file(GLPI_ROOT.'/plugins/itop/config/'.$file, true);
      if (empty($config)) {
         return false;
      }
      return $config;
   }

   static function getGlpiUniqueID($glpi_server_id, $itemtype, $glpi_id) {
      return "CONCAT_WS('-', $glpi_server_id, '$itemtype', $glpi_id)";
   }


      /**
    * Helper to execute an HTTP POST request
    * Source: http://netevil.org/blog/2006/nov/http-post-from-php-without-curl
    *         originaly named after do_post_request
    * Does not require cUrl but requires openssl for performing https POSTs.
    *
    * @param string $sUrl The URL to POST the data to
    * @param hash $aData The data to POST as an array('param_name' => value)
    * @param string $sOptionnalHeaders Additional HTTP headers as a string with newlines between headers
    * @param hash $aResponseHeaders An array to be filled with reponse headers: WARNING: the actual content of the array depends on the library used: cURL or fopen, test with both !! See: http://fr.php.net/manual/en/function.curl-getinfo.php
    * @param int $iConnectionTimeout Maximum time to wait either for the establishment of the connection OR the response data
    * @return string The result of the POST request
    * @throws Exception
    */
   static public function DoPostRequest($sUrl, $aData, $sOptionnalHeaders = null, &$aResponseHeaders = null, $iConnectionTimeout = 120) {
      // $sOptionnalHeaders is a string containing additional HTTP headers that you would like to send in your request.

         // If cURL is available, let's use it, since it provides a greater control over the various HTTP/SSL options
         // For instance fopen does not allow to work around the bug: http://stackoverflow.com/questions/18191672/php-curl-ssl-routinesssl23-get-server-helloreason1112
         // by setting the SSLVERSION to 3 as done below.
         $aHeaders = explode("\n", $sOptionnalHeaders);
         $aHTTPHeaders = [];
      foreach ($aHeaders as $sHeaderString) {
         if (preg_match('/^([^:]): (.+)$/', $sHeaderString, $aMatches)) {
            $aHTTPHeaders[$aMatches[1]] = $aMatches[2];
         }
      }
         $aOptions = [
            CURLOPT_RETURNTRANSFER  => true,     // return the content of the request
            CURLOPT_HEADER       => false,    // don't return the headers in the output
            CURLOPT_FOLLOWLOCATION  => true,     // follow redirects
            CURLOPT_ENCODING     => "",       // handle all encodings
            CURLOPT_USERAGENT    => "spider", // who am i
            CURLOPT_AUTOREFERER     => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT  => (int)$iConnectionTimeout,      // timeout on connect
            CURLOPT_TIMEOUT         => (int)$iConnectionTimeout,      // timeout on response
            CURLOPT_MAXREDIRS    => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYHOST  => 0,     // Disabled SSL Cert checks
            CURLOPT_SSL_VERIFYPEER  => 0,     // Disabled SSL Cert checks
            //CURLOPT_SSLVERSION    => 3,     // MUST to prevent a strange SSL error: http://stackoverflow.com/questions/18191672/php-curl-ssl-routinesssl23-get-server-helloreason1112
            CURLOPT_POST         => count($aData),
            CURLOPT_POSTFIELDS      => http_build_query($aData),
            CURLOPT_HTTPHEADER      => $aHTTPHeaders,
         ];

         $ch = curl_init($sUrl);
         curl_setopt_array($ch, $aOptions);
         $response = curl_exec($ch);
         $iErr = curl_errno($ch);
         $sErrMsg = curl_error( $ch );
         $aHeaders = curl_getinfo( $ch );
         if ($iErr !== 0) {

            Toolbox::logInFile('itop', 'SYNCHRO : Problem opening URL: $sUrl, $sErrMsg');
            throw new Exception("Problem opening URL: $sUrl, $sErrMsg");
         }
         if (is_array($aResponseHeaders)) {
            $aHeaders = curl_getinfo($ch);
            foreach ($aHeaders as $sCode => $sValue) {
               $sName = str_replace(' ', '-', ucwords(str_replace('_', ' ', $sCode))); // Transform "content_type" into "Content-Type"
               $aResponseHeaders[$sName] = $sValue;
            }
         }
         curl_close( $ch );

   }




}
