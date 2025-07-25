
import { withTranslation } from "react-i18next";
import withRouter from '../Components/Common/withRouter';
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import React, {useCallback, useState,useEffect} from "react";
import { Col, Row, Card, CardBody} from 'reactstrap';
import { Input,Modal, ModalBody} from 'reactstrap';
import {EmbeddedCheckout, EmbeddedCheckoutProvider} from "@stripe/react-stripe-js";
import smallImage9 from "../assets/images/back4.jpg";
import {loadStripe} from "@stripe/stripe-js";
import { UncontrolledTooltip } from 'reactstrap';
import {getDeposit, getDepositList} from "../services/service_deposit";
import { useDeposit } from './DepositContext'; 
import {stripe_pay} from "../services/service_stripe";
import * as utils from './utils';
import { useNavigate} from 'react-router-dom';
const stripePromise = loadStripe("pk_live_51QWPQTFQ6YrUwLJ4cwK4RCqCVytD3nozMjry7tj8H8yaVY8huRwlhWJapMaKiuWFpFJ07cSjxhTBNM1GKUY99QAT00M1wXUYaA");





function DepositModal(props){
    const [user, setUser] = useState(null);
    const [depositList, setDepositList] = useState([]);
    const [deposit, setDeposit] = useState(null);
    const [addAmount, setAddAmount] = useState("");
    const [showStripe, setShowStripe] = useState(false);
    const { refreshDeposit } = useDeposit();  
    const navigate = useNavigate();

    useEffect(() => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        setUser(user)

        of_getDepositList(user)
        of_getDeposit(user)
        refreshDeposit()
      }, [props.depositModal]);


    function of_getDepositList( user){
        getDepositList(user.id, user.token).then((response) => {
            setDepositList(response);
            setAddAmount("")
        }).catch(error => NotificationManager.error(error,"", 4000));
    }

    function of_getDeposit(user) {
        getDeposit(user.id, user.token).then((response) => {
            setDeposit(response[0].deposit || 0)
            setAddAmount("")
        }).catch(error => NotificationManager.error(error, "", 4000));
    }

    // yukarida selected order set edilir ve setPay true yapilursa odeme sayfasini ac
    const fetchClientSecret = useCallback(() => {
    
        if (addAmount > 0) {
            return stripe_pay(addAmount, props.user.token).then((response) => {
                alert(response.clientSecret)
                return (response.clientSecret);
            }).catch(error => NotificationManager.error(error, "", 4000));
        }
    }, [ addAmount]);


    function of_close(){
        setShowStripe(false)
        props.setDepositModal(false)
    }



  return (
  <React.Fragment>
     { props.depositModal === true &&
        <Modal isOpen={props.depositModal } centered style={{ maxWidth: '630px' }}>
            <ModalBody className="bg-light" style={{ maxHeight: '650px', overflowY: 'auto', padding: '10px', boxSizing: 'border-box' }}>

                <Row>
                    <Col lg={12}>
                        <div className="bg-light   px-1 pt-1 ">
                            <div className="modal-team-cover position-relative mb-0 mt-n4 mx-n4 rounded-top overflow-hidden">
                                <img src={smallImage9} alt="" id="cover-img" className="img-fluid" />
                                <div className="d-flex position-absolute start-0 end-0 top-0 p-3">
                                    <div className="flex-grow-1">
                                    </div>
                                    <div className="flex-shrink-0">
                                        <div className="d-flex gap-3 align-items-center">
                                            <button type="button" className="btn-close btn-close-white" onClick={() => of_close() }
                                                    id="btnclose" data-bs-dismiss="modal" aria-label={props.t("Close")}></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Col>
                </Row>

                {showStripe === false ?
                    <div style={{ overflowX: 'hidden', paddingTop: '10px' }}>
                    <Card className=" mx-auto mt-3 me-2 ms-2" style={{ height: '140px' }}>
                        <CardBody style={{ paddingTop: '10px' }}> {/* Added padding to the top of CardBody */}
                            <table className="table me-2 ms-2" style={{ lineHeight: 1, marginTop: '10px' }}> {/* Added marginTop to the table */}
                                <tbody>
                                    <tr>
                                        <td>{props.t("Your current deposit")} :</td>
                                        <td id="deposit" className="text-end">${deposit}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ verticalAlign: 'middle' }}>
                                            {props.t("How much do you want to add $")} :
                                        </td>
                                        <td style={{
                                            display: 'flex',
                                            justifyContent: 'flex-end',
                                            alignItems: 'center', color:'green'
                                        }}>
                                            <Input id="addamountdeposit" 
                                                type="number" min="0"
                                                style={{ width: '100px', textAlign: 'right' }}
                                                value={addAmount}
                                                onChange={e => setAddAmount(parseFloat(e.target.value))}
                                            />
                                        </td>
                                    </tr>
                
                                    
                                </tbody>
                            </table>
                        </CardBody>
                    </Card>
                
                    <Card className="mx-auto mt-2 me-2 ms-2" style={{ height: '180px', overflow: 'auto' }}>
                        <CardBody>
                            <Row>
                                <Col lg={12}>
                                    <div className="product-list" style={{ maxHeight: '200px', overflowY: 'auto' }}>
                                        <span className="fw-semibold" style={{ marginLeft: '5px' }}>
                                            {props.t("Deposit History")}
                                        </span>
                                        <table className="table mt-1">
                                            <tbody>
                                                {(depositList || []).map((row, key) => (
                                                    <tr key={row.id} style={{ height: '25px', lineHeight: '1.2' }}>
                                                        <td style={{ padding: '4px 6px', whiteSpace: 'nowrap', width: '120px' }}>
                                                            <span id="date" style={{ fontSize: '14px' }}>
                                                            {utils.getLocalTime(row.date).toLocaleDateString()}
                                                            </span>
                                                        </td>
                                                        <td style={{ padding: '4px 6px', width: '100px', color:row.amount < 0 ? 'red' : 'green' }}>
                                                            <span id="amount" style={{ fontSize: '14px' }}>${row.amount}</span>
                                                        </td>
                                                        <td style={{ padding: '4px 6px', whiteSpace: 'nowrap' }}>
                                                            <UncontrolledTooltip placement="bottom" target={"notesInfo" + row.id}>
                                                                {row.description ? props.t(row.description?.replace(/^JB-\d*\s/, "")) : ""}
                                                            </UncontrolledTooltip>
                                                            <span id={"notesInfo" + row.id} style={{ fontSize: '14px' }}>
                                                                {props.t(row.description?.replace(/^JB-\d*\s/, "") || "")}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </Col>
                            </Row>
                        </CardBody>
                    </Card>
            
                         

                    <Row>
                            <Col lg={12}>
                                <div className="hstack gap-2 justify-content-end">
                                    {props.user.email==="cat.ngs@yahoo.com" || props.user.email==="muratyelkovan@yahoo.com"   ?
                                    <button type="button" className="btn btn btn-success mb-2" id="selectAbone" onClick={() => {navigate(`/subscriptionselect`) ; of_close()}}>{props.t("Abonelik Satın Al")}</button>
                                        : null
                                    }
                                    <button type="button" className="btn btn btn-success mb-2" id="add" 
                                            onClick={() => parseFloat(addAmount) > 0 ? setShowStripe(true) :  NotificationManager.error(props.t("Please enter the amount"), "", 4000)}>
                                                {props.t("Add Deposit")}</button>
            
                                    <button type="button" className="btn btn-light mb-2 me-2" id="close" onClick={() => of_close()}>{props.t("Close")}</button>
                                </div>
                            </Col>
                    </Row>
                </div>

                : <div style={{
                    width: '100%',
                    display: 'flex',
                    justifyContent: 'center',
                    alignItems: 'center'
                }}>
                    <EmbeddedCheckoutProvider
                        stripe={stripePromise}
                        options={{ fetchClientSecret }}>
                        <EmbeddedCheckout/>
                    </EmbeddedCheckoutProvider>
                </div>
            }
            </ModalBody>
        </Modal>
     }


    <NotificationContainer/>
    </React.Fragment>
    );
  }


export default withRouter(withTranslation()(DepositModal));



/*
Fake odendiyi komple kaldirdim admin tarafindan deposit yatiracagiz

    function of_fakePay(){
        getResetToken(props.user.token).then((response) => {
            depositAdd(props.user.id, null, null, addAmount, 'You have deposited money into your account.', 0,  response[0].resettoken, props.user.token ).then((response) => {
                NotificationManager.success(props.t("${{amount}} added to your account", {'amount':addAmount}),"", 5000);
                refreshDeposit()
                setAddAmount("")
                of_getDepositList(user)
                of_getDeposit(user)
            }).catch(error => NotificationManager.error(error, "", 4000));
        }).catch(error => NotificationManager.error(error, "", 4000));
    }

    {(props.user.id ==51 || props.user.id ==80 || props.user.id ==83) &&
    <button type="button" className="btn btn-danger mb-2" id="save" onClick={() => of_fakePay()}>{props.t("Fake Odendi Yap")}</button>
}

*/

