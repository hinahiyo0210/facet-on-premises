<?php 

class ConfigService {
	

	// ロゴ画像を取得。
	public static function getAccessViewLogo(array $device) {
		
		$ret = WsApiService::accessWsApi($device, [
 			"method"=>"accessView.getLogo"
 			, "id"=>WsApiService::genId()
 			, "params"=>null
 		]);
		
		if (empty($ret["data"])) {
			throw new DeviceWsException("ロゴ画像が取得出来ませんでした。");
		}
		
		return base64_decode($ret["data"]);
	}
	

	// ロゴ画像を設定。
	public static function setAccessViewLogo(array $device, $base64png) {
		
 		$ret = WsApiService::accessWsApi($device, [
	  			"method"=>"accessView.setLogo"
	  			, "id"=>WsApiService::genId()
	  			, "params"=>null
 				, "data"=>$base64png
  			]
 		);
		
 		return $ret;
	}

	
	
	// 設定ファイルをエクスポート
	public static function exportConfig(array $device) {

		$ret = WsApiService::accessWsApi($device, [
 			"method"=>"configCentre.exportConfig"
 			, "id"=>WsApiService::genId()
 			, "params"=>null
 		]);
		
		if (empty($ret["data"])) {
			throw new DeviceWsException("デバイスからの返却値の中に、設定データが格納されていません。");
		}
		
		return base64_decode($ret["data"]);
	}
	
	
	// 設定ファイルをインポート
	public static function importConfig(array $device, $base64file) {

		$ret = WsApiService::accessWsApi($device, [
 			"method"=>"configCentre.importConfig"
 			, "id"=>WsApiService::genId()
 			, "params"=>null
 		], 
		$base64file);
		
		return $ret;
	}
	
	
	
