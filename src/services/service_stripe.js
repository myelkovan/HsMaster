import axios from 'axios';
import {of_getData, of_get, of_update} from './service_base'
import * as utils from '../pages/utils';


export function stripe_checkout(orders_id, payType, token) {
    const url = process.env.REACT_APP_SERVER_URI + "/PHP/stripe_checkout.php"
    return of_post(url, { orders_id: orders_id, payType : payType}, token)
}



export function stripe_pay(addAmount, token) {
    const url = process.env.REACT_APP_SERVER_URI + "/PHP/stripe_pay.php"
    return of_post(url, { amount: addAmount}, token)
}


export function isPaid(orders_id, token) {
    return of_getData("/PHP/paid_select.php?orders_id=" + orders_id, token)
}


export function paid_update(resettoken, token ) {
    const now = utils.getUtcTime(new Date())
    return of_get("/PHP/paid_update.php?token=" + resettoken + "&now=" + now, token)
}



function of_post(url, data, token){
    return axios.post(url, data, {headers: {Authorization: token}}).then((response) => {
  
        if (response) {
            return response;
        } else {
            alert('Client secret not found in response');
        }
    }).catch((error) => {
        alert('Error fetching client secret:', error);
    });
}