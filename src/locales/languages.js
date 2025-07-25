import flagus from "../assets/images/flags/us.svg";
import flagspain from "../assets/images/flags/spain.svg";
import flaggermany from "../assets/images/flags/germany.svg";
import flagitaly from "../assets/images/flags/italy.svg";
import flagrussia from "../assets/images/flags/russia.svg";
import flagchina from "../assets/images/flags/china.svg";
import flagfrench from "../assets/images/flags/fr.svg";
import flagarabic from "../assets/images/flags/sa.svg";
import flagturkey from "../assets/images/flags/tr.svg";

const languages = {
  en: {
     label: "English",
     flag: flagus,
   },
  gr: {
   label: "Deutsch",
   flag: flaggermany,
  },
  tr: {
    label: "Türkçe",
    flag: flagturkey,
  },
  sp: {
    label: "Español",
    flag: flagspain,
  },
  it: {
    label: "Italiano",
    flag: flagitaly,
  },
  rs: {
    label: "Pусский",
    flag: flagrussia,
  },

  cn: {
    label: "中文",
    flag: flagchina,
  },
  fr: {
    label: "Français",
    flag: flagfrench,
  },
  ar: {
    label: "العربية",
    flag: flagarabic,
  },

}

export default languages
