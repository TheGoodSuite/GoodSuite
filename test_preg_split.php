<?php

$input = "text <:script:> text text <: script :><:scipt:> text \n \n text <:script;  script:> text <:script \n scipt:>text tex <:script; \nscript; \nscript:>";
$regex = '(?=<:)|(?<=:>)';
$regex_script_delimiter_left = '<:';
$regex_script_delimiter_right = ':>';
$regex_script_statementender = ';';

$regex_script = $regex_script_delimiter_left . '[\s\S]*?' . $regex_script_delimiter_right;

$output = preg_split('/'. $regex .'/', $input);


for ($i = 0; $i < count($output); $i++)
{
	if (preg_match('/'. $regex_script.'/', $output[$i]))
	{
		$map[$i] = 'script';
		
		$output[$i] = preg_split('/('. $regex_script_delimiter_left . ')|(' . $regex_script_delimiter_right . ')|(' . $regex_script_statementender .')/', $output[$i], -1, PREG_SPLIT_NO_EMPTY);
		
	}
	else
	{
		$map[$i] = 'text';
	}
}

echo "<pre>\n";
print_r($output);
echo "\n\n\n";
print_r($map);
echo "\n</pre>"
?>
