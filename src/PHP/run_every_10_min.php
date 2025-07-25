<?php
include("login/db_connect.php");
include("utils/jwt.php");

$ch = curl_init();


$apple_token = "";
$FireBase_Server_Key="";


//IOS android notification icin tokeni al?
$sql = "Select value from settings where `key` = 'AppleToken'"; 
if ($result = $conn -> query($sql)) {
    $row = $result->fetch_object();
    $apple_token = $row->value;
}


$sql = "Select value from settings where `key` = 'FireBase_Server_Key'"; 
if ($result = $conn -> query($sql)) {
    $row = $result->fetch_object();
    $FireBase_Server_Key = $row->value;
}




//Ship olan ve tracking number girilen tum kayitlari al
$sql = "SELECT id, shipment_code, tracking_number, IFNULL(length(tracking_details),0) as detail_length,
                    (select device_id from customer where id = shipment.customer_id) as device_id FROM 
            shipment where status = 'S' and tracking_number is not null and id = 12"; 


if ($result = $conn -> query($sql)) {
	$resultArray = array();
 
	while($row = $result->fetch_object())
	{
	   $id = $row->id;
	   $tracking_number = $row->tracking_number;
	   $device_id = $row->device_id;
	   $shipment_code = $row->shipment_code;
	   $detail_length = $row->detail_length;

	   if($tracking_number != null) {
    	    curl_setopt($ch, CURLOPT_URL, "https://api.comfylabels.com/v1/track/ups?trackingNumber='$tracking_number'");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            $response = curl_exec($ch);
            //echo $response;

            $pos = strpos($response, "\"type\":\"D\"");
            if ($pos !== false){
                    $lb_arrived = true;
            }else{
                    $lb_arrived = false;
            }
                
             
            $jsonDecoded = json_decode($response,true);
            $package = $jsonDecoded['payload']['shipment'][0]['package'][0];
            $ls_current =  $package['currentStatus']['description']; 
            $ls_save = "";
            $activity = $package['activity'];
            foreach($activity as $key => $value) {
              $ls_status =  $value['status']['description'];
              $address = $value['location']['address'];
              
                 $ls_address = "";
                if( isset( $address['city'] ) ){
                   $ls_address = $ls_address . $address['city'];
                }
                
                if( isset( $address['stateProvince'] ) ){
                    if(strlen($ls_address) > 0){
                        $ls_address = $ls_address . ",";
                    }
                    
                   $ls_address = $ls_address . $address['stateProvince']; 
                }
            
                if( isset( $address['country'] ) ){
                    if(strlen($ls_address) > 0){
                        $ls_address = $ls_address . ",";
                    }
                    
                   $ls_address = $ls_address . $address['country']; 
                }
                           
              $datetime = $value['date'].$value['time'];
              $yyyy = substr($datetime,0,4);
              $mm = substr($datetime,4,2);
              $dd = substr($datetime,6,2);
              $hh = substr($datetime,8,2);
              $MM = substr($datetime,10,2); 
              $ls_date = $yyyy."/".$mm."/".$dd ." ".$hh.":".$MM;
             
              if(strlen($ls_save) > 0){
                 $ls_save = $ls_save . "\n";
              }
             
              $ls_save = $ls_save . $ls_date . " ".  $ls_status; 
              
              if(strlen($ls_address) > 0){
                 $ls_save = $ls_save . ' - ' .  $ls_address;
              }
              
              
             }
            
            
              
              $ls_sql = "Update shipment set ";
              if ($detail_length != strlen($ls_save)){
                  $ls_sql = $ls_sql." tracking_details ='".$ls_save."'"; 
                  
              }
              
              if ($lb_arrived == true){
                  if ($detail_length != strlen($ls_save)){
                      $ls_sql = $ls_sql . ",";
                  }    
                  
                  $ls_sql = $ls_sql . " status = 'D'";
              }
              
              $ls_sql = $ls_sql . " where id = $id";
              
              echo $ls_sql;
              
            f_update($ls_sql, $conn, false);
            if ($lb_arrived == true){
                if (strlen($device_id) < 70){
                    f_send_apple_notification($shipment_id, $shipment_code, 'D', $device_id, $apple_token);
                }else{
                    f_send_google_notification($shipment_id, $shipment_code, 'D', $device_id, $FireBase_Server_Key);
                }

            }
           
        }
	}
}
curl_close($ch);
$conn -> close();



