# usb_device_list
lsusbコマンドを使用して各デバイスの接続ポート等を取得する

# 動作環境など

[使用できる環境] PHP Cliとlsusbコマンドが使用できるlinux  
動作サンプルは sample.php を参照。  

# 設定箇所

## usb_device_list.phps
    define(PATH_LSUSB_COMMAND,	'/usr/bin/lsusb');	// lsusbコマンドのパス
    define(LSUSB_TREE_INDENT,	4);	// lsusb -t でカスケードされる行の1段分のスペース数

# 実行例
## USBの接続状況
    $ lsusb -t
    /:  Bus 05.Port 1: Dev 1, Class=root_hub, Driver=uhci_hcd/2p, 12M
    /:  Bus 04.Port 1: Dev 1, Class=root_hub, Driver=uhci_hcd/2p, 12M
    /:  Bus 03.Port 1: Dev 1, Class=root_hub, Driver=uhci_hcd/2p, 12M
        |__ Port 1: Dev 2, If 0, Class=Hub, Driver=hub/4p, 12M
            |__ Port 1: Dev 16, If 0, Class=Human Interface Device, Driver=, 1.5M
            |__ Port 1: Dev 16, If 1, Class=Human Interface Device, Driver=, 1.5M
            |__ Port 4: Dev 4, If 0, Class=Video, Driver=uvcvideo, 12M
            |__ Port 4: Dev 4, If 1, Class=Video, Driver=uvcvideo, 12M
            |__ Port 4: Dev 4, If 2, Class=Audio, Driver=snd-usb-audio, 12M
            |__ Port 4: Dev 4, If 3, Class=Audio, Driver=snd-usb-audio, 12M
    /:  Bus 02.Port 1: Dev 1, Class=root_hub, Driver=uhci_hcd/2p, 12M
    /:  Bus 01.Port 1: Dev 1, Class=root_hub, Driver=ehci-pci/8p, 480M
        |__ Port 2: Dev 2, If 0, Class=Hub, Driver=hub/4p, 480M
            |__ Port 1: Dev 20, If 0, Class=Human Interface Device, Driver=, 1.5M
            |__ Port 1: Dev 20, If 1, Class=Human Interface Device, Driver=, 1.5M
            |__ Port 3: Dev 8, If 0, Class=Video, Driver=uvcvideo, 480M
            |__ Port 3: Dev 8, If 1, Class=Video, Driver=uvcvideo, 480M
        |__ Port 4: Dev 4, If 0, Class=Vendor Specific Class, Driver=rt2800usb, 480M
        |__ Port 5: Dev 5, If 0, Class=Mass Storage, Driver=usb-storage, 480M
        |__ Port 8: Dev 6, If 0, Class=Video, Driver=uvcvideo, 480M
        |__ Port 8: Dev 6, If 1, Class=Video, Driver=uvcvideo, 480M
    $ lsusb
    Bus 001 Device 006: ID eb1a:2761 eMPIA Technology, Inc. EeePC 701 integrated Webcam
    Bus 001 Device 005: ID 0951:1606 Kingston Technology Eee PC 701 SD Card Reader [ENE UB6225]
    Bus 001 Device 004: ID 07aa:0042 Corega K.K. CG-WLUSB300GNM
    Bus 001 Device 008: ID 056e:7007 Elecom Co., Ltd 
    Bus 001 Device 020: ID 0c45:7401 Microdia TEMPer Temperature Sensor
    Bus 001 Device 002: ID 05e3:0608 Genesys Logic, Inc. Hub
    Bus 001 Device 001: ID 1d6b:0002 Linux Foundation 2.0 root hub
    Bus 005 Device 001: ID 1d6b:0001 Linux Foundation 1.1 root hub
    Bus 004 Device 001: ID 1d6b:0001 Linux Foundation 1.1 root hub
    Bus 003 Device 004: ID 046d:08ca Logitech, Inc. Mic (Fusion)
    Bus 003 Device 016: ID 0c45:7401 Microdia TEMPer Temperature Sensor
    Bus 003 Device 002: ID 1a40:0101 Terminus Technology Inc. Hub
    Bus 003 Device 001: ID 1d6b:0001 Linux Foundation 1.1 root hub
    Bus 002 Device 001: ID 1d6b:0001 Linux Foundation 1.1 root hub
こんな具合だとする。

## サンプル実行結果
    $ php sample.php
    -- デバイスを列挙する --
    * デバイス名称: Linux Foundation 1.1 root hub
    * 接続されている場所: R
    * デバイス番号: 1
    * デバイスID: 1d6b:0001
    *
    * デバイス名称: Linux Foundation 1.1 root hub
    * 接続されている場所: R
    * デバイス番号: 1
    * デバイスID: 1d6b:0001
    *
    * デバイス名称: Linux Foundation 1.1 root hub
    * 接続されている場所: R
    * デバイス番号: 1
    * デバイスID: 1d6b:0001
    *
    * デバイス名称: Terminus Technology Inc. Hub
    * 接続されている場所: RB3
    * デバイス番号: 2
    * デバイスID: 1a40:0101
    *
    * デバイス名称: Microdia TEMPer Temperature Sensor
    * 接続されている場所: RB3P1
    * デバイス番号: 17
    * デバイスID: 0c45:7401
    *
    * デバイス名称: Logitech, Inc. Mic (Fusion)
    * 接続されている場所: RB3P1
    * デバイス番号: 4
    * デバイスID: 046d:08ca
    *
    * デバイス名称: Linux Foundation 1.1 root hub
    * 接続されている場所: RB3P1
    * デバイス番号: 1
    * デバイスID: 1d6b:0001
    *
    * デバイス名称: Linux Foundation 2.0 root hub
    * 接続されている場所: R
    * デバイス番号: 1
    * デバイスID: 1d6b:0002
    *
    * デバイス名称: Genesys Logic, Inc. Hub
    * 接続されている場所: RB1
    * デバイス番号: 2
    * デバイスID: 05e3:0608
    *
    * デバイス名称: Microdia TEMPer Temperature Sensor
    * 接続されている場所: RB1P2
    * デバイス番号: 21
    * デバイスID: 0c45:7401
    *
    * デバイス名称: Elecom Co., Ltd
    * 接続されている場所: RB1P2
    * デバイス番号: 8
    * デバイスID: 056e:7007
    *
    * デバイス名称: Corega K.K. CG-WLUSB300GNM
    * 接続されている場所: RB1
    * デバイス番号: 4
    * デバイスID: 07aa:0042
    *
    * デバイス名称: Kingston Technology Eee PC 701 SD Card Reader [ENE UB6225]
    * 接続されている場所: RB1
    * デバイス番号: 5
    * デバイスID: 0951:1606
    *
    * デバイス名称: eMPIA Technology, Inc. EeePC 701 integrated Webcam
    * 接続されている場所: RB1
    * デバイス番号: 6
    * デバイスID: eb1a:2761
    *
    -- 0c45:7401 のデバイスを列挙する --
    接続されている場所: RB3P1
    デバイス番号: 17
    --
    接続されている場所: RB1P2
    デバイス番号: 21
    --
    -- 056e:7007 と同じハブに繋がっている 0c45:7401 の最初の１つを取得する --
    デバイス番号: 21
    --
