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

/*if(isset($_REQUEST['add']) || isset($_REQUEST['update'])){

   if($_REQUEST['name'] = '' || empty($_REQUEST['name'])){
      Session::addMessageAfterRedirect(__('The field "name" is mandatory','morecategories'),
         true,ERROR, false);
      Html::back();
   }

}*/


$dropdown = new PluginItopField();
include (GLPI_ROOT . "/front/dropdown.common.form.php");
