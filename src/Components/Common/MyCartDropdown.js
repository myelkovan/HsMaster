import React, { useRef, useEffect, useState } from 'react';
import { Col, Dropdown, DropdownMenu, DropdownToggle, Row } from 'reactstrap';
import { Link } from 'react-router-dom';
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import {useNavigate } from 'react-router-dom';
import SimpleBar from "simplebar-react";



const MyCartDropdown = (props) => {
    const [data, setData] = useState([]);
    const [user, setUser] = useState(null);
    const navigate = useNavigate();
    const [isCartDropdown, setIsCartDropdown] = useState(false);


    const toggleCartDropdown = () => {
        setIsCartDropdown(!isCartDropdown);
    };


   function of_getUnseenOffers(user){
      //getUnseenOffers(user.user_id, user.token).then(response => {
      //     setData(response);
      // }).catch(error => NotificationManager.error(error.message,'Error'));
    }


     //tiklanan offerlar select edilirken goruldu yapiliyor burada sadece notification mesajlarini kaldiriyoruz.
     function of_removeItem(demand_id){
          setData(data.filter(a =>a.demand_id !== demand_id))
          setIsCartDropdown(false);
     }

    useEffect(() => {
         const authuser = sessionStorage.getItem("authUser")
         const user = JSON.parse(authuser);
         setUser(user)
        // of_getUnseenOffers(user)
     }, []);

    return (
        <React.Fragment>
          <NotificationContainer/>
        </React.Fragment>
    );
};

export default MyCartDropdown;


/*
    return (
        <React.Fragment>
            <Dropdown isOpen={isCartDropdown} toggle={toggleCartDropdown} className="topbar-head-dropdown ms-1 header-item">
                <DropdownToggle type="button" tag="button" className="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle">
                    <i className='bx bx-ordersping-bag fs-22'></i>
                    <span
                        className="position-absolute cartitem-badge topbar-badge fs-10 translate-middle badge rounded-pill bg-info">{data.length}<span
                            className="visually-hidden">unseen offers</span></span>
                </DropdownToggle>
                <DropdownMenu className="dropdown-menu-xl dropdown-menu-end p-0 dropdown-menu-cart"
                    aria-labelledby="page-header-cart-dropdown">
                    <div className="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                        <Row className="align-items-center">
                            <Col>
                                <h6 className="m-0 fs-16 fw-semibold"> Offers </h6>
                            </Col>
                            <div className="col-auto">
                                <span className="badge badge-soft-warning fs-13"><span className="cartitem-badge"> {data.length} </span> offers</span>
                            </div>
                        </Row>
                    </div>
                    <SimpleBar style={{ maxHeight: "300px" }}>
                        <div className="p-2">
                            {data.length===0
                                ?(<div className="text-center empty-cart" id="empty-cart">
                                     <div className="avatar-md mx-auto my-3">
                                        <div className="avatar-title bg-soft-info text-info fs-36 rounded-circle">
                                            <i className='bx bx-cart'></i>
                                        </div>
                                    </div>
                                   <h5 className="mb-3">No New Offer!</h5>
                                </div>)

                                :data.map((row, key) =>
                                  (<div className="d-block dropdown-item text-wrap dropdown-item-cart px-3 py-2" key={key}>
                                    <div className="d-flex align-items-center">
                                          <img src={process.env.REACT_APP_API_URL + "/image/flag/" + row.flag}
                                            className="me-3 p-2 bg-light" alt="user-pic" height="40" width="50" />
                                        <div className="flex-1">
                                            <h6 className="mt-0 mb-1 fs-14">
                                                <Link to="/DemandOffers" state={{ demand_id: row.demand_id, user:user }}
                                                onClick={() => of_removeItem(row.demand_id)} >
                                                {row.demand_country}
                                                </Link>
                                            </h6>
                                            <p className="mb-0 fs-12 text-muted">
                                               <Link to="/DemandOffers" state={{ demand_id: row.demand_id, user:user }}
                                                    onClick={() => of_removeItem(row.demand_id)}>
                                                    {new Date(row.offer_date).toLocaleDateString() + " " + new Date(row.offer_date).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}
                                               </Link>
                                            </p>
                                        </div>
                                        <div className="px-2">
                                            <Link to="/DemandOffers" state={{ demand_id: row.demand_id, user:user }} >
                                                 <span className="cart-item-price">{row.price? row.currency + row.price : ""}</span>
                                             </Link>
                                        </div>
                                    </div>
                                  </div>
                                ))
                            }
                        </div>
                    </SimpleBar>
                    <div className="p-3 border-bottom-0 border-start-0 border-end-0 border-dashed border" id="checkout-elem">

                    </div>
                </DropdownMenu>
            </Dropdown>
            <NotificationContainer/>
        </React.Fragment>
    );
};

export default MyCartDropdown;
*/