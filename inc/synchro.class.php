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

class PluginItopSynchro extends CommonDropdown {

   const STATE_SYNCHRO_IMPLEMENTATION  =    'implementation';
   const STATE_SYNCHRO_OBSELETE        =    'obsolete';
   const STATE_SYNCHRO_PRODUCTION      =    'production';


   static function getTypeName($nb = 0) {
      return __("iTop synchronization", "itop");
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

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      switch ($item->getType()) {
         case 'PluginItopInstance':
            return self::getTypeName();
         break;
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case 'PluginItopInstance':
            $synchro = new self();
            $synchro->showFormForInstance($item->getID());
         break;
      }
      return true;
   }

   static function getAllEntriesByInstances(PluginItopInstance $instance) {

      $data = [];
      $synchro = new self();
      $data = $synchro->find("`plugin_itop_instances_id` = ".$instance->fields['id']." order by `rank`");

      return $data;

   }

   public function getJSON() {

      global $CFG_GLPI;

      $data = "";

      //get instances
      $instance = new PluginItopInstance();
      $instance->getFromDB($this->fields['plugin_itop_instances_id']);

      //get fields
      $fields = PluginItopField::getAllEntriesBySynchro($this);
      $fieldData = [];
      foreach ($fields as $key => $value) {
         $field = new PluginItopField();
         $field->getFromDB($value['id']);
         $fieldData[] = $field->fields;
      }

      $synchroData = $this->fields;
      $instanceData = $instance->fields;

      $data = [];
      $data[get_class($instance)] = $instanceData;
      $data[get_class($instance)][get_class($this)] = $synchroData;
      $data[get_class($instance)][get_class($this)]["PluginItopField"] = $fieldData;

      $json = json_encode($data, JSON_PRETTY_PRINT);

      $monfichier = fopen(GLPI_DOC_DIR."/_plugins/itop/".$this->fields['name'].'.json', 'w+');
      fclose($monfichier);

      file_put_contents(GLPI_DOC_DIR."/_plugins/itop/".$this->fields['name'].'.json', $json);

   }

   public function showFormForInstance($ID, $options = []) {

      $instance = new PluginItopInstance();
      $instance->getFromDB($ID);

      $datas = self::getAllEntriesByInstances($instance);

      $tabRank = range(0, 100);

      echo '<div class="spaced" id="tabsbody">';
      echo '<table class="tab_cadre_fixe" id="mainformtable">';
      echo '<tbody>';
      echo '<tr class="headerRow">';
      echo '<th colspan="4">'.__('Synchronizations', 'itop').'</th>';
      echo '</tr>';
      echo '</tbody>';
      echo '</table>';
      echo '<table class="tab_cadre_fixe">';

      echo '<tr class="headerRow">';
      echo '<th>'.__('Name', 'itop').'</th>';
      echo '<th>'.__('iTop scope class', 'itop').'</th>';
      echo '<th>'.__('Glpi scope class', 'itop').'</th>';
      echo '<th>'.__('Rank', 'itop').'</th>';
      echo '</tr>';

      foreach ($datas as $key => $value) {

         $synchro = new PluginItopSynchro();
         $synchro->getFromDB($value['id']);

         if ($synchro->isAllowToPush()) {
            echo "<tr class='line0'>";
            echo "<td>".$synchro->getLink()."</td>";
            echo "<td>".$synchro->fields['scope_class']."</td>";
            echo "<td>".$synchro->fields['glpi_scope_class']."</td>";
            echo "<td>";

            Dropdown::showFromArray('Rank_'.$synchro->fields['id'], $tabRank, ['value' => $synchro->fields['rank'], 'on_change' => 'updateGlpiField('.$value['id'].',"PluginItopSynchro","rank", this.value);']);

            echo "</td>";

            echo "</tr>";
         }

      }

      echo '</table>';

      return true;
   }

