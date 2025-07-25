import axios from "axios";
import {of_get, of_update} from './service_base'
import {NotificationManager} from "react-notifications";





export function fileUpload(id, table, column, file,token) {
  return new Promise((resolve, reject) => {

    if(checkFileSize(file) == -1){
      resolve(-1);
    }

    const data = new FormData()
    data.append('id', id)
    data.append('table',table)
    data.append('column',column)
    data.append('file', file)


    axios.post(process.env.REACT_APP_SERVER_URI + "/PHP/file_upload.php", data, {headers: {Authorization: token, "Content-Type": "multipart/form-data"}}).then((response) => {
        let x = response.data === undefined? response :response.data
        resolve(parseInt(x));
    }).catch(error => NotificationManager.error(error,"", 4000));
    })
}


export function uploadReturnLabel(id, file, token) {
    return new Promise((resolve, reject) => {
      if(checkFileSize(file) == -1){
        resolve(-1);
      }
      
      const data = new FormData()
      data.append('product_id', id)
      data.append('file', file)
      axios.post(process.env.REACT_APP_SERVER_URI + "/PHP/file_upload_return_label.php", data, {headers: {Authorization: token, "Content-Type": "multipart/form-data"}}).then((response) => {

        let ret = response.data === undefined? response :response.data
        if (Number.isInteger(ret)) {
            resolve(parseInt(ret));
        } else {
            resolve(response.trim()); //new file name
        }
        }).catch(error => NotificationManager.error(error,"", 4000));
      })
  }

  export function deleteReturnLabel(id, filename, token) {
    return of_get("/PHP/file_delete_return_label.php?product_id=" + id + "&filename="+filename, token) 
  }





//Bir demande ait resimleri donderir
export function getProofPicturesBlob(orders_id, screenShot, token) {
    return new Promise((resolve, reject) => {

          axios.get(process.env.REACT_APP_SERVER_URI + "/PHP/orders_proof_picture_select.php?orders_id=" + orders_id, {headers: {Authorization: token}} ).then((response) => {
                const picturePaths = response;
                var found = false

              //adminden geliyorsa resim kaydet
              if (screenShot!==''){
                    if (response.length > 0){
                        for (var i = 0; i < response.length; i++) {
                            if (response[i].picture_path === orders_id + "_screenshot.png"){
                                found = true
                            }
                        }
                    }

                    if (found === false){
                         new Promise((resolve, reject) => {
                            const data = new FormData()
                            data.append('id', orders_id)
                            data.append('image', screenShot);

                            axios.post(process.env.REACT_APP_SERVER_URI + "/PHP/screenshot_upload.php", data, {headers: {Authorization: token, "Content-Type": "multipart/form-data"}}).then((response) => {
                                let x = response.data === undefined? response :response.data
                                resolve(parseInt(x));
                            }).catch(error => NotificationManager.error(error,"", 4000));
                         })
                    }
              }


                const files = picturePaths.map(picture => ({
                    source: process.env.REACT_APP_API_URL + "/images/" + picture.picture_path,
                    options: { type: "local" }
                }))

                if (found === false && screenShot!==''){
                     files.push({source: process.env.REACT_APP_API_URL + "/images/" + orders_id + "_screenshot.png", options: { type: "local" }})
                }

                 resolve(files);

          }).catch(error => reject("Data could not be read from database - Network error"));
    })
}


function checkFileSize(file){
        const maxFileSize = 1 * 1024 * 1024; // 1MB in bytes
      
        if (file) {
          if (file.size > maxFileSize) {
            NotificationManager.error("File size exceeds the limit of 1MB. Please choose a smaller file.","", 4000);
            return -1
          }
          return 1
  
        }
      }

