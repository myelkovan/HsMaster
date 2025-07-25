--------------------------------------------------------------------------------------------------------------
PROJENIN SON HALININ PRODUCTION'A ATILMASI
projenin klasorune girip asagidakini yazin
npm run build

proje derlenecek ve sisteme atilacaktir. Sistem atilma isi ana klasordeki ftp.js dosyasi araciligiyla yapilir.
Ftp ayarlari kullanici adi ve sifresi icin ftp.js dosyasina bakiniz.


--------------------------------------------------------------------------------------------------------------
SERVICES
Veritabanindan gelen datalar services klasoru altindaki servis objeleri ile yapilmaktadir.
tablolara gore objeler gruplanmistir.



--------------------------------------------------------------------------------------------------------------
KULLANICI YETKILERI
User tablosunda permission_level degeri BitWise & ile kontrol edilir
Number(stored_permission_level&4) == 4 seklinde kontrol edilir. Esitlik dogru ise User Urun fiyati girebilir

2 - Demand Confirm edebilir
4 - Urun fiyati girebilir
8 - Kullanici performansi girebilir


--------------------------------------------------------------------------------------------------------------
SESSION BILGILERI
Kullanici login oldugunda kullanicinin user_id vs. degeleri diger sayfalardan kullanilabilsin diye
redux ile browser storage da saklanir. App/UserSlice.js altinda saklanan alanlari gorebilirsiniz.
Bu verileri karismasin diye stored_user_id seklinde isimlendirdik.

import { useSelector, useDispatch } from 'react-redux';
const stored_user_id = useSelector(user_id);



**************************************************************************************************************
YAPILACAKLAR
**************************************************************************************************************



