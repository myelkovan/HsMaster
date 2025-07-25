       var tekrarDENE=false;
//background.js den getData requesti gelince aktif taba eklenmis olan bu kod cagiriliyor
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.action === "getData") {
        //mevcut tab daki web sayfasinin adi aliniyor -> amazon
        const siteNames=['google','amazon','ebay','etsy','bestbuy','walmart','target','bigbasket','nike',
                        'adidas','michaelkors','bhphotovideo','sears',
                        'costco','underarmour','reebok','macys','nordstrom','www.calvinklein.us',
                        'tommy','columbia','levi','zara','converse','kohls',
                        'jcpenney','sephora','gap','jcrew','victoriassecret','puma',
                        'skechers','ralphlauren','tiffany','fossil','ray-ban',//bananarepublic de gap ile ortak
                        'uniqlo','autozone','oakley','vans','journeys','drmartens',
                        'ugg','coachoutlet','us.pandora.net','ae','swarovski','kendrascott',
                        'ulta','maccosmetics','bathandbodyworks'];
        
        const hostname = window.location.hostname
        let siteName = hostname.match(/(?:www\.)?([^\.]+)\.com/);
        siteName = siteName ? siteName[1] : hostname;

    if (siteNames.includes(siteName)) {
            // boyutları almak için özel olarak tıklanıp açılması gereken bölümü aç. "View Product Details","Specifications"  v.b
            if (siteName==='nike'){ 
               var button = document.querySelector('[data-testid="view-product-details-button"]');
               if (button) {
                  button.click();
            }
            }
            if (siteName==='bestbuy'){ 
               var button = document.querySelector('[data-testid="brix-button"]');
               //var iframeBestBuy=document.evaluate('//*[@title="manufacturer-content-iframe"]', document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
               var iframeXPath='iframe[title="manufacturer-content-iframe"]';
               fetchIframeContent(iframeXPath, function(iframeContent) {
            var iframeData = iframeContent; // İframe'den gelen değerleri bir değişkene ata
         //   var specs = iframeData.querySelector('div.specs');
         
            console.log(iframeData);
            // Burada iframeData değişkenini kullanabilirsiniz
            // sendResponse işlemini daha sonra yapabilirsiniz
            });
               if (button) {
               //   button.click();
            }
            }
            if (siteName==='target'){ 
               //var buttons = document.querySelectorAll('button.styles_button__D8Xvn.styles_buttonStandard__0BuND.styles_buttonEnabled__3cVAx');
               var buttons = document.querySelectorAll('button');
               var targetButton = Array.from(buttons).find(button => button.textContent.includes('Specifications'));
               if (targetButton) {
                  targetButton.click();
               }
            }
         //getPricefromUPCsearch("195949728969")
         //transformALLsiteKEYdata(siteNames)

         //Sayfayi tarayip verileri bulan kendi functionumuzu cagiriyor ve gelen datayi geri gonderiyoruz
        readData(siteName).then(data => {
            if (tekrarDENE===false){
               sendResponse(data);
            } else{
                     readData(siteName).then(data => {
                  //if (tekrarDENE===false){
                     sendResponse(data);
                  //}
                  //sendResponse(data);
               }).catch(error => {
                     console.error("Error fetching data:", error);
                     sendResponse({ error: error.message });
               });
            }
            //sendResponse(data);
        }).catch(error => {
            console.error("Error fetching data:", error);
            sendResponse({ error: error.message });
        });
       return true;
    }else{  // desteklenmeyen bir site
      console.log("Bu site desteklenmiyor", siteName);
      sendResponse({siteName:"Bu site desteklenmiyor"});
     }
   }
});

