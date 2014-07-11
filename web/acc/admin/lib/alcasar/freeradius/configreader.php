<?php
/*
TODO :
- contrôler et tester les expressions régulières
- prise en compte de tous les types de commentaires "# et *"
- prise en compte d'une valeurs multi-lignes

-> pas de setter pour le configreader
*/

/**
* Page contenant les classes <b>confItem</b>, <b>pairItem</b>, <b>sectionItem</b> et <b>configReader</b>.
* Ces classes permettent de parser des fichiers de configuration du même format
* que ceux utilisés par freeradius.
*/

/**
* Classe abstraite <b>confItem</b>.
*
* @name confItem
* @author steweb57
* @version 0.2.0
*/
Abstract class confItem
{
	/**
	* Variable contenant le type d'item (pair ou section)
	* @var string
	*/
	protected $_type	= null;
	/**
	* Variable contenant le nom de l'item
	* @var string
	*/
	protected $_name	= null;
	/**
	* Variable contenant le nom de l'instance de l'item (section uniquement)
	* @var string
	*/
	protected $_instanceName	= null;
	/**
	* Variable contenant le lien vers la section parente (section uniquement???)
	* @var string
	*/
	protected $_parent	= null;
	/**
	 * Tableau contenant les parametres (items)
	 * @var array
	 */
	protected $_items	= array();
	/**
	 * Return the parent confItem
	 *
	 * @name getParent()()
	 * @return confItem
	 */
	public function getParent(){
		return $this->_parent;
	}
	/**
	 * return the type of the confItem
	 *
	 * @name getType()
	 * @return string
	 */
	public function getType(){
		return $this->_type;
	}
	/**
	 * return all items
	 *
	 * @name getAll()
	 * @return array()
	 */
	public function getAll(){
		return $this->_items;
	}
	/**
	 * return the name of the confItem
	 *
	 * @name getName()
	 * @return string
	 */
	public function getName(){
		return $this->_name;
	}
	/**
	 * return the instance name of the confItem
	 *
	 * @name getInstanceName()
	 * @return string
	 */
	public function getInstanceName(){
		return $this->_instanceName;
	}
	/**
	 *
	 * Return the specified atribute
	 *
	 * @name __get()
	 * @param string $attr
	 * @return mixed (confitem object or string)
	 */
	public function __get($attr) {
		$resp=array();
		foreach ($this->_items as $item){
			if (is_object($item)){
				if ($item->getName() === $attr){
					$resp[] = $item;
				}
			} else {
				if (array_key_exists($attr, $this->_items))
					$resp[] = $this->_items[$attr];
			}
		}
		if (count($resp)===0){
			return false;
		}elseif (count($resp)===1){
			return $resp[0];
		} else {
			return $resp;// si vide, on répond par un array vide ou par false?
		}
	}
	/**
	 * Converte the object to a string
	 *
	 * @name __toString()
	 * @return string
	 */
	public function __toString() {
		return $this->_name;
    }
}

