import { of_getData, of_get } from "./service_base";

export const getHsCode = async (value, token) => {
  try {
    const encodedValue = encodeURIComponent(value); // Güvenli hale getir

    return of_getData(`/PHP/hscode_finder.php?value=${encodedValue}`, token);
  } catch (error) {
    console.error("API Hatası:", error);
    return { error: "Veri alınamadı", details: error.message };
  }
};

// export function getHsCode(hs_search, token) {
//          return of_getData("/PHP/hscode_finder_AI.php?description=" + hs_search, `Bearer ${token}`)
// }
