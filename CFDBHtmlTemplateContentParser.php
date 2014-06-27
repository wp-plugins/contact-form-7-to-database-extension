<?php
/*
    "Contact Form to Database" Copyright (C) 2011-2013 Michael Simpson  (email : michael.d.simpson@gmail.com)

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


class CFDBHtmlTemplateContentParser {

    const HEADER_START_DELIMITER = '{{HEADER}}';
    const HEADER_END_DELIMITER = '{{/HEADER}}';

    const FOOTER_START_DELIMITER = '{{FOOTER}}';
    const FOOTER_END_DELIMITER = '{{/FOOTER}}';

    /**
     * @param $content string
     * @return array[$header, $template, $footer]
     */
    public function parseHeaderTemplateFooter($content) {
        $header = null;
        $startDelimiter = self::HEADER_START_DELIMITER;
        $endDelimiter = self::HEADER_END_DELIMITER;
        $startDelimiterStartPos = strpos($content, $startDelimiter);
        $endDelimiterStartPos = strpos($content, $endDelimiter);
        if ($startDelimiterStartPos !== false &&
                $endDelimiterStartPos !== false &&
                $startDelimiterStartPos < $endDelimiterStartPos) {
            $startDelimiterEndPos = $startDelimiterStartPos + strlen($startDelimiter);
            $endDelimiterEndPos = $endDelimiterStartPos + strlen($endDelimiter);
            $header = substr($content, $startDelimiterEndPos, $endDelimiterStartPos - $startDelimiterEndPos);
            $content = substr($content, $endDelimiterEndPos, strlen($content) - $startDelimiterEndPos);
        }

        $footer = null;
        $startDelimiter = self::FOOTER_START_DELIMITER;
        $endDelimiter = self::FOOTER_END_DELIMITER;
        $startDelimiterStartPos = strpos($content, $startDelimiter);
        $endDelimiterStartPos = strpos($content, $endDelimiter);
        if ($startDelimiterStartPos !== false &&
                $endDelimiterStartPos !== false &&
                $startDelimiterStartPos < $endDelimiterStartPos) {
            $startDelimiterEndPos = $startDelimiterStartPos + strlen($startDelimiter);
            $footer = substr($content, $startDelimiterEndPos, $endDelimiterStartPos - $startDelimiterEndPos);
            $content = substr($content, 0, $startDelimiterStartPos);
        }

        return array($header, $content, $footer);
    }

} 