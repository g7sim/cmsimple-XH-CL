/*
 * Copyright 07-04-2015 Bl00dsoul
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
 
(function(){
	
	// private variables, global for all instances of the functions within this closure
	var max_loaded_id = 0;
	var loaded_elements = [];

	// add our constructor function to the global scope.
	window.Script_loader = function(){	

		// private functions
		function isNumber(n) {
			// returns true if n is a number
			return !isNaN(parseFloat(n)) && isFinite(n);
		}
		function isset( argument ){
			// returns true if argument has a value
			if( typeof(argument) !== 'undefined' ){
				if( isNumber(argument) ){
					return true;
				}
				if( argument !== null && argument !== "" ){
					return true;
				}
			}
			return false;
		}
		function add_to_element_list( url, ref ){
			// function stores a reference to the inserted element in the loaded_elements array

			max_loaded_id++;				
			loaded_elements[ max_loaded_id ] = {
				"id" : max_loaded_id,
				"ref" : ref,
				"src" : url
			};
			return max_loaded_id;
		}
		function remove_from_element_list( obj ){
			// function removes a reference to an element from the loaded_elements array

			if( isset(obj) ){
				var index = loaded_elements.indexOf(obj);
				loaded_elements[ index ] = undefined;

				return true;
			}	
			return false;
		}
		function get_element_list(){
			// return the loaded_elements array

			return loaded_elements;
		}
		function get_loaded_ids(){
			// return an array of all ids of the reference objects in the loaded_elements array
		
			var id_array = [];
			for( var i = 0; i <= max_loaded_id; i++ ){
				if( isset(loaded_elements[i]) && isset( loaded_elements[i].id ) ){
					id_array.push( loaded_elements[i].id );
				}
			}
			return id_array;
		}


		// public object
		return {
			"loaded_elements" : function(){
				return get_element_list();
			},
			"get_ids" : function(){
				return get_loaded_ids();
			},
			"load" : function( url, callback ){
				// add the script / stylesheet 'url' to the <head>
		
				if( !isset(url) ){
					throw new Error("function expects argument 1 to be an url.");
				}

				// determine type
				var type_info = url.split(".");
				var type = type_info[type_info.length - 1];

				// functions inserted after the loading element can not be called by it, so we always insert the scripts before it.
				var scripts = document.getElementsByTagName('head')[0].getElementsByTagName("script");
				var loading_element = scripts[ scripts.length - 1 ];

				// append to head
				switch(type){
					case "js":
						var script = document.createElement("script");
						script.src = url;
						script.type = "text/javascript";
						script.onreadystatechange = function () {		// IE compatibility mode
							if (this.readyState == 'complete'){
								if( isset(callback) && typeof(callback) == "function" ){
									callback();
								}
							}
						}
						script.onload = callback;
						var ref = document.getElementsByTagName('head')[0].insertBefore(script, loading_element);
					 break;
					case "css":
						var css = document.createElement("link");
						css.href = url;
						css.type = "text/css";
						css.rel  = 'stylesheet';
						var ref = document.getElementsByTagName('head')[0].insertBefore(css, loading_element);
					 break;
					default:
						throw new Error("Unknown type: " + type + ".");
					break;
				}
				return add_to_element_list( url, ref );
			},
			"unload" : function( identifier ){
				// remove the element that corresponds the to id given in the array loaded_elements

				if( !isset(identifier) ){
					throw new Error("function expects argument 1 to be an id or url.");
				}
			
				var ids = get_loaded_ids();
				var element_list = get_element_list();

				if( isNumber(identifier) ){
					// delete based on id

					if( isset(element_list[identifier]) ){
						document.getElementsByTagName('head')[0].removeChild( element_list[identifier].ref );
					}

					remove_from_element_list( element_list[identifier] );

				} else {
					// delete based on url

					ids.forEach( function( id ){
						if( isset(element_list[id]) && isset(element_list[id].src) ){
							if( element_list[id].src == identifier ){

								document.getElementsByTagName('head')[0].removeChild( element_list[id].ref );
								remove_from_element_list( element_list[id] );
							}
						}
					});
				}
			}
		};
	}
})();





var loadercss = new Script_loader();

loadercss.load( './assets/css/core.css' );

var loaderjs = new Script_loader();

loaderjs.load( './assets/js/admin.min.js' );

