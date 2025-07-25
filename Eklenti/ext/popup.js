
// popup.html sayfasinda send tusununa basilirsa background.js dosyasina getData requesti gonder
//SİNAN:"send" ID'sine sahip butonun tıklama olayı bir dinleyici ekle. 
document.getElementById("send").addEventListener("click", () => {

    //SİNAN: Butona birden fazla basılmaması için disabled yap.
    document.getElementById("send").disabled = true;

    //SİNAN: arka plan scriptine (background.js) action: "getData" şeklinde bir mesaj gönder. 
   chrome.runtime.sendMessage({ action: "getData" }, function (response) {      //SİNAN: background.js den gelecek cevabı(response) al ve aşağıda işle
        if (chrome.runtime.lastError) {     //SİNAN: mesaj gönderiminde bir hata olup olmadığını kontrol et
            console.error("Error sending message:", chrome.runtime.lastError);  //SİNAN:hata dönerse hatayı loga yaz
        } else {
            console.log('Response from background script:', response);
        }

       

        //getData requestinden sonra eklenti kapatilacak fade efecti ile kapatiyoruz
        //SİNAN: sayfanın opasitesini(saydamlığını) yavaş yavaş azaltarak bir fade-out (solma) efekti oluştur.
        let opacity = 1; // Initial opacity
        const fadeDuration = 500; // Duration of the fade in milliseconds
        const interval = 50; // Interval between opacity changes in milliseconds
        const decrement = interval / fadeDuration; // Amount to decrease opacity each interval

        // Create an interval that reduces opacity
        const fadeOutInterval = setInterval(() => {     //SİNAN: interval oluştur.
            opacity -= decrement;
            if (opacity <= 0) {
                clearInterval(fadeOutInterval);     //SİNAN: tamamen soldu intervalı durdur.
                window.close(); // Close the window when fading is complete
            } else {
                document.body.style.opacity = opacity;      //SİNAN: tamamen solmadı ise sayfanın saydamlığını düşürmeye devam.
            }
        }, interval)

    });
});



// popup.html sayfasinda send tusunun sola saga hareket etmesini sagliyor
//SİNAN:DOMContentLoaded Olayı için Dinleyici EKLE:
//SİNAN:Bu event(DOMContentLoaded) triger edildiğinde yani ilgili HTML sayfası tamamen yüklendiğinde 
//SİNAN: ve ayrıştırıldığında aşağıdaki kodları çalıştır.


document.addEventListener('DOMContentLoaded', () => {

    //SİNAN:"send" ID'sine sahip butonu al 
   const sendButton = document.getElementById('send');

    if (sendButton) {   //SİNAN: Eğer buton mevcutsa, wobble (sarsıntı) efekti uygulanacaktır.
        // saga sola hareket parametreleri //SİNAN: Wobble (sarsıntı) effecti için gerekli  parametereler
        const wobbleDistance = 10; //SİNAN: Butonun sağa ve sola maksimum hareket edeceği mesafe piksel olarak
        const wobbleDuration = 1000; //SİNAN: Tam bir wobble(sarsıntı) döngüsünün süresi 
        const wobbleInterval = 10; //SİNAN: Pozisyon güncellemeleri arasındaki zaman aralığı 

        let position = 0; //SİNAN: Butonun mevcut yatay pozisyonu
        let goingRight = true; //SİNAN: buton sağa doğru sarsılmaya başlayacak 

        function wobble() {
            const step = (wobbleDistance * 2) / (wobbleDuration / wobbleInterval); //SİNAN: Her adımda hareket edilecek mesafeyi hesapla

            if (goingRight) {
                position += step;   //SİNAN: belirlenen adım uzunluğunda sağa git
                if (position >= wobbleDistance) {   //SİNAN: yeni pozisyon, sınırda ise 
                    position = wobbleDistance;
                    goingRight = false;             //SİNAN: bir sonraki hareket yönünü sola çevir 
                }
            } else {
                position -= step;   //SİNAN: belirlenen adım uzunluğunda sola git
                if (position <= -wobbleDistance) {  //SİNAN: yeni pozisyon sınırda ise 
                    position = -wobbleDistance;
                    goingRight = true;              //SİNAN: bir sonraki hareket yönünü sağa çevir 
                }
            }
            sendButton.style.transform = `translateX(${position}px)`;   //SİNAN: butonu yeni pozisyonuna taşı
        }

        setInterval(wobble, wobbleInterval);    //SİNAN: sarsıntı efektini oluşturan wobble fonksiyonunu belirlenen süre içinde tekrar tekrar çağır.
    } else {
        console.error('Button with ID "sendButton" not found.');
    }
});

       

