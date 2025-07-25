import { withTranslation } from "react-i18next";
import withRouter from '../Components/Common/withRouter';
import React, { useEffect, useState} from "react";
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import { useNavigate, useLocation } from 'react-router-dom';
import "react-rangeslider/lib/index.css";
import { Link } from 'react-router-dom';
import { Card, CardBody, CardHeader, Col, Container, Form, Input, Label, Nav, NavItem, NavLink, Row, TabContent, TabPane, Button } from 'reactstrap';
import classnames from "classnames";
import Flatpickr from "react-flatpickr";
import "nouislider/distribute/nouislider.css";
import avatar1 from '../assets/images/users/user-dummy-img.jpg';
import moment from 'moment';
import UpdatePassword from "./Authentication/UpdatePassword";
import { getUser, updateUser, deleteUser} from '../services/service_user';
import {getDepositList,getDeposit, depositAddAdmin} from "../services/service_deposit";
import { fileUpload } from '../services/service_upload';
import Background from '../assets/images/auth-one-bg.svg';
import PhoneInput from 'react-phone-input-2'
import * as utils from './utils';

function UserProfile(props){
    const [data, setData] = useState([]);
    const [pictureData, setPictureData] = useState([]);
    const [backgroundData, setBackgroundData] = useState([]);
    const [selectedBackgroundPath, setSelectedBackgroundPath] = useState([]);
    const [selectedPicturePath, setSelectedPicturePath] = useState([]);
    const [user, setUser] = useState(null);
    const [activeTab, setActiveTab] = useState("1");
    const [deleteUserName, setDeleteUserName] = useState("");
    const [deposit, setDeposit] = useState(0);
    const [depositList, setDepositList] = useState([]);
    const [addAmount, setAddAmount] = useState(0);
    const [description, setDescription] = useState('Deposited by JetBasket');
    const [userId, setUserId] = useState(0);
    const [showFind, setShowFind] = useState(false);
    const navigate = useNavigate();
    const location = useLocation();



    useEffect(() => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        setUser(user)
        var ls_userId = ""
   
 
         //Bu ifadeyi bulana kadar canim cikti userId set edilmezse undefined veya null mi diye check etsen program patliyordu
         if (location.state && location.state.user_id){
             ls_userId = location.state.user_id
         }else{
             ls_userId = null
             setShowFind(true)
         }
         setUserId(ls_userId)
 
         if (ls_userId === null){
             setData([{
                        id:0,
                        name:"",
                        email:"",
                        mobile:"",
                        permission_level:"0"
                        }])
        }else{
             //setPerformansYetki(Number(user.permission_level&8) === 8)
             of_getUser(ls_userId, user)
        }
     }, []);
 

     
   function of_getUser(userId, user){
        
        if (isNaN(Number(userId))) {
            NotificationManager.error("Enter a valid user id","", 4000)
            return
        }
        getUser(userId, user.token).then(response => {
            if(response.length == 0 ){
                NotificationManager.error("No user with this user id","", 4000)
                setData([{ id:0,name:"", email:"",mobile:"", permission_level:"0" }])
            }else{
                setData(response);
                of_getDeposit(userId, user)
                //alert("user profile loaded")
                of_getDepositList(userId,user)
            }
        }).catch(error => NotificationManager.error(error,"", 4000));
     }


    function of_getDeposit(userId, user) {
        getDeposit(userId, user.token).then((response) => {
            setDeposit(response[0].deposit || 0)
        }).catch(error => NotificationManager.error(error, "", 4000));
    }
    function of_getDepositList(userId,user){
        getDepositList(userId, user.token).then((response) => {
            setDepositList(response);
            // setAddAmount("")
        }).catch(error => NotificationManager.error(error,"", 4000));
    }
    function of_addDeposit(){
            depositAddAdmin(userId, addAmount, description, user.token).then((response) => {
                NotificationManager.success(props.t("Deposit added"),"", 4000);
                of_getDeposit(userId, user)
                of_getDepositList(userId,user)
            }).catch(error => NotificationManager.error(error, "", 4000));

    }
  

     const tabChange = (tab) => {
        if (activeTab !== tab) setActiveTab(tab);
     };

 

    const handleDeleteUserName = (e) => {
        setDeleteUserName(e.target.value)
    }


    function of_deleteUser(){
        if (deleteUserName === ""){
             NotificationManager.error(props.t("Please enter the name of the user you want to delete"))
        }else if (deleteUserName !== user.name){
              NotificationManager.error(props.t('User name is wrong'))
        }else{
              deleteUser(user.id, user.token).then(response => {
               if (response >= 0){
                   NotificationManager.error(props.t("User Deleted"))
                   navigate('/')
               }else{
                   NotificationManager.error(props.t('Server error please try again!'))
               }
              }).catch(error => NotificationManager.error(error,"", 4000));
        }
    }



    function  handleChange(colname, val){
          const newArray = data.map((item) => {
              return { ...item, [colname]: val };
          });
          setData(newArray);
    }


   const handleChange1 = (value)  => {
        const newArray = data.map((item) => {
           return { ...item, ["birth_date"]: value };
       });
       setData(newArray);
   }



   function updateData (colname, newData){
         const newArray = data.map((item) => {
            return { ...item, [colname]: newData };
        });
        setData(newArray);
    }


    const updatePicture = (e) => {
        setPictureData(e.target.files[0])
        setSelectedPicturePath(URL.createObjectURL(e.target.files[0]))

        fileUpload(userId, 'user', "picture_path", e.target.files[0], user.token).then(response => {
            if (response >= 0){
                NotificationManager.success(props.t("Saved"),'',4000)
            }else{
                 NotificationManager.error(props.t('Server error please try again!'))
            }
        }).catch(error => NotificationManager.error(error,"", 4000));
     }


    const updateBackground = (e) => {
        setBackgroundData(e.target.files[0])
        setSelectedBackgroundPath(URL.createObjectURL(e.target.files[0]))
       fileUpload(userId, 'user', "background_path", e.target.files[0], user.token).then(response => {
           if (response >= 0){
               NotificationManager.success(props.t("Saved"),'',4000)
           }else{
                NotificationManager.error(props.t('Server error please try again!'))
           }
       }).catch(error => NotificationManager.error(error,"", 4000));
     }


    function of_check(val, name ){
         if ( val===null || val.trim() === "") {
            NotificationManager.error(props.t("Please enter")+ " " + name,props.t('Warning'))
            return -1
        }
    }


    const handleFormSubmit = (event) => {
        event.preventDefault();

        data[0].birth_date = moment(new Date(data[0].birth_date)).format('yyyy-MM-DD')

        if (of_check(data[0].name, props.t("name")) === -1) return
        if (of_check(data[0].email, props.t("email")) === -1) return
        if (of_check(data[0].mobile, props.t("phone number")) === -1) return

         updateUser(userId, data[0], user.token).then((response) => {
          if (response >= 0){
              NotificationManager.success(props.t("Saved"),'',4000)
              sessionStorage.setItem("authUser", JSON.stringify(user));
          }else{
               NotificationManager.error(props.t('Server error please try again!'))
          }
         }).catch(error => NotificationManager.error(error,"", 4000));
    };


     const handlePerm = (event, val) => {
         event.preventDefault();
        //tiklanan secenegin adi 2-4-8 gibi degerler. Tiklanan degeri al
         const li_add_val = Number(val)

         //kullanicinin veritabanindaki yetki degerini al
         var li_perm = Number(data[0].permission_level)

        // yetki ekleniyorsa degeri ekle yoksa cikar
         if (event.target.checked === true) {
            li_perm = li_perm + li_add_val
          }else{
            li_perm = li_perm - li_add_val
         }

         //son degerei ata
         updateData('permission_level', li_perm)
     }

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
          <div style={imgStyle}>
            {(data || []).map((row, key) => (
              <div className="page-content"  key={key} style={{  padding: '200px 3% 0px 25%' }}>
                <Container fluid>
               

                    {showFind && (
                        <CardBody  style={{ minHeight: '100px', overflow: 'hidden' }}>
                                <Row className="g-2">
                                <div className="col-auto">
                                    <input
                                        onChange={(e)=>setUserId(e.target.value)}
                                            id="search" type="text" className="form-control bg-white border-light"
                                            placeholder={props.t("Search...")}/>
                                        
                                </div>

                                <div className="col-auto">
                                    <Button  id="btnsearch" className="btn btn-success"  onClick={() => of_getUser(userId, user)}>{props.t("Search")}</Button>
                                </div>


                                </Row>
                        </CardBody>
                    )}
                   


                    <Row>
                       <Col xxl={9}>
                            <Card className="mt-xxl-n5" style={{maxWidth: '810px' }}>
                                <CardHeader>
                                    <Nav className="nav-tabs-custom rounded card-header-tabs border-bottom-0"
                                        role="tablist">
                                        <NavItem>
                                            <NavLink
                                                id="navlinkpersonalinfo"
                                                className={classnames({ active: activeTab === "1" })}
                                                onClick={() => {tabChange("1")}}>
                                                <i className="fas fa-home"></i>
                                                {props.t("Personal Information")}
                                            </NavLink>
                                        </NavItem>

                                        <NavItem>
                                            <NavLink to="#"
                                            id="navlinkchangepassword"
                                                className={classnames({ active: activeTab === "2" })}
                                                onClick={() => {tabChange("2")}}
                                                type="button">
                                                <i className="far fa-user"></i>
                                                {props.t("Change Password")}
                                            </NavLink>
                                        </NavItem>

                                       {(Number(user.permission_level&8) == 8) &&
                                            <NavItem>
                                                <NavLink to="#"
                                                id="navlinkpermission"
                                                    className={classnames({ active: activeTab === "3" })}
                                                    onClick={() => {tabChange("3")}}
                                                    type="button">
                                                    <i className="far fa-user"></i>
                                                    {props.t("Permission")}
                                                </NavLink>
                                            </NavItem>
                                        }

                                        {(Number(user.permission_level&2) == 2) && 
                                            <NavItem>
                                                <NavLink to="#"
                                                id="navlinkdeposit"
                                                    className={classnames({ active: activeTab === "4" })}
                                                    onClick={() => {tabChange("4")}}
                                                    type="button">
                                                    <i className="far fa-user"></i>
                                                    {props.t("Deposit")}
                                                </NavLink>
                                            </NavItem>
                                         }
                  
                                    </Nav>
                                </CardHeader>
                                <CardBody className="p-4">
                                    <TabContent activeTab={activeTab}>
                                        <TabPane tabId="1">
                                            <Form>
                                                <Row>
                                                   <Col xxl={3}>
                                                         <Card className="mt-4">
                                                            <CardBody>
                                                                <Row>
                                                                    <Col xl={12}>
                                                                        <div className="text-center">
                                                                            <div className="profile-user position-relative d-inline-block mx-auto  mb-4">
                                                                                    <img id="imguserprofil" src={pictureData.name? selectedPicturePath: row.picture_path? process.env.REACT_APP_API_URL + "/images/" + row.picture_path : avatar1}  className="rounded-circle avatar-xl img-thumbnail user-profile-image" alt="user-profile" />

                                                                                    <div className="avatar-xs p-0 rounded-circle profile-photo-edit">
                                                                                        <Input id="profile-img-file-input" type="file" className="profile-img-file-input" onChange={(e) => updatePicture(e,"picture_path")} />
                                                                                        <Label htmlFor="profile-img-file-input" className="profile-photo-edit avatar-xs">
                                                                                             <span className="avatar-title rounded-circle bg-light text-body">
                                                                                                <i className="ri-camera-fill"></i>
                                                                                            </span>
                                                                                        </Label>
                                                                                    </div>
                                                                            </div>
                                                                            <h5 className="fs-16 mb-1">{row.name}</h5>

                                                                        </div>
                                                                    </Col>
                                                                </Row>
                                                            </CardBody>
                                                        </Card>
                                                    </Col>

                                                    <Col lg={9}>
                                                        <div className="mb-3">
                                                            <Label htmlFor="firstnameInput" className="form-label">{props.t("Full Name")}<span className="text-danger">*</span></Label>
                                                            <Input type="text" className="form-control" id="firstnameInput"
                                                                value={row.name} onChange={(e) =>handleChange('name', e.target.value)}
                                                                invalid={row.name===""?true:false}/>
                                                        </div>
                                                          <div className="mb-3">
                                                            <Label htmlFor="emailInput" className="form-label">{props.t("Email Address")}<span className="text-danger">*</span></Label>
                                                            <Input type="email" className="form-control" id="emailInput"
                                                                value={row.email}
                                                                onChange={(e) => handleChange('email', e.target.value)}
                                                                disabled={true}
                                                                invalid={row.email===""?true:false}/>
                                                        </div>

                                                        <div className="mb-3">
                                                            <Label htmlFor="phonenumberInput" className="form-label">{props.t("Phone Number")}<span className="text-danger">*</span></Label>
                                                            <PhoneInput
                                                                    id="phonenumberInput"
                                                                    country={'us'}
                                                                    required={true}
                                                                    autoFocus={true}
                                                                    value={row.mobile}
                                                                    onChange={mobile => handleChange('mobile',mobile)}
                                                                    invalid={row.mobile.trim() == ""}

                                                                />
                                                            

                                                        </div>
                                                    </Col>

                                                    <Col lg={6}>
                                                       <div className="mb-3">
                                                            <Label htmlFor="startingdateInput" className="form-label">{props.t("Joining Date")}</Label>
                                                              <Input type="text" className="form-control"
                                                              id="startingdateInput" disabled={true}
                                                             value={utils.getLocalTime(row.starting_date).toLocaleDateString() + " " + utils.getLocalTime(row.starting_date).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) }/>
                                                       </div>
                                                    </Col>
                                                    <Col lg={6}>
                                                        <div className="mb-3">
                                                            <Label htmlFor="birthDateInput" className="form-label">{props.t("Birth Date")}</Label>
                                                            <Flatpickr
                                                                id="birthDateInput"
                                                                className="form-control"
                                                                options={{dateFormat: "M d, Y"}}
                                                                value={row.birth_date==null?'':new Date(row.birth_date)}
                                                                onChange={(value) => handleChange1(value)}/>
                                                        </div>
                                                    </Col>
                                                  </Row>
                                            </Form>
                                            <Col lg={12}>
                                                <div className="hstack gap-2 justify-content-end">
                                                    <Button id="btnsave1" color="success" onClick={e => handleFormSubmit(e)} > {props.t("Save")} </Button>
                                                    <Button id="btnclose1" color="success" onClick={e => navigate("/")} > {props.t("Close")} </Button>
                                                </div>
                                             </Col>
                                        </TabPane>


                                        <TabPane tabId="2">
                                              <UpdatePassword target={""}/>
                                        </TabPane>

                                         <TabPane tabId="3">
                                             <Form>
                                               <Row className="g-2">
                                                 <Col lg={12}>

                                                      <ul className="list-unstyled mb-0">
                                                          <li className="d-flex">
                                                              <div className="flex-grow-1">
                                                                  <label htmlFor="permissionbuyer" className="form-check-label fs-14">{props.t("JetBasket Buyer")}</label>
                                                                  <p className="text-muted">{props.t("This user will be able to buy the products for JetBasket customers")}</p>
                                                              </div>
                                                              <div className="flex-shrink-0">
                                                                  <div className="form-check form-switch">
                                                                    <Input className="form-check-input" type="checkbox" role="switch" id="permissionbuyer"
                                                                    disabled={user.permission_level&2 !==2 } defaultChecked={Number(row.permission_level&2) === 2} onChange={(e) => handlePerm(e,2)}/>
                                                                  </div>
                                                              </div>
                                                          </li>
                                                          <li className="d-flex">
                                                              <div className="flex-grow-1">
                                                                  <label htmlFor="permissionteam" className="form-check-label fs-14">{props.t("Warehouse Team")}</label>
                                                                  <p className="text-muted">This user will be able warehouse tasks</p>
                                                              </div>
                                                              <div className="flex-shrink-0">
                                                                  <div className="form-check form-switch">
                                                                    <Input className="form-check-input" type="checkbox" role="switch" id="permissionteam"
                                                                    defaultChecked={Number(row.permission_level&4) === 4} onChange={(e) => handlePerm(e,4)}/>
                                                                  </div>
                                                              </div>
                                                          </li>
                                                         <li className="d-flex">
                                                              <div className="flex-grow-1">
                                                                  <label htmlFor="permissionadmin" className="form-check-label fs-14">{props.t("Admin")}</label>
                                                                  <p className="text-muted">The user will be admin of JetBasket</p>
                                                              </div>
                                                              <div className="flex-shrink-0">
                                                                  <div className="form-check form-switch">
                                                                    <Input className="form-check-input" type="checkbox" role="switch" id="permissionadmin"
                                                                    disabled={user.permission_level&8 !==8 } defaultChecked={Number(row.permission_level&8) === 8} onChange={(e) => handlePerm(e,8)}/>
                                                                  </div>
                                                              </div>
                                                          </li>


                                                      <div className="hstack gap-2 justify-content-end">
                                                        <button id="btnsave2" type="submit" className="btn btn-success" disabled={Number(user.permission_level&8) !== 8} onClick={e => handleFormSubmit(e)}>{props.t("Save")}</button>
                                                        <Button id="btnclose2" color="success" onClick={e => navigate("/")} > {props.t("Close")} </Button>

                                                      </div>

                                                    </ul>
                                                 </Col>
                                               </Row>
                                             </Form>
                                          </TabPane>

                                          <TabPane tabId="4">
                                             <Form>
                                               <Row className="g-2">

                                               <div>
                                                        <table className="table mb-3 mt-4 " style={{lineHeight: 1}}>
                                                            <tbody>

                                                            <tr>
                                                                <td>{props.t("Current deposit")} :</td>
                                                                <td id="currentdeposit" className="text-end">${deposit}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style={{verticalAlign: 'middle'}}>{props.t("How much do you want to add $")} :</td>
                                                                <td style={{
                                                                    display: 'flex',
                                                                    justifyContent: 'flex-end',
                                                                    alignItems: 'center'
                                                                }}>
                                                                    <Input
                                                                        id="addamount"
                                                                        type="text"
                                                                        style={{width: '100px', textAlign: 'right'}}
                                                                        onChange={e => setAddAmount(parseFloat(e.target.value))}
                                                                    />
                                                                </td>
                                                            </tr>
                                                           
                                                            <tr>
                                                                <td style={{verticalAlign: 'middle'}}>{props.t("Description")} :</td>
                                                                <td style={{
                                                                    display: 'flex',
                                                                    justifyContent: 'flex-end',
                                                                    alignItems: 'center'
                                                                }}>
                                                                    <Input
                                                                        id="description"
                                                                        type="text"
                                                                        style={{width: '500px', textAlign: 'right'}}
                                                                        value = {"Deposited by JetBasket"}
                                                                        onChange={e => setDescription(e.target.value)}
                                                                    />
                                                                </td>
                                                            </tr>
                                                        
                                                            </tbody>
                                                        </table>

                                                        <Col lg={12}>
                                                            <div className="hstack gap-2 justify-content-end">
                                                                <Button id="btnadddeposit" color="success" onClick={() => of_addDeposit()} > {props.t("Add Deposit")} </Button>
                                                                <Button id="btnclose3" color="success" onClick={e => navigate("/")} > {props.t("Close")} </Button>
                                                            </div>
                                                        </Col>
                                                                
                                                            
                                                    </div>
                                               </Row>
                                             <Row>
                                                                             <Col lg={12}>
                                                                                 <div className="product-list" style={{ maxHeight: '200px', overflowY: 'auto' }}>
                                                                                     <span className="fw-semibold" style={{ marginLeft: '5px' }}>
                                                                                         {props.t("Deposit History")}
                                                                                     </span>
                                                                                     <table className="table mt-1" border="2">
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
                                             </Form>
                                          </TabPane>
                                       

                                          <TabPane tabId="5">
                                              <div className="mb-4 pb-2">
                                                  <h5 className="card-title text-decoration-underline mb-3">{props.t("Security:")}</h5>
                                                  <div className="d-flex flex-column flex-sm-row mb-4 mb-sm-0">
                                                      <div className="flex-grow-1">
                                                          <h6 className="fs-14 mb-1">Two-factor Authentication</h6>
                                                          <p className="text-muted">Two-factor authentication is an enhanced
                                                              security meansur. Once enabled, you'll be required to give
                                                              two types of identification when you log into Google
                                                              Authentication and SMS are Supported.</p>
                                                      </div>
                                                      <div className="flex-shrink-0 ms-sm-3">
                                                          <Link to="#"
                                                              className="btn btn-sm btn-primary">Enable Two-facor
                                                              Authentication</Link>
                                                      </div>
                                                  </div>
                                                  <div className="d-flex flex-column flex-sm-row mb-4 mb-sm-0 mt-2">
                                                      <div className="flex-grow-1">
                                                          <h6 className="fs-14 mb-1">Secondary Verification</h6>
                                                          <p className="text-muted">The first factor is a password and the
                                                              second commonly includes a text with a code sent to your
                                                              smartphone, or biometrics using your fingerprint, face, or
                                                              retina.</p>
                                                      </div>
                                                      <div className="flex-shrink-0 ms-sm-3">
                                                          <Link to="#" className="btn btn-sm btn-primary">Set
                                                              up secondary method</Link>
                                                      </div>
                                                  </div>
                                                  <div className="d-flex flex-column flex-sm-row mb-4 mb-sm-0 mt-2">
                                                      <div className="flex-grow-1">
                                                          <h6 className="fs-14 mb-1">Backup Codes</h6>
                                                          <p className="text-muted mb-sm-0">A backup code is automatically
                                                              generated for you when you turn on two-factor authentication
                                                              through your iOS or Android Twitter app. You can also
                                                              generate a backup code on twitter.com.</p>
                                                      </div>
                                                      <div className="flex-shrink-0 ms-sm-3">
                                                          <Link to="#"
                                                              className="btn btn-sm btn-primary">Generate backup codes</Link>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div className="mb-3">
                                                  <h5 className="card-title text-decoration-underline mb-3">Application Notifications:</h5>
                                                  <ul className="list-unstyled mb-0">
                                                      <li className="d-flex">
                                                          <div className="flex-grow-1">
                                                              <label htmlFor="directMessage"
                                                                  className="form-check-label fs-14">Direct messages</label>
                                                              <p className="text-muted">Messages from people you follow</p>
                                                          </div>
                                                          <div className="flex-shrink-0">
                                                              <div className="form-check form-switch">
                                                                  <Input className="form-check-input" type="checkbox"
                                                                      role="switch" id="directMessage" defaultChecked />
                                                              </div>
                                                          </div>
                                                      </li>
                                                      <li className="d-flex mt-2">
                                                          <div className="flex-grow-1">
                                                              <Label className="form-check-label fs-14"
                                                                  htmlFor="desktopNotification">
                                                                  Show desktop notifications
                                                              </Label>
                                                              <p className="text-muted">Choose the option you want as your
                                                                  default setting. Block a site: Next to "Not allowed to
                                                                  send notifications," click Add.</p>
                                                          </div>
                                                          <div className="flex-shrink-0">
                                                              <div className="form-check form-switch">
                                                                  <Input className="form-check-input" type="checkbox"
                                                                      role="switch" id="desktopNotification" defaultChecked />
                                                              </div>
                                                          </div>
                                                      </li>
                                                      <li className="d-flex mt-2">
                                                          <div className="flex-grow-1">
                                                              <Label className="form-check-label fs-14"
                                                                  htmlFor="emailNotification">
                                                                  Show email notifications
                                                              </Label>
                                                              <p className="text-muted"> Under Settings, choose Notifications.
                                                                  Under Select an account, choose the account to enable
                                                                  notifications for. </p>
                                                          </div>
                                                          <div className="flex-shrink-0">
                                                              <div className="form-check form-switch">
                                                                  <Input className="form-check-input" type="checkbox"
                                                                      role="switch" id="emailNotification" />
                                                              </div>
                                                          </div>
                                                      </li>
                                                      <li className="d-flex mt-2">
                                                          <div className="flex-grow-1">
                                                              <Label className="form-check-label fs-14"
                                                                  htmlFor="chatNotification">
                                                                  Show chat notifications
                                                              </Label>
                                                              <p className="text-muted">To prevent duplicate mobile
                                                                  notifications from the Gmail and Chat apps, in settings,
                                                                  turn off Chat notifications.</p>
                                                          </div>
                                                          <div className="flex-shrink-0">
                                                              <div className="form-check form-switch">
                                                                  <Input className="form-check-input" type="checkbox"
                                                                      role="switch" id="chatNotification" />
                                                              </div>
                                                          </div>
                                                      </li>
                                                      <li className="d-flex mt-2">
                                                          <div className="flex-grow-1">
                                                              <Label className="form-check-label fs-14"
                                                                  htmlFor="purchaesNotification">
                                                                  Show purchase notifications
                                                              </Label>
                                                              <p className="text-muted">Get real-time purchase alerts to
                                                                  protect yourself from fraudulent charges.</p>
                                                          </div>
                                                          <div className="flex-shrink-0">
                                                              <div className="form-check form-switch">
                                                                  <Input className="form-check-input" type="checkbox"
                                                                      role="switch" id="purchaesNotification" />
                                                              </div>
                                                          </div>
                                                      </li>
                                                  </ul>
                                              </div>
                                              <div>
                                                  <h5 className="card-title text-decoration-underline mb-3">{props.t("Delete This Account:")}</h5>
                                                  <p className="text-muted">Please enter the full name of the user you want to delete :
                                                  </p>
                                                  <div>
                                                      <Input type="name" className="form-control" id="nameInput" placeholder={props.t("Enter the full name")} style={{ maxWidth: "265px" }}
                                                      onChange={(e) => handleDeleteUserName(e)}/>
                                                  </div>
                                                  <div className="hstack gap-2 mt-3">
                                                      <Link id="linkdeleteuser" to="#" onClick={() => of_deleteUser()} className="btn btn-soft-danger">{props.t("Close & Delete This Account")}</Link>
                                                      <Link  id="linkcancel" to="#" className="btn btn-light">{props.t("Cancel")}</Link>
                                                  </div>
                                              </div>
                                          </TabPane>





                                     </TabContent>
                                </CardBody>
                            </Card>
                        </Col>
                    </Row>
                </Container>
            </div>
           ))}
        <NotificationContainer/>
        </div>
        </React.Fragment>
    );
}

export default withRouter(withTranslation()(UserProfile));


