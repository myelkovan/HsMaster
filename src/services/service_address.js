import {of_getData, of_get, of_update} from './service_base'
import axios from "axios";


//Adres bilgileri bir defa okunup sessiona yaziliyor. Ancak adres ekraninda adreste degisiklik olursa yeniden okunacak
export function getAddress(user_id, refresh, token) {
    return new Promise((resolve, reject) => {
        const sessionData = JSON.parse(sessionStorage.getItem("Address"));

        if (sessionData && sessionData.length > 0 && refresh ===false && sessionData[0].user_id === user_id) {
              resolve(sessionData);
        }else{        
              axios.get(process.env.REACT_APP_SERVER_URI + "/PHP/user_address_select.php?user_id="+user_id, {headers: {Authorization: token}}).then((response) => {    
                sessionStorage.setItem("Address", JSON.stringify(response));
                  resolve(response);
               }).catch(error => reject("Data could not be read from database - Network error"));
        }
    })
  }


//Kullanici adresini siler
export function deleteAddress(id, token) {
    return of_get("/PHP/user_address_delete.php?id=" + id, token)
}

//Yeni adres girer
export function updateAddress(insert_data,  token) {
    const data = { id: insert_data.id,
                   user_id: insert_data.user_id,
                   alias: insert_data.alias,
                   name : insert_data.name,
                   address : insert_data.address,
                   city : insert_data.city,
                   state : insert_data.state,
                   zipcode : insert_data.zipcode,
                   country_id : insert_data.country_id,
                   phone : insert_data.phone
                 }
    return of_update("/PHP/user_address_insert.php", data, token)
}


