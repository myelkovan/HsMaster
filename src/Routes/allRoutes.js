import React from "react";
import { Navigate } from "react-router-dom";

//MY
import UserProfile from "../pages/UserProfile";
import DefaultPage from "../pages/DefaultPage";
import PayConfirm from "../pages/PayConfirm";
import DepositConfirm from "../pages/DepositConfirm";
import Mail from '../pages/Mail';
import Dashboard1 from "../pages/Dashboard1";

//pages
import Maintenance from '../pages/Pages/Maintenance/Maintenance';
import ComingSoon from '../pages/Pages/ComingSoon/ComingSoon';


//login
import Login from "../pages/Authentication/Login";
import ForgetPasswordPage from "../pages/Authentication/ForgetPassword";
import Logout from "../pages/Authentication/Logout";
import Register from        "../pages/Authentication/Register";
import RegisterSuccess from "../pages/Authentication/RegisterSuccess";
import UpdatePassword from "../pages/Authentication/UpdatePassword";
import PasswordCreate from "../pages/Authentication/PasswordCreate";
import Activate from "../pages/Authentication/Activate";
import OnePage from "../pages/Landing/OnePage";
import PrivacyPolicy from '../pages/Pages/PrivacyPolicy';
import TermsCondition from '../pages/Pages/TermsCondition';


   const adminRoutes = [
            //{ path: "/OrdersAdmin", component: <OrdersAdmin /> },

      ];

 
  
let authProtectedRoutes = [

        //   BURDAN SONRAKİ SAYFALAR NORMAL KULLANICILARA GÖSTERİLECEK SAYFALAR
  { path: "/userProfile", component: <UserProfile /> },
  { path: "/pages-terms-condition", component: <TermsCondition /> },
  { path: "/DefaultPage", component: <DefaultPage /> },
  { path: "/Mail", component: <Mail /> },
  { path: "/UpdatePassword", component: <UpdatePassword /> },
   { path: "/Dashboard1", component: <Dashboard1 /> },
  { path: "/", exact: true, component: <Navigate to="/Dashboard1" /> },
  { path: "*", component: <Navigate to="/Dashboard1" /> },

];



const publicRoutes = [
  // Authentication Page
  { path: "/logout", component: <Logout /> },{ path: "/login", component: <Login /> },
  { path: "/forgot-password", component: <ForgetPasswordPage /> },
  { path: "/register", component: <Register /> },
  { path: "/registersuccess", component: <RegisterSuccess /> },
  { path: "/password-create/:email/:reset_token", component: <PasswordCreate /> },
  { path: "/activate/:email/:token", component: <Activate /> },
  { path: "/pages-maintenance", component: <Maintenance /> },
  { path: "/pages-coming-soon", component: <ComingSoon /> },
  { path: "/landing", component: <OnePage /> },
  { path: "/pages-privacy-policy", component: <PrivacyPolicy /> },
  { path: "/PayConfirm/:orders_id/:resettoken/:payType", component: <PayConfirm /> },
  { path: "/DepositConfirm/:user_id/:stripe_product_id/:resettoken", component: <DepositConfirm /> },
];


export { authProtectedRoutes, publicRoutes,adminRoutes };
