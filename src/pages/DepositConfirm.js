import { withTranslation } from "react-i18next";
import withRouter from '../Components/Common/withRouter';
import { useEffect } from 'react';
import { NotificationManager} from 'react-notifications';
import 'react-notifications/lib/notifications.css';
import { useNavigate,useParams } from 'react-router-dom';
import {depositAdd} from "../services/service_deposit";
import { useDeposit } from './DepositContext'; 



//Musteri stripe ile odeme yapti ve basarili oldu ise urettigi token ile burayi cagirir
//Bizde burada odendigine dair tarihi update ediyoruz
function DepositConfirm(props){
    const { user_id, stripe_product_id, resettoken } = useParams();
    const { refreshDeposit } = useDeposit();  


      useEffect(() => {
            const authuser = sessionStorage.getItem("authUser")
            const user = JSON.parse(authuser);

            //token sonunda -5 varsa 5 dolar bizim yatiracagimiz miktar
            const lastHyphenPos = resettoken.lastIndexOf('-');
            if (lastHyphenPos !== -1) {
                //token ve yatacak parayi al
                const token = resettoken.substring(0, lastHyphenPos);
                const amount = resettoken.substring(lastHyphenPos + 1);


                if (user_id === user.id) {
                    depositAdd(user_id, null, stripe_product_id, amount, null, 0,  token, user.token, ).then((response) => {
                        NotificationManager.success(props.t("${{amount}} added to your account", {'amount':amount}),"", 5000);
                        refreshDeposit()
                    }).catch(error => NotificationManager.error(error, "", 4000));
                }
            }
       }, []);

}


export default withRouter(withTranslation()(DepositConfirm));


