import { withTranslation } from "react-i18next";
import withRouter from '../Components/Common/withRouter';
import React, {useEffect, useState, useCallback} from "react";
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import {Button, Card, CardHeader, CardBody, Col, Input, Container, Label, Modal, Form, ModalBody, Row,Nav, NavItem, NavLink, TabContent, TabPane} from 'reactstrap';
import {MessageBox, saveAsXls} from './utils'
import { ScaleLoader }  from 'react-spinners';
import {getTokenCount} from "../services/service_deposit";
import * as date from './date';
import CountUp from "react-countup";
import {Link, useNavigate} from 'react-router-dom';
import {EmbeddedCheckout, EmbeddedCheckoutProvider} from "@stripe/react-stripe-js";
import smallImage9 from "../assets/images/back4.jpg";
import {loadStripe} from "@stripe/stripe-js";
import {getDeposit, getDepositList} from "../services/service_deposit";
import { useDeposit } from './DepositContext'; 
import {genarateApiToken} from "../services/service_user";
import {getHsCode} from "../services/service_hscode";
import * as utils from './utils';
import classnames from "classnames";
import { paid_update, stripe_pay } from '../services/service_stripe';
const stripePromise = loadStripe("pk_live_51ReiV92MpVVabidVbGOtz3s0HGzw5y15wxKUOAyuoUcekwn9RwdxhscKOYXQcFHqH6GVYeycHo62xlpBLq6IdEsk00xAZhRQ26")

