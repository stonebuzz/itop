<?php
/*
 -------------------------------------------------------------------------
 More categories plugin for GLPI
 Copyright (C) 2003-2011 by the  More categories Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of  More categories.

  More categories is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

  More categories is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with  More categories. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginItopInstance extends CommonDropdown {

   static function getTypeName($nb = 0) {
      return __("iTop instance", "itop");
   }


   function getSearchOptionsNew() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Characteristics')
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => self::getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => self::getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => self::getTable(),
         'field'              => 'url',
         'name'               => __('Url', 'itop'),
         'datatype'           => 'text',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => self::getTable(),
         'field'              => 'login',
         'name'               => __('Login'),
         'datatype'           => 'text',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => self::getTable(),
         'field'              => 'version',
         'name'               => __('Version'),
         'datatype'           => 'text',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => self::getTable(),
         'field'              => 'comment',
         'name'               => __('Comment', 'itop'),
         'datatype'           => 'text',
         'massiveaction'      => false
      ];

      return $tab;

   }


   public function showForm($ID, $options = []) {

      $this->getFromDB($ID);

      $options['colspan'] = 2;
      $options['target']  = Toolbox::getItemTypeFormURL(__CLASS__);
      $this->showFormHeader($options);

      echo '<table class="tab_cadre_fixe">';

      echo "<tr class='line0'><td>" . __('Name') . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "</tr>";

      echo "<tr class='line0'><td>" . __('Url', 'itop') . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      Html::autocompletionTextField($this, "url");
      echo '&nbsp ex :http://127.0.0.1/itop';
      echo "</td>";
      echo "</tr>";

      echo "<tr class='line0'><td>" . __('Login') . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      Html::autocompletionTextField($this, "login");
      echo "</td>";
      echo "</tr>";

      echo "<tr class='line0'><td>" . __('Password') . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      $value = Toolbox::decrypt($this->getField('password'), GLPIKEY);
      echo "<input type='password' name='password' value='".$value."' autocomplete='off'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='line0'><td>" . __('Comment', 'itop') . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      Html::autocompletionTextField($this, "comment");
      echo '&nbsp'.__('Use for history', 'itop');
      echo "</td>";
      echo "</tr>";

      echo "<tr class='line0'><td>" . __('Version') . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      Html::autocompletionTextField($this, "version");
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>".__("Test", 'itop')." </td>";
      echo "<td>";
      echo "<a href='#' class='vsubmit' onclick='testConnection()'>".__('Test', 'itophelpdesk')."</a>";
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td></td>";
      echo "<td>";
      echo "<span id='result'></span>";

      echo "</td>";
      echo "</tr>";

      echo "</table>";

      if ($ID > 0) {
         echo "<input value='".__('Export to JSON', 'itop')."' name='getJSON' class='submit' type='submit'>";
         echo Html::hidden('json_name', ['value' => trim($this->fields['name'])]);
         $this->createJsonFile();

      }

      $this->showFormButtons($options);

      return true;
   }




   public function checkCredential($host, $login, $mdp, $version) {

      $aOperation = [
            'operation' => 'core/check_credentials',
            "user"   => $login,
            "password" => $mdp
      ];

      $aData = [];
      $aData['auth_user'] = $login;
      $aData['auth_pwd']  = $mdp;
      $aData['json_data'] = json_encode($aOperation);
      $sUrl = $host;

      $clientrest = new PluginItopClientRest();
      $response = $clientrest->DoPostRequest($sUrl, $aData, null);
      $aResults = json_decode($response, true);

      if ($aResults != false) {

         if (isset($aResults['authorized']) && $aResults['authorized']) {
            return __('Connection successfully', 'itop');
         } else {
            return __('Wrong login or password', 'itop');
         }
      } else {

         return $aResults['message'];
      }

   }


   public function synchroItop() {

      $instance = new PluginItopInstance();
      $datasInstance = $instance->find();

      foreach ($datasInstance as $key => $value) {

         $synchro = new PluginItopSynchro();
         $datasSynchro = $synchro->find("plugin_itop_instances_id = ".$value['id'], "rank");

         foreach ($datasSynchro as $key => $value) {
            $synchroToExecute = new PluginItopSynchro();
            $synchroToExecute->getFromDB($value['id']);
            $synchroToExecute->execSynchro();
         }
      }
   }

   /**
    * Give localized information about 1 task
    *
    * @param $name of the task
    *
    * @return array of strings
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'synchroItop' :
            return ['description' => __('Cron for iTop synchro', 'itop')];
      }
      return [];
   }


   /**
    * Execute 1 task manage by the plugin
    *
    * @param $task Object of CronTask class for log / stat
    *
    * @return interger
    *    >0 : done
    *    <0 : to be run again (not finished)
    *     0 : nothing to do
    */
   static function cronsynchroItop($task) {

      $instance = new PluginItopInstance();
      $instance->synchroItop();
      return 1;
   }



   public function createJsonFile() {

      $instanceData = $this->fields;

      $data = [];
      $data[get_class($this)] = $instanceData;

      //get all synchro by instance
      $synchros = PluginItopSynchro::getAllEntriesByInstances($this);

      foreach ($synchros as $key => $value) {

         $synchro = new PluginItopSynchro();
         $synchro->getFromDB($value['id']);
         $synchroData = $synchro->fields;

         $fields = PluginItopField::getAllEntriesBySynchro($synchro);
         $fieldData = [];
         foreach ($fields as $key => $value) {
            $field = new PluginItopField();
            $field->getFromDB($value['id']);
            $fieldData[] = $field->fields;
         }
         if (count($fieldData) > 0) {
            $synchroData['PluginItopField'] = $fieldData;
         }
         $data[get_class($this)][get_class($synchro)][] = $synchroData;

      }

      $json = json_encode($data, JSON_PRETTY_PRINT);

      $monfichier = fopen(GLPI_DOC_DIR."/_plugins/itop/".trim($this->fields['name']).'.json', 'w+');
      fclose($monfichier);

      file_put_contents(GLPI_DOC_DIR."/_plugins/itop/".trim($this->fields['name']).'.json', $json);

   }

   public function getJSON() {

      $json = json_encode($this->fields);
      return $json;

   }

   function prepareInputForAdd($input) {

      if (isset($input["password"])) {
         $input["password"] = Toolbox::encrypt(stripslashes($input["password"]), GLPIKEY);
      }

      return $input;
   }


   function prepareInputForUpdate($input) {

      if (isset($input["password"])) {
         $input["password"] = Toolbox::encrypt(stripslashes($input["password"]), GLPIKEY);
      }

      return $input;
   }




   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id`        int(11) NOT NULL AUTO_INCREMENT,
                     `name`      varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `url`       varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `login`     varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `password`  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `comment`   varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `version`   varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die("Error adding table $table");
      }
   }

   static function uninstall() {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }


}
