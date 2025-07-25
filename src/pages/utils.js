import PropTypes from "prop-types";
import React from "react";
import { Modal, ModalBody } from "reactstrap";
import html2pdf from "html2pdf.js";
import moment from 'moment-timezone';
import { MobiledataOffSharp } from "@mui/icons-material";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

//Bir orderin sonradan hesaplanan shipment cost ve custom fee fiyatini toplar
export function of_getCalculatedShipmentCost(ordersData) {
    const price = parseFloat(ordersData.calculated_shipment_cost || "0") +
                  parseFloat(ordersData.calculated_custom_fee  || "0")
    return parseFloat(price.toFixed(2))     
}


//Bir orderin musterinin girdigi shipment cost ve custom fee fiyatini toplar
export function of_getShipmentCost(ordersData) {
    const price = parseFloat(ordersData.shipment_cost || "0") +
                  parseFloat(ordersData.custom_fee  || "0")
    return parseFloat(price.toFixed(2))   
}



//Bir orderen urunlerin fiyatina bizim shipment costu ekler. kendi aldiysa sadece bizim costu donderir. Yani odeyecegi para
export function of_getProductsPriceWithCost(ordersData) {
    const price =  (!isNaN(parseFloat(ordersData?.service_fee)) ? parseFloat(ordersData.service_fee) : 0) +
                    (!isNaN(parseFloat(ordersData?.custom_fee)) ? parseFloat(ordersData.custom_fee) : 0) +
                  (ordersData.you_buy === "2" ? parseFloat(ordersData.product_price_total) :0) +
                  parseFloat(ordersData.shipment_cost || "0")             
    return parseFloat(price.toFixed(2))   
    
}



//Bir orderin kullanici fiyati custem service fee vs. toplam fiyatini bulur
//Burada fark iptal olan fiyati dusurur, artan urunlerin fiyati eklendi
export function of_getUserTotal(ordersData) {
    const price = parseFloat(ordersData.shipment_cost || "0") +
                  parseFloat(ordersData.service_fee || "0") +
                  parseFloat(ordersData.custom_fee  || "0") +
                  (ordersData.you_buy === "2" ? parseFloat(ordersData.product_total_price || "0") :0)
    return parseFloat(price.toFixed(2))   
}

//Bir orderen urunlerin fiyatina bizim shipment costu ekler. kendi aldiysa sadece bizim costu donderir. Yani odeyecegi para
export function of_getCanceledTotalWithCost(ordersData) {
    const price = parseFloat(ordersData.service_fee || "0") +
                  parseFloat(ordersData.custom_fee  || "0") + 
                  parseFloat(ordersData.shipment_cost || "0")  +
                  (ordersData.you_buy === "2" ? parseFloat(ordersData.canceled_total): 0)
    return parseFloat(price.toFixed(2))               
}


//Bir orderin sonradan hesaplanan toplan urun fiyati custem service fee vs. toplam fiyatini bulur
//Burada fark iptal olan productlarin fiyati dusurur, artan urunlerin fiyati eklendi
export function of_getCalculatedTotal(ordersData) {
    const price = parseFloat(ordersData.calculated_shipment_cost || "0") +
                  parseFloat(ordersData.calculated_service_fee || "0") +
                  parseFloat(ordersData.return_cost  || "0") +
                  parseFloat(ordersData.calculated_custom_fee  || "0") +
                 (ordersData.you_buy === "2" ? parseFloat(ordersData.calculated_product_total_price || "0") :0)
    return parseFloat(price.toFixed(2))           
}



//Bir orderen urunlerin fiyatina bizim shipment costu ekler. kendi aldiysa sadece bizim costu donderir. Yani odeyecegi para
export function of_getReturnedTotalWithCost(ordersData) {

    const price = parseFloat(ordersData.service_fee || "0") +
                  parseFloat(ordersData.custom_fee  || "0") + 
                  parseFloat(ordersData.shipment_cost || "0")  -
                  parseFloat(ordersData.return_cost || "0") + 
                  (ordersData.you_buy === "2" ? parseFloat(ordersData.returned_total):0)

                  //TODO bunu kaldirdik
                  //of_getProductDifference(ordersData) +

 return parseFloat(price.toFixed(2))          
}


