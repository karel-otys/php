<?php

/**
 * Jyxo PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Rpc\Xml;

/**
 * Class for creating a XML-RPC server.
 *
 * @category Jyxo
 * @package Jyxo\Rpc
 * @subpackage Xml
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Server extends \Jyxo\Rpc\Server
{
	/**
	 * Server instance.
	 *
	 * @var resource
	 */
	private $server = null;

	/**
	 * Creates a class instance.
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->server = xmlrpc_server_create();
	}

	/**
	 * Destroys a class instance.
	 */
	public function __destruct()
	{
		parent::__destruct();
		if (is_resource($this->server)) {
			xmlrpc_server_destroy($this->server);
		}
	}

	/**
	 * Actually registers a function to a server method.
	 *
	 * @param string $func Function definition
	 */
	protected function register($func)
	{
		xmlrpc_server_register_method($this->server, $func, array($this, 'call'));
	}

	/**
	 * Processes a request and sends a XML-RPC response.
	 */
	public function process()
	{
		$options = array(
			'output_type' => 'xml',
			'verbosity' => 'pretty',
			'escaping' => array('markup'),
			'version' => 'xmlrpc',
			'encoding' => 'utf-8'
		);

		$response = xmlrpc_server_call_method($this->server, file_get_contents('php://input'), null, $options);
		header('Content-Type: text/xml; charset="utf-8"');
		echo $response;
	}
	
	static function fixXmlrpcArgs($array) {
		if (!is_array($array))
			return $array;
		
		
		$new = array();
		$range = range(0, count($array) - 1);
		if (array_keys($array) === $range) { # just array
			foreach ($array as $val)
				$new[] = self::fixXmlrpcArgs($val);
	
		} else { # structure
			foreach ($array as $key => $val)
				$new[$key. chr(0x00)] = self::fixXmlrpcArgs($val);
	
		}
	
		return $new;
	}
	
	protected function call($method, $params) {
		$out=parent::call($method, $params);
		$out=self::fixXmlrpcArgs($out);
		return $out;
	}
}