function convertKeyRowsToJSON_2(apiResponse) {
    const result = {};

    apiResponse.forEach(item => {
        const mainType = item.main_type;
        const subType = item.sub_type;
        const value = item.value.replace(/\\\//g, '/'); // Escape edilmiş / karakterlerini normale çevirir

        if (!result[mainType]) {
            result[mainType] = {};
        }

        if (!result[mainType][subType]) {
            result[mainType][subType] = [];
        }

        result[mainType][subType].push(value);
    });

    return result;
}

//Bizim kendi site inceleme ve veri bulma kodumuz
async function readData(siteName) {
    var url;
    var length = "";
    var width = "";
    var height = "";
    var weight = "";
    var asin = "";
    var description = "";
    var key = "";
    var imageUrl = "";
    var price = "";
    var price_cents = "";
    var length_unit = "";
    var weight_unit = "";

      try {
             //veritabanina baglan ve aktif tab icin keylerini al
            try {
                  const response = await fetch('https://jetbasket.us/shop/PHP/extension_key_select_v2.php?webpage='+siteName); // Ensure this URL is correct
                  //const response = await fetch('http://localhost/JB/PHP/extension_key_select_v2.php?webpage='+siteName); // Ensure this URL is correct

               if (!response.ok) {
                  throw new Error('Network response was not ok: ' + response.statusText);
               }
                //var data = await response.json(); // Parse JSON response  ORJINAL BU
                if (siteName==="amazon1"){
                  var data = await response.json(); // Parse JSON response  ORJINAL BU
                }else{
                  var text = await response.text();
                  //console.log("SERVİS RESPONSE :",text)
                  var _data =  convertKeyRowsToJSON_2(JSON.parse(text))   //convertKeyRowsToJSON(response.data);
                  var data=[_data]        //data[0].Main şeklinde okumak için bunları ekledik.
                  console.log("SERVİS RESPONSE :",data)
                }
            console.log(JSON.stringify(data), null, 2);

            } catch (error) {
               console.log('VERİ TABANINDAN ÇEKEMEDİM BURDAN DEVAM', siteName + error);
               var data = getSiteKeys(siteName);
               console.log('SERVİS HATA VERDİ: keyleri lokalden  ALDIM')
            }
            
            if (data.length===0){
               console.log('Veri tabaından keyler gelmedi');
               var data = getSiteKeys(siteName);
               console.log('SERVİSTEN KEYLER GELMEDİ:  lokalden ALDIM')
            }
            if (data.length===0){
               throw new Error('Key of ' + siteName + ' is not defined ');
             }
            console.log('BURADAN: data[0]:',data[0]);
            //ilk veri url
            url = window.location.href;
            console.log('data[0].url:',url);
        
            try {
               if (data[0].Main && data[0].Main.asin) {
                     const asinKeys = data[0].Main.asin;
                           // Asin değerleri üzerinde for döngüsü ile işlem yapma ve döngüden çıkma
                           for (let i = 0; i < asinKeys.length; i++) {
                              console.log("asin Key değeri:" + i , asinKeys[i]);
                              asin=getTextContentByXPath(asinKeys[i])
                                                      
                              // eğer asin bilgisini almışsan döngüden çık
                              if (asin && asin !== "") {
                                 asin=extractASIN(asin)
                                 console.log("İstenen asin değerine ulaşıldı, döngüden çıkılıyor.");
                                 break; // Döngüyü sonlandırır
                              }
                           }
                  } else {
                  console.log("asin Key bulunamadı.");
                  }
             } catch (error) {
               console.log("asin xPath sorunlu olabilir.",error);
             }
           
            try{
                  if (data[0].Main && data[0].Main.description) {
                     const descriptionKeys = data[0].Main.description;
                           for (let i = 0; i < descriptionKeys.length; i++) {
                              console.log("description Key değeri:" + i , descriptionKeys[i]);
                              description=getTextContentByXPath(descriptionKeys[i])
                              if (description && description !== "") {
                                 console.log("İstenen description değerine ulaşıldı, döngüden çıkılıyor.:",description);
                                 break; // Döngüyü sonlandırır
                              }
                           }
                  } else {
                  console.log("description Key bulunamadı.");
                  }
            } catch (error) {
               console.log("description xPath sorunlu olabilir.",error);
             }

            try {
                  // bu sitelerin resim bilgisine erişmek için özel işlemler gerekiyor 
                  var ResmiDinamikSiteler=['macys','www.calvinklein.us','tommy','columbia',
                                          'kohls','victoriassecret','skechers','autozone','oakley',
                                          'coachoutlet','us.pandora.net']  
               
                  if (!ResmiDinamikSiteler.includes(siteName)){   // normal sitelerden resim çekme
                     if (data[0].Main && data[0].Main.image) {
                           const imageKeys = data[0].Main.image;
                           for (let i = 0; i < imageKeys.length; i++) {
                              console.log("image Key değeri:" + i , imageKeys[i]);
                              imageUrl = getImageByXpath(imageKeys[i])
                              if (imageUrl && imageUrl !== "") {
                                 console.log("İstenen imageUrl değerine ulaşıldı, döngüden çıkılıyor:",imageUrl);
                                 break; // Döngüyü sonlandırır
                              }
                           }
                     } else {
                           console.log("image Key bulunamadı.");
                           }
                  }else{    // resim adresini  dinamik olarak değiştiren  siteler 
                        switch (siteName) {
                        case 'macys':
                           yeniDeger=asin
                        default:
                              yeniDeger=description.split(' ')[0] // description ilk kelimesini al
                        }
                        imageUrl=getIMAGEURL(data,siteName,"DINAMIKRESIM",yeniDeger) //getIMAGEURL(data,_siteName,eskiDeger="DINAMIKRESIM",yeniDeger)
                  }
               } catch (error) {
                  console.log("image xPath sorunlu olabilir.",error);
               } 
               console.log('data[0].image1:',imageUrl);

          try{
                  if (data[0].Main && data[0].Main.price) {
                        const priceKeys = data[0].Main.price;
                              // Asin değerleri üzerinde for döngüsü ile işlem yapma ve döngüden çıkma
                              for (let i = 0; i < priceKeys.length; i++) {
                                 console.log("price Key değeri:" + i , priceKeys[i]);
                                 price=getSpecialPartByXPath(priceKeys[i])
                                 if (price && price !== "") {
                                    console.log("İstenen price değerine ulaşıldı, döngüden çıkılıyor.");
                                    break; // Döngüyü sonlandırır
                                 }
                              }
                     } else {
                     console.log("price Key bulunamadı.");
                     }
            } catch (error) {
               console.log("Price xPath sorunlu olabilir.",error);
             }
               console.log('data[0].Main.price:',price);

            try{
                  let decimalSeparator = price.includes(',') ? ',' : '.'; 
                  let decimalPart = price.split(decimalSeparator)[1]; 
                  if (!price.includes(decimalSeparator) || decimalPart === '00'){
                        if (data[0].Main && data[0].Main.price_cents) {
                           const price_centsKeys = data[0].Main.price_cents;
                                 // Asin değerleri üzerinde for döngüsü ile işlem yapma ve döngüden çıkma
                                 for (let i = 0; i < price_centsKeys.length; i++) {
                                    console.log("price_cents Key değeri:" + i , price_centsKeys[i]);
                                    price=getTextContentByXPath(price_centsKeys[i])
                                    if (price_cents && price_cents !== "") {
                                       console.log("İstenen price_cents değerine ulaşıldı, döngüden çıkılıyor.");
                                       break; // Döngüyü sonlandırır
                                    }
                                 }
                        } else {
                        console.log("price_cents Key bulunamadı.");
                        }
                     }
                     console.log('data[0].Main.price_cents:',price_cents);
               //price_cents diye bir key veritabanindan geldiyse ki bazi sitelerde 10.99 un 99 kismi ayri obje
                  //onuda alip fiyati birlestiriyoruz
                  if (price_cents !==""){
                     price = price + price_cents
                  }
            } catch (error) {
               console.log("price cent  xPath sorunlu olabilir.",error);
             }
                if (data[0].Dimensions){
                  console.log('diğer SİteLERDEN BİRİNDEYİM: ',data[0].Dimensions)
                  var a = of_getOTHER_SITES_Weight(data[0].Dimensions)
               }
               if (siteName === "amazon" && a.width === "" ){
                     var SorguinputElement = document.getElementById('askQuestionTextInput'); 
                     if (SorguinputElement){
                        if (a.width === "" && SorguinputElement.value === "") { // eğer hala boyutlar boş ise amazon için ürün özelliği sorgulamayı çalıştır ve tekrar boyut ara.
                           tekrarDENE=true;
                           searchAndTryAgain(); //performSearch();
                           //tekrarDENE=false;
                        }else{tekrarDENE=false;}   // boyut yada arama text i dolu ise tekrar denemeye gerk yok
                     }else{

                        // İşlevi çalıştırma
                        await scrollAndLoadInput();
                        // setTimeout(() => { // İkinci scroll işlemi 
                        //    window.scrollTo({top: 0, behavior: 'smooth'}); }, 5000); // 2000 milisaniye bekler
                        SorguinputElement = document.getElementById('askQuestionTextInput'); 
                        tekrarDENE=true;
                        // Fonksiyonu çağırın ve işlem tamamlandıktan sonra veya timeout sonrası sayfayı kaydırın
                        await Promise.race([
                           searchAndTryAgain(),
                           timeout_FOR_Promise(2000) // 5000 milisaniye (5 saniye) timeout
                        ]);
                        window.scrollTo({
                           top: 0,
                           behavior: 'smooth'
                        });


                        // await searchAndTryAgain(); //performSearch();
                        //  // Sayfanın başına geri kaydırma
                        // window.scrollTo({
                        //    top: 0,
                        //    behavior: 'smooth'
                        // });
                     }   //  a
                }
         
               if (a){
                 weight  = a.weight
                weight_unit = a.weight_unit
                length  = a.length
                width   = a.width
                height  = a.height
                length_unit = a.length_unit 
               } 
            //price basinda sonunda fazla karakterler varsa onlari temizle
            price = extractPrice(price)
            console.log(' siteName: ', siteName);  console.log(' url: ', url);   console.log(' asin: ', asin); console.log(' description: ', description);  console.log(' price: ', price);
            console.log(' imageUrl: ', imageUrl);  console.log(' weight: ', weight);   console.log(' weight_unit: ', weight_unit);  console.log(' length: ', length);
            console.log(' width: ', width);  console.log(' height: ', height);   console.log(' length_unit: ', length_unit);
            
        } catch (error) {
           console.log('Hata OLUŞTU:',error)
        }
     return { url, description, price, weight, weight_unit, length, width, height, length_unit, asin, imageUrl };

    };

// Belirli bir süre boyunca bekleme işlevi
function waitForElement(selector, timeout = 2000) {
  return new Promise((resolve, reject) => {
    const interval = 100;
    let elapsedTime = 0;
    const checkExistence = setInterval(() => {
      if (document.querySelector(selector)) {
        clearInterval(checkExistence);
        resolve(document.querySelector(selector));
      } else {
        elapsedTime += interval;
        if (elapsedTime >= timeout) {
          clearInterval(checkExistence);
          reject(new Error("Element not found within the timeout period"));
        }
      }
    }, interval);
  });
}
async function scrollAndLoadInput() {
      // İlk scroll işlemi (belirli bir nesneye kadar)
      const scrollTarget = document.querySelector('#Ask'); // Nesneyi seçin
      if (scrollTarget) {
         scrollTarget.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
         });
         // Input elementin yüklenmesini bekleme
         try {
            await waitForElement('#askQuestionTextInput');
            console.log("Input element yüklendi!");
            // // Sayfanın başına geri kaydırma
            // window.scrollTo({
            // top: 0,
            // behavior: 'smooth'
            // });

         } catch (error) {
            console.log(error.message);
         }
      } else {
         console.log("Scroll yapılacak nesne bulunamadı.");
      }
}
// Timeout fonksiyonu
function timeout_FOR_Promise(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}
async function searchAndTryAgain(){
       try {
        const inputElement = document.getElementById('askQuestionTextInput');
        inputElement.value = 'dimension and weight ';
        // Input olayını manuel olarak tetikleyin
        const inputEvent = new Event('input', { bubbles: true });
        inputElement.dispatchEvent(inputEvent);
        // Boşluk karakterini input alanına ekleyin ve input olayını tekrar tetikleyin
        //inputElement.value += ' ';
        //inputElement.dispatchEvent(new Event('input', { bubbles: true }));

        // Biraz bekle ve ardından keypress olayını tetiklemeyi dene
        await new Promise(resolve => setTimeout(resolve, 100)); // 100 ms bekle
        const spaceKeyEvent = new KeyboardEvent('keypress', {
            key: ' ',
            code: 'Space',
            charCode: 32,
            keyCode: 32,
            which: 32,
            bubbles: true
        });
        inputElement.dispatchEvent(spaceKeyEvent);

        // Biraz daha bekle ve enter tuşuna basarak aramayı zorla
        await new Promise(resolve => setTimeout(resolve, 100)); // 100 ms bekle
        const enterKeyEvent = new KeyboardEvent('keypress', {
            key: 'Enter',
            code: 'Enter',
            charCode: 13,
            keyCode: 13,
            which: 13,
            bubbles: true
        });
        inputElement.dispatchEvent(enterKeyEvent);
      
        await new Promise(resolve => setTimeout(resolve, 100)); // 100 ms bekle
        // Arama sonuçları ile ilgili işlemleri yapacak fonksiyon
        function handleSearchResults(resolve) {
            // İşlemler burada gerçekleştirilecek
            console.log('Arama sonuçları işlendi.');
            a = of_getOTHER_SITES_Weight();
            resolve(); // Promise'i çözmek için resolve'u çağırın
        }

        // Promise döndürün
        return new Promise((resolve, reject) => {
            // MutationObserver'ı tanımlayın
            const observer = new MutationObserver((mutations) => {
                handleSearchResults(resolve);
                observer.disconnect(); // Observer'ı durdurun
            });
            // Sayfada dinamik olarak eklenen elementleri izlemek için observer'ı başlatın
            observer.observe(document.body, { childList: true, subtree: true });
        });
    } catch (error) {
        console.log('hata oluştu: .', error);       
    } 
}

