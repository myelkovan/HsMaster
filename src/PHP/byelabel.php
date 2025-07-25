<?php

//require_once "../PHP/utils/GuzzleHttp/vendor/autoload.php";

require_once('/home/u242434967/domains/jetbasket.us/public_html/shop/PHP/utils/GuzzleHttp/vendor/autoload.php');

// // Gelen isteğin referer bilgisi
// $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// // Sunucunuzun domaini (referer kontrolü için)
// $allowed_domain = 'jetbasket.us'; // Buraya kendi domaininizi yazın

// // Eğer referer boşsa veya izin verilen domain içermiyorsa erişimi engelle
// if (empty($referer) || strpos($referer, $allowed_domain) === false) {
//     header('HTTP/1.0 403 Forbidden'); // 403 yasaklama kodu
//     echo "Bu sayfaya doğrudan erişim izni yok.";
//     exit; // İşlemi sonlandır
// }


use GuzzleHttp\Client;

use function PHPUnit\Framework\throwException;

class ByelabelService
{

    private $client;
    private $cache;
    private $user;
    private $BYELABEL_API_URL = 'https://api.byelabel.com/v1';
    private $BYELABEL_API_CLIENT_ID = '15d0fc2e8725de06d21caa1b611bc011';
    private $BYELABEL_API_CLIENT_SECRET = '537a44019a3aba554229466d5f449756';
    private $WORKSPACE_ID = '32b7fb8b-24f0-4350-b9d7-e3f886db70a5';

    public function __construct($user, $cache)
    {
        $this->client = new Client();
        $this->user = $user;
        $this->cache = $cache;
    }





