<?php

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
     * Version of this deployment.
     * Override this to set your current release version, e.g. "1.0", 1.1.1"
     * Best practice: define version strings to be easily compared using version_compare() 
     * (http://php.net/manual/en/function.version-compare.php)
     * NOTE: You should manually make this match the SVN tag for your release and "Stable tag" in readme.txt
     * @return string
     */
    public function getVersion() {
        return "0";
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
