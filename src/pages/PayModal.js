import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import React, {useEffect, useState} from "react";
import {Button, Card, CardBody, Col, Row} from 'reactstrap';
import smallImage9 from '../assets/images/back4.jpg';
import {Modal, ModalBody} from 'reactstrap';
import {EmbeddedCheckout, EmbeddedCheckoutProvider} from "@stripe/react-stripe-js";
import * as utils from "../utils";
import {paid_update, stripe_checkout} from "../services/service_stripe";
import {getDeposit} from "../services/service_deposit";
import {loadStripe} from '@stripe/stripe-js';
import { useDeposit } from '../DepositContext';
import { getResetToken} from '../services/service_user';
const stripePromise = loadStripe("pk_live_51QWPQTFQ6YrUwLJ4cwK4RCqCVytD3nozMjry7tj8H8yaVY8huRwlhWJapMaKiuWFpFJ07cSjxhTBNM1GKUY99QAT00M1wXUYaA");
 

function PayModal(props){
    const [deposit, setDeposit] = useState(null);
    const [finalPayment, setFinalPayment] = useState(0);
    const [loader, setLoader] = useState(false);
    const [type, setType] = useState(null);
    const { refreshDeposit } = useDeposit();   

    //sayfa yuklenirken kullanicinin deposutosunu al
    useEffect(() => {
        of_getDeposit(props.user.id, props.user.token)
    }, [props.user.id]);

    //deposit oku
    function of_getDeposit(user_id, token) {
        getDeposit(user_id, token).then((response) => {
            setDeposit(parseFloat(response[0].deposit))

            const shipmentDifference = utils.of_getCalculatedShipmentCost(props.ordersData) - utils.of_getShipmentCost(props.ordersData) 
            const productDifference = utils.of_getProductDifference(props.ordersData)
            const returnedProductPriceTotal = props.ordersData.you_buy !== "2" ?0 :parseFloat(props.ordersData.returned_total || "0") 
            const returnCost = parseFloat(props.ordersData.return_cost || "0")
            const finalPayment =    shipmentDifference + returnCost -  returnedProductPriceTotal



            if (finalPayment > 0){
                setFinalPayment(finalPayment)
                setType('final')
      

            }else if (productDifference > 0) {
                setFinalPayment(productDifference)
                setType('additional')
            }

                   
        }).catch(error => NotificationManager.error(error, "", 4000));
    }


    
async function of_payFromDeposit() {
    try {
        // Önce getDeposit çağrısını bekliyoruz
        const response = await getDeposit(props.user.id, props.user.token);
        const _deposit = parseFloat(response[0].deposit);

        // Burada _deposit güncellendikten sonra kontrol yapılıyor
        if (_deposit >= deposit) {
            if (deposit + 1 >= finalPayment) {
                getResetToken(props.user.token).then((response) => {
                    // Ödeme güncelleme işlemini bekliyoruz
                    paid_update(props.ordersData.id, type, response[0].resettoken, props.user.token).then((response) => {
                        props.setPayModal(false);
                        props.of_getOrders(props.user);
                        refreshDeposit();
                    })
                })
            }
        }else{
            NotificationManager.error(props.t("Your depozit has been changed. Check your deposit and try again"), "", 4000);
        }
        
    } catch (error) {
        // Hata durumunda bildirim
        NotificationManager.error(error, "", 4000);
    }
}
                 
    function fetchClientSecret() {
        setLoader(true)

        const productDifference = utils.of_getProductDifference(props.ordersData)
        const shipmentDifference = utils.of_getCalculatedShipmentCost(props.ordersData) - utils.of_getShipmentCost(props.ordersData) 
        const returnCost = parseFloat(props.ordersData.return_cost || "0")
        const returnedProductPriceTotal = props.ordersData.you_buy !== "2" ?0 :parseFloat(props.ordersData.returned_total || "0") 
        const finalPayment = shipmentDifference + returnCost - returnedProductPriceTotal  
                 


        if (finalPayment > 0){
            return stripe_checkout(props.ordersData.id,"final", props.user.token).then((response) => {
                setLoader(false)

                //fiyat 0 ise clientSecret yerine token geliyor ve sonunda $3 var, serverdan da 0 oldugunu teyit ettik
                if (response.clientSecret.endsWith('$3')) {
                        //const total = 0;      
                        //setTotal(total);
                        const newToken = response.clientSecret.slice(0, -2)
                        //setNewToken(newToken)
                        return newToken;
                  }

                return(response.clientSecret);
            }).catch(error => NotificationManager.error(error,"", 4000));
        }else if (productDifference > 0) {
            return stripe_checkout(props.ordersData.id, "additional", props.user.token).then((response) => {
                setLoader(false);
                return(response.clientSecret);
            }).catch((error) => { NotificationManager.error(error, "", 4000);setLoader(false); });
        }
     }




  function of_close(){
    props.setPayModal(false)
  }


  return (
  <React.Fragment>
      <Modal isOpen={props.payModal} centered style={{ maxWidth: '600px' }}>
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
                                          <button type="button" className="btn-close btn-close-white" onClick={() => of_close() } id="createMemberBtn-close" data-bs-dismiss="modal" aria-label={props.t("Close")}></button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </Col>
              </Row>

              {finalPayment && deposit + 1 >= finalPayment
              ? <div className="justify-content-center">
                <Card className="mx-auto mb-3 mt-4" style={{ width: '550px', height: '360px',minWidth: '450px', overflow: 'auto' }}>
                    <CardBody>

                    <Row>
                        <Col lg={12}>
                            <div className="d-flex align-items-center flex-wrap mt-5 justify-content-center">
                                <span className="mb-2 fs-20 fw-semibold d-block bold" style={{ textAlign: 'center' }}>
                                    {props.t("{{amount}} will be paid from your deposit", { amount: "$" + parseFloat(finalPayment).toFixed(2) })}
                                </span>
                            </div>
                        </Col>
                    </Row>

                    <Row>
                        <Col lg={12}>
                            <div className="d-flex flex-wrap justify-content-center">
                                <span className="fs-14 mt-2 d-block" style={{color : 'green'}}>
                                    {props.t("Your new deposit will be {{amount}}", { amount: "$" + (deposit - finalPayment).toFixed(2) })}
                                </span>
                            </div>
                        </Col>
                    </Row>

                        <Row>
                            <Col lg={12}>
                                <div className="d-flex align-items-center flex-wrap mt-5 justify-content-center">
                                    <Button  className="btn btn-primary btn-lg fw-bold"  onClick={() => of_payFromDeposit()}>{props.t("Pay")}</Button>
                                </div>
                            </Col>
                        </Row>

                    </CardBody>
                </Card>
            </div>


              :<div style={{ width: '100%', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                  <EmbeddedCheckoutProvider
                    stripe={stripePromise}
                    options={{fetchClientSecret}}>
                    <EmbeddedCheckout/>
                 </EmbeddedCheckoutProvider>         

              </div>
              }

                {type === "final" &&
                    <span className="fs-12 mt-2 d-block" style={{color : 'red'}}>
                        {props.t("Once this final payment is made, you will no longer be able to request a return for any items.")}
                    </span>
                }
          </ModalBody>
      </Modal>

      <NotificationContainer/>
    </React.Fragment>

    );

  }


export default withRouter(withTranslation()(PayModal));



