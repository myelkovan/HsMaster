import {of_getData, of_get, of_update} from './service_base'
import moment from 'moment';
import * as utils from '../pages/utils';




export function getDeposit(user_id, token) {
    return of_getData("/PHP/deposit_select.php?user_id=" + user_id, token)
}

export function getDepositList(user_id, token) {
    return of_getData("/PHP/deposit_list_select.php?user_id=" + user_id, token)
}


export function depositAdd( user_id, orders_id, stripe_product_id, amount, description, shipment_refund, resettoken, token) {
    const data = {
        user_id: user_id,
        orders_id: orders_id,
        stripe_product_id:stripe_product_id,
        amount: amount,
        description: description,
        resettoken:resettoken,
        now:utils.getUtcTime(new Date()),
        shipment_refund:shipment_refund
    }
    return of_update("/PHP/deposit_insert.php", data, token)
}


export function depositAddAdmin( user_id, amount, description, token) {
    const data = {
        user_id: user_id,
        amount: amount,
        description: description,
        now:utils.getUtcTime(new Date()),
    }
    return of_update("/PHP/deposit_insert_admin.php", data, token)
}

export function getTokenCount(user_id, token) {
    return of_getData("/PHP/token_count_select.php?user_id=" + user_id, token)
}
