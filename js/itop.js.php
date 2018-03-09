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
global $CFG_GLPI;

$root_ajax = $CFG_GLPI['root_doc']."/plugins/itop/ajax/ajax.php";
$question = __('Are you sure you want to delete this object ?', 'itop');
$question2 = __('Are you sure you want to delete this status ?', 'itop');

$JS = <<<JAVASCRIPT


function checkOQL(DOM,instance,className){

    var request    = $("#"+DOM).val();

    console.log(request);
   

     /* $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "{$root_ajax}", // url du fichier php
        data: "action=checkOQL&" +
            "request=" + request +
            "class=" + className +
            "instance=" + instance , // données à transmettre
      success: function (response) { // si l'appel a bien fonctionné
        window.location.reload();
      },
      error: function () {
        alert("Ajax error");
      }
    });*/



}


function testConnection(){

   var host    = $("input[type=text][name=url]" ).val();
   var login   = $("input[type=text][name=login]" ).val();
   var mdp     = $("input[type=password][name=password]" ).val();
   var version = $("input[type=text][name=version]" ).val();
   var sURL    = host+"/webservices/rest.php?version="+version;
   $('#result').html('');

   var oJSON = {
      operation: 'list_operations'
   };

    $.ajax({
         type: "POST",
         url: sURL,
         dataType: 'json',
         data: { auth_user: login, auth_pwd: mdp, json_data: JSON.stringify(oJSON) },
         crossDomain: 'true',
         success: function (data) {
            $('#result').html('OK');
         },
         error: function (data){
            $('#result').html(syntaxHighlight(data));
         }
    });

}

function syntaxHighlight(json) {
    if (typeof json != 'string') {
         json = JSON.stringify(json, undefined, 2);
    }
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}





function deleteStatus(id){

	if (confirm("{$question2}")) {

		$.ajax({ // fonction permettant de faire de l'ajax
				type: "POST", // methode de transmission des données au fichier php
				url: "{$root_ajax}", // url du fichier php
				data: "action=deleteStatus&" +
					  "id=" + id, // données à transmettre
			success: function (response) { // si l'appel a bien fonctionné
				window.location.reload();
			},
			error: function () {
				alert("Ajax error");
			}
		});

	}



}

function deleteMatche(id){

	if (confirm("{$question}")) {

		$.ajax({ // fonction permettant de faire de l'ajax
				type: "POST", // methode de transmission des données au fichier php
				url: "{$root_ajax}", // url du fichier php
				data: "action=deleteMatch&" +
					  "id=" + id, // données à transmettre
			success: function (response) { // si l'appel a bien fonctionné
				window.location.reload();
			},
			error: function () {
				alert("Ajax error");
			}
		});

	}



}

function deleteSoftwareCategory(id){

	if (confirm("{$question}")) {

		$.ajax({ // fonction permettant de faire de l'ajax
				type: "POST", // methode de transmission des données au fichier php
				url: "{$root_ajax}", // url du fichier php
				data: "action=deleteSoftwareCategory&" +
					  "id=" + id, // données à transmettre
			success: function (response) { // si l'appel a bien fonctionné
				window.location.reload();
			},
			error: function () {
				alert("Ajax error");
			}
		});

	}



}

function changeTypeGlpiItem(){

	var glpiType = $("#dropdown_itemtype").find(":selected").val();
	var drop = $("#typeItemType");

	if(glpiType != 0){

		drop.empty();

		$.ajax({ // fonction permettant de faire de l'ajax
			type: "POST", // methode de transmission des données au fichier php
			url: "{$root_ajax}", // url du fichier php
			data: "action=getComboType&" +
				  "itemtype=" + glpiType, // données à transmettre
		success: function (response) { // si l'appel a bien fonctionné
			drop.html(response);
		},
		error: function () {
			drop.html("Ajax error");
		}

		});


	}


}


JAVASCRIPT;

echo $JS;
