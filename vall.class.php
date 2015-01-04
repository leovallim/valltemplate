<?php
/**
 * Vall v0.2
 * 
 * Essa classe tem a finalidade de separar a lógica de programação da 
 * interface gráfica do sistema.
 * 
 * Retomei esse projeto que abandonei de 2009 e finalmente finalizei em 2014.
 * 
 * @author Leonardo Vallim <leovallim.com>
 * @version 0.2
 * 
 */
final class vall 
{
	public $dir_name;
	private $variables = array();
	private $tpl_variables = array();
	private $template;
	
	public function __construct($path = "template")
	{
		$this->dir_name = $path;
	}
	
	private function _find($string = NULL, $init= NULL, $end = NULL)
	{
		if($string && ($init || $end))
		{
			if(strstr($string,$init))
			{
				$foo = explode($init,$string,2);
				$foo = explode($end,$foo[1],2);
				return $foo;
			}
			else
				return FALSE;
		}
		else
			return FALSE;
	}
	
	private function _slice($string = NULL, $init= NULL, $end = NULL)
	{
		if($string && ($init || $end))
		{
			if(strstr($string,$init))
			{
				$foo = explode($init,$string,2);
				$ini_search = $foo[0];
				$foo = explode($end,$foo[1],2);
				$search = $foo[0];
				$end_search = $foo[1];
				
				$arr_return = array($ini_search, $search, $end_search);
				return $arr_return;
			}
			else
				return FALSE;
		}
		else
			return FALSE;
	}
	
	private function _is_array($var = NULL)
	{
		if($var)
		{
			if(strstr($var,'.'))
			{
				$arr = explode('.',$var);
				return $arr;
			}
			return FALSE;
		}
		
		return FALSE;
	}
	
	private function _include($file = NULL)
	{
		if(file_exists($this->dir_name ."/". $file))
		{
			if($once == FALSE)
				include($this->dir_name .'/'. $file);
			else
				include_once($this->dir_name .'/'. $file);
		}
		else
			print "O arquivo ". $this->dir_name ."/". $file ." nÃ£o foi encontrado";
		
		
	}
	
	private function search_tpl_vars()
	{
		$flag = TRUE;
		$str = $this->template;
		
		while($flag === TRUE)
		{
			$var = $this->_find($str,'{$','}');
			
			if($var && is_array($var))
			{
				if(!@array_search($var[0],$this->tpl_variables))
					$this->tpl_variables[] = $var[0];
				
				$str = $var[1];
			}
			else
				$flag = FALSE;
		}
	}
	
	private function search_tpl_methods()
	{
		$flag = TRUE;
		$str = $this->template;
		
		while($flag === TRUE)
		{
			$var = $this->_find($str,'{.','}');
			
			if($var && is_array($var))
			{
				$foo = explode(":",$var[0],2);
				
				switch(trim($foo[0]))
				{
					case "include": 
						$file = file_get_contents($this->dir_name."/".$foo[1]);
		            	$this->template = str_replace("{.include:". $foo[1] ."}", $file, $this->template);
						break;
					case "date":
						$aux = "{.". $foo[0] .":". $foo[1] ."}";
						$this->template = str_replace($aux,date($foo[1]),$this->template);
						break;
				}
				$inc = "{.". $var[0] ."}";
				$this->template = str_replace($inc, "", $this->template);
				$str = $var[1];
			}
			else
				$flag = FALSE;
			
		}
	}
	
	private function tpl_css()
	{
		$str = $this->template;
		
		$head = explode("<head>",$str);
		$head = $head[1];
		
		$head = explode("</head>",$head);
		$head = $head[0];
		$assistantHead = $head;
		$realHead = $head;
		
		
		$flag = TRUE;
		
		while($flag == TRUE)
		{
			$strpos = stripos($head,"<link");
			
			if($strpos !== FALSE)
			{
				$assistant = explode("<link",$head,2);
				$assistant = explode(">",$assistant[1],2);
				
				$head = $assistant[1];
				
				$assistant = explode("href=\"",$assistant[0],2);
				@$assistant = explode("\"",$assistant[1]);
				
				$link = $assistant[0];
				
				if(stripos($link,"http") === FALSE)
					$assistantHead = str_replace($link, $this->dir_name ."/". $link, $assistantHead);
				
				
				
			}
			else
				$flag = FALSE;
		}
		
		$str = str_replace($realHead,$assistantHead,$str);
		
		$this->template = $str;
	}
	