/**
* Classe <b>pairItem</b>.
*
* @name pairItem
* @author steweb57
* @version 0.2.0
*/
class pairItem extends confItem
{
	/**
	 * Constructeur
	 *
	 * <p>création de l'instance de la classe</p>
	 *
	 * @name pairItem::__construct()
	 * @param string $pairName, $value
	 * @return void
	 */
	public function __construct($pairName,$value) {
		$this->_type = 'pair';
		$this->_items[$pairName] = $value;
		$this->_name = $pairName;
	}
	/**
	 * Converte the object to a string
	 *
	 * @name __toString()
	 * @return string
	 */
	public function __toString() {
		return $this->_items[$this->_name];// on retourne la valeur
    }
	/**
	 * Get a pair value
	 *
	 * @name getPair()
	 * @param string $pairName
	 * @return mixed (string or array)
	 */
	public function getPair($pairName = null){
		if ($pairName!==null){
			if (array_key_exists($pairName, $this->_items)){
				return $this->_items[$pairName]; // on renvoie une valeur
			} else {
				return false;
			}
		} else {
			return $this->_items; //on renvoie un tableau
		}
	}
	public function __get($pairName){
		return $this->getPair($pairName);
	}
	/**
	 * Set a pair value
	 *
	 * @name getPair()
	 * @param string $pairName
	 * @param string $value
	 * @return bool
	 */
	public function setPair($pairName, $value){
		if (array_key_exists($pairName, $this->_items)){
				$this->_items[$pairName] = $value;
				return true;
		} else {
			return false;
		}
	}
	public function __set($pairName, $value){
		return $this->setPair($pairName, $value);
	}
}
/**
* Classe <b>sectionItem</b>.
*
* @name sectionItem
* @author steweb57
* @version 0.3.6
*/
class sectionItem extends confItem //extends pairItems?????
{
	/**
	 * Constructeur
	 *
	 * <p>création de l'instance de la classe</p>
	 *
	 * @name sectionItem::__construct()
	 * @param string $name
	 * @param string $instanceName
	 * @param sectionItem $parent
	 * @return void
	 */
	public function __construct($name, $instanceName = "", sectionItem &$parent=null) {
		$this->_type = 'section';
		$this->_parent = $parent;
		$this->_name = $name;
		$this->_instanceName = $instanceName;
	}
	/**
	 * add a new child sectionItem
	 *
	 * @name addSection()
	 * @param string $name
	 * @param string $instanceName
	 * @return sectionItem object
	 */
	public function addSection($name, $instanceName = "") {
		$this->_items[] = new sectionItem($name, $instanceName, $this);
		return end($this->_items);
	}
	/**
	 * add a new child pairItem
	 *
	 * @name addPair()
	 * @param string $pairName
	 * @param string $value
	 * @return pairItem Object
	 */
	public function addPair($pairName, $value) {
		$this->_items[] = new pairItem($pairName, $value);
		return end($this->_items);
	}
	/**
	 * return the child instance of the confSection by instanceName
	 *
	 * @name getInstance()
	 * @param string $instanceName
	 * @return mixed (sectionItem object or array())
	 */
	public function getInstance($instanceName=null){
		if ($instanceName === null)
			return false;
		$resp = array();
		foreach ($this->_items as $item){
			if ($item->getType() !== 'section'){
				continue;
			}
			if ($item->getInstanceName() === $instanceName){
				$resp[] = $item;
			}
		}
		if (count($resp)===0){
			return false;
		}elseif (count($resp)===1){
			return $resp[0];
		} else {
			return $resp;
		}
	}
	/**
	 * return the child instance of the confSection by instanceName and sectionName
	 *
	 * @name getSectionInstance()
	 * @param string $sectionName
	 * @param string $instanceName
	 * @return mixed (sectionItem object or array())
	 */
	public function getSectionInstance($sectionName = null, $instanceName=null){
		if (($sectionName === null)||($instanceName === null))
			return false;
		$resp = array();
		foreach ($this->_items as $item){
			if ($item->getType() !== 'section'){
				continue;
			}
			if (($item->getName() === $sectionName)&&($item->getInstanceName() === $instanceName)){
				$resp[] = $item;
				
			}
		}
		if (count($resp)===0){
			return false;
		}elseif (count($resp)===1){
			return $resp[0];
		} else {
			return $resp;
		}
	}
	/**
	 * return child sectionItems of the sectionItem
	 *
	 * @name getSection()
	 * @param string $sectionName
	 * @return mixed (sectionItem object or array())
	 */
	public function getSection($sectionName = null){
		$resp = array();
		foreach ($this->_items as $item){
			if ($item->getType() !== 'section'){
				continue;
			}
			if ($sectionName === null){
				$resp[] = $item;
			} else {
				if ($item->getName() === $sectionName){
					$resp[] = $item;
				}
			}
		}
		if (count($resp)===0){
			return false;
		}elseif (count($resp)===1){
			return $resp[0];
		} else {
			return $resp;
		}
	}
	/**
	 * return child pairItems of the sectionItem
	 *
	 * @name getPair()
	 * @param string $pairName
	 * @return mixed (pairItem object or array())
	 */
	public function getPair($pairName = null){
		$resp = array();
		foreach ($this->_items as $item){
			if ($item->getType() !== 'pair'){
				continue;
			}
			if ($pairName === null){
				$resp[] = $item;
			} else {
				if ($item->getName() === $pairName){
					$resp[] = $item;
				}
			}
		}
		if (count($resp)===0){
			return false;
		}elseif (count($resp)===1){
			return $resp[0];
		} else {
			return $resp;
		}
	}
	
	public function __get($name){
		if ($this->getSection($name)!==false){
			return $this->getSection($name);
		} elseif ($this->getPair($name)!==false) {
			return $this->getPair($name);
		} elseif ($this->getInstance($name)!==false) {
			return $this->getInstance($name);
		} else {
			return false;
		}
	}
	public function deleteSection($sectionName, $instanceName=null){
		$t = false;
		foreach ($this->_items as $key => $item){
			if ($item->getType() !== 'section'){
				continue;
			}
			if ($item->getName() === $sectionName){
				if ($instanceName!==null){
					if ($item->getInstanceName() === $instanceName){
						unset($this->_items[$key]);
						$t = true;
					}
				} else {
					unset($this->_items[$key]);
					$t = true;
				}
			}
		}
		return $t;
	}
	public function setSection($sectionName, sectionItem $sectionItem){
		$t = false;
		foreach ($this->_items as $key => $item){
			if ($item->getType() !== 'section'){
				continue;
			}
			if ($item->getName() === $sectionName){
				$this->_items[$key] = $sectionItem;
				$t = true;
			}
		}
		return $t;
	}
	