//Bir orderen urunlerin fiyatina bizim shipment costu ekler. kendi aldiysa sadece bizim costu donderir. Yani odeyecegi para
export function of_getProductDifference(ordersData) {
  
    const returnedProductPriceTotal = ordersData.you_buy !== "2" ?0 :parseFloat(ordersData.returned_total || "0") 
     //Iade edilen urun varsa product difference farkli hesaplaniyor
    let price = 0
    if (returnedProductPriceTotal > 0){
        //urun fiyati degismis. adam kendi aldiysa iptal edince veritabaninda fiyat dusuyor ama ekrana yansitmayacagiz. Adamin kendi aldigi urunde fiyat degismesi bir odeme emri cikarmaz
        price =  parseFloat(ordersData.product_buy_price_total) - parseFloat(ordersData.product_price_total)
    }else{
        if (ordersData.you_buy === "2"){
            price = parseFloat(ordersData.calculated_product_total_price) - parseFloat(ordersData.product_total_price)
        }else{
            price = 0
        }
    }
    return parseFloat(price.toFixed(2))          
}




//Price ekraninda toplam odeneni bulur. paid=true verilir ise gercekten odenmis olan rakami donderir
export function of_getTotalPaid(ordersData, odenmisleriGoster) {
    // musterinin ilk hesapladigi para
    const total = of_getProductsPriceWithCost(ordersData) 
    let totalPaid = total  

     //odenMEmisse musterinin hesabini goster
    if (ordersData.paid_date === null){
        return totalPaid.toFixed(2)   
    }else{
        //iptalse 0 goster
        if (ordersData.cancel_date !== null){     
            return "0.00"
        //Odenmisse
        }else{    

            let shipmentDifference = (of_getCalculatedShipmentCost(ordersData) - of_getShipmentCost(ordersData)) //yol parasi farki
            const productDifference = of_getProductDifference(ordersData)            
            const canceledProductPriceTotal = ordersData.you_buy !== "2" ?0 :parseFloat(ordersData.canceled_total || "0") 
            const returnedProductPriceTotal = ordersData.you_buy !== "2" ?0 :parseFloat(ordersData.returned_total || "0") 
            const returnCost = parseFloat(ordersData.return_cost || "0")
            //const canceledTotalWithCost = of_getCanceledTotalWithCost(ordersData)
            //const returnedTotalWithCost = of_getReturnedTotalWithCost(ordersData)
            
    
             //shipment difference eksi veya arti olabilir onu da ekle    
            totalPaid +=  shipmentDifference 

            //biz aldiysak urun fiyati artmissa artani ekle 
            if (ordersData.you_buy === "2" && productDifference>0 ) {
                totalPaid += productDifference
            }
            
            totalPaid -=  canceledProductPriceTotal 
        
            //consolide var ve bu kayit digerine consolide olmus ise bunun shipment costu iade edilmisti bu yuzden bu kayittan dus
            if (ordersData.consolidated_to !==null && ordersData.consolidated_to !== ordersData.id){
                totalPaid -= parseFloat(ordersData.shipment_cost || "0") 
            }      
   
       
            if(ordersData.allReturnedOrCanceled){
                totalPaid = returnCost
            }else{
                totalPaid += returnCost
                totalPaid -= returnedProductPriceTotal
            } 
      

            if(odenmisleriGoster){
                //product difference odenmemisse cikar
                if (ordersData.paid_date_additional ===null){
                    totalPaid -=  productDifference
                }
       
                // consolide islemi varsa fark odenmis gibi degerlendiriyoruz. consolide ettiysek shipment
                // artmistir ama consolideden dolayi musteri lehine olusan fark bunu odeyecek aksi takdirde consolide anlamsiz 
                if (ordersData.paid_date_final ===null && ordersData.consolidated_to === null){
                    const finalPayment = (shipmentDifference + returnCost - returnedProductPriceTotal)
        
                    if (finalPayment > 0 ){
                        totalPaid -= finalPayment
                    }
            
                }
            }
        }
    }
                
    return totalPaid.toFixed(2)   
}