function searchTextEkle(strInput="askQuestionTextInput",strSearch="dimension"){
     // Input elementini seçin ve değer atayın
      const inputElement = document.getElementById(strInput) //('askQuestionTextInput');
      inputElement.value = strSearch;

      // Input olayını manuel olarak tetikleyin
      const inputEvent = new Event('input', { bubbles: true });
      inputElement.dispatchEvent(inputEvent);

      // Boşluk karakterini input alanına ekleyin ve input olayını tekrar tetikleyin
      inputElement.value += ' ';
      inputElement.dispatchEvent(new Event('input', { bubbles: true }));

      // Keypress (boşluk) olayını tetikleyin
      const spaceKeyEvent = new KeyboardEvent('keypress', {
      key: ' ',
      code: 'Space',
      charCode: 32,
      keyCode: 32,
      which: 32,
      bubbles: true
      });
      inputElement.dispatchEvent(spaceKeyEvent); 
   }
// Sayfa işlemlerini durdurmak için Promise tabanlı bir fonksiyon tanımlayın
function waitForSearchResults() {
  return new Promise((resolve) => {
    const observer = new MutationObserver((mutations) => {
               handleSearchResults();
               observer.disconnect(); // Observer'ı durdurun
               resolve(node); // Promise'i çöz
                     // mutations.forEach((mutation) => {
                     // mutation.addedNodes.forEach((node) => {
                     //    if (node.nodeType === 1 && node.classList.contains('askBtfSearchResultsContainer')) {
                     //       // Arama sonuçlarının yüklendiği durumda çalışacak kodlar
                     //       console.log('Arama sonuçları yüklendi!');
                     //       observer.disconnect(); // Observer'ı durdurun
                     //       resolve(node); // Promise'i çöz
                     //    }
                     // });
                     // });
    });

    // Sayfada dinamik olarak eklenen elementleri izlemek için observer'ı başlatın
    observer.observe(document.body, { childList: true, subtree: true });
  });
}
// Asenkron bir işlev tanımlayın
async function performSearch() {
   searchTextEkle();
  // Sayfa işlemlerini durdurun ve arama sonuçlarının yüklenmesini bekleyin
  const searchResultsContainer = await waitForSearchResults();

  // Arama sonuçları ile ilgili işlemleri yapın
  handleSearchResults();

  // Sayfa işlemleri devam edebilir
  console.log('İşlemler tamamlandı, sayfa devam ediyor.');
}
// Arama sonuçları ile ilgili işlemleri yapacak fonksiyon
function handleSearchResults() {
  // İşlemler burada gerçekleştirilecek
  console.log('Arama sonuçları işlendi.');
   a = of_getOTHER_SITES_Weight();
}


