const renderPenyewa = (d) => {
  document.getElementById("penyewa-nomor-kamar").innerText =
    d.kamar?.nomor_kamar ?? "-";
  document.getElementById("penyewa-fasilitas").innerText =
    d.kamar?.fasilitas ?? "-";
  document.getElementById("penyewa-status-sewa").innerText =
    d.kamar?.status_sewa ?? "-";
  document.getElementById("penyewa-tanggal-masuk").innerText =
    d.kamar?.tanggal_masuk ?? "-";
  document.getElementById("penyewa-tanggal-keluar").innerText =
    d.kamar?.tanggal_keluar ?? "-";

  const nominal = d.tagihan
    ? "Rp " + parseFloat(d.tagihan.total_tagihan).toLocaleString("id-ID")
    : "Tidak ada tagihan";
  document.getElementById("penyewa-tagihan-nominal").innerText = nominal;
  document.getElementById("penyewa-tagihan-jatuh-tempo").innerText =
    d.tagihan?.tanggal_jatuh_tempo ?? "-";
  document.getElementById("penyewa-tagihan-status").innerText =
    d.tagihan?.status_tagihan ?? "-";

  const pending = d.keluhan.find((k) => k.status_keluhan === "pending");
  document.getElementById("penyewa-keluhan-pending").innerText =
    pending?.total ?? "0";
};

let previousData = "";

const getPenyewa = async () => {
  try {
    const cacheKey = "dashboard_penyewa";

    const cached = await loadDB(cacheKey);
    if (cached) {
      console.log("penyewa: loaded from IndexedDB cache");
      renderPenyewa(cached.data);
      previousData = JSON.stringify(cached.data);
    }

    const response = await fetch(
      "http://tugas-mandiri.test/server/api/dashboard/penyewa",
      { method: "GET", credentials: "include" },
    );
    const result = await response.json();
    console.log("penyewa dashboard:", result);
    if (result.status !== "success") return;

    await saveDB(cacheKey, result.data);
    previousData = JSON.stringify(result.data);

    renderPenyewa(result.data);
  } catch (err) {
    console.error("Penyewa dashboard error:", err);
  }
};

const startPolling = () => {
  setInterval(async () => {
    try {
      const response = await fetch(
        "http://tugas-mandiri.test/server/api/dashboard/penyewa",
        {
          method: "GET",
          credentials: "include",
        },
      );

      const result = await response.json();

      if (result.status === "success") {
        const newData = JSON.stringify(result.data);

        if (previousData === newData) {
          return;
        } else {
          previousData = newData;
          renderPenyewa(result.data);
        }
      }
    } catch (error) {
      console.error("Polling error:", error);
    }
  }, 5000);
};

document.addEventListener("DOMContentLoaded", async () => {
  const session = await checkSession("penyewa");

  if (!session) return;

  document.getElementById("user-display-name").innerText = session.email;
  getPenyewa();
});
