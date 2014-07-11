<?php
/*
This class handled of ldap configuration.
WARNING! This class can't says if the configuration is valid or not.
*/

class ldapConfig
{
	protected $_items = Array();
	protected $_tls = array();
	protected $instanceName;
	
	public function __construct($instanceName=null) {
		if ($instanceName!== null)
			$this->instanceName = $instanceName;
		// LDAP setting
		$this->_items['protocol']					= 'ldap';
		$this->_items['host']						= 'test';
		$this->_items['server']						= $this->_items['protocol'].'://'.$this->_items['host'];
		$this->_items['port']						= '389';//not use yet (689 = ldaps)
		$this->_items['identity']					= '';
		$this->_items['password']					= '';
		$this->_items['basedn']						= 'dc=example,dc=com';
		$this->_items['uid']						= 'uid';
		$this->_items['filter']						= "($this->_items['uid']=%{Stripped-User-Name:-%{User-Name}})";
		$this->_items['base_filter']				= '';
		$this->_items['ldap_connections_number']	= '5';
		$this->_items['timeout']					= '4';
		$this->_items['timelimit']					= '3';
		$this->_items['net_timeout'] 				= '1';
		// TLS setting related items
		$this->_tls['start_tls']					= 'no'; // if no all tls config are comments
		$this->_tls['cacertfile']					= '#';
		$this->_tls['cacertdir']					= '#';
		$this->_tls['certfile']						= '#';
		$this->_tls['keyfile']						= '#';
		$this->_tls['randfile']						= '#';
		$this->_tls['require_cert']					= '#';
		// others ldap setting (optional)
		$this->_items['default_profile']			= '#';
		$this->_items['profile_attribute']			= '#';
		$this->_items['access_attr']				= '#';
		// Mapping of RADIUS dictionary attributes to LDAP
		// directory attributes.
		$this->_items['dictionary_mapping']	= '${confdir}/ldap.attrmap';
		// for ldap like NOVEL
		$this->_items['password_attribute']			= '#';
		$this->_items['edir_account_policy_check']	= 'no';
		//  Group membership checking.  Disabled by default.
		$this->_items['groupname_attribute']		= '#';
		$this->_items['groupmembership_filter']		= '#';
		$this->_items['groupmembership_attribute']	= '#';
		$this->_items['compare_check_items']		= '#';
		$this->_items['do_xlat']					= '#';
		$this->_items['access_attr_used_for_allow']	= '#';
		// auth option
		$this->_items['set_auth_type']				= '#';
		// debug option
		$this->_items['ldap_debug']					= '#';
	}
	