   public function showForm($ID, $options = []) {

      $tabChunkSize = range(0,10000,1000);

      global $CFG_GLPI;
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

      echo "<tr class='line0'><td>" . __('Description', 'itop') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "description");
      echo "</td>";
      echo "</tr>";

      echo "<tr class='line0'><td>" . __('iTop instance', 'itop') . "&nbsp;<span class='red'>*</span></td>";
      echo "<td>";
      PluginItopInstance::dropdown(['name'   => 'plugin_itop_instances_id',
                                    'value'  => $this->fields["plugin_itop_instances_id"],
                                    'right'  => 'all']);
      echo "</td>";
      echo "</tr>";

      if ($ID > 0) {

         $instance = new PluginItopInstance();
         $instance->getFromDB($this->fields["plugin_itop_instances_id"]);

         $tabGlpiType = $CFG_GLPI["state_types"];

         $options = [];
         foreach ($tabGlpiType as $type) {
            if ($item = getItemForItemtype($type)) {
               $options[__('Object', 'itop')][$type] = $item->getTypeName(1);
            }
         }

         $tabDropdownType = Dropdown::getStandardDropdownItemTypes();
         $tabItemType = array_merge($tabDropdownType, $options);

         echo "<tr class='line0'><td>" . __('Statut') . "</td>";
         echo "<td>";
         echo self::dropdownStatus(['value' => $this->fields["status"]]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('User') . "</td>";
         echo "<td>";
         echo "<textarea cols='40' rows='4' name='user_id'>".$this->fields["user_id"]."</textarea>";
         echo "&nbsp;(".__('OQL Request', 'itop').")";
         //echo '<a href="#" class="vsubmit" onclick="checkOQL(\'user_id\','.$this->fields["plugin_itop_instances_id"].',\'User\')">'.__('OQL check','itop')."</a>&nbsp;&nbsp;";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('Contact to notify', 'itop') . "</td>";
         echo "<td>";
         echo "<textarea cols='40' rows='4' name='notify_contact_id'>".$this->fields["notify_contact_id"]."</textarea>";
         echo "&nbsp;(".__('OQL Request', 'itop').")";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('iTop scope class', 'itop') . "&nbsp;<span class='red'>*</span></td>";
         echo "<td>";

         $option = [];
         $option['value'] = $this->fields["scope_class"];
         if ($this->fields["data_sync_source_id"] != 0) {
            $option['readonly'] = true;
         }
         echo self::dropdownItopScopeClass($instance, $option);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('itop scope restriction', 'itop') . "</td>";
         echo "<td>";
         echo "<textarea cols='40' rows='4' name='scope_restriction'>".$this->fields["scope_restriction"]."</textarea>";
         echo "&nbsp;(".__('OQL Request', 'itop').")";
         echo "</td>";
         echo "</tr>";

         if ($this->fields["data_sync_source_id"] == 0) {
            echo "<tr class='line0'><td>" . __('Glpi scope class', 'itop') . "&nbsp;<span class='red'>*</span></td>";
            echo "<td>";
            self::dropdownGlpiScopeClass($tabItemType, ['display' => true, 'name' => 'glpi_scope_class', 'value' => $this->fields["glpi_scope_class"]]);
            echo "</td>";
            echo "</tr>";
         } else {
            echo "<tr class='line0'><td>" . __('Glpi scope class', 'itop') . "&nbsp;<span class='red'>*</span></td>";
            echo "<td>";
            $item = getItemForItemtype($this->fields["glpi_scope_class"]);
            echo $item->getTypeName();
            echo "</td>";
            echo "</tr>";
         }

         echo "<tr class='line0'><td>" . __('Glpi scope restriction', 'itop') . "</td>";
         echo "<td>";
         echo "<textarea cols='40' rows='4' name='glpi_scope_restriction'>".$this->fields["glpi_scope_restriction"]."</textarea>";
         echo "&nbsp;(".__('SQL Request', 'itop').")";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('Database table name', 'itop') . "</td>";
         echo "<td>";

         $option = [];
         if ($this->fields["data_sync_source_id"] != 0) {
            $option = ['option' => 'readonly'];
         }
         Html::autocompletionTextField($this, "database_table_name", $option);
         echo "&nbsp;(".__('Optional', 'itop').")";
         echo "</td>";
         echo "</tr>";
      
         echo "<tr class='line0'><td>" . __('Chunk size', 'itop') . "</td>";
         echo "<td>";
         Dropdown::showFromArray('chunk_size', $tabChunkSize, ['value' => $this->fields["chunk_size"]]);
         echo "</td>";
         echo "</tr>";

         echo '<tr class="headerRow">';
         echo '<th colspan="2">'.__('Search & reconciliation', 'itop').'</th><th colspan="2"></th>';
         echo '</tr>';

         echo "<tr class='line0'><td>" . __('Reconciliation policy', 'itop') . "</td>";
         echo "<td>";
         echo self::dropdownItopReconciliationAndSearch( $instance, 'fields', 'reconciliation_policy', ['value' => $this->fields["reconciliation_policy"]]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('Action on zero', 'itop') . "</td>";
         echo "<td>";
         echo self::dropdownItopReconciliationAndSearch($instance, 'fields', 'action_on_zero', ['value' => $this->fields["action_on_zero"]]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('Action on one', 'itop') . "</td>";
         echo "<td>";
         echo self::dropdownItopReconciliationAndSearch($instance, 'fields', 'action_on_one', ['value' => $this->fields["action_on_one"]]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('Action on multiple', 'itop') . "</td>";
         echo "<td>";
         echo self::dropdownItopReconciliationAndSearch($instance, 'fields', 'action_on_multiple', ['value' => $this->fields["action_on_multiple"]]);
         echo "</td>";
         echo "</tr>";

         echo '<tr class="headerRow">';
         echo '<th colspan="2">'.__('Deletion rules', 'itop').'</th><th colspan="2"></th>';
         echo '</tr>';

         echo "<tr class='line0'><td>" . __('Full load periodicity', 'itop') . "</td>";
         echo "<td>";

         echo '<input type="text" class="form-control" id="duration_full_load_periodicity">';
         echo '<input type="hidden" name="full_load_periodicity" id="full_load_periodicity" value="'.$this->fields["full_load_periodicity"].'">';
         self::initializeDurationPicker('duration_full_load_periodicity', 'full_load_periodicity', $this->fields["full_load_periodicity"]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('Delete policy', 'itop') . "</td>";
         echo "<td>";
         echo self::dropdownItopDeletePolicy($instance, 'fields', 'delete_policy', ['value' => $this->fields["delete_policy"]]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='line0'><td>" . __('Delete policy update', 'itop') . "</td>";
         echo "<td>";
         echo "<textarea cols='40' rows='4' name='delete_policy_update'>".$this->fields["delete_policy_update"]."</textarea>";
         echo "</td>";

         echo "</tr>";
         echo "<tr class='line0'><td>" . __('Delete policy retention', 'itop') . "</td>";
         echo "<td>";

         echo '<input type="text" class="form-control" id="duration_delete_policy_retention">';
         echo '<input type="hidden" name="delete_policy_retention" id="delete_policy_retention" value="'.$this->fields["delete_policy_retention"].'">';
         self::initializeDurationPicker('duration_delete_policy_retention', 'delete_policy_retention', $this->fields["delete_policy_retention"]);
         echo "</td>";
         echo "</tr>";

         //check to bdd if glpi scope class is set efore display button for push

         if ($this->isAllowToPush()) {
            echo '<tr class="headerRow">';
            echo '<th colspan="2">'.__('iTop', 'itop').'</th><th colspan="2"></th>';
            echo '</tr>';

            if ($this->fields["data_sync_source_id"] != 0) {
               echo "<tr class='line0'><td>" . __('iTop data source', 'itop') . "</td>";
               echo "<td>";
               echo '<a target="_blank" href="'.$instance->fields['url'].'/pages/UI.php?operation=details&class=SynchroDataSource&id='.$this->fields["data_sync_source_id"].'">'.__('See', 'itop').'</a>';
               echo "</td>";
               echo "</tr>";
            }

            echo "<tr>";
            echo "<td></td>";
            echo "<td>";

            if ($this->fields["data_sync_source_id"] == 0) {
                  echo "<input value='".__('Create DataSource', 'itop')."' name='createDataSource' class='submit' type='submit'>";
            } else {
                  echo "<input value='".__('Update DataSource', 'itop')."' name='updateDataSource' class='submit' type='submit'>&nbsp;";
                  echo "<input value='".__('Delete DataSource', 'itop')."' name='deleteDataSource' class='submit' type='submit'>&nbsp;";
                  echo "<input value='".__('Export to JSON', 'itop')."' name='getJSON' class='submit' type='submit'>";
            }
            echo "</td>";
            echo "</tr>";

            echo '<tr class="headerRow">';
            echo '<th colspan="2"></th><th colspan="2"></th>';
            echo '</tr>';
         }

      }

      echo "</table>";

      $this->showFormButtons($options);

      $this->getJSON();

      return true;
   }



   public function deleteDataSource($data) {
      $instance = new PluginItopInstance();
      $instance->getFromDB($data['plugin_itop_instances_id']);

      $synchro =  new self();
      $synchro->getFromDB($data['id']);

      $aOperation = [
            'operation' => 'core/delete',
            'class'     => 'SynchroDataSource',
            'comment'   => $instance->fields["comment"],
            'output_fields' => "*",
            'key' => $synchro->fields['data_sync_source_id']
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');
      $tab = [];

      if ($res) {

         if ($API->resultat['code'] == 0) {

            $synchro->fields['data_sync_source_id'] = 0;
            $synchro->update($synchro->fields);

            Session::addMessageAfterRedirect(__('itop datasource deleted !', 'itop'),
            true, INFO, false);

            return true;
         } else {
            Session::addMessageAfterRedirect(__('Error when deleting datasource -> '.$API->error, 'itop'),
               true, ERROR, false);
         }

      } else {

         Session::addMessageAfterRedirect(__('Error when deleting datasource -> '.$API->error, 'itop'),
            true, ERROR, false);

         return false;
      }

      return $tab;

   }

   public function createOrUpdateDataSource($reconciliationParams, $params) {
      $reconciliationString = '';

      foreach ($reconciliationParams as $param) {
         if (!isset($params[$param])) {
            echo 'Error : '.$param.' is not present in params array';
            return false;
         }

         $reconciliationString .= $param.' = "'.$params[$param].'" AND ';
      }

      $instance = new PluginItopInstance();
      $instance->getFromDB($params['plugin_itop_instances_id']);

      $synchro =  new self();
      $synchro->getFromDB($params['id']);

      $aOperation = [
         'operation'     => 'core/get',
         'class'         => 'SynchroDataSource',
         'key'           => 'SELECT SynchroDataSource WHERE '.rtrim($reconciliationString, " AND "),
         'output_fields' => 'friendlyname'
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');

      $sOperation = 'core/create';

      if ($res) {
         $sOperation = 'core/update';
      }

      $aOperation = [
            'operation'     => $sOperation,
            'class'         => 'SynchroDataSource',
            'comment'       => $instance->fields["comment"],
            'output_fields' => "*",
            'key'           => 'SELECT SynchroDataSource WHERE '.rtrim($reconciliationString, " AND "),
            'fields' => [
               'name'                     => $params['name'],
               'status'                   => $params['status'],
               'description'              => $params['description'],
               'user_id'                  => $params['user_id'],
               'notify_contact_id'        => $params['notify_contact_id'],
               'scope_class'              => $params['scope_class'],
               'database_table_name'      => $params['database_table_name'],
               'full_load_periodicity'    => $params['full_load_periodicity'],
               'reconciliation_policy'    => $params['reconciliation_policy'],
               'action_on_zero'           => $params['action_on_zero'],
               'action_on_one'            => $params['action_on_one'],
               'action_on_multiple'       => $params['action_on_multiple'],
               'delete_policy'            => $params['delete_policy'],
               'delete_policy_update'     => $params['delete_policy_update'],
               'delete_policy_retention'  => $params['delete_policy_retention']
            ]
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');
      $tab = [];

      if ($res) {

         if ($API->resultat['code'] == 0) {

            Session::addMessageAfterRedirect(__('iTop datasource '.ltrim($sOperation, 'core/').'d !', 'itop'),
            true, INFO, false);

            return true;
         } else {
            Session::addMessageAfterRedirect(__('Error when creating or updating datasource -> '.$API->error, 'itop'),
               true, ERROR, false);
         }

      } else {

         Session::addMessageAfterRedirect(__('Error when creating or updating datasource -> '.$API->error, 'itop'),
            true, ERROR, false);

         return false;
      }

      return $res;
   }

   public function updateDataSource($data) {

      $instance = new PluginItopInstance();
      $instance->getFromDB($data['plugin_itop_instances_id']);

      $synchro =  new self();
      $synchro->getFromDB($data['id']);

      $aOperation = [
            'operation' => 'core/update',
            'class'     => 'SynchroDataSource',
            'comment'   => $instance->fields["comment"],
            'output_fields' => "*",
            'key' => $synchro->fields['data_sync_source_id'],
            'fields' => [
               'name'                     => $data['name'],
               'status'                   => $data['status'],
               'description'              => $data['description'],
               'user_id'                  => $data['user_id'],
               'notify_contact_id'        => $data['notify_contact_id'],
               'scope_class'              => $data['scope_class'],
               'database_table_name'      => $data['database_table_name'],
               'full_load_periodicity'    => $data['full_load_periodicity'],
               'reconciliation_policy'    => $data['reconciliation_policy'],
               'action_on_zero'           => $data['action_on_zero'],
               'action_on_one'            => $data['action_on_one'],
               'action_on_multiple'       => $data['action_on_multiple'],
               'delete_policy'            => $data['delete_policy'],
               'delete_policy_update'     => $data['delete_policy_update'],
               'delete_policy_retention'  => $data['delete_policy_retention'],
            ]
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');
      $tab = [];

      if ($res) {

         if ($API->resultat['code'] == 0) {

            Session::addMessageAfterRedirect(__('itop datasource updated !', 'itop'),
            true, INFO, false);

            return true;
         } else {
            Session::addMessageAfterRedirect(__('Error when updating datasource -> '.$API->error, 'itop'),
               true, ERROR, false);
         }

      } else {

         Session::addMessageAfterRedirect(__('Error when updating datasource -> '.$API->error, 'itop'),
            true, ERROR, false);

         return false;
      }

      return $tab;

   }

   public function createDataSource($data) {

      $instance = new PluginItopInstance();
      $instance->getFromDB($data['plugin_itop_instances_id']);

      $synchro =  new self();
      $synchro->getFromDB($data['id']);

      $aOperation = [
            'operation'     => 'core/create',
            'class'         => 'SynchroDataSource',
            'comment'       => $instance->fields["comment"],
            'output_fields' => "*",
            'fields'        => [
               'name'                     => $data['name'],
               'status'                   => $data['status'],
               'description'              => $data['description'],
               'user_id'                  => $data['user_id'],
               'notify_contact_id'        => $data['notify_contact_id'],
               'scope_class'              => $data['scope_class'],
               'database_table_name'      => $data['database_table_name'],
               'full_load_periodicity'    => $data['full_load_periodicity'],
               'reconciliation_policy'    => $data['reconciliation_policy'],
               'action_on_zero'           => $data['action_on_zero'],
               'action_on_one'            => $data['action_on_one'],
               'action_on_multiple'       => $data['action_on_multiple'],
               'delete_policy'            => $data['delete_policy'],
               'delete_policy_update'     => $data['delete_policy_update'],
               'delete_policy_retention'  => $data['delete_policy_retention'],
            ]
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $instance, 'objects');
      $tab = [];

      if ($res) {

         if ($API->resultat['code'] == 0) {
            foreach ($API->resultat['objects'] as $k => $aObj) {
               $synchro->fields['data_sync_source_id'] = $aObj['key'];
               $synchro->fields['database_table_name'] = $aObj['fields']['database_table_name'];
               $synchro->update($synchro->fields);
            }

            Session::addMessageAfterRedirect(__('itop datasource created !', 'itop'),
            true, INFO, false);

            return true;
         } else {
            Session::addMessageAfterRedirect(__('Error when creating datasource -> '.$API->error, 'itop'),
               true, ERROR, false);
         }

      } else {

         Session::addMessageAfterRedirect(__('Error when creating datasource -> '.$API->error, 'itop'),
            true, ERROR, false);

         return false;
      }

      return $tab;

   }

   public function isAllowToPush() {

      if ($this->fields['plugin_itop_instances_id'] != 0 && $this->fields['glpi_scope_class'] != '') {
         return true;
      } else {
         return false;
      }

   }

   public static function initializeDurationPicker($DomId, $HiddenDomId, $value) {

      echo "<script>

      $( document ).ready(function() {
          $('#".$DomId."').durationPicker({
            translations: {
               day: '".__('Day')."',
               hour: '".__('Hour')."',
               minute: '".__('Minute')."',
               second: '".__('Second', 'itop')."',
               days: '"._n('Day', 'Days', 2)."',
               hours: '"._n('Hour', 'Hours', 2)."',
               minutes: '"._n('Minute', 'Minutes', 2)."',
               seconds: '"._n('Second', 'Seconds', 2, 'itop')."',
            },

            showSeconds: true,
            showDays: true,
            onChanged: function (value, isInitializing) {
               $('#".$HiddenDomId."').val(value);
            }
         });
         $('#".$DomId."').data('durationPicker').setValue(".$value.");
      });            
      </script>";

   }


   /**
    * Get all itemtype from iTop
    *
    * @param      array   $options  The options
    * @param      PluginItopInstance   $conn
    *
    * @return     <type>  ( description_of_the_return_value )
    */
   static function dropdownGlpiScopeClass($tab, array $options = []) {

      $p['name']      = $options['name'];
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
   static function dropdownItopDeletePolicy(PluginItopInstance $conn, $arrayKey, $attriTop, array $options = []) {

      $p['name']      = $attriTop;
      $p['showtype']  = 'normal';
      $p['display']   = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $tab = self::getItopDeletePolicyAsArray($conn, $arrayKey, $attriTop);

      return Dropdown::showFromArray($p['name'], $tab, $p);
   }


   public static function getItopDeletePolicyAsArray(PluginItopInstance $conn, $key, $attriTop) {

      $aOperation = [
            'operation' => 'core/get_values_for_attribute',
            'class'     => 'SynchroDataSource',
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






   /**
    * Get all itemtype from iTop
    *
    * @param      array   $options  The options
    * @param      PluginItopInstance   $conn
    *
    * @return     <type>  ( description_of_the_return_value )
    */
   static function dropdownItopReconciliationAndSearch(PluginItopInstance $conn, $arrayKey, $attriTop, array $options = []) {

      $p['name']      = $attriTop;
      $p['showtype']  = 'normal';
      $p['display']   = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $tab = self::getItopReconciliationAndSearchAsArray($conn, $arrayKey, $attriTop);

      return Dropdown::showFromArray($p['name'], $tab, $p);
   }


   public static function getItopReconciliationAndSearchAsArray(PluginItopInstance $conn, $key, $attriTop) {

      $aOperation = [
            'operation' => 'core/get_values_for_attribute',
            'class'     => 'SynchroDataSource',
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




   /**
    * Get all itemtype from iTop
    *
    * @param      array   $options  The options
    * @param      PluginItopInstance   $conn
    *
    * @return     <type>  ( description_of_the_return_value )
    */
   static function dropdownItopScopeClass(PluginItopInstance $conn, array $options = []) {

      $p['name']      = 'scope_class';
      $p['showtype']  = 'normal';
      $p['display']   = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $tab = self::getItopScopeAsArray($conn, 'fields');

      return Dropdown::showFromArray($p['name'], $tab, $p);
   }


   public static function getItopScopeAsArray(PluginItopInstance $conn, $key) {

      $aOperation = [
            'operation' => 'core/get_all_object'
      ];

      $API  = new PluginItopClientRest();
      $res = $API->CallAPI($aOperation, $conn, $key);
      $tab = [];

      if ($res) {
         //create link between ticket GLPi and ref iTop
         foreach ($API->resultat[$key]as $k => $v) {
            $tab[$k] = $v;
         }

      }

      return $tab;

   }


   /**
    * Get internal status as dropdown
    *
    * @param      array   $options  The options
    *
    * @return     <type>  ( description_of_the_return_value )
    */
   static function dropdownStatus(array $options = []) {

      $p['name']      = 'status';
      $p['showtype']  = 'normal';
      $p['display']   = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      //if empty set default value
      if (!isset($p['value']) || empty($p['value'])) {
         $p['value']     = PluginItopSynchro::STATE_SYNCHRO_IMPLEMENTATION;
      }

      $tab = self::getStatusAsArray();

      return Dropdown::showFromArray($p['name'], $tab, $p);
   }



   public static function getStatusLabelByName($value) {
      switch ($value) {
         case PluginItopSynchro::STATE_SYNCHRO_IMPLEMENTATION:
               return __('Implementation', 'itop');
            break;

         case PluginItopSynchro::STATE_SYNCHRO_OBSELETE:
               return __('Obselete', 'itop');
            break;

         case PluginItopSynchro::STATE_SYNCHRO_PRODUCTION:
               return __('Production', 'itop');
            break;
      }

   }


   public static function getStatusAsArray() {

      $tab = [];
      $tab[PluginItopSynchro::STATE_SYNCHRO_IMPLEMENTATION] = self::getStatusLabelByName(PluginItopSynchro::STATE_SYNCHRO_IMPLEMENTATION);
      $tab[PluginItopSynchro::STATE_SYNCHRO_OBSELETE]       = self::getStatusLabelByName(PluginItopSynchro::STATE_SYNCHRO_OBSELETE);
      $tab[PluginItopSynchro::STATE_SYNCHRO_PRODUCTION]     = self::getStatusLabelByName(PluginItopSynchro::STATE_SYNCHRO_PRODUCTION);

      return $tab;
   }

   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                     `id`                       int(11) NOT NULL AUTO_INCREMENT,
                     `name`                     varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `description`              varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `status`                   varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'implementation',
                     `user_id`                  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SELECT User WHERE id = 1',
                     `notify_contact_id`        varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SELECT Contact WHERE id = 1',
                     `scope_class`              varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `glpi_scope_class`         varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `glpi_scope_restriction`   varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `database_table_name`      varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `scope_restriction`        varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `full_load_periodicity`    int(11) NOT NULL DEFAULT '0',
                     `reconciliation_policy`    varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'use_attributes',
                     `action_on_zero`           varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'create',
                     `action_on_one`            varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'update',
                     `action_on_multiple`       varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'error',
                     `delete_policy`            varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ignore',
                     `delete_policy_update`     varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                     `delete_policy_retention`  int(11) NOT NULL DEFAULT '0',
                     `plugin_itop_instances_id` int(11) NOT NULL DEFAULT '0',
                     `data_sync_source_id`      int(11) NOT NULL DEFAULT '0',
                     `rank`                     int(11) NOT NULL DEFAULT '0',
                     `chunk_size`                     int(11) NOT NULL DEFAULT '0',
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
