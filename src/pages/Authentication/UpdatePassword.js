import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import React, { useEffect, useState} from "react";
import {NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import { useNavigate, useParams  } from 'react-router-dom';
import { updatePassword } from '../../services/service_user'
import { Col, Form, Input, Label,  Row, Button, FormFeedback } from 'reactstrap';
import * as Yup from "yup";
import { useFormik } from "formik";

function UpdatePassword(props){
    const [password1, setPassword1] = useState();
    const [password2, setPassword2] = useState();
    const navigate = useNavigate();
    const {email, reset_token} = useParams();
    const {target} = props;
    const [user, setUser] = useState(null);


  useEffect(() => {
    const authuser = sessionStorage.getItem("authUser")
    const user = JSON.parse(authuser);
    setUser(user)
     }, []);


     const validation = useFormik({
        // enableReinitialize : use this flag when initial values needs to be changed
        enableReinitialize: true,

        initialValues: {
            password: '',
            confirm_password: '',
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
                }).required(props.t("Confirm Password Required")),
        }),
        onSubmit: (values) => {
            of_updatePassword(validation.values.password)
        }
    });



    const handleChange = (colname) => (e) => {
       if(colname === "password1") {setPassword1(e.target.value );}
       if(colname === "password2") {setPassword2(e.target.value );}
    }


    const handleFormSubmit = (data) => {
        //event.preventDefault();
        if (password1 !== password2){
            NotificationManager.error(props.t("Your passwords are not same!"),props.t("Error"))
            return
        }

        if (password1.length < 6){
            NotificationManager.error(props.t("Password must be six characters at least!"),props.t("Error"))
            return
        }
        of_updatePassword(password1)
   };

    function of_updatePassword(password){
        var ls_reset_token = ""
        var ls_email = user.id
        if (reset_token){
            ls_reset_token = reset_token
            ls_email = email
        }

        updatePassword(ls_email, password, ls_reset_token, user.token).then(response => {
        if (response >= 0){
            NotificationManager.success(props.t('Your password has been changed!'),'',4000)
             // Userprofile dan cagrilirsa target "" gonderiliyor yani baska bir sayfaya gitme
             // ancak mail ile link gonderilmisse target diye bir degisken yok ve login sayfasina gidecek
              if (target!==''){
                 navigate('/login')
             }
        }else{
             NotificationManager.error(props.t('Your password could not be changed!'))
        }
        }).catch(error => NotificationManager.error(error,"", 4000));
    }


return (

   <Form className="needs-validation" action="/#" 
     onSubmit={(e) => {
     e.preventDefault();
     validation.handleSubmit();
     return false;
     }}>
        <Row className="g-2">
            <Col lg={6}>
                <div>
               

                    <Label htmlFor="newpasswordInput" className="form-label">{props.t("New Password")}<span className="text-danger">*</span></Label>
                    <Input
                        id="newpasswordInput" 
                        name="password"
                        type="password"
                        placeholder={props.t("Enter Password")}
                        onChange={validation.handleChange}
                        onBlur={validation.handleBlur}
                        value={validation.values.password || ""}
                        invalid={validation.touched.password && validation.errors.password ? true : false}
                    />
                    {validation.touched.password && validation.errors.password ? (
                        <FormFeedback type="invalid">
                            <div>{validation.errors.password}</div>
                        </FormFeedback>
                    ) : null}
                </div>
            </Col>

            <Col lg={6}>
                <div>
                    <Label htmlFor="confirmpasswordInput" className="form-label">{props.t("Confirm Password")}<span className="text-danger">*</span></Label>
                    <Input
                    id="confirmpasswordInput"
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
            </Col>

            <Col lg={12}>
                <div className="text-end">
                    <Button id="btnchangepassword"  type="submit" className="btn btn-success me-2">{props.t("Change Password")}</Button>
                    <Button id="btnclose" color="success" onClick={e => navigate("/")} > {props.t("Close")} </Button>

                </div>
            </Col>
        </Row>
    </Form>
)
}

export default withRouter(withTranslation()(UpdatePassword));