	private function tpl_script()
	{
		$str = $this->template;
		
		$head = explode("<head>",$str);
		$head = $head[1];
		
		$head = explode("</head>",$head);
		$head = $head[0];
		$assistantHead = $head;
		$realHead = $head;
		
		
		$flag = TRUE;
		
		while($flag == TRUE)
		{
			$strpos = stripos($head,"<script");
			
			if($strpos !== FALSE)
			{
				$assistant = explode("<script ",$head,2);
				$assistant = explode(">",$assistant[1],2);
				
				$head = $assistant[1];
				
				$assistant = explode("src=\"",$assistant[0],2);
				@$assistant = explode("\"",$assistant[1]);
				
				$link = $assistant[0];
				
				if(stripos($link,"http://") === false)
					$assistantHead = str_replace($link, $this->dir_name ."/". $link, $assistantHead);
				
				
			}
			else
				$flag = FALSE;
		}
		
		$str = str_replace($realHead,$assistantHead,$str);
		
		$this->template = $str;
	}
	
	public function set($propertie = NULL, $value = NULL)
	{
		if($propertie)
			$this->variables[$propertie] = $value;
	}
	
	
	private function tpl_var_replace()
	{
		if(!empty($this->tpl_variables))
		{
			foreach($this->tpl_variables as $value)
			{
				if($var = $this->_is_array($value))
				{
					$foo = $this->variables;
					foreach($var as $val)
					{
						if(@array_key_exists($val, $foo))
						$foo = $foo[$val];
					}
					
					if(@is_String($foo) || @is_int($foo) || @is_bool($foo) || @is_double($foo))	
						$this->template = str_replace("{\$$value}", $foo, $this->template);
				}
				else
				{
					if(array_key_exists($value, $this->variables))
					
						$this->template = str_replace("{\$$value}", $this->variables[$value], $this->template);
				}
			}
		}
	}
	
	private function replace_block()
	{
		$original_template = $this->template;
		$work_template = $original_template;
		$have_block = TRUE;
		
		while($have_block == TRUE)
		{
			$pieces = $this->_slice($work_template,"{block","{/block}");
			if($pieces === FALSE)
				$have_block = FALSE;
			if($have_block == TRUE)
			{
				$original_block = "{block". $pieces[1] ."{/block}";
				
				$work_template = $pieces[2];
				
				$pieces = $this->_slice($pieces[1],"$","}");
				
				$variable_loop = $pieces[1];
				$content = $pieces[2];
				
				$count = count($this->variables[$variable_loop]);
				
				$var_replace = NULL;
				
				if($count)
				{
					$have_var = TRUE;
					$content_original = $content;
					$content_work = $content_original;
					while($have_var === TRUE)
					{
						$pieces = $this->_slice($content_work,'{$',".");
						$content_work = $pieces[2];
						if($pieces[1])
							$variables[$pieces[1]] = $pieces[1];
						
						if($pieces === FALSE)
							$have_var = FALSE;
					}
					foreach($variables as $value)
					{
						$content = str_replace("\$$value.","\$$value.NUMBER_OF_INDICE_TL.",$content);
					}
					for($i=0;$i<$count;$i++)
					{
						$content_i = str_replace("NUMBER_OF_INDICE_TL", $i, $content);
						$block_replace .= $content_i;
					}
				}
				else
					$original_block = $block_replace;
				
				$original_template = str_replace($original_block, $block_replace, $original_template);
				
				$block_replace = NULL;
				$original_block = NULL;
			}
		}
		$this->template = $original_template;
	}
	
	private function replace_if()
	{
		$original_template = $this->template;
		$work_template = $original_template;
		$have_if = TRUE;
		
		while($have_if == TRUE)
		{
			$pieces = $this->_slice($work_template,"{if","{/if}");
			if($pieces === FALSE)
				$have_if = FALSE;
			if($have_if == TRUE)
			{
				$original_if = "{if". $pieces[1] ."{/if}";
				
				$work_template = $pieces[2];
				
				$pieces = $this->_slice($pieces[1],"$","}");
				
				$variable_if = $pieces[1];
				$content = $pieces[2];
				
				$content_aux = explode("{else}",$content,2);
				
				$content_if = $content_aux[0];
				$content_else = $content_aux[1];
				
				if($this->variables[$variable_if])
					$original_template = str_replace($original_if, $content_if, $original_template);
				else
					$original_template = str_replace($original_if, $content_else, $original_template);
			}
		}
		$this->template = $original_template;
	}
	
	public function display($file = "index.html")
	{
		if($this->dir_name)
			$file =  $this->dir_name ."/". $file;
			
		$this->template = file_get_contents($file);
		@$this->search_tpl_methods();
		@$this->replace_block();
		@$this->replace_if();
		@$this->search_tpl_vars();
		@$this->tpl_var_replace();
		@$this->tpl_css();
		@$this->tpl_script();
		print $this->template;
	}
}
?>
