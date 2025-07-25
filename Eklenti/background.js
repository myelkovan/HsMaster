
//Eklenti kurulunca bos sayfa gorunuyordu https://www.amazon.com/deals sayfasini gosterdik
chrome.runtime.onInstalled.addListener(async () => {
    let url = "https://www.amazon.com/deals";
    let tab = await chrome.tabs.create({ url });
});

//SİNAN:Chrome un runtime ı kurulduğunda tetiklenen eventi dinle ve aşağıdaki değişkeni oluştur.
//ilk kurulumda storage'a firstUse=true diye bir degisken olustur. 
//Ilk kurulumda jetbasket.us aciksa sayfanizi guncelleyin mesaji cikacak
chrome.runtime.onInstalled.addListener(() => {
    chrome.storage.local.set({ firstUse: true });
});

//SİNAN:  Chrome runtime i mesaj verdiğinde tetiklenen eventi dinle.
// Eğer tetiklenen mesaj  "getData" ise chrome tablarından  aktif tabi buluyor ve ona gonderiyoruz.
// bu istegi content aliyor ve aktif sitede keyler yardimi ile fiyat vs bilgilerini bulup geri donderiyor
// response buraya geri geldiginde gelen datayi storage'a kaydedip jetbasket tabini bulup one sinyal gonderiyoruz

chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.action === "getData") {
        console.log("Background script received the getData request");
         
        // Aktif tabi bul. Aktif tab icin manifest dosyasinda izin verilmis olmasi lazim
        chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
            if (chrome.runtime.lastError) {
                console.log("Error querying tabs:", chrome.runtime.lastError);
                sendResponse({ success: false });
                return;
            }

            //Hata varsa cik
            if (tabs.length === 0) {
                console.log("No active tabs found");
                sendResponse({ success: false });
                return;
            }

            // aktif sayfanin content scriptine getData requesti gonder
            chrome.tabs.sendMessage(tabs[0].id, { action: "getData" }, (response) => {
                if (chrome.runtime.lastError) {
                    console.log("Error sending message to content script:", chrome.runtime.lastError);
                    sendResponse({ success: false });
                    return;
                }

                console.log("Data read from amazon successfully", response);

                //Donen datayi storage'a kaydet
                if (chrome && chrome.storage && chrome.storage.local && response) {
                    console.log("chrome.storage.local is available.");
                    chrome.storage.local.set({ amazonData: response }, () => {
                        if (chrome.runtime.lastError) {
                            console.log("Error saving data to storage:", chrome.runtime.lastError);
                            sendResponse({ success: false });
                        } else {
                            console.log("Data saved to storage.");

                            //Data kaydedildi acik jetbasket tabini bul,sec ve amazonDataChanged diye bir bir event tetikle
                            chrome.tabs.query({ url: "*://*.jetbasket.us/*" }, (tabs) => {
                                if (tabs.length > 0) {
                                    chrome.tabs.sendMessage(tabs[0].id, { type: 'amazonDataChanged' });
                                         chrome.tabs.update(tabs[0].id, { active: true }, () => {
                                           console.log("tab selected");
                                         })
                                } else {
                                    console.log('No tabs found with URL matching *.jetbasket.us/*');
                                }
                                sendResponse({ success: true });
                            });

                        }
                    });
                } else {
                    console.log("chrome.storage.local is not available.");
                    sendResponse({ success: false });
                }
            });
        });
        // Return true to indicate you intend to send a response asynchronously
        return true;
    }


console.log('backgroun a geld,m:' + message.action)

    if (message.action === "openIframeAndFetchContent") {
        chrome.tabs.create({url: message.src, active: false}, (newTab) => {
            chrome.scripting.executeScript(
                {
                    target: {tabId: newTab.id},
                    func: getXPathContent,
                    args: [message.xpath]
                },
                (result) => {
                    if (result && result[0] && result[0].result) {
                        const content = result[0].result;
                        chrome.tabs.remove(newTab.id, () => {
                            sendResponse(content);
                        });
                    }
                }
            );
        });
        return true; // Will respond asynchronously
    }


function getXPathContent(xpath) {
    var result = document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
    return result ? result.textContent : 'Element not found.';
}





    
});