	public static function getBasicConfigDefine() {
			
        return [
            
            // ======================================== デバイス管理：基本設定 ======================================== 
            // デバイス名(半角英数32文字まで)
            "deviceMachineName"     =>["name"    => "MachineGlobal",
                                       "convert" => function($content)        { return $content[0]["MachineName"];     },
                                       "validate"=> function(Validator $v)    { return $v->maxlength(32);              },
                                       "set"     => function(&$content, $val) { $content[0]["MachineName"] = $val;     }],
            
            // 音声ボリューム(0-100)
            "deviceAudioVolume"     =>["name"    => "AudioOut", 
                                       "convert" => function($content)        { return $content[0]["Volume"];           }, 
                                       "validate"=> function(Validator $v)    { return $v->digit(0, 100);               },
                                       "set"     => function(&$content, $val) { $content[0]["Volume"] = (int) $val;      }],

            // 画面の明るさ(0-100)
            "deviceScreenBrightness"=>["name"    => "Screen", 
                                       "convert" => function($content)        { return $content[0]["Brightness"];        }, 
                                       "validate"=> function(Validator $v)    { return $v->digit(0, 100);                },
                                       "set"     => function(&$content, $val) { $content[0]["Brightness"] = (int) $val;  }],

            // LED照明の明るさ(0-100)
            "deviceLedBrightness"   =>["name"    => "SOCConstantLamp", 
                                       "convert" => function($content)        { return $content[0]["Brightness"];        }, 
                                       "validate"=> function(Validator $v)    { return $v->digit(0, 100);                },
                                       "set"     => function(&$content, $val) { $content[0]["Brightness"] = (int) $val;  }],

            // スクリーンセーバーに入る時間(-1 ~ 86400)秒
            "deviceStandbyTime"     =>["name"    => "AccessView"       ,
                                       "convert" => function($content)        { return $content["StandbyTime"];          },
                                       "validate"=> function(Validator $v)    { return $v->digit(-1, 86400);              },
                                       "set"     => function(&$content, $val) { $content["StandbyTime"] = (int) $val;    }],

            // スタンバイに入る時間(0~86400)秒
            "deviceWorkstateTime"   =>["name"    => "AccessView"        , 
                                       "convert" => function($content)        { return $content["WorkstateTime"];        },
                                       "validate"=> function(Validator $v)    { return $v->digit(0, 86400);              },
                                       "set"     => function(&$content, $val) { $content["WorkstateTime"] = (int) $val;  }],

            // デバイス休止時のカード認証機能
            "hibernateRecogEnable"   =>["name"    => "CardEnableInDormancy",
                                        "convert" => function($content)        { return $content["CardEnable"] ? 1 : 0; },
                                        "validate"=> function(Validator $v)    { return $v->flag();                 },
                                        "set"     => function(&$content, $val) { $content["CardEnable"] = empty($val) ? false : true; }],

            // デバイス休止中時の表示メッセージ内容
            "hibernateTips"         =>["name"    => "Dormancy",
                                       "convert" => function($content)        { return $content["DormancyTip"];     },
                                       "validate"=> function(Validator $v)    { return $v->maxlength(10);           },
                                       "set"     => function(&$content, $val) { $content["DormancyTip"] = $val;     }],
            
            // ======================================== アクセス制御管理 ========================================
            // アクセスタイプ 1: 顔認識, 3: 顔認識+カード, 4: 顔認識/カード
            "accessType"            =>["name"    => "DoorGlobalTime"    , 
                                       "convert" => function($content)        { return $content[0][0]["PassTimes"][0]["AccessType"];         },
                                       "validate"=> function(Validator $v)    { return $v->inArray([1, 2, 3, 4]);                            },
                                       "set"     => function(&$content, $val) { $content[0][0]["PassTimes"][0]["AccessType"] = (int) $val;   }],

            // ======================================== ネットワーク設定 ========================================
            // ----------- TCP/IP
            // LANカード：参照のみ。eth0固定。
            "netDefaultNetCard"         =>["name"   => "Netconfig"  , 
                                           "convert"=> function($content)         { return $content["DefaultNetCard"];                                           }],
            
            // MACアドレス：参照のみ。
            "netEth0MacAddress"         =>["name"   => "Netconfig"  , 
                                           "convert"=> function($content)         { return $content["eth0"]["MACAddress"];                                       }],
                                       
            // IPv4設定：DHCPを有効にするかどうか 1 or 0
            "netEth0Ipv4DhcpEnable"     =>["name"    => "Netconfig" , 
                                           "convert" => function($content)        { return $content["eth0"]["IPv4Address"]["DHCPEnable"] ? 1 : 0;                }, 
                                           "validate"=> function(Validator $v)    { return $v->flag();                                                           },
                                           "set"     => function(&$content, $val) { $content["eth0"]["IPv4Address"]["DHCPEnable"] = empty($val) ? false : true;  }],
            
            // IPv4設定：IPアドレス
            "netEth0Ipv4IpAddress"      =>["name"    => "Netconfig" ,
                                           "convert" => function($content)        { return $content["eth0"]["IPv4Address"]["IPAddress"];                         },
                                           "validate"=> function(Validator $v)    { return $v->maxlength(15);                                                    },
                                           "set"     => function(&$content, $val) { $content["eth0"]["IPv4Address"]["IPAddress"] = $val;                         }],
            
            // IPv4設定：サブネットマスク
            "netEth0Ipv4SubnetMask"     =>["name"    => "Netconfig" , 
                                           "convert" => function($content)        { return $content["eth0"]["IPv4Address"]["SubnetMask"];                        },
                                           "validate"=> function(Validator $v)    { return $v->maxlength(15);                                                    },
                                           "set"     => function(&$content, $val) { $content["eth0"]["IPv4Address"]["SubnetMask"] = $val;                        }],
            
            // IPv4設定：デフォルトゲートウェイ
            "netEth0Ipv4DefaultGateway" =>["name"    => "Netconfig" ,
                                           "convert" => function($content)        { return $content["eth0"]["IPv4Address"]["DefaultGateway"];                    },
                                           "validate"=> function(Validator $v)    { return $v->maxlength(15);                                                    },
                                           "set"     => function(&$content, $val) { $content["eth0"]["IPv4Address"]["DefaultGateway"] = $val;                    }],

            // IPv4設定：優先DNSサーバ
            "netEth0Ipv4DnsServer1"     =>["name"    => "Netconfig" , 
                                           "convert" => function($content)        { return $content["eth0"]["IPv4Address"]["DNSServers"][0];                     },
                                           "validate"=> function(Validator $v)    { return $v->maxlength(15);                                                    },
                                           "set"     => function(&$content, $val) { $content["eth0"]["IPv4Address"]["DNSServers"][0] = $val;                     }],

            // IPv4設定：代替DNSサーバ
            "netEth0Ipv4DnsServer2"     =>["name"    => "Netconfig", 
                                           "convert" => function($content)        { return $content["eth0"]["IPv4Address"]["DNSServers"][1];                     },
                                           "validate"=> function(Validator $v)    { return $v->maxlength(15);                                                    },
                                           "set"     => function(&$content, $val) { $content["eth0"]["IPv4Address"]["DNSServers"][1] = $val;                     }],

            // IPv6設定：リンクアドレス
            "netEth0Ipv6LinkLocalAddress"=>["name"    => "Netconfig"    , 
                                            "convert" => function($content)        { return $content["eth0"]["IPv6Address"]["LinkLocalAddress"];                 },
                                            "validate"=> function(Validator $v)    { return $v->maxlength(39);                                                   },
                                            "set"     => function(&$content, $val) { $content["eth0"]["IPv6Address"]["LinkLocalAddress"] = $val;                 }],
                                            
            // IPv6設定：IPアドレス
            "netEth0Ipv6IpAddress"       =>["name"    => "Netconfig"    , 
                                            "convert" => function($content)        { return $content["eth0"]["IPv6Address"]["IPAddress"];                        },
                                            "validate"=> function(Validator $v)    { return $v->maxlength(39);                                                   },
                                            "set"     => function(&$content, $val) { $content["eth0"]["IPv6Address"]["IPAddress"] = $val;                        }],
            
            // IPv6設定：IPアドレスプレフィックス
            "netEth0Ipv6IpAddressPrefix" =>["name"    => "Netconfig"    , 
                                            "convert" => function($content)        { return $content["eth0"]["IPv6Address"]["Prefix"];                           },
                                            "validate"=> function(Validator $v)    { return $v->maxlength(39);                                                   },
                                            "set"     => function(&$content, $val) { $content["eth0"]["IPv6Address"]["Prefix"] = $val;                           }],
            
            // IPv6設定：デフォルトゲートウェイ
            "netEth0Ipv6Defaultgateway"  =>["name"    => "Netconfig"    , 
                                            "convert" => function($content)        { return $content["eth0"]["IPv6Address"]["DefaultGateway"];                   },
                                            "validate"=> function(Validator $v)    { return $v->maxlength(39);                                                   },
                                            "set"     => function(&$content, $val) { $content["eth0"]["IPv6Address"]["DefaultGateway"] = $val;                   }],

            // IPv6設定：優先DNSサーバ
            "netEth0Ipv6DnsServer1"      =>["name"    => "Netconfig"    , 
                                            "convert" => function($content)        { return $content["eth0"]["IPv6Address"]["DNSServers"][0];                    },
                                            "validate"=> function(Validator $v)    { return $v->maxlength(39);                                                   },
                                            "set"     => function(&$content, $val) { $content["eth0"]["IPv6Address"]["DNSServers"][0] = $val;                    }],

            // IPv6設定：代替DNSサーバ
            "netEth0Ipv6DnsServer2"      =>["name"    => "Netconfig"    , 
                                            "convert" => function($content)        { return $content["eth0"]["IPv6Address"]["DNSServers"][1];                    },
                                            "validate"=> function(Validator $v)    { return $v->maxlength(39);                                                   },
                                            "set"     => function(&$content, $val) { $content["eth0"]["IPv6Address"]["DNSServers"][1] = $val;                    }],
            
            // --------- ポート
            // HTTPポート
            "portHttp"      =>["name"     => "Web"      , 
                               "convert"  => function($content)         { return $content["Port"];                                                   }, 
                               "validate" => function(Validator $v)     { return $v->digit(0, 65535);                                                },
                               "set"      => function(&$content, $val)  { $content["Port"] = (int) $val;                                             }],
                               
            // RTSPポート
            "portRtsp"      =>["name"     => "RTSP"     , 
                               "convert"  => function($content)         { return $content["Port"];                                                   }, 
                               "validate" => function(Validator $v)     { return $v->digit(0, 65535);                                                },
                               "set"      => function(&$content, $val)  { $content["Port"] = (int) $val;                                             }],

            // サーバポート
            "portServer"    =>["name"     => "NetService"   , 
                               "convert"  => function($content)         { return $content["Port"];                                                   },
                               "validate" => function(Validator $v)     { return $v->digit(0, 65535);                                                },
                               "set"      => function(&$content, $val)  { $content["Port"] = (int) $val;                                             }],    
            
            
            // ======================================== デバイス管理：高機能設定：情報表示のカスタム ========================================
            // ---------------- シーン1：デバイスの前に人がいる場合
            
            // 会社名などのカスタム表示。12文字以内。 
            "dispInfo"      =>["name"     => "MachineGlobal" , 
                               "convert"  => function($content)         { return $content[0]["Address"];                                }, 
                               "validate" => function(Validator $v)     { return $v->maxlength(12);                                     },
                               "set"      => function(&$content, $val)  { $content[0]["Address"] = $val;                                }], 
        
            // ---------------- シーン2：デバイスの前に人がいない場合
            // 認識された人物の氏名の表示を行うかどうか 1 or 0
            "dispShowName"  =>["name"     => "AccessView"  , 
                               "convert"  => function($content)         { return $content["ShowName"]  ? 1 : 0;                          },
                               "validate" => function(Validator $v)     { return $v->flag();                                             },
                               "set"      => function(&$content, $val)  { $content["ShowName"] = empty($val) ? false : true  ;          }], 

            // 認識された人物のIDの表示を行うかどうか 1 or 0
            "dispShowID"    =>["name"     => "AccessView"  , 
                               "convert"  => function($content)         { return $content["ShowID"]  ? 1 : 0;                            },
                               "validate" => function(Validator $v)     { return $v->flag();                                             },
                               "set"      => function(&$content, $val)  { $content["ShowID"] = empty($val) ? false : true  ;          }], 
                               
                               
            // 認識された人物の写真の表示を行うかどうか 1 or 0
            "dispShowPhoto" =>["name"     => "AccessView"   , 
                               "convert"  => function($content)         { return $content["ShowPhoto"] ? 1 : 0;                         }, 
                               "validate" => function(Validator $v)     { return $v->flag();                                            },
                               "set"      => function(&$content, $val)  { $content["ShowPhoto"] = empty($val) ? false : true  ;         }], 
                
            // ---------------- パネル情報表示
            // デバイスのIPアドレスの表示を行うかどうか 1 or 0
            "dispShowIp"         =>["name"     => "AccessView"  , 
                                    "convert"  => function($content)         { return $content["ExtraInfo"]["ShowIP"]  ? 1 : 0;                         },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                    "set"      => function(&$content, $val)  { $content["ExtraInfo"]["ShowIP"] = empty($val) ? false : true  ;          }], 
            
            // デバイスのシリアルナンバーの表示を行うかどうか 1 or 0
            "dispShowSerailNo"   =>["name"     => "AccessView"  , 
                                    "convert"  => function($content)         { return $content["ExtraInfo"]["ShowSerailNo"]  ? 1 : 0;                   }, 
                                    "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                    "set"      => function(&$content, $val)  { $content["ExtraInfo"]["ShowSerailNo"] = empty($val) ? false : true  ;    }], 
            
            // デバイスのバージョン情報の表示を行うかどうか 1 or 0
            "dispShowVersion"    =>["name"     => "AccessView"  , 
                                    "convert"  => function($content)         { return $content["ExtraInfo"]["ShowVersion"]  ? 1 : 0;                    },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                    "set"      => function(&$content, $val)  { $content["ExtraInfo"]["ShowVersion"] = empty($val) ? false : true  ;     }], 
            
            // デバイスの登録人数の表示を行うかどうか 1 or 0
            "dispShowPersonInfo" =>["name"     => "AccessView"  , 
                                    "convert"  => function($content)         { return $content["ExtraInfo"]["ShowPersonInfo"]  ? 1 : 0;                 },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                    "set"      => function(&$content, $val)  { $content["ExtraInfo"]["ShowPersonInfo"] = empty($val) ? false : true  ;  }], 
            
            // オフライン人数の表示を行うかどうか   1 or 0
            "dispShowOfflineData"=>["name"     =>"AccessView"  , 
                                    "convert"  => function($content)         { return $content["ExtraInfo"]["ShowOfflineData"]  ? 1 : 0;                },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                    "set"      => function(&$content, $val)  { $content["ExtraInfo"]["ShowOfflineData"] = empty($val) ? false : true  ; }], 
            
            // ======================================== デバイス管理：高機能設定：通知表示設定（顔認証） ========================================
            // ---------------- 認証成功時
            // 認証成功時に通知表示するかどうか 1 or 0
            "tipsEnable"        =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["Enable"] ? 1 : 0;                                    },
                                   "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                   "set"      => function(&$content, $val)  { $content["Enable"] = empty($val) ? false : true  ;                    }], 
            
            // 認証成功時の通知表示 Welcome(登録者), RecognitOK(認証成功), PunchOK(カード認証成功), PassOK(通行許可), Custom(カスタム文字列)
            "tipsType"          =>["name"     => "DoorOpenTips"  ,
                                   "convert"  => function($content)         { return $content["Tips"];                                                      },
                                   "validate" => function(Validator $v)     { return $v->inArray(["Welcome", "RecognitOK", "PunchOK", "PassOK", "Custom"]); },
                                   "set"      => function(&$content, $val)  { $content["Tips"] = $val;                                                      }],
                                   
            // 認証成功時の通知表示(カスタム文字列) 25文字以内
            "tipsCustom"        =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["CustomTip"];                                         },
                                   "validate" => function(Validator $v)     { return $v->maxlength(25);                                             },
                                   "set"      => function(&$content, $val)  { $content["CustomTip"] = $val;                                         }],

