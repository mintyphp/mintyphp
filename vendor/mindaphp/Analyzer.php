<?php
namespace MindaPHP;

class Analyzer
{
    public static $tokens    = array('T_ECHO','T_PRINT','T_EXIT','T_STRING','T_EVAL','T_OPEN_TAG_WITH_ECHO');
    public static $functions = array('echo','print','die','exit','var_dump','eval','<?=');
    
    public static function execute()
    {
    	static::check('action',Router::getTemplateAction());
    	static::check('action',Router::getAction());
    	static::check('view',Router::getView());
    	static::check('view',Router::getTemplateView());
    }
    
    protected static function check($type,$filename)
    {
    	if (!$filename) return;
    	$tokens = token_get_all(file_get_contents($filename));
    	foreach ($tokens as $token) {
    		if (is_array($token)) {
    			if (in_array(token_name($token[0]),static::$tokens)) {
    				if (in_array($token[1],static::$functions)) {
    					trigger_error('MindaPHP '.$type.' "'.$filename.'" should not use "'.htmlentities($token[1]).'" on line '.$token[2].'. Error raised ', E_USER_WARNING);
    				}
    			}
    		}
    	}
    }
    
}