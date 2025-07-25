// Buraya kendi global functionlarimizi yaziyoruz

     //SİNAN:gönderilen xpath değerine uyan, döküman içindeki ilk eşleşen düğümü getirir. yoksa null  döner.
function getElementByXPath(xpath) {
    return document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
}

function getTextContentByXPath(xpath) {
    var element = getElementByXPath(xpath);              //SİNAN:xpath değerine uyan ilk düğümü getir
    return element ? element.textContent.trim() : '';   //SİNAN:dönen elementin text içeriğini al, boşlukları temizle yok ise bos string dön
}
    //SİNAN:  Bazı sitelerde ürünbilgileri bir script içinde json formatında tututluyor 
    //          tüm ürün bilgileri bu json dan alınabilir
function getElement_AsJSON_ByXPath(xpath) {
    var element = getElementByXPath(xpath);              //SİNAN:xpath değerine uyan ilk düğümü getir
    return element ? element.textContent.trim() : '';   //SİNAN:dönen elementin text içeriğini al, boşlukları temizle yok ise bos string dön
}
//SİNAN: xpath değerine uyan ilk elementi bulur veistenilen  Part değeri (textContent-value-src-srcset v.b) ne ise elementin o özelliğini döndürür
function getSpecialPartByXPath(xpath, Part = "textContent") {
    var element = getElementByXPath(xpath); // XPath değerine uyan ilk düğümü getir
    if (element) {
        // Part parametresine göre uygun değeri döndür
        if (Part in element) {
            return element[Part].trim ? element[Part].trim() : element[Part]; // Eğer trim fonksiyonu varsa, onu uygula
        } else {
            return ''; // Geçersiz bir Part değeri verilirse boş string döner
        }
    }
    return ''; // Element bulunamazsa boş string döner
}

function getValueById(id) {
    var element = document.getElementById(id);          //SİNAN:id değerine  sahip elemnti bulur
    return element ? element.value.trim() : '';   //SİNAN:dönen elementin text içeriğini al, boşlukları temizle yok ise bos string dön
}

// function getASINById(id) {
//     var element = document.getElementById(id);          //SİNAN:id değerine  sahip elemnti bulur
//     return element.value ? element.value.trim() : '';   //SİNAN:dönen elementin value içeriğini al, boşlukları temizle yok ise bos string dön
// }

function getImageSourceById(id) {
    var imageElement = document.getElementById(id);     //SİNAN:id değerine  sahip resim elemnti bulur
    return imageElement ? imageElement.src : '';        //SİNAN:dönen elementin src(kaynak,resim url) değerini al,  yok ise bos string dön
}

function getImageByXpath(selector) {                     
    var element = getElementByXPath(selector);          //SİNAN:xpath değerine uyan ilk resim elemnti bulur
   // return element ? element.src : '';                  //SİNAN:dönen elementin src(kaynak,resim url) değerini al,  yok ise bos string döner
       return element ? (element.src || element.srcset || element.dataset.imageHrSrc || '') : '';    //SİNAN  element yoksa boş, var ve src si de varsa onu, src yokda srcset varsa onu ikiside yoksa boş dön.
}
function getImage_srcSET_ByXpath(selector) {                     
    var element = getElementByXPath(selector);          //SİNAN:xpath değerine uyan ilk resim elemnti bulur
    //return element ? element.srcset : '';                  //SİNAN:dönen elementin src(kaynak,resim url) değerini al,  yok ise bos string döner
    return element ? (element.src || element.srcset || '') : '';    //SİNAN  element yoksa boş, var ve src si de varsa onu, src yokda srcset varsa onu ikiside yoksa boş dön.
}

function getValueBySelector(selector) {
    const element = document.querySelector(selector);   //SİNAN:selector (css selector) değerine uyan ilk  elementi bul.  örnek kullanım: ID aramak için (#myId),Class aramak için (.myClass), Div gibi bir Tag aramak için (div), Attribute aratmak için [hreflang="en"] ,Pseudo-classes (:hover, :active, etc.),  Combinators (>, +, ~, etc.)
     return element ? element.value.trim() : '';  //SİNAN:dönen elementin text içeriğini al, boşlukları temizle yok ise bos string dön
}

    //SİNAN: Verilen string bir dizeden sayısal bir değeri (tam sayı veya ondalıklı) çıkarmak
