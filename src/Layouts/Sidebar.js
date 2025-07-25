import React, { useState, useEffect, useRef } from 'react';
import { Link } from "react-router-dom";
import SimpleBar from "simplebar-react";

//import logo
import logoSm from "../assets/images/logo-sm.png";
//import logoDark from "../assets/images/logo-dark.png";
//import logoLight from "../assets/images/logo-light.png";

//Import Components
import VerticalLayout from "./VerticalLayouts";
import TwoColumnLayout from "./TwoColumnLayout";
import { Container } from "reactstrap";
import HorizontalLayout from "./HorizontalLayout";

const Sidebar = ({ layoutType }) => {
  const [user, setUser] = useState("");

  useEffect(() => {

  const authuser = sessionStorage.getItem("authUser")
  const user = JSON.parse(authuser);
  setUser(user)

    var verticalOverlay = document.getElementsByClassName("vertical-overlay");
    if (verticalOverlay) {
      verticalOverlay[0].addEventListener("click", function () {
        document.body.classList.remove("vertical-sidebar-enable");
      });
    }
  },[]);

       // solda ki markaların inkleri ile ilgili kodlar
  const containerRef = useRef(null);
  const [overflowStyle, setOverflowStyle] = useState("hidden"); // Başlangıç değeri

let markalardayim = false
const normalSpeed = 50;
let currentSpeed = normalSpeed;
let scrollInterval;


// Start Scroll Function
const startScroll = (speed = normalSpeed) => {
    console.log("kaydirmaHizi: " + speed);
    const container = containerRef.current;
    if (container) {
      stopScroll(); // Çakışmaları önlemek için önce mevcut interval durdurulur
      if (!scrollInterval) {
            scrollInterval = setInterval(() => {
                  if (Math.ceil(container.scrollTop + container.clientHeight) >= container.scrollHeight) {
                    container.scrollTop = 0; // Sonsuz scroll: başa dön
                  } else {
                    container.scrollTop += 1; // Increment scroll position
                  }
            }, speed); // Hız ayarı
              console.log("kaydirmaHizi2: " + speed);
          }
    }

};

  useEffect(() => {
    startScroll(currentSpeed); // Sayfa yüklendiğinde scroll başlat
    return () => stopScroll(); // Kaynak temizliği
  }, []);

  // Scroll durdur
  const stopScroll = () => {
    if (scrollInterval) {
      clearInterval(scrollInterval);
      scrollInterval = null;
    }
  };
 
  const HiziYavaslat = () => {
    if (document.documentElement.getAttribute('data-sidebar-size') === 'sm') {
        document.documentElement.setAttribute('data-sidebar-size', 'lg');
    }
    setOverflowStyle("auto"); // Fare üzerine geldiğinde 'auto' yap
  };

  const hiziArtir = () => {
      setOverflowStyle("hidden"); // Fare gidince  geldiğinde 'auto' yap
      markalardayim=false;
      logoKucult();
  };
   
  const logoKucult = () => {
        setTimeout(() => {
          if (markalardayim===true) {
            return
          }
          if (document.documentElement.getAttribute('data-sidebar-size') === 'lg') {
              document.documentElement.setAttribute('data-sidebar-size', 'sm');
            }
    }, 200); 
  };  
  
  const markaDevam = () => {
    markalardayim=true;
  };  
   

const scrollContainerStyle = {
  width: '100%',
  height: '500px',
  overflow: overflowStyle,
  position: 'relative',
  marginTop: "50px", // Üstten 20px boşluk bırak
  display: "flex", // Flexbox kullan
  flexDirection: "column", // İçerikleri dikey olarak hizala
  justifyContent: "flex-start", // İçerikleri dikeyde ortala
  alignItems: "center", // İçerikleri yatayda ortala

};

const linkStyle = {
  display: 'block',
  textAlign: 'center',
  marginBottom: '10px',
};

const imgStyle = {
 width: '80%',
  maxWidth: '80%',
  height: '160',
};
    


      // SON solda ki markaların inkleri ileilgili kodlar bitiş


  const addEventListenerOnSmHoverMenu = () => {
    // add listener Sidebar Hover icon on change layout from setting
    if (document.documentElement.getAttribute('data-sidebar-size') === 'sm-hover') {
      document.documentElement.setAttribute('data-sidebar-size', 'sm-hover-active');
    } else if (document.documentElement.getAttribute('data-sidebar-size') === 'sm-hover-active') {
      document.documentElement.setAttribute('data-sidebar-size', 'sm-hover');
    } else {
      document.documentElement.setAttribute('data-sidebar-size', 'sm-hover');
    }
 

 
      
  };

  return (
    <React.Fragment>
      <div className="app-menu navbar-menu">
        <div className="navbar-brand-box">
          <Link to="/" className="logo logo-dark">
            <span className="logo-sm">
              <img src={logoSm} alt="" height="22" />
            </span>
            <span className="logo-lg">
              <img src={process.env.REACT_APP_API_URL + "/images/" +user.logo_path} alt="" height="160" />
            </span>
          </Link>

          <Link to="/" className="logo logo-light">
            <span className="logo-sm">
              <img src={logoSm} alt="" height="22" />
            </span>
            <span className="logo-lg">
            <div className="mb-3 mt-3">
              <img src={process.env.REACT_APP_API_URL + "/images/" + user.logo_path} alt="" height="160" />
            </div>
            </span>
          </Link>
          <button
            onClick={addEventListenerOnSmHoverMenu}
            type="button"
            className="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover"
          >
            <i className="ri-record-circle-line"></i>
          </button>
        </div>
        {layoutType === "horizontal" ? (
          <div id="scrollbar">
            <Container fluid>
              <div id="two-column-menu"></div>
              <ul className="navbar-nav" id="navbar-nav">
                <HorizontalLayout />
              </ul>
            </Container>
          </div>
        ) : layoutType === 'twocolumn' ? (
          <React.Fragment>
            <TwoColumnLayout layoutType={layoutType} />
            <div className="sidebar-background"></div>
          </React.Fragment>
        ) : (
          <React.Fragment>
            <SimpleBar id="scrollbar" className="h-100">
              <Container fluid>
                <div id="two-column-menu"></div>
                <ul className="navbar-nav" id="navbar-nav">
                  <VerticalLayout layoutType={layoutType} />
                 
                </ul>
              </Container>
            </SimpleBar>
            <div className="sidebar-background"></div>
          </React.Fragment>
        )}
      </div>
      <div className="vertical-overlay"></div>
    </React.Fragment>
  );
};

export default Sidebar;
