console.log('JetBasketContent script loaded into React page');



// background.js datayi alip storagea yazmisti ve amazonDataChanged diye bir event tetiklemisti
// jetbasket.us tabi surekli bu eventi dinliyor, tetiklenirse asil react projemize event gonderecek
chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    if (message.type === 'amazonDataChanged') {
        console.log('Data changed received from background:', message.data);

        //SİNAN:amazonData adlı öğeyi yerel depolamadan al ve alttaki işlemleri yap ve   değeri sonuç olarak döndür.
    chrome.storage.local.get('amazonData', (result) => {
            if (chrome.runtime.lastError) {             //SİNAN: hata oluştu ise loga yaz ve geriye bos döndür.
                console.error("Error retrieving data from storage:", chrome.runtime.lastError);
                return;
            }

            //data okundu
            if (result.amazonData) {    //SİNAN: eğer amazonData dolu ise
                console.log("Data retrieved from storage:", result.amazonData);

                //"amazonDataAvailable" diye bir event olusturuyoruz. Ve local storage den okuduğumuz reselt.amazonDATA yı da dinleyene gönderiyoruz.
                // gercek proje(JETBASKET) de bu eventi dinleyen bir listener var. dinliyor ve gelen değerleri alıp işleyip ekrana basıyor.
                const event = new CustomEvent('amazonDataAvailable', { detail: result.amazonData });
                window.dispatchEvent(event);    //SİNAN: bu eventi , window nesnesi üzerinde yayımla,


                // data event ile birlikte gitti, storagei temizleyebiliriz artik
                chrome.storage.local.remove('amazonData', () => {
                    if (chrome.runtime.lastError) {
                        console.error("Error removing data from storage:", chrome.runtime.lastError);
                    } else {
                        console.log("Data successfully removed from storage.");
                    }
                });


            } else {
                console.log("No data found in storage.");
            }
        });
    }
});



