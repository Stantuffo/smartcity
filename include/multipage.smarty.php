<?php
	
	class multipage{
		
		private $smarty = null;
		private $get_param = null;
		private $num_elements = null;
		private $num_middle_pages = null;
		static $assign_smarty = array();
		
		public $prev_page_label = '&laquo; Precedente';
		public $next_page_label = 'Successiva &raquo;';
		public $middle_page_label = '[%1]';
		public $middle_page_separator = ' ';
		
		public function __construct(&$smarty, $get_param = 'page', $num_elements = 25, $num_middle_pages = 9){
			
			$this->smarty = $smarty;
			$this->get_param = $get_param;
			$this->num_elements = $num_elements;
			$this->num_middle_pages = $num_middle_pages;
			
		}
		
		private function _overwrite_query_string($fields, $src = null){
			
			if(is_null($src))
				$src = $_GET;
			
			foreach($fields as $key => $value)
				$src[$key] = $value;
			
			$parts = array();
			foreach($src as $key => $value) {
                if(is_array($value)){
					// TDxyz
                    foreach($value as $k => $elem) {
                        $parts[] = "{$key}[{$k}]={$elem}";
                    }
                } else {
                    $parts[] = "{$key}={$value}";
                }

            }

			
			return $_SERVER['SCRIPT_NAME'].'?'.implode('&amp;', $parts);
			
		}
		
		private function _get_middle_pages($act_page, $num_pages){
			
			// calcolo la posizione della prima pagina da visualizzare
			if($act_page-floor($this->num_middle_pages/2) < 1){
				$first_page = 1;
				$last_page = $this->num_middle_pages;
			}elseif($act_page+floor($this->num_middle_pages/2) > $num_pages){
				$first_page = $num_pages-$this->num_middle_pages+1;
				$last_page = $num_pages;
			}else{
				$first_page = $act_page-floor($this->num_middle_pages/2);
				$last_page = $first_page+$this->num_middle_pages-1;
			}
			
			if($first_page < 1)
				$first_page = 1;
			if($last_page > $num_pages)
				$last_page = $num_pages;
			
			$ritornato = array();
			for($i=$first_page; $i<=$last_page; $i++)
				$ritornato[$i] = $this->_overwrite_query_string(array($this->get_param => $i));
			
			return $ritornato;
			
		}
		
		function _make_link($uri, $label){
			
			return "<a href=\"{$uri}\">{$label}</a>";
			
		}
		
		
		public function fetch($num_elements){
			
			// Numero di pagine
			$num_pages = ceil($num_elements/$this->num_elements);
			
			// Recupero la pagina attuale
			$page = 1;
			if(!empty($_GET[$this->get_param]) && is_numeric($_GET[$this->get_param]))
				$page = (int)$_GET[$this->get_param];
			if($page < 1) $page = 1;
			if($page > $num_pages) $page = $num_pages;
			
			// Creo i link middle
			$links = array();
			$uri_pages = $this->_get_middle_pages($page, $num_pages);
			
			// Se la prima pagina non è la numero 1 aggiungo il
			reset($uri_pages);
			if(key($uri_pages) != 1 && count($uri_pages))
				$links[] = $this->_make_link($this->_overwrite_query_string(array($this->get_param => 1)), str_replace('%1', '1..', $this->middle_page_label));
			
			// Inserisco le pagine intermedie
			foreach($uri_pages as $num_page => $uri_page)
				if($num_page == $page)
					$links[] = "<span>".str_replace('%1', $num_page, $this->middle_page_label)."</span>";
				else
					$links[] = $this->_make_link($uri_page, str_replace('%1', $num_page, $this->middle_page_label));
			
			// Se la prima pagina non è la numero 1 aggiungo il
			end($uri_pages);
			if(key($uri_pages) != $num_pages)
				$links[] = $this->_make_link($this->_overwrite_query_string(array($this->get_param => $num_pages)), str_replace('%1', "..{$num_pages}", $this->middle_page_label));
			
			// Inserisco i dati nel template
			self::$assign_smarty[$this->get_param] = array(
				'tot_elements' => $num_elements,
				'tot_pages' => $num_pages,
				'act_page' => $page,
				'prev_link' => "<a href=\"".$this->_overwrite_query_string(array($this->get_param => max(1, $page-1)))."\">{$this->prev_page_label}</a>",
				'next_link' => "<a href=\"".$this->_overwrite_query_string(array($this->get_param => min($num_pages, $page+1)))."\">{$this->next_page_label}</a>",
				'middle_link' => implode($this->middle_page_separator, $links),
			);
			
			if(!count($uri_pages)){
				self::$assign_smarty[$this->get_param]['prev_link'] = '';
				self::$assign_smarty[$this->get_param]['next_link'] = '';
				self::$assign_smarty[$this->get_param]['middle_link'] = '';
			}
			
			// Inserisco le informazioni nel template
			$this->smarty->assign('multipage', self::$assign_smarty);
			
			// Ritorno l'offset e il limit
			return array(max($this->num_elements*($page-1), 0), $this->num_elements);
			
		}


		public function exec($array_elements){

			// Numero di pagine
			$num_pages = ceil(count($array_elements)/$this->num_elements);

			// Recupero la pagina attuale
			$page = 1;
			if(!empty($_GET[$this->get_param]) && is_numeric($_GET[$this->get_param]))
				$page = (int)$_GET[$this->get_param];
			if($page < 1) $page = 1;
			if($page > $num_pages) $page = $num_pages;

			// Creo i link middle
			$links = array();
			$uri_pages = $this->_get_middle_pages($page, $num_pages);

			// Se la prima pagina non è la numero 1 aggiungo il
			reset($uri_pages);
			if(key($uri_pages) != 1 && count($uri_pages))
				$links[] = $this->_make_link($this->_overwrite_query_string(array($this->get_param => 1)), str_replace('%1', '1..', $this->middle_page_label));

			// Inserisco le pagine intermedie
			foreach($uri_pages as $num_page => $uri_page)
				if($num_page == $page)
					$links[] = "<span>".str_replace('%1', $num_page, $this->middle_page_label)."</span>";
				else
					$links[] = $this->_make_link($uri_page, str_replace('%1', $num_page, $this->middle_page_label));

			// Se la prima pagina non è la numero 1 aggiungo il
			end($uri_pages);
			if(key($uri_pages) != $num_pages)
				$links[] = $this->_make_link($this->_overwrite_query_string(array($this->get_param => $num_pages)), str_replace('%1', "..{$num_pages}", $this->middle_page_label));

			// Inserisco i dati nel template
			self::$assign_smarty[$this->get_param] = array(
				'tot_elements' => count($array_elements),
				'tot_pages' => $num_pages,
				'act_page' => $page,
				'prev_link' => "<a href=\"".$this->_overwrite_query_string(array($this->get_param => max(1, $page-1)))."\">{$this->prev_page_label}</a>",
				'next_link' => "<a href=\"".$this->_overwrite_query_string(array($this->get_param => min($num_pages, $page+1)))."\">{$this->next_page_label}</a>",
				'middle_link' => implode($this->middle_page_separator, $links),
			);

			if(!count($uri_pages)){
				self::$assign_smarty[$this->get_param]['prev_link'] = '';
				self::$assign_smarty[$this->get_param]['next_link'] = '';
				self::$assign_smarty[$this->get_param]['middle_link'] = '';
			}

			// Inserisco le informazioni nel template
			$this->smarty->assign('multipage', self::$assign_smarty);

			// Ritorno la parte di array interessata all visualizzazione
			return array_slice($array_elements, $this->num_elements*($page-1), $this->num_elements);

		}
		
	}
	
	
?>