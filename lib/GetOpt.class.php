<?php

class GetOpt
{
    protected static $default_config = array('optVal');
    private static   $errors         = array();

    /**
     * Parse and extract left-most options up to the first non-option argument
     *
     * @param array $args List of arguments to search through
     * @param array $opts Option templates. Defines rules for the options we need to parse
     *
     * @return array Extracted options
     */
    static function extractLeft(&$args, $opts)
    {
        $result       = array();
        self::$errors = array();
        $opts         = self::normalizeTpl($opts);
        $short_opts   = self::mapShortOpts($opts);
        $not_opts     = array();

        while (!empty($args)) {
            $arg = array_shift($args);
            if (preg_match('/^--([a-z][a-z\\-_]*)/i', $arg, $matches)) {
                $matches[1] = strtolower($matches[1]);
                if (isset($opts[$matches[1]])) {
                    try {
                        $result[$matches[1]] = self::parseValue($args, $arg, $opts[$matches[1]]);
                    } catch (Exception $e) {
                        self::$errors[] = $e->getMessage();

                        return false;
                    }
                } else {
                    self::$errors[] = 'Invalid option \''.$matches[1].'\'';

                    return false;
                }
            } elseif (preg_match('/^-([a-z])/', $arg, $matches)) {
                foreach (str_split($matches[1]) as $o) {
                    if (isset($short_opts[$o])) {
                        try {
                            $result[$short_opts[$o]] = self::parseValue($args, $arg, $opts[$short_opts[$o]]);
                        } catch (Exception $e) {
                            self::$errors[] = $e->getMessage();

                            return false;
                        }
                    } else {
                        self::$errors[] = 'Invalid option \''.$matches[1].'\'';

                        return false;
                    }
                }
            } else {
                array_push($not_opts,$arg);
            }
        }
        $args = $not_opts;
        return $result;
    }

    /**
     * Expand array values without custom keys into "'value' => true" pairs
     *
     * @param array $opts Array to process
     *
     * @return array Processed array
     */
    private static function normalizeTpl($opts)
    {
        foreach ($opts as &$tpl) {
            $ntpl = array();
            foreach ($tpl as $k => $t) {
                if (is_string($k)) {
                    $ntpl[$k] = $t;
                } elseif (is_int($k) && is_string($t)) {
                    $ntpl[$t] = true;
                }
            }
            $tpl = $ntpl;
        }

        return $opts;
    }

    /**
     * Get the associations between short and long options, if any exist
     *
     * @param array $opts Options to parse
     *
     * @return array List of mappings between short_options => long_options
     */
    private static function mapShortOpts($opts)
    {
        $result = array();
        foreach ($opts as $k => $o) {
            if (!empty($o['short'])) {
                $result[$o['short']] = $k;
            }
        }

        return $result;
    }

    /**
     * Get "value" part of the long, if any, from the arguments list.
     *
     * Note: $args might be modified depending on the given option template
     *
     * @param array  $args List of command-line arguments
     * @param string $arg  Argument being parsed
     * @param array  $tpl  Template for the argument being parsed
     *
     * @return mixed Parsed option value, null if no value required
     * @throws Exception
     */
    private static function parseValue(&$args, $arg, $tpl)
    {
        foreach ($tpl as $t => $v) {
            switch ($t) {
                case 'req_val':
                    if (strpos($arg, '=') === false) {
                        if (!empty($args)) {
                            return array_shift($args);
                        } else {
                            throw new Exception('Missing option value');
                        }
                    } else {
                        return substr(strstr($arg, '='), 1);
                    }
                    break;
                case 'opt_val':
                    if (strpos($arg, '=') !== false) {
                        return substr(strstr($arg, '='), 1);
                    }
                    break;
            }
        }

        return null;
    }

    /**
     * Return list errors encountered while parsing the arguments
     *
     * @return array List of errors
     */
    static function errors()
    {
        return self::$errors;
    }
}