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
 * limitations under the License. - modified 2025 by github.com/g7sim
 */

(function(window) {
    "use strict";

    let maxLoadedId = 0;
    /** @type {Map<number, {id: number, ref: HTMLElement, src: string, type: string}>} */
    const loadedElements = new Map();
    /** @type {Map<string, number>} */
    const urlToIdMap = new Map(); // For faster unload by URL

    /**
     * Checks if a value is a valid number.
     * @param {*} n - The value to check.
     * @returns {boolean} True if n is a finite number.
     */
    function isNumber(n) {
        return typeof n === 'number' && isFinite(n);
    }

    /**
     * Extracts the file extension from a URL, ignoring query parameters and fragments.
     * @param {string} url - The URL string.
     * @returns {string|null} The file extension (e.g., "js", "css") or null if not found.
     */
    function getUrlExtension(url) {
        if (typeof url !== 'string' || !url) {
            return null;
        }
        
        const path = url.split(/[?#]/)[0];
        
        const match = path.match(/\.([^./\\]+)$/);
        return match ? match[1].toLowerCase() : null;
    }

    /**
     * Adds a reference to the shared loaded element map.
     * @param {string} url - The URL of the loaded resource.
     * @param {HTMLElement} ref - The DOM element reference (<script> or <link>).
     * @param {string} type - The type of resource ('js' or 'css').
     * @returns {number} The unique ID assigned to this element.
     */
    function addToList(url, ref, type) {
        maxLoadedId++;
        const entry = {
            id: maxLoadedId,
            ref: ref,
            src: url,
            type: type
        };
        loadedElements.set(maxLoadedId, entry);
        urlToIdMap.set(url, maxLoadedId); // Add to URL lookup map
        console.log(`Added to list: ID=${maxLoadedId}, URL=${url}`); // Debug log
        return maxLoadedId;
    }

    /**
     * Removes an element reference from the shared internal maps by its ID.
     * @param {number} id - The ID of the element to remove.
     * @returns {boolean} True if the element was found and removed, false otherwise.
     */
    function removeFromListById(id) {
        const entry = loadedElements.get(id);
        if (entry) {
            const url = entry.src; // Get URL before deleting
            loadedElements.delete(id);
            urlToIdMap.delete(url); // Remove from URL lookup map using the stored URL
            console.log(`Removed from list: ID=${id}, URL=${url}`); // Debug log
            return true;
        }
        console.log(`Attempted remove from list, ID not found: ${id}`); // Debug log
        return false;
    }

    /**
     * Removes the DOM element safely.
     * @param {HTMLElement} element - The DOM element to remove.
     */
    function removeDomElement(element) {
        if (element && element.parentNode) {
            try {
                element.parentNode.removeChild(element);
                console.log(`Removed DOM element: ${element.tagName} src/href=${element.src || element.href}`); // Debug log
            } catch (e) {
                console.error("ScriptLoader: Failed to remove DOM element.", e, element);
            }
        } else {
             console.log(`Attempted remove DOM element, but element or parentNode missing.`); // Debug log
        }
    }

    // --- Constructor Function ---
    /**
     * Creates an instance of the Script Loader.
     * Note: All instances share the same list of loaded resources.
     * @constructor
     */
    window.Script_loader = function() {

        return {
            /**
             * Gets a copy of the list of loaded element details.
             * @returns {Array<{id: number, ref: HTMLElement, src: string, type: string}>} An array of loaded element objects.
             */
            getLoadedElements: function() {
                // Return a copy to prevent external modification
                return Array.from(loadedElements.values());
            },

            /**
             * Gets an array of the IDs of all currently loaded elements.
             * @returns {number[]} An array of loaded element IDs.
             */
            getIds: function() {
                return Array.from(loadedElements.keys());
            },

            /**
             * Loads a script or stylesheet dynamically.
             * @param {string} url - The URL of the script (.js) or stylesheet (.css) to load.
             * @param {function} [callback] - Optional function to execute when the script loads successfully. Not reliably called for CSS.
             * @returns {number} The unique ID assigned to the loaded element.
             * @throws {Error} If the URL is invalid or the file type is unsupported.
             */
            load: function(url, callback) {
                if (typeof url !== 'string' || !url.trim()) {
                    throw new Error("ScriptLoader.load: Argument 1 must be a non-empty URL string.");
                }
                if (callback && typeof callback !== 'function') {
                    console.warn("ScriptLoader.load: Optional callback argument must be a function.");
                    callback = null; // Ignore invalid callback
                }

                if (urlToIdMap.has(url)) {
                    const existingId = urlToIdMap.get(url);
                    console.warn(`ScriptLoader.load: URL "${url}" (ID: ${existingId}) is already loaded or loading.`);
                    // Optionally call callback immediately if it's already loaded and was a script?
                    // Or just return the existing ID.
                    return existingId;
                }

                const type = getUrlExtension(url);
                const head = document.head || document.getElementsByTagName('head')[0];

                if (!head) {
                    throw new Error("ScriptLoader.load: Cannot find document <head> element.");
                }

                let element;
                let elementId = -1; // Placeholder

                const handleLoad = () => {
                    // Cleanup listeners to prevent memory leaks
                    if (element.removeEventListener) { // Standard
                        element.removeEventListener('load', handleLoad);
                        element.removeEventListener('error', handleError);
                    } else if (element.detachEvent) { // IE < 9 fallback
                        element.detachEvent('onload', handleLoad);
                        element.detachEvent('onerror', handleError);
                    }
                    
                    element.onreadystatechange = null;

                    if (callback) {
                        try {
                            callback();
                        } catch (e) {
                            console.error(`ScriptLoader: Error in callback for ${url}`, e);
                        }
                    }
                };

                const handleError = (errorEvent) => {
                   
                    if (element.removeEventListener) {
                        element.removeEventListener('load', handleLoad);
                        element.removeEventListener('error', handleError);
                    } else if (element.detachEvent) {
                        element.detachEvent('onload', handleLoad);
                        element.detachEvent('onerror', handleError);
                    }
                    element.onreadystatechange = null;

                    console.error(`ScriptLoader.load: Failed to load script from ${url}`, errorEvent);
                    
                    if (elementId !== -1) {
                        removeFromListById(elementId); 
                    }
                    
                };


                switch (type) {
                    case "js":
                        element = document.createElement("script");
                        element.src = url;
                        element.type = "text/javascript";
                        element.async = true; // Load asynchronously

                        element.addEventListener('load', handleLoad, { once: true });
                        element.addEventListener('error', handleError, { once: true });

                        // IE fallback (mainly IE < 9)
                        element.onreadystatechange = function() {
                            
                            if (this.readyState === 'loaded' || this.readyState === 'complete') {
                                
                                element.onreadystatechange = null; 
                                handleLoad();
                            }
                        };

                        head.appendChild(element);
                        elementId = addToList(url, element, type); 
                        break;

                    case "css":
                        element = document.createElement("link");
                        element.href = url;
                        element.type = "text/css";
                        element.rel = 'stylesheet';

                        if (callback) {
                            console.warn("ScriptLoader.load: Callbacks for CSS loading are not reliably supported across all browsers.");
                           }

                        head.appendChild(element);
                        elementId = addToList(url, element, type); 
                        break;

                    default:
                        throw new Error(`ScriptLoader.load: Unknown or unsupported file type for URL: ${url} (Type detected: ${type})`);
                }

                return elementId;
            },

            /**
             * Unloads a previously loaded script or stylesheet.
             * @param {number|string} identifier - The ID (number) returned by load() or the URL (string) of the resource to unload.
             * @returns {boolean} True if the element was found and removed, false otherwise.
             * @throws {Error} If the identifier is missing or invalid.
             */
            unload: function(identifier) {
                if (identifier == null) { 
                    throw new Error("ScriptLoader.unload: Argument 1 (identifier) must be provided.");
                }

                let idToUnload = -1;

                if (isNumber(identifier)) {
                    idToUnload = identifier;
                } else if (typeof identifier === 'string' && identifier.trim()) {
                    // Lookup ID by URL using the shared secondary map
                    if (urlToIdMap.has(identifier)) {
                        idToUnload = urlToIdMap.get(identifier);
                    } else {
                        console.warn(`ScriptLoader.unload: URL "${identifier}" not found in loaded elements map.`);
                        return false; // URL not found
                    }
                } else {
                    throw new Error("ScriptLoader.unload: Identifier must be a number (ID) or a non-empty string (URL).");
                }

                // Now unload using the determined ID from the shared map
                if (idToUnload !== -1 && loadedElements.has(idToUnload)) {
                    const entry = loadedElements.get(idToUnload);
                    removeDomElement(entry.ref); // Remove from DOM first using shared function
                    return removeFromListById(idToUnload); // Then remove from shared tracking maps
                } else {
                    console.warn(`ScriptLoader.unload: Element with identifier "${identifier}" (resolved ID: ${idToUnload}) not found.`);
                    return false; // ID not found in map
                }
            }
        }; 
    }; 

})(window);




var loadercss = new Script_loader();

loadercss.load( './assets/css/core.css' );

var loadsfont = new Script_loader();

loadsfont.load( './assets/css/system-font.css' );

var loadsfile = new Script_loader();

loadsfont.load( './plugins/filebrowser/css/stylesheet.css' );

var loaderjs = new Script_loader();

loaderjs.load( './assets/js/admin.min.js' );

