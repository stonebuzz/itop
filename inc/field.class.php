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

class PluginItopField extends CommonDropdown {

   static function getTypeName($nb = 0) {
      return __("Field", "itop");
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      switch ($item->getType()) {
         case 'PluginItopSynchro':
            if (self::canAccesToFieldMgmt($item)) {
               return self::getTypeName();
            }
            break;
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case 'PluginItopSynchro':
            if (self::canAccesToFieldMgmt($item)) {
               $field = new self();
               $field->showForm($item->getID());
            }
            break;
      }
      return true;
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
         'field'              => 'attcode',
         'name'               => __('Attributes', 'itop'),
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
         'field'              => 'reconcile',
         'name'               => __('Reconciliation ?', 'itop'),
         'datatype'           => 'bool',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => self::getTable(),
         'field'              => 'update_field',
         'name'               => __('Update ?', 'itop'),
         'datatype'           => 'bool',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => self::getTable(),
         'field'              => 'update_policy',
         'name'               => __('Update Policy', 'itop'),
         'datatype'           => 'text',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => PluginItopSynchro::getTable(),
         'field'              => 'name',
         'name'               => __("iTop synchronization", "itop"),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

            $tab[] = [
         'id'                 => '7',
         'table'              => self::getTable(),
         'field'              => 'reconciliation_attcode',
         'name'               => __('Reconciliation Key', 'itop'),
         'datatype'           => 'text',
         'massiveaction'      => false
            ];

            return $tab;

   }


   public function showForm($ID, $options = []) {

      $synchro = new PluginItopSynchro();
      $synchro->getFromDB($ID);

      $instance = new PluginItopInstance();
      $instance->getFromDB($synchro->fields['plugin_itop_instances_id']);

      if (!self::alreadyHaveEntries($synchro)) {
         self::createEntries($synchro);
      }

      $datas = self::getAllEntriesBySynchro($synchro);
      $tabUpdatePolicy = self::getItopUpdatePolicyAsArray($instance, 'fields', 'update_policy');
      $tabAttributeGLPi = self::dropdownGetGlpiAttributeByItemType($synchro->fields['glpi_scope_class']);

      echo '<div class="spaced" id="tabsbody">';
      echo '<table class="tab_cadre_fixe" id="mainformtable">';
      echo '<tbody>';
      echo '<tr class="headerRow">';
      echo '<th colspan="9">'.__('Field managment', 'itop').'</th>';
      echo '</tr>';
      echo '</tbody>';
      echo '</table>';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr class="headerRow">';
      echo '<th>'.__('GLPi attributes', 'itop').'</th>';
      echo '<th>'.__('Itop attributes', 'itop').'</th>';
      echo '<th>'.__('Reconciliation ?', 'itop').'</th>';
      echo '<th>'.__('Update ?', 'itop').'</th>';
      echo '<th>'.__('Update Policy', 'itop').'</th>';
      echo '<th>'.__('Reconciliation Key', 'itop').'</th>';
      echo '<th>'.__('States', 'itop').'</th>';
      echo '<th>'.__('Actions', 'itop').'</th>';
      echo '<th>'.__('Link', 'itop').'</th>';
      echo '</tr>';

      foreach ($datas as $key => $value) {

         echo "<tr class='line0'>";

         echo "<td>";
         echo self::dropdownGlpiAttribute($tabAttributeGLPi, 'glpi_attribute', ['value' => $value['glpi_attribute'], 'display_emptychoice' => true]);
         echo"</td>";

         echo "<td>".$value['attcode']."</td>";
         echo "<td>".Dropdown::showYesNo('reconcile', $value['reconcile'], -1, ['display' => false , 'on_change' => 'updateField('.$value['id'].',"PluginItopField",'.$value['sync_attr_id'].', '.$instance->fields['id'].',"'.$value['finalclass'].'","reconcile", this.value);'])."</td>";

         echo "<td>".Dropdown::showYesNo('update_field', $value['update_field'], -1, ['display' => false , 'on_change' => 'updateField('.$value['id'].',"PluginItopField",'.$value['sync_attr_id'].', '.$instance->fields['id'].',"'.$value['finalclass'].'","update", this.value);'])."</td>";

         echo "<td>";
         echo self::dropdownItopUpdatePolicy($tabUpdatePolicy, 'update_policy', ['value' => $value['update_policy'] , 'on_change' => 'updateField('.$value['id'].',"PluginItopField",'.$value['sync_attr_id'].', '.$instance->fields['id'].',"'.$value['finalclass'].'","update_policy", this.value);']);
         echo "</td>";

         if ($value['finalclass'] == 'SynchroAttExtKey') {

            echo "<td>".self::dropdownItopReconciliationKey($instance, 'fields', $value['attcode'], $synchro->fields['scope_class'], ['value' => $value["reconciliation_attcode"], 'on_change' => 'updateField('.$value['id'].',"PluginItopField",'.$value['sync_attr_id'].', '.$instance->fields['id'].',"'.$value['finalclass'].'","reconciliation_attcode", this.value);'])."</td>";
         } else {
            echo "<td></td>";
         }

         echo "<td>";
         self::checkFieldState($value, $instance);
         echo "</td>";

         echo '<td><a target="_blank" href="'.$instance->fields['url'].'/pages/UI.php?operation=details&class='.$value['finalclass'].'&id='.$value['sync_attr_id'].'">'.__('See', 'itop').'</a></td>';
         echo "</tr>";

      }

      echo '</table>';

      $this->showFormButtons($options);

      return true;
   }