            // 認証成功時のテキスト背景色
            "tipsBackgroundColor" =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["BackgroundColor"];                                   },
                                   "validate" => function(Validator $v)     { return $v->inArray(["Blue", "Red", "Green"]); 						 },
                                   "set"      => function(&$content, $val)  { $content["BackgroundColor"] = $val;                                   }],

            // 認証成功時に音声再生を行うかどうか 1 or 0
            "tipsVoiceEnable"   =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["VoiceEnable"] ? 1 : 0;                               }, 
                                   "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                   "set"      => function(&$content, $val)  { $content["VoiceEnable"] = empty($val) ? false : true  ;               }],

            // 認証成功時の音声再生内容 現在利用可能は Welcome(登録者) のみ
            "tipsVoiceType"     =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["Voice"];                                             },
                                   "validate" => function(Validator $v)     { return $v->inArray(["Default", "Welcome"]);                           },
                                   "set"      => function(&$content, $val)  { $content["Voice"] = $val;                                             }],
            
            // ---------------- 認証失敗時
            // 認証失敗時に通知表示するかどうか 1 or 0
            "strangerTipsEnable" =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["Enable"] ? 1 : 0;                                   },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                   },
                                    "set"      => function(&$content, $val)  { $content["Enable"] = empty($val) ? false : true  ;                   }],

            // 認証失敗時の通知表示 AccessFailNothit(未登録者), Custom(カスタム文字列)  
            "strangerTipsType"   =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["Tips"];                                             },
                                    "validate" => function(Validator $v)     { return $v->inArray(["AccessFailNothit", "Custom"]);                  },
                                    "set"      => function(&$content, $val)  { $content["Tips"] = $val;                                             }],

            // 認証失敗時の通知表示(カスタム文字列) 25文字以内
            "strangerTipsCustom" =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["CustomTip"];                                        },
                                    "validate" => function(Validator $v)     { return $v->maxlength(25);                                            },
                                    "set"      => function(&$content, $val)  { $content["CustomTip"] = $val;                                        }],
                                    
            // 認証失敗時のテキスト背景色
            "strangerTipsBackgroundColor"=>["name"     => "StrangerTips"  , 
                                  		    "convert"  => function($content)         { return $content["BackgroundColor"];                         },
                                   		    "validate" => function(Validator $v)     { return $v->inArray(["Blue", "Red", "Green"]);				 },
                                   			"set"      => function(&$content, $val)  { $content["BackgroundColor"] = $val;                         }],
                                   
            // 認証失敗時に音声再生を行うかどうか 1 or 0
            "strangerVoiceEnable"=>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["VoiceEnable"] ? 1 : 0;                              },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                   },
                                    "set"      => function(&$content, $val)  { $content["VoiceEnable"] = empty($val) ? false : true  ;              }],

            // 認証失敗時の音声再生内容 現在利用可能は strangerVoice1(未登録者) のみ
            "strangerVoiceType"  =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["Voice"];                                            },
                                    "validate" => function(Validator $v)     { return $v->inArray(["strangerVoice1"]);                              },
                                    "set"      => function(&$content, $val)  { $content["Voice"] = $val;                                             }],
                                     
            // ======================================== デバイス管理：高機能設定：通知表示設定（カード認証） ========================================
            // ---------------- 認証成功時
            // 認証成功時に通知表示するかどうか 1 or 0
            "tipsEnableCard"        =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["CardEnable"] ? 1 : 0;                                    },
                                   "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                   "set"      => function(&$content, $val)  { $content["CardEnable"] = empty($val) ? false : true  ;                    }], 
            
            // 認証成功時の通知表示 Welcome(登録者), RecognitOK(認証成功), PunchOK(カード認証成功), PassOK(通行許可), Custom(カスタム文字列)
            "tipsTypeCard"          =>["name"     => "DoorOpenTips"  ,
                                   "convert"  => function($content)         { return $content["CardTip"];                                                      },
                                   "validate" => function(Validator $v)     { return $v->inArray(["Welcome", "RecognitOK", "PunchOK", "PassOK", "Custom"]); },
                                   "set"      => function(&$content, $val)  { $content["CardTip"] = $val;                                                      }],
                                   
            // 認証成功時の通知表示(カスタム文字列) 25文字以内
            "tipsCustomCard"        =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["CardCustomTip"];                                         },
                                   "validate" => function(Validator $v)     { return $v->maxlength(25);                                             },
                                   "set"      => function(&$content, $val)  { $content["CardCustomTip"] = $val;                                         }],

            // 認証成功時のテキスト背景色
            "tipsBackgroundColorCard" =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["CardBackgroundColor"];                                   },
                                   "validate" => function(Validator $v)     { return $v->inArray(["Blue", "Red", "Green"]); 						 },
                                   "set"      => function(&$content, $val)  { $content["CardBackgroundColor"] = $val;                                   }],

            // 認証成功時に音声再生を行うかどうか 1 or 0
            "tipsVoiceEnableCard"   =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["CardVoiceEnable"] ? 1 : 0;                               }, 
                                   "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                   "set"      => function(&$content, $val)  { $content["CardVoiceEnable"] = empty($val) ? false : true  ;               }],

            // 認証成功時の音声再生内容 現在利用可能は Welcome(登録者) のみ
            "tipsVoiceTypeCard"     =>["name"     => "DoorOpenTips"  , 
                                   "convert"  => function($content)         { return $content["CardVoiceTip"];                                             },
                                   "validate" => function(Validator $v)     { return $v->inArray(["Default", "Welcome"]);                           },
                                   "set"      => function(&$content, $val)  { $content["CardVoiceTip"] = $val;                                             }],
            
            // ---------------- 認証失敗時
            // 認証失敗時に通知表示するかどうか 1 or 0
            "strangerTipsEnableCard" =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["CardEnable"] ? 1 : 0;                                   },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                   },
                                    "set"      => function(&$content, $val)  { $content["CardEnable"] = empty($val) ? false : true  ;                   }],

            // 認証失敗時の通知表示 AccessFailNothit(未登録者), Custom(カスタム文字列)  
            "strangerTipsTypeCard"   =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["CardTip"];                                             },
                                    "validate" => function(Validator $v)     { return $v->inArray(["AccessFailNothit", "Custom"]);                  },
                                    "set"      => function(&$content, $val)  { $content["CardTip"] = $val;                                             }],

            // 認証失敗時の通知表示(カスタム文字列) 25文字以内
            "strangerTipsCustomCard" =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["CardCustomTip"];                                        },
                                    "validate" => function(Validator $v)     { return $v->maxlength(25);                                            },
                                    "set"      => function(&$content, $val)  { $content["CardCustomTip"] = $val;                                        }],
                                    
            // 認証失敗時のテキスト背景色
            "strangerTipsBackgroundColorCard"=>["name"     => "StrangerTips"  , 
                                  		    "convert"  => function($content)         { return $content["CardBackgroundColor"];                         },
                                   		    "validate" => function(Validator $v)     { return $v->inArray(["Blue", "Red", "Green"]);				 },
                                   			"set"      => function(&$content, $val)  { $content["CardBackgroundColor"] = $val;                         }],
                                   
            // 認証失敗時に音声再生を行うかどうか 1 or 0
            "strangerVoiceEnableCard"=>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["CardVoiceEnable"] ? 1 : 0;                              },
                                    "validate" => function(Validator $v)     { return $v->flag();                                                   },
                                    "set"      => function(&$content, $val)  { $content["CardVoiceEnable"] = empty($val) ? false : true  ;              }],

            // 認証失敗時の音声再生内容 現在利用可能は strangerVoice1(未登録者) のみ
            "strangerVoiceTypeCard"  =>["name"     => "StrangerTips"  , 
                                    "convert"  => function($content)         { return $content["CardVoiceTip"];                                            },
                                    "validate" => function(Validator $v)     { return $v->inArray(["strangerVoice1"]);                              },
                                    "set"      => function(&$content, $val)  { $content["CardVoiceTip"] = $val;                                             }],
                                     
            // ======================================== デバイス管理：高機能設定：識別設定 ========================================
            // 識別距離
            // データ格納上はセンチメートルなので2mの場合は200。選択肢は0.5m(50), 1m(100), 1.5m(150), 2m(200)
            "recogWorkstateTime" =>["name"     => "FaceRecogniseConfig" , 
                                    "convert"  => function($content)         { return $content[0]["RecogniseDistanceRange"][1] / 100;               },
                                    "validate" => function(Validator $v)     { return $v->inArray(["0.5", "1", "1.5", "2"]);                        },
                                    "set"      => function(&$content, $val)  { $content[0]["RecogniseDistanceRange"][1] = (int) ($val * 100);       }],
            
            // 識別レベル
            "recogLiveness"      =>["name"     => "Liveness"         ,
                                    "convert"  => function($content) { 
                                        if (empty($content[0]["Enable"])  && empty($content[1]["Enable"]))  return 1;       // 写真/ビデオの偽装を判別しない
                                        if (!empty($content[0]["Enable"]) && empty($content[1]["Enable"]))  return 2;       // 写真/ビデオの偽装を部分的に判別する
                                        if (!empty($content[0]["Enable"]) && !empty($content[1]["Enable"])) return 3;       // 写真/ビデオの偽装を正確に判別する
                                    },

                                    "validate" => function(Validator $v)     { return $v->inArray(["1", "2", "3"]);                             },
                                    
                                    "set"      => function(&$content, $val)  { 
                                        // 写真/ビデオの偽装を判別しない
                                        if ($val == "1") {
                                            $content[0]["Enable"] = false;
                                            $content[1]["Enable"] = false;
                                        }
                                        // 写真/ビデオの偽装を部分的に判別する
                                        if ($val == "2") {
                                            $content[0]["Enable"] = true;
                                            $content[1]["Enable"] = false;
                                        }
                                        // 写真/ビデオの偽装を正確に判別する
                                        if ($val == "3") {
                                            $content[0]["Enable"] = true;
                                            $content[1]["Enable"] = true;
                                        }
                                    } ],
            
            // 識別間隔(0-10)
            "recogCircleInterval" =>["name"     => "FaceRecogniseConfig" , 
                                     "convert"  => function($content)         { return $content[0]["RecogniseCircleInterval"];                  },
                                     "validate" => function(Validator $v)     { return $v->digit(0, 10);                                        },
                                     "set"      => function(&$content, $val)  { $content[0]["RecogniseCircleInterval"] = (int) $val;            }],
                                     
            // 認識比較閾値(1-100)
            "recogSearchThreshold"=>["name"     => "FaceRecogniseRule"   , 
                                     "convert"  => function($content)         { return $content[0]["SearchThreshold"];                          },
                                     "validate" => function(Validator $v)     { return $v->digit(1, 100);                                       },
                                     "set"      => function(&$content, $val)  { $content[0]["SearchThreshold"] = (int) $val;                    }],

            // マスク検出時の認識比較閾値(1-100)
            "recogMouthoccThreshold"=>["name"   => "FaceRecogniseRule"   , 
                                     "convert"  => function($content)         { return $content[0]["MouthoccThreshold"];                        },
                                     "validate" => function(Validator $v)     { return $v->digit(1, 100);                                       },
                                     "set"      => function(&$content, $val)  { $content[0]["MouthoccThreshold"] = (int) $val;                   }],
	
			// 顔写真登録時の警告類似度(0-100)
			"captureAlarteThreshold"=>["name"   => "FaceRecogniseRule"   ,
									 "convert"  => function($content)         { return $content[0]["PictureCheckThreshold"];                    },
									 "validate" => function(Validator $v)     { return $v->digit(0, 100);                                       },
									 "set"      => function(&$content, $val)  { $content[0]["PictureCheckThreshold"] = (int) $val;               }],
                                     
                                     
            // ======================================== デバイス管理：高機能設定：マスク検出 ========================================
            // マスク検出の基本設定                 0: マスク判定のみ, 1: マスクのない人をシールドする, 2: マスクのある人をシールドする
            "maskDetectMode"                =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["DetectMode"];                        },
                                               "validate" => function(Validator $v)     { return $v->digit(0, 2);                               },
                                               "set"      => function(&$content, $val)  { $content["DetectMode"] = (int) $val;                  }],
                                                
			// マスク検出モード　0: 口のみ覆うも許可する 1: 鼻と口の両方を覆う
			"maskFaceAttrSwitch"			=>["name"     => "FaceAttrConfig", 
                                               "convert"  => function($content)         { return $content["FaceAttrSwitch"];                    },
                                               "validate" => function(Validator $v)     { return $v->digit(0, 1);                               },
                                               "set"      => function(&$content, $val)  { $content["FaceAttrSwitch"] = (int) $val;               }],
                                               
            // マスクあり時に通知表示するかどうか   1 / 0
            "maskWearShowEnable"            =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["WearMask"]["ShowEnable"] ? 1 : 0;                    },
                                               "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                               "set"      => function(&$content, $val)  { $content["WearMask"]["ShowEnable"] = empty($val) ? false : true  ;    }],

            // マスクあり時の通知テキスト           25文字まで
            "maskWearShowTips"              =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["WearMask"]["ShowTips"];                              },
                                               "validate" => function(Validator $v)     { return $v->maxlength(25);                                             },
                                               "set"      => function(&$content, $val)  { $content["WearMask"]["ShowTips"] = $val;                               }],

            // マスクあり時の通知テキスト背景色     Blue / Green / Red
            "maskWearShowBackgroundColor"   =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["WearMask"]["BackgroundColor"];                       },
                                               "validate" => function(Validator $v)     { return $v->inArray(["Blue", "Green", "Red"]);                         },
                                               "set"      => function(&$content, $val)  { $content["WearMask"]["BackgroundColor"] = $val;                        }],

            // マスクあり時に音声表示するかどうか   1 / 0
            "maskWearVoiceEnable"           =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["WearMask"]["VoiceEnable"] ? 1 : 0;                   },
                                               "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                               "set"      => function(&$content, $val)  { $content["WearMask"]["VoiceEnable"] = empty($val) ? false : true  ;   }],

            // マスク無し時に通知表示するかどうか   1 / 0
            "maskNowearShowEnable"          =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["NoMask"]["ShowEnable"] ? 1 : 0;                      },
                                               "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                               "set"      => function(&$content, $val)  { $content["NoMask"]["ShowEnable"] = empty($val) ? false : true  ;      }],

            // マスク無し時の通知テキスト           25文字まで
            "maskNowearShowTips"            =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["NoMask"]["ShowTips"];                                },
                                               "validate" => function(Validator $v)     { return $v->maxlength(25);                                            },
                                               "set"      => function(&$content, $val)  { $content["NoMask"]["ShowTips"] = $val;                                }],

            // マスク無し時の通知テキスト背景色     Blue / Green / Red
            "maskNowearShowBackgroundColor" =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["NoMask"]["BackgroundColor"];                         },
                                               "validate" => function(Validator $v)     { return $v->inArray(["Blue", "Green", "Red"]);                         },
                                               "set"      => function(&$content, $val)  { $content["NoMask"]["BackgroundColor"] = $val;                          }],

            // マスク無し時に音声表示するかどうか   1 / 0
            "maskNowearVoiceEnable"         =>["name"     => "MaskDetectConfig", 
                                               "convert"  => function($content)         { return $content["NoMask"]["VoiceEnable"] ? 1 : 0;                     },
                                               "validate" => function(Validator $v)     { return $v->flag();                                                    },
                                               "set"      => function(&$content, $val)  { $content["NoMask"]["VoiceEnable"] = empty($val) ? false : true  ;     }],

            
            // 「マスクあり時に音声で通知する」「マスクなし時に音声で通知する」の内容(テキスト入力)は現状機能しないため不要(チェックボックス機能は要)
            
            // ======================================== デバイス管理：高機能設定：温度検出 ========================================
            // 有効無効
            "tempEnable"            =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return $content["Enable"] ? 1 : 0;                                             },
                                       "validate" => function(Validator $v)     { return $v->flag();                                                            },
                                       "set"      => function(&$content, $val)  { $content["Enable"] = empty($val) ? false : true  ;                            }],

            // ---------------- 体温検出基本設定
            // 検知モード 0: 検出判定のみ, 1: 体温異常者をシールドする
            "tempDetectMode"        =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return $content["DetectMode"];                                                },
                                       "validate" => function(Validator $v)     { return $v->digit(0, 1);                                                       },
                                       "set"      => function(&$content, $val)  { $content["DetectMode"] = (int) $val;                                          }],

            // 温度单位  0: 摂氏, 1: 華氏
            "tempUnit"              =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return $content["TempUnit"];                                                  },
                                       "validate" => function(Validator $v)     { return $v->digit(0, 1);                                                       },
                                       "set"      => function(&$content, $val)  { $content["TempUnit"] = (int) $val;                                            }],

            // 正常体温:下限(摂氏の場合は10.0-42.0 華氏の場合は93.2-107.6 小数点以下第一位まで。)
            "tempValueRangeFrom"    =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return round($content["ValueRange"][0], 1);                                   },
                                       "validate" => function(Validator $v)     { return $v->float(1, 10, 107);                                                 },
                                       "set"      => function(&$content, $val)  { $content["ValueRange"][0] = (float) $val;                                     }],

            // 正常体温:上限(摂氏の場合は10.0-42.0 華氏の場合は93.2-107.6 小数点以下第一位まで。)
            "tempValueRangeTo"      =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return round($content["ValueRange"][1], 1);                                   },
                                       "validate" => function(Validator $v)     { return $v->float(1, 10, 107);                                                 },
                                       "set"      => function(&$content, $val)  { $content["ValueRange"][1] = (float) $val;                                     }],

            // 温度補正 -5 - 5　小数点以下第一位まで
            "tempCorrection"        =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return round($content["Correction"], 1);                                      },
                                       "validate" => function(Validator $v)     { return $v->float(1, -5, 5);                                                   },
                                       "set"      => function(&$content, $val)  { $content["Correction"] =  (float) $val;                                       }],

            // 低温補正  1 / 0
            "tempLowTempCorrection" =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return $content["LowTempCorrection"];		},
                                       "validate" => function(Validator $v)     { return $v->flag(); 			               	},
                                       "set"      => function(&$content, $val)  { $content["LowTempCorrection"] = (int) $val;  }],
                                       
            // 表面温度補正 -5-5　小数点以下第一位まで
