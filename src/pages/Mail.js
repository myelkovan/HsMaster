import { withTranslation } from "react-i18next";
import withRouter from '../Components/Common/withRouter';
import Paper from '@mui/material/Paper';
import React, { useEffect, useState} from "react";
import {NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import { useNavigate, useLocation, useParams } from 'react-router-dom';
import "react-rangeslider/lib/index.css";
import "nouislider/distribute/nouislider.css";
import Background from '../assets/images/auth-one-bg.svg';
import { sendMail } from '../services/service_user';
import {  Col, Container, Input, Row} from 'reactstrap';
import smallImage9 from '../assets/images/back4.jpg';



function Mail(props){
      const [user, setUser] = useState(null);
      const [subject, setSubject] = useState('Need support');
      const [body, setBody] = useState(null);
      const [orderId, setOrderId] = useState(null);
      const navigate = useNavigate();
 
 
   useEffect(() => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        setUser(user)
    }, []);


    function of_send() {

      sendMail(subject, body, orderId, user ).then(response => {
            NotificationManager.success(props.t("Messsage sent"),"", 4000);
            of_close()
      }).catch(error => {NotificationManager.error(error,"", 4000)});
    }
 
      function of_close(){
       navigate('/Orders')
      }

   //background style
    const imgStyle = {
        backgroundImage: "url(" + Background + ")",
        backgroundPosition: "center",
        backgroundSize: "cover",
        backgroundRepeat: "no-repeat",
        width: "100vw",
        height: "100vh",
    };







  return (
    <React.Fragment>
      <div style={imgStyle} >   
      
      {user &&            
            <div className="hstack gap-3 mt-5 me-5">
            <Container component="main" maxWidth="lg" sx={{my: {xs: 1, md: 8, mb: 8, mt: 1}}}>
                  <Paper className="bg-light" variant="outlined" sx={{my: {xs: 3, md: 6, mb: 5, mt: 5}, p: {xs: 2, mt: 3, md: 3}}}>
                  <React.Fragment>
                      
                          
                  <Row>
                     <Col lg={12}>
                         <input type="hidden" id="memberid-input" className="form-control" defaultValue="" />
                         <div className="bg-light px-1 pt-1 ">
                             <div className="modal-team-cover position-relative mb-0 mt-n4 mx-n4 rounded-top overflow-hidden">
                                 <img src={smallImage9} alt="" id="cover-img" className="img-fluid" />

                                 <div className="d-flex position-absolute start-0 end-0 top-0 p-3">
                                     <div className="flex-grow-1">
                                         <h5 className="modal-title text-white" id="createMemberLabel"></h5>
                                     </div>
                                     <div className="d-flex position-absolute start-0 top-0  p-3">
                                      <span className="fs-18 fw-semibold" style={{color: 'black'}}>
                                       {props.t("Get email support")}
                                      </span>
                                     </div>

                                     <div className="flex-shrink-0">
                                         <div className="d-flex gap-3 align-items-center">
                                             <button type="button" className="btn-close btn-close-white"
                                                     onClick={() => of_close()} id="Btn-close"
                                                     data-bs-dismiss="modal" aria-label={props.t("Close")}></button>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </Col>
                </Row>
                             
                <div className=" mt-4">
                  <span style={{color: 'green'}}>{props.t("If you'd like to add any additional information about your order, you can include a note in your order list.")}</span>        
                </div>     


                  <div className="mb-2 mt-2">
                        <Input type="text" className="form-control" id="subject"
                        placeholder = {props.t("Subject"  )}
                        onChange={(e) =>setSubject(e.target.value)}
                        />
                  </div>     

                  <div className="mb-2" style={{width: '500px'}}>
                        <div className="d-flex align-items-center">
                        
                        <Input
                              type="text"
                              className="form-control"
                              id="firstnameInput"
                              defaultValue="JB-"
                              onChange={(e) => setOrderId(e.target.value)}
                              style={{width: '150px'}}
                        />
                        </div>
                  </div>
  

                  <div className="mb-2">
                        <Input type="textarea" className="form-control" id="messageinput" 
                        rows="8"
                        placeholder = {props.t("Your message")}
                        onChange={e => setBody(e.target.value)}/>
                  </div>

                <div className=" mt-0">
                  <span id="useremail" className = "text-muted">{props.t("We will respond to your email adress ({{email}}) as soon as possible.",{email:user.email})}</span>        
                </div>     
            
                                               
                  <Row>
                        <Col lg={12}>
                        <div className="hstack gap-2 justify-content-end mt-4">
                              <button type="button" className="btn btn-success" id="save" onClick={() => of_send()}>{props.t("Send")}</button>
                              <button type="button" className="btn btn-light" id="close" onClick={()=>of_close()}>{props.t("Close")}</button>
                        </div>
                        </Col>
                  </Row>
 
            </React.Fragment>
            </Paper>
      </Container>

      </div>
}
</div>
      

    </React.Fragment>
  );
}

export default withRouter(withTranslation()(Mail));