function extractNumber(str){
    const matched = str.match(/[\d,]+/);    //SİNAN:Dizede rakamlar veya virgül içeren ilk eşleşmeyi bulur.
    if (matched) {
      // Replace commas with dots if needed
      const num = matched[0].replace(',', '.'); //SİNAN:Virgülü noktaya çevir
      return num;
    }
    return "";
  };
 
  //SİNAN: bir metindeki İlk sayısal değerden öncesini temizlemek için kullanın
  function removeTextBeforeFirstNumber(input) {
       const regex = /\d/;
    const match = input.match(regex);
    // İlk sayısal değerin indeksini bulun
    const index = match ? match.index : -1;
    // İlk sayısal değerden önceki kısmı silin
    const result = index !== -1 ? input.substring(index) : input;
    return result;
}



 //SİNAN: (uzunluk, genişlik, yükseklik "10x20x30"  veya "10.5 x 20.5 x 30" ) gibi Boyutları içeren bir dizeden bu değerleri çıkarmak için kullanılır.
//SİNAN:YAPILACAK  5 1/2" h   şeklinde boyut buluyor ama aşağıdaki kodlar rakamları alamıyor  örn. ASIN:B00NQQTZCO
//SİNAN:YAPILACAK  8.5"L x 8.5"W x 1.35"Th  şeklinde boyut buluyor ama aşağıdaki kodlar rakamları alamıyor  örn. ASIN:B0C6F6VYW6 
//SİNAN:YAPILACAK  5.43"w x 11.02"h   şeklinde boyut buluyor  ama aşağıdaki kodlar rakamları alamıyor  örn. ASIN: B0CJZMP7L1 
function extractDimensions(dimensionString) {
    //console.log('EXTRACTdimentions TAYIM:',dimensionString)
   //SİNAN:   REGEX AÇIKLAMASI
                // (\d+(\.\d+)?) açıklaması--> (\d+)--> (0-9) arasında Bir veya daha fazla rakamı olacak .  (\.\d+)?--> \. nokta ile başlayıp \d+ bir yada daha fazla rakam ile devam eden demektir. ? önündeki parantezli kısmı opsiyonel yapar. Yani ondalık kısmı olmayabilir demek şstiyoruz.
                // \s*           açıklaması--> \s--> Boşluk karakterlerini (boşluk, tab, yeni satır vb.) temsil eder.  * --> Önceki karakterin (bu durumda boşluk,tab vb.) sıfır veya daha fazla kez tekrar edebileceğini belirtir. yani sayıların etrafında boşluk olabilir demek istiyor.
                // ["']?[xX]     açıklaması--> ["']?Opsiyonel olarak bir tek veya çift tırnak.  [xX]--> 'x' veya 'X' harfini eşleştirir
                // \s*(\d+(\.\d+)?)\ açıklaması--> boşluk rakam noktaRakam(opsiyonel)
                // \s*["']?[xX]      açıklaması--> boşluk tırnak(opsiyonel)  x veya X
                 // \s*(\d+(\.\d+)?)\ açıklaması--> boşluk rakam noktaRakam(opsiyonel)
    const regex = /(\d+(\.\d+)?)\s*["']?[xX]\s*(\d+(\.\d+)?)\s*["']?[xX]\s*(\d+(\.\d+)?)/;  //ORJİNAL
    //const regex = /(\d+(?:\s+\d+\/\d+)?)\s*["']?[xX]?\s*(\d+(?:\s+\d+\/\d+)?)\s*["']?[xX]?\s*(\d+(?:\s+\d+\/\d+)?)/;

    const match = dimensionString.match(regex);
   
    // Initialize variables
    let length = "";
    let width = "";
    let height = "";

    //SİNAN:YAPILACAK  aşağıda  BOYUTLARI ayırma işlemi yapılıyor. ve sıra olarak genel kabul olan ( L-W-H ) şeklinde ilk değer L ikinci W üçüncü H şeklinde direk yerlerştiriyor.
    //.....DEVAM     ancak   12.1"W x 11.4"H x 24.6"L  şeklinde sıraya uymayan ancak yanında ki harf ile türünü belirten formatlar oluyor 
    //.....DEVAM-2     bu tarz sıralamaları da yanında ki harfe göre grupla ve aşağıda ki eşleşmeyi ona göre yap.
    if (match) {        //SİNAN: örneğin boyutlar 3.60 X 2.10 X  3.40  ise yukarıda ki regex ile 6 gruba ayrıldı. her bir parantez Arası bir grup
        length = match[1];  //SİNAN: match[1]= 3.60  ama  match[2]=.60    ondalık kısım.
        width = match[3];   //SİNAN: match[3]= 2.10  ama  match[4]=.10    ondalık kısım.
        height = match[5];  //SİNAN: match[5]= 3.40  ama  match[6]=.40    ondalık kısım.
    }
    else{       //SİNAN   8.5"l x 8.5"w x 1.35"th   şeklinde bir değer var 
            console.log('BOYUTLAR BULUNAMADI İKİNCİ REGEX E BAKIYOM',dimensionString);
            const regex2 =      /(\d+(\.\d+)?)\s*["']?[\w*]?\s*[xX]\s*(\d+(\.\d+)?)\s*["']?[\w*]?\s*[xX]\s*(\d+(\.\d+)?)(?:\s*["']?[\w*]?)?/;  
            const match2 = dimensionString.match(regex2);
            if (match2) {        //SİNAN: örneğin boyutlar 3.60 X 2.10 X  3.40  ise yukarıda ki regex ile 6 gruba ayrıldı. her bir parantez Arası bir grup
                length = match2[1];  //SİNAN: match[1]= 3.60  ama  match[2]=.60    ondalık kısım.
                width = match2[3];   //SİNAN: match[3]= 2.10  ama  match[4]=.10    ondalık kısım.
                height = match2[5];  //SİNAN: match[5]= 3.40  ama  match[6]=.40    ondalık kısım.
            }else{      // 5.43"w x 11.02"h şeklinde bir değer var 
                console.log('2.regex te de BOYUTLAR BULUNAMADI',dimensionString);
                const normalizedString = dimensionString.replace(/X/g, 'x') + 'x'; // Büyük X'leri küçük x'e çevir ve üçüncü değer olmama ihtimalini ortadan kaldır.
                const parts = normalizedString.split('x');
                length = parts[0].replace(/[^0-9.,]/g, ' ').trim().split(' ')[0]; //SİNAN: rakam ve . ile , dışındakileri sil
                width = parts[1].replace(/[^0-9.,]/g, ' ').trim().split(' ')[0];   //SİNAN:  sondaki split boyut alanında iki satır olanlar var ASIN:B0CJM1GNFQ
                if (parts.length > 2) {     // gelen string de sadece bir adet x olabilir eğer öyle ise üçüncü bölüm yok   
                    height =parts[2].replace(/[^0-9.,]/g, ' ').trim().split(' ')[0];  
                }
                if (length==="" || width===""){       //  boyutlar hala boş ise   örnek boyut text-->: measurements: 14.93 height/inches, 16.9 width/inches, 12.97 depth/inches
                    console.log('3. türde de  BOYUTLAR BULUNAMADI',dimensionString);
                    // Boyutları yakalamak için düzenli ifade
                      const regex3 = /(\d+(\.\d+)?)\s*(height|width|depth)\/(inches|cm|mm)/g;
                      let match3;
                    while ((match3 = regex3.exec(dimensionString)) !== null) {
                        const value = match3[1]; // Boyut değeri
                        const type = match3[3]; // Boyut türü (height, width, depth)

                        // Türüne göre boyutları ata
                        if (type === "height") {
                            height = value;
                        } else if (type === "width") {
                            width = value;
                        } else if (type === "depth") {
                            length = value; // depth'i length olarak alıyoruz
                        }
                    }


                }
            }
    }

         if (length === "" || width === "") {    //4. YÖNTEM   burası sonradan diğer sitelerin hesaplamalarından alındı silinebilir.
        console.log('BOYUTLAR BULUNAMADI 5. YÖNTEM E BAKIYOM- DİREK SAYILARI ÇEK', dimensionString);
               const numbers = dimensionString.match(/(\d+(\.\d+)?)/g);
        if (numbers ) {
            //return `${numbers[0]} x ${numbers[1]} x ${numbers[2]}`;
            if ( numbers.length > 2){
            length = `${numbers[0]}` || length ;
            width = `${numbers[1]}` || width; 
            height = `${numbers[2]}` || height; 
            }
            if ( numbers.length === 2){
             length= `${numbers[0]}` || length; 
            width = `${numbers[1]}` || width; 
            height =  height;
            }
           
        }
    }
           

    return { length, width, height };
}

