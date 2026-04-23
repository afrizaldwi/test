const renderAdmin = (d) => {
  const kamarData = { total: 0, terisi: 0, tersedia: 0, perbaikan: 0 };
  d.kamar.forEach((row) => {
    kamarData[row.status_kamar] = parseInt(row.total);
    kamarData.total += parseInt(row.total);
  });

  const keluhanPending = parseInt(d.keluhan[0]?.count ?? 0);
  const pengeluaran = parseFloat(d.pengeluaran[0]?.sum ?? 0);
  const tamuHariIni = parseInt(d.tamu[0]?.count ?? 0);

  document.getElementById("stat-total-kamar").innerText = kamarData.total;
  document.getElementById("stat-kamar-terisi").innerText = kamarData.terisi;
  document.getElementById("stat-kamar-tersedia").innerText = kamarData.tersedia;
  document.getElementById("stat-keluhan").innerText = keluhanPending;
  document.getElementById("stat-pengeluaran").innerText =
    "Rp " + pengeluaran.toLocaleString("id-ID");
  document.getElementById("stat-tamu").innerText = tamuHariIni;
};

const exportCSV = async () => {
  try {
    const totalKamar = document.getElementById("stat-total-kamar").innerText;
    const terisi = document.getElementById("stat-kamar-terisi").innerText;
    const tersedia = document.getElementById("stat-kamar-tersedia").innerText;
    const keluhan = document.getElementById("stat-keluhan").innerText;
    const pengeluaran = document.getElementById("stat-pengeluaran").innerText;
    const tamu = document.getElementById("stat-tamu").innerText;

    const rows = [
      ["=== RINGKASAN KAMAR ==="],
      ["Total", "Terisi", "Tersedia"],
      [totalKamar, terisi, tersedia],
      [],
      ["=== TAGIHAN ==="],
      ["Keluhan Pending", "Pengeluaran Bulan Ini", "Tamu Hari Ini"],
      [keluhan, pengeluaran, tamu],
      [],
      ["=== INFO ==="],
      ["Diekspor pada", new Date().toLocaleString("id-ID")],
    ];

    const csv = rows.map((r) => r.join(",")).join("\n");
    const blob = new Blob([csv], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "dashboard_" + new Date().toISOString().slice(0, 10) + ".csv";
    a.click();
    URL.revokeObjectURL(url);
  } catch (err) {
    console.error("Export CSV error:", err);
  }
};

const showAdminPage = (pageId) => {
  document
    .querySelectorAll(".admin-page")
    .forEach((p) => p.classList.add("hidden"));
  document.getElementById("admin-page-" + pageId)?.classList.remove("hidden");
  localStorage.setItem("lastAdminPage", pageId);
};

let previousData = "";

const getAdmin = async () => {
  try {
    const cached = await loadDB("dashboard_admin");
    if (cached) {
      console.log("admin: loaded from IndexedDB cache");
      renderAdmin(cached.data);
      previousData = JSON.stringify(cached.data);
    }

    const response = await fetch(
      "http://tugas-mandiri.test/server/api/dashboard/admin",
      {
        method: "GET",
        credentials: "include",
      },
    );
    const result = await response.json();
    if (result.status !== "success") return;

    await saveDB("dashboard_admin", result.data);
    console.log("admin: saved to IndexedDB cache");

    renderAdmin(result.data);
    previousData = JSON.stringify(result.data);
  } catch (error) {
    console.error("Dashboard fetch error:", error);
  }
};

const startPolling = () => {
  setInterval(async () => {
    try {
      const response = await fetch(
        "http://tugas-mandiri.test/server/api/dashboard/admin",
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
          renderAdmin(result.data);
        }
      }
    } catch (error) {
      console.error("Polling error:", error);
    }
  }, 5000);
};
document.addEventListener("DOMContentLoaded", async () => {
  const lastPage = localStorage.getItem("lastAdminPage") ?? "beranda";
  showAdminPage(lastPage);

  const cached = await loadDB("dashboard_admin");
  if (cached) {
    console.log("admin: optimistically loaded from cache");
    renderAdmin(cached.data);
  }

  const session = await checkSession("admin");

  if (!session) return;

  document.getElementById("admin-display-name").innerText = session.email;
  await getAdmin();
  startPolling();
});
