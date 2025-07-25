import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import React, { useEffect } from "react";
import { Row, Col, Card, Alert, Container, Input, Label, Form, FormFeedback } from "reactstrap";
import * as Yup from "yup";
import { useFormik } from "formik";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { registerUser, apiError, resetRegisterFlag } from "../../store/actions";
import { useSelector, useDispatch } from "react-redux";
import { Link, useNavigate } from "react-router-dom";
import AuthSlider from './authCarousel';
import PhoneInput from 'react-phone-input-2'
import 'react-phone-input-2/lib/style.css'


const Register = (props) => {
    const dispatch = useDispatch();
    const navigate = useNavigate();

    const validation = useFormik({
        // enableReinitialize : use this flag when initial values needs to be changed
        enableReinitialize: true,

        initialValues: {
            email: '',
            name: '',
            password: '',
            confirm_password: '',
            phone:'',
        },
        validationSchema: Yup.object({
            email: Yup.string().required(props.t("Please Enter Your Email")),
            name: Yup.string().required(props.t("Please Enter Your Username")),
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
                }).required(props.t("Confirm Password Required")),
            mobile: Yup.string().min(8, props.t("We may need to reach you by phone"))
                                .required(props.t("We may need to reach you by phone"))
        }),
        onSubmit: (values) => {
            dispatch(registerUser(values));
        }
    });

    const { error, registrationError, success } = useSelector(state => ({
        registrationError: state.Account.registrationError,
        success: state.Account.success,
        error: state.Account.error
    }));

    useEffect(() => {
        dispatch(apiError(""));
    }, [dispatch]);

    useEffect(() => {
        if (success) {
            navigate("/registersuccess")
        }

        setTimeout(() => {
            dispatch(resetRegisterFlag());
        }, 3000);

    }, [dispatch, success, error, navigate]);


    return (
        <React.Fragment>
            <div className=" auth-bg-cover py-5 justify-content-center align-items-center min-vh-100">
                <div className="bg-overlay"></div>
                <div className="auth-page-content overflow-hidden pt-lg-5">
                    <Container>
                        <Row>
                            <Col lg={12}>
                                <Card className="overflow-hidden m-0">
                                    <Row className="justify-content-center g-0">
                                        <AuthSlider />

                                        <Col lg={4}>
                                            <div className="p-lg-5 p-4">
                                                <div>
                                                    <h5 className="text-primary">{props.t("Register Account")}</h5>
                                                    <p className="text-muted">{props.t('Get your Free {{appName}} account now.', {appName: process.env.REACT_APP_NAME})}</p>
                                                </div>

                                                <div className="mt-4">
                                                    <Form
                                                        onSubmit={(e) => {
                                                            e.preventDefault();
                                                            validation.handleSubmit();
                                                            return false;
                                                        }}
                                                        className="needs-validation" action="/#">

                                                        {success && success ? (
                                                            <>
                                                                {toast(props.t("Your Redirect To Login Page..."), {
                                                                    position: "top-right",
                                                                    hideProgressBar: false,
                                                                    className: 'bg-success text-white',
                                                                    progress: undefined,
                                                                    toastId: ""
                                                                })}
                                                                <ToastContainer autoClose={8000} limit={1}/>
                                                                <Alert color="success">
                                                                    {props.t('Register User Successfully and Your Redirect To Login Page...')}
                                                                </Alert>
                                                            </>
                                                        ) : null}

                                                        {error && error ? (
                                                            <Alert color="danger">
                                                                {registrationError.includes('VAR')
                                                                    ?
                                                                    <div>{props.t('Email has been Register Before, Please Use Another Email Address...')} </div>
                                                                    :
                                                                    <div>{props.t('not send email to this email address...')} </div>}
                                                            </Alert>
                                                        ) : null}

                                                        <div className="mb-3">
                                                            <Label htmlFor="email"
                                                                   className="form-label">{props.t('Email')} <span
                                                                className="text-danger">*</span></Label>
                                                            <Input
                                                                id="email"
                                                                name="email"
                                                                className="form-control"
                                                                placeholder={props.t('Enter email address')}
                                                                type="email"
                                                                onChange={validation.handleChange}
                                                                onBlur={validation.handleBlur}
                                                                value={validation.values.email || ""}
                                                                invalid={
                                                                    validation.touched.email && validation.errors.email ? true : false
                                                                }
                                                            />
                                                            {validation.touched.email && validation.errors.email ? (
                                                                <FormFeedback type="invalid">
                                                                    <div>{validation.errors.email}</div>
                                                                </FormFeedback>
                                                            ) : null}

                                                        </div>
                                                        <div className="mb-3">
                                                            <Label htmlFor="name"
                                                                   className="form-label">{props.t('Full Name')} <span
                                                                className="text-danger">*</span></Label>
                                                            <Input
                                                                id="name"
                                                                name="name"
                                                                type="text"
                                                                placeholder={props.t("Enter Full Name")}
                                                                onChange={validation.handleChange}
                                                                onBlur={validation.handleBlur}
                                                                value={validation.values.name || ""}
                                                                invalid={validation.touched.name && validation.errors.name ? true : false
                                                                }
                                                            />
                                                            {validation.touched.name && validation.errors.name ? (
                                                                <FormFeedback type="invalid">
                                                                    <div>{validation.errors.name}</div>
                                                                </FormFeedback>
                                                            ) : null}

                                                        </div>

                                                        <div className="mb-3">
                                                            <Label htmlFor="password"
                                                                   className="form-label">{props.t('Password')} <span
                                                                className="text-danger">*</span></Label>
                                                            <Input
                                                                id="password"
                                                                name="password"
                                                                type="password"
                                                                placeholder={props.t("Enter Password")}
                                                                onChange={validation.handleChange}
                                                                onBlur={validation.handleBlur}
                                                                value={validation.values.password || ""}
                                                                invalid={
                                                                    validation.touched.password && validation.errors.password ? true : false
                                                                }
                                                            />
                                                            {validation.touched.password && validation.errors.password ? (
                                                                <FormFeedback type="invalid">
                                                                    <div>{validation.errors.password}</div>
                                                                </FormFeedback>
                                                            ) : null}

                                                        </div>

                                                        <div className="mb-2">
                                                            <Label htmlFor="confirmPassword" className="form-label">Confirm
                                                                Password <span className="text-danger">*</span></Label>
                                                            <Input
                                                                id="confirmPassword"
                                                                name="confirm_password"
                                                                type="password"
                                                                placeholder={props.t("Confirm Password")}
                                                                onChange={validation.handleChange}
                                                                onBlur={validation.handleBlur}
                                                                value={validation.values.confirm_password || ""}
                                                                invalid={
                                                                    validation.touched.confirm_password && validation.errors.confirm_password ? true : false
                                                                }
                                                            />
                                                            {validation.touched.confirm_password && validation.errors.confirm_password ? (
                                                                <FormFeedback type="invalid">
                                                                    <div>{validation.errors.confirm_password}</div>
                                                                </FormFeedback>
                                                            ) : null}

                                                        </div>


                                                        <div className="mb-2" >
                                                            <Label htmlFor="Phone" className="form-label">
                                                                Phone <span className="text-danger">*</span>
                                                            </Label>

                                                            <div>
                                                                <PhoneInput
                                                                    id="phone" 
                                                                    name="mobile" 
                                                                    country={'us'}
                                                                    required={true}
                                                                    autoFocus={true}
                                                                    value={validation.values.mobile}
                                                                    onChange={mobile => validation.setFieldValue('mobile', mobile)}
                                                                    invalid={validation.touched.mobile && validation.errors.mobile ? true : false}

                                                                />
                                                                {validation.errors.mobile && validation.touched.mobile && (
                                                                    <div style={{color: '#f06548', fontSize: '12px' }}>{validation.errors.mobile}</div>
                                                                )}
                                                            </div>
                                                        </div>




                                                <div className="mb-4">
                                                    <p className="mb-0 fs-12 text-muted fst-italic">{props.t("By registering, you agree to the {{appName}} Terms and Conditions and Privacy Policy.", {appName: process.env.REACT_APP_NAME}) + " "}
                                                        <Link to="#"
                                                              className="text-primary text-decoration-underline fst-normal fw-medium">{props.t("Terms of Use")}</Link>
                                                    </p>
                                                </div>

                                                <div className="mt-4">
                                                    <button id="btnsignup" className="btn btn-success w-100"
                                                            type="submit">{props.t("Sign Up")}</button>
                                                </div>

                                            </Form>
                </div>

                <div className="mt-4 text-center">
                    <p className="mb-0">{props.t("Already have an account ?")} 
                        <Link id="signin" to="/login" className="fw-semibold text-primary text-decoration-underline"> 
                            {props.t('Signin')} </Link>
                    </p>
                </div>
            </div>
        </Col>
</Row>
</Card>
</Col>

</Row>


                    </Container>
                </div>


            </div>
        </React.Fragment>
    );
};


export default withRouter(withTranslation()(Register));

