<?php
/*
This class handled of ldap configuration.
WARNING! This class can't says if the configuration is valid or not.
*/
require_once('configreader.php');
class siteConfig
{
	/*
	$_sections : radius sections container
	*/
	protected $_sections = Array();
	
	public function __construct() {
		$this->_sections['authorize']		= new sectionItem('authorize');
		$this->_sections['authenticate']	= new sectionItem('authorize');
		$this->_sections['preacct']			= new sectionItem('preacct');
		$this->_sections['accounting']		= new sectionItem('accounting');
		$this->_sections['session']			= new sectionItem('session');
		$this->_sections['post-auth']		= new sectionItem('post-auth');
		$this->_sections['pre-proxy']		= new sectionItem('pre-proxy');
		$this->_sections['post-proxy']		= new sectionItem('post-proxy');
	}
	private function _doSpace($nbspace = 0){
		$resp="";
		for ($i = 1; $i <= $nbspace; $i++){
			$resp.="	";
		}
		return $resp;
	}	
	private function _writeModule($module, $default=null, $space=0){
		if (is_object($module)){
			if ($module->getType()==='section'){
				$resp = $this->_doSpace($space).$module." ".$module->getInstanceName();
				if (count($module->getAll())>0){
					$resp .= " { \n";
					foreach ($module->getAll() as $childItem) {
						$resp .= $this->_writeModule($childItem, null, $space+1);
					}				
					$resp .= $this->_doSpace($space)." } \n";
				}	elseif ($module->getInstanceName()!==""){
					$resp .= " { \n";
					$resp .= "\n";
					$resp .= $this->_doSpace($space)." } \n";
				} else {
					$resp .= "\n";
				}
				return $resp;
			}elseif ($module->getType()==='pair'){
				$resp = $this->_doSpace($space).$module->getName()."=";
				$resp .=$module->getPair($module->getName());
				$resp .="\n";
				return $resp;
			} else {
			
			}
		} elseif (is_array($module)&&count($module)>0) {
			/*
			for section width multiple instance
			!!! empty section are array too!! we must count the array!
			*/
			$resp = "";
			foreach ($module as $instance) {
				$resp .= $this->_doSpace($space). $this->_writeModule($instance, $default, $space);
			}
			return $resp;
		} else {
			return $default;
		}
	}
	public function __get($attr){
		if (array_key_exists($attr, $this->_sections)){
			return $this->_sections[$attr];
		}
		return false;
	}
	public function __set($attr, $value){
	/*
	Ne prend pas en compte les section contenant un "-". Pour ce cas utiliser la méthode setSection('sectionName', 'myvalue').
	*/
			$this->setSection($sectionName, $value);
			exit('ee');
	}
	public function setSection($sectionName, $value){
		if (array_key_exists($sectionName, $this->_sections)){
			$this->_sections[$sectionName] = $value;
		}
	}
	public function load($confFile){
		// use here the parsing class
		require_once("configreader.php");
		
		$r = new configReader($confFile);
		$this->_sections['authorize']		= $r->getSection('authorize');
		$this->_sections['authenticate']	= $r->getSection('authenticate');
		$this->_sections['preacct']			= $r->getSection('preacct');
		$this->_sections['accounting']		= $r->getSection('accounting');
		$this->_sections['session']			= $r->getSection('session');
		$this->_sections['post-auth']		= $r->getSection('post-auth');
		$this->_sections['pre-proxy']		= $r->getSection('pre-proxy');
		$this->_sections['post-proxy']		= $r->getSection('post-proxy');
	}
	public function __toString() {
		return "siteConfig";
	}
	public function save($savefile = null, $returnconfig = false){
	/*
	outpout with template (faster and we can write a lot of comments)
	*/
	$config = "
######################################################################
#
#	As of 2.0.0, FreeRADIUS supports virtual hosts using the
#	\"server\" section, and configuration directives.
#
#	Virtual hosts should be put into the \"sites-available\"
#	directory.  Soft links should be created in the \"sites-enabled\"
#	directory to these files.  This is done in a normal installation.
#
#
######################################################################
#
#	Read \"man radiusd\" before editing this file.  See the section
#	titled DEBUGGING.  It outlines a method where you can quickly
#	obtain the configuration you want, without running into
#	trouble.  See also \"man unlang\", which documents the format
#	of this file.
#
#	This configuration is designed to work in the widest possible
#	set of circumstances, with the widest possible number of
#	authentication methods.  This means that in general, you should
#	need to make very few changes to this file.
#
#	The best way to configure the server for your local system
#	is to CAREFULLY edit this file.  Most attempts to make large
#	edits to this file will BREAK THE SERVER.  Any edits should
#	be small, and tested by running the server with \"radiusd -X\".
#	Once the edits have been verified to work, save a copy of these
#	configuration files somewhere.  (e.g. as a \"tar\" file).  Then,
#	make more edits, and test, as above.
#
#	There are many \"commented out\" references to modules such
#	as ldap, sql, etc.  These references serve as place-holders.
#	If you need the functionality of that module, then configure
#	it in radiusd.conf, and un-comment the references to it in
#	this file.  In most cases, those small changes will result
#	in the server being able to connect to the DB, and to
#	authenticate users.
#
######################################################################

#
#	In 1.x, the \"authorize\", etc. sections were global in
#	radiusd.conf.  As of 2.0, they SHOULD be in a server section.
#
#	The server section with no virtual server name is the \"default\"
#	section.  It is used when no server name is specified.
#
#	We don't indent the rest of this file, because doing so
#	would make it harder to read.
#

#  Authorization. First preprocess (hints and huntgroups files),
#  then realms, and finally look in the \"users\" file.
#
#  The order of the realm modules will determine the order that
#  we try to find a matching realm.
#
#  Make *sure* that 'preprocess' comes before any realm if you
#  need to setup hints for the remote radius server
authorize {
	#
	#  The preprocess module takes care of sanitizing some bizarre
	#  attributes in the request, and turning them into attributes
	#  which are more standard.
	#
	#  It takes care of processing the 'raddb/hints' and the
	#  'raddb/huntgroups' files.
	#
	#  It also adds the %{Client-IP-Address} attribute to the request.
	".$this->_writeModule($this->_sections['authorize']->preprocess, 'preprocess')."

	#
	#  If you want to have a log of authentication requests,
	#  un-comment the following line, and the 'detail auth_log'
	#  section, above.
	".$this->_writeModule($this->_sections['authorize']->getSection('auth-log'), '#	auth_log')."
	#
	#  The chap module will set 'Auth-Type := CHAP' if we are
	#  handling a CHAP request and Auth-Type has not already been set
".$this->_writeModule($this->_sections['authorize']->chap, '#	chap')."
	#
	#  If the users are logging in with an MS-CHAP-Challenge
	#  attribute for authentication, the mschap module will find
	#  the MS-CHAP-Challenge attribute, and add 'Auth-Type := MS-CHAP'
	#  to the request, which will cause the server to then use
	#  the mschap module for authentication.
".$this->_writeModule($this->_sections['authorize']->mschap, '#	mschap')."
	#
	#  If you have a Cisco SIP server authenticating against
	#  FreeRADIUS, uncomment the following line, and the 'digest'
	#  line in the 'authenticate' section.
".$this->_writeModule($this->_sections['authorize']->digest, '#	digest')."
	#
	#  Look for IPASS style 'realm/', and if not found, look for
	#  '@realm', and decide whether or not to proxy, based on
	#  that.
".$this->_writeModule($this->_sections['authorize']->IPASS, '#	IPASS')."
	#
	#  If you are using multiple kinds of realms, you probably
	#  want to set \"ignore_null = yes\" for all of them.
	#  Otherwise, when the first style of realm doesn't match,
	#  the other styles won't be checked.
	#
".$this->_writeModule($this->_sections['authorize']->suffix, '#	suffix')."
".$this->_writeModule($this->_sections['authorize']->ntdomain, '#	ntdomain')."
	#
	#  This module takes care of EAP-MD5, EAP-TLS, and EAP-LEAP
	#  authentication.
	#
	#  It also sets the EAP-Type attribute in the request
	#  attribute list to the EAP type from the packet.
	#
	#  As of 2.0, the EAP module returns \"ok\" in the authorize stage
	#  for TTLS and PEAP.  In 1.x, it never returned \"ok\" here, so
	#  this change is compatible with older configurations.
	#
	#  The example below uses module failover to avoid querying all
	#  of the following modules if the EAP module returns \"ok\".
	#  Therefore, your LDAP and/or SQL servers will not be queried
	#  for the many packets that go back and forth to set up TTLS
	#  or PEAP.  The load on those servers will therefore be reduced.
	#
".$this->_writeModule($this->_sections['authorize']->eap, '#	eap {
#		ok = return
#	}')."
	#
	#  Pull crypt'd passwords from /etc/passwd or /etc/shadow,
	#  using the system API's to get the password.  If you want
	#  to read /etc/passwd or /etc/shadow directly, see the
	#  passwd module in radiusd.conf.
	#
".$this->_writeModule($this->_sections['authorize']->unix, '#	unix')."
	#
	#  Read the 'users' file
".$this->_writeModule($this->_sections['authorize']->files, '#	files')."
	#
	#  Look in an SQL database.  The schema of the database
	#  is meant to mirror the \"users\" file.
	#
	#  See \"Authorization Queries\" in sql.conf
	".$this->_writeModule($this->_sections['authorize']->sql, 'sql')."
	".$this->_writeModule($this->_sections['authorize']->noresetcounter, 'noresetcounter')."
	".$this->_writeModule($this->_sections['authorize']->dailycounter, 'dailycounter')."
	".$this->_writeModule($this->_sections['authorize']->monthlycounter, 'monthlycounter')."
	#
	#  If you are using /etc/smbpasswd, and are also doing
	#  mschap authentication, the un-comment this line, and
	#  configure the 'etc_smbpasswd' module, above.
".$this->_writeModule($this->_sections['authorize']->etc_smbpasswd, '#	etc_smbpasswd')."
	#
	#  The ldap module will set Auth-Type to LDAP if it has not
	#  already been set
".$this->_writeModule($this->_sections['authorize']->ldap, '#	ldap {
#		fail = 1
#	}')."
	#
	#  Enforce daily limits on time spent logged in.
".$this->_writeModule($this->_sections['authorize']->daily, '#	daily')."
	#
	# Use the checkval modulel
".$this->_writeModule($this->_sections['authorize']->checkval, '#	checkval')."
	".$this->_writeModule($this->_sections['authorize']->expiration, 'expiration')."
".$this->_writeModule($this->_sections['authorize']->logintime, 'logintime')."
	#
	#  If no other module has claimed responsibility for
	#  authentication, then try to use PAP.  This allows the
	#  other modules listed above to add a \"known good\" password
	#  to the request, and to do nothing else.  The PAP module
	#  will then see that password, and use it to do PAP
	#  authentication.
	#
	#  This module should be listed last, so that the other modules
	#  get a chance to set Auth-Type for themselves.
	#
".$this->_writeModule($this->_sections['authorize']->pap, '#	pap')."
	#
	#  If \"status_server = yes\", then Status-Server messages are passed
	#  through the following section, and ONLY the following section.
	#  This permits you to do DB queries, for example.  If the modules
	#  listed here return \"fail\", then NO response is sent.
	#
".$this->_writeModule($this->_sections['authorize']->getSection('Autz-Type'), '#	Autz-Type Status-Server {
#
#	}')."

}


#  Authentication.
#
#
#  This section lists which modules are available for authentication.
#  Note that it does NOT mean 'try each module in order'.  It means
#  that a module from the 'authorize' section adds a configuration
#  attribute 'Auth-Type := FOO'.  That authentication type is then
#  used to pick the apropriate module from the list below.
#

#  In general, you SHOULD NOT set the Auth-Type attribute.  The server
#  will figure it out on its own, and will do the right thing.  The
#  most common side effect of erroneously setting the Auth-Type
#  attribute is that one authentication method will work, but the
#  others will not.
#
#  The common reasons to set the Auth-Type attribute by hand
#  is to either forcibly reject the user (Auth-Type := Reject),
#  or to or forcibly accept the user (Auth-Type := Accept).
#
#  Note that Auth-Type := Accept will NOT work with EAP.
#
#  Please do not put \"unlang\" configurations into the \"authenticate\"
#  section.  Put them in the \"post-auth\" section instead.  That's what
#  the post-auth section is for.
#
authenticate {
#	#
#	#  PAP authentication, when a back-end database listed
#	#  in the 'authorize' section supplies a password.  The
#	#  password can be clear-text, or encrypted.
".$this->_writeModule($this->_sections['authenticate']->getSectionInstance('Auth-Type','PAP'), '#	Auth-Type PAP {
#		pap
#	}')."
#
#	#
#	#  Most people want CHAP authentication
#	#  A back-end database listed in the 'authorize' section
#	#  MUST supply a CLEAR TEXT password.  Encrypted passwords
#	#  won't work.
".$this->_writeModule($this->_sections['authenticate']->getSectionInstance('Auth-Type','CHAP'), '#	Auth-Type CHAP {
#		chap
#	}')."
#
#	#
#	#  MSCHAP authentication.
".$this->_writeModule($this->_sections['authenticate']->getSectionInstance('Auth-Type','MS-CHAP'), '#	Auth-Type MS-CHAP {
#		mschap
#	}')."
#
#	#
#	#  If you have a Cisco SIP server authenticating against
#	#  FreeRADIUS, uncomment the following line, and the 'digest'
#	#  line in the 'authorize' section.
".$this->_writeModule($this->_sections['authenticate']->digest, '#	digest')."
#
#	#
#	#  Pluggable Authentication Modules.
".$this->_writeModule($this->_sections['authenticate']->pam, '#	pam')."
#
#	#
#	#  See 'man getpwent' for information on how the 'unix'
#	#  module checks the users password.  Note that packets
#	#  containing CHAP-Password attributes CANNOT be authenticated
#	#  against /etc/passwd!  See the FAQ for details.
#	#
".$this->_writeModule($this->_sections['authenticate']->unix, '#	unix')."
#
#	# Uncomment it if you want to use ldap for authentication
#	#
#	# Note that this means \"check plain-text password against
#	# the ldap database\", which means that EAP won't work,
#	# as it does not supply a plain-text password.
".$this->_writeModule($this->_sections['authenticate']->getSectionInstance('Auth-Type','LDAP'), '#	Auth-Type LDAP {
#		ldap
#	}')."
#
#	#
#	#  Allow EAP authentication.
".$this->_writeModule($this->_sections['authenticate']->eap, '#	eap')."
}


#
#  Pre-accounting.  Decide which accounting type to use.
#
preacct {
	".$this->_writeModule($this->_sections['preacct']->preprocess, '#	preprocess')."

	#
	#  Ensure that we have a semi-unique identifier for every
	#  request, and many NAS boxes are broken.
".$this->_writeModule($this->_sections['preacct']->acct_unique, '#	acct_unique')."

	#
	#  Look for IPASS-style 'realm/', and if not found, look for
	#  '@realm', and decide whether or not to proxy, based on
	#  that.
	#
	#  Accounting requests are generally proxied to the same
	#  home server as authentication requests.
".$this->_writeModule($this->_sections['preacct']->IPASS, '#	IPASS')."
".$this->_writeModule($this->_sections['preacct']->suffix, '#	suffix')."
".$this->_writeModule($this->_sections['preacct']->ntdomain, '#	ntdomain')."

	#
	#  Read the 'acct_users' file
".$this->_writeModule($this->_sections['preacct']->files, '#	files')."
}

#
#  Accounting.  Log the accounting data.
#
accounting {
	#
	#  Create a 'detail'ed log of the packets.
	#  Note that accounting requests which are proxied
	#  are also logged in the detail file.
".$this->_writeModule($this->_sections['accounting']->detail, '#	detail')."
".$this->_writeModule($this->_sections['accounting']->daily, '#	daily')."

	#  Update the wtmp file
	#
	#  If you don't use \"radlast\", you can delete this line.
".$this->_writeModule($this->_sections['accounting']->unix, '#	unix')."

	#
	#  For Simultaneous-Use tracking.
	#
	#  Due to packet losses in the network, the data here
	#  may be incorrect.  There is little we can do about it.
".$this->_writeModule($this->_sections['accounting']->radutmp, '#	radutmp')."
	".$this->_writeModule($this->_sections['accounting']->sradutmp, 'sradutmp')."

	#  Return an address to the IP Pool when we see a stop record.
".$this->_writeModule($this->_sections['accounting']->main_pool, '#	main_pool')."

	#
	#  Log traffic to an SQL database.
	#
	#  See \"Accounting queries\" in sql.conf
	".$this->_writeModule($this->_sections['accounting']->sql, 'sql')."

	#
	#  Instead of sending the query to the SQL server,
	#  write it into a log file.
	#
".$this->_writeModule($this->_sections['accounting']->sql_log, '#	sql_log')."

	#  Cisco VoIP specific bulk accounting
".$this->_writeModule($this->_sections['accounting']->getSection('pgsql-voip'), '#	pgsql-voip')."

	#  Filter attributes from the accounting response.
	".$this->_writeModule($this->_sections['accounting']->getSection('attr_filter.accounting_response'), 'attr_filter.accounting_response')."

	#
	#  See \"Autz-Type Status-Server\" for how this works.
	#
".$this->_writeModule($this->_sections['accounting']->getSectionInstance('Acct-Type','Status-Server'), '#	Acct-Type Status-Server {
#
#	}')."

}


#  Session database, used for checking Simultaneous-Use. Either the radutmp
#  or rlm_sql module can handle this.
#  The rlm_sql module is *much* faster
session {
".$this->_writeModule($this->_sections['session']->radutmp, '#	radutmp')."

	#
	#  See \"Simultaneous Use Checking Queries\" in sql.conf
	".$this->_writeModule($this->_sections['session']->sql, '#	sql')."
}


#  Post-Authentication
#  Once we KNOW that the user has been authenticated, there are
#  additional steps we can take.
post-auth {
	#  Get an address from the IP Pool.
".$this->_writeModule($this->_sections['post-auth']->main_pool, '#	main_pool')."

	#
	#  If you want to have a log of authentication replies,
	#  un-comment the following line, and the 'detail reply_log'
	#  section, above.
".$this->_writeModule($this->_sections['post-auth']->reply_log, '#	reply_log')."

	#
	#  After authenticating the user, do another SQL query.
	#
	#  See \"Authentication Logging Queries\" in sql.conf
".$this->_writeModule($this->_sections['post-auth']->sql, '#	sql')."

	#
	#  Instead of sending the query to the SQL server,
	#  write it into a log file.
	#
".$this->_writeModule($this->_sections['post-auth']->sql_log, '#	sql_log')."

	#
	#  Un-comment the following if you have set
	#  'edir_account_policy_check = yes' in the ldap module sub-section of
	#  the 'modules' section.
	#
".$this->_writeModule($this->_sections['post-auth']->ldap, '#	ldap')."

".$this->_writeModule($this->_sections['post-auth']->exec, '#	exec')."

	#
	#  Access-Reject packets are sent through the REJECT sub-section of the
	#  post-auth section.
	#
	#  Add the ldap module name (or instance) if you have set
	#  'edir_account_policy_check = yes' in the ldap module configuration
	#
	Post-Auth-Type REJECT {
		attr_filter.access_reject
	}
	".$this->_writeModule($this->_sections['post-auth']->files, '#	files')."
}

#
#  When the server decides to proxy a request to a home server,
#  the proxied request is first passed through the pre-proxy
#  stage.  This stage can re-write the request, or decide to
#  cancel the proxy.
#
#  Only a few modules currently have this method.
#
pre-proxy {
".$this->_writeModule($this->_sections['preacct']->attr_rewrite, '#	attr_rewrite')."

	#  Uncomment the following line if you want to change attributes
	#  as defined in the preproxy_users file.
".$this->_writeModule($this->_sections['preacct']->files, '#	files')."

	#  Uncomment the following line if you want to filter requests
	#  sent to remote servers based on the rules defined in the
	#  'attrs.pre-proxy' file.
".$this->_writeModule($this->_sections['preacct']->getSection('attr_filter.pre-proxy'), '#	attr_filter.pre-proxy')."

	#  If you want to have a log of packets proxied to a home
	#  server, un-comment the following line, and the
	#  'detail pre_proxy_log' section, above.
".$this->_writeModule($this->_sections['preacct']->pre_proxy_log, '#	pre_proxy_log')."
}

#
#  When the server receives a reply to a request it proxied
#  to a home server, the request may be massaged here, in the
#  post-proxy stage.
#
post-proxy {

	#  If you want to have a log of replies from a home server,
	#  un-comment the following line, and the 'detail post_proxy_log'
	#  section, above.
".$this->_writeModule($this->_sections['post-proxy']->post_proxy_log, '#	post_proxy_log')."

".$this->_writeModule($this->_sections['post-proxy']->attr_rewrite, '#	attr_rewrite')."

	#  Uncomment the following line if you want to filter replies from
	#  remote proxies based on the rules defined in the 'attrs' file.
".$this->_writeModule($this->_sections['post-proxy']->getSection('attr_filter.post-proxy'), '#	attr_filter.post-proxy')."

	#
	#  If you are proxying LEAP, you MUST configure the EAP
	#  module, and you MUST list it here, in the post-proxy
	#  stage.
	#
	#  You MUST also use the 'nostrip' option in the 'realm'
	#  configuration.  Otherwise, the User-Name attribute
	#  in the proxied request will not match the user name
	#  hidden inside of the EAP packet, and the end server will
	#  reject the EAP request.
	#
".$this->_writeModule($this->_sections['post-proxy']->eap, '#	eap')."

	#
	#  If the server tries to proxy a request and fails, then the
	#  request is processed through the modules in this section.
	#
	#  The main use of this section is to permit robust proxying
	#  of accounting packets.  The server can be configured to
	#  proxy accounting packets as part of normal processing.
	#  Then, if the home server goes down, accounting packets can
	#  be logged to a local \"detail\" file, for processing with
	#  radrelay.  When the home server comes back up, radrelay
	#  will read the detail file, and send the packets to the
	#  home server.
	#
	#  With this configuration, the server always responds to
	#  Accounting-Requests from the NAS, but only writes
	#  accounting packets to disk if the home server is down.
	#
".$this->_writeModule($this->_sections['post-proxy']->getSection('Post-Proxy-Type'), '#	Post-Proxy-Type Fail {
#			detail
#	}')."

}";

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
		if ($returnconfig){
			return $config;
		}else{
			return true;
		}
	}
}