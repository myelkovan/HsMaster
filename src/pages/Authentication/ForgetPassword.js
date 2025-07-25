import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import PropTypes from "prop-types";
import { useEffect ,React} from "react";
import { Row, Col, Alert, Card, CardBody, Container, FormFeedback, Input, Label, Form } from "reactstrap";
import { useSelector, useDispatch } from "react-redux";
import { useNavigate, Link } from "react-router-dom";
import * as Yup from "yup";
import { useFormik } from "formik";
import { userForgetPassword ,resetForgetPasswordState } from "../../store/actions";
import logoLight from "../../assets/images/logo-light.png";
import ParticlesAuth from "./ParticlesAuth";


const ForgetPasswordPage = props => {
  const { forgetError, forgetSuccessMsg } = useSelector(state => ({
    forgetError: state.ForgetPassword.forgetError,
    forgetSuccessMsg: state.ForgetPassword.forgetSuccessMsg,
  }));
  const dispatch = useDispatch();
  const navigate = useNavigate();

  useEffect(() => {
    dispatch(resetForgetPasswordState()); // Redux üzerinden sıfırla
  }, []);
  
  
  
  const validation = useFormik({
    // enableReinitialize : use this flag when initial values needs to be changed
    enableReinitialize: true,

    initialValues: {
      email: '',
    },
    validationSchema: Yup.object({
      email: Yup.string().required(props.t("Please Enter Your Email")),
    }),
    onSubmit: (values) => {
        console.log("onSubmit deyim 1: forgetSuccessMsg: ",forgetSuccessMsg);
         if (forgetSuccessMsg===null){
          console.log("onSubmit deyim 2: dispatch öncesi: ",forgetSuccessMsg);  
          dispatch(userForgetPassword(values, props.history));
          console.log("onSubmit deyim 3:  dispatch sonrası: ",forgetSuccessMsg + ' forgeterror:' + JSON.stringify(forgetError));
         }else{
            console.log("onSubmit deyim 4:  login öncesi : ",forgetSuccessMsg);
            navigate("/login");
         }
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
                  <Link to="/" className="d-inline-block auth-logo">
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
                    <h5 className="text-primary">{props.t("Forgot Password?")}</h5>

                    <lord-icon
                      src="https://cdn.lordicon.com/rhvddzym.json"
                      trigger="loop"
                      colors="primary:#0ab39c"
                      className="avatar-xl"
                      style={{ width: "120px", height: "120px" }}
                      >
                    </lord-icon>

                  </div>

                  <Alert className="alert-borderless alert-warning text-center mb-2 mx-2" role="alert">
                    {props.t("Enter your email and instructions will be sent to you!")}
                  </Alert>
                  <div className="p-2">
                    {forgetError && Object.keys(forgetError).length > 0 ? (
                      <Alert id="alertforgeterror" color="danger" style={{ marginTop: "13px" }}>
                        {forgetError?.message || JSON.stringify(forgetError)}
                      </Alert>
                    ) : null}
                    {forgetSuccessMsg ? (
                      <Alert id="alertforgetsuccessmsg" color="success" style={{ marginTop: "13px" }}>
                        {forgetSuccessMsg}
                      </Alert>
                    ) : null}
                    <Form
                      onSubmit={(e) => {
                        e.preventDefault();
                        validation.handleSubmit();
                        return false;
                      }}
                    >
                      <div className="mb-4">
                        <Label className="form-label"  htmlFor="email-input" >{props.t('Email')}</Label>
                        <Input
                          id="email-input"
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
                          <FormFeedback type="invalid"><div>{validation.errors.email}</div></FormFeedback>
                        ) : null}
                      </div>

                      <div className="text-center mt-4">
                        <button  id="btnsubmit" className="btn btn-success w-100" type="submit">{forgetSuccessMsg===null?props.t('Send Reset Link'):props.t('Login')}</button>
                      </div>
                    </Form>
                  </div>
                </CardBody>
              </Card>

              <div className="mt-4 text-center">
                <p className="mb-0">{props.t('Wait, I remember my password...')} 
                  <Link id="linklogin" to="/login" className="fw-semibold text-primary text-decoration-underline"> {props.t('Click here')} </Link> </p>
              </div>

            </Col>
          </Row>
        </Container>
      </div>
    </ParticlesAuth>
  );
};

ForgetPasswordPage.propTypes = {
  history: PropTypes.object,
};


export default withRouter(withTranslation()(ForgetPasswordPage));