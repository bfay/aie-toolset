<?php
/**
 * Courtesy from: http://sarfraznawaz.wordpress.com/2012/01/05/outputting-php-to-browser-console/
 * Logs messages/variables/data to browser console from within php
 *
 * @param $name: message to be shown for optional data/vars
 * @param $data: variable (scalar/mixed) arrays/objects, etc to be logged
 * @param $jsEval: whether to apply JS eval() to arrays/objects
 *
 * @return none
 * @author Sarfraz
 */
function DDL_logConsole($name, $data = NULL, $jsEval = FALSE)
{
	if (! $name) return false;

	$isevaled = false;
	$type = ($data || gettype($data)) ? 'Type: ' . gettype($data) : '';

	if ($jsEval && (is_array($data) || is_object($data)))
	{
		$data = 'eval(' . preg_replace('#[\s\r\n\t\0\x0B]+#', '', json_encode($data)) . ')';
		$isevaled = true;
	}
	else
	{
		$data = json_encode($data);
	}

	# sanitalize
	$data = $data ? $data : '';
	$search_array = array("#'#", '#""#', "#''#", "#\n#", "#\r\n#");
	$replace_array = array('"', '', '', '\\n', '\\n');
	$data = preg_replace($search_array,  $replace_array, $data);
	$data = ltrim(rtrim($data, '"'), '"');
	$data = $isevaled ? $data : ($data[0] === "'") ? $data : "'" . $data . "'";

	$js = <<<JSCODE
\n<script>
     // fallback - to deal with IE (or browsers that don't have console)
     if (! window.console) console = {};
     console.log = console.log || function(name, data){};
     // end of fallback

     console.log('$name');
     console.log('------------------------------------------');
     console.log('$type');
     console.log($data);
     console.log('\\n');
</script>
JSCODE;

	echo $js;
} # end logConsole