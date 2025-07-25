import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import React, { useEffect, useState } from 'react';
import { Card, Col, Container, Input, Label, Row, Button, Form, FormFeedback, Alert, Spinner } from 'reactstrap';
import img_check from '../../assets/images/check.png';
import 'aos/dist/aos.css';
import AOS from 'aos';
import { Link, useNavigate } from "react-router-dom";


const RegisterSuccess = (props) => {
     const navigate = useNavigate();

    useEffect(() => {
            AOS.init({duration: 1000, disable: 'mobile'});
            AOS.refresh();                
    }, []);
    

document.title= process.env.REACT_APP_NAME;
return (
    <React.Fragment>
        <div className="auth-page-wrapper auth-bg-cover py-4 d-flex justify-content-center align-items-center min-vh-100">
            <div className="bg-overlay"></div>
            <div className="auth-page-content overflow-hidden pt-lg-0">
                <Container>
                    <Row>
                        <Col lg={12}>
                            <Card className="mx-auto mb-3 mt-4" style={{ width: '70%', maxWidth: '65%', height: 'auto' }}>
                                             
                                    <div className="d-flex flex-column align-items-center mt-2 justify-content-center">
                                       

                                        <div className="d-flex justify-content-center mt-4 mb-2" data-aos="zoom-in">
                                            <img
                                            src={img_check} id="cover-img" alt=""
                                            height="100px"  width="100px"
                                            className="img-fluid"
                                            />
                                        </div>
                              

                                        <span className="fs-18 d-block mt-3" style={{ fontWeight: 'bold' }}>
                                            {props.t("You're all set! We've successfully created your account.")}
                                        </span>
                                        
                                    </div>

                                    <div className="d-flex flex-column align-items-center mt-3 justify-content-center">
                                         <p className="text-muted fs-17 mt-2 ms-5 me-5">{props.t("We've sent you an activation email. Please activate your account and sign in to access {{appName}}. Don't forget to check your spam/junk folder!",{ appName:process.env.REACT_APP_NAME})}</p>
                                    </div>

                                    <div className="d-flex flex-column align-items-center justify-content-center mt-3 mb-5 mx-auto" style={{ width: '30%' }}>
                                        <Button id="btnsignin" color="success" className="btn btn-success w-100"
                                            onClick={() => navigate("/login")}>
                                            {props.t("Sign In")}
                                        </Button>
                                    </div>
                                    
                            
                            </Card>
                        </Col>
                    </Row>
                </Container>
            </div>

            <footer className="footer">
                <Container>
                    <Row>
                        <Col lg={12}>
                            <div className="text-center">
                               
                            </div>
                        </Col>
                    </Row>
                </Container>
            </footer>

        </div>


    </React.Fragment>
);
};


export default withRouter(withTranslation()(RegisterSuccess));
