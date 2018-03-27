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

include ('../../../inc/includes.php');

$optionalParams = array('import_only' => 'boolean', 'collect_only' => 'boolean', 'synchro_only' => 'boolean', 'dump_only' => 'boolean');

$file = PluginItopToolbox::readParameter('config_file');

// TODO : if $file is dir, iterate through it
if (!file_exists($file)) {
  echo "Error : file $filename does not exist"; // TODO : translate
} else {
  $json = file_get_contents($file);

  if (json_decode($json)) {
    $content = json_decode($json, true);

  	if (is_array($content)) {
  	  if (isset($content['PluginItopInstance'])){
  	  	// generate temp array for current method
        $params = $content['PluginItopInstance'];

        // remove useless indexes
        unset($params['PluginItopSynchro']);
        unset($params['id']);

        // instance of PluginItopInstance or false
        $instance = PluginItopToolbox::findOrCreate('PluginItopInstance', array('name'), $params);

        echo 'Begining...'.PHP_EOL;

        if ($instance) {
          echo 'Instance '.$instance['name'].' created ! '.PHP_EOL;
          foreach ($content['PluginItopInstance']['PluginItopSynchro'] as $params) {
          	// keep fields declaration for further use
          	$fields = $params['PluginItopField'];
          	  
          	// remove useless indexes
          	unset($params['PluginItopField']);
            unset($params['id']);
              
            // inserting id retrieved from previous instance find or create
            $params['plugin_itop_instances_id'] = $instance['id'];
  	
            // each synchro in an instance is retrieved by its name and its parent instance
            $reconciliationParams = array('name', 'plugin_itop_instances_id');
              
            // instance of PluginItopSynchro or false
            $synchro = PluginItopToolbox::findOrCreate('PluginItopSynchro', $reconciliationParams, $params);
              
            if ($synchro) {
              echo 'Synchro '.$synchro['name'].' created ! '.PHP_EOL; 
              foreach ($fields as $params) {
              	// remove useless indexes
                unset($params['id']);
                unset($params['sync_attr_id']);

                // inserting id retrieved from previous synchro find or create
                $params['plugin_itop_synchros_id'] = $synchro['id'];
                
                // each field in a synchro is retrieved by its parent synchro, glpi attribute name and itop attribute name
                $reconciliationParams = array('plugin_itop_synchros_id', 'glpi_attribute', 'attcode');

                // instance of PluginItopField or false
                $field = PluginItopToolbox::findOrCreate('PluginItopField', $reconciliationParams, $params);
              }
            }
          }
        }
  	  }
  	}
  }
}