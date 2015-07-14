<?php

/**
 * Author: Maksim Kirillov (kirillovmax@gmail.com)
 * Date: 11/7/12
 * Package: MPP
 */

require_once "phing/Task.php";

class MMPTask extends Task {

    /**
     * The params passed in the buildfile.
     */
    private $params = array();

    /**
     * The config_file passed in the buildfile.
     */
    private $config_file = null;

    /**
     * Config options for MMP.
     */
    private $options = array();

    /**
     * Migration options for MMP.
     */
    private $action_options = null;

    /**
     * Migration options for MMP.
     */
    private $action = null;

    /**
     * The setter for params["host"]
     */
    public function setHost($str) {
        $this->params['host'] = $str;
    }

    /**
     * The setter for params["user"]
     */
    public function setUser($str) {
        $this->params['user'] = $str;
    }

    /**
     * The setter for params["password"]
     */
    public function setPassword($str) {
        $this->params['password'] = $str;
    }

    /**
     * The setter for params["db"]
     */
    public function setDb($str) {
        $this->params['db'] = $str;
    }

    /**
     * The setter for params["versiontable"]
     */
    public function setVersiontable($str) {
        $this->params['versiontable'] = $str;
    }

    /**
     * The setter for params["savedir"]
     */
    public function setSavedir($str) {
        $this->params['savedir'] = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $str;
    }

    /**
     * The setter for params["verbose"]
     */
    public function setVerbose($str) {
        $this->params['verbose'] = $str;
    }

    /**
     * The setter for params["excludetables"]
     */
    public function setExclude_tables($str) {
        $this->params['exclude_tables'] = array_filter(explode(",",preg_replace("/\s/m", "", $str)));
    }

    /**
     * The setter for the attribute "config_file"
     */
    public function setConfig_file($str) {
        $this->config_file = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $str;
    }

    /**
     * The setter for the attribute "action_options"
     */
    public function setAction_options($str) {
        $this->action_options = explode(" ", trim($str, " "));
    }

    /**
     * The setter for the attribute "action"
     */
    public function setAction($str) {
        $this->action = $str;
    }

    /**
     * The init method.
     */
    public function init() {
        require_once __DIR__ . '/../init.php';
    }

    /**
     * The main entry point method.
     */
    public function main() {
        if(file_exists($this->config_file)){
            $this->options = parse_ini_file($this->config_file);
        }
        $this->options = array_replace($this->options, $this->params); //task params overrides everything

        Helper::setConfig($this->options);

        $controller = Helper::getController($this->action, $this->action_options);

        if($controller !== false){
            $controller->runStrategy();
        }else{
            Output::error('mmp: unknown command "' . $this->action . '"');
            Helper::getController('help')->runStrategy();
            exit(1);
        }
    }
}

?>