//          "tempSurfaceCorrection" =>["name"    => "TempDetectConfig", "convert" => function($content) { return $content["SurfaceTempCorrection"]; 

            // 温度測定最小顔ピクセル 0-1024
            "tempMinPixel"          =>["name"     => "TempDetectConfig", 
                                       "convert"  => function($content)         { return $content["MinPixel"];                                                  },
                                       "validate" => function(Validator $v)     { return $v->digit(0, 1024);                                                    },
                                       "set"      => function(&$content, $val)  { $content["MinPixel"] = (int) $val;                                            }],
                                        

            // 高温判定感度切替閾値 0-100
//          "tempHighTempJudgeSensibilityThreshold"=>["name"    => "TempDetectConfig", "convert" => function($content) { return $content["HighTempJudgeSensibilityThreshold"]; 

            // サーマル検出エリア調整X -10～10
//          "tempPicAreaOffsetX"    =>["name"    => "TempDetectConfig", "convert" => function($content) { return $content["TempPicAreaOffset"][0]; 

            // サーマル検出エリア調整Y -10～10
//          "tempPicAreaOffsetY"    =>["name"    => "TempDetectConfig", "convert" => function($content) { return $content["TempPicAreaOffset"][1]; 
            
            // ---------------- 自動補正
            // 自動補正適用 1 or 0
