import React, { useState, useEffect } from 'react';
import {Button, Dropdown, DropdownItem, DropdownMenu, DropdownToggle} from 'reactstrap';
import avatar1 from "../../assets/images/users/user-dummy-img.jpg";
import { useNavigate } from 'react-router-dom';
import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import DepositModal from "../../pages/DepositModal";

const ProfileDropdown = (props) => {

    //const { profileUser } = useSelector(state => ({ user: state.Profile.user }));
    const [userName, setUserName] = useState("Login");
    const [picture_path, setPicture_path] = useState("");
    const [depositModal, setDepositModal] = useState(false)
    const [user, setuser] = useState(null);
    const [isProfileDropdown, setIsProfileDropdown] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        setuser(user)
     
        if (user.name=== undefined){
            setUserName("Login")
        }else{
           setUserName(user.alias)
           setPicture_path(user.picture_path)
        }
    }, [userName]);  // profileUser bu neyse bunu kaldirdim]);


    const toggleProfileDropdown = () => {
        setIsProfileDropdown(!isProfileDropdown);
    };


 

    return (
            <React.Fragment>

                    <Dropdown isOpen={isProfileDropdown} toggle={toggleProfileDropdown}
                              className="ms-sm-3 header-item topbar-user">
                        <DropdownToggle tag="button" type="button" className="btn">
                                <span className="d-flex align-items-center">
                                    <img className="rounded-circle header-profile-user"
                                         src={picture_path === undefined || picture_path === null ? avatar1 : process.env.REACT_APP_API_URL + "/images/" + picture_path}
                                         alt="Header Avatar"/>
                                    <span className="text-start ms-xl-2">
                                        <span
                                            className="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{userName}</span>
                                        <span className="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text"></span>
                                    </span>
                                </span>
                        </DropdownToggle>

                    
                            <DropdownMenu className="dropdown-menu-end" style={{width: '250px'}}>
                            <div className={"mb-2 "}>
                                <span
                                    className="align-middle fs-16 ms-4 text-muted">{props.t("Welcome") + " " + userName + "!"}</span>
                            </div>

                            {/*sadece customer burada depositosunu gorecek */}
                            {user && user.permission_level==0 &&
                                <DropdownItem href="#" onClick={() => setDepositModal(true)}><i
                                className="mdi mdi-currency-usd fs-18 align-middle me-1" style={{ color: 'green' }}></i>
                                <span className="align-middle fs-15" style={{color:'green'}}>{props.t("Deposit")}</span></DropdownItem>
                            }

                        {user  && 
                        <>

                            <DropdownItem href="#" onClick={() => navigate('/userProfile', {state: {user_id: user.id}})}><i
                                className="mdi mdi-account-circle text-muted fs-18 align-middle me-1"></i>
                                <span className="align-middle fs-15">{props.t("Account")}</span></DropdownItem>


                            <DropdownItem href="#" onClick={() => navigate('/logout')}>
                                <i className="mdi mdi-logout text-muted fs-18 align-middle me-1"></i>
                                <span className="align-middle fs-15"
                                      data-key="t-logout">{props.t("Logout")}</span>
                             </DropdownItem>
                        </> 
                        }


                         </DropdownMenu>

                    </Dropdown>


           
                <DepositModal
                    depositModal={depositModal}
                    setDepositModal={setDepositModal}
                    user={user}
                />
           


            </React.Fragment>

    );
};

//export default ProfileDropdown;
export default withRouter(withTranslation()(ProfileDropdown));