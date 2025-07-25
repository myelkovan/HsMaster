import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import React, { useState } from 'react';
import { useNavigate, Link, useParams  } from 'react-router-dom';
import { Button, Card, CardBody, Col, Container, Row, Form, Input, Label, FormFeedback } from 'reactstrap';
import ParticlesAuth from './ParticlesAuth';
import logoLight from "../../assets/images/logo-light.png";
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { updatePassword } from '../../services/service_user'
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';


const PasswordCreate = (props) => {
    const {email, reset_token} = useParams();
    const [passwordShow, setPasswordShow] = useState(false);
    const [confirmPasswordShow, setConfirmPasswordShow] = useState(false);
    const navigate = useNavigate();



    function of_updatePassword(password){
        updatePassword(email, password, reset_token, "").then(response => {
        if (parseInt(response) >= 0){
            NotificationManager.success(props.t("Your password has been changed!"),'',4000)
            navigate('/login')
        }else{
           NotificationManager.error(props.t("Your password could not be changed! You should change your password in 20 min after requesting."),'',4000)
        }
        }).catch(error => NotificationManager.error(error,"", 4000));
    }



    const validation = useFormik({
        enableReinitialize: true,

        initialValues: {
            password: "",
            confirm_password: "",
        },
        validationSchema: Yup.object({
            password: Yup.string()
                .min(8, props.t("Password must be at least 8 characters"))
                .matches(RegExp('(.*[a-z].*)'), props.t("Password must be at least lowercase letter"))
                .matches(RegExp('(.*[A-Z].*)'), props.t("Password must be at least uppercase letter"))
                .matches(RegExp('(.*[0-9].*)'), props.t("Password must be at least one number"))
                .required(props.t("This field is required")),
            confirm_password: Yup.string()
                .when("password", {
                    is: (val) => (val && val.length > 0 ? true : false),
                    then: Yup.string().oneOf(
                        [Yup.ref("password")],
                        props.t("Both password need to be the same")
                    ),
                })
                .required(props.t("Confirm Password Required")),
        }),
        onSubmit: (values) => {
            of_updatePassword(values.password)
        }
    });


   return (
        <ParticlesAuth>
            <div className="auth-page-content">
                <Container>
                    <Row>
                        <Col lg={12}>
                            <div className="text-center mt-sm-5 mb-4 text-white-50">
                                <div>
                                    <Link to="/#" className="d-inline-block auth-logo">
                                        <img src={logoLight} alt="" height="60" />
                                    </Link>
                                </div>
                            </div>
                        </Col>
                    </Row>
                    <Row className="justify-content-center">
                        <Col md={8} lg={6} xl={5}>
                            <Card className="mt-4">

                                <CardBody className="p-4">
                                    <div className="text-center mt-2">
                                        <h5 className="text-primary">{props.t("Create New Password")}</h5>
                                        <p className="text-muted">{props.t("Your new password must be different from previous used password.")}</p>
                                    </div>

                                    <div className="p-2">
                                        <Form onSubmit={validation.handleSubmit} action="/auth-signin-basic">
                                            <div className="mb-3">
                                                <Label className="form-label" htmlFor="password-input">{props.t("Password")}</Label>
                                                <div className="position-relative auth-pass-inputgroup">
                                                    <Input
                                                        type={passwordShow ? "text" : "password"}
                                                        className="form-control pe-5 password-input"
                                                        id="password-input"
                                                        name="password"
                                                        value={validation.values.password}
                                                        onBlur={validation.handleBlur}
                                                        onChange={validation.handleChange}
                                                        invalid={validation.errors.password && validation.touched.password ? true : false}
                                                    />
                                                    {validation.errors.password && validation.touched.password ? (
                                                        <FormFeedback type="invalid">{validation.errors.password}</FormFeedback>
                                                    ) : null}
                                                    <Button color="link" onClick={() => setPasswordShow(!passwordShow)} className="position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button"
                                                        id="password-addon"><i className="ri-eye-fill align-middle"></i></Button>
                                                </div>
                                                <div  className="form-text">{props.t("Password must be at least 8 characters")}</div>
                                            </div>

                                            <div className="mb-3">
                                                <Label className="form-label" htmlFor="confirm-password-input">{props.t("Confirm Password")}</Label>
                                                <div className="position-relative auth-pass-inputgroup mb-3">
                                                    <Input
                                                        type={confirmPasswordShow ? "text" : "password"}
                                                        className="form-control pe-5 password-input"
                                                        id="confirm-password-input"
                                                        name="confirm_password"
                                                        value={validation.values.confirm_password}
                                                        onBlur={validation.handleBlur}
                                                        onChange={validation.handleChange}
                                                        invalid={validation.errors.confirm_password && validation.touched.confirm_password ? true : false}
                                                    />
                                                    {validation.errors.confirm_password && validation.touched.confirm_password ? (
                                                        <FormFeedback type="invalid">{validation.errors.confirm_password}</FormFeedback>
                                                    ) : null}
                                                    <Button id="btnconfirmpassword" color="link" onClick={() => setConfirmPasswordShow(!confirmPasswordShow)} className="position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button">
                                                        <i className="ri-eye-fill align-middle"></i></Button>
                                                </div>
                                            </div>

                                            <div id="password-contain" className="p-3 bg-light mb-2 rounded">
                                                <h5 className="fs-13">{props.t("Password rules")}</h5>
                                                <p id="pass-length" className="invalid fs-12 mb-2">{props.t("Password must be at least 8 characters")}</p>
                                                <p id="pass-lower" className="invalid fs-12 mb-2"> {props.t("Password must be at least lowercase letter")}</p>
                                                <p id="pass-upper" className="invalid fs-12 mb-2"> {props.t("Password must be at least uppercase letter")}</p>
                                                <p id="pass-number" className="invalid fs-12 mb-0">{props.t("Password must be at least one number")}</p>
                                            </div>

                                            <div className="mt-4">
                                                <Button id="btnresetpassword" color="success" className="w-100" type="submit">{props.t("Reset Password")}</Button>

                                            </div>
                                        </Form>
                                    </div>
                                </CardBody>
                            </Card>
                            <div className="mt-4 text-center">
                                <p className="mb-0">{props.t("Wait, I remember my password...")} 
                                    <Link to="/login" className="fw-semibold text-primary text-decoration-underline"> 
                                    {props.t("Click here")} </Link> 
                                </p>
                            </div>
                        </Col>
                    </Row>
                    <NotificationContainer/>
                </Container>
                <NotificationContainer/>
            </div>
            <NotificationContainer/>
        </ParticlesAuth>
    );
};


export default withRouter(withTranslation()(PasswordCreate));