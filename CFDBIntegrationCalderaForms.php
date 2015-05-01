<?php

/*
    "Contact Form to Database" Copyright (C) 2011-2015 Michael Simpson  (email : michael.d.simpson@gmail.com)

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

class CFDBIntegrationCalderaForms {

    /**
     * @var CF7DBPlugin
     */
    var $plugin;

    /**
     * @param $plugin CF7DBPlugin
     */
    function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function registerHooks() {
        // http://docs.calderaforms.com/caldera_forms_submit_post_process_end/
        add_action('caldera_forms_submit_post_process_end', array(&$this, 'saveFormData'), 10, 3);
    }

    /**
     * @param $form array
     * @param $referrer array
     * @param $process_id string
     * @return bool
     */
    public function saveFormData($form, $referrer, $process_id) {
        try {
            // debug
//            $this->plugin->getErrorLog()->log('$form: ' . print_r($form, true));
//            $this->plugin->getErrorLog()->log('$referrer: ' . print_r($referrer, true));
//            $this->plugin->getErrorLog()->log('$process_id: ' . print_r($process_id, true));
//            global $processed_data;
//            $this->plugin->getErrorLog()->log('$processed_data: ' . print_r($processed_data, true));

            $data = $this->convertData($form);
            return $this->plugin->saveFormData($data);
        } catch (Exception $ex) {
            $this->plugin->getErrorLog()->logException($ex);
        }
        return true;
    }

    /**
     * @param $form array
     * @return null|object
     */
    public function convertData($form) {
        global $processed_data;

        if (is_array($form) && is_array($processed_data)) {
            $title = $form['name'];
            $postedData = array();
            $uploadedFiles = array();

            $results = $processed_data[$form['ID']];

            foreach ($results as $field_id => $field_value) {

                if (!array_key_exists($field_id, $form['fields'])) {
                    // ignore non-field entries _entry_id and _entry_token
                    continue;
                }
                $field_name = $form['fields'][$field_id]['label'];
                $is_file = $form['fields'][$field_id]['type'] == 'file';

                if (is_array($field_value)) {
                    $postedData[$field_name] = implode(',', $field_value);
                } else if ($is_file && $field_value != null)  {
                    // $field_value is a URL to the file like
                    // http://SITE.com/wp-content/uploads/2015/05/my_file.png
                    $postedData[$field_name] = basename($field_value);
                    $path = get_home_path() . $this->getUrlWithoutSchemeHostAndPort($field_value);
                    $uploadedFiles[$field_name] = $path;
                } else {
                    $postedData[$field_name] = $field_value;
                }
            }

            return (object)array(
                    'title' => $title,
                    'posted_data' => $postedData,
                    'uploaded_files' => $uploadedFiles);
        }
        return null;
    }

    /**
     * Return the end part of a URL minus the "http://host:port" part
     * @param $url string
     * @return string|null
     */
    public function getUrlWithoutSchemeHostAndPort($url) {
        $matches = array();
        preg_match('#^http(s)?://[^/]+(.*)#', $url, $matches);
        if (count($matches) >= 3) {
            return $matches[2];
        }
        return null;
    }
}