// İframe içeriğinden bilgileri almak için çalışan fonksiyon
function fetchIframeContent(iframeXPath,xpath, callback) {
   return new Promise((resolve, reject) => {
   //iframeXPath='iframe[title="manufacturer-content-iframe"]'
    var iframe = document.querySelector(iframeXPath);
    if (iframe) {
        var iframeSrc = iframe.src;
        chrome.runtime.sendMessage(
            {action: "openIframeAndFetchContent", src: iframeSrc, xpath: xpath},
            function(response) {
                resolve(response);
            }
        );
    } else {
        resolve('Iframe not found.');
    }
     });
}


function transformALLsiteKEYdata(sites){
var jsonString
         sites.forEach(siteN => {
               var path=siteN;
               const data=getSiteKeys(siteN)
           var newData = transformSiteKeysData(data[0]);   
      const _jsonString = JSON.stringify(newData, null, 2) 
      .replace(/"([^"]+)":/g, '"$1":') // Anahtarların tırnaklarını korur 
      .replace(/"([^"]*?)"/g, function(match, p1) { 
         return "'" + p1.replace(/'/g, "\\'") + "'"; // Değerlerin tırnaklarını değiştirir ve içindeki çift tırnakları korur });

         });
         jsonString= jsonString + "\n\nSİTE ADI:" + siteN + "\n\n" + _jsonString
            })

             

console.log(jsonString);

}
    function transformSiteKeysData(oldData) {
  const newData = {
    Main: {},
    Dimensions: {}
  };

  // Main verilerini işleme
  for (let key in oldData) {
    if (oldData.hasOwnProperty(key)) {
      const value = oldData[key];

      if (key.startsWith("description") && value !== "zz") {
        if (!newData.Main.description) {
          newData.Main.description = [];
        }
        newData.Main.description.push(value);
      }

      if (key.startsWith("image") && value !== "zz") {
        if (!newData.Main.image) {
          newData.Main.image = [];
        }
        newData.Main.image.push(value);
      }

      if (key.startsWith("price") && value !== "zz") {
        if (!newData.Main.price) {
          newData.Main.price = [];
        }
        newData.Main.price.push(value);
      }

      if (key === "asin" && value !== "zz") {
        if (!newData.Main.asin) {
          newData.Main.asin = [];
        }
        newData.Main.asin.push(value);
      }
    }
  }

  // Dimensions verilerini işleme
  if (oldData.dimensions) {
    oldData.dimensions.forEach(dimension => {
      if (dimension.value !== "zz") {
        if (!newData.Dimensions[dimension.type]) {
          newData.Dimensions[dimension.type] = [];
        }
        newData.Dimensions[dimension.type].push(dimension.value);
      }
    });
  }

  return newData;
}
     // normal de sitelerde arama yapılacak değerlerin keyleri siteden/veritabanından geliyor.
     // ancak bir şekilde hata olurda siteden/veritabanindan gelmezise diye alışveriş sitesine göre tüm keyleri buradan alıyoruz.
function getSiteKeys(sitename) {
  }


   // veritabanindan veya o aktif değil se getSiteKeys() den  gelen description1, description2 ve description3 keylerinin verilerini sayfadan bulmaya calis
function getDescription(data){
   var _getDescription;
   console.log('getDescriptionDAYIM:',data[0].description1)
   _key = data[0].description1
   console.log('getDescriptionDAYIM:_key',_key)
            if (_key !== "" && _key != null){
                 _getDescription = getTextContentByXPath(_key)
                 if (_getDescription === ""){
                    _key = data[0].description2
                    if (_key !== "" && _key != null){
                       _getDescription = getTextContentByXPath(_key)
                       if (_getDescription === ""){
                          _key = data[0].description3
                          if (_key !== "" && _key != null){
                               _getDescription = getTextContentByXPath(_key)
                          }
                       }
                    }
                 }
            }
            
            return _getDescription;
}
  // eğer site resim datasını dinamik tutan bir site ise farklı şekilde imgurl aliyoruz
