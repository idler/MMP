<?php

/**
 * Author: Maksim Kirillov (kirillovmax@gmail.com)
 * Date: 11/7/12
 * Package: MPP
 */
class MMPTask extends Task
{
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
     *
     * @param $str
     */
    public function setHost($str)
    {
        $this->params['host'] = $str;
    }

    /**
     * The setter for params["user"]
     *
     * @param $str
     */
    public function setUser($str)
    {
        $this->params['user'] = $str;
    }

    /**
     * The setter for params["password"]
     *
     * @param $str
     */
    public function setPassword($str)
    {
        $this->params['password'] = $str;
    }

    /**
     * The setter for params["db"]
     *
     * @param $str
     */
    public function setDb($str)
    {
        $this->params['db'] = $str;
    }

    /**
     * The setter for params["versiontable"]
     *
     * @param $str
     */
    public function setVersiontable($str)
    {
        $this->params['versiontable'] = $str;
    }

    /**
     * The setter for params["aliastable"]
     *
     * @param $str
     */
    public function setAliastable($str)
    {
        $this->params['aliastable'] = $str;
    }

    /**
     * The setter for params["aliasprefix"]
     *
     * @param $str
     */
    public function setAliasprefix($str)
    {
        $this->params['aliasprefix'] = $str;
    }

    /**
     * The setter for params["savedir"]
     *
     * @param $str
     */
    public function setSavedir($str)
    {
        $this->params['savedir'] = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$str;
    }

    /**
     * The setter for params["verbose"]
     *
     * @param $str
     */
    public function setVerbose($str)
    {
        $this->params['verbose'] = $str;
    }

    /**
     * The setter for params["excludetables"]
     *
     * @param $str
     */
    public function setExclude_tables($str)
    {
        $this->params['exclude_tables'] = array_filter(explode(',', preg_replace('/\\s/m', '', $str)));
    }

    /**
     * The setter for the attribute "config_file"
     *
     * @param $str
     */
    public function setConfig_file($str)
    {
        $this->config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$str;
    }

    /**
     * The setter for the attribute "action_options"
     *
     * @param $str
     */
    public function setAction_options($str)
    {
        $this->action_options = explode(' ', trim($str, ' '));
    }

    /**
     * The setter for the attribute "action"
     *
     * @param $str
     */
    public function setAction($str)
    {
        $this->action = $str;
    }

    /**
     * The init method.
     */
    public function init()
    {
        require_once __DIR__.'/../init.php';
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        if (file_exists($this->config_file)) {
            $this->options = parse_ini_file($this->config_file);
        }
        $this->options = array_replace($this->options, $this->params);
        //task params overrides everything
        Helper::setConfig($this->options);
        $controller = Helper::getController($this->action, $this->action_options);
        if ($controller !== false) {
            $controller->runStrategy();
        } else {
            Output::error('mmp: unknown command "'.$this->action.'"');
            Helper::getController('help')->runStrategy();
            die(1);
        }
    }
}