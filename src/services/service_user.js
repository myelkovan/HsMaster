import {of_getData, of_get, of_update} from './service_base'

//import { APIClient } from "../helpers/api_helper";
//const api = new APIClient();
//Kullanicinin eposta ve sifresi ile login olmasini saglar
//const login = (email, password) => api.get(process.env.REACT_APP_SERVER_URI + "/PHP/login/login.php?email="+email + "&password="+password);


//Kullanicinin sifresini degistiriri
export function activateUser(email, activate_token) {
     return of_get("/PHP/login/activate.php?email=" + email + "&activate_token=" + activate_token, '')
}

//Kullanicinin sifresini degistirmek icin link gonder
export function sendPasswordLink(email) {
     return of_get("/PHP/login/send_password_link.php?email=" + email, '')
}

//Kullanicinin sifresini degistirir
export function updatePassword(email, password, reset_token, token) {
     return of_get("/PHP/login/update_password.php?email=" + email + "&password=" + password + "&reset_token=" + reset_token, token)
}

//Kullanici bilgilerini donderir
export function getUser(id, token) {
     return of_getData("/PHP/user_select.php?id=" + id, token)
}

//Tum Kullanici bilgilerini donderir
export function getTeams(token) {
    return of_getData("/PHP/teams_select.php", token)
}


//Tum Kullanici bilgilerini donderir
export function getUsers(key,token) {
     return of_getData("/PHP/users_select.php?key=" + key, token)
 }

export function updateUser(id, insert_data,  token) {
    const data = {  id: id,
                   name: insert_data.name,
                   email: insert_data.email,
                   mobile : insert_data.mobile,
                   birth_date : insert_data.birth_date,
                   permission_level : insert_data.permission_level
                 }
    return of_update("/PHP/user_insert.php", data, token)
}



//Kullanici siler
export function genarateApiToken(token) {
     return of_getData("/PHP/user_genarate_api_token.php", token)
}



//Kullanici siler
export function deleteUser(id, token) {
     return of_get("/PHP/user_delete.php?id=" + id, token)
}


//Kullanicinin arar
export function searchUser(name, warehouse_id, token) {
     return of_getData("/PHP/user_search.php?name=" + name + "&warehouse_id="+warehouse_id, token)
}


//Kullanici billing adres bilgilerini donderir
export function getBillingAddress(id, token) {
    return of_getData("/PHP/user_billing_address_select.php?id="+id, token)
}

export function updateBillingAddress(insert_data,  token) {
    const data = {
        id: insert_data.id,
        billing_name: insert_data.billing_name,
        billing_address : insert_data.billing_address,
        billing_phone : insert_data.billing_phone,
        billing_tax_id : insert_data.billing_tax_id
    }
    return of_update("/PHP/user_billing_address_insert.php", data, token)
}


//Kullanicinin anlik resettoken datasini donderir. Bu data duruma gore degisen bir tokendir
export function getResetToken(token) {
     return of_getData("/PHP/login/reset_token_select.php", token)
}

export function sendMail(subject, body, orderId, user) {
     const data = {
          subject: subject,
          body : body,
          order_id : orderId,
          user_id : user.id,
          email:user.email,
          name:user.name
       }
       return of_update("/PHP/mail_send.php", data, user.token)
}

export function sendMailToSpesicificUser(subject, body, orderId, user, userId, userMail, userName) {
     const data = {
          subject: subject,
          body : body,
          order_id : orderId,
          user_id : userId,
          email:userMail,
          name:userName,
          directToUser:true
       }
       //alert(JSON.stringify(data ))
       return of_update("/PHP/mail_send.php", data, user.token)
}

