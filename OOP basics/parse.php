<?php
	/**
	 * projekt: IPP - 1. projekt (parse.php)
	 * autor: Tomáš Hampl (xhampl10)
	 */

	/* globals */
	$lineCounter = 0;
	$commCounter = 0;
	$instCounter = 0;
	$orderCounter = 0;
	$jumpCounter = 0;
	$first = true;
	$labelCounter = 0;
	$stats = false;

	/**
	 * function checkEscSeq 
	 * checks if string contains escape sequences, returns true or false on behalf of correct interpretation
	 * in: string
	 * out: bool
	 */
	function checkEscSeq($string){
		$array = str_split($string);
    	$count = count($array);

		for ($i = 0; $i < $count; $i++){
			if ($array[$i] === "\\"){
				if (is_numeric($array[$i + 1]) && is_numeric($array[$i + 2]) && is_numeric($array[$i + 3])){
					continue;
				}
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * function readInput
	 * reads input from stdin, deletes comments, white spaces and checks for header, returns instruction and arguments in array
	 * out: array
	 */
	function readInput(){
		global $lineCounter, $commCounter, $instCounter, $first;
		

		while($input = fgets(STDIN)){

			if (preg_match("/#.*$/", $input)){  # comments
				$input = preg_replace("/\#.*$/", "", $input);
				$commCounter++;
			}

			$input = trim($input); # white spaces
			$input = preg_replace("/\s+/", " ", $input);

			$inputArray = explode(" ", $input);

			$inputArray[0] = strtoupper($inputArray[0]);
			
			if ($first){ # check for header	
				$first = false;		
				$testLanguage = strtoupper(".IPPcode19");
				
				if (strcmp($inputArray[0], ".IPPCODE19") != 0){
					fwrite(STDERR, "missing header\n");
					exit(21);
				}
				
				continue;
			}
			$lineCounter++;
			
			if ((trim($input)) != ""){
				$instCounter++;
				return $inputArray;
			} else {
				continue;
			}
		}
	}

	/**
	 * function instructionCheck
	 * checks if instruction has correct number of arguments, returns bool
	 * in: array
	 * out: bool
	 */
	function instructionCheck($array){
		$count = count($array);
		
		$instrArray = array("CREATEFRAME","PUSHFRAME","POPFRAME", "RETURN", "BREAK","DEFVAR", "CALL", "PUSHS", "POPS", "WRITE", "LABEL", 
		"JUMP", "EXIT", "DPRINT","MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE", "NOT", "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", 
		"AND", "OR", "STRI2INT", "CONCAT", "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ");

		if (array_search($array[0], $instrArray) === FALSE) exit(22);

		if ($count == 0){
			fwrite(STDERR, "instruction has wrong parametres\n");
			exit(23);
		}

		if ($count == 1){
			$instructionArray1 = array("CREATEFRAME","PUSHFRAME","POPFRAME", "RETURN", "BREAK");
			
			if (array_search($array[0], $instructionArray1) === FALSE) exit(23);

		} elseif ($count == 2){
			$instructionArray2 = array("DEFVAR", "CALL", "PUSHS", "POPS", "WRITE", "LABEL", "JUMP", "EXIT", "DPRINT");

			if (array_search($array[0], $instructionArray2) === FALSE) exit(23);		

		} elseif ($count == 3){
			$instructionArray3 = array("MOVE", "INT2CHAR", "READ", "STRLEN", "TYPE", "NOT");

			if (array_search($array[0], $instructionArray3) === FALSE) exit(23);

		} elseif ($count == 4){
			$instructionArray4 = array("ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR", "STRI2INT", "CONCAT", "GETCHAR", "SETCHAR", "JUMPIFEQ", "JUMPIFNEQ");

			if (array_search($array[0], $instructionArray4) === FALSE) exit(23);
			
		} else {
			fwrite(STDERR, "instruction has wrong parametres\n");
			exit(23);
		}
		return TRUE;
	}

	/**
	 * function getArgType 
	 * checks type of argument and returns it
	 * in: string
	 * out: string
	 */
	function getArgType($arg){
		if (preg_match("/^int@/", $arg)){
			return "int";
		} elseif (preg_match("/^string@/", $arg)){
			return "string";
		} elseif (preg_match("/^bool@/", $arg)){
			return "bool";
		} elseif (preg_match("/^(LF|TF|GF)@/", $arg)){
			return "var";
		} elseif (preg_match("/^(int|string|bool)$/", $arg)){
			return "type";
		} elseif (preg_match("/^nil@/", $arg)){
			return "nil";
		} else {
			fwrite(STDERR, "wrong type\n");
			exit(23);
		}
	}

	/**
	 * function replaceSpecialChars 
	 * checks if string contains special characters and replaces them, returns string
	 * in: string
	 * out: string
	 */
	function replaceSpecialChars($string){
		if (preg_match("/\&+(?!amp;|gt;|lt;)/", $string)){
			$string = str_replace("&", "&amp;", $string);
		} 
		if (preg_match("/\<+/", $string)){
			$string = str_replace("<", "&lt;", $string);
		} 
		if (preg_match("/\>+/", $string)){
			$string = str_replace(">", "&gt;", $string);
		}
		if (preg_match("/\"+/", $string)){
			$string = str_replace("\"", "&quot;", $string);
		}
		if (preg_match("/\'+/", $string)){
			$string = str_replace("\'", "&apos;", $string);
		}
		return $string;
	}

	/**
	 * function checkArgType 
	 * compares argument to dedicated type, checks if argument contains correct value
	 * in: string, string
	 * out: array (string, string)
	 */
	function checkArgType($type, $arg){
		if ($type === "var"){
			if (preg_match("/^(GF|TF|LF)@[a-zA-Z*\**\?*\!*\$*\&*\**\-*\%*\_*\d*]+$/", $arg)) return array($type,  replaceSpecialChars($arg));
		} elseif ($type === "symb"){
			$argType = getArgType($arg);
			$string = explode("@", $arg, 2)[1];

			if ($string === "") $empty = true;

			if ($argType === "int"){
				if (preg_match("/^([\-]|[\+])?[0-9]*+\Z/", $string)) return array($argType, $string);
			} elseif ($argType === "string"){		
				if (preg_match("/[^#\s]+\z/", $string)){
					if (checkEscSeq($string)) return array($argType, replaceSpecialChars($string));
				} elseif ($empty){
					return array($argType, $string);
				}
			} elseif ($argType === "bool"){
				if (preg_match("/^(true|false)$/", $string)) return array($argType, $string);
			} elseif ($argType === "nil"){
				if (preg_match("/^(nil)$/", $string)) return array($argType, $string);
			} elseif ($argType === "var"){
				if (preg_match("/^(GF|TF|LF)@[a-zA-Z*\**\?*\!*\$*\&*\**\-*\%*\_*\d*]+$/", $arg)) return array($argType, replaceSpecialChars($arg));
			} elseif ($argType === "type"){
				if (preg_match("/^(int|string|bool)$/", $arg)) return array($argType, $arg);
			}
		} elseif ($type === "label"){
			if (preg_match("/^[\w*\_*\-*\$*\%*\&*\**\!*\?*\d*]*$/", $arg)) return array($type, replaceSpecialChars($arg));
		}
		fwrite(STDERR, "wrong argument\n");
		exit(23);
	}
	
	/* arguments */
	if (count($argv) >= 2){
		
		if (array_search("--help", $argv)){
			echo "HELP:\n";
			echo "Script reads .IPPcode19 from stdin and returns XML to stdout.\n";
			echo "EXTENSIONS:\n";
			echo "Script gives user statistics of IPPcode19.\n";
			echo "USAGE:\n";
			echo "This extension needs to be given file to work. Without file it will crash.\n";
			echo "--stats=file : file wrete statistict will be written\n";
			echo "--loc : number of instructions\n";
			echo "--comments : number of comments\n";
			echo "--labels : number of labels\n";
			echo "--jumps : number of jumps \n";
			exit;
		}

		$i = 0; $j = 0;

		foreach ($argv as $arg){ # reads arguments of program
			if (preg_match("/--stats=\S*/", $arg)){
				$string = explode("=", $arg)[1];
				$file[$i] = $string;
				$i++;
			} elseif (preg_match("/--loc|--comments|--labels|--jumps/", $arg)){
				$arguments[$j] = $arg;
				$j++;
			} else {
				if (preg_match("/parse\.php/", $arg)) continue;
				fwrite(STDERR, "wrong run argument\ntype --help as run argument to get help\n");
				exit(10);
			}
		}
		if (empty($file) && !empty($arguments)){ # no file while arguments given
			fwrite(STDERR, "no file given\ntype --help as run argument to get help\n");
			exit(10);
		}
		if (count($file) > 1){ # more files
			fwrite(STDERR, "too many files given\ntype --help as run argument to get help\n");
			exit(10);
		}
		if (file_exists($file[0])){ # cleans file
			file_put_contents($file[0],"");
		} else {
			echo exec("touch $file[0]");
		}
		$stats = true;
	}

	/* xml document */
	$document = new DomDocument("1.0", "UTF-8");
	$program = $document->createElement("program");
	$program->setAttribute("language", "IPPcode19");
	
	/* xml builder */
	while($inputArray = readInput()){
		$orderCounter++;

		$type = $inputArray[0];

		if (instructionCheck($inputArray)){
			$instruction = $document->createElement("instruction");
			$instruction->setAttribute("order", $orderCounter);
			$instruction->setAttribute("opcode", $inputArray[0]);
			$program->appendChild($instruction);
		}

		switch ($type) {
			case "CREATEFRAME": case "PUSHFRAME": case "POPFRAME": case "RETURN": case "BREAK":
				break;

			case "DEFVAR": case "POPS":
				$array = checkArgType("var", $inputArray[1]);
				$arg = $document->createElement("arg1", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);
				break;

			case "PUSHS": case "WRITE": case "EXIT": case "DPRINT":
				$array = checkArgType("symb", $inputArray[1]);
				$type1 = $array[0];
				$arg = $document->createElement("arg1", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);
				break;

			case "MOVE": case "INT2CHAR": case "READ": case "NOT": case "STRLEN": case "TYPE":
				$array = checkArgType("var", $inputArray[1]);
				$arg = $document->createElement("arg1", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);

				$array = checkArgType("symb", $inputArray[2]);
				$arg = $document->createElement("arg2", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);
				break;

				case "JUMP": case "JUMPIFEQ": case "JUMPIFNEQ": $jumpCounter++; case "CALL": case "LABEL": 
				if ($type === "LABEL") $labelCounter++;

				$array = checkArgType("label", $inputArray[1]);
				$arg = $document->createElement("arg1", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);
				
				if ($type === "JUMPIFEQ" || $type === "JUMPIFNEQ"){
					$array = checkArgType("symb", $inputArray[2]);
					$arg = $document->createElement("arg2", $array[1]);
					$arg->setAttribute("type", $array[0]);
					$instruction->appendChild($arg);

					$array = checkArgType("symb", $inputArray[3]);
					$arg = $document->createElement("arg3", $array[1]);
					$arg->setAttribute("type", $array[0]);
					$instruction->appendChild($arg);
				}
				break;
				
			case "LT": case "GT": case "EQ": case "AND": case "OR": case "STRI2INT": case "CONCAT": case "GETCHAR": case "SETCHAR": case "ADD": case "SUB": case "MUL": case "IDIV":
				$array = checkArgType("var", $inputArray[1]);
				$arg = $document->createElement("arg1", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);

				$array = checkArgType("symb", $inputArray[2]);
				$arg = $document->createElement("arg2", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);

				$array = checkArgType("symb", $inputArray[3]);
				$arg = $document->createElement("arg3", $array[1]);
				$arg->setAttribute("type", $array[0]);
				$instruction->appendChild($arg);
				break;

			default:
				fwrite(STDERR, "Unknonw instruction\n");
				exit(22);
				break;
		}

	}

	/* empty file check */
	if ($lineCounter == 0){
		fwrite(STDERR, "empty file\n");
		exit(21);
	}

	/* statistics */
	if ($stats){
        if (!empty($arguments)){
            foreach ($arguments as $arg){
                switch ($arg){
                    case "--loc":
                        file_put_contents($file[0], $instCounter . "\n", FILE_APPEND);
                        break;
                    case "--comments":
                        file_put_contents($file[0], $commCounter . "\n", FILE_APPEND);
                        break;
                    case "--labels":
                        file_put_contents($file[0], $labelCounter . "\n", FILE_APPEND);
                        break;
                    case "--jumps":
                        file_put_contents($file[0], $jumpCounter . "\n", FILE_APPEND);
                        break;
                }
            }
        }
	}

	/* xml output */
	$document->appendChild($program);
    $document->formatOutput = true;
	echo $document->saveXML();
	exit(0);
?>