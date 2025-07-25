import React from "react";
import { Link } from "react-router-dom";
import { Card, CardBody, Col, Container, Row } from "reactstrap";
import BreadCrumb from "../../../Components/Common/BreadCrumb";
import FeatherIcon from "feather-icons-react";

const PrivacyPolicy = () => {
  return (
    <React.Fragment>
      <div className="page-content">
        <Container fluid>
          <BreadCrumb title="Privacy Policy" pageTitle="Pages" />
          <Row className="justify-content-center">
            <Col lg={10}>
              <Card>
                <div className="bg-soft-info position-relative">
                  <CardBody className="p-5">
                    <div className="text-center">
                      <h3>Privacy Policy</h3>
                      <p className="mb-0 text-muted">
                        Last update: 18 Jan, 2024
                      </p>
                    </div>
                  </CardBody>
                  <div className="shape">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      version="1.1"
                      xmlnsXlink="http://www.w3.org/1999/xlink"
                      // xmlns:svgjs="http://svgjs.com/svgjs"
                      width="1440"
                      height="60"
                      preserveAspectRatio="none"
                      viewBox="0 0 1440 60"
                    >
                      <g mask='url("#SvgjsMask1001")' fill="none">
                        <path
                          d="M 0,4 C 144,13 432,48 720,49 C 1008,50 1296,17 1440,9L1440 60L0 60z"
                          style={{ fill: "var(--vz-card-bg-custom)" }}
                        ></path>
                      </g>
                      <defs>
                        <mask id="SvgjsMask1001">
                          <rect width="1440" height="60" fill="#ffffff"></rect>
                        </mask>
                      </defs>
                    </svg>
                  </div>
                </div>
                <CardBody className="p-4">


                  <div className="d-flex">
                    <div className="flex-shrink-0 me-3">
                      <FeatherIcon
                        icon="check-circle"
                        className="text-success icon-dual-success icon-xs"
                      />
                    </div>
                    <div className="flex-grow-1">
                      <h5>Privacy Policy for ComfyShip App</h5>
                      <p className="text-muted">
                        ComfyShip built the ComfyShip App as a free App. This service is provided by ComfyShip at no cost and is intended for use as is.
                        This page is used to inform visitors regarding our policies with the collection, use, and disclosure of
                        Personal Information if anyone decided to use our Service.
                        If you choose to use our Service, then you agree to the collection and use of information in relation to this policy.
                        The Personal Information that we collect is used for providing and improving the Service.
                        We will not use or share your information with anyone except as described in this Privacy Policy.
                        The terms used in this Privacy Policy have the same meanings as in our Terms and Conditions,
                        which are accessible at ComfyShip unless otherwise defined in this Privacy Policy.
                      </p>
                      </div>

                   </div>



                  <div className="d-flex">
                    <div className="flex-shrink-0 me-3">
                      <FeatherIcon
                        icon="check-circle"
                        className="text-success icon-dual-success icon-xs"
                      />
                    </div>
                    <div className="flex-grow-1">
                      <h5>Information Collection and Use</h5>
                      <p className="text-muted">
                       When you register for an Account, we may ask for your
                       contact information, including items such as name, email address, and phone number and user id in your warehouse system.
                       We are also collecting your device id to able to send you notifications.
                       The information that we request will be retained by us and used as described in this privacy policy.
                       The app does use third-party services that may collect information used to identify you.
                      </p>
                    </div>
                  </div>

                  <div className="d-flex">
                       <div className="flex-shrink-0 me-3">
                         <FeatherIcon
                           icon="check-circle"
                           className="text-success icon-dual-success icon-xs"
                         />
                       </div>
                       <div className="flex-grow-1">
                         <h5>Cookies</h5>
                         <p className="text-muted">
                          Cookies are files with a small amount of data that are commonly used as anonymous unique identifiers.
                          These are sent to your browser from the websites that you visit and are stored on your device's internal memory.
                          This Service does not use these “cookies” explicitly.
                         </p>
                       </div>
                  </div>


                  <div className="d-flex">
                       <div className="flex-shrink-0 me-3">
                         <FeatherIcon
                           icon="check-circle"
                           className="text-success icon-dual-success icon-xs"
                         />
                       </div>
                       <div className="flex-grow-1">
                         <h5>Service Providers</h5>
                         <p className="text-muted">
                           We may employ third-party companies and individuals due to the following reasons:
                           To facilitate our Service;
                           To provide the Service on our behalf;
                           To perform Service-related services; or
                           To assist us in analyzing how our Service is used.
                           We want to inform users of this Service that these third parties have access to their Personal Information.
                           The reason is to perform the tasks assigned to them on our behalf.
                           However, they are obligated not to disclose or use the information for any other purpose.
                         </p>
                       </div>
                  </div>



                  <div className="d-flex">
                       <div className="flex-shrink-0 me-3">
                         <FeatherIcon
                           icon="check-circle"
                           className="text-success icon-dual-success icon-xs"
                         />
                       </div>
                       <div className="flex-grow-1">
                         <h5>Security</h5>
                         <p className="text-muted">
                            We value your trust in providing us your Personal Information, thus we are striving to use commercially
                            acceptable means of protecting it.
                            But remember that no method of transmission over the internet, or method of electronic storage is 100% secure and reliable,
                            and we cannot guarantee its absolute security.</p>
                       </div>
                  </div>



                  <div className="d-flex">
                       <div className="flex-shrink-0 me-3">
                         <FeatherIcon
                           icon="check-circle"
                           className="text-success icon-dual-success icon-xs"
                         />
                       </div>
                       <div className="flex-grow-1">
                         <h5>Changes to This Privacy Policy</h5>
                         <p className="text-muted">
                            We may update our Privacy Policy from time to time. Thus, you are advised to review this
                            page periodically for any changes. We will notify you of any changes by posting the new
                            Privacy Policy on this page.
                            This policy is effective as of 2024-01-18.</p>
                       </div>
                  </div>

                  <div className="d-flex">
                       <div className="flex-shrink-0 me-3">
                         <FeatherIcon
                           icon="check-circle"
                           className="text-success icon-dual-success icon-xs"
                         />
                       </div>
                       <div className="flex-grow-1">
                         <h5>Contact Us</h5>
                         <p className="text-muted">
                            If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us at
                         </p>
                          <p className="text-muted">  Email: info@comfyship.com</p>
                          <p className="text-muted">  Address: 809 Hylton rd. STE 1 Pennsauken ,NJ, USA, 08110.</p>

                       </div>
                  </div>



                </CardBody>
              </Card>
            </Col>
          </Row>
        </Container>
      </div>
    </React.Fragment>
  );
};

export default PrivacyPolicy;

