#!/usr/bin/php
<?php

include 'usb_device_list.phps';

// USBデバイスリストを作成

$usb	= new UsbDeviceList();

// 現在繋がっているUSBデバイスを列挙する
echo "-- デバイスを列挙する --\n";
$devices	= $usb->listDevice();
foreach($devices as $dev){
	echo "* デバイス名称: ".$dev->getDeviceName()."\n";
	echo "* 接続されている場所: ".$dev->getBusportId()."\n";
	echo "* デバイス番号: ".$dev->getDeviceNum()."\n";
	echo "* デバイスID: ".$dev->getDeviceId()."\n";
	echo "*\n";
}

// デバイスID 0c45:7401 のデバイスを列挙する
echo "-- 0c45:7401 のデバイスを列挙する --\n";
$cond	= array(
	UsbDevice::DEVICE_ID	=> '0c45:7401',
);
$devices	= $usb->enumDevice($cond);
foreach($devices as $dev){
	echo "接続されている場所: ".$dev->getBusportId()."\n";
	echo "デバイス番号: ".$dev->getDeviceNum()."\n";
	echo "--\n";
}

// デバイスID 056e:7007 と同じハブに繋がっている 0c45:7401 の最初の１つを取得する
echo "-- 056e:7007 と同じハブに繋がっている 0c45:7401 の最初の１つを取得する --\n";
$cond	= array(
	UsbDevice::DEVICE_ID	=> '056e:7007',
);
$dev1	= $usb->searchDevice($cond);

if( !$dev1 ){
	echo "056e:7007 のデバイスはありません。\n";
} else {
	$cond	= array(
		UsbDevice::BUS_PORT_ID	=> $dev->getBusportId(),
	);
	$dev2	= $usb->searchDevice($cond);

	if( !$dev2 ){
		echo "056e:7007 と同じハブに繋がっている 0c45:7401 はありません。\n";
	} else {
		echo "デバイス番号: ".$dev2->getDeviceNum()."\n";
		echo "--\n";
	}
}

?>