//Bir urunun vergisi, shipment vs toplam fiyati donderir, product_buy_price verince bizim fiyati verirsek bizim fiyati donderir
export function of_getProductPrice(price, quantity, shipment_cost, you_buy) {
    if (typeof price === 'string') {
        price = parseFloat(price.replace(/,/g, '')) || 0;
    }
   if (typeof shipment_cost === 'string') {
        shipment_cost = parseFloat(shipment_cost.replace(/,/g, '')) || 0;
    }

    
    quantity = parseFloat(quantity) || 1;
    const basePrice = price * quantity;
    const totalCost = basePrice + (you_buy !== "2" ? 0 : shipment_cost);
    const tax = parseFloat((totalCost * 0.06625).toFixed(2)) || 0;
    const finalPrice = (totalCost + tax).toFixed(2);

    return parseFloat(finalPrice);
}




//Bir urunun tax ini hesaplar
export function of_calcProductTax(price, quantity, shipment_cost, you_buy) {
   if (typeof price === 'string') {
        price = parseFloat(price.replace(/,/g, '')) || 0;
    }else{
        price = 0
    }

   if (typeof shipment_cost === 'string') {
        shipment_cost = parseFloat(shipment_cost.replace(/,/g, '')) || 0;
    }else{
        shipment_cost = 0
    }

    quantity = parseFloat(quantity) || 0;
    const basePrice = price * quantity;
    const totalCost = basePrice + (you_buy !== "2" ? 0 : shipment_cost);
    const tax = parseFloat((totalCost * 0.06625).toFixed(2)) || 0;

    return tax;
}





   //Bir ordera ait productlarin toplam fiyatini hesaplar
   export  function of_getTotalProductPrice(products, you_buy){
        const totalPrice = products.reduce((acc, row) => {
            //if (row.cancel_date === null) { 
                const totalPrice =  of_getProductPrice(row.product_price,
                                                        row.product_quantity,
                                                        row.product_shipment_cost,
                                                        you_buy)
                return acc + totalPrice;
            //}else{
            //    return 0
            //}
        }, 0);
        return totalPrice.toFixed(2)
    }

 //Bir ordera ait productlarin toplam fiyatini hesaplar
 export  function of_getCalcTotalProductPrice(products, you_buy){
    const totalPrice = products.reduce((acc, row) => {
        //if (row.cancel_date === null) { 
            const totalPrice =  of_getProductPrice(row.product_buy_price,
                                                    row.product_quantity,
                                                    row.product_shipment_cost,
                                                    you_buy)
                                                   
            return acc + totalPrice;
        //}else{
        //    return 0
        //}
    }, 0);
    return totalPrice.toFixed(2)
}



     


export function of_getDiscountedPrice(price){
    var newPrice = (price / 4)
    if (newPrice < 10){
        newPrice = 10
    }
    return newPrice
}




export function of_calcTotalProductDimensions(products) {
    let totalLength = 0;
    let maxWidth = 0;
    let maxHeight = 0;

    products.forEach((product) => {
        let productLength = parseFloat(product.product_length);
        let productWidth = parseFloat(product.product_width);
        let productHeight = parseFloat(product.product_height);

        // If the unit is cm, convert to inches
        if (product.product_length_units === "cm") {
            productLength /= 2.54;
            productWidth /= 2.54;
            productHeight /= 2.54;
        }


        // Calculate total length for linear arrangement
        totalLength += productLength * product.product_quantity;

        // Determine max width and height
        maxWidth = Math.max(maxWidth, productWidth);
        maxHeight = Math.max(maxHeight, productHeight);

        //alert(totalLength + "---- " + maxWidth  + "---- " + maxHeight)
    });

    if (isNaN(totalLength)){
        totalLength= 1
    }
    if (isNaN(maxWidth)){
        maxWidth = 1
    }
    if (isNaN(maxHeight)){
        maxHeight = 1
    }

    // Return dimensions of the box
    return { length: totalLength, width: maxWidth, height: maxHeight };
}




 //Bir ordera ait productlarin toplam agirligini hesaplar
    export function of_calcTotalProductWeight(products){
        const totalWeight = products.reduce((acc, row) => {

            let product_weight= parseFloat(row.product_weight);

            if (row.product_weight_units === "kg") {
                product_weight = product_weight * 2.20462; // 1 kg = 2.20462 lb
            } else if (row.product_weight_units === "gr") {
                product_weight = product_weight * 0.00220462; // 1 g = 0.00220462 lb
            } else if (row.product_weight_units === "oz") {
                product_weight = product_weight * 0.0625; // 1 oz = 0.0625 lb
            }

            const total_weight = (product_weight * row.product_quantity);
          return acc + total_weight;
        }, 0);
        return totalWeight
    }




   //shipment sureleri
   export  function of_getShipmentDays(service_code){
        if (service_code === "A180"){   //"asendia_epaq_sel_directaccess_ca_ddp"){
            return "5-8"
        }

        if (service_code === "A124"){ //"asendia_epaq_select_ddp"){
            return "5-12"
        }

        if (service_code === "A1105"){  //"asendia_epaq_elite_dpd"){
            return "5-7"
        }

        if (service_code === "FEDEX_INTERNATIONAL_CONNECT_PLUS"){
             return "3-5"
        }

        if (service_code ===  "08"){ //"ups_worldwide_expedited"){
            return "3-5"
        }

        if (service_code === "ups_standard_international"){
            return "3-5"
        }


         //isimiz olmayanlar domestic
         //"FedEx Ground Economy"){
        //"fedex_ground"){
        //"FEDEX_2_DAY"){
        //"FEDEX_2_DAY"){
        //    if (service_code ==="72" ){ // "UPS Worldwide Economy DDP"  eskisi://"ups_worldwide_saver"){
        //        return "5-8"
        //  }
        //if (service_code ==="03" ){ //  UPS Ground
        //    return "5-8"
       //}
       // if (service_code ==="92" ){ //  UPS Surepost Less Than 1L
        //   return "5-8"
      // }
       // if (service_code ==="93" ){ //  UPS Surepost Greater Than 1LB
       //     return "5-8"
       // }   
    

    

        return "5-8"
    }



    //urun descriptionunda ASIN var mi?
    export function of_containsASIN(product_description) {
      // Regular expression to match a 10-character alphanumeric string
      const asinRegex = /\b[A-Z0-9]{10}\b/;

      // Test the description against the regular expression
      return asinRegex.test(product_description);
    }


   export function of_trackingClicked(tracking_number){

        const getTrackingLink = () => {
            let link = "";


            // FEDEX
            if (tracking_number.length === 12) {
                link = `https://www.fedex.com/fedextrack/?trknbr=${tracking_number}`;
            }
            // UPS
            else if (tracking_number.length === 18) {
                link = `https://www.ups.com/track?loc=en_US&requester=QUIC&tracknum=${tracking_number}/trackdetails`;
            }
            // USPS
            else if (tracking_number.length >= 22) {
                link = `https://tools.usps.com/go/TrackConfirmAction?tRef=fullpage&tLc=2&text28777=&tLabels=${tracking_number}`;
            }
            // ASENDIA
            else if (tracking_number.length == 13 || tracking_number.length == 10 || tracking_number.length == 20) {
                link = `https://a1.asendiausa.com/tracking/?trackingnumber=${tracking_number}`;
            }
            return link;
        };


        // Get the tracking link
        const trackingLink = getTrackingLink();
        if (trackingLink !==""){
             window.open(trackingLink , '_blank');
        }
   }




//BASE FUNCTIONS **************************************************************

  export function of_base64ToBlob (base64, contentType){
      const byteCharacters = atob(base64);
      const byteArrays = [];

      for (let offset = 0; offset < byteCharacters.length; offset += 512) {
        const slice = byteCharacters.slice(offset, offset + 512);

        const byteNumbers = new Array(slice.length);
        for (let i = 0; i < slice.length; i++) {
          byteNumbers[i] = slice.charCodeAt(i);
        }

        const byteArray = new Uint8Array(byteNumbers);
        byteArrays.push(byteArray);
      }

      const blob = new Blob(byteArrays, { type: contentType });
      return blob;
  }



  export function of_handlePrint(divID){
        const printContent = document.getElementById(divID);
        const printWindow = window.open('', '_blank');

        printWindow.document.write(`
          <html>
            <head>
              <style>
                body { margin: 0; }
                iframe { width: 100%; height: 100%; border: none; }
              </style>
            </head>
            <body>
              ${printContent.innerHTML}
            </body>
          </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        const checkContentLoaded = () => {
           printWindow.print();
           printWindow.close();
        }

          printWindow.onload = () => {
            setTimeout(checkContentLoaded, 100);
          };
     }



      export  function of_generate_unique_code(){
             const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
             let result = "";

             for (var i=0; i < 3; i++) {
                 result += characters.charAt(Math.floor(Math.random() * 62));
             }
             result += "-"
             for (i=0; i < 3; i++) {
                  result += characters.charAt(Math.floor(Math.random() * 62));
              }
              result += "-"
             for (i=0; i < 3; i++) {
                 result += characters.charAt(Math.floor(Math.random() * 62));
             }
         }


     export function MessageBox ({ show, onConfirmClick, onCloseClick, CloseLabel = "Close", ConfirmLabel, message }) {
       return (
         <Modal fade={true} isOpen={show} toggle={onCloseClick} centered={true}>
           <ModalBody className="py-3 px-5">
             <div className="mt-2 text-center">
               <lord-icon
                 src="https://cdn.lordicon.com/gsqxdxog.json"
                 trigger="loop"
                 colors="primary:#f7b84b,secondary:#f06548"
                 style={{ width: "100px", height: "100px" }}
               ></lord-icon>
               <div className="mt-4 pt-2 fs-16 mx-4 mx-sm-5">
                 <p className="text mx-4 mb-0">
                   {message}
                 </p>
               </div>
             </div>
             <div className="d-flex gap-2 justify-content-center mt-4 mb-2">
               <button
                 type="button"
                 className="btn w-sm btn-success "
                 id="delete-record"
                 onClick={onConfirmClick}>
                 {ConfirmLabel}
               </button>

                 <button
                   type="button"
                   className="btn w-sm btn-danger"
                   data-bs-dismiss="modal"
                   onClick={onCloseClick} >
                   {CloseLabel}
                 </button>

             </div>
           </ModalBody>
         </Modal>
       );
     }

     MessageBox.propTypes = {
       onCloseClick: PropTypes.func,
       onConfirmClick: PropTypes.func,
       ConfirmLabel: PropTypes.any,
       CloseLabel: PropTypes.any,
       show: PropTypes.any,
     }



    //Linki browserda aciyor
    export function redirectToUrl(url) {
        // Ensure the URL starts with http:// or https://
        if (!/^https?:\/\//i.test(url)) {
            url = 'https://' + url; // Default to https if no protocol is provided
        }

        if (!url.includes('jetbasket')) {

            window.open(url, '_blank');
        }
    }



    export function saveAsPdf(divKey, fileName) {
        // Get the element you want to convert to PDF
        const element = document.querySelector(divKey);

        // Use html2pdf.js to generate and download the PDF
        const options = {
            filename: fileName,  // Specify the PDF filename
            margin: [10, 10, 10, 10],  // Set margins (optional)
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },  // PDF settings (A4 format, portrait)
            html2canvas: { scale: 2 },  // Scale setting for HTML to canvas conversion (optional)
        };

        //pdf olarak kaydet
        html2pdf().from(element).set(options).save(); 
    }



    // linke tiklayinca https ile baslamiyorsa veya jetbasket iceriyorsa gitme
    // birisi veritabanina jetbasket/PHP/xxx.php gibi birsey yazip calistirmamizi saglayabilir guvenlik acigi olusur
    export function linkCheck(e, link){
        if (link.includes('jetbasket') || !link.startsWith('https')) {
            e.preventDefault(); // Prevent the default navigation
            return -1
        }
        return 1
    }

 export function getLocalTime(utcDateSting) {
    let orderDate = new Date(utcDateSting);
    // UTC zamanını yerel zamana dönüştür
    const localTime = moment.utc(orderDate).tz(moment.tz.guess());
    //return localTime.format('YYYY-MM-DD HH:mm:ss');
    return new Date(localTime['_d']);
}


export function getLocalTime1(utcDateString) {
    const orderDate = new Date(utcDateString);
    const localTime = moment.utc(orderDate).tz(moment.tz.guess());
    // Format the local time to a string, e.g., 'YYYY-MM-DD HH:mm:ss'
    return localTime.format('YYYY-MM-DD HH:mm:ss');
}


export function getUtcTime(localTime) {
    // Yerel zaman ve saat dilimini UTC zamanına dönüştür
    const utcTime = moment(localTime).tz(moment.tz.guess()); // Türkiye saat dilimi
    return utcTime.utc().format();
        
}

// Utility function to save data as an Excel (.xls) file
export const saveAsXls = (data, name = "data_table.xls" ) => {
    // Create a new worksheet from the data
    const ws = XLSX.utils.json_to_sheet(data);

    // Create a new workbook with the worksheet
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Data");

    // Export the workbook as an .xls file
    const excelBuffer = XLSX.write(wb, { bookType: "xls", type: "array" });
    const dataBlob = new Blob([excelBuffer], { type: "application/vnd.ms-excel" });

    // Save the Excel file with a specified filename
    saveAs(dataBlob, name);
};



//**  datatable dark mode için kullanılan tema
// export const createSolarizedTheme = () => {
//   return ('solarized', {
//     text: {
//       primary: props => props.theme.mode === 'dark' ? '#FFFFFF' : '#000000',
//       secondary: props => props.theme.mode === 'dark' ? '#FFFFFF' : '#000000',
//     },
//     background: {
//       default: props => props.theme.mode === 'dark' ? '#333333' : '#FFFFFF',
//     },
//     context: {
//       background: '#cb4b16',
//       text: '#FFFFFF',
//     },
//     divider: {
//       default: 'lightgray', //'#073642',
//     },
//     action: {
//       button: 'rgba(0,0,0,.54)',
//       hover: 'rgba(0,0,0,.08)',
//       disabled: 'rgba(0,0,0,.12)',
//     },
//   }, 'dark');
// };

 //** datatable dark mode için kullanılan tema SONU

export function of_getLocaleDateString() {
       const formats = {
         "af-ZA": "yyyy/MM/dd",
         "am-ET": "d/M/yyyy",
         "ar-AE": "dd/MM/yyyy",
         "ar-BH": "dd/MM/yyyy",
         "ar-DZ": "dd-MM-yyyy",
         "ar-EG": "dd/MM/yyyy",
         "ar-IQ": "dd/MM/yyyy",
         "ar-JO": "dd/MM/yyyy",
         "ar-KW": "dd/MM/yyyy",
         "ar-LB": "dd/MM/yyyy",
         "ar-LY": "dd/MM/yyyy",
         "ar-MA": "dd-MM-yyyy",
         "ar-OM": "dd/MM/yyyy",
         "ar-QA": "dd/MM/yyyy",
         "ar-SA": "dd/MM/yy",
         "ar-SY": "dd/MM/yyyy",
         "ar-TN": "dd-MM-yyyy",
         "ar-YE": "dd/MM/yyyy",
         "arn-CL": "dd-MM-yyyy",
         "as-IN": "dd-MM-yyyy",
         "az-Cyrl-AZ": "dd.MM.yyyy",
         "az-Latn-AZ": "dd.MM.yyyy",
         "ba-RU": "dd.MM.yy",
         "be-BY": "dd.MM.yyyy",
         "bg-BG": "dd.M.yyyy",
         "bn-BD": "dd-MM-yy",
         "bn-IN": "dd-MM-yy",
         "bo-CN": "yyyy/M/d",
         "br-FR": "dd/MM/yyyy",
         "bs-Cyrl-BA": "d.M.yyyy",
         "bs-Latn-BA": "d.M.yyyy",
         "ca-ES": "dd/MM/yyyy",
         "co-FR": "dd/MM/yyyy",
         "cs-CZ": "d.M.yyyy",
         "cy-GB": "dd/MM/yyyy",
         "da-DK": "dd-MM-yyyy",
         "de-AT": "dd.MM.yyyy",
         "de-CH": "dd.MM.yyyy",
         "de-DE": "dd.MM.yyyy",
         "de-LI": "dd.MM.yyyy",
         "de-LU": "dd.MM.yyyy",
         "dsb-DE": "d. M. yyyy",
         "dv-MV": "dd/MM/yy",
         "el-GR": "d/M/yyyy",
         "en-029": "MM/dd/yyyy",
         "en-AU": "d/MM/yyyy",
         "en-BZ": "dd/MM/yyyy",
         "en-CA": "dd/MM/yyyy",
         "en-GB": "dd/MM/yyyy",
         "en-IE": "dd/MM/yyyy",
         "en-IN": "dd-MM-yyyy",
         "en-JM": "dd/MM/yyyy",
         "en-MY": "d/M/yyyy",
         "en-NZ": "d/MM/yyyy",
         "en-PH": "M/d/yyyy",
         "en-SG": "d/M/yyyy",
         "en-TT": "dd/MM/yyyy",
         "en-US": "M/d/yyyy",
         "en-ZA": "yyyy/MM/dd",
         "en-ZW": "M/d/yyyy",
         "es-AR": "dd/MM/yyyy",
         "es-BO": "dd/MM/yyyy",
         "es-CL": "dd-MM-yyyy",
         "es-CO": "dd/MM/yyyy",
         "es-CR": "dd/MM/yyyy",
         "es-DO": "dd/MM/yyyy",
         "es-EC": "dd/MM/yyyy",
         "es-ES": "dd/MM/yyyy",
         "es-GT": "dd/MM/yyyy",
         "es-HN": "dd/MM/yyyy",
         "es-MX": "dd/MM/yyyy",
         "es-NI": "dd/MM/yyyy",
         "es-PA": "MM/dd/yyyy",
         "es-PE": "dd/MM/yyyy",
         "es-PR": "dd/MM/yyyy",
         "es-PY": "dd/MM/yyyy",
         "es-SV": "dd/MM/yyyy",
         "es-US": "M/d/yyyy",
         "es-UY": "dd/MM/yyyy",
         "es-VE": "dd/MM/yyyy",
         "et-EE": "d.MM.yyyy",
         "eu-ES": "yyyy/MM/dd",
         "fa-IR": "MM/dd/yyyy",
         "fi-FI": "d.M.yyyy",
         "fil-PH": "M/d/yyyy",
         "fo-FO": "dd-MM-yyyy",
         "fr-BE": "d/MM/yyyy",
         "fr-CA": "yyyy-MM-dd",
         "fr-CH": "dd.MM.yyyy",
         "fr-FR": "dd/MM/yyyy",
         "fr-LU": "dd/MM/yyyy",
         "fr-MC": "dd/MM/yyyy",
         "fy-NL": "d-M-yyyy",
         "ga-IE": "dd/MM/yyyy",
         "gd-GB": "dd/MM/yyyy",
         "gl-ES": "dd/MM/yy",
         "gsw-FR": "dd/MM/yyyy",
         "gu-IN": "dd-MM-yy",
         "ha-Latn-NG": "d/M/yyyy",
         "he-IL": "dd/MM/yyyy",
         "hi-IN": "dd-MM-yyyy",
         "hr-BA": "d.M.yyyy.",
         "hr-HR": "d.M.yyyy",
         "hsb-DE": "d. M. yyyy",
         "hu-HU": "yyyy. MM. dd.",
         "hy-AM": "dd.MM.yyyy",
         "id-ID": "dd/MM/yyyy",
         "ig-NG": "d/M/yyyy",
         "ii-CN": "yyyy/M/d",
         "is-IS": "d.M.yyyy",
         "it-CH": "dd.MM.yyyy",
         "it-IT": "dd/MM/yyyy",
         "iu-Cans-CA": "d/M/yyyy",
         "iu-Latn-CA": "d/MM/yyyy",
         "ja-JP": "yyyy/MM/dd",
         "ka-GE": "dd.MM.yyyy",
         "kk-KZ": "dd.MM.yyyy",
         "kl-GL": "dd-MM-yyyy",
         "km-KH": "yyyy-MM-dd",
         "kn-IN": "dd-MM-yy",
         "ko-KR": "yyyy. MM. dd",
         "kok-IN": "dd-MM-yyyy",
         "ky-KG": "dd.MM.yy",
         "lb-LU": "dd/MM/yyyy",
         "lo-LA": "dd/MM/yyyy",
         "lt-LT": "yyyy.MM.dd",
         "lv-LV": "yyyy.MM.dd.",
         "mi-NZ": "dd/MM/yyyy",
         "mk-MK": "dd.MM.yyyy",
         "ml-IN": "dd-MM-yy",
         "mn-MN": "yy.MM.dd",
         "mn-Mong-CN": "yyyy/M/d",
         "moh-CA": "M/d/yyyy",
         "mr-IN": "dd-MM-yyyy",
         "ms-BN": "dd/MM/yyyy",
         "ms-MY": "dd/MM/yyyy",
         "mt-MT": "dd/MM/yyyy",
         "nb-NO": "dd.MM.yyyy",
         "ne-NP": "M/d/yyyy",
         "nl-BE": "d/MM/yyyy",
         "nl-NL": "d-M-yyyy",
         "nn-NO": "dd.MM.yyyy",
         "nso-ZA": "yyyy/MM/dd",
         "oc-FR": "dd/MM/yyyy",
         "or-IN": "dd-MM-yy",
         "pa-IN": "dd-MM-yy",
         "pl-PL": "dd.MM.yyyy",
         "prs-AF": "dd/MM/yy",
         "ps-AF": "dd/MM/yy",
         "pt-BR": "d/M/yyyy",
         "pt-PT": "dd-MM-yyyy",
         "qut-GT": "dd/MM/yyyy",
         "quz-BO": "dd/MM/yyyy",
         "quz-EC": "dd/MM/yyyy",
         "quz-PE": "dd/MM/yyyy",
         "rm-CH": "dd/MM/yyyy",
         "ro-RO": "dd.MM.yyyy",
         "ru-RU": "dd.MM.yyyy",
         "rw-RW": "M/d/yyyy",
         "sa-IN": "dd-MM-yyyy",
         "sah-RU": "MM.dd.yyyy",
         "se-FI": "d.M.yyyy",
         "se-NO": "dd.MM.yyyy",
         "se-SE": "yyyy-MM-dd",
         "si-LK": "yyyy-MM-dd",
         "sk-SK": "d. M. yyyy",
         "sl-SI": "d.M.yyyy",
         "sma-NO": "dd.MM.yyyy",
         "sma-SE": "yyyy-MM-dd",
         "smj-NO": "dd.MM.yyyy",
         "smj-SE": "yyyy-MM-dd",
         "smn-FI": "d.M.yyyy",
         "sms-FI": "d.M.yyyy",
         "sq-AL": "yyyy-MM-dd",
         "sr-Cyrl-BA": "d.M.yyyy",
         "sr-Cyrl-CS": "d.M.yyyy",
         "sr-Cyrl-ME": "d.M.yyyy",
         "sr-Cyrl-RS": "d.M.yyyy",
         "sr-Latn-BA": "d.M.yyyy",
         "sr-Latn-CS": "d.M.yyyy",
         "sr-Latn-ME": "d.M.yyyy",
         "sr-Latn-RS": "d.M.yyyy",
         "sv-FI": "d.M.yyyy",
         "sv-SE": "yyyy-MM-dd",
         "sw-KE": "M/d/yyyy",
         "syr-SY": "dd/MM/yyyy",
         "ta-IN": "dd-MM-yyyy",
         "te-IN": "dd-MM-yy",
         "tg-Cyrl-TJ": "dd.MM.yy",
         "th-TH": "d/M/yyyy",
         "tk-TM": "dd.MM.yy",
         "tn-ZA": "yyyy/MM/dd",
         "tr-TR": "DD.MM.yyyy",
         "tt-RU": "dd.MM.yyyy",
         "tzm-Latn-DZ": "dd-MM-yyyy",
         "ug-CN": "yyyy-M-d",
         "uk-UA": "dd.MM.yyyy",
         "ur-PK": "dd/MM/yyyy",
         "uz-Cyrl-UZ": "dd.MM.yyyy",
         "uz-Latn-UZ": "dd/MM yyyy",
         "vi-VN": "dd/MM/yyyy",
         "wo-SN": "dd/MM/yyyy",
         "xh-ZA": "yyyy/MM/dd",
         "yo-NG": "d/M/yyyy",
         "zh-CN": "yyyy/M/d",
         "zh-HK": "d/M/yyyy",
         "zh-MO": "d/M/yyyy",
         "zh-SG": "d/M/yyyy",
         "zh-TW": "yyyy/M/d",
         "zu-ZA": "yyyy/MM/dd",
       };
       return formats[navigator.language] || "dd/MM/yyyy";
     }


