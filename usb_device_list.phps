<?php

/**
 * USBデバイスリスト
 *
 * lsusbコマンドを使用して、各デバイスの接続ポート等を取得する。
 *
 * @author usoinfo http://blog.usoinfo.info/
 * @version 1.0
 *
 */

define(PATH_LSUSB_COMMAND,	'/usr/bin/lsusb');	// lsusbコマンドのパス
define(LSUSB_TREE_INDENT,	4);	// lsusb -t でカスケードされる行の1段分のスペース数

abstract class _name_value_object
{
	protected	$data;

	public function __construct()
	{
		$this->data	= array();
		return $this;
	}

	public function set($name, $value)
	{
		$this->data[$name]	= $value;
		return $this;
	}
	
	public function get($name)
	{
		return $this->data[$name];
	}

	public function has($name)
	{
		return isset($this->data[$name]);
	}

	public function remove($name)
	{
		unset($this->data[$name]);
		return $this;
	}
}

/**
 * USBデバイスクラス
 *
 * UsbDeviceクラス: USBデバイスの情報を保持する
 *  接続されている場所は、可変長の文字列(BUS_PORT_ID)として表される。
 *  バス1から、USBハブ1->USBハブ2->USBハブ3->デバイス と繋がっている場合の例
 *  RB1P1P2P3
 *  || | | |
 *  || | | +- ポート
 *  || | +- ポート
 *  || +- ポート
 *  |+- バス
 *  +- ルート
 *  
 * getDeviceNum() デバイス番号を取得
 * getDeviceName() デバイス名称を取得
 * getBusportId() 接続されている場所を取得
 * getDeviceId() デバイスID(ベンダーID:プロダクトID)を取得
 *
 * @author usoinfo http://blog.usoinfo.info/
 * @version 1.0
 *
 */

class UsbDevice extends _name_value_object
{
	const	BUS_PORT_ID	= 'bpid';
	const	BUS_NUM		= 'bnum';
	const	DEVICE_NUM	= 'dnum';
	const	DEVICE_ID	= 'deid';
	const	CLASS_NAME	= 'cnam';
	const	DRIVER_NAME	= 'vnam';
	const	SPEED		= 'sped';
	const	DEVICE_NAME	= 'dnam';
	const	IF_NUM		= 'inum';

	public function __construct()
	{
		parent::__construct();
	}
	
	public function toString($bShort = false)
	{
		$ret	= '';
		foreach($this->data as $i => $v){
			if( $bShort && !in_array($i,array(self::BUS_PORT_ID,self::DEVICE_NUM,self::DEVICE_ID,self::DEVICE_NAME)) ) continue;
			$ret	.= "$i:$v ";
		}
		return $ret;
	}
	
	public function getDeviceNum()
	{
		return $this->get(self::DEVICE_NUM);
	}

	public function getDeviceName()
	{
		return $this->get(self::DEVICE_NAME);
	}
	
	public function getBusportId()
	{
		return $this->get(self::BUS_PORT_ID);
	}
	
	public function getDeviceId()
	{
		return $this->get(self::DEVICE_ID);
	}
}

/**
 * USBデバイスリストクラス
 *
 * UsbDeviceListクラス: lsusbコマンドの出力を解析してデバイスを列挙する
 * 
 * refresh() 情報を更新する
 * listDevice() 全デバイスを列挙する
 * searchDevice($cond) 条件に適合するデバイスで見つかった最初の一つを返す
 * enumDevice($cond) 条件に適合するデバイスを列挙する
 *
 * @author usoinfo http://blog.usoinfo.info/
 * @version 1.0
 *
 */

class UsbDeviceList
{
	const	STATUS_NONE		= 0;
	const	STATUS_LISTED	= 1;

	private	$lsusb		= PATH_LSUSB_COMMAND;
	private	$status		= STATUS_NONE;
	private	$hubstack	= array();
	private	$devices	= array();

	public function __construct($lsusb = false)
	{
		if( $lsusb ) $this->lsusb	= $lsusb;
		$this->refresh();
	}

	public function toString($s = "\n")
	{
		$ret	= "";
		foreach($this->devices as $d){
			$ret	.= $d->toString(true).$s;
		}
		return $ret;
	}

	private static function get_tree_param($line)
	{
		$line	= trim($line);
		if( preg_match("/Bus ([0-9]+?)\./", $line, $match) ){
			$ret['bus']	= intval($match[1]);
		}
		if( preg_match("/Port ([0-9]+?):/", $line, $match) ){
			$ret['port']	= intval($match[1]);
		}
		if( preg_match("/Dev ([0-9]+?),/", $line, $match) ){
			$ret['dev']	= intval($match[1]);
		}
		if( preg_match("/If ([0-9]+?),/", $line, $match) ){
			$ret['if']	= intval($match[1]);
		}
		if( preg_match("/Class=(.+?),/", $line, $match) ){
			$ret['class']	= $match[1];
		}
		if( preg_match("/Driver=(.+?),/", $line, $match) ){
			$ret['driver']	= $match[1];
		}
		if( preg_match("/, ([0-9.]+M)$/", $line, $match) ){
			$ret['speed']	= $match[1];
		}
		return $ret;
	}

