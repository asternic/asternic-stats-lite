<?php
/*
   Copyright 2007, 2021 Nicolás Gudiño

   This file is part of Asternic Call Center Stats.

   Asternic Call Center Stats is free software: you can redistribute it
   and/or modify it under the terms of the GNU General Public License as
   published by the Free Software Foundation, either version 3 of the
   License, or (at your option) any later version.

   Asternic Call Center Stats is distributed in the hope that it will be
   useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Asternic Call Center Stats.  If not, see
   <http://www.gnu.org/licenses/>.
*/

// AMI Class based on phpagi-asmanager.php by
// Matthew Asham <matthewa@bcwireless.net>

class AsteriskManager
{
    var $config;
    var $socket = NULL;
    var $server;
    var $port;
    var $event_handlers;
    var $waits_for_response = 1;
    
    function __construct($config=NULL, $optconfig=array())
    {
        // add default values to config for uninitialized values
        if(!isset($this->config['asmanager']['server'])) $this->config['asmanager']['server'] = 'localhost';
        if(!isset($this->config['asmanager']['port'])) $this->config['asmanager']['port'] = 5038;
        if(!isset($this->config['asmanager']['username'])) $this->config['asmanager']['username'] = 'admin';
        if(!isset($this->config['asmanager']['secret'])) $this->config['asmanager']['secret'] = 'amp111';
    }
    
    function send_request($action, $parameters=array(), $wait_response=1) {
        $req = "Action: $action\r\n";
        foreach($parameters as $var=>$val) {
            $req .= "$var: $val\r\n";
        }
        $req .= "\r\n";
        fwrite($this->socket, $req);
        if($wait_response==1) {
            return $this->wait_response();
        } else {
            return true;
        }
    }

    function wait_response($allow_timeout=false) {

        $timeout = false;

        do {
            $type = NULL;
            $parameters = array();

            if (feof($this->socket)) { return false; }

            $buffer = trim(fgets($this->socket, 4096));

            while($buffer != '') {

                $a = strpos($buffer, ':');

                if($a) {
                    if(!count($parameters)) {
                        // first line in a response?
                        $type = strtolower(substr($buffer, 0, $a));

                        if(substr($buffer, $a + 2) == 'Follows') {
                            // A follows response means there is a multiline field that follows.
                            $parameters['data'] = '';
                            $buff = fgets($this->socket, 4096);

                            // fputs($fp, "2 fgets $buff\n");

                            while(substr($buff, 0, 6) != '--END ') {
                                $parameters['data'] .= $buff;
                                $buff = fgets($this->socket, 4096);
                            }
                        }
                    }

                    // store parameter in $parameters
                    if(substr($buffer, 0, $a)=="Output") {
                        $parameters[substr($buffer, 0, $a)] .= substr($buffer, $a + 2)."\n";
                    } else {
                        $parameters[substr($buffer, 0, $a)] = substr($buffer, $a + 2);
                    }

                }
                $buffer = trim(fgets($this->socket, 4096));
            }

            if(!isset($parameters['data'])) {
                if(isset($parameters['Output'])) {
                    $parameters['data']=$parameters['Output'];
                }
            }
            // process response
            switch($type) {
                case '': // timeout occured
                    $timeout = $allow_timeout;
                    break;
                case 'event':
                    $this->process_event($parameters);
                    break;
                case 'response':
                    break;
                default:
                    $this->log('Unhandled response packet from Manager: ' . print_r($parameters, true));
                    break;
            }
        } while($type != 'response' && !$timeout);


        return $parameters;
    }

    function connect($server=NULL, $username=NULL, $secret=NULL, $events='off')
    {
        // use config if not specified
        if(is_null($server)) $server = $this->config['asmanager']['server'];
        if(is_null($username)) $username = $this->config['asmanager']['username'];
        if(is_null($secret)) $secret = $this->config['asmanager']['secret'];

        // get port from server if specified
        if(strpos($server, ':') !== false) {
            $c = explode(':', $server);
            $this->server = $c[0];
            $this->port = $c[1];
        } else {
            $this->server = $server;
            $this->port = $this->config['asmanager']['port'];
        }
        
        $errno = $errstr = NULL;
        $this->socket = @fsockopen($this->server, $this->port, $errno, $errstr);
        if(!$this->socket)
        {
            $this->log("Unable to connect to manager {$this->server}:{$this->port} ($errno): $errstr");
            return false;
        }
        
        // read the header
        $str = fgets($this->socket);
        if($str == false) {
            // a problem.
            $this->log("Asterisk Manager header not received.");
            return false;
        }
        // If its an old asterisk, we do not receive resopnse from UserEvents so we have to signal this somehow
        if(preg_match("/Asterisk Call Manager\/1.0/",$str)) {
           $this->waits_for_response = 0;
        }
        // login
        $res = $this->send_request('login', array('Username'=>$username, 'Secret'=>$secret, 'Events'=>$events));

        if(false) {
            $this->log("Failed to login.");
            $this->disconnect();
            return false;
        }

        if($res['Response']=='Error') {
            return false;
        }

        return true;
    }
    
    function disconnect() {
        $this->logoff();
        fclose($this->socket);
    }
    
    function Command($command) {    
        return $this->send_request('Command', array('Command'=>$command));
    }
    
    function Events($eventmask) {
        return $this->send_request('Events', array('EventMask'=>$eventmask));
    }

    function ExtensionState($exten, $context, $actionid) {
        return $this->send_request('ExtensionState', array('Exten'=>$exten, 'Context'=>$context, 'ActionID'=>$actionid));
    }

