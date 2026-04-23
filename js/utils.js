const dbName = "kostDB";
const version = 1;
const storeName = "dashboard";

const openDB = async () => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(dbName, version);

    request.onupgradeneeded = (e) => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains(storeName)) {
        db.createObjectStore(storeName, { keyPath: "key" });
      }
    };

    request.onsuccess = (e) => resolve(e.target.result);
    request.onerror = (e) => reject(e.target.error);
  });
};

const saveDB = async (key, data) => {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, "readwrite");
    tx.objectStore(storeName).put({
      key,
      data,
      cachedAt: new Date().toISOString(),
    });
    tx.oncomplete = () => resolve();
    tx.onerror = (e) => reject(e.target.error);
  });
};

const loadDB = async (key) => {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, "readonly");
    const request = tx.objectStore(storeName).get(key);
    request.onsuccess = (e) => resolve(e.target.result ?? null);
    request.onerror = (e) => reject(e.target.error);
  });
};
const clearDB = async (key) => {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, "readwrite");
    tx.objectStore(storeName).delete(key);
    tx.oncomplete = () => resolve();
    tx.onerror = (e) => reject(e.target.error);
  });
};

const logout = async () => {
  localStorage.removeItem("lastAdminPage");

  await clearDB("dashboard_admin");
  await clearDB("dashboard_penyewa");

  await fetch("http://tugas-mandiri.test/server/api/auth/logout", {
    method: "POST",
    credentials: "include",
  });
  window.location.href = "/";
};

const checkSession = async (role) => {
  const response = await fetch(
    "http://tugas-mandiri.test/server/api/auth/check",
    {
      method: "GET",
      credentials: "include",
    },
  );
  const session = await response.json();

  if (session.status !== "success" || session.data.role !== role) {
    window.location.href = "/";
    return null;
  }
  return session.data;
};

const toggleButton = () => {
  const sidebar = document.getElementById("sidebar");

  sidebar.classList.toggle("open");
};