	public function setInstance($instanceName, $value){
		foreach ($this->_items as $key => $item){
			if ($item->getType() !== 'section'){
				continue;
			}
			if ($item->getInstanceName() === $instanceName){
				$this->_items[$key] = $value;
			}
		}
		// test pour valeur de retour ?
	}
	
	
	public function setSectionInstance($sectionName, $instanceName, $value){
		foreach ($this->_items as $key => $item){
			if ($item->getType() !== 'section'){
				continue;
			}
			if (($item->getName() === $sectionName)&&($item->getInstanceName() === $instanceName)){
				$this->_items[$key] = $value;
			}
		}
		// test pour valeur de retour ?
	}
	
	public function __set($name, $value){
		return $this->setSection($name, $value);
		// use others methode width instance ?
		// create section if not existe?
	}
	
}
/**
* Classe <b>configReader</b>.
*
* @name configReader
* @author steweb57
* @version 0.4.0
*/
class configReader extends sectionItem
{
	private $_file = null;
	private $_pt	= null;
	protected $_name	= "root";
	
	/**
	 * Constructeur
	 *
	 * <p>création de l'instance de la classe</p>
	 *
	 * @name configReader::__construct()
	 * @param string $filename
	 * @return void
	 */
	public function __construct($filename=null) {

		if ($filename !== null){
			$this->parse($filename);
		}
	}
	/**
	 * Destructeur
	 */
	public function __destruct() {
		$this->_file = NULL;
	}
	/**
	 *
	 * @name configReader::_deleteComment()
	 * @param string $line
	 * @return void
	 */
	private function _deleteComment($line){
		return $line;
	}
	/**
	 * parse the configFile
	 *
	 * @name config_file::parse()
	 * @param file $filename
	 * @return void
	 */
	public function parse($filename=null){
		if ($filename !== null){
			// test is_file et file_exist à faire 
			$this->_file = $filename;
		}
		if ($this->_file===null) return false;
		
		$fro = fopen( $this->_file, 'r' );
		while( $line = fgets( $fro ) )
		{
			/*
			on saute les commentaires
			*/
			if (preg_match('/^[[:space:]]*#/',$line) || preg_match('/^[[:space:]]*$/',$line))
					continue;
					
			//test d'entrée dans une section
			if (preg_match('`^([\sa-zA-Z0-9_-]*{[\s[:print:]]*)$`',$line)){//test section
							
				// Nétoyage des commentaires et espaces
				$tmp = explode("{", $line, 2);
				$line = trim($tmp[0]);
											
				// test here if exist an instance name
				$tmpInstanceName = "";
				$t = explode(" ", $line, 2);
				$tmpSectionName = $t[0];
				if (count($t)>1){
					$tmpInstanceName = $t[1];
				}
				// end test of an instance name
							
				if ($this->_pt===null){
					$this->addSection($tmpSectionName, $tmpInstanceName);
					$this->_pt = end($this->_items);
				} else {
					$this->_pt = $this->_pt->addSection($tmpSectionName, $tmpInstanceName);
				}
			}
			//recherche fin de section
			elseif (preg_match('`^([\s]*}[\s[:print:]]*)$`',$line)){//test fin de section
				$this->_pt = $this->_pt->getParent();
			}
			//test de présence d'une pair parametre/valeur
			elseif (preg_match('`^([\s[:print:]]*=)`',$line)){ //test pair
				$tmpPair = trim($line);
				list($pairName, $pairValue) = explode('=', $tmpPair, 2);
				$pairName = trim($pairName);
				$pairValue = trim($pairValue);
				
				/*
				A FAIRE : 
				- prendre en compte le multi-ligne
				*/
				$l = strlen($pairValue);
				if (strpos($pairValue, "'") === 0){ // valeur entre des quotes
					$pairValue = preg_replace("`^[']([[:print:]]*)[']([[:print:]]*)`","$1",$pairValue);
				}elseif(strpos($pairValue, '"') === 0){// valeur entre des double-quotes
					$pairValue = preg_replace('`^["]([[:print:]]*)["]([[:print:]]*)`','$1',$pairValue);
				}else{ // valeur sans quote ou double-quote
					//suppression des commentaires (pour un # dans la chaine, alors il faut que la chaine soit entre quote ou double-quote)
					$tmp = explode("#", $pairValue, 2);
					$pairValue = trim($tmp[0]);
				}
				
				if ($this->_pt===null){
					$this->addPair($pairName,$pairValue);
				} else {
					$this->_pt->addPair($pairName,$pairValue);
				}
			}
			//test de présence d'un parametre (c'est traité comme une section mais sans contenu!)
			elseif (preg_match('`^([\s[:print:]]*)$`',$line)) { //test value
				$tmpItem = trim($line);
				if ($this->_pt===null){
					$this->addSection($tmpItem);
				} else {
					$this->_pt->addSection($tmpItem);
				}
			}
		}
		fclose( $fro );
		$this->_pt=null;
	}
}
?>