//          "tempAutoCorrectionEnable"   =>["name"    => "TempDetectConfig", "convert" => function($content) { return $content["AutoCorrection"]["Enable"] ? 1 : 0; 

            // 更新間隔 0 - 24
//          "tempAutoCorrectionInterval" =>["name"    => "TempDetectConfig", "convert" => function($content) { return $content["AutoCorrection"]["Interval"]; 

            // 更新頻度 3 - 10000
//          "tempAutoCorrectionFrequency"=>["name"    => "TempDetectConfig", "convert" => function($content) { return $content["AutoCorrection"]["Frequency"]; 

            
            // ---------------- 体温検出通知設定 (%Cは検出温度値)
            // 体温正常時の通知表示するかどうか 1 / 0
            "tempNormalShowEnable"   =>["name"     => "TempDetectConfig", 
                                        "convert"  => function($content)         { return $content["Normal"]["ShowEnable"] ? 1 : 0;                         },
                                        "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                        "set"      => function(&$content, $val)  { $content["Normal"]["ShowEnable"] = empty($val) ? false : true  ;         }],

            // 体温正常時の通知表示する際の通知テキスト
            "tempNormalShowTips"     =>["name"     => "TempDetectConfig", 
                                        "convert"  => function($content)         { return $content["Normal"]["ShowTips"];                                   },
                                        "validate" => function(Validator $v)     { return $v->maxlength(25);                                                },
                                        "set"      => function(&$content, $val)  { $content["Normal"]["ShowTips"] = $val;                                   }],

            // 体温正常時の音声通知するかどうか 1 / 0
            "tempNormalVoiceEnable"  =>["name"     => "TempDetectConfig", 
                                        "convert"  => function($content)         { return $content["Normal"]["VoiceEnable"] ? 1 : 0;                        },
                                        "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                        "set"      => function(&$content, $val)  { $content["Normal"]["VoiceEnable"] = empty($val) ? false : true  ;        }],

            // 体温異常時の通知表示するかどうか 1 / 0
            "tempAbnormalShowEnable" =>["name"     => "TempDetectConfig", 
                                        "convert"  => function($content)         { return $content["Abnormal"]["ShowEnable"] ? 1 : 0;                       },
                                        "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                        "set"      => function(&$content, $val)  { $content["Abnormal"]["ShowEnable"] = empty($val) ? false : true  ;       }],

            // 体温異常時の通知表示する際の通知テキスト (25文字以内)
            "tempAbnormalShowTips"   =>["name"     => "TempDetectConfig", 
                                        "convert"  => function($content)         { return $content["Abnormal"]["ShowTips"];                                 },
                                        "validate" => function(Validator $v)     { return $v->maxlength(25);                                                },
                                        "set"      => function(&$content, $val)  { $content["Abnormal"]["ShowTips"] = $val;                                 }],

            // 体温異常時の音声通知するかどうか 1 / 0
            "tempAbnormalVoiceEnable"=>["name"     => "TempDetectConfig", 
                                        "convert"  => function($content)         { return $content["Abnormal"]["VoiceEnable"] ? 1 : 0;                      },
                                        "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                        "set"      => function(&$content, $val)  { $content["Abnormal"]["VoiceEnable"] = empty($val) ? false : true  ;      }],

            
            // ※1「表面温度補正」、「高温判定感度切替閾値」、「サーマル検出エリア調整」、「自動補正」は不要
            // ※2「体温正常時の音声通知する」「マスクなし時に音声で通知する」の内容(テキスト入力)は現状機能しないため不要(チェックボックス機能は要)            
            
            // ---------------- 日付設定
            // NTPを有効にするかどうか	1 / 0
            "ntpEnable"				 =>["name"     => "NTP", 
                                        "convert"  => function($content)         { return $content["Enable"] ? 1 : 0;                      	},
                                        "validate" => function(Validator $v)     { return $v->flag();                                          },
                                        "set"      => function(&$content, $val)  { $content["Enable"] = empty($val) ? 0 : 1  ; 		     	}],
                                        
            // NTPサーバ
            "ntpHostName"			  =>["name"     => "NTP", 
                                        "convert"  => function($content)         { return $content["HostName"];		                      	},
                                        "validate" => function(Validator $v)     { return $v->maxlength(100);                                  },
                                        "set"      => function(&$content, $val)  { $content["HostName"] = $val;      							}],
            // NTPサーバのポート
            "ntpPort"				   =>["name"     => "NTP", 
                                        "convert"  => function($content)         { return $content["Port"];			                      	},
                                        "validate" => function(Validator $v)     { return $v->digit(0, 65535);  	      	   	                 },
                                        "set"      => function(&$content, $val)  { $content["Port"] = (int) $val;      							}],
                                        
            // 時刻同期間隔(分)
            "ntpInterval"		   	  =>["name"     => "NTP", 
                                        "convert"  => function($content)         { return $content["Interval"];			                    },
                                        "validate" => function(Validator $v)     { return $v->digit(1, 1440);  	      	   	                },
                                        "set"      => function(&$content, $val)  { $content["Interval"] = (int) $val;      						}],
                                        
            
        ];
        
        
		
		
	}

	// 再起動系の設定
	public static function getRebootConfigDefine() {
		
		return [
			// ======================================== システムメンテナンス ======================================== 

			// 自動再起動が有効かどうか 1 or 0
			"rebootScheduleEnable"	=>["name"     => "AutoMaintain"  , 
									   "convert"  => function($content) 		{ return $content["Enable"] ? 1 : 0; 									    },
                                       "validate" => function(Validator $v)     { return $v->flag();                                                       },
                                       "set"      => function(&$content, $val)  { $content["Enable"] = empty($val) ? false : true  ;      				}],
									   
			// 自動再起動の曜日。0が日曜日で6が土曜日。
			"rebootScheduleDayWeek"	=>["name"     => "AutoMaintain"  ,
									   "convert"  => function($content) 		{ return $content["DayWeek"]; 											   },
			                           "validate" => function(Validator $v)     { return $v->digit(0, 6);                                                 },
                                       "set"      => function(&$content, $val)  { $content["DayWeek"] = (int) $val;										   }],
			
			// 自動再起動の時刻。Hourのみが指定出来る。	
			"rebootScheduleHours"	=>["name"     => "AutoMaintain"  , 
									   "convert"  => function($content) 		{ return $content["Hours"]; 											   },
                                       "validate" => function(Validator $v)     { return $v->digit(0, 23);                                                },
                                       "set"      => function(&$content, $val)  { $content["Hours"] = (int) $val;										   }],
			
		];
		
	}
	
	// システム系の設定
	public static function getHiddenConfigDefine() {
		
		return [
			// 隠しパラメータ
			"saveMessage"			=>["name"     => "SaveMessage"  , 
									   "convert"  => function($content) 		 { return $content["Message"]; 									    },
                                       "set"      => function(&$content, $val)  { $content["Message"] = $val;							      		}],
											   
		];
		
	}
	
	
	
	// 設定を取得
	public static function getConfig(array $device, $define, $updateCallback = false) {
		
		$param = '{"method":"specific.multicall","id":'.WsApiService::genId().',"params":[';
		$params = [];
		$idx = 0;
		$configNameIndex = [];
		foreach ($define as $key=>$config) {
			if (isset($configNameIndex[$config["name"]])) continue;
  			$params[] = '{"method":"configCentre.getConfig","params":{"name":"'.$config["name"].'"},"id":'.WsApiService::genId().'}';
  			$configNameIndex[$config["name"]] = $idx;
  			$idx++;
		}
		$param .= join(",", $params);
		$param .= ']}';
		$ret = WsApiService::accessWsApi($device, $param);

		if (empty($ret["params"])) {
			throw new DeviceWsException("設定データがデバイスから取得出来ませんでした");
		}
		
		$datas = [];
		foreach ($define as $key=>$config) {
			$idx = $configNameIndex[$config["name"]];
			$retParam = $ret["params"][$idx];
			
			if ($retParam["result"]) {
				$content = $retParam["params"]["content"];
				$convertFunction = $config["convert"];
				$value = $convertFunction($content);

				// 値変更のためのコールバック。
				if (!empty($updateCallback)) {
					$retParam["params"]["content"] = $updateCallback($key, $content, $value);
					$ret["params"][$idx] = $retParam;
				}
				
			} else {
				$value = false;
				
			}
			
			$ret["params"][$idx] = $retParam; 
			$datas[$key] = $value;
		}
		
		
		return $datas;
	}
	

	// 設定を判定
	public static function setConfig($device, $define, $data, $pgName) {
		
		// 開始ログ
		SyncService::insertBeginLog($device["device_id"], basename(__FILE__)."_".$pgName);
		
		// 現在の設定を取得。
		$erroredKeys = [];
		$updatedKeys = [];
		$config = ConfigService::getConfig($device, $define, function($key, $content, $currentValue) use($device, $define, $data, &$erroredKeys, &$updatedKeys) {
			
			if (!isset($data[$key])) return $content;
			
			set_time_limit(30);
			
			// 値を変更。
			$setFunction = $define[$key]["set"];
			
			$beforeValue = json_encode($content);
			
			$setFunction($content, $data[$key]);

			$afterValue =  json_encode($content);
			
			// 値変更処理の前後で変化が無い場合には何も行わない。
			if ($beforeValue == $afterValue) {
				return $content;
			}
			
			$params = [];
			$params["name"]   = $define[$key]["name"];
			$params["content"] = $content;
			
			infoLog("設定を変更。".$key."=".$data[$key]);
			
			try {
				$ret = WsApiService::accessWsApi($device, [
		 			"method"=>"configCentre.setConfig"
		 			, "id"=>WsApiService::genId()
		 			, "params"=>$params
		 		]);
				$updatedKeys[] = $key;
			} catch (DeviceWsException $e) {
				$erroredKeys[] = $key;
			}
			
			return $content;
		});
		
		$ret = ["updated"=>$updatedKeys, "errored"=>$erroredKeys];
		
		if (empty($erroredKeys)) {
			SyncService::updateEndLog(20, json_encode($ret, JSON_UNESCAPED_UNICODE));	// 終了ログ
			
		} else {
// 			foreach ($erroredKeys as $erroredKey) {
// 				Errors::add($erroredKey, "デバイス側からのエラーにより、設定の反映が行えませんでした。");
// 			}

			if (empty($updatedKeys)) {
				SyncService::updateEndLog(30, json_encode($ret, JSON_UNESCAPED_UNICODE));	// 終了ログ
			} else {
				SyncService::updateEndLog(40, json_encode($ret, JSON_UNESCAPED_UNICODE));	// 終了ログ
			}
		}
		
		return $ret;
	}
	
	
	
}