function Dashboard1(props){
    const [data, setData] = useState([]);
    const [count, setCount] = useState([0,0]);
    const [user, setUser] = useState(null);
    const [loader, setLoader] = useState(false);
    const [dateRange, setDateRange] = useState([null, null]);
    const [tokenCount, setTokenCount] = useState([])
    const [selectedRadio, setSelectedRadio] = useState("");
    const [secim, setSecim] = useState("");
    const [selectedBox, setSelectedBox] = useState(4);
    const [islemModal, setIslemModal] = useState(false);
    const [editedRow, setEditedRow] = useState({});
    const [filter, setFilter] = useState("All");
    const [addAmount, setAddAmount] = useState(0);
    const [payModal, setPayModal] = useState(false); 
    const [description, setDescription] = useState("");  
    const [hscode, setHscode] = useState(""); 
    const [messageBox1, setMessageBox1] = useState(false); 
    const [activeTab, setActiveTab] = useState("PHP");
    const navigate = useNavigate();
        





useEffect(() => {
    const authuser = sessionStorage.getItem("authUser")
    const user = JSON.parse(authuser);
    setUser(user)
  
    const today = date.getToday()
    setDateRange([date.getFirstDay(today), date.getLastDay(today)]);
}, []);


useEffect(() => {

        if (user && selectedRadio !== "") {
           // of_checkSelect(selectedRadio)
        }
}, [selectedRadio]);




useEffect(() => {
if (user && user.token){    
    of_getCounts()     
}
},[user]);


useEffect(() => {
    fetchClientSecret(); 
}, [addAmount]);



// yukarida selected order set edilir ve setPay true yapilursa odeme sayfasini ac
const fetchClientSecret = useCallback(() => {
    if (addAmount > 0) {
        return stripe_pay(addAmount, user.token).then((response) => {
            // alert(response)
            return (response.clientSecret);
        }).catch(error => NotificationManager.error(error, "", 4000));
    }
}, [addAmount]);


function of_findHSCode(){
    getHsCode( description, user.token).then((response) => {
         //alert(JSON.stringify(response))
        if (response.success === false){
            NotificationManager.error(response.message, "", 4000);
            return
        }
        const data = response.HsCode;
        const firstSuggestion = data && !isNaN(Number(data)) ? data : null;

        console.log("hscode: " + firstSuggestion);
        //alert(firstSuggestion);
        of_creditUsed() 
        NotificationManager.success("HSCode --> " + firstSuggestion, "", 4000)  
        setHscode(firstSuggestion);
        setMessageBox1(true)
         
    }).catch(error => {NotificationManager.error(error,'Error')}); 
}

function of_getCounts(){
    if(dateRange[1] === undefined){
        return
    }

    //alert(user.token)
    getTokenCount( user.id, user.token).then((response) => {
         setCount(response);
    }).catch(error => {NotificationManager.error(error,'Error');setLoader(false)}); 
}

const of_creditUsed = () => {
    setCount(prev => [
    {
        ...prev[0],
        used_token_count: parseInt(prev[0].used_token_count) + 1
    },
    ...prev.slice(1)
    ]);
};

function of_genarateApi(){
    genarateApiToken( user.token).then((response) => {
        setUser(prevUser => ({...prevUser, api_token: response }));
    }).catch(error => {NotificationManager.error(error,'Error');});    
}

function of_islem(row){
    setEditedRow(row)
    setIslemModal(true)
}

function of_close(){

        paid_update("x", user.token).then((response) => {
            navigate('/dashboard1/?reload=true', {state: {fromOrder: "YES"}})
            //refreshDeposit()
        }).catch(error => NotificationManager.error(error,"", 4000));

    setPayModal(false)
}

const handleChange = (colname) => (e) => {
    setEditedRow({ ...editedRow, [colname]: e.target.value,
    });
};




const codeSamples = 
  {
    PHP: `<?php

$product_description = "baby doll";
$api_token = "YOUR API TOKEN";
$url = "https://hsmaster.ai/api/v1/hscode-finder/description/" . urlencode($product_description);

$ch = curl_init();
$headers = [
    "Authorization: Bearer $api_token",
    "Referer: https://yoursite.com"
];

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(["error" => "API request failed.", "details" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);
$jsonResponse = json_decode($response, true);

if ($jsonResponse === null) {
    echo json_encode(["error" => "Invalid JSON response received.", "rawData" => $response]);
} else {
    echo json_encode($jsonResponse, JSON_PRETTY_PRINT);
}`
 ,
    JavaScript: `import axios from 'axios';

const productDescription = "baby doll";
const apiToken = "YOUR API TOKEN";

const fetchHSCode = async () => {
  try {
    const response = await axios.get(
      \`https://hsmaster.ai/api/v1/hscode-finder/description/\${encodeURIComponent(productDescription)}\`,
      {
        headers: {
          Authorization: \`Bearer \${apiToken}\`,
          Referer: 'https://yoursite.com'
        }
      }
    );

    if (response.data) {
      console.log("API response:", response.data);
    } else {
      console.error("Invalid JSON response");
    }
  } catch (error) {
    console.error("API request failed:", error.message);
  }
};`
,
    Python: `import requests
import urllib.parse

product_description = "baby doll"
api_token = "YOUR API TOKEN"
encoded_description = urllib.parse.quote(product_description)
url = f"https://hsmaster.ai/api/v1/hscode-finder/description/{encoded_description}"

headers = {
    "Authorization": f"Bearer {api_token}",
    "Referer": "https://yoursite.com"
}

try:
    response = requests.get(url, headers=headers)
    response.raise_for_status()
    json_response = response.json()
    print("API response:", json_response)

except requests.exceptions.HTTPError as http_err:
    print({"error": "API request failed.", "details": str(http_err)})
except ValueError:
    print({"error": "Invalid JSON response", "rawData": response.text})
except Exception as err:
    print({"error": "Unexpected error occurred.", "details": str(err)})`

    ,"C#": `using System;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Threading.Tasks;

class Program {
    static async Task Main() {
        string productDescription = "baby doll";
        string apiToken = "YOUR API TOKEN";
        string encodedDescription = Uri.EscapeDataString(productDescription);
        string url = $"https://hsmaster.ai/api/v1/hscode-finder/description/{encodedDescription}";

        using (HttpClient client = new HttpClient()) {
            client.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", apiToken);
            client.DefaultRequestHeaders.Referrer = new Uri("https://yoursite.com");

            try {
                HttpResponseMessage response = await client.GetAsync(url);
                response.EnsureSuccessStatusCode();
                string jsonResponse = await response.Content.ReadAsStringAsync();
                Console.WriteLine("API response: " + jsonResponse);
            } catch (HttpRequestException httpEx) {
                Console.WriteLine($"API request failed. Details: {httpEx.Message}");
            } catch (Exception ex) {
                Console.WriteLine($"Unexpected error occurred. Details: {ex.Message}");
            }
        }
    }
}`
 
    ,Java: `import okhttp3.*;

public class HsCodeFetcher {
    public static void main(String[] args) {
        String productDescription = "baby doll";
        String apiToken = "YOUR API TOKEN";
        String url = "https://hsmaster.ai/api/v1/hscode-finder/description/" +
                     java.net.URLEncoder.encode(productDescription, java.nio.charset.StandardCharsets.UTF_8);

        OkHttpClient client = new OkHttpClient();

        Request request = new Request.Builder()
            .url(url)
            .addHeader("Authorization", "Bearer " + apiToken)
            .addHeader("Referer", "https://yoursite.com")
            .get()
            .build();

        try (Response response = client.newCall(request).execute()) {
            if (!response.isSuccessful()) {
                System.out.println("API request failed: " + response.code());
            } else {
                System.out.println("API response: " + response.body().string());
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}`
  , Ruby:  `require 'net/http'
require 'uri'
require 'json'

product_description = "baby doll"
api_token = "YOUR API TOKEN"

encoded_desc = URI.encode_www_form_component(product_description)
url = URI("https://hsmaster.ai/api/v1/hscode-finder/description/#{encoded_desc}")

http = Net::HTTP.new(url.host, url.port)
http.use_ssl = true

request = Net::HTTP::Get.new(url)
request["Authorization"] = "Bearer #{api_token}"
request["Referer"] = "https://yoursite.com"

begin
  response = http.request(request)
  if response.code == "200"
    json_response = JSON.parse(response.body)
    puts "API response: #{json_response}"
  else
    puts "API request failed: #{response.code}"
  end
rescue => e
  puts "Error: #{e.message}"
end`
}

  





  
  const tabChange = (lang) => {
    setActiveTab(lang);
  };

  
return (
<React.Fragment>


   <div className="modal fade" id="ss" aria-hidden="true">
        <div className="modal-dialog modal-dialog-centered modal-xlg">
            <Modal isOpen={loader} centered style={{ maxWidth: '90px', width: '80%' }}>
                <ModalBody>
                    <ScaleLoader color="#36d7b7" />
                </ModalBody>
            </Modal>
        </div>
    </div>




 
 <div className="page-content">
 <Container fluid>
      
      {messageBox1===true && 
 <utils.MessageBox
      show={messageBox1}
      onConfirmClick={setMessageBox1(false)}
      ConfirmLabel={props.t("Yes")}
       CloseLabel={props.t("No")}
      message={props.t("Your HS code is" + " " + hscode)}
      onCloseClick={() => setMessageBox1(false)}
    />
      }


    <Row>
    <Col xl={4} md={6}>
    <Card style={{ background: 'linear-gradient(150deg,rgba(243, 184, 114, 1)0%,rgba(229, 184, 22, 1) 60%,rgba(208, 157, 16, 1) 100%)'}} className="card-animate">
    <CardBody onClick={(e)=> setSelectedBox(1)}>
                <div className="d-flex align-items-center">
                    <div className="flex-grow-1 overflow-hidden">
                    <h4  className={` text-uppercase fw-20 text-truncate mb-0`}>{"Total Query Credits"}</h4>
 
                    </div>
                    <div className="d-flex flex-column">
                    <div className="flex-shrink-0">
                        <h5 className={"fs-14 mb-0 text-success"}>                                             
                            <i className={"fs-13 align-middle " + "ri-arrow-right-up-line"}></i> {parseInt(count[0].total_token_count)} %
                        </h5>
                    </div>
                    <div className="flex-shrink-0">
                        <h5 className={"fs-14 mb-0 text-success"}>  
                            <i className={"fs-13 align-middle " + "ri-arrow-right-up-line"}></i> 
                        </h5>
                    </div>
                    </div>
                </div>
                <div className="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 className={`fs-18 fw-semibold ff-primary mb-4`}><span className="counter-value" data-target="559.25">
                            <CountUp
                                start={0}
                                prefix={""}
                                suffix={""}
                                separator={""}
                                end={count[0].total_token_count}
                                decimals={0}
                                duration={1}
                            />
                        </span></h4>
                        <Link to="#" disabled className="text-decoration-underline">&nbsp;</Link>
                    </div>
                    <div className="avatar-sm flex-shrink-0">
                        <span className={"avatar-title rounded fs-3 bg-soft-primary"}>
                        <i className={"text-primary bx bx-shopping-bag"}></i>
                        </span>
                    </div>
                </div>
            </CardBody>
        </Card>
    </Col>

    <Col xl={4} md={6}>
        <Card style={{ background: 'linear-gradient(150deg, rgba(240, 136, 115, 1)0%,rgba(229, 39, 22, 1) 60%,rgba(182, 51, 12, 1) 100%)'}} className="card-animate">
         <CardBody onClick={(e)=> setSelectedBox(2)}>
                <div className="d-flex align-items-center">
                    <div className="flex-grow-1 overflow-hidden">
                    <h4  className={` text-uppercase fw-20 text-truncate mb-0"}`}>{"Queries Used"}</h4>
 
                    </div>
                    <div className="d-flex flex-column">
                    <div className="flex-shrink-0">
                        <h5 className={"fs-14 mb-0 text-success"}>
                            <i className={"fs-13 align-middle " + "ri-arrow-right-up-line"}></i> {parseInt(count[0].used_token_count)} %
                        </h5>
                    </div>
                     <div className="flex-shrink-0">
                        <h5 className={"fs-14 mb-0 text-success"}>
                            <i className={"fs-13 align-middle " + "ri-arrow-right-up-line"}></i> 
                        </h5>
                    </div>
                    </div>
                </div>
                <div className="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 className={`fs-18 fw-semibold ff-primary mb-4`}><span className="counter-value" data-target="559.25">
                            <CountUp
                                start={0}
                                prefix={""}
                                suffix={""}
                                separator={""}
                                end={count[0].used_token_count}
                                decimals={0}
                                duration={1}
                            />
                        </span></h4>
                        <Link to="#" disabled className="text-decoration-underline">&nbsp;</Link>
                    </div>
                    <div className="avatar-sm flex-shrink-0">
                        <span className={"avatar-title rounded fs-3 bg-soft-warning"}>
                            <i className={"text-warning bx bx-shopping-bag"}></i>
                        </span>
                    </div>
                </div>
            </CardBody>
        </Card>
    </Col>

    <Col xl={4} md={6}>
        <Card className="card-animate" style={{background: 'linear-gradient(150deg,rgba(115, 235, 109, 1) 0%,rgba(9, 168, 1, 1) 50%, rgba(15, 150, 8, 1) 100%)'}}>
            <CardBody onClick={(e)=> setSelectedBox(3)}>
                           <div className="d-flex align-items-center">
                    <div className="flex-grow-1 overflow-hidden">
                    <h4  className={` text-uppercase fw-20 text-truncate mb-0 text-muted"}`}>{"Remaining Query Credits"}</h4>
 
                    </div>
                    <div className="d-flex flex-column">
                    <div className="flex-shrink-0">
                        <h5 className={"fs-14 mb-0 text-success"}>                                             
                            <i className={"fs-13 align-middle " + "ri-arrow-right-up-line"}></i> {parseInt(count[0].total_token_count) - parseInt(count[0].used_token_count)} %
                        </h5>
                    </div>
                    <div className="flex-shrink-0">
                        <h5 className={"fs-14 mb-0 text-success"}>  
                            <i className={"fs-13 align-middle " + "ri-arrow-right-up-line"}></i> 
                        </h5>
                    </div>
                    </div>
                </div>
                <div className="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 className={`fs-18 fw-semibold ff-primary mb-4`}><span className="counter-value" data-target="559.25">
                            <CountUp
                                start={0}
                                prefix={""}
                                suffix={""}
                                separator={""}
                                end={parseInt(count[0].total_token_count) - parseInt(count[0].used_token_count)}
                                decimals={0}
                                duration={1}
                            />
                        </span></h4>
                        <Link to="#" disabled className="text-decoration-underline">&nbsp;</Link>
                    </div>
                    <div className="avatar-sm flex-shrink-0">
                        <span className={"avatar-title rounded fs-3 bg-soft-primary"}>
                        <i className={"text-primary bx bx-shopping-bag"}></i>
                        </span>
                    </div>
                </div>
            </CardBody>
        </Card>
    </Col>


    </Row>


<Row className="align-items-start mb-3 mt-4">
  <Card className="card-animate mt-2">
    <CardBody>


      <div className="d-flex justify-content-end align-items-center gap-2 mt-2 mb-2">
         <Input type="textarea" className="form-control" id="messageinput" 
                        rows="2"
                        placeholder = {props.t("Enter your product description")}
                        onChange={e => setDescription(e.target.value)}/>
        <button  type="button"
          className="btn btn-success"  style={{ width: '170px' }} id="search" onClick={() => of_findHSCode()}
        >
          {props.t("Find HS Code")}
        </button>
      </div>
    </CardBody>
  </Card>
</Row>

  <Row className="align-items-start mb-3">
  <Card className="card-animate mt-2">
    <CardBody>


      <div className="d-flex justify-content-end align-items-center gap-2  mt-2 mb-2">

        <Label htmlFor="addamountdeposit" className="form-label me-2">
        {props.t("Credits to Buy (1 credit = 1 query, $0.05 each)")}
      </Label>
        <Input
          id="addamountdeposit"
          type="number"
          min="0"
          style={{ width: '100px', textAlign: 'right' }}
          onBlur={e => setAddAmount(parseFloat(e.target.value) || 0)}
        />
        <span id="idorder" className="" style={{ width: '100px' }}>
          {"$" + ((addAmount * 5) / 100).toFixed(2)}
        </span>
        <button  type="button"
          className="btn btn-success"  style={{ width: '150px' }} id="save" onClick={() => setPayModal(true)}
        >
          {props.t("Pay")}
        </button>
      </div>
    </CardBody>
  </Card>
</Row>



    
  <Row className="align-items-start mb-3">
  <Card className="card-animate mt-2">
    <CardBody>


      <div className="d-flex justify-content-end align-items-center gap-2  mt-2 mb-2">

        <Label htmlFor="addamountdeposit" className="form-label me-2">
        {props.t("Your API token")}
      </Label>
        
         <Input
          id="addamountdeposit"
          type="text"
          disabled
          style={{ width: '400px', textAlign: 'right' }}
          value={user? user.api_token :""}
          onBlur={e => setAddAmount(parseFloat(e.target.value) || 0)}
        />
        <button  type="button"
          className="btn btn-success"  style={{ width: '150px' }} id="Genarete" onClick={() => of_genarateApi()}
        >
          {props.t("Genarate New")}
        </button>
      </div>
    </CardBody>
  </Card>
</Row>


 <Row className="align-items-start mb-3">
  <Card className="mt-3">

    <div className="flex-shrink-0 mt-4 ms-4">
        <h5 className={"fs-14 mb-0 text-success"}>
           {props.t("Sample API Codes")}
        </h5>
    </div>


      <CardHeader>
        <Nav
          className="nav-tabs-custom rounded card-header-tabs border-bottom-0"
          role="tablist"
        >
          {Object.keys(codeSamples).map((lang) => (
            <NavItem key={lang}>
              <NavLink
                className={classnames({ active: activeTab === lang })}
                onClick={() => tabChange(lang)}
                role="button"
              >
                {lang}
              </NavLink>
            </NavItem>
          ))}
        </Nav>
      </CardHeader>
     <CardBody className="p-4">
        <TabContent activeTab={activeTab}>
          <TabPane tabId={activeTab}>
            <Input
              type="textarea"
              className="form-control"
              rows="15"
              value={codeSamples[activeTab]}
              readOnly
            />
          </TabPane>
        </TabContent>
      </CardBody>
    </Card>
</Row>



 
    </Container>
    </div>



    <Modal isOpen={islemModal}  centered style={{maxWidth: '600px', maxHeight: '10vh', overflowY: 'auto'}} >
            <ModalBody className="bg-light"  style={{ maxHeight: '600px', overflowY: 'auto', padding: '20px', boxSizing: 'border-box' }}>
            
            <Form>
                    <Row>
                        <Col lg={12}>
                            <input type="hidden" id="memberid-input" className="form-control" defaultValue="" />
                            <div className=" px-1 pt-1 ">
                                <div className="modal-team-cover position-relative mb-0 mt-n4 mx-n4 rounded-top overflow-hidden">
                                    <img src={smallImage9} alt="" id="cover-img" className="img-fluid" />

                                    <div className="d-flex position-absolute start-0 end-0 top-0 p-3">
                                        <div className="flex-grow-1">
                                            <h5 className="modal-title text-white" id="createMemberLabel"></h5>
                                        </div>
                                        <div className="d-flex position-absolute start-0 top-0  p-3">
                                        <span id="idorder" className="fs-18 fw-semibold text-black" >
                                            {"Kar-Zarar :" + (parseFloat(editedRow.invoice_amount || "0") - (parseFloat(editedRow.kargo_bedeli || "0") + parseFloat(editedRow.gumruk || "0") + parseFloat(editedRow.custom_invoice || "0"))).toFixed(2)}
                                        </span>
                                        </div>
                                        <div className="">
                                        <span id="idorder" className="fs-18 fw-semibold text-black" >
                                                {"Tracking number :" + editedRow.tracking_number}
                                        </span>
                                        </div>
                            
                                        <div className="flex-shrink-0">
                                            <div className="d-flex gap-3 align-items-center">
                                                <button type="button" className="btn-close btn-close-white"
                                                        onClick={() => of_close()} id="closebtn"
                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </Col>
                    </Row>

                <Row>
                    <Col lg={12}>
                        <div className="mt-4">
                            <div className="input-group ">
                                
                                <Input type="textarea" className="form-control" id="islem_aciklama" 
                                value = {editedRow.islem_aciklama} rows="4"
                                onChange={handleChange('islem_aciklama')}/>
                                </div>
                            </div>
                        </Col>
                </Row>

            
                    


                <Row>
                        <Col lg={12}>
                            <div className="hstack gap-2 justify-content-end mt-4">
                                <button type="button" className="btn btn-success" id="save" onClick={() => alert("")}>{"Save"}</button>
                                <button type="button" className="btn btn-light" id="close" onClick={() => of_close()}>{"Close"}</button>
                            </div>
                        </Col>
                </Row>
            </Form>
            
            </ModalBody>
    </Modal>


    <Modal isOpen={payModal} centered style={{ maxWidth: '600px' }}>
        <ModalBody style={{ maxHeight: '600px', overflowY: 'auto', padding: '20px', boxSizing: 'border-box' }}>

            <Row>
                <Col lg={12}>
                    <div className="px-1 pt-1 ">
                        <div className="modal-team-cover position-relative mb-0 mt-n4 mx-n4 rounded-top overflow-hidden">
                            <img src={smallImage9} alt="" id="cover-img" className="img-fluid" />
                            <div className="d-flex position-absolute start-0 end-0 top-0 p-3">
                                <div className="flex-grow-1">
                                </div>
                                <div className="flex-shrink-0">
                                    <div className="d-flex gap-3 align-items-center">
                                        <button type="button" className="btn-close btn-close-white" onClick={() => of_close()} id="createMemberBtn-close" data-bs-dismiss="modal" aria-label={props.t("Close")}></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Col>
            </Row>

            
            <div style={{ width: '100%', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                <EmbeddedCheckoutProvider
                stripe={stripePromise}
                options={{fetchClientSecret}}>
                <EmbeddedCheckout/>
                </EmbeddedCheckoutProvider>         
            </div>
  
        </ModalBody>
    </Modal>

    <NotificationContainer/>
               
</React.Fragment>
)}


export default withRouter(withTranslation()(Dashboard1));