    function GetVar($channel, $variable) {
        return $this->send_request('GetVar', array('Channel'=>$channel, 'Variable'=>$variable));
    }
    
    function Ping() {
        return $this->send_request('Ping');
    }

    function WaitEvent($timeout) {
        return $this->send_request('WaitEvent',array('Timeout'=>$timeout));
    }

    function Hangup($channel) {
        return $this->send_request('Hangup', array('Channel'=>$channel));
    }
    
    function IAXPeers() {
        return $this->send_request('IAXPeers');
    }

    function Logoff() {
        return $this->send_request('Logoff');
    }
    
    function Monitor($channel, $file) {
        return $this->send_request('Monitor', array('Channel'=>$channel, 'File'=>$file));
    }

    function UserEvent($event, $headers) {
        $eventheader['UserEvent']=$event;
        $finalheaders = array_merge($eventheader,$headers);
        return $this->send_request('UserEvent', $finalheaders, $this->waits_for_response);
    }

    function Queues() {
        return $this->send_request('Queues');
    }

    function QueueStatus() {
        return $this->send_request('QueueStatus');
    }

    function Reload() {
        return $this->send_request('Reload');
    }

    function Originate($channel, $exten, $context, $priority, $timeout, $callerid, $variable, $account, $application, $data)
    {
        $parameters = array();
        if($channel) $parameters['Channel'] = $channel;
        if($exten) $parameters['Exten'] = $exten;
        if($context) $parameters['Context'] = $context;
        if($priority) $parameters['Priority'] = $priority;
        if($timeout) $parameters['Timeout'] = $timeout;
        if($callerid) $parameters['CallerID'] = $callerid;
        if($variable) $parameters['Variable'] = $variable;
        if($account) $parameters['Account'] = $account;
        if($application) $parameters['Application'] = $application;
        if($data) $parameters['Data'] = $data;
        return $this->send_request('Originate', $parameters);
    }

    function Status($channel) {
        return $this->send_request('Status', array('Channel'=>$channel));
    }

    function database_show($family='') {
        $r = $this->command("database show $family");

        $data = explode("\n",$r["data"]);
        $db   = array();

        // Remove the Privilege => Command initial entry that comes from the heading
        //
        if(preg_match("/^Privilege/",$data[0])) {
            array_shift($data);
        }
        foreach ($data as $line) {
            $temp = explode(":",$line,2);
            if (trim($temp[0]) != '' && count($temp) == 2) {
                $temp[1] = isset($temp[1])?$temp[1]:null;
                $db[ trim($temp[0]) ] = trim($temp[1]);
            }
        }
        return $db;
    }

    function database_get($family, $key) {
        $r = $this->command("database get ".str_replace(" ","/",$family)." ".str_replace(" ","/",$key));
        $data = strpos($r["data"],"Value:");
        if ($data !== false) {
            return trim(substr($r["data"],6+$data));
        } else {
            return false;
        }
    }

    function database_put($family, $key, $value) {
        $command = "database put ".str_replace(" ","/",$family)." ".str_replace(" ","/",$key)." \"".$value."\"";
        $r = $this->command($command);
        return true;
    }

    function database_del($family, $key) {
        $command = "database del ".str_replace(" ","/",$family)." ".str_replace(" ","/",$key);
        $r = $this->command($command);
        return true;
    }

    function log($message, $level=1) {
        error_log(date('r') . ' - ' . $message);
    }

    /**
    * Add event handler
    *
    * Known Events include ( http://www.voip-info.org/wiki-asterisk+manager+events )
    *   Link - Fired when two voice channels are linked together and voice data exchange commences.
    *   Unlink - Fired when a link between two voice channels is discontinued, for example, just before call completion.
    *   Newexten -
    *   Hangup -
    *   Newchannel -
    *   Newstate -
    *   Reload - Fired when the "RELOAD" console command is executed.
    *   Shutdown -
    *   ExtensionStatus -
    *   Rename -
    *   Newcallerid -
    *   Alarm -
    *   AlarmClear -
    *   Agentcallbacklogoff -
    *   Agentcallbacklogin -
    *   Agentlogoff -
    *   MeetmeJoin -
    *   MessageWaiting -
    *   join -
    *   leave -
    *   AgentCalled -
    *   ParkedCall -
    *   Cdr -
    *   ParkedCallsComplete -
    *   QueueParams -
    *   QueueMember -
    *   QueueStatusEnd -
    *   Status -
    *   StatusComplete -
    *   ZapShowChannels -
    *   ZapShowChannelsComplete -
    *
    * @param string $event type or * for default handler
    * @param string $callback function
    * @return boolean sucess
    */
    function add_event_handler($event, $callback) {
        $event = strtolower($event);
        if(isset($this->event_handlers[$event]))
        {
            $this->log("$event handler is already defined, not over-writing.");
            return false;
        }
        $this->event_handlers[$event] = $callback;
        return true;
    }

    /**
    * Process event
    *
    * @access private
    * @param array $parameters
    * @return mixed result of event handler or false if no handler was found
    */
    function process_event($parameters)
    {
        $ret = false;
        $e = strtolower($parameters['Event']);
        $this->log("Got event.. $e");

        $handler = '';
        if(isset($this->event_handlers[$e])) $handler = $this->event_handlers[$e];
        elseif(isset($this->event_handlers['*'])) $handler = $this->event_handlers['*'];

        if(function_exists($handler))
        {
            $this->log("Execute handler $handler");
            $ret = $handler($e, $parameters, $this->server, $this->port);
        }
        return $ret;
    }

}
?>
