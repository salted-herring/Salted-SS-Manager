<?php

class SSHConnector {
    // SSH Host 
    private $ssh_host = 'myserver.example.com'; 
    // SSH Port 
    private $ssh_port = 22; 
    // SSH Server Fingerprint 
    private $ssh_server_fp = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'; 
    // SSH Username 
    private $ssh_auth_user = 'username'; 
    // SSH Public Key File 
    private $ssh_auth_pub = '/home/username/.ssh/id_rsa.pub'; 
    // SSH Private Key File 
    private $ssh_auth_priv = '/home/username/.ssh/id_rsa'; 
    // SSH Private Key Passphrase (null == no passphrase) 
    private $ssh_auth_pass; 
    // SSH Connection 
    private $connection;

    public function __construct($host, $port, $server_fp, $user, $pass, $pub = null, $priv = null) {
    	$this->ssh_host = $host;
    	$this->ssh_port = $port;
    	$this->ssh_server_fp = $server_fp;
    	$this->ssh_auth_user = $user;
    	$this->ssh_auth_pass = $pass;
    	$this->ssh_auth_pub = $pub;
    	$this->ssh_auth_priv = $priv;
    }
    
    public function connect() { 
        if (!($this->connection = ssh2_connect($this->ssh_host, $this->ssh_port))) { 
            throw new Exception('Cannot connect to server'); 
        } 
        $fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX); 
        if (strcmp($this->ssh_server_fp, $fingerprint) !== 0) { 
            throw new Exception('Unable to verify server identity!'); 
        }

        if (empty($this->ssh_auth_pub) || empty($this->ssh_auth_priv)) {
	        if( !ssh2_auth_password( $this->connection, $this->ssh_auth_user, $this->ssh_auth_pass ) ) {
		       throw new Exception('Autentication rejected by server'); 
	    	}

	    } else {
 	       if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass)) { 
    	        throw new Exception('Autentication rejected by server'); 
        	}
        }

        return $this->connection;
    } 
    public function exec($cmd, $detailed_feedback = false) {
        if (empty($this->connection)) {
            return false;
        }
        if (!($stream = ssh2_exec($this->connection, $cmd))) { 
            throw new Exception('SSH command failed'); 
        }
		$errstr = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		
        stream_set_blocking($stream, true); 
		stream_set_blocking($errstr, true);
        /*$data = ""; 
        while ($buf = fread($stream, 4096)) { 
            $data .= $buf; 
        }*/
		$data = stream_get_contents($stream);
        $errData = stream_get_contents($errstr);
		if (!empty($errData)) {
			$data .= $errData;
		}
        fclose($stream); 
		fclose($errstr);

        if ($detailed_feedback) {
            return $data; 
        }
        
        return !empty($errData) ? false : true;
    } 
    public function disconnect() { 
        $this->exec('echo "EXITING" && exit;'); 
        $this->connection = null; 
    } 
    public function __destruct() { 
        $this->disconnect(); 
    } 
} 