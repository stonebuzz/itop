# iTop plugin for GLPI

**Please note that an [iTop module, provided by Teclib'](https://github.com/TECLIB/teclib-itop-glpi-module), is also required for the synchronization to work.**

## Features

The plugin allows you to export GLPI inventory data into iTop CMDB.

The itop plugin adds multiple objects (in typology section) :

### iTop instance

This object contains informations about one instance of remote itop. An instance can be linked to multiple data sources.

**fields (each field is mandatory)**

- name     : name of itop instance
- url      : url to itop instance, used to generate access url to itop api
- user     : username to access itop, must be allowed to use API
- password : password used to authenticate the specified username through the API
- comment  : text that will be used to populate modifications history log in iTop
- version  : version of the api used, used to generate the url

### iTop instance

This object is the equivalent of itop data source class, it contains each informations and parameters like table name, obsolescence etc.

**fields**

- name                    : name of the data source
- description             : comment about this data source (for documentation)
- itop instance           : itop instance where this synchro has to be created
- status                  : iTop status of the synchro, production, implementation or obsolete (for documentation)
- user                    : OQL request to retrieve the user (in itop) which will execute this synchro
- contact                 : OQL request to retrieve the contact which will be notified with results of each synchro 
- itop scope class        : dropdown filled with itop classes list (PC, UserRequest, etc)
- itop scope restriction  : not used yet
- glpi scope class        : itemtype which will be used to fill csv files before injecting in itop stagging tables
- glpi scope restriction  : SQL request which will be used to retrieve data in glpi and construct csv file, columns returned by the sql request has to match the mapped fields between glpi and itop 
- database table name     : name of the stagging table which will be created in itop database to be filled with temporary data 

- reconciliation policy   : choose between "Use the fields" or "Use primary key", if you select "Use the fields" you have to specify one or more fields as reconciliation fields in mapping field tab. If you select "Use primary key" your SQL request in glpi scope restriction has to contain a column named "primary_key" which will be used in itop stagging table as primary key.
- action on zero          : operation executed by the synchro when 0 element is found in itop with reconciliation fields (or primary key), create the missing element or raise an error
- action on one           : operation executed by the synchro when 1 element is found, update or raise an error
- action on multiple      : operation executed by the synchro when multiple elements is found, take the first or raise an error

- full load periodicity   : delay before considering an element of the data source as "obsolete"
- delete policy           : which operation has to be executed on a obsolete element, ignore it, delete it, update it or update it then delete it after a retention delay 
- delete policy update    : if the delete policy is setted up on "update" or "update then delete" you have to specify which fields will be updated and with values (field:value)
- delete policy retention : if the delete policry is setted up on "update then delete", specify here the delay between the update and the deletion of the element 

- buttons : create, update or delete the data source (in itop thorugh the API), export to json (the json can be used with CLI exec.php file to import instance, synchro and fields definitons)

### iTop fields

This object is the equivalent of itop fields tab (on data source synchro), this object permits to specify which field is a reconciliation field, if we have to update it, if we have to lock it (disable modification etc)

**fields**

- glpi attribute     : the equivalent of this field on glpi
- itop attribute     : the name of the field on itop
- reconciliation     : specify if this field is used to reconciliate element (multiple reconciliation fields is possible)
- update             : specify if this field has to be updated or not
- update policy      : if you choose "master locked", this field is updated each time and nobody can modify it through iTop UI. if you choose "master unlocked" it will be updated each time and can be modified through iTop UI. if you choose "write if field is empty" the synchro will fill this field only if no value is present
- reconciliation key : if the field is a relation on itop, you can choose here which field on relation object will be used to reconciliate it (name, serial, etc)

### CLI 



## Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer
