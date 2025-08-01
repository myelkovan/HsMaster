import { of_getData, of_get } from "./service_base";

export const getHsCode = async (value, token) => {
  try {
    const encodedValue = encodeURIComponent(value); // Güvenli hale getir

    return of_getData(`/PHP/hscode_finder.php?product_description=${encodedValue}`, token);
  } catch (error) {
    alert(error)
    console.error("API Hatası:", error);
    return { error: "Veri alınamadı", details: error.message };
  }
};

