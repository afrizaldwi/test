const login = async () => {
  const formData = new FormData();

  const email = document.getElementById("email").value;
  const pass = document.getElementById("password").value;

  formData.append("email", email);
  formData.append("pass", pass);

  try {
    const response = await fetch(
      "http://tugas-mandiri.test/server/api/auth/login",
      {
        method: "POST",
        credentials: "include",
        body: formData,
      },
    );
    const data = await response.json();
    if (data.status === "success") {
      if (data.data.role === "admin") {
        window.location.href = "./pages/admin.html";
      } else {
        window.location.href = "./pages/penyewa.html";
      }
    } else {
      showLoginError();
    }
  } catch (error) {
    console.error("Login error:", error);
    showLoginError();
  }
};

const showLoginError = () => {
  const err = document.getElementById("login-error");
  const email = document.getElementById("email");
  const pass = document.getElementById("password");

  err.classList.remove("hidden");
  email.classList.add("error-input");
  pass.classList.add("error-input");

  setTimeout(() => {
    err.classList.add("hidden");
    email.classList.remove("error-input");
    pass.classList.remove("error-input");
  }, 3000);
};

document.addEventListener("DOMContentLoaded", async () => {
  document.getElementById("login-form").addEventListener("submit", (e) => {
    e.preventDefault();
    login();
  });

  try {
    const response = await fetch(
      "http://tugas-mandiri.test/server/api/auth/check",
      {
        method: "GET",
        credentials: "include",
      },
    );

    const session = await response.json();

    if (session.status === "success") {
      if (session.data.role === "admin") {
        window.location.href = "./pages/admin.html";
      } else {
        window.location.href = "./pages/penyewa.html";
      }
    }
  } catch (error) {
    console.log("Belum login atau gagal mengecek sesi:", error);
  }
});
