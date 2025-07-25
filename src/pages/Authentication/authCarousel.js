import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import React from "react";
import { Col } from "reactstrap";
import { Carousel } from "react-responsive-carousel";
import "react-responsive-carousel/lib/styles/carousel.min.css";
import { Link } from "react-router-dom";

// Import Images
import logoLight from "../../assets/images/logo-light.png";

const AuthSlider = (props) => {
    return (
        <React.Fragment>

            <Col lg={8}>
                <div className="p-lg-2 p-4 auth-one-bg " 
                 style={{ //SİNAN: aşağıda ki kod olmaz ise logindeki resim mobilde tam görünmüyor.
                  height:[window.innerWidth >= 992 ? '100%' : '380px']
                  }}>
               
                    <div className="bg-overlaym"></div>
                    <div className="position-relative h-100 d-flex flex-column">
                        <div className="mb-4">
                            
                        </div>

{/*
<div className="mt-auto" style={{
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
}}>

<Carousel
  showThumbs={false}
  autoPlay={true}
  showArrows={false}
  showStatus={false}
  infiniteLoop={true}
  className="carousel slide"
  id="qoutescarouselIndicators"
>
   <div style={{
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center',
      height: '150px',
    }}>
      <div style={{
        width: '70%', // moved here
        backgroundColor: 'rgba(233, 229, 229, 0.6)',
        padding: '20px',
        textAlign: 'center',
        color: 'black'
      }}>
      <p style={{ fontStyle: 'italic', fontSize: '19px', margin: 0 }}>
        {props.t("The smartest way to shop from the USA")}
      </p>
    </div>
  </div>

  <div style={{
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    height: '150px', // adjust height as needed
  }}>
    <div style={{
      backgroundColor: 'rgba(233, 229, 229, 0.6)',
      width: '70%',
      padding: '20px',
      textAlign: 'center',
      color: 'black'
    }}>
      <p style={{ fontStyle: 'italic', fontSize: '19px', margin: 0 }}>
        {props.t("You shop, we ship")}
      </p>
    </div>
  </div>

  <div style={{
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    height: '150px', // adjust height as needed
  }}>
    <div style={{
      backgroundColor: 'rgba(233, 229, 229, 0.6)',
      width: '70%',
      padding: '20px',
      textAlign: 'center',
      color: 'black'
    }}>
      <p style={{ fontStyle: 'italic', fontSize: '19px', margin: 0 }}>
        {props.t("Simple, easy, fast.")}
      </p>
    </div>
  </div>
</Carousel>

    </div>
    */}
</div>


                      
                 
                </div>
            </Col>
        </React.Fragment >
    );
};
export default withRouter(withTranslation()(AuthSlider));

/*
<Link to="https://jetbasket.us" className="d-block">
    <img src={logoLight} alt="" height="58" />
</Link>
*/