	private static function get_list_param($line)
	{
		$line	= trim($line);
		if( preg_match("/Bus ([0-9]+)/", $line, $match) ){
			$ret['bus']	= intval($match[1]);
		}
		if( preg_match("/Device ([0-9]+):/", $line, $match) ){
			$ret['dev']	= intval($match[1]);
		}
		if( preg_match("/ID ([0-9a-z]{4}:[0-9a-z]{4}) (.+)$/", $line, $match) ){
			$ret['id']		= $match[1];
			$ret['name']	= $match[2];
		}

		return $ret;
	}

	private static function get_device_by_busandnum(&$in, $bus, $num)
	{
		foreach($in as $v){
			if( $v['bus'] == $bus && $v['dev'] == $num ) return $v;
		}
		return false;
	}

	private static function exec_cmd($cmd)
	{
		ob_start();
		passthru($cmd);
		return ob_get_clean();
	}

	private function hubstack2id($pos)
	{
		$ret	= 'R';
		for($i=0;$i<count($this->hubstack)-$pos;$i++){
			$ret	.= $this->hubstack[$i];
		}
		return $ret;
	}

	public function refresh()
	{
		$tree	= self::exec_cmd($this->lsusb.' -t');
		$node	= self::exec_cmd($this->lsusb);
		
		$this->hubstack	= array();
		$line	= explode("\n", $tree);
		$depth	= 0;
		$currentbus	= 0;
		
		for($i=0;$i<count($line);$i++){
			$ret	= self::get_tree_param($line[$i]);
			$dev	= new UsbDevice();
			$dev->set( UsbDevice::CLASS_NAME, $ret['class'] );
			$dev->set( UsbDevice::DRIVER_NAME, $ret['driver'] );
			$dev->set( UsbDevice::SPEED, $ret['speed'] );
			$dev->set( UsbDevice::DEVICE_NUM, $ret['dev'] );

			if( strpos($line[$i],"/:") === 0 ){
				// roothub
				$dev->set( UsbDevice::BUS_PORT_ID, $this->hubstack2id(1) );
				$dev->set( UsbDevice::BUS_NUM, $ret['bus'] );
				
				$this->devices[]	= $dev;
				$this->hubstack	= array('B'.$ret['bus']);
				$currentbus	= $ret['bus'];
				$depth	= 0;
				continue;
			}

			if( ($pos = strpos($line[$i],"|__ ")) > 0 ){
				while( $pos < LSUSB_TREE_INDENT * ($depth+1) ){
					array_pop($this->hubstack);
					$depth--;
				}
				$this->hubstack[]	= 'P'.$ret['port'];
				$depth++;

				$dev->set( UsbDevice::BUS_NUM, $currentbus );
				$dev->set( UsbDevice::IF_NUM, $ret['if'] );
				$dev->set( UsbDevice::BUS_PORT_ID, $this->hubstack2id(1) );
				if( !$this->searchDevice(array(UsbDevice::DEVICE_NUM => $ret['dev'])) ){
					$this->devices[]	= $dev;
				}
				continue;
			}
		}

		$line	= explode("\n", $node);
		$nodes	= array();
		for($i=0;$i<count($line);$i++){
			$nodes[]	= self::get_list_param($line[$i]);
		}
		
		for($i=0;$i<count($this->devices);$i++){
			$ref	= self::get_device_by_busandnum($nodes, $this->devices[$i]->get(UsbDevice::BUS_NUM), $this->devices[$i]->get(UsbDevice::DEVICE_NUM) );
			if( $ref ){
				$this->devices[$i]->set(UsbDevice::DEVICE_ID, $ref['id']);
				$this->devices[$i]->set(UsbDevice::DEVICE_NAME, $ref['name']);
			}
		}
		
		$this->status	= self::STATUS_LISTED;
		$hubstack	= array();
		return true;
	}

	public function listDevice()
	{
		return $this->devices;
	}
	
	public function searchDevice($cond)
	{
		$ret	= $this->enumDevice($cond);
		return count($ret) > 0 ? $ret[0] : false;
	}

	public function enumDevice($cond)
	{
		$ret		= array();
		$appended	= array();
		for($i=0;$i<count($this->devices);$i++){
			$bmatch	= true;
			foreach($cond as $name => $value){
				if( $this->devices[$i]->get($name) != $value ){
					$bmatch	= false;
					break;
				}
			}
			if( $bmatch && !$appended[ $this->devices[$i]->getDeviceNum() ] ){
				$ret[]	= $this->devices[$i];
				$appended[ $this->devices[$i]->getDeviceNum() ]	= true;
			}
		}
		return $ret;
	}

}

?>