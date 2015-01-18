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

class CFDBIntegrationWRContactForm {

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
        add_action('wr_contactform_before_save_form', array(&$this, 'saveFormData'), 10, 7);
    }

    /**
     * @param $dataForms array
     * @param $postID array
     * @param $post array
     * @param $submissionsData array
     * @param $dataContentEmail array
     * @param $nameFileByIdentifier array
     * @param $requiredField array
     * @param $fileAttach array
     * @return bool
     */
    public function saveFormData($dataForms, $postID, $post, $submissionsData, $dataContentEmail,
                                 $nameFileByIdentifier, $requiredField, $fileAttach) {

        try {
            $data = $this->convertData($postID, $post, $nameFileByIdentifier, $fileAttach);

            return $this->plugin->saveFormData($data);
        } catch (Exception $ex) {
            $this->plugin->getErrorLog()->logException($ex);
        }
        return true;
    }


    /**
     * @param $postID array
     * @param $post array
     * @param $nameFileByIdentifier array
     * @param $fileAttach array
     * @return object
     */
    public function convertData($postID, $post, $nameFileByIdentifier, $fileAttach) {

        $postedData = array();
        $uploadFiles = array();


        // assume $nameFileByIdentifier and $post in same order
        $fieldNames = array_values($nameFileByIdentifier);
        $fieldValues = array_values($post);
        $fieldTypes = array_keys($post);

        for ($idx = 0; $idx < count($fieldNames); $idx++) {
            $fieldName = $fieldNames[$idx];
            $fieldValue = $fieldValues[$idx];
            $fieldType = $fieldTypes[$idx];

            if (is_array($fieldValue)) {
                switch ($fieldType) {
                    case 'name':
                        $tmp = array();
                        // todo

                        break;

                    default:
                        break;
                }

            } else {
                $postedData[$fieldName] = $fieldValue;
            }

            // todo
        }

        // TODO: handle upload files

        $data = (object)array(
                'title' => get_the_title($postID),
                'posted_data' => $postedData,
                'uploaded_files' => $uploadFiles);
        return $data;
    }



}