function getIMAGEURL(data,_siteName,eskiDeger="DINAMIKRESIM",yeniDeger){
   var _getIMAGEURL;
   yeniDeger=yeniDeger.replace(/&/g, '&amp;');        // description da  & varsa  hata alıyor en azından kohl sitesi öyle
  // console.log('getIMAGEURL  DEYİM:',data[0].image1)
            if (data[0].Main && data[0].Main.image) {
                     const imageKeys = data[0].Main.image;
                     for (let i = 0; i < imageKeys.length; i++) {
                        console.log("image Key değeri:" + i , imageKeys[i]);
                           _key =imageKeys[i]
                            _key = _key.replace(/DINAMIKRESIM/g, yeniDeger); // key deki DINAMIKRESIM  ifadesini yeni deger ile degistiriyoruz ve src değil srcset  değerini alıyoruz. 
                          _getIMAGEURL = getImage_srcSET_ByXpath(_key)
                         if (_getIMAGEURL && _getIMAGEURL !== "") {
                           console.log("İstenen imageUrl değerine ulaşıldı, döngüden çıkılıyor:",_getIMAGEURL);
                            var turnedIMG = _getIMAGEURL ? _getIMAGEURL.split(',')[0].split(' ')[0] : null; // İlk URL'yi al
                           break; // Döngüyü sonlandırır
                        }
                     }
                     if (turnedIMG){
                        return turnedIMG.replace(/&/g, '&amp;');  //  & işareti yerine &amp;  istiyor
                     }
               }
               return "";
}


    function of_getAmazonWeight(){

      var read = "";
      var length = "";
      var width = "";
      var height = "";
      var weight = "";
      var asin = "";
      var weight_unit=""
      var length_unit=""
                           
                           
      const xpathArray = [ '//div[contains(@id,"productDetails") or contains(@id,"tech") ]//*[contains(.," Weight ")]/following::*//text()',
                           '//div[contains(@id,"productDetails") or contains(@id,"tech") ]//*[contains(.," Dimensions ") or contains(.," Size ")]/following::*//text()',
                           '//div[contains(@id,"productDetails") or contains(@id,"tech")]//tr[contains(.,"Weight")]/td[2]//p//text()',
                           '//div[contains(@id,"productDetails") or contains(@id,"tech") ]//tr[contains(.," Dimensions ") or contains(.," Size")]/td[position()=2]//p//text()',
                           //'//*[@id="detailBullets_feature_div"]//*[contains(.," Dimensions")]/following::*/text()',
                           '//*[@id="detailBullets_feature_div"]//*[contains(normalize-space(.),"Weight")]/following::*/text()',
                           '//*[@id="detailBullets_feature_div"]//*[contains(normalize-space(.),"Dimensions")]/following::*/text()',
                           '//*[@id="ask-btf-container"]//span[@class="matches" and contains(.,"dimensions")]/following::span[contains(text(),"length x width x height")]/text() | //*[@id="ask-btf-container"]//span[@class="matches" and contains(.,"dimensions")]/following::text()[contains(., " x ")]'

                           
                          //'//*[@id="ask-btf-container"]//span[@class="matches" and  contains(.," dimension")]/following-sibling::text()[contains(., "length x width x height:")]/following::text()',
                           // //'//div[contains(@id,"productDetails")]//*[contains(.,"Dimensions") or contains(.,"Size")]/descendant::*/text() | //div[contains(@id,"productDetails")]//*[contains(.,"Dimensions") or contains(.,"Size")]/following::*/text()'
                           // '//*[@id="productDetails_techSpec_section_1"]/tbody/tr[XXX]/td',
                           // '//*[@id="detailBullets_feature_div"]/ul/li[1]/span/span[XXX]/text()',
                           // '//*[@id="productDetails_techSpec_section_1"]/tbody/tr[XXX]/td',
                           // '//*[@id="productOverview_feature_div"]/div/table/tbody/tr[XXX]/td[2]/span',                         //SİNAN: ben sonradan ekledim
                           // '//*[@id="productDetails_expanderTables_depthLeftSections"]/div[1]/div/div/table/tbody/tr[XXX]/td',  //SİNAN: ben sonradan ekledim
                           // '//*[@id="productDetails_expanderTables_depthLeftSections"]/div[2]/div/div/table/tbody/tr[XXX]/td',  //SİNAN: ben sonradan ekledim ASIN:B0C6F6VYW6
                           // '//*[@id="productDetails_expanderTables_depthLeftSections"]/div[3]/div/div/table/tbody/tr[XXX]/td',   //SİNAN: ağırlıkla ilgili ekledim 
                           // '//*[@id="detailBullets_feature_div"]/ul/li[XXX]/span/span[2]',                                         //SİNAN: boyut örn ASIN:B09S5GWJRD
                           // '//*[@id="glance_icons_div"]/table/tbody/tr[2]/td[2]/table/tbody/tr/td[2]/span[2]',                 //SİNAN ağırlık alıyor sadece örn:B0CJZMP7L1
                           //  '//*[@id="tech"]/div[4]/div/div[1]/div/table/tbody/tr[1]/td[2]/p',
                           //  '//*[@id="tech"]//td[contains(.,"Weight")]/following::td',
                           //  '//*[@id="tech"]/div[4]/div/div[1]/div/table/tbody/tr[2]/td[2]/p'
                        ];                                    
                                             // "//*[@id="productDetails_techSpec_section_1"]//*[contains(.,"Weight")]/following::td" //*[@id="productOverview_feature_div"]/div/table/tbody/tr[2]/td[2]/span',     //SİNAN: 5"L x 2"W x 23"H şeklinde boyut  örn ASIN:B07DBCDCTX
                                         
         asin=getValueById('ASIN') //#ASIN     
         console.log('ASIN FROM ID:',asin)  

        for (let xpath of xpathArray) {
            try{

               
               var path=xpath;
               var _maxDonguSayisi=30;
               console.log(` Key Değer: ${xpath}`);
            for (var i = 1; i <= _maxDonguSayisi; i++) {
                     if (path.includes('XXX')){
                           path = path.replace(/XXX/g, i);
                           console.log('path XXX ler' + i + ' OLDU:',path)
                        }else{
                           i=30;       // XXX yoksa 30 kere dönmeye gerek yok
                        }
                 read = getTextContentByXPath(path)

                 if (typeof read === 'string') {
                     read = read.toLowerCase();
                     if (read.length>0) {
                        console.log(i + 'READ:' +  path,read)    //SİNAN:YAPILACAK  5 1/2" h   şeklinde boyut buluyor ama aşağıdaki kodlar rakamları alamıyor  örn. ASIN:B00NQQTZCO
                     }
                     if ((read.includes("inch") || read.includes('"')) && read.includes(' x ') && length === "") {
                        var dimensions = extractDimensions(read);
                        console.log('dimentionsı Buldum :',dimensions)
                        length = dimensions.length;
                        width = dimensions.width;
                        height = dimensions.height;
                        length_unit = "in"
                     }

                      if ((read.includes("cm") || read.includes('"')) && read.includes(' x ') && length === "") {
                         var dimensions = extractDimensions(read);
                         length = dimensions.length;
                         width = dimensions.width;
                         height = dimensions.height;
                         length_unit = "cm"
                      }
                     
                      if (read.includes("mm")  && read.includes(' x ') && length === "") {
                         var dimensions = extractDimensions(read);
                         length = dimensions.length;
                         width = dimensions.width;
                         height = dimensions.height;
                         length_unit = "mm"
                      }

                     if (read.includes("pound") && weight === "") {
                        const foundWeight  = getPounds(read,["pounds", "pound"])
                        if (foundWeight > weight){
                            weight = foundWeight
                            weight_unit = "pound"
                        }
                     }

                     if (read.includes("ounce") && weight ==="") {
                       const foundWeight = (parseFloat(getPounds(read,["ounce", "ounces"]))).toFixed(2)
                        if (foundWeight > weight){
                            weight = foundWeight
                            weight_unit = "oz"
                        }
                     }
                     if (read.includes(" oz ") && weight ==="") {
                       const foundWeight = (parseFloat(getPounds(read,["oz"]))).toFixed(2)
                        if (foundWeight > weight){
                            weight = foundWeight
                            weight_unit = "oz"
                        }
                     }

                     if ((read.includes("kilogram") || read.includes("kg")) && weight ==="") {
                        const foundWeight = (parseFloat(getPounds(read,["kilograms","kilogram", "kg"]))).toFixed(2)
                         if (foundWeight > weight){
                             weight = foundWeight
                             weight_unit = "kg"
                         }
                      }

                   if ((read.includes("gram") || read.includes("gr") || read.includes(" g")) && weight ==="") {
                       const foundWeight = (parseFloat(getPounds(read,["gram","grams", "gr", " g"]))).toFixed(2)  //SİNAN: "grams" şeklinde ifadeyi bulamıyordu ekledim
                        console.log('GRAM YAKALADIM:',foundWeight)
                        if (foundWeight > weight){
                            weight = foundWeight
                            weight_unit = "gr"
                        }
                     }


                     if (isValidASIN(read.toUpperCase()) && asin ===""){
                        asin = read.toUpperCase()
                     }
                }
            }
         
             } catch (error) {
               console.log('umulmayan bir hata oldu sonraki xpathe gec : ',error)
            }
        }

         return {  weight, weight_unit, length, width, height, length_unit, asin };
   }
    function of_getAmazonWeight_2(strXPaths){

      var read = "";
      var length = "";
      var width = "";
      var height = "";
      var weight = "";
      var asin = "";
      var weight_unit=""
      var length_unit=""
      
      var xPaths =strXPaths          
      if (xPaths){
        
      }else{
            try {
         xPaths = [
        {
            'ALL': [
                '//div[contains(@id,"productDetails") or contains(@id,"tech") ]//*[contains(.," Weight ")]/following::*//text()',
                '//div[contains(@id,"productDetails") or contains(@id,"tech") ]//*[contains(.," Dimensions ") or contains(.," Size ")]/following::*//text()',
                '//div[contains(@id,"productDetails") or contains(@id,"tech")]//tr[contains(.,"Weight")]/td[2]//p//text()',
                '//div[contains(@id,"productDetails") or contains(@id,"tech") ]//tr[contains(.," Dimensions ") or contains(.," Size")]/td[position()=2]//p//text()',
                '//*[@id="detailBullets_feature_div"]//*[contains(normalize-space(.),"Weight")]/following::*/text()',
                '//*[@id="detailBullets_feature_div"]//*[contains(normalize-space(.),"Dimensions")]/following::*/text()',
                '//*[@id="ask-btf-container"]//span[@class="matches" and contains(.,"dimensions")]/following::span[contains(text(),"length x width x height")]/text() | //*[@id="ask-btf-container"]//span[@class="matches" and contains(.,"dimensions")]/following::text()[contains(., " x ")]'
            ],
            'Width': [''],
            'Length': [''],
            'Height': [''],
            'Weight': ['']
        }
    ];
} catch (e) {
    console.error('Bir hata oluştu:', e);
}

     }

   for (let subType of xPaths) {
      try{
          if (xPaths.hasOwnProperty(subType)) {
            const values = xPaths[subType];
            console.log(`Alt tür: ${subType}`);
            values.forEach(value => {     
               var path=value;
               var _maxDonguSayisi=30;
               console.log(` Key Değer: ${value}`);
            for (var i = 1; i <= _maxDonguSayisi; i++) {
                     if (path.includes('XXX')){
                           path = path.replace(/XXX/g, i);
                           console.log('path XXX ler' + i + ' OLDU:',path)
                        }else{
                           i=30;       // XXX yoksa 30 kere dönmeye gerek yok
                        }
                 read = getTextContentByXPath(path)

                 if (typeof read === 'string') {
                     read = read.toLowerCase();
                     if (read.length>0) {
                        console.log(i + 'READ:' +  path,read)    //SİNAN:YAPILACAK  5 1/2" h   şeklinde boyut buluyor ama aşağıdaki kodlar rakamları alamıyor  örn. ASIN:B00NQQTZCO
                     }
                     else{
                        console.log('READ BOŞ -',i)
                        continue;
                     }
                     console.log('xpath tipi :',subType)
                     
                     switch (subType.toLowerCase()){
                        
                     case "length": //SİNAN:YAPILACAK
                        var dimensions = extractDimensions_WithType(read,"length");
                        length = dimensions.length;
                        break;
                     case "width":   //SİNAN:YAPILACAK
                        var dimensions = extractDimensions_WithType(read,"width");
                        width = dimensions.width;
                        break;
                     case "height":  //SİNAN:YAPILACAK
                        var dimensions = extractDimensions_WithType(read,"height");
                        height = dimensions.height;
                        break;
                     case "weight":  //SİNAN:YAPILACAK
                           if (read.includes("pound") && weight === "") {
                              const foundWeight  = getPounds(read,["pounds", "pound"])
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "pound"
                              }
                           }
                           if ((read.includes("lb") || read.includes("oz")) && weight === "") {
                                 const foundWeight  = getPounds(read,["lb", "oz"])
                                 if (foundWeight > weight){
                                    weight = foundWeight
                                    weight_unit = "lb"
                                 }
                           }
                           if (read.includes("ounce") && weight ==="") {
                           const foundWeight = (parseFloat(getPounds(read,["ounce", "ounces"]))).toFixed(2)
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "oz"
                              }
                           }

                           if ((read.includes("kilogram") || read.includes("kg")) && weight ==="") {
                              const foundWeight = (parseFloat(getPounds(read,["kilograms","kilogram", "kg"]))).toFixed(2)
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "kg"
                              }
                           }

                        if ((read.includes("gram") || read.includes("gr") || read.includes(" g")) && weight ==="") {
                           const foundWeight = (parseFloat(getPounds(read,["gram","grams", "gr", " g"]))).toFixed(2)  //SİNAN: "grams" şeklinde ifadeyi bulamıyordu ekledim
                              console.log('GRAM YAKALADIM:',foundWeight)
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "gr"
                              }
                           }
                              break;
                     case "all":
                  // eğer xpath tüm boyutları içerisyorsa 
                        console.log('ALL  içindeyim: ',read)
                        if ((read.includes("inch")|| read.includes('in.')  || read.includes(' x ') || read.includes('"') || read.includes('\'\'') || read.includes('”')  || read.includes(' cm ')) && length === ""){
                           console.log('dimentionsı Buldum :',read)
                           read=removeTextBeforeFirstNumber(read);
                           //var dimensions = extractDimensions(read);
                           var dimensions = extractDimensions_WithType(read,"all");
                           console.log('dimentionsı Buldum :2',dimensions)
                              length = dimensions.length;
                              width = dimensions.width;
                              height = dimensions.height;
                              //YAPILACAK:  hem inch hem de cm cinsinden değerler varsa birimi doğru seçmek için kontrol yapmalı. ÖRN: ASIN:B0BZWRSRWV
                           if ((read.includes("inch") || read.includes(' in')|| read.includes('"') || read.includes('\'\'')  || read.includes('”') ) ) {
                                 length_unit = "in"
                              }

                           if ((read.includes("cm")) && length_unit==="" ) {
                                 length_unit = "cm"
                              }
                              
                           if ((read.includes("mm")) && length_unit==="" )  {
                                 length_unit = "mm"
                              }

                        }

                        if  (weight === ""){    //SİNAN:YAPILACAK esasen bu kontrol ile en alttaki foundweight ile kıyaslanması mantıklı değil 
                           if (read.includes("pound")) {
                              const foundWeight  = getPounds(read,["pounds", "pound"])
                              weight_unit = "pound"
                           }

                           if (read.includes("ounce")) {
                              const foundWeight = (parseFloat(getPounds(read,["ounce", "ounces"]))).toFixed(2)
                              weight_unit = "oz"
                           }

                           if (read.includes(" oz ")) {
                              const foundWeight = (parseFloat(getPounds(read,["oz"]))).toFixed(2)
                              weight_unit = "oz"
                           }

                           if ((read.includes("kilogram") || read.includes("kg"))) {
                              const foundWeight = (parseFloat(getPounds(read,["kilograms","kilogram", "kg"]))).toFixed(2)
                              weight_unit = "kg"
                           }

                           
                           if ((weight_unit !== "kg") && ((read.includes("gram") || read.includes("gr") || read.includes(" g")))) {
                              const foundWeight = (parseFloat(getPounds(read,["gram","grams", "gr", " g"]))).toFixed(2)  //SİNAN: "grams" şeklinde ifadeyi bulamıyordu ekledim
                              console.log('GRAM YAKALADIM:',foundWeight)
                              weight_unit = "gr"
                           }
                           if ((foundWeight !== "") && (foundWeight > weight)){
                                 weight = foundWeight
                           }
                  }    
                       
               }
            
               if (isValidASIN(read.toUpperCase()) && asin ===""){
                        asin = read.toUpperCase()
                     }
               }
               }
            });
         }
      } catch (error) {
         console.log('umulmayan bir hata oldu sonraki xpathe gec : ',error)
      }
   }
         console.log('tüm xpathleri dolandım boyutları dönüyorum: ',length)
         return {  weight, weight_unit, length, width, height, length_unit, asin };
   }
