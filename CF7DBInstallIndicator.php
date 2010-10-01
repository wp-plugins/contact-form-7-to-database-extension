<?php

require_once("CF7DBOptionsManager.php");

/**
 * The methods in this class are used to track whether or not the plugin has been installed.
 * It writes a value in options to indicate that this plugin is installed.
 * 
 * @author Michael Simpson
 */

class CF7DBInstallIndicator extends CF7DBOptionsManager {

    // Having a constant here in a subclass causes a PHP Fatal error for some reason
    const optionInstalled = '_installed';

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
    public function markAsInstalled() {
        return $this->updateOption(self::optionInstalled, true);
    }

    /**
     * Note in DB that the plugin is uninstalled
     * @return bool returned form delete_option.
     * true implies the plugin was installed at the time of this call,
     * false implies it was not.
     */
    public function markAsUnInstalled() {
        return $this->deleteOption(self::optionInstalled);
    }

}
