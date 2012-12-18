<?php


class Template
{
	// the filename of the template file (is set in constructor)
	var $file;
	// the variables that the calling file registers with this class
	var $registeredVars = array();
	
	//regexes for different things in the languages
	var $regex_var = '[A-Za-z][A-Za-z0-9_]*';
	
	// emulating an ENUM with an associative array - it enums the different modes execution can be in
	var $modes = array('plain' => 0, 
					   'comment' => 1,
					   'script' => 2,
					   'commentedScript' => 3);
	
	function Template($fileName)
	{
		$this->file = $fileName;
	}
	
	function registerVar($varName, $varValue)
	{
		$registeredVars[] = array($varName, $varValue);
	}
	
	function output()
	{		
		$template = file_get_contents($this->file);
		
		//$template = $this->parse($template);
		$template = $this->parseUsingArray($template);
		
		echo $template;
	}
	
	function parse($input)
	{
		//for testing I really want to know how long this function takes to be executed
		$executionTime = microtime(true);
		
		while (strlen($input) > 0)
		{
			// First we set nextPos to the place where we can find the next delimiter
			// and we set nextDelimiter to that next delimiter so we'll know what to with it
			if ($mode == $this->modes['plain'])
			{
				$nextPos = strpos($input, "<:");
				$nextDelimiter = "<:";
			}
			else if ($mode == $this->modes['script'])
			{
				$possibility1 = strpos($input, "<:-");
				$possibility2 = strpos($input, ";");
				$possibility3 = strpos($input, ":>");
				
				// for any symbol that is not found, we'll replace it's value by one googol, so it'll never be the first
				// one, except if they're all googols, in which case it will fall into the else category and will
				// be put into the else statement, which sets nextPos to false, just like it would have been if
				// it were a single strpos - and thus it will be handled later together with all other unfound symbols
				if ($possibility1 === false)
				{
					$possibility1 = 1E100;
				}
				if ($possibility2 === false)
				{
					$possibility2 = 1E100;
				}
				if ($possibility3 === false)
				{
					$possibility3 = 1E100;
				}
				
				if ($possibility1 < $possibility2 && $possibility1 < $possibility3)
				{
					$nextPos = $posibility1;
					$nextDelimiter = "<:-";
				}
				else if ($possibility2 < $possibility1 && $possibility2 < $possibility3)
				{
					$nextPos = $possibility2;
					$nextDelimiter = ";";
				}
				else if ($possibility3 < $possibility1 && $possibility3 < $possibility2)
				{
					$nextPos = $possibility3;
					$nextDelimiter = ":>";
				}
				else
				{
					$nextPos = false;
				}
			}
			else if ($mode == $this->modes['comment'] || $mode == $this->modes['commentedScript'])
			{
				$nextPos = strpos($input, "-:>");
				$nextDelimiter = "-:>";
			}
			
			if ($nextPos === false)
			// if the delimiter was not found we have to act accordingly
			{
				if ($mode == $this->modes['plain'])
				{
					// if we're in plain mode, we have to put the nextPos at strlen of input to parse it normally
					// and the set nextDelimiter to null to signal that this is the end
					$nextPos = strlen($input);
					$nextDelimiter = null;
				}
				else
				{
					// We should be in plain mode at the end of the file, so we're going to throw an error here
					$output .= "<b>fatal error: end to block was not found. Expecting: ";
					$output .= "<i>$nextDelimiter</i>. <br /> \n";
					
					//now we empty input to end termination
					$input = "";
				}
			}
			
			// Now we have defined the active part - what we need to evaluate if the current block
			// is script, remove it if it is a comment, or copy it to the output if it is plain text
			
			if (input != "")
			// input may have been emptied to stop execuion of the script, so we only continue if it hasn't been
			{				
				if ($mode == $this->modes['plain'])
				// if the active block is in plain mode, we simply copy it to the output
				{
					$output .= $this->plaintext(substr($input, 0, $nextPos));
				}
				else if ($mode == $this->modes['comment'] || $mode == $this->modes['commentedScript'])
				// if the active block is a comment or a comment within a script, we simply don't do anything
				// with it
				{
				}
				else if ($mode == $this->modes['script'])
				// now we really have to evaluate it if it is a script - this is the hard part
				{
					$output .= $this->evaluate(substr($input, 0, $nextPos));
				}
				
				// Next we decide what mode the next block is going to be
				if ($nextDelimiter == "<:")
				// if we see this delimiter we still need to check whether we're dealing with a script tag
				// or with a comment tag
				{
					// if the next character is a -, we're dealing with a comment
					
					if (substr($input, $nextPos + 2, 1) == '-')
					{
						$mode = $this->modes['comment'];
						// we can safely assume it's not within a script, because inside the code
						// does not look for a '<:' delimiter
						
						//now we set the delimiter correctly, so it will be removed before 
						// the next iteration of the loop
						$nextDelimiter = "<:-";
					}
					else
					// logically, we're dealing with a script if we're not dealing with a comment and
					// it is delimited by '<:' - so there is noo need to change that
					{
						$mode = $this->modes['script'];
					}
				}
				// else if ($nextDelimiter == ';')
				// semicolons are only looked for in the script, so they simply mean that 
				// we'll do the next statement, but this only happens inside a script, meaning that
				// there is no need to change the mode - we can safely leave it alone
				else if ($nextDelimiter == '<:-')
				{
					$mode = $this->modes['commentedScript'];
					// we can safely assume this comment is inside a script tag,
					// as we would have looked for '<:' rather than '<:-' if not
					// note: if we used if instead of else if the change would have been made and this
					// would also have been executed
				}
				else if ($nextDelimiter == ':>')
				{
					$mode = $this->modes['plain'];
				}
				else if ($nextDelimiter == '-:>')
				// we need to check whether this comment we're ending was inside a script or not
				{
					if ($mode == $this->modes['comment'])
					{
						$mode = $this->modes['plain'];
					}
					else if ($mode == $this->modes['commentedScript'])
					{
						$mode = $this->modes['script'];
					}
				}
				
				// last but not least we have to remove the block we have executed from the input
				// and the ending delimiter, so we won't just execute it again
				$input = substr($input, $nextPos + strlen($nextDelimiter));
			}
		}
		
		//for testing I wanna know how long this function outputs how long it took
		echo "<div style='display: none;' name='stros method time taken'>";
		print_r((microtime(true)-$executionTime));
		echo " seconds</div> \n";
		
		return $output;
	}
	
