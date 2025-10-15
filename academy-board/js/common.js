// common.js
document.addEventListener("DOMContentLoaded", async () => {
  const headerContainer = document.createElement("div");
  document.body.prepend(headerContainer);

  try {
    // ✅ 1️⃣ header.html 불러오기
    const res = await fetch("header.html");
    const html = await res.text();
    headerContainer.innerHTML = html;

    // ✅ 2️⃣ header가 로드된 뒤 요소 참조 가능
    const loginLink = document.getElementById("login-link");
    const registerLink = document.getElementById("register-link");
    const logoutLink = document.getElementById("logout-link");
    const menuToggle = document.getElementById("menu-toggle");
    const nav = document.getElementById("main-nav");

    // 홈 버튼
    window.goHome = () => (window.location.href = "index.html");

    // 메뉴 토글
    if (menuToggle && nav) {
      menuToggle.addEventListener("click", () => {
        nav.classList.toggle("open");
      });
    }

    // ✅ 3️⃣ 로그인 상태 확인
    try {
      const res = await fetch("/api/auth/check_session.php");
      const result = await res.json();

      if (result.logged_in) {
        // 로그인됨
        loginLink.style.display = "none";
        registerLink.style.display = "none";
        logoutLink.style.display = "inline";
        console.log(`환영합니다, ${result.user.nickname}님`);
      } else {
        // 비로그인
        loginLink.style.display = "inline";
        registerLink.style.display = "inline";
        logoutLink.style.display = "none";
      }
    } catch (err) {
      console.error("세션 확인 실패:", err);
    }

    // ✅ 4️⃣ 로그아웃 동작
    logoutLink?.addEventListener("click", async (e) => {
      e.preventDefault();
      await fetch("/api/auth/logout.php");
      window.location.reload();
    });

  } catch (err) {
    console.error("헤더 로드 실패:", err);
  }
});