function of_getOTHER_SITES_Weight(xPaths){

      var read = "";
      var length = "";
      var width = "";
      var height = "";
      var weight = "";
      var foundWeight = "";
      var asin = "";
      var weight_unit="";
      var length_unit="";

        
// Dimensions içindeki tüm alt türler üzerinde döngü

  for (let subType in xPaths) {
   try {
    if (xPaths.hasOwnProperty(subType)) {
      const values = xPaths[subType];
      
      // Alt türün adını yazdır
      console.log(`Alt tür: ${subType}`);
      
      // Değerler üzerinde döngü
      values.forEach(value => {
           if (value===''){
               return;
           }
            var path=value;
               var _maxDonguSayisi=30;
            console.log(`Değer: ${value}`);

            for (var i = 1; i <= _maxDonguSayisi; i++) {
                        if (path.includes('XXX')){
                           path = path.replace(/XXX/g, i);
                           console.log('path XXX ler' + i + ' OLDU:',path)
                        }else{
                           i=30;
                        }
                     read = getTextContentByXPath(path)
                     
                     if (typeof read === 'string' && read.length>0) {
                           read = read.toLowerCase();
                           if (read.length>0) {
                           console.log(i + 'READ:' +  path,read)    //SİNAN:YAPILACAK  5 1/2" h   şeklinde boyut buluyor ama aşağıdaki kodlar rakamları alamıyor  örn. ASIN:B00NQQTZCO
                           }
                           else{
                              console.log('READ BOŞ -',i)
                              continue;
                           }
                        
                         console.log('xpath tipi :',subType)
                           
                         
                  switch (subType.toLowerCase()){
                        
                     case "length": //SİNAN:YAPILACAK
                        var dimensions = extractDimensions_WithType(read,"length");
                        if (!isNaN(parseFloat(dimensions.length)) && length < parseFloat(dimensions.length)) { 
                           length = parseFloat(dimensions.length); 
                        }
                        break;
                     case "width":   //SİNAN:YAPILACAK
                        var dimensions = extractDimensions_WithType(read,"width");
                        if (!isNaN(parseFloat(dimensions.width)) && width < parseFloat(dimensions.width)) { 
                           width = parseFloat(dimensions.width); 
                        }
                        break;
                     case "height":  //SİNAN:YAPILACAK
                        var dimensions = extractDimensions_WithType(read,"height");
                        if (!isNaN(parseFloat(dimensions.height)) && height < parseFloat(dimensions.height)) { 
                           height = parseFloat(dimensions.height); 
                        }
                        break;
                     case "weight":  //SİNAN:YAPILACAK
                           if (read.includes("pound") && weight === "") {
                              foundWeight  = getPounds(read,["pounds", "pound"])
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "pound"
                              }
                           }
                           if ((read.includes("lb") || read.includes("oz")) && weight === "") {
                                 foundWeight  = getPounds(read,["lbs.","lbs","lb", "oz"])
                                 if (foundWeight > weight){
                                    weight = foundWeight
                                    weight_unit = "lb"
                                 }
                           }
                           if (read.includes("ounce") && weight ==="") {
                              foundWeight = (parseFloat(getPounds(read,["ounce", "ounces"]))).toFixed(2)
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "oz"
                              }
                           }
                           if (read.includes(" oz ") && weight ==="") {
                              foundWeight = (parseFloat(getPounds(read,["oz"]))).toFixed(2)
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "oz"
                              }
                           }

                           if ((read.includes("kilogram") || read.includes("kg")) && weight ==="") {
                              foundWeight = (parseFloat(getPounds(read,["kilograms","kilogram", "kg"]))).toFixed(2)
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "kg"
                              }
                           }

                        if ((weight_unit !== "kg") && (read.includes("gram") || read.includes("gr") || read.includes(" g")) && weight ==="") {
                              foundWeight = (parseFloat(getPounds(read,["gram","grams", "gr", " g"]))).toFixed(2)  //SİNAN: "grams" şeklinde ifadeyi bulamıyordu ekledim
                              console.log('GRAM YAKALADIM:',foundWeight)
                              if (foundWeight > weight){
                                 weight = foundWeight
                                 weight_unit = "gr"
                              }
                           }
                              break;
                     case "all":
                  // eğer xpath tüm boyutları içerisyorsa 
                        console.log('ALL  içindeyim: ',read)
                        if ((read.includes("inch")|| read.includes('in.')  || read.includes(' x ') || read.includes('"') || read.includes('\'\'') || read.includes('”')  || read.includes(' cm ')) && length === ""){
                           console.log('dimentionsı Buldum :',read)
                           read=removeTextBeforeFirstNumber(read);
                           //var dimensions = extractDimensions(read);
                           var dimensions = extractDimensions_WithType(read,"all");
                           console.log('dimentionsı Buldum :2',dimensions)
                              length = dimensions.length;
                              width = dimensions.width;
                              height = dimensions.height;
                              let gelen_length_unit=dimensions.unit;

                           if ((gelen_length_unit.includes("inch") || gelen_length_unit.includes('in')|| gelen_length_unit.includes('"') || gelen_length_unit.includes('\'\'')  || gelen_length_unit.includes('”') ) ){
                              gelen_length_unit = "in"
                           }
                           if ((read.includes("inch") || read.includes(' in')|| read.includes('"') || read.includes('\'\'')  || read.includes('”') ) ) {
                                 length_unit = "in"
                              }

                           if ((read.includes("cm")) && length_unit==="" ) {
                                 length_unit = "cm"
                              }
                              
                           if ((read.includes("mm")) && length_unit==="" )  {
                                 length_unit = "mm"
                                 length = dimensions.length/10;
                                 width = dimensions.width/10;
                                 height = dimensions.height/10;
                                 length_unit = "cm"
                                 if (gelen_length_unit==="mm") {
                                    gelen_length_unit="cm"
                                 }   //gelen_length_unit  de mm gelmişse aşağıdaki kontrole takılmaması için onu da cm ye çevir 
                              }
                           
                           // öteden gelen _length_unit ile burda bulduğumuz length_unit tutarsız ise geleni al (birden fazla birime sahip boyutlarda asin:B0BHZT5S12)
                           if (gelen_length_unit !==""){
                              if (length_unit !== gelen_length_unit){
                                 length_unit = gelen_length_unit
                              }
                           }
                        }
                        
                        if  (weight === ""){    //SİNAN:YAPILACAK esasen bu kontrol ile en alttaki foundweight ile kıyaslanması mantıklı değil 
                           if (read.includes("pound")) {
                              foundWeight  = getPounds(read,["pounds", "pound"])
                              weight_unit = "pound"
                           }

                           if (read.includes("ounce") ) {
                              foundWeight = (parseFloat(getPounds(read,["ounce", "ounces"]))).toFixed(2)
                              weight_unit = "oz"
                           }

                           if (read.includes(" oz ") ) {
                              foundWeight = (parseFloat(getPounds(read,["oz"]))).toFixed(2)
                              weight_unit = "oz"
                           }

                           if ((read.includes("kilogram") || read.includes("kg"))) {
                              foundWeight = (parseFloat(getPounds(read,["kilograms","kilogram", "kg"]))).toFixed(2)
                              weight_unit = "kg"
                           }
                           
                           if ((weight_unit !== "kg") && ((read.includes("gram") || read.includes("gr") || read.includes(" g")))) {
                              foundWeight = (parseFloat(getPounds(read,["gram","grams", "gr", " g"]))).toFixed(2)  //SİNAN: "grams" şeklinde ifadeyi bulamıyordu ekledim
                              weight_unit = "gr"
                              //console.log('GRAM YAKALADIM:',foundWeight)
                           }
                           if ((foundWeight !== "") && (foundWeight > weight)){
                                 weight = foundWeight
                           }
                        }
                  }
                 
                   }
     
         }
         });
         }
   } catch (error) {
          console.log('umulmayan bir hata oldu : ',error)
   }

 }
      
   
      console.log('tüm xpathleri dolandım boyutları dönüyorum: ',length)
         return {  weight, weight_unit, length, width, height, length_unit, asin };
   
}