	function parseUsingArray($input)
	{
		//for testing I really want to know how long this function takes to be executed
		$executionTime = microtime(true);
		
		// We put each block into it's own 
		$random_id = "Gjasov45";
		$input = str_replace(array("<:", ":>"),
							 array("<*EXPLOSION MARK(" . $random_id . ")*><:",
								   ":><*EXPLOSION MARK(" . $random_id . ")*>"),
							 $input);
		
		$input_array = explode("<*EXPLOSION MARK(" . $random_id . ")*>", $input);
		
		/*
		echo "<pre>";
		print_r($input_array);
		echo "</pre>";
		*/
		
		$output = '';
		$currentBlock = array();
		$nestedCount = 0;
		$mode = $this->modes['plain'];
		$endedComment = true;
		
		// for debugging
		$logModes = '';
		//echo $input;
		//print_r($input_array);
		
		// for testing
		$time2 = microtime(true);
		
		foreach ($input_array as $currentBlock)
		{
			// for debugging
			//echo "we <i>are</i> inside the foreach... <br />";
			//echo $currentBlock;
			//if ($currentBlock == null)
			//{
			//	echo "null";
			//}
			
			// first we update the mode
			
			// for debugging
			if ($endedComment == true)
			{
				//$logModes .= 'true ';
			}
			else
			{
				//$logModes .= 'false ';
			}
			
			if (!$endedComment)
			{
				// here we do not change the mode, it is comment or commentedScript and it should
				// stay that way. However, it is important that if thi evaluates to true the other
				// options are all not executed
			}
			else if (substr($currentBlock, 0, 3) == "<:-")
			{
				// when we're dealing with a comment we need to know whether that comment was inside a
				// a script or not, so we will know what to return to next block
				if ($mode == $this->modes['plain'])
				{
					$mode = $this->modes['comment'];
				}
				else if ($mode == $this->modes['script'])
				{
					$mode = $this->modes['commentedScript'];
				}
				
				// whenever we start a comment block, we'll set endedComment to false
				// it wil evaluate anything that comes later as a comment - until we ended 
				// the comment, which only happens if we find a comment end-tag
				$endedComment = false;
			}
			else if (substr($currentBlock, 0, 2) == "<:")
			// if the block does start with a script tag, but not with a comment tag, it is a script
			{
				// we ought to check for a scipt within a comment somehow, but I am not implementing this yet
				$mode = $this->modes['script'];
			}
			else
			// of there's nothing at the beginning of the block it's plain text - or script, if we came from a commentedScript
			{
				if ($mode == $this->modes['commentedScript'])
				{
					$mode = $this->modes['script'];
				}
				else
				{
					$mode = $this->modes['plain'];
				}
			}
			
			// for debugging
			if ($mode === null)
			{
				//$logModes .= 'null ';
			}
			else
			{
				//$logModes .= "$mode ";
			}
			
			// The active block was defined - what we need to do is break it up ito statements and evaluate these if the current block
			// is script, remove it if it is a comment, or copy it to the output if it is plain text
			
			if ($mode == $this->modes['plain'])
			// if the active block is in plain mode, we simply copy it to the output
			{
				$output .= $this->plaintext($currentBlock);
			}
			else if ($mode == $this->modes['comment'] || $mode == $this->modes['commentedScript'])
			// if the active block is a comment or a comment within a script, we simply don't do anything
			// with it
			{
			}
			else if ($mode == $this->modes['script'])
			// it's script, we'll break it up into statements and execute these
			{
				// first we remove the beginning end script tag
				$temp = str_replace(array("<:", ":>"), "", $currentBlock);
				//$temp = str_replace(":>", "", $temp);
				
				// then we seperate the different statements
				$temp = explode(";", $temp);
				
				foreach($temp as $currentStatement)
				// now we really have to evaluate it if it is a script - this is the hard part
				{
					// for now, we just take it out - like we do with comments
					// I'm just writing this bit wo we can see some of the arraywise execution
					
					$output .= $this->evaluate($currentStatement);
				}
			}
			
			// If we're doing a yet unended comment we have to see if it was properly ended
			if (!$endedComment)
			{
				//$logModes .= '_' . substr($currentBlock, -3) . '_ ';
				if (substr($currentBlock, -3) == "-:>")
				{
					$endedComment = true;
				}
			}
			
		}
		
		// if the last mode was not plain there is something wrong, print an error
		if ($mode != $modes['plain'])
		{
			if ($mode == $this->modes['comment'] || $mode == $this->modes['commentedScript'])
			{
				$expected = "-:>";
			}
			else if ($mode == $this->modes['script'])
			{
				$expected = ":>";
			}
			
			$output .= "<b>fatal error: Ending tag not found. Expecting " . $expected . ".";
		}
		
		
		//for testing I wanna know how long this function outputs how long it took
		echo "<div style='display: none;' name='array method time taken'>";
		print_r((microtime(true)-$executionTime));
		echo " seconds</div> \n";
		
		echo "<div style='display: none;' name='array method loop time only'>";
		print_r((microtime(true)-$time2));
		echo " seconds</div> \n";
		
		// for debugging
		$output = $logModes . $output;
		
		// testing this (removing blank lines)
		//$output = str_replace(array('\r\n\r\n', '\r\r', '\n\n'), '', $output);
				
		return $output;
	}
	
	function evaluate($string)
	{
		// Convert newlines to spaces - as that's what they're evaluated as
		// And then strip all spaces the statement starts with
		
		/*
		
		str_replace(array('\n', '\r');
		
		pattern = '[:space:]*' . regex_var . '([:space:]|' . regex_operation . ')+'
		
		// Find out whether we're dealing with a number literal, a variable or a function
		$firstSymbol = substr($string, 0, 1);
		
		*/
		
	}
	
	function plaintext($string)
	{
	//	if (str_replace(array("\t", "\n", "\r", " "), '', $string) == '')
	//	{
	//		return '';	
	//	}
	//	else
	//	{
			return ereg_replace('(^[:space:]+)|([:space:]+$)', '', $string);
	//	}
	}
	
}