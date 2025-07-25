import axios from "axios";


export function of_update(url, data, token) {
    return new Promise((resolve, reject) => {

        axios.post(process.env.REACT_APP_SERVER_URI + url, data,  {headers: {Authorization: token}}).then((response) => {
            console.log(url + `--` + response)
              let ret = response.data === undefined? response :response.data
             
               if (Number.isInteger(ret)) {
                   resolve(parseInt(ret));
               } else {
                    if (response && typeof response === 'string' && (response.includes("invalidToken") || response.includes("Expired token"))) {
                          window.location.href = '/login';
                     }else if (response.includes("Duplicate")){
                        resolve(-3);
                    }else{
                        reject(ret)
                    }
               }

        }).catch(error => reject(error));
  })
}

export function of_getData(url, token, cache =false, refresh=false) {
    return new Promise((resolve, reject) => {

        if (cache === true){
            const sessionData = JSON.parse(sessionStorage.getItem(url));
            if (sessionData && sessionData.length > 0 && refresh ===false) {
                  resolve(sessionData);
                  return
            }
        }

        
        //axios.get(process.env.REACT_APP_SERVER_URI + url, {headers: {Authorization: token}}).then((response) => {
        //*****NETWORK ERROR HATASI İÇİN EKLENMELİ /
     
        axios.get(process.env.REACT_APP_SERVER_URI + url, {
                    headers: {
                        Authorization: token,
                        'Content-Type': 'multipart/form-data', // Bu satır eklendi
                        Accept: 'application/json' // Bu satır eklendi
                    }
                }).then((response) => {
                    console.log("PHP -----------------------------------------" + url + `--` + response)
            let ret = response.data === undefined? response :response.data

            if (ret && typeof ret === 'string' && (ret.includes("invalidToken") || ret.includes("Expired token"))) {
                    window.location.href = '/login';
            }else{ 
                if (cache === true){
                    sessionStorage.setItem(url, JSON.stringify(response));   
                } 
            resolve(ret);
            }
        }).catch(error => reject(error));
  })
}




export function of_get(url, token="") {
    return new Promise((resolve, reject) => {
               axios.get(process.env.REACT_APP_SERVER_URI + url, {headers: {Authorization: token}}).then((response) => {
               console.log(url + `--` + response) //))JSON.stringify(response))

                let ret = response.data === undefined? response :response.data
               if (Number.isInteger(ret)) {
                   resolve(parseInt(ret));
               } else {
                    if (response && typeof ret === 'string' && (response.includes("invalidToken") || response.includes("Expired token"))) {
                          window.location.href = '/login';
                   }
               }
    }).catch(error => reject(error));
  })
}




function getBase64(url) {
    global.Buffer = global.Buffer || require('buffer').Buffer
    return axios.get(url, { responseType: 'arraybuffer'})
         .then(response => Buffer.from(response.data, 'binary').toString('base64'))
         .catch(ex => alert("Uploaded pictures can not be read " + ex));
}


