[![License](https://img.shields.io/github/license/pluginsGLPI/itop.svg?&label=License)](https://github.com/pluginsGLPI/itop/blob/master/LICENSE)
[![Build Status](https://secure.travis-ci.org/pluginsGLPI/itop.svg?branch=master)](https://secure.travis-ci.org/pluginsGLPI/itop)
[![Project Status: Active - The project has reached a stable, usable state and is being actively developed.](http://www.repostatus.org/badges/latest/active.svg)](http://www.repostatus.org/#active)

# iTop plugin for GLPI

**Please note that an [iTop module, provided by Teclib'](https://github.com/TECLIB/teclib-itop-glpi-module), is also required for the synchronization to work.**

## Features

The plugin allows you to export GLPI inventory data into iTop CMDB.
There's two export modes :
- online : the plugin pushes data into iTop synchronization tables
- offline : the plugin exports data into CSV files, to be imported into iTop

What you can configure
- Matching between object types in GLPI and iTop
- Matching between GLPI statuses and iTop statuses
- Matching between GLPI software categories and iTop software instances types

## Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer

![3.-Screenshot](/screenshot.png "Screenshot")


## EN - Introduction


GLPI Plugin that allow you to export your GLPI data to iTop. Require
installation of iTop module in order to modify its data model.

EN - Features


1.  Matching between GLPI Object types and iTop Object ones

2.  Matching between GLPI and iTop statuses

3.  Matching between GLPI Software category and iTop SoftwareInstance
    type

4.  Export GLPI data to iTop

## EN - Installation


1.  Plugin GLPI

<!-- -->

     $ cd /<your_glpi_dir>/plugins/
     $ wget <plugin_url>
     $ tar jvxf <downloaded_archive>.tar.bz2

-   Go to <http://URL-GLPI/front/plugin.php> to install and enable
    the plugin.

    1.  Module iTop

<!-- -->

     $ cd /<your_itop_dir>/extensions/
     $ wget <module_url>
     $ tar jvxf <downloaded_archive>.tar.bz2

-   Go to <http://URL-iTop/setup/> to override iTop configuration with
    the new module added in the correct step.

    1.  iTop Collector

<!-- -->

     $ cd /collector-dir/
     $ wget <collector_url>
     $ tar jvxf <downloaded_archive>.tar.bz2

## EN - Configuration


**config/itop.ini.**

     [itop]
     active        = <Type 1 to enable the synchronisation on this GLPI instance>
     itop_path     = <Type here path to iTop directory>
     itop_user     = <Type your iTop username (admin rights required)>
     itop_pwd      = <Type here your iTop password>
     php_path      = <Type here the php executable path>

**config/config-local.ini.**

     [general]
     glpi_server_id = 1
     active         = 1

     ;Export : PluginItopOutputSQL or PluginItopOutputCsv or PluginItopOutputCsvWithImport
     ; Export methode
     output_method  = PluginItopOutputCsvWithImport

     ;CSV Options
     separator      = ,
     output_directory = /tmp
     ;Chunk size
     chunk_size = 10000

     [mappings]
     ;Change here the php classes handling the export (if you want to override them).
     Organization      = PluginItopExportOrganization
     Xxxxxxxx          = PluginItopExportXxxxxxxx
     Software           = PluginItopExportSoftware

     [itop_datasources]
     ;Change here the data synchronisation tables name in iTop.
     Organization      = synchro_data_organization_1
     Xxxxxxxx          = synchro_data_xxxxxx_x
     Software         = synchro_data_software_21

     [links]
     ; iTop specific data
     mandatory_links  = Server,PC,Virtual Machine,Hypervisor,Phone,IPPhone,MobilePhone,Printer,NetworkDevice,Printer,Tablet
     status_link      = Production,Implementation,Stock,Obsolete
     software_classes = Middleware, PCSoftware, WebServer, OtherSoftware, DBServer

**inc/dbitop.class.php.**

     <?php
      class PluginItopDbItop extends DBmysql {
        var $dbhost     = '<URL to iTop database server>';
        var $dbuser      = '<Database username>';
        var $dbpassword = '<Database password>';
        var $dbdefault  = '<iTop database name>';
      }
     ?>

## EN - First synchronisation


1.  On GLPI, go to tab "configuration ⇒ plugins" then click on the
    plugin name to configure it.

2.  Fill in this form matchings between object types, status and
    software categories in GLPI and their iTop equivalents

3.  Go now on tab "configuration ⇒ automatic actions ⇒ itopExport" in
    order to execute or planning a synchronisation

4.  On iTop, go in the "Synchronisation" menu to check que if the data
    has been imported successfully in iTop.


## FR - Introduction

Plugin GLPI permettant d’exporter le parc de GLPI vers iTop. Nécessite
l’installation d’un module iTop afin de modifier son modèle de données.

## FR - Fonctionnalités


1.  Correspondances entre le type d’objet GLPI et le type d’objet iTop

2.  Correspondances entre les status GLPI et status iTop

3.  Correspondances entre les catégories de logiciel GLPI et type
    d’instance logiciel iTop

4.  Export des données de GLPI à iTop

## FR - Installation


1.  Plugin GLPI

        [source,sh]
        ----
         $ cd /glpi-dir/plugins/
         $ wget <URL_du_plugin>
         $ tar jvxf <archive_telechargée>.tar.bz2
        ----

    -   Rendez-vous sur <http://URL-GLPI/front/plugin.php> afin
        d’installer et d’activer le plugin.

2.  Module iTop

        [source,sh]
        ----
         $ cd /itop-dir/extensions/
         $ wget <URL_du_module>
         $ tar jvxf <archive_telechargée>.tar.bz2
        ----

    -   Rendez vous sur <http://URL-iTop/setup/> pour rejouer la
        configuration d’iTop et y intégrer le module à l'étape adéquate.

3.  Collecteur iTop

        [source,sh]
        ----
         $ cd /collector-dir/
         $ wget <URL_du_collecteur>
         $ tar jvxf <archive_telechargée>.tar.bz2
        ----

    -   Chaque entité iTop est définie dans le dossier collectors en
        tant que json, pour modifier la source de données il faut
        modifier le fichier json correspondant et lancer l’execution de
        l’import depuis GLPI.

## FR - Configuration


**config/itop.ini.**

     [itop]
     active        = <Tapez 1 si ce GLPI doit synchroniser ses données, 0 sinon>
     itop_path     = <Renseignez ici le chemin d'accès à iTop>
     itop_user     = <Entrez ici votre nom d'utilisateur iTop>
     itop_pwd      = <Entrez ici votre mot de passe iTop>
     php_path      = <Renseignez ici le chemin d'accès à votre executable PHP>

**config/config-local.ini.**

     [general]
     glpi_server_id = 1
     active         = 1

     ;  URL vers le fichier exec.php dans le dossier du collecteur
     collector_executable_url = http://localhost/glpi-plugin/empty-connector/exec.php

     ;Export : PluginItopOutputSQL or PluginItopOutputCsv or PluginItopOutputCsvWithImport
     ; Méthode d'export
     output_method  = PluginItopOutputCsvWithImport

     ;CSV Options
     separator      = ,
     output_directory = /tmp
     ; Chunk size
     chunk_size = 10000

     ;number of old files to keep
     ;add_file_suffix = 1
     ;files_retention = 10

     [mappings]
     ;Modifiez ici les classes php permettant l'export dans les différentes entités iTop en cas de surcharge des classes par défaut.
     Organization      = PluginItopExportOrganization
     Xxxxxxxx          = PluginItopExportXxxxxxxx
     Software          = PluginItopExportSoftware

     [itop_datasources]
     ;Modifiez ici le nom des tables de synchro coté iTop.
     Organization      = synchro_data_organization_1
     Xxxxxxxx          = synchro_data_xxxxxx_x
     Software          = synchro_data_software_21

     [links]
     ; Données spécifiques iTop
     mandatory_links  = Server,PC,Virtual Machine,Hypervisor,Phone,IPPhone,MobilePhone,Printer,NetworkDevice,Printer,Tablet
     status_link      = Production,Implementation,Stock,Obsolete
     software_classes = Middleware, PCSoftware, WebServer, OtherSoftware, DBServer

**inc/dbitop.class.php.**

     <?php
      class PluginItopDbItop extends DBmysql {
        var $dbhost     = '<URL du serveur ou est la base de données iTop>';
        var $dbuser     = '<Utilisateur base de données>';
        var $dbpassword = 'Mot de passe base de données';
        var $dbdefault  = 'Nom de la base iTop';
      }
     ?>

## FR - Première synchronisation


1.  Sur GLPI, allez sur l’onglet "configuration ⇒ plugins" puis cliquez
    sur le nom du plugin pour le configurer.

2.  Renseignez dans ce formulaire les correspondances entre vos type
    d’objets, status et catégories de logiciel côté GLPI et leur
    équivalent iTop.

3.  Allez maintenant sur l’onglet "configuration ⇒ actions automatiques
    ⇒ itopExport" afin d’executer une synchronisation ou en
    planifier une.

4.  Sur iTop, allez maintenant dans le menu "Synchronisation" pour
    vérifier que les données ont bien été importées dans les
    différentes entités.