import React, { useState,useEffect  } from 'react';
import { Col, Dropdown, DropdownMenu, DropdownToggle, Nav, NavItem, NavLink, Row, TabContent, TabPane } from 'reactstrap';
import { Link } from 'react-router-dom';
import {useNavigate } from 'react-router-dom';
import classnames from 'classnames';
import bell from "../../assets/images/svg/bell.svg";
import SimpleBar from "simplebar-react";
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';



const NotificationDropdown = () => {
    const [isNotificationDropdown, setIsNotificationDropdown] = useState(false);
    const toggleNotificationDropdown = () => { setIsNotificationDropdown(!isNotificationDropdown) };
    const [data, setData] = useState([]);
    const [user, setUser] = useState(null);
    const navigate = useNavigate();
    const [activeTab, setActiveTab] = useState('1');
    const toggleTab = (tab) => {
        if (activeTab !== tab) {
            setActiveTab(tab);
        }
    };



   //tiklanan mesaj select edilirken goruldu yapiliyor burada sadece notification mesajlarini kaldiriyoruz.
   function of_removeItem(id){
       setData(data.filter(a =>a.id !== id))
       setIsNotificationDropdown(false)
   }


   useEffect(() => {
        const authuser = sessionStorage.getItem("authUser")
        const user = JSON.parse(authuser);
        setUser(user)
        //of_getUnreadChatData(user)
    }, []);

   return (
        <React.Fragment>
            <NotificationContainer/>
        </React.Fragment>
    );
};

export default NotificationDropdown;

/*
    return (
        <React.Fragment>
            <Dropdown isOpen={isNotificationDropdown} toggle={toggleNotificationDropdown} className="topbar-head-dropdown ms-1 header-item">
                <DropdownToggle type="button" tag="button" className="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle">
                    <i className='bx bx-bell fs-22'></i>
                    <span
                        className="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">{data.length}<span
                            className="visually-hidden">unread messages</span></span>
                </DropdownToggle>
                <DropdownMenu className="dropdown-menu-lg dropdown-menu-end p-0">
                    <div className="dropdown-head bg-primary bg-pattern rounded-top">
                        <div className="p-3">
                            <Row className="align-items-center">
                                <Col>
                                    <h6 className="m-0 fs-16 fw-semibold text-white"> Notifications </h6>
                                </Col>
                                <div className="col-auto dropdown-tabs">
                                    <span className="badge badge-soft-light fs-13">{data.length + " New"}</span>
                                </div>
                            </Row>
                        </div>

                        <div className="px-2 pt-2">
                            <Nav className="nav-tabs dropdown-tabs nav-tabs-custom">
                                <NavItem>
                                    <NavLink
                                        href="#"
                                        className={classnames({ active: activeTab === '1' })}
                                        onClick={() => { toggleTab('1'); }}
                                    >
                                        {"Chat"}
                                    </NavLink>
                                </NavItem>
                                <NavItem>
                                    <NavLink
                                        href="#"
                                        className={classnames({ active: activeTab === '2' })}
                                        onClick={() => { toggleTab('2'); }}
                                    >
                                        Messages
                                    </NavLink>
                                </NavItem>
                                <NavItem>
                                    <NavLink
                                        href="#"
                                        className={classnames({ active: activeTab === '3' })}
                                        onClick={() => { toggleTab('3'); }}
                                    >
                                        Alerts
                                    </NavLink>
                                </NavItem>
                            </Nav>
                        </div>

                    </div>

                    <TabContent activeTab={activeTab}>
                        <TabPane tabId="1" className="py-2 ps-2">
                            <SimpleBar style={{ maxHeight: "300px" }} className="pe-2">

                               {data.map((row, key) => (
                                   <div className="text-reset notification-item d-block dropdown-item position-relative active" key={key}>
                                        <div className="d-flex align-items-center">
                                            <img src={process.env.REACT_APP_API_URL + "/image/" + row.picture_path}
                                                className="me-3 rounded-circle avatar-xs" alt="user-pic" />
                                            <div className="px-2 fs-15">
                                                <Link to="/Chat" state={{ user_id: row.user_id, name: row.name, picture_path: row.picture_path }}
                                                 onClick={() => of_removeItem(row.user_id)} className="stretched-link"><h6 className="mt-0 mb-1 fs-13 fw-semibold">{row.name}</h6></Link>

                                            </div>
                                            <div className="px-2">
                                                 <Link to="/Chat" state={{ user_id: row.user_id, name: row.name, picture_path: row.picture_path }}
                                                  onClick={() => of_removeItem(row.user_id)} className="stretched-link"><h6 className="mt-0 mb-1 fs-13 fw-semibold">{row.count}</h6></Link>
                                            </div>
                                        </div>
                                    </div>
                               ))}

                                 <div className="my-3 text-center">
                                    <button type="button" className="btn btn-soft-success waves-effect waves-light">
                                        All Notifications <i className="ri-arrow-right-line align-middle"></i></button>
                                </div>
                            </SimpleBar>

                        </TabPane>

                        <TabPane tabId="2" className="py-2 ps-2">
                            <SimpleBar style={{ maxHeight: "300px" }} className="pe-2">
                                <div className="text-reset notification-item d-block dropdown-item">

                                </div>
                                 <div className="my-3 text-center">
                                    <button type="button" className="btn btn-soft-success waves-effect waves-light">View
                                        All Messages <i className="ri-arrow-right-line align-middle"></i></button>
                                </div>
                            </SimpleBar>
                        </TabPane>
                        <TabPane tabId="3" className="p-4">
                            <div className="w-25 w-sm-50 pt-3 mx-auto">
                                <img src={bell} className="img-fluid" alt="user-pic" />
                            </div>
                            <div className="text-center pb-5 mt-2">
                                <h6 className="fs-18 fw-semibold lh-base">Hey! You have no any notifications </h6>
                            </div>
                        </TabPane>
                    </TabContent>
                </DropdownMenu>
            </Dropdown>
            <NotificationContainer/>
        </React.Fragment>
    );
};

export default NotificationDropdown;
*/