   function prepareInputForAdd($input) {
      return $input;
   }


   function prepareInputForUpdate($input) {
      return $input;
   }


   public static function dropdownGetGlpiAttributeByItemType($itemtype) {

      global $DB;
      $item = new $itemtype();

      $tab = [];
      $tabAttributeGLPi = $DB->list_fields($item::getTable(), true);

      foreach ($tabAttributeGLPi as $key => $val) {
         $tab[$key] = $val['Field'];
      }

      return $tab;
   }

   public function updateGlpiFieldByItopSynchroAttribute($synchroAttribute_id) {

      $synchro = new PluginItopSynchro();
      $synchro->getFromDB($this->fields['plugin_itop_synchros_id']);

      $instance = new PluginItopInstance();
      $instance->getFromDB($synchro->fields['plugin_itop_instances_id']);

      $iTopData = self::getSynchroAttById($instance, 'objects', $synchroAttribute_id, $this->fields['finalclass']);

      $this->fields['update_field'] = $iTopData['update'];
      $this->fields['reconcile'] = $iTopData['reconcile'];
      $this->fields['update_policy'] = $iTopData['update_policy'];
      $this->fields['reconciliation_attcode'] = $iTopData['reconciliation_attcode'];

      Toolbox::loginfile('itop', "itop ->'".$iTopData['update_field']."' GLPi -> '".$this->fields['update_field']."'");

      $this->update($this->fields);

   }

