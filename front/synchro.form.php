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


include ('../../../inc/includes.php');

Plugin::load('itop', true);
$dropdown = new PluginItopSynchro();

if (isset($_REQUEST['createDataSource'])) {

    $dropdown->update($_POST);
    $dropdown->createDataSource($_POST);
    Html::back();

} else if (isset($_REQUEST['updateDataSource'])) {

    $dropdown->update($_POST);
    $dropdown->updateDataSource($_POST);
    Html::back();

}else if (isset($_REQUEST['deleteDataSource'])) {

    $dropdown->update($_POST);
    $dropdown->deleteDataSource($_POST);
    PluginItopField::deleteAllEntriesBySynchro($dropdown);
    Html::back();

}else if (isset($_REQUEST['getJSON'])) {

    Toolbox::sendFile(GLPI_DOC_DIR."/_plugins/itop/DEMO.json", "DEMO.json");

}else{

    include (GLPI_ROOT . "/front/dropdown.common.form.php");
    
}

