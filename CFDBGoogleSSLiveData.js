/*
 "Contact Form to Database" Copyright (C) 2011-2014 Michael Simpson  (email : michael.d.simpson@gmail.com)

 This file is part of Contact Form to Database.

 Contact Form to Database is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Contact Form to Database is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Contact Form to Database.
 If not, see <http://www.gnu.org/licenses/>.
 */

/* This is a script to be used with a Google Spreadsheet to make it dynamically load data (similar to Excel IQuery)
 Instructions:
1. Create a new Google Spreadsheet
2. Go to Tools menu -> Script Editor...
3. Click Spreadsheet
4. Copy the text from this file and paste it into the Google script editor.
5. Save and close the script editor.
6. Click on a cell A1 in the Spreadsheet (or any cell)
7. Enter in the cell the formula:
   =cfdbdata("site_url", "form_name", "user", "password")
  Where the parameters are (be sure to quote them):
    site_url: the URL of you site, e.g. "http://www.mywordpress.com"
    form_name: name of the form
    user: your login name on your WordPress site
    pwd: password
*/

/**
 * Use this function in your spreadsheet to fetch saved form data from your WordPress Site
 * @param site_url your top level WordPress site URL
 * @param form_name name of the WordPress form to fetch data from
 * @param user login name to your WordPress site. User must have permission to view form data
 * @param password WordPress site password. If your site_url is "http" and not "https" then
 * beware that your password is being sent unencrypted from a Google server to your WordPress server.
 * Also beware that others who can view this code in your Google Spreadsheet can see this password.
 * @param "option_name1", "option_value1", "option_name2", "option_value2", ... (optional param pairs).
 * These are CFDB option such as "filter", "name=Smith", "show", "first_name,last_name"
 * These should come in pairs.
 * @returns {*} Your WordPress saved form data in a format suitable for Google Spreadsheet to display.
 * String error message if there is an error logging into the WordPress site
 */
function cfdbdata(site_url, form_name, user, password /*, [option_name, option_value] ... */) {
    var param_array = [];
    param_array.push("action=cfdb-login");
    param_array.push("username=" + encodeURI(user));
    param_array.push("password=" + encodeURI(password));
    param_array.push("cfdb-action=cfdb-export");
    param_array.push("form=" + encodeURI(form_name));

    var args = arg_slice(arguments, 4);
    args = process_name_value_args(args);
    param_array = param_array.concat(args);

    return fetch_csv_url(site_url, param_array);
}

/**
 * @deprecated for backward compatibility. Use cfdbdata() instead.
 */
function CF7ToDBData(site_url, form_name, search, user, password) {
    if (search != "") {
        return cfdbdata(site_url, form_name, user, password, "search", search);
    }
    return cfdbdata(site_url, form_name, user, password);
}

/**
 * "slice" function for varargs Argument object
 * @param args Argument object
 * @param position int > 0 indicating the slice position
 * @returns {Array} of args from the slide index to the end.
 * Returns empty array if slice position exceeds length of args
 */
function arg_slice(args, position) {
    var array = [];
    if (args.length > position) {
        for (var i = position; i < args.length; i++) {
            array.push(args[i]);
        }
    }
    return array;
}

/**
 * Converts array like ['a', '1', 'b', '2'] to ['a=1', 'b=2']
 * where each value is made to be URI-encoded.
 * Purposed of this is to transform and array of name,value arguments
 * into HTTP GET/POST parameters
 * @param array Array like ['a', '1', 'b', '2']
 * @returns {Array} like ['a=1', 'b=2'].
 * If there is an odd number of arguments then the last one is dropped
 * (expecting pairs of name,value)
 */
function process_name_value_args(array) {
    var name_value_array = [];
    var flag = true;
    var name = null;
    for (var i = 0; i < array.length; i++) {
        if (flag) {
            name = array[i];
        } else {
            name_value_array.push(encodeURI(name) + "=" + encodeURI(array[i]));
        }
        flag = !flag;
    }
    return name_value_array;
}

/**
 * Fetch CSV data from WordPress URL. Specific to CFDB plugin.
 * Uses CFDB plugin CSV export URL
 * @param site_url WordPress site top level URL
 * @param param_array array of URI-encoded ["name=value"] elements intended as POST parameters
 * @returns {*} array (multidimensional) of data suitable for Google Spreadsheet to display.
 * String error message if there is a login failure
 */
function fetch_csv_url(site_url, param_array) {
    var url = site_url + "/wp-admin/admin-ajax.php";
    var payload = param_array.join("&");
    var response = UrlFetchApp.fetch(url, { method: "post", payload: payload });
    var content = response.getContentText();
    if (content.indexOf("<strong>ERROR") == 0) {
        // If error message is returned, just return that as the content
        return content;
    }
    return csvToArray(content);
}

/**
 * Taken from: http://stackoverflow.com/questions/1293147/javascript-code-to-parse-csv-data
 * Used to parse csv text into an array that Google Spreadsheet can put into cells
 * @param text String csv text
 * @returns {Array} of data suitable for Google Spreadsheets to display
 */
function csvToArray(text) {
    text = csvParseToArray(text, ",");
    var arr = [];
    var c = [];
    for (var i = 0; i < text.length - 1; i++) {
        c = [];
        for (var j = 0; j < text[0].length; j++) {
            c.push(text[i][j]);
        }
        arr.push(c);
    }
    return arr;
}

/**
 * Taken from: http://stackoverflow.com/questions/1293147/javascript-code-to-parse-csv-data
 * This will parse a delimited string into an array of arrays.
 * @param strData String csv text
 * @param strDelimiter String optional. The default delimiter is the comma.
 * @returns {*[]}
 * @constructor
 */
function csvParseToArray(strData, strDelimiter) {
    // Check to see if the delimiter is defined. If not,
    // then default to comma.
    strDelimiter = (strDelimiter || ",");

    // Create a regular expression to parse the CSV values.
    var objPattern = new RegExp(
            (
                // Delimiters.
                    "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

                        // Quoted fields.
                            "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

                        // Standard fields.
                            "([^\"\\" + strDelimiter + "\\r\\n]*))"
                    ),
            "gi"
    );

    // Create an array to hold our data. Give the array
    // a default empty first row.
    var arrData = [
        []
    ];

    // Create an array to hold our individual pattern
    // matching groups.
    var arrMatches;

    // Keep looping over the regular expression matches
    // until we can no longer find a match.
    while (arrMatches = objPattern.exec(strData)) {

        // Get the delimiter that was found.
        var strMatchedDelimiter = arrMatches[ 1 ];

        // Check to see if the given delimiter has a length
        // (is not the start of string) and if it matches
        // field delimiter. If id does not, then we know
        // that this delimiter is a row delimiter.
        if (
                strMatchedDelimiter.length &&
                        (strMatchedDelimiter != strDelimiter)
                ) {

            // Since we have reached a new row of data,
            // add an empty row to our data array.
            arrData.push([]);

        }

        // Now that we have our delimiter out of the way,
        // let's check to see which kind of value we
        // captured (quoted or unquoted).
        var strMatchedValue;
        if (arrMatches[ 2 ]) {

            // We found a quoted value. When we capture
            // this value, unescape any double quotes.
            strMatchedValue = arrMatches[ 2 ].replace(
                    new RegExp("\"\"", "g"),
                    "\""
            );
        } else {
            // We found a non-quoted value.
            strMatchedValue = arrMatches[ 3 ];
        }

        // Now that we have our value string, let's add
        // it to the data array.
        arrData[ arrData.length - 1 ].push(strMatchedValue);
    }

    // Return the parsed data.
    return( arrData );
}
