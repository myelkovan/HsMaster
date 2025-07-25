/*
Kullanici Signup yaptiktan sonra kendisine gonderilen maile tiklarsa activateUser yapiliyor
ve login sayfasi aciliyor
*/
import { withTranslation } from "react-i18next";
import withRouter from '../../Components/Common/withRouter';
import React, { useEffect, useState} from "react";
import {NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import { useNavigate, useParams } from 'react-router-dom';
import { activateUser } from '../../services/service_user'


function Activate(props){
    const navigate = useNavigate();
    const { email, token } = useParams();


  useEffect(() => {
    activateUser(email, token).then(response => {
        if (response >= 1){
            NotificationManager.success(props.t('Your account is activated!'),'',4000)
            navigate('/login')
        }else{
            NotificationManager.error(props.t('Your account could not activated!'))
        }
    }).catch(error => NotificationManager.error(error,"", 4000));
  }, []);


return (
<div>
   <NotificationContainer/>
</div>

)
}

export default withRouter(withTranslation()(Activate));