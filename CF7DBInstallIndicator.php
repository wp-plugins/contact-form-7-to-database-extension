<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2010 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
require_once("CF7DBOptionsManager.php");

/**
 * The methods in this class are used to track whether or not the plugin has been installed.
 * It writes a value in options to indicate that this plugin is installed.
 *
 * @author Michael Simpson
 */

class CF7DBInstallIndicator extends CF7DBOptionsManager {

    const optionInstalled = '_installed';
    const optionVersion = '_version';

    /**
     * @return bool indicating if the plugin is installed already
     */
    public function isInstalled() {
        return $this->getOption(self::optionInstalled) == true;
    }

    /**
     * Note in DB that the plugin is installed
     * @return null
     */
    protected function markAsInstalled() {
        return $this->updateOption(self::optionInstalled, true);
    }

    /**
     * Note in DB that the plugin is uninstalled
     * @return bool returned form delete_option.
     * true implies the plugin was installed at the time of this call,
     * false implies it was not.
     */
    protected function markAsUnInstalled() {
        return $this->deleteOption(self::optionInstalled);
    }

    /**
     * Set a version string in the options. This is useful if you install upgrade and
     * need to check if an older version was installed to see if you need to do certain
     * upgrade housekeeping (e.g. changes to DB schema).
     * @param  $version
     * @return null
     */
    protected function getVersionSaved() {
        return $this->getOption(self::optionVersion);
    }

    /**
     * Set a version string in the options.
     * need to check if
     * @param  $version best practice: use a dot-delimited string like "1.2.3" so version strings can be easily
     * compared using version_compare (http://php.net/manual/en/function.version-compare.php)
     * @return null
     */
    protected function setVersionSaved($version) {
        return $this->updateOption(self::optionVersion, $version);
    }

    /**
     * Version of this code.
     * Override this function to set your current release version, e.g. "1.0", 1.1.1"
     * Best practice: define version strings to be easily compared using version_compare()
     * (http://php.net/manual/en/function.version-compare.php)
     * NOTE: You should manually make this match the SVN tag for your main plugin file "Version" release and "Stable tag" in readme.txt
     * @return string
     */
    public function getVersion() {
        return "0";
    }

    /**
     * Useful when checking for upgrades, can tell if the currently installed version is earlier than the
     * newly installed code. This case indicates that an upgrade has been installed and this is the first time it
     * has been activated, so any upgrade actions should be taken. 
     * @return bool true if the version saved in the options is earlier than the version declared in getVersion().
     * true indicates that new code is installed and this is the first time it is activated, so upgrade actions
     * should be taken. Assumes that version string comparable by version_compare, examples: "1", "1.1", "1.1.1", "2.0", etc.
     */
    public function isInstalledCodeAnUpgrade() {
        return $this->isSavedVersionLessThan($this->getVersion());
    }

    /**
     * Used to see if the installed code is an upgrade to the input version.
     * For example, $this->isInstalledCodeAnUpgradeToVersion("2.3") == true indicates that the
     * @param  $aVersion string
     * @return bool true if the saved version is earlier (by natural order) than the input version
     */
    public function isSavedVersionLessThan($aVersion) {
        return $this->isVersionLessThan($this->getVersionSaved(), $aVersion);
    }

    /**
     * @param  $version1 string a version string such as "1", "1.1", "1.1.1", "2.0", etc.
     * @param  $version2 string a version string such as "1", "1.1", "1.1.1", "2.0", etc.
     * @return bool true if version_compare of $versions1 and $version2 shows $version1 as earlier
     */
    public function isVersionLessThan($version1, $version2) {
        return (version_compare($version1, $version2) < 0);
    }

    /**
     * Record the installed version to options.
     * This helps track was version is installed so when an upgrade is installed, it should call this when finished
     * upgrading to record the new current version
     * @return void
     */
    protected function saveInstalledVersion() {
        $this->setVersionSaved($this->getVersion());
    }

}
