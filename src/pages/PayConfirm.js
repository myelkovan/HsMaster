import { withTranslation } from "react-i18next";
import withRouter from '../Components/Common/withRouter';
import React, { useEffect } from 'react';
import { NotificationContainer, NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import { useNavigate, useParams } from 'react-router-dom';
import { paid_update } from '../services/service_stripe';
import { useDeposit } from './DepositContext'; 



//Musteri stripe ile odeme yapti ve basarili oldu ise urettigi token ile burayi cagirir
//Bizde burada odendigine dair tarihi update ediyoruz
function PayConfirm(props){
    const { resettoken } = useParams();
    const navigate = useNavigate();
    const { refreshDeposit } = useDeposit();  

      useEffect(() => {
            const authuser = sessionStorage.getItem("authUser")
            const user = JSON.parse(authuser);

            paid_update(resettoken, user.token).then((response) => {
                navigate('/dashboard1/?reload=true', {state: {fromOrder: "YES"}})
                refreshDeposit()
            }).catch(error => NotificationManager.error(error,"", 4000));
       }, []);



  return (
        <React.Fragment>
        <NotificationContainer/>
        </React.Fragment>
    );
}


export default withRouter(withTranslation()(PayConfirm));