/*
{
   "success":true,
   "payload":{
      "shipment":[
         {
            "inquiryNumber":"1ZWE58136821755160",
            "package":[
               {
                  "trackingNumber":"1ZWE58136821755160",
                  "deliveryDate":[
                     {
                        "type":"RDD",
                        "date":"20240131"
                     }
                  ],
                  "deliveryTime":{
                     "startTime":"124500",
                     "type":"EDW",
                     "endTime":"164500"
                  },
                  "activity":[
                     {
                        "location":{
                           "address":{
                              "city":"Calgary",
                              "stateProvince":"AB",
                              "countryCode":"CA",
                              "country":"CA"
                           },
                           "slic":"7439"
                        },
                        "status":{
                           "type":"I",
                           "description":"Processing at UPS Facility",
                           "code":"DS",
                           "statusCode":"021"
                        },
                        "date":"20240131",
                        "time":"055005",
                        "gmtDate":"20240131",
                        "gmtOffset":"-07:00",
                        "gmtTime":"12:50:05"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Calgary",
                              "stateProvince":"AB",
                              "countryCode":"CA",
                              "country":"CA"
                           },
                           "slic":"6219"
                        },
                        "status":{
                           "type":"I",
                           "description":"Arrived at Facility",
                           "code":"AR",
                           "statusCode":"005"
                        },
                        "date":"20240130",
                        "time":"152500",
                        "gmtDate":"20240130",
                        "gmtOffset":"-07:00",
                        "gmtTime":"22:25:00"
                     },
                     {
                        "location":{
                           "address":{
                              
                           },
                           "slic":"5179"
                        },
                        "status":{
                           "type":"X",
                           "description":"Your package has cleared customs and is on the way.",
                           "code":"VK",
                           "statusCode":"134"
                        },
                        "date":"20240130",
                        "time":"025734",
                        "gmtDate":"20240130",
                        "gmtOffset":"-06:00",
                        "gmtTime":"08:57:34"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Winnipeg",
                              "stateProvince":"MB",
                              "countryCode":"CA",
                              "country":"CA"
                           },
                           "slic":"6219"
                        },
                        "status":{
                           "type":"I",
                           "description":"Departed from Facility",
                           "code":"DP",
                           "statusCode":"005"
                        },
                        "date":"20240129",
                        "time":"221500",
                        "gmtDate":"20240130",
                        "gmtOffset":"-06:00",
                        "gmtTime":"04:15:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Winnipeg",
                              "stateProvince":"MB",
                              "countryCode":"CA",
                              "country":"CA"
                           },
                           "slic":"5179"
                        },
                        "status":{
                           "type":"I",
                           "description":"Import Scan",
                           "code":"IP",
                           "statusCode":"005"
                        },
                        "date":"20240129",
                        "time":"151629",
                        "gmtDate":"20240129",
                        "gmtOffset":"-05:00",
                        "gmtTime":"20:16:29"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Winnipeg",
                              "stateProvince":"MB",
                              "countryCode":"CA",
                              "country":"CA"
                           },
                           "slic":"5519"
                        },
                        "status":{
                           "type":"I",
                           "description":"Arrived at Facility",
                           "code":"AR",
                           "statusCode":"005"
                        },
                        "date":"20240129",
                        "time":"125300",
                        "gmtDate":"20240129",
                        "gmtOffset":"-06:00",
                        "gmtTime":"18:53:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Fargo",
                              "stateProvince":"ND",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"5519"
                        },
                        "status":{
                           "type":"I",
                           "description":"Departed from Facility",
                           "code":"DP",
                           "statusCode":"005"
                        },
                        "date":"20240129",
                        "time":"063700",
                        "gmtDate":"20240129",
                        "gmtOffset":"-06:00",
                        "gmtTime":"12:37:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Fargo",
                              "stateProvince":"ND",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"5519"
                        },
                        "status":{
                           "type":"I",
                           "description":"Arrived at Facility",
                           "code":"AR",
                           "statusCode":"005"
                        },
                        "date":"20240127",
                        "time":"054300",
                        "gmtDate":"20240127",
                        "gmtOffset":"-06:00",
                        "gmtTime":"11:43:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Eagan",
                              "stateProvince":"MN",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"5519"
                        },
                        "status":{
                           "type":"I",
                           "description":"Departed from Facility",
                           "code":"DP",
                           "statusCode":"005"
                        },
                        "date":"20240127",
                        "time":"010300",
                        "gmtDate":"20240127",
                        "gmtOffset":"-06:00",
                        "gmtTime":"07:03:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Eagan",
                              "stateProvince":"MN",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"1729"
                        },
                        "status":{
                           "type":"I",
                           "description":"Arrived at Facility",
                           "code":"AR",
                           "statusCode":"005"
                        },
                        "date":"20240126",
                        "time":"012500",
                        "gmtDate":"20240126",
                        "gmtOffset":"-06:00",
                        "gmtTime":"07:25:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Middletown",
                              "stateProvince":"PA",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"1729"
                        },
                        "status":{
                           "type":"I",
                           "description":"Departed from Facility",
                           "code":"DP",
                           "statusCode":"005"
                        },
                        "date":"20240125",
                        "time":"084800",
                        "gmtDate":"20240125",
                        "gmtOffset":"-05:00",
                        "gmtTime":"13:48:00"
                     },
                     {
                        "location":{
                           "address":{
                              
                           },
                           "slic":"1729"
                        },
                        "status":{
                           "type":"X",
                           "description":"Due to operating conditions, your package may be delayed.",
                           "code":"08",
                           "statusCode":"048"
                        },
                        "date":"20240125",
                        "time":"050000",
                        "gmtDate":"20240125",
                        "gmtOffset":"-05:00",
                        "gmtTime":"10:00:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Middletown",
                              "stateProvince":"PA",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"0819"
                        },
                        "status":{
                           "type":"I",
                           "description":"Arrived at Facility",
                           "code":"AR",
                           "statusCode":"005"
                        },
                        "date":"20240124",
                        "time":"233500",
                        "gmtDate":"20240125",
                        "gmtOffset":"-05:00",
                        "gmtTime":"04:35:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Lawnside",
                              "stateProvince":"NJ",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"0819"
                        },
                        "status":{
                           "type":"I",
                           "description":"Departed from Facility",
                           "code":"DP",
                           "statusCode":"005"
                        },
                        "date":"20240124",
                        "time":"212200",
                        "gmtDate":"20240125",
                        "gmtOffset":"-05:00",
                        "gmtTime":"02:22:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Lawnside",
                              "stateProvince":"NJ",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"0819"
                        },
                        "status":{
                           "type":"X",
                           "description":"Severe weather conditions have delayed delivery.",
                           "code":"DJ",
                           "statusCode":"032"
                        },
                        "date":"20240123",
                        "time":"215624",
                        "gmtDate":"20240124",
                        "gmtOffset":"-05:00",
                        "gmtTime":"02:56:24"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Lawnside",
                              "stateProvince":"NJ",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"0829"
                        },
                        "status":{
                           "type":"I",
                           "description":"Arrived at Facility",
                           "code":"AR",
                           "statusCode":"005"
                        },
                        "date":"20240123",
                        "time":"204500",
                        "gmtDate":"20240124",
                        "gmtOffset":"-05:00",
                        "gmtTime":"01:45:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Pleasantville",
                              "stateProvince":"NJ",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"0829"
                        },
                        "status":{
                           "type":"I",
                           "description":"Departed from Facility",
                           "code":"DP",
                           "statusCode":"005"
                        },
                        "date":"20240123",
                        "time":"194800",
                        "gmtDate":"20240124",
                        "gmtOffset":"-05:00",
                        "gmtTime":"00:48:00"
                     },
                     {
                        "location":{
                           "address":{
                              "city":"Pleasantville",
                              "stateProvince":"NJ",
                              "countryCode":"US",
                              "country":"US"
                           },
                           "slic":"0829"
                        },
                        "status":{
                           "type":"I",
                           "description":"We have your package",
                           "code":"OR",
                           "statusCode":"005"
                        },
                        "date":"20240123",
                        "time":"182609",
                        "gmtDate":"20240123",
                        "gmtOffset":"-05:00",
                        "gmtTime":"23:26:09"
                     },
                     {
                        "location":{
                           "address":{
                              "countryCode":"US",
                              "country":"US"
                           }
                        },
                        "status":{
                           "type":"M",
                           "description":"Shipper created a label, UPS has not received the package yet. ",
                           "code":"MP",
                           "statusCode":"003"
                        },
                        "date":"20240123",
                        "time":"153907",
                        "gmtDate":"20240123",
                        "gmtOffset":"-05:00",
                        "gmtTime":"20:39:07"
                     }
                  ],
                  "currentStatus":{
                     "description":"Out for Delivery",
                     "code":"005"
                  },
                  "packageAddress":[
                     {
                        "type":"ORIGIN",
                        "name":"COMFYLIFE INTL",
                        "attentionName":"",
                        "address":{
                           "addressLine1":" 809 HYLTON RD ",
                           "addressLine2":"",
                           "city":"PENNSAUKEN",
                           "stateProvince":"NJ",
                           "postalCode":"081101335",
                           "countryCode":"US",
                           "country":"US"
                        }
                     },
                     {
                        "type":"DESTINATION",
                        "name":"MONARCH USA INC",
                        "attentionName":"MONARCH USA INC",
                        "address":{
                           "addressLine1":"AMAZON FC YYC1",
                           "addressLine2":"",
                           "city":"BALZAC",
                           "stateProvince":"AB",
                           "postalCode":"T4A1C6",
                           "countryCode":"CA",
                           "country":"CA"
                        }
                     }
                  ],
                  "weight":{
                     "unitOfMeasurement":"LBS",
                     "weight":"49.20"
                  },
                  "service":{
                     "code":"661",
                     "levelCode":"011",
                     "description":"UPS® Standard"
                  },
                  "referenceNumber":[
                     {
                        "type":"SHIPMENT",
                        "number":"WE58139STBR"
                     }
                  ],
                  "deliveryInformation":{
                     "deliveryPhoto":{
                        "isNonPostalCodeCountry":false
                     }
                  },
                  "packageCount":1
               }
            ],
            "userRelation":[
               "SHIPPER"
            ]
         }
      ]
   }
}
*/



?>