   public static function getSynchroAttById(PluginItopInstance $conn, $key, $attExtKey_id, $class) {

      $aOperation = [
            'operation' => 'core/get',
            'class'     => $class,
            'comment'   => $conn->fields["comment"],
            'output_fields' => '*',
            'key'        => 'SELECT SynchroAttExtKey WHERE id = '. $attExtKey_id
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {
         foreach ($API->resultat[$key] as $k => $v) {
            foreach ($v['fields'] as $key => $value) {
               $tab[$key] = $value;
            }
         }
      }

      return $tab;

   }



   public function updateItopSynchroAttributeByGlpiField() {

      $synchro = new PluginItopSynchro();
      $synchro->getFromDB($this->fields['plugin_itop_synchros_id']);

      $instance = new PluginItopInstance();
      $instance->getFromDB($synchro->fields['plugin_itop_instances_id']);

      $aOperation = [
            'operation'       => 'core/update',
            'class'           => $this->fields['finalclass'],
            'comment'         => $instance->fields["comment"],
            'output_fields'   => "*",
            'key'             => $this->fields['sync_attr_id'],
            'fields'          => [
                                    'update'                   =>  $this->fields['update_field'],
                                    'update_policy'            =>  $this->fields['update_policy'],
                                    'reconciliation_attcode'   =>  $this->fields['reconciliation_attcode'],
                                    'reconcile'                =>  $this->fields['reconcile'],
                                 ]
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');

      if ($res) {

         if ($API->resultat['code'] == 0) {

            Session::addMessageAfterRedirect(__('itop '.$this->fields['finalclass'].' updated !', 'itop'),
            true, INFO, false);

            return true;
         } else {
            Session::addMessageAfterRedirect(__('Error when updating '.$this->fields['finalclass'].' -> '.$API->error, 'itop'),
               true, ERROR, false);
            return false;
         }

      } else {

         Session::addMessageAfterRedirect(__('Error when updating '.$this->fields['finalclass'].' -> '.$API->error, 'itop'),
            true, ERROR, false);

         return false;
      }

   }

   static function checkFieldState($data, $instance) {

      global $CFG_GLPI;

      $aOperation = [
            'operation' => 'core/get',
            'class'     => $data['finalclass'],
            'comment'   => $instance->fields["comment"],
            'key'    => $data['sync_attr_id']
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');
      $tab = [];

      if ($res) {
         $tab[''] = __('ID (primary key)', 'itop');
         //create link between ticket GLPi and ref iTop
         foreach ($API->resultat['objects'] as $key => $value) {
            foreach ($value as $k => $v) {
               $tab[$k] = $v;
            }
         }
      }

      $isGood = true;
      $tooltip = "";

      foreach ($data as $key => $value) {

         $keyItop = $key;
         if ($keyItop == 'update_field') {
            $keyItop = 'update';
         }

         if (isset($tab['fields'][$keyItop]) && $data[$key] != $tab['fields'][$keyItop]) {
            $isGood = false;
            if ($key == 'reconciliation_attcode') {
               //in iTop blanck means use primary key
               $tab['fields'][$keyItop] = __('ID (primary key)', 'itop');
            }
            $tooltip .= "GLPI ".$key." -> ".$data[$key]." iTop ".$keyItop." -> ".$tab['fields'][$keyItop]."\n";
         }
      }

      if (!$isGood) {
         echo "<img  src='".$CFG_GLPI["root_doc"]."/plugins/itop/pics/cross16.png' title='".$tooltip."'><br>";
         echo "<td>";
         echo "<img  style='cursor:pointer' onclick='updateGlpiFieldByItopSynchroAttribute(".$data['id'].",".$data['sync_attr_id'].")' src='".$CFG_GLPI["root_doc"]."/plugins/itop/pics/arrow-left16.png' title='".__('Push to GLPI', 'itop')."'>";
         echo "&nbsp;&nbsp;<img  style='cursor:pointer' onclick='updateItopSynchroAttributeByGLPiField(".$data['id'].")' src='".$CFG_GLPI["root_doc"]."/plugins/itop/pics/arrow-right16.png' title='".__('Push to ITOP', 'itop')."'>";
         echo "</td>";
      } else {
         echo "<img  src='".$CFG_GLPI["root_doc"]."/plugins/itop/pics/check16.png'>";
         echo "<td></td>";
      }
   }

   static function getOnClick($data) {

   }


   static function deleteAllEntriesBySynchro(PluginItopSynchro $synchro) {

      $field = new PluginItopField();
      $found = $field->find("`plugin_itop_synchros_id` = ".$synchro->fields['id']);
      if (count($found) > 0) {

         foreach ($found as $key => $value) {
            $fieldToDelete = new PluginItopField();
            $fieldToDelete->getFromDB($value['id']);
            $fieldToDelete->delete($fieldToDelete->fields);
         }
      }
   }

      /**
    * Get all itemtype from iTop
    *
    * @param      array   $options  The options
    * @param      PluginItopInstance   $conn
    *
    * @return     <type>  ( description_of_the_return_value )
    */
   static function dropdownItopReconciliationKey(PluginItopInstance $conn, $arrayKey, $attriTop, $classItop, array $options = []) {

      $p['name']      = $attriTop;
      $p['showtype']  = 'normal';
      $p['display']   = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $tab = self::getItopReconciliationKeyAsArray($conn, $classItop, 'fields', $attriTop);

      return Dropdown::showFromArray($p['name'], $tab, $p);
   }


   public static function getItopReconciliationKeyAsArray(PluginItopInstance $conn, $class, $key, $attriTop) {

      $aOperation = [
            'operation' => 'core/get_reconciliation_key',
            'class'     => $class,
            'comment'   => $conn->fields["comment"],
            'fields'    => $attriTop
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {
         $tab[''] = __('ID (primary key)', 'itop');
         //create link between ticket GLPi and ref iTop
         foreach ($API->resultat[$key] as $k => $v) {
            $tab[$k] = $v;
         }

      }

      return $tab;

   }




   public function updateExternalSynchroAttribut($instanceId, $iTopClassName, $itopId, $field, $value) {

      $instance = new PluginItopInstance();
      $instance->getFromDB($instanceId);

      $aOperation = [
            'operation' => 'core/update',
            'class'     => $iTopClassName,
            'comment'   => $instance->fields["comment"],
            'output_fields' => "*",
            'key' => $itopId,
            'fields' => [
               $field                     => $value
            ]
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');

      if ($res) {

         if ($API->resultat['code'] == 0) {

            Session::addMessageAfterRedirect(__('itop '.$iTopClassName.' updated !', 'itop'),
            true, INFO, false);

            return true;
         } else {
            Session::addMessageAfterRedirect(__('Error when updating '.$iTopClassName.' -> '.$API->error, 'itop'),
               true, ERROR, false);
            return false;
         }

      } else {

         Session::addMessageAfterRedirect(__('Error when updating '.$iTopClassName.' -> '.$API->error, 'itop'),
            true, ERROR, false);

         return false;
      }

   }

      /**
    * Get all itemtype from iTop
    *
    * @param      array   $options  The options
    * @param      PluginItopInstance   $conn
    *
    * @return     <type>  ( description_of_the_return_value )
    */
   static function dropdownItopUpdatePolicy($tab, $name, array $options = []) {

      $p['name']      = $name;
      $p['showtype']  = 'normal';
      $p['display']   = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      return Dropdown::showFromArray($p['name'], $tab, $p);
   }

         /**
    * Get all itemtype from iTop
    *
    * @param      array   $options  The options
    * @param      PluginItopInstance   $conn
    *
    * @return     <type>  ( description_of_the_return_value )
    */
   static function dropdownGlpiAttribute($tab, $name, array $options = []) {

      $p['name']      = $name;
      $p['showtype']  = 'normal';
      $p['display']   = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      return Dropdown::showFromArray($p['name'], $tab, $p);
   }


   public static function getItopUpdatePolicyAsArray(PluginItopInstance $conn, $key, $attriTop) {

      $aOperation = [
            'operation' => 'core/get_values_for_attribute',
            'class'     => 'SynchroAttribute',
            'comment'   => $conn->fields["comment"],
            'fields'    => $attriTop
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {
         //create link between ticket GLPi and ref iTop
         foreach ($API->resultat[$key][$attriTop] as $k => $v) {
            $tab[$k] = $v;
         }

      }

      return $tab;

   }



   static function getAllEntriesBySynchro(PluginItopSynchro $synchro) {

      $data = [];
      $field = new self();
      $data = $field->find("`plugin_itop_synchros_id` = ".$synchro->fields['id']);

      return $data;

   }

   static function createEntries(PluginItopSynchro $synchro) {
      $instance = new PluginItopInstance();
      $instance->getFromDB($synchro->fields['plugin_itop_instances_id']);

      //LOAD SYNCHRO ATTRIBUTE
      $data = self::getSynchroAttributeBySynchroSource($instance, $synchro, 'objects', true);

      foreach ($data as $key => $value) {

         $reconciliation = '';
         if ($value['finalclass'] == 'SynchroAttExtKey') {
            $dataForAttExtKey = self::getSynchroAttExtKeyById($instance, $synchro, 'objects', $key);
            foreach ($dataForAttExtKey as $k => $v) {
               $reconciliation = $v['reconciliation_attcode'];
            }
         }

         $field = new PluginItopField();
         $field->add([
            "sync_attr_id" => $key,
            "attcode" => $value['attcode'],
            "attr_description" => $value['attcode'],
            "reconcile" => $value['reconcile'],
            "update_field" => $value['update'],
            "update_policy" => $value['update_policy'],
            "finalclass" =>  $value['finalclass'],
            "reconciliation_attcode" =>  $reconciliation ,
            "plugin_itop_synchros_id" => $synchro->fields['id']]
            );

      }
   }

   public static function getSynchroAttExtKeyById(PluginItopInstance $conn, $key, $key, $attExtKey_id) {

      $aOperation = [
            'operation' => 'core/get',
            'class'     => 'SynchroAttExtKey',
            'comment'   => $conn->fields["comment"],
            'output_fields' => '*',
            'key'        => 'SELECT SynchroAttExtKey WHERE id = '. $attExtKey_id
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {
         foreach ($API->resultat[$key] as $k => $v) {
            foreach ($v['fields'] as $key => $value) {
               $tab[$v['key']][$key] = $value;
            }
         }
      }

      return $tab;

   }

   public static function getSynchroAttLinkSetById(PluginItopInstance $conn, PluginItopSynchro $synchro, $key, $attLinkSet_id) {

      $aOperation = [
            'operation' => 'core/get',
            'class'     => 'SynchroAttLinkSet',
            'comment'   => $conn->fields["comment"],
            'output_fields' => '*',
            'key'        => 'SELECT SynchroAttLinkSet WHERE id = '. $attLinkSet_id
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {
         foreach ($API->resultat[$key] as $k => $v) {
            foreach ($v['fields'] as $key => $value) {
               $tab[$v['key']][$key] = $value;
            }
         }
      }

      return $tab;

   }




   public static function getSynchroAttributeBySynchroSource(PluginItopInstance $conn, PluginItopSynchro $synchro, $key, $without_synchroAttLinkSet) {

      $where = '';
      if ($without_synchroAttLinkSet) {
         $where = "AND finalclass != 'SynchroAttLinkSet'";
      }
      $aOperation = [
            'operation' => 'core/get',
            'class'     => 'SynchroAttribute',
            'comment'   => $conn->fields["comment"],
            'output_fields' => '*',
            'key'        => 'SELECT SynchroAttribute WHERE sync_source_id = '. $synchro->fields['data_sync_source_id'] . $where,
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {
         //create link between ticket GLPi and ref iTop
         foreach ($API->resultat[$key] as $k => $v) {
            foreach ($v['fields'] as $key => $value) {
               $tab[$v['key']][$key] = $value;
            }
         }
      }

      return $tab;

   }


   static function alreadyHaveEntries(PluginItopSynchro $synchro) {
      $field = new self();
      $found = $field->find("`plugin_itop_synchros_id` = ".$synchro->fields['id']);
      if (count($found) <= 0) {
         return false;
      } else {
         return true;
      }
   }


   static function canAccesToFieldMgmt(PluginItopSynchro $synchro) {
      if ($synchro->fields['plugin_itop_instances_id'] != 0 && $synchro->fields['data_sync_source_id'] != 0) {
         return true;
      } else {
         return false;
      }
   }

   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id`                       int(11) NOT NULL AUTO_INCREMENT,
                     `plugin_itop_synchros_id`  int(11) NOT NULL DEFAULT '0',
                     `sync_attr_id`             int(11) NOT NULL DEFAULT '0',
                     `glpi_attribute`           varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `attcode`                  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `attr_description`         varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `reconcile`                int(11) NOT NULL DEFAULT '0',
                     `update_field`             int(11) NOT NULL DEFAULT '0',
                     `update_policy`            varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `finalclass`               varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `reconciliation_attcode`   varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
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