    public function getRates_FromByeLabel(
        $from_country_code,
        $from_zipcode,
        $to_country_code,
        $to_zipcode,
        $from_state,
        $to_state,
        $from_city,
        $to_city,
        $from_street_1,
        $from_street_2,
        $from_street_3,
        $to_street_1,
        $to_street_2,
        $to_street_3,
        $weight,
        $length,
        $width,
        $height,
        $carrier_id = null,
        $carrier_code = null,
        $carrier_service = null,
        $carrier_packaging = null
    ) {
        if (
            $from_zipcode === "" || $from_country_code === "" ||
            $to_country_code === "" || $to_zipcode === "" ||
            $weight === "" || $length === "" || $width === "" || $height === ""
        ) {
            throw new Exception('Zorunlu parametrelerden biri  edilmemiş veya boş.');
        }
        $session = $this->getSession();


        $data = [

            'to' => [
                'zip_code' => $to_zipcode,
                'country_code' => $to_country_code
            ],
            'from' => [
                'zip_code' => $from_zipcode,
                'country_code' => $from_country_code
            ],
            'package' => [
                [
                    'weight' => [
                        'unit' => 'lb',
                        'value' => $weight
                    ],
                    'dimensions' => [
                        'unit' => 'in',
                        'length' => $length,
                        'width' => $width,
                        'height' => $height
                    ]
                ]
            ]
        ];


        //  Zorunlu olmayan parametrlerden gelenler var ise data ya ekle
        if (isset($carrier_id) && !empty($carrier_id)) {
            $data['carrier']['id'] = $carrier_id;
        }
        if (isset($carrier_code) && !empty($carrier_code)) {
            $data['carrier']['code'] = $carrier_code;
        }
        if (isset($carrier_service) && !empty($carrier_service)) {
            $data['carrier']['service'] = $carrier_service;
        }
        if (isset($carrier_packaging) && !empty($carrier_packaging)) {
            $data['carrier']['packaging'] = $carrier_packaging;
        }

        if (isset($from_state) && !empty($from_state)) {
            $data['from']['state_code'] = $from_state;
        }
        if (isset($from_city) && !empty($from_city)) {
            $data['from']['city'] = $from_city;
        }
        if (isset($from_street_1) && !empty($from_street_1)) {
            $data['from']['street'][0] = $from_street_1;
        }
        if (isset($from_street_2) && !empty($from_street_2)) {
            $data['from']['street'][1] = $from_street_2;
        }
        if (isset($from_street_3) && !empty($from_street_3)) {
            $data['from']['street'][2] = $from_street_3;
        }
        if (isset($from_residential) && !empty($from_residential)) {
            $data['from']['residential'] = $from_residential;
        }

        if (isset($to_state) && !empty($to_state)) {
            $data['to']['state_code'] = $to_state;
        }
        if (isset($to_city) && !empty($to_city)) {
            $data['to']['city'] = $to_city;
        }
        if (isset($to_street_1) && !empty($to_street_1)) {
            $data['to']['street'][0] = $to_street_1;
        }
        if (isset($to_street_2) && !empty($to_street_2)) {
            $data['to']['street'][1] = $to_street_2;
        }
        if (isset($to_street_3) && !empty($to_street_3)) {
            $data['to']['street'][2] = $to_street_3;
        }
        if (isset($to_residential) && !empty($to_residential)) {
            $data['to']['residential'] = $to_residential;
        }

        //var_dump($data);

        try {
            $response = $this->client->post(
                $this->BYELABEL_API_URL . '/rates',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ],
                    'json' => $data
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                $newResponse = [];
                //var_dump($data);
                //echo (" succed:" . $data['success'] );
                if ($data['success'] && isset($data['payload'])) {
                    foreach ($data['payload'] as $record) {
                        $carrierId = isset($record['carrier']['id']) ? $record['carrier']['id'] : '';
                        $carrierCode = isset($record['carrier']['code']) ? $record['carrier']['code'] : '';
                        $serviceCode = isset($record['carrier']['service']['code']) ? $record['carrier']['service']['code'] : '';
                        $shipment_cost = isset($record['rate']['cost']) ? $record['rate']['cost'] : '';
                        $serviceName = isset($record['carrier']['service']['name']) ? $record['carrier']['service']['name'] : '';
                        $packaging_code = isset($record['carrier']['packaging']['code']) ? $record['carrier']['packaging']['code'] : '';
                        $packaging_name = isset($record['carrier']['packaging']['name']) ? $record['carrier']['packaging']['name'] : '';

                        //echo "carrierCode : " . $carrierCode. "--- serviceCode : " . $serviceCode . 
                        //              "--- rateCost" .  $shipment_cost . "--- otherCost" .  $serviceName .  "\n";
                        $item = [
                            'carrier_id' => $carrierId,
                            'carrier_code' => $carrierCode,
                            'service_code' => $serviceCode,
                            'service_name' => $serviceName,
                            'packaging_code' => $packaging_code,
                            'packaging_name' => $packaging_name,
                            'shipment_cost' => $shipment_cost
                        ];
                        $newResponse[] = $item;
                    }
                } else {
                    throw new Exception("Servis call for getting rate list is not successed with error : " . $data['error']['message']);
                    error_log("Byelabel getting rate list failed:" . $data['error']);
                }
                $this->logWebService('getSession:', $data, "");

                return $newResponse;
            } else {
                error_log("Byelabel getting rate list failed with Status Code:"  . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('getRates: ' . $e->getMessage(), $data, "");
            error_log("Byelabel getting rate list failed with error: " . $e->getMessage());
            throw new Exception("Failed to getting rate  list. Error: " . $e->getMessage() . " with Status Code:" . $statusCode);
        }
    }










    public function createLabel_FromByeLabel(
        $from_name,
        $from_taxnumber,
        $from_email,
        $from_phone,
        $from_country_code,
        $from_zipcode,
        $from_state,
        $from_city,
        $from_street_1,
        $from_street_2,
        $from_street_3,
        $to_name,
        $to_email,
        $to_phone,
        $to_country_code,
        $to_zipcode,
        $to_state,
        $to_city,
        $to_street_1,
        $to_street_2,
        $to_street_3,
        $weight,
        $length,
        $width,
        $height,
        $carrier_id,
        $carrier_code,
        $carrier_service,
        $carrier_packaging,
        $invoice_date,
        $invoice_number,
        $invoice_contents,
        $invoice_description,
        $items
    ) {

        if (!isset($carrier_id) ||  empty($carrier_id)) {
            throw new Exception('Zorunlu parametre carrier_id set edilmemiş veya boş.');
        }

        if (!isset($carrier_code) ||  empty($carrier_code)) {
            throw new Exception('Zorunlu parametre carrier_code set edilmemiş veya boş.');
        }
        if (!isset($carrier_service) || empty($carrier_service)) {
            throw new Exception('Zorunlu parametre carrier service set edilmemiş veya boş.');
        }
        if (!isset($carrier_packaging) || empty($carrier_packaging)) {
            throw new Exception('Zorunlu parametre carrier packaging set edilmemiş veya boş.');
        }

        $session = $this->getSession();

        //  ÖRNEK VERİLERLE DOLU DATA
        $data = [
            'carrier' => [
                'code' => 'ups',
                'service' => '08',
                'packaging' => '02'
            ],
            'invoice' => [
                'date' => '2025-01-15',
                'number' => 'AA00121',
                'contents' => 'merchandise',
                'description' => 'NRI: 1111'
            ],
            'from' => [
                'name' => 'John Wick',
                'company' => '',
                'tax_number' => '100011',
                'email' => 'john@wick.com',
                'phone' => '1555055555',
                'country_code' => 'US',
                'zip_code' => '44067',
                'state_code' => 'OH',
                'city' => 'Northfield',
                'street' => [
                    '85 W Aurora Rd'
                ]
            ],
            'to' => [
                'name' => 'Dayne Starkk',
                'company' => '',
                'email' => 'john@wick.com',
                'phone' => '4032624326',
                'country_code' => 'CA',
                'zip_code' => 'T2Y 3S5',
                'state_code' => 'AB',
                'city' => 'Calgary',
                'street' => [
                    '16061 Macleod Trail SE'
                ],
                'residential' => true
            ],
            'package' => [
                [
                    'description' => 'Package 1',
                    'weight' => [
                        'value' => 2.2
                    ]
                ],
                [
                    'description' => 'Package 2',
                    'weight' => [
                        'value' => 4.2
                    ]
                ],
                [
                    'description' => 'Package 3',
                    'weight' => [
                        'value' => 5
                    ]
                ]
            ],
            'items' => [
                [
                    'code' => 'ASIN01',
                    'description' => 'Sport Shoes',
                    'hts_code' => '1220.01.1101',
                    'country_of_origin' => 'US',
                    'quantity' => 10,
                    'currency_code' => 'USD',
                    'total_selling_price' => 100
                ],
                [
                    'code' => 'ASIN02',
                    'description' => 'Sunglasses',
                    'hts_code' => '2220.01.1115',
                    'country_of_origin' => 'US',
                    'quantity' => 20,
                    'currency_code' => 'USD',
                    'total_selling_price' => 200
                ]
            ]
        ];

        //echo ('BU BİZİM carrier_code' . $carrier_code);

        $data = [       //  açıklama satırları optional olan parametreler için.
            //'date' =>  date('Y-m-d');,
            'carrier' => [
                'id' => $carrier_id,    // '',
                'code' => $carrier_code,    // 'ups',
                'service' => $carrier_service,    //    '08',
                'packaging' => $carrier_packaging,  // '02'
            ],
            'invoice' => [
                'date' => $invoice_date,
                'number' => $invoice_number,
                'contents' => $invoice_contents,
                'description' => $invoice_description
            ],
            'from' => [
                'name' => $from_name,
                'company' => '',
                'tax_number' => $from_taxnumber,
                'email' => $from_email,
                'phone' => $from_phone,
                'country_code' => $from_country_code,   //'US',
                'zip_code' => $from_zipcode,    //'44067',
                'state_code' => $from_state,    //'OH',
                'city' => $from_city,   //'Northfield',
                'street' => [
                    $from_street_1    //''85 W Aurora Rd'
                ],
                // 'residential' => true // bu alan rate ler için çok önemliymiş.
            ],
            'to' => [
                'name' => $to_name, //'Dayne Starkk',
                'company' => '',
                // eğer direk alıcıya değil de bir depoya filan gidecek ise  uluslar arası gönderilerde to kısmına depo bilgileri sold_to kısmına da Ahmet mehmet  kişi bilgisi girilmeli.
                // 'sold_to' => [
                //         'name' => '',
                //         'company' => ''
                //         'email' => '',
                //         'phone' => '',
                //         'fax' => ''

                //     ],
                'email' => $to_email,
                'phone' => $to_phone,
                'country_code' => $to_country_code, //'CA',
                'zip_code' => $to_zipcode,      //'T2Y 3S5',
                'state_code' => $to_state,  //'AB',
                'city' => $to_city,     //'Calgary',
                'street' => [
                    $to_street_1    //'16061 Macleod Trail SE'
                ],
                //  'residential' => true // bu alan rate ler için çok önemliymiş.
            ],
            'package' => [
                [
                    'description' => 'Package 1',   // Paket multipeace ise zorunlu
                    'weight' => [
                        'value' => $weight
                    ],
                    'dimensions' => [
                        'unit' => 'in',
                        'length' => $length,
                        'width' => $width,
                        'height' => $height
                    ]
                ]
            ],
            'items' => $items
        ];

        if ($from_street_2 !== '') {
            $data['from']['street'][] = $to_street_2;
        }
        if ($from_street_3 !== '') {
            $data['from']['street'][] = $to_street_3;
        }
        if ($to_street_2 !== '') {
            $data['to']['street'][] = $to_street_2;
        }
        if ($to_street_3 !== '') {
            $data['to']['street'][] = $to_street_3;
        }

        // var_dump($data);

        try {
            $response = $this->client->post(
                $this->BYELABEL_API_URL . '/create-label',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ],
                    'json' => $data
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                //var_dump($data);
                //echo (" succed:" . $data['success']);
                $newResponse = null;
                if ($data['success'] && isset($data['payload'])) {
                    $payload = $data['payload'];
                    /*
                        echo "ID: " . $payload['id'] . "\n";
                        echo "Tracking Number: " . $payload['tracking_number'] . "\n";
                        echo "Created At: " . $payload['created_at'] . "\n";
                        echo "Provider: " . $payload['provider'] . "\n";
                        echo "Carrier Code: " . $payload['carrier']['code'] . "\n";
                        echo "Carrier Name: " . $payload['carrier']['name'] . "\n";
                        echo "Service Code: " . $payload['carrier']['service']['code'] . "\n";
                        echo "Service Name: " . $payload['carrier']['service']['name'] . "\n";
                        echo "Packaging Code: " . $payload['carrier']['packaging']['code'] . "\n";
                        echo "Packaging Name: " . $payload['carrier']['packaging']['name'] . "\n";
                        echo "Currency: " . $payload['currency'] . "\n";
                        echo "Cost: " . $payload['cost'] . "\n";
                        echo "Tax: " . $payload['tax'] . "\n";
                        echo "Tracking Number: " . $payload['tracking_number'] . "\n";
                        echo "Tracking URL: " . $payload['tracking_url'] . "\n";
                        echo "Label Format: " . $payload['labelFile']['format'] . "\n";
                    */
                    $id = isset($payload['id']) ? $payload['id']  : '';
                    $created_at = isset($payload['created_at']) ? $payload['created_at'] : '';
                    $tracking_number = isset($payload['tracking_number']) ? $payload['tracking_number'] : '';
                    $carrierId = isset($payload['carrier']['id']) ? $payload['carrier']['id'] : '';
                    $carrierCode = isset($payload['carrier']['code']) ? $payload['carrier']['code'] : '';
                    $serviceCode = isset($payload['carrier']['service']['code']) ? $payload['carrier']['service']['code'] : '';
                    $serviceName = isset($payload['carrier']['service']['name']) ? $payload['carrier']['service']['name'] : '';
                    $shipment_cost = isset($payload['cost']) ? $payload['cost'] : '';
                    $tax = isset($payload['tax']) ? $payload['tax'] : '';
                    $label_data = isset($payload['labelFile']['data']) ? $payload['labelFile']['data'] : '';

                    //echo "carrierCode : " . $carrierCode . "--- serviceName : " . $serviceName .
                    //    "--- rateCost" .  $shipment_cost . "--- id:" .  $id . "--- tracking_number" .  $tracking_number .  "\n";
                    $item = [
                        'id' => $id,
                        'created_at' => $created_at,
                        'tracking_number' => $tracking_number,
                        'carrier_id' => $carrierId,
                        'carrier_code' => $carrierCode,
                        'service_code' => $serviceCode,
                        'service_name' => $serviceName,
                        'shipment_cost' => $shipment_cost,
                        'tax' => $tax,
                        'label_data' => $label_data

                    ];
                    $newResponse = $item;
                } else {
                    throw new Exception("Servis call for create label is not successed with error : " . $data['error']['message']);
                    error_log("Byelabel create label failed:" . $data['error']);
                }
                $this->logWebService('getSession:', $data, "");
                return $newResponse;
            } else {
                error_log("Byelabel create label failed with Status Code:"  . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('create label_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel create label failed with error: " . $e->getMessage());
            throw new Exception("Failed to create label. Error: " . $e->getMessage() . " with Status Code:" . $statusCode);
        }
    }





    public function voidLabel_FromByeLabel($tracking_number)
    {
        $session = $this->getSession();
        if ($tracking_number === "") {
            throw new Exception("Tracking number or label id is required");
        }

        $data = ['tracking_number' => $tracking_number];

        try {
            $response = $this->client->post(
                $this->BYELABEL_API_URL . '/void-label',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ],
                    'json' => $data
                ]
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);

                if ($data['success']) {
                    if (isset($data['payload'])) {
                        return $data;
                    } else {
                        echo "HATA PAYLOAD BOŞ DÖNDÜ: --- tracking_number:" . $tracking_number .  "\n";
                    }
                } else {
                    throw new Exception("Servis call for void label is not successed  with error :" . $data['error']['message']);
                    error_log("Byelabel void label list failed:" . $data['error']);
                }
                $this->logWebService('void label:', $data, "");
            } else {
                error_log("Byelabel void label failed with Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('void label_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel void label failed with error: " . $e->getMessage());
            throw new Exception("Failed to void label " . $e->getMessage());
        }
    }








    public function trackShipment_FromByeLabel($carrierCode, $trackingNumber)
    {
        if ($carrierCode === "" || $trackingNumber === "") {
            // burada parametreler boş sa şunu bunu yap diye kontroller olacak
            throw new Exception("carrierCode  veya trackingNumber  can not be null");
        }
        $session = $this->getSession();

        try {
            $response = $this->client->get(
                $this->BYELABEL_API_URL . "/track/" . $carrierCode . "/" . $trackingNumber,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ]
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $newResponse = null;
                $data = json_decode($response->getBody(), true);
                if ($data['success'] && isset($data['payload'])) {
                    $payload = $data['payload'];

                    $status = isset($payload['status']) ? $payload['status']  : '';
                    $delivered = isset($payload['delivered']) ? $payload['delivered'] : '';
                    $estimatedDeliveryDateTime = isset($payload['estimatedDeliveryDateTime']) ? $payload['estimatedDeliveryDateTime'] : '';
                    $package_count = isset($payload['package']['count']) ? $payload['package']['count'] : '';
                    $package_weight = isset($payload['package']['weight']['value']) ? $payload['package']['weight']['value'] : '';
                    $package_width = isset($payload['package']['dimensions']['width']) ? $payload['package']['dimensions']['width'] : '';

                    $item = [
                        'status' => $status,
                        'delivered' => $delivered,
                        'estimatedDeliveryDateTime' => $estimatedDeliveryDateTime,
                        'package_count' => $package_count,
                        'package_weight' => $package_weight,
                        'package_width' => $package_width,
                    ];
                    $newResponse = $item;
                } else {
                    throw new Exception("Servis call for trackShipment  is not successed  with error :" . $data['error']['message']);
                    error_log("Byelabel trackShipment failed:" . $data['error']);
                }
                $this->logWebService('trackShipment:', $data, "");
                return $newResponse;
            } else {
                error_log("Byelabel getting trackShipment failed with Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('trackShipment_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel getting trackShipment failed with error: " . $e->getMessage());
            throw new Exception("Failed to trackShipment " . $e->getMessage());
        }
    }


    // Generate session for Byelabel API
    public function getSession()
    {
        $data = [
            'client_id' => $this->BYELABEL_API_CLIENT_ID,
            'client_secret' => $this->BYELABEL_API_CLIENT_SECRET,
        ];

        try {
            $response = $this->client->post(
                $this->BYELABEL_API_URL . '/login',
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ]
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                if ($data['success']) {
                    $session = [
                        'id' => $data['payload']['id'],
                        'token' => $data['payload']['access']['token'],                     // kısa süreli token
                        'expires_in' => $data['payload']['access']['expires_in'],           //9000 ms
                        'refresh_token' => $data['payload']['refresh']['token'],            //uzun süreli token
                        'refresh_expires_in' => $data['payload']['refresh']['expires_in']   // 604800 ms
                    ];
                } else {
                    throw new Exception("Servis call is not successed  with error :" . $data['error']['message']);
                    error_log("Byelabel Session generation failed:" . $data['error']);
                }
                $this->logWebService('getSession:', $data, $session["id"]);
                //echo("get session başarılı TOKEN:" . $session["token"] );
                return $session;
            } else {
                error_log("Byelabel Session generation failed Status Code:" . $statusCode);
                echo ("Byelabel Session generation failed Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('getSession_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel Session generation failed: " . $e->getMessage());
            throw new Exception("Failed to generate session");
        }
    }


    public function refreshToken($eskiToken)
    {
        $data = [
            'token:' => $eskiToken
        ];

        try {
            $response = $this->client->post(
                $this->BYELABEL_API_URL . '/refresh-token ',
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ]
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                if ($data['success']) {
                    $session = [
                        'id' => $data['payload']['id'],
                        'token' => $data['payload']['access']['token'],                     // kısa süreli token
                        'expires_in' => $data['payload']['access']['expires_in'],           //9000 ms
                        'refresh_token' => $data['payload']['refresh']['token'],            //uzun süreli token
                        'refresh_expires_in' => $data['payload']['refresh']['expires_in']   // 604800 ms
                    ];
                } else {
                    error_log("Byelabel token refresh failed:" . $data['error']);
                }
                return $session;
            } else {
                error_log("Byelabel Session generation failed Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('getSession_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel Session generation failed: " . $e->getMessage());
            throw new Exception("Failed to refresh session");
        }
    }


    // Get the token for API calls
    public function getToken($uzunSureli = false)
    {
        $session = $this->getSession();
        if (!$session) {
            throw new Exception('Token not created');
        }
        if ($uzunSureli === true) {
            return $session['refresh_token'];       // daha uzun süreli istenirse 
        } else {
            return $session['token'];
        }
    }


    public function getcarriersList()
    {
        $session = $this->getSession();
        //echo ("session-Token:" . $session['token']);
        try {
            $response = $this->client->get(
                $this->BYELABEL_API_URL . '/carriers',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ]
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                $newResponse = [];
                if ($data['success']) {
                    //var_dump($data['payload']);
                    foreach ($data['payload'] as $carrier) {
                        // echo "Carrier: " . $carrier['code'] . ", name: " . $carrier['name'] . "\n"  ;
                        // foreach ($carrier['service'] as $service) {
                        //     echo "service" . $service['code'] . ", name: " . $service['name'] . "\n" ;
                        // }
                        // foreach ($carrier['packaging'] as $packaging) {
                        //     echo "packaging" . $packaging['code'] . ", name: " . $packaging['name'] . "\n" ;
                        // }
                        // dönen nesneyi özelleştir.
                        if (isset($carrier['service']) && is_array($carrier['service'])) {
                            foreach ($carrier['service'] as $service) {
                                if (isset($carrier['packaging']) && is_array($carrier['packaging'])) {
                                    foreach ($carrier['packaging'] as $packaging) {
                                        $newResponse[] = [
                                            'carrier_id' => isset($carrier['id']) ? $carrier['id'] : '',
                                            'carrier_code' => isset($carrier['code']) ? $carrier['code'] : '',
                                            'service_code' => isset($service['code']) ? $service['code'] : '',
                                            'service_name' => isset($service['name']) ? $service['name'] : '',
                                            'packaging_code' => isset($packaging['code']) ? $packaging['code'] : '',
                                            //'packaging_name' => isset($packaging['name']) ? $packaging['name'] : ''
                                        ];
                                    }
                                }
                            }
                        }
                    }
                } else {
                    throw new Exception("Servis call for getting carriers list is not successed  with error :" . $data['error']['message']);
                    error_log("Byelabel getting carriers list failed:" . $data['error']);
                }
                $this->logWebService('getcarriersList:', $data, "");
                //return $response;
                return $newResponse;
            } else {
                error_log("Byelabel getting carriers list failed with Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('getSession_ERROR: ' . $e->getMessage(), "", "");
            error_log("Byelabel getting carriers list failed with error: " . $e->getMessage());
            throw new Exception(" Hey Failed to getting carriers list " . $e->getMessage());
        }
    }


    public function getcountryList()
    {
        $session = $this->getSession();

        try {
            $response = $this->client->get(
                $this->BYELABEL_API_URL . '/lookup/country',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ]
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                if ($data['success']) {
                    foreach ($data['payload'] as $country) {
                        echo "country: " . $country['code'] . ", name: " . $country['name'] . "\n" .
                            "flag: " . $country['flag'] . ", currency: " . $country['currency']["code"] . $country['currency']["name"] .  "\n" .
                            "phone_code: " . $country['phone_code'] . ", order: " . $country['order'] . "\n";
                    }
                } else {
                    throw new Exception("Servis call for getting country list is not successed  with error :" . $data['error']['message']);
                    error_log("Byelabel getting country list failed:" . $data['error']);
                }
                $this->logWebService('getCountrylist:', $data, "");
                return $response;
            } else {
                error_log("Byelabel getting country list failed with Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('getSession_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel getting country list failed with error: " . $e->getMessage());
            throw new Exception("Hey Failed to getting country list" . $e->getMessage());
        }
    }


    public function getStateProvinceList($state)
    {
        if ($state === "") {
            // burada parametreler boş sa şunu bunu yap diye kontroller olacak
            throw new Exception("State  can not be null");
        }
        $session = $this->getSession();

        try {
            $response = $this->client->get(
                $this->BYELABEL_API_URL . '/lookup/state?parent_code=' . $state,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ]
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                if ($data['success']) {
                    foreach ($data['payload'] as $Province) {
                        echo "country: " . $Province['code'] . ", name: " . $Province['name'] . $Province['order'] . "\n";
                    }
                } else {
                    throw new Exception("Servis call for getting State-Province list is not successed  with error :" . $data['error']['message']);
                    error_log("Byelabel getting State-Province list failed:" . $data['error']);
                }
                $this->logWebService('getSession:', $data, "");
                return $response;
            } else {
                error_log("Byelabel getting State Province list failed with Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('getStateProvince_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel getting State-Province list failed with error: " . $e->getMessage());
            throw new Exception("Failed to getting State Province list" . $e->getMessage());
        }
    }


    public function getZipcodeToStateCity($country_code = 'US', $zip_code = '08110')
    {
        $session = $this->getSession();

        if ($zip_code === "") {
            // burada parametreler boş sa şunu bunu yap diye kontroller olacak
            $zip_code = "08110"; //geçici olarak boşsa
        }

        $data = [
            'country_code' => $country_code,
            'zip_code' => $zip_code,
        ];

        try {
            $response = $this->client->post(
                $this->BYELABEL_API_URL . '/zip-to-state',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' =>  $session['token'],
                        'X-Workspace-Id' => $this->WORKSPACE_ID
                    ],
                    'json' => $data // 'body' yerine 'json' parametresi kullanıldı
                ]
            );

            // Status code'u kontrol et
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = json_decode($response->getBody(), true);
                if ($data['success']) {
                    if (isset($data['payload'])) {
                        //var_dump($data['payload']);
                        //echo "state_code: " . $data['payload']['state_code'] . ", city: " . $data['payload']['city'] .  "\n";
                    } else {
                        echo "HATA PAYLOAD BOŞ DÖNDÜ: " . "country_code:" . $country_code . " --- zip_code:" . $zip_code .  "\n";
                    }
                } else {
                    throw new Exception("Servis call for getting State-Province list is not successed  with error :" . $data['error']['message']);
                    error_log("Byelabel getting State-Province list failed:" . $data['error']);
                }
                $this->logWebService('getSession:', $data, "");
                return $response;
            } else {
                error_log("Byelabel getting State Province list failed with Status Code:" . $statusCode);
            }
        } catch (Exception $e) {
            $this->logWebService('getZipCodeToStatecity_ERROR: ' . $e->getMessage(), $data, "");
            error_log("Byelabel getting State-Province list failed with error: " . $e->getMessage());
            throw new Exception("Failed to getting State Province list" . $e->getMessage());
        }
    }






    private function logWebService($action, $data, $transactionId)
    {
        // Log web service request
    }
}