function Yontem_1(dimensionString){
     console.log('BOYUTLAR BULUNAMADI Yontem_1 BAKIYOM', dimensionString); 
        // Yeni regex: Boyutları ve türlerini yakalamak için
        const regex = /(?:(height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(inches|cm|mm|"|”)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?)?\s*[xX]\s*(?:(height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(inches|cm|mm|"|”)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?)?\s*[xX]\s*(?:(height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(inches|cm|mm|"|”)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?)/i;
        const match = dimensionString.match(regex);

        if (match) {        //1. YÖNTEM
            // Türlere göre boyutları ata
            const dimensions = {};
            for (let i = 1; i < match.length; i += 5) {
                const value = match[i + 1];
                const unit = match[i + 3] || 'N/A';
                const type = match[i] || match[i + 4] || '';

                if (type.toLowerCase() === "height" || type.toLowerCase() === "h") {
                    dimensions.height = parseFloat(value);
                    dimensions.heightUnit = unit;
                } else if (type.toLowerCase() === "width" || type.toLowerCase() === "w") {
                    dimensions.width = parseFloat(value);
                    dimensions.widthUnit = unit;
                } else if (type.toLowerCase() === "depth" || type.toLowerCase() === "d" || type.toLowerCase() === "length" || type.toLowerCase() === "l") {
                    dimensions.length = parseFloat(value);
                    dimensions.lengthUnit = unit;
                } else {
                    // Boyut içeriğinde türü ifade eden bir harf veya kelime yoksa direkt sayıları ata ve döngüden çık
                    dimensions.length = parseFloat(match[2]);
                    dimensions.lengthUnit = match[4] || '';
                    dimensions.width = parseFloat(match[7]);
                    dimensions.widthUnit = match[9] || '';
                    dimensions.height = parseFloat(match[12]);
                    dimensions.heightUnit = match[14] || '';
                    break;
                }           }
           return dimensions;
        } 
}
function Yontem_2(dimensionString){
        console.log('BOYUTLAR BULUNAMADI Yontem_2 BAK',dimensionString);
        const regex = /(?:height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?\s*[xX]\s*(?:height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?\s*[xX]\s*(?:height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)(?:\s*(?:inches|cm|mm)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?)?/i;
        const match = dimensionString.match(regex);

        if (match) {        //1. YÖNTEM
            // Türlere göre boyutları ata
            const dimensions = {};
            for (let i = 1; i < match.length; i += 3) {
                const value = match[i];
                const type = match[i + 2] ? match[i + 2].toLowerCase() : match[i + 1] ? match[i + 1].toLowerCase() : '';
                if (type === "height" || type === "h") {
                    dimensions.height = value;
                } else if (type === "width" || type === "w") {
                    dimensions.width = value;
                } else if (type === "depth" || type === "d" || type === "length" || type === "l") {
                    dimensions.length = value;
                } else{     //boyut içeriğinde türü ifade eden bir harf veya kelime yoksa direk sayıları ata ve döngüden çık .  
                    dimensions.length = match[1];
                    dimensions.width = match[4];
                    dimensions.height = match[7];
                    break;
                }
            }
           return dimensions;
        } 
}
function Yontem_3(dimensionString){
        console.log('BOYUTLAR BULUNAMADI Yontem_3 BAK',dimensionString);
        const regex2 = /(\d+(\.\d+)?)\s*["']?[\w*]?\s*[xX]\s*(\d+(\.\d+)?)\s*["']?[\w*]?\s*[xX]\s*(\d+(\.\d+)?)(?:\s*["']?[\w*]?)?/;
        const match2 = dimensionString.match(regex2);
        if (match2) {
             const dimensions = {};
            dimensions.length = match2[1];
            dimensions.width = match2[3];
            dimensions.height = match2[5];
        return dimensions;
    }
        
}
function Yontem_4(dimensionString){
    console.log('BOYUTLAR BULUNAMADI Yontem_4 BAK',dimensionString);
                //const combinedRegex = /(?:height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?\s*[xX]?\s*(?:height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?\s*[xX]?\s*(?:height|width|depth|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm)?\s*\(?\s*(height|width|depth|h|w|d|l)?\)?(?:\s*\/\s*(\d+(\.\d+)?)\s*[xX]\s*(\d+(\.\d+)?)\s*[xX]\s*(\d+(\.\d+)?))?/i;
        //const combinedRegex = /(?:height|width|depth|length|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm|(?:"|'))?\s*\(?\s*(height|width|depth|length|h|w|d|l)?\)?\s*[xX]?\s*(?:height|width|depth|length|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm|(?:"|'))?\s*\(?\s*(height|width|depth|length|h|w|d|l)?\)?\s*[xX]?\s*(?:height|width|depth|length|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm|(?:"|'))?\s*\(?\s*(height|width|depth|length|h|w|d|l)?\)?(?:\s*\/\s*(\d+(\.\d+)?)\s*[xX]\s*(\d+(\.\d+)?)\s*[xX]\s*(\d+(\.\d+)?))?/i;
        const combinedRegex = /(?:height|width|depth|length|h|w|d|l)?\s*(\d+(\.\d+)?)\s*(?:inches|cm|mm|[”"'])?\s*\(?\s*(height|width|depth|length|h|w|d|l)?\)?\s*[xX]?\s*(\d+(\.\d+)?)?\s*(?:inches|cm|mm|[”"'])?\s*\(?\s*(height|width|depth|length|h|w|d|l)?\)?\s*[xX]?\s*(\d+(\.\d+)?)?\s*(?:inches|cm|mm|[”"'])?\s*\(?\s*(height|width|depth|length|h|w|d|l)?\)?/i;
        const combinedmatch = dimensionString.match(combinedRegex);

        if (combinedmatch) {
            const combineddimensions = {};
            for (let i = 1; i < combinedmatch.length; i += 3) {
                const value = combinedmatch[i];
                const type = combinedmatch[i + 2] ? combinedmatch[i + 2].toLowerCase() : combinedmatch[i + 1] ? combinedmatch[i + 1].toLowerCase() : '';
                if (type === "height" || type === "h") {
                    combineddimensions.height = value;
                } else if (type === "width" || type === "w") {
                    combineddimensions.width = value;
                } else if (type === "depth" || type === "d" || type === "length" || type === "l") {
                    combineddimensions.length = value;
                }
            }
            return combineddimensions;
        }
}
function Yontem_5(dimensionString){
        console.log('BOYUTLAR BULUNAMADI Yontem_5 BAK',dimensionString);
        const regex3 = /(\d+(\.\d+)?)\s*(height|width|depth|h|w|d|l)\/?(inches|cm|mm)?/gi;
        let match3;
        while ((match3 = regex3.exec(dimensionString)) !== null) {
            const dimensions = {};
            const value = match3[1];
            const type = match3[3].toLowerCase();
            if (type === "height" || type === "h") {
                dimensions.height = value;
            } else if (type === "width" || type === "w") {
                dimensions.width = value;
            } else if (type === "depth" || type === "d" || type === "length" || type === "l") {
                dimensions.length = value;
            }
            return dimensions;
        }
        
}
function Yontem_6(dimensionString){
    console.log('BOYUTLAR BULUNAMADI Yontem_6 BAK',dimensionString)
        const extraDimensionsRegex4 = /(\d+(\.\d+)?)\s*["']?\s*[xX]\s*(\d+(\.\d+)?)\s*["']?\s*[xX]\s*(\d+(\.\d+)?)(?:\s*\/\s*(\d+(\.\d+)?)\s*[xX]\s*(\d+(\.\d+)?)\s*[xX]\s*(\d+(\.\d+)?))?/;  
        const extraMatch4 = dimensionString.match(extraDimensionsRegex4);
        if (extraMatch4) {
            const extraDimensions = {};
            extraDimensions.length = extraMatch4[1] || length; 
            extraDimensions.width = extraMatch4[3] || width; 
            extraDimensions.height = extraMatch4[5] || height;
        return extractDimensions;
    }
        
}
function Yontem_7(dimensionString){
            console.log('BOYUTLAR BULUNAMADI Yontem_7 BAK',dimensionString)
            const normalizedString = dimensionString.replace(/X/g, 'x') + 'x';
            const parts = normalizedString.split('x');
            if (parts && parts.length >= 1){
                const dimensions = {};
                dimensions.length = parts[0].replace(/[^0-9.,]/g, ' ').trim().split(' ')[0];
                dimensions.width = parts[1].replace(/[^0-9.,]/g, ' ').trim().split(' ')[0];
                dimensions.height = "";
                if (parts.length > 2) {
                    dimensions.height = parts[2].replace(/[^0-9.,]/g, ' ').trim().split(' ')[0];
                }
                return dimensions;
            }
           
}
    
function extractDimensions_WithType(dimensionString, dimensionType = "all") {
     // Initialize variables
        let length = "";    let width = ""; let height = ""; 
        let unit=""; 
        var dimensions = {};
    try {
        //önce gelen sting de sayı varsa onları al yoksa hiç uğraşma
        const numbers = dimensionString.match(/(\d+(\.\d+)?)/g);

        if (numbers) {
            if (length === "" || width === "") {       //1.YÖNTEM     
                dimensions= Yontem_1(dimensionString)
                if (dimensions){
                    height = dimensions.height || "";
                    width = dimensions.width || "";
                    length = dimensions.length || "";
                    unit= dimensions.lengthUnit !== "" ? dimensions.lengthUnit : dimensions.heightUnit; 
                }
            } 
            
            if (length === "" || width === "") {       //2.YÖNTEM     
                dimensions= Yontem_2(dimensionString)
                if (dimensions){
                    height = dimensions.height || "";
                    width = dimensions.width || "";
                    length = dimensions.length || "";
                }
            }

            if (length === "" || width === "") {       //3.YÖNTEM
                dimensions= Yontem_3(dimensionString)
                if (dimensions){
                    height = dimensions.height || "";
                    width = dimensions.width || "";
                    length = dimensions.length || "";
                }
            }
            if (length === "" || width === "") {    //4. YÖNTEM
                dimensions= Yontem_4(dimensionString)
                if (dimensions){
                    height = dimensions.height || "";
                    width = dimensions.width || "";
                    length = dimensions.length || "";
                }
            }  
            
            if (length === "" || width === "") {    //5. YÖNTEM
                dimensions= Yontem_5(dimensionString)
                if (dimensions){
                    height = dimensions.height || "";
                    width = dimensions.width || "";
                    length = dimensions.length || "";
                }
            }

            if (length === "" || width === "") {    //6.YÖNTEM
                dimensions= Yontem_6(dimensionString)
                if (dimensions){
                    height = dimensions.height || "";
                    width = dimensions.width || "";
                    length = dimensions.length || "";
                }
            }

            if (length === "" || width === "") {    //7.YÖNTEM
                dimensions= Yontem_7(dimensionString)
                if (dimensions){
                    height = dimensions.height || "";
                    width = dimensions.width || "";
                    length = dimensions.length || "";
                }
            }

            if (length === "" || width === "") {    //8. YÖNTEM
                console.log('BOYUTLAR BULUNAMADI 8. YÖNTEM E BAKIYOM- DİREK SAYILARI ÇEK', dimensionString);
                if (numbers ) {
                    //return `${numbers[0]} x ${numbers[1]} x ${numbers[2]}`;
                    if ( numbers.length > 2){
                        length = `${numbers[0]}` || length ;
                        width = `${numbers[1]}` || width; 
                        height = `${numbers[2]}` || height; 
                    }
                    if ( numbers.length === 2){
                        length= `${numbers[0]}` || length; 
                        width = `${numbers[1]}` || width; 
                        height=  height;
                    }
                
                }
            }

            const ilkValue = length;  
            switch (dimensionType.toLowerCase()){     // eğer all  dan farklı ve sadece bir boyut için gelmişisek onu set et diğerlerini boşalt
                            
                            case "length":  
                                length = ilkValue;  
                                height = "";    width = "";
                                break
                            case "width":   
                                width = ilkValue;
                                length = "";    height = "";
                                break;
                            case "height": 
                                height = ilkValue;
                                length = "";    width = "";
                                break;
                    }
            }
        } catch (error) {
            console.log(" HATA OLUŞTU-->extractDimensions_WithType :",error);
        }
    return { length, width, height,unit };
}

function getDimensionsWithRegex_2(_dimensionString){
        const regex2 = /(\d+(\.\d+)?)\s*["']?[\w*]?\s*[xX]\s*(\d+(\.\d+)?)\s*["']?[\w*]?\s*[xX]\s*(\d+(\.\d+)?)(?:\s*["']?[\w*]?)?/;
        const match2 = _dimensionString.match(regex2);
       
}

function insertXIfMissing(input) {
    // boyut text i içinde x veya X işareti olup olmadığını kontrol et
    if (!input.includes('x') && !input.includes('X')) {
        // Sayıları ayırmak için regex kullan
        const numbers = input.match(/(\d+(\.\d+)?)/g);
        if (numbers && numbers.length >= 2) {
            // Sayılar arasına " x " işareti ekle
            return `${numbers[0]} x ${numbers[1]} x ${numbers[2]}`;
        }
    }
    // Eğer x veya X işareti varsa veya yeterli sayı yoksa orijinal değeri döndür
    return input;
}


function isValidASIN(asin) {
  // Check if the ASIN is exactly 10 characters long
  if (asin.length !== 10) {
    return false;
  }

  //SİNAN: en az karakter uzunluğunda olacak ve Büyük harf ve rakam içerecek .Check if the ASIN contains only uppercase letters and digits
  const asinPattern = /^[A-Z0-9]{10}$/;
  return asinPattern.test(asin);
}

function getPounds(input, keywords) {
    //SİNAN: ondalık sayılara izin veren ve yukarıdan gelen keywords de geçen  kelimelerden herhangi biriyle 
    // eşleşecek bir regex ifade oluşturuyoruz,
    //SİNAN:  REGEX AÇIKLAMASI
    // \\b -->parça değil tam kelime eşleşmesini sağlar.
    // \\d+ --> Bir veya daha fazla rakamı eşleştirir
    // (?:...) --> Eşleştirilen kısmı yakalamayan (non-capturing) bir grup. yani uygun olan yapıyı kontrol et ama sonuç arrayinde virgül kısmı için ayrı ca bir dizi elemanı oluşturma 
    // \\.\\d+)? --> Bir ondalık noktası ve ardından bir veya daha fazla rakam. ?  bu grup opsiyoneldir
     // \\s*: Sıfır veya daha fazla boşluk karakteri.
     // (${keywords.join('|')}) --> yukarıdan gelen keywors dizinindeki her elemanı al aralarına  | bu işareti koy.
     // \\b -->parça değil  tam kelime eşleşmesini sağlar. kelimenin son sınırı
     // 'i'  büyük küçük harf duyarsız ol.
    input=input.replace("(lbs.)","lbs.")    //  ()  işaretlerinden dolayı regex doğru çalışmıyor.
    
     const keywordRegex = new RegExp(`\\b(\\d+(?:\\.\\d+)?)\\s*(${keywords.join('|')})\\b`, 'i');
    console.log('getpoundsDAYIM: ',input)
    // Search for the pattern in the input string
    const match = input.match(keywordRegex);

    if (match) {
        // Return the first capturing group which is the quantity
        return match[1];
    }

    return null; // Return null if no match is found
}

function extractASIN(asinString) {
     //SİNAN: : işaretinin konum index ini  bul
    const _Index = asinString.indexOf(':');
    if (_Index !== -1) {
        //SİNAN: : işaretinden sonraki herşeyi al ve trimle
        return asinString.substring(_Index + 1).trim();
    }
    else{   //SİNAN: : işareti yok ise ve gelen  değeri direkt döndür
       
         return asinString ;
    }
    return ""; // Return an empty string if no valid asin found
}

function extractPrice(priceString) {
    // "Price:
    //    592.86 TL+"   BU ŞEKİLDE Kİ BİR FİYATTA problem oluyor.
     //SİNAN: Tüm virgülleri kaldır
        priceString = priceString.replace(/,/g, ''); // Remove all commas
        //SİNAN: Dolar işaretinin konum index ini  bul
    const dollarIndex = priceString.indexOf('$');
       //SİNAN:YAPILACAK       buraya eğer fiyat içinde $ yok ise hata alıyor eğer dolar yoksa ve rakam içeriyorsa direk döndür
    if (dollarIndex !== -1) {
        //SİNAN: Dolar işaretinden sonraki herşeyi al ve trimle
        let price = priceString.substring(dollarIndex + 1).trim();
       //SİNAN: ondalık sayıya döndür.
        var numericPrice = parseFloat(price);
    }
    else{   //SİNAN: Dolar işareti yok ise ve gelen değer numerik ise o değeri direkt döndür
       
         var numericPrice = parseFloat(priceString);
    }
        if (!isNaN(numericPrice)) {
            return numericPrice.toFixed(2); //SİNAN: ondalık hanesini iki rakam olarak tespit et ve değeri döndür.
        }
        else{
            //  Total price is: 37 dollars and 99 cents. gibi ifadeler olabiliyor.
           // const regex =  /(\d+)(?:\s*\.?\s*(\d+))?\s*(?:dollars?|cents?)/i;
           const regex = /(\d+)(?:\s*\.?\s*(\d+))?\s*(?:dollars?|cents?)\s*(?:and\s*(\d+)\s*cents?)?/i;
        const match = priceString.match(regex);
            if (match) {
                const wholeNumber = match[1]; // Tamsayı kısmı
                const decimalNumber = match[3] ? match[3] : '00'; // Ondalık kısmı, yoksa '00' olarak ayarla
                numericPrice =  wholeNumber + '.' + decimalNumber // Birleştir
                return parseFloat(numericPrice).toFixed(2);     
            }
               

        }
    return ""; // Return an empty string if no valid price found
}

//SİNAN:  Yukarıdaki  fonksiyonların window nesnesine atanması,
//SİNAN: bunların global kapsamda erişilebilir hale gelmesini sağlar
window.getTextContentByXPath = getTextContentByXPath;
window.getElementByXPath = getElementByXPath;
window.getElement_AsJSON_ByXPath = getElement_AsJSON_ByXPath;
window.getSpecialPartByXPath = getSpecialPartByXPath;
window.getValueById = getValueById;
window.getImageSourceById = getImageSourceById;
window.getValueBySelector = getValueBySelector;
window.extractNumber = extractNumber;
window.extractDimensions = extractDimensions;
window.extractDimensions_WithType = extractDimensions_WithType;
window.isValidASIN = isValidASIN;
window.getPounds = getPounds;
window.getImageByXpath = getImageByXpath;
window.getImage_srcSET_ByXpath = getImage_srcSET_ByXpath;
window.extractPrice = extractPrice;