//Asagida bir messaj ekrani olusturuyor.
function showMessage(message) {
    // Create modal elements
    const modal = document.createElement('div');
    const modalContent = document.createElement('div');
    const closeButton = document.createElement('button');
    const messageText = document.createElement('p');

    // Set styles for modal background
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.zIndex = '1000';

    // Set styles for modal content
    modalContent.style.backgroundColor = 'white';
    modalContent.style.padding = '20px';
    modalContent.style.borderRadius = '8px';
    modalContent.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
    modalContent.style.textAlign = 'center';

    // Set message text
    messageText.textContent = message;

    // Set styles for close button
    closeButton.textContent = 'Close';
    closeButton.style.marginTop = '20px';
    closeButton.style.padding = '10px 15px';
    closeButton.style.backgroundColor = '#007BFF';
    closeButton.style.color = 'white';
    closeButton.style.border = 'none';
    closeButton.style.borderRadius = '5px';
    closeButton.style.cursor = 'pointer';

    // Append elements
    modalContent.appendChild(messageText);
    modalContent.appendChild(closeButton);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    // Close the modal when the button is clicked
    closeButton.onclick = () => {
        document.body.removeChild(modal);
    };
}

//Eklenti ilk kuruldugunda background.js de storage'a firstUse=true yazmistik. Sent tusuna her basildiginda
//bunu kontrol ediyoruz ve Jetbasket sayfasini guncelleyin diyoruz. Eklenti update yapilirsa da ayni islem gecerli
chrome.storage.local.get(['firstUse'], (result) => {
    if (result.firstUse) {
        showMessage('Thank you for installing the JetBasket extension! Please refresh the JetBasket page to start using it.');

        //SİNAN: ilkkullanim değerini sıfırla
        chrome.storage.local.set({ firstUse: false });
    }
});