	public function __get($attr){ // to get an $item
		if ($attr==='tls'){
			return $this->_tls;
		} elseif (array_key_exists($attr, $this->_items)){
			return $this->_items[$attr];
		} elseif (array_key_exists($attr, $this->_tls)){
			return $this->_tls[$attr];
		}
		// nothing else!
	}
	public function __set($attr, $value){// to set an $item
		if (array_key_exists($attr, $this->_items)){
			switch ($attr){
				case "protocol":
					$this->_items['protocol']	= $value;
					$this->_items['server']		= $this->_items['protocol'].'://'.$this->_items['host'];
					break;
				case "host":
					$this->_items['host']		= $value;
					$this->_items['server']		= $this->_items['protocol'].'://'.$this->_items['host'];
					break;
				case "server":
					// extract protocole & host
					$tmp = explode("://",$value,2);
					if (count($tmp) == 2){
						$this->_items['protocol'] = $tmp[0];
						$this->_items['host'] 	= $tmp[1];
					} else {
						$this->_items['protocol'] = 'ldap';
						$this->_items['host'] 	= $tmp[0];
					}
					$this->_items['server'] = $this->_items['protocol'].'://'.$this->_items['host'];
					break;
				case "uid":
					$this->_items['uid']		= $value;
					$this->_items['filter']		= "(".$this->_items['uid']."=%{Stripped-User-Name:-%{User-Name}})";
					break;
				case "filter":
					// extract uid
					if (preg_match('`^[\(]([\sa-zA-Z0-9_-]*)=\%\{Stripped\-User\-Name:\-\%\{User-Name\}\}\)`',$value)){
						$this->_items['uid'] = preg_replace('`^[\(]([\sa-zA-Z0-9_-]*)=\%\{Stripped\-User\-Name:\-\%\{User-Name\}\}\)`','$1',$value);
					} else {
						$this->_items['uid'] = 'uid';
					}
					$this->_items['filter']		= "($this->_items['uid']=%{Stripped-User-Name:-%{User-Name}})";
					break;
				default:
					$this->_items[$attr] = $value;
			}
		} elseif (array_key_exists($attr, $this->_tls)){
			$this->_tls[$attr] = $value;
		}
	}
	public function load($confFile){
		// use here the parsing class
		require_once("configreader.php");
		$r = new configReader($confFile);
		/*
		loading only if the file containt only one ldap instance.
		If more instance are found, we use the default values instead.
		*/
		if (is_object($r->ldap)){
			$this->instanceName = $r->ldap->getInstanceName();
			$items = $r->ldap->getpair();

			foreach ($items as $pair){
				$pairName = $pair->getName();
				$pairValue = $pair->getPair($pairName);
				if (array_key_exists($pairName , $this->_items))
					$this->$pairName = $pairValue; // we use __set() function to have all exceptions!
			}
			if (is_object($r->ldap->tls)){
				$tls = $r->ldap->tls->getpair();
				
				foreach ($tls as $pair){
					$tlsPairName = $pair->getName();
					$tlsPairValue = $pair->getPair($tlsPairName);
					if (array_key_exists($tlsPairName , $this->_tls))
						$this->$tlsPairName = $pairValue; // we use __set() function to have all exceptions!
				}
			}
		}
	}
	public function __toString() {
		return $this->save(null, true);
    }
	protected function _noComment($name, $value, $quote = false){
		if ($value !== '#'){
			if ($quote === true){
				return $name." = \"".$value."\"";
			} else {
				return $name." = ".$value;
			}
		}
	}
	public function save($savefile = null, $returnconfig = false){
	// make config file
	$config = "
	# Lightweight Directory Access Protocol (LDAP)
	#
	#  This module definition allows you to use LDAP for
	#  authorization and authentication.
	#
	#  See raddb/sites-available/default for reference to the
	#  ldap module in the authorize and authenticate sections.
	#
	#  However, LDAP can be used for authentication ONLY when the
	#  Access-Request packet contains a clear-text User-Password
	#  attribute.  LDAP authentication will NOT work for any other
	#  authentication method.
	#
	#  This means that LDAP servers don't understand EAP.  If you
	#  force \"Auth-Type = LDAP\", and then send the server a
	#  request containing EAP authentication, then authentication
	#  WILL NOT WORK.
	#
	#  The solution is to use the default configuration, which does
	#  work.
	#
	#  Setting \"Auth-Type = LDAP\" is ALMOST ALWAYS WRONG.  We
	#  really can't emphasize this enough.
	#	
	ldap ".$this->instanceName."{
		#
		#  Note that this needs to match the name in the LDAP
		#  server certificate, if you're using ldaps.
		server = \"".$this->_items['server']."\"
		identity = \"".$this->_items['identity']."\"
		password = ".$this->_items['password']."
		basedn = \"".$this->_items['basedn']."\"
		filter = \"".$this->_items['filter']."\"
		base_filter = \"".$this->_items['base_filter']."\"

		#  How many connections to keep open to the LDAP server.
		#  This saves time over opening a new LDAP socket for
		#  every authentication request.
		ldap_connections_number = ".$this->_items['ldap_connections_number']."

		# seconds to wait for LDAP query to finish. default: 20
		timeout = ".$this->_items['timeout']."

		#  seconds LDAP server has to process the query (server-side
		#  time limit). default: 20
		#
		#  LDAP_OPT_TIMELIMIT is set to this value.
		timelimit = ".$this->_items['timelimit']."

		#
		#  seconds to wait for response of the server. (network
		#   failures) default: 10
		#
		#  LDAP_OPT_NETWORK_TIMEOUT is set to this value.
		net_timeout = ".$this->_items['net_timeout']."

		#
		#  This subsection configures the tls related items
		#  that control how FreeRADIUS connects to an LDAP
		#  server.  It contains all of the \"tls_*\" configuration
		#  entries used in older versions of FreeRADIUS.  Those
		#  configuration entries can still be used, but we recommend
		#  using these.
		#
		tls {
			# Set this to 'yes' to use TLS encrypted connections
			# to the LDAP database by using the StartTLS extended
			# operation.
			#			
			# The StartTLS operation is supposed to be
			# used with normal ldap connections instead of
			# using ldaps (port 689) connections
			start_tls = ".$this->_tls['start_tls']."

			# cacertfile	= /path/to/cacert.pem
			# cacertdir		= /path/to/ca/dir/
			# certfile		= /path/to/radius.crt
			# keyfile		= /path/to/radius.key
			# randfile		= /path/to/rnd
			".$this->_noComment("cacertfile", $this->_tls['cacertfile'])."
			".$this->_noComment("cacertdir", $this->_tls['cacertdir'])."
			".$this->_noComment("certfile", $this->_tls['certfile'])."
			".$this->_noComment("keyfile", $this->_tls['keyfile'])."
			".$this->_noComment("randfile", $this->_tls['randfile'])."
			#  Certificate Verification requirements.  Can be:
			#    \"never\" (don't even bother trying)
			#    \"allow\" (try, but don't fail if the cerificate
			#		can't be verified)
			#    \"demand\" (fail if the certificate doesn't verify.)
			#
			#	The default is \"allow\"
			# require_cert	= \"demand\"
			".$this->_noComment("require_cert", $this->_tls['require_cert'], true)."
		}

		# default_profile = \"cn=radprofile,ou=dialup,o=My Org,c=UA\"
		# profile_attribute = \"radiusProfileDn\"
		# access_attr = \"dialupAccess\"
		".$this->_noComment("default_profile", $this->_items['default_profile'], true)."
		".$this->_noComment("profile_attribute", $this->_items['profile_attribute'], true)."
		".$this->_noComment("access_attr", $this->_items['access_attr'], true)."
		# Mapping of RADIUS dictionary attributes to LDAP
		# directory attributes.
		dictionary_mapping = ".$this->_items['dictionary_mapping']."

		#  Set password_attribute = nspmPassword to get the
		#  user's password from a Novell eDirectory
		#  backend. This will work ONLY IF FreeRADIUS has been
		#  built with the --with-edir configure option.
		#
		#  See also the following links:
		#
		#  http://www.novell.com/coolsolutions/appnote/16745.html
		#  https://secure-support.novell.com/KanisaPlatform/Publishing/558/3009668_f.SAL_Public.html
		#
		#  Novell may require TLS encrypted sessions before returning
		#  the user's password.
		#
		# password_attribute = userPassword
		".$this->_noComment("access_attr", $this->_items['access_attr'])."
		#  Un-comment the following to disable Novell
		#  eDirectory account policy check and intruder
		#  detection. This will work *only if* FreeRADIUS is
		#  configured to build with --with-edir option.
		#
		edir_account_policy_check = no
		".$this->_noComment("access_attr", $this->_items['access_attr'])."
		#
		#  Group membership checking.  Disabled by default.
		#
		# groupname_attribute = cn
		# groupmembership_filter = \"(|(&(objectClass=GroupOfNames)(member=%{Ldap-UserDn}))(&(objectClass=GroupOfUniqueNames)(uniquemember=%{Ldap-UserDn})))\"
		# groupmembership_attribute = radiusGroupName
		".$this->_noComment("groupname_attribute", $this->_items['groupname_attribute'])."
		".$this->_noComment("groupmembership_filter", $this->_items['groupmembership_filter'], true)."
		".$this->_noComment("groupmembership_attribute", $this->_items['groupmembership_attribute'])."
		# compare_check_items = yes
		# do_xlat = yes
		# access_attr_used_for_allow = yes
		".$this->_noComment("compare_check_items", $this->_items['compare_check_items'])."
		".$this->_noComment("do_xlat", $this->_items['do_xlat'])."
		".$this->_noComment("access_attr_used_for_allow", $this->_items['access_attr_used_for_allow'])."
		#
		#  By default, if the packet contains a User-Password,
		#  and no other module is configured to handle the
		#  authentication, the LDAP module sets itself to do
		#  LDAP bind for authentication.
		#
		#  THIS WILL ONLY WORK FOR PAP AUTHENTICATION.
		#
		#  THIS WILL NOT WORK FOR CHAP, MS-CHAP, or 802.1x (EAP). 
		#
		#  You can disable this behavior by setting the following
		#  configuration entry to \"no\".
		#
		#  allowed values: {no, yes}
		# set_auth_type = yes
		# set_auth_type = no
		".$this->_noComment("set_auth_type", $this->_items['set_auth_type'])."
		#  ldap_debug: debug flag for LDAP SDK
		#  (see OpenLDAP documentation).  Set this to enable
		#  huge amounts of LDAP debugging on the screen.
		#  You should only use this if you are an LDAP expert.
		#
		#	default: 0x0000 (no debugging messages)
		#	Example:(LDAP_DEBUG_FILTER+LDAP_DEBUG_CONNS)
		#ldap_debug = 0x0028
		".$this->_noComment("ldap_debug", $this->_items['ldap_debug'])."
	}
	";
		if ($savefile !== null){
			// save config file
			if (is_file($savefile)){
				// save the file
				if (!is_writable($savefile))
					return false;
				$updatedFile = fopen( $savefile, 'w' );
				fwrite( $updatedFile, $config );
				fclose( $updatedFile );
			} else {
				// create a new file
				$newFile = fopen($savefile, 'w') or die("can't create file");
				fwrite( $newFile, $config );
				fclose( $newFile );
			}
		}	
		// test $returnconfig
		if (($returnconfig===true)||($returnconfig==="yes")){
			return $config;
		}else{
			return true;
		}
	}
}
?>