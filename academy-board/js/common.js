// header.html 불러오기
document.addEventListener("DOMContentLoaded", async () => {
  const headerContainer = document.createElement("div");
  document.body.prepend(headerContainer);

  try {
    const res = await fetch("header.html");
    const html = await res.text();
    headerContainer.innerHTML = html;

    // 홈 버튼 기능
    window.goHome = () => (window.location.href = "index.html");

    // ✅ 여기서 메뉴 토글 이벤트 연결
    const menuToggle = document.getElementById("menu-toggle");
    const nav = document.getElementById("main-nav");

    if (menuToggle && nav) {
      menuToggle.addEventListener("click", () => {
        nav.classList.toggle("open");
      });
    }

    // 로그인 상태 표시 로직 (선택)
    const isLoggedIn = sessionStorage.getItem("user_logged_in") === "true";
    const loginLink = document.getElementById("login-link");
    const registerLink = document.getElementById("register-link");
    const logoutLink = document.getElementById("logout-link");

    if (isLoggedIn) {
      loginLink.style.display = "none";
      registerLink.style.display = "none";
      logoutLink.style.display = "inline-block";
    }

    logoutLink?.addEventListener("click", () => {
      sessionStorage.clear();
      alert("로그아웃 되었습니다.");
      window.location.href = "index.html";
    });
  } catch (err) {
    console.error("헤더 로드 실패:", err);
  }
});
