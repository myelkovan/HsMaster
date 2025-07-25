import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import React, { useEffect, useState } from 'react';
import { Card, Col, Container, Input, Label, Row, Button, Form, FormFeedback, Alert, Spinner } from 'reactstrap';
import AuthSlider from './authCarousel';
import { useSelector, useDispatch } from "react-redux";
import { Link } from "react-router-dom";
import * as Yup from "yup";
import { useFormik } from "formik";


import { GoogleLogin } from "react-google-login";
// import TwitterLogin from "react-twitter-auth"
import FacebookLogin from "react-facebook-login/dist/facebook-login-render-props";
// actions
import { loginUser, socialLogin, resetLoginFlag } from "../../store/actions";
import logoLight from "../../assets/images/logo-light.jpeg";
import { facebook, google } from "../../config";


const Login = (props) => {
    const [userLogin, setUserLogin] = useState([]);
    const [passwordShow, setPasswordShow] = useState(false);
    const { user, errorMsg, loading, error } = useSelector(state => ({
        user: state.Account.user,
        errorMsg: state.Login.errorMsg,
        loading: state.Login.loading,
        error: state.Login.error,
    }));
   const dispatch = useDispatch();



    useEffect(() => {
        if (user && user.user) {
            setUserLogin({
                email: user.user.email,
                password: user.user.confirm_password
            });
        }
    }, [user]);


    const validation = useFormik({
        // enableReinitialize : use this flag when initial values needs to be changed
        enableReinitialize: true,

        initialValues: {
            email: userLogin.email || '',
            password: userLogin.password || '',
        },
        validationSchema: Yup.object({
            email: Yup.string().required(props.t("Please Enter Your Email")),
            password: Yup.string().required(props.t("Please Enter Your Password")),
        }),
        onSubmit: (values) => {
            dispatch(loginUser(values, props.router.navigate));
        }
    });

    const signIn = (res, type) => {
        if (type === "google" && res) {
            const postData = {
                name: res.profileObj.name,
                email: res.profileObj.email,
                token: res.tokenObj.access_token,
                idToken: res.tokenId,
            };
            dispatch(socialLogin(postData, props.router.navigate, type));
        } else if (type === "facebook" && res) {
            const postData = {
                name: res.name,
                email: res.email,
                token: res.accessToken,
                idToken: res.tokenId,
            };
            dispatch(socialLogin(postData, props.router.navigate, type));
        }
    };

    //handleGoogleLoginResponse
    const googleResponse = response => {
        signIn(response, "google");
    };

    //handleTwitterLoginResponse
    // const twitterResponse = e => {}

    //handleFacebookLoginResponse
    const facebookResponse = response => {
        signIn(response, "facebook");
    };

    useEffect(() => {
        if (error) {
            setTimeout(() => {
                dispatch(resetLoginFlag());
            }, 3000);
        }
    }, [dispatch, error]);


document.title= process.env.REACT_APP_NAME;
return (
    <React.Fragment>
        <div className="auth-page-wrapper auth-bg-cover py-4 d-flex justify-content-center align-items-center min-vh-100"
        style={{"--bs-body-color": "bs-dark" }}>
      
    
            <div className="bg-overlay"></div>
            <div className="auth-page-content overflow-hidden pt-lg-0">
                <Container>
                    <Row>
                        <Col lg={12}>
                            <Card className="overflow-hidden">
                                <Row className="g-0">
                                   <AuthSlider /> 

                                    <Col lg={4}>
                                        <div className="p-lg-5 p-4">
                                            <div>
                                                <h5 className="text-primary">{props.t("Welcome Back !")}</h5>
                                                <p className="text-muted">{props.t('Sign in to continue to {{appName}}',{ appName:process.env.REACT_APP_NAME})}</p>
                                            </div>
                                            {errorMsg && errorMsg ? (<Alert color="danger"> {errorMsg} </Alert>) : null}
                                            <div className="p-2 mt-4">
                                                <Form
                                                    onSubmit={(e) => {
                                                        e.preventDefault();
                                                        validation.handleSubmit();
                                                        return false;
                                                    }}
                                                    action="#">

                                                    <div className="mb-3">
                                                        <Label htmlFor="email" className="form-label">{props.t('Email')}</Label>
                                                        <Input
                                                            id="email"
                                                            name="email"
                                                            className="form-control"
                                                            placeholder={props.t("Enter email")}
                                                            type="email"
                                                            onChange={validation.handleChange}
                                                            onBlur={validation.handleBlur}
                                                            value={validation.values.email || ""}
                                                            invalid={
                                                                validation.touched.email && validation.errors.email ? true : false
                                                            }
                                                        />
                                                        {validation.touched.email && validation.errors.email ? (
                                                            <FormFeedback type="invalid">{validation.errors.email}</FormFeedback>
                                                        ) : null}
                                                    </div>

                                                    <div className="mb-3">
                                                        <div className="float-end">
                                                            <Link id="forgotpassword" to="/forgot-password" className="text-muted">{props.t('Forgot password?')}</Link>
                                                        </div>
                                                        <Label className="form-label" htmlFor="password-input">{props.t('Password')}</Label>
                                                        <div className="position-relative auth-pass-inputgroup mb-3">
                                                            <Input
                                                                id="password-input"                                                            
                                                                name="password"
                                                                value={validation.values.password || ""}
                                                                type={passwordShow ? "text" : "password"}
                                                                className="form-control pe-5"
                                                                placeholder={props.t("Enter Password")}
                                                                onChange={validation.handleChange}
                                                                onBlur={validation.handleBlur}
                                                                invalid={
                                                                    validation.touched.password && validation.errors.password ? true : false
                                                                }
                                                            />
                                                            {validation.touched.password && validation.errors.password ? (
                                                                <FormFeedback type="invalid">{validation.errors.password}</FormFeedback>
                                                            ) : null}
                                                            <button className="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted" type="button" id="password-addon" onClick={() => setPasswordShow(!passwordShow)}>
                                                                <i className="ri-eye-fill align-middle"></i>
                                                            </button>
                                                        </div>
                                                    </div>


                                                    <div className="mt-4">
                                                        <Button id="btnsignin" disabled={error ? null : loading ? true : false} color="success" className="btn btn-success w-100" type="submit">
                                                            {error ? null : loading ? <Spinner size="sm" className='me-2'> Loading... </Spinner> : null}
                                                            {props.t("Sign In")}
                                                        </Button>
                                                    </div>
                                                 </Form>
                                            </div>

                                            <div className="mt-5 text-center">
                                                <p className="mb-0">{props.t("Don't you have an account ?")}</p> 
                                                <Link to="/register" className="text-muted">{props.t("Signup")}</Link>
                                            </div>
                                        </div>
                                    </Col>
                                </Row>
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


export default withRouter(withTranslation()(Login));
/*
178 de idi
        <div className="form-check">
            <Input className="form-check-input" type="checkbox" value="" id="auth-remember-check" />
            <Label className="form-check-label" htmlFor="auth-remember-check">Remember me</Label>
        </div>

//bundan once
 <div className="mt-4">
     <Button disabled={error ? null : loading ? true : false} color="success" className="btn btn-success w-100" type="submit">

*/