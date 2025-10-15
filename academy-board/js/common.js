// ✅ 공통 헤더 로드 + 로그인 상태 유지 통합 스크립트
document.addEventListener("DOMContentLoaded", async () => {
  const headerContainer = document.createElement("div");
  document.body.prepend(headerContainer);

  try {
    // 1️⃣ header.html 불러오기
    const res = await fetch("header.html");
    const html = await res.text();
    headerContainer.innerHTML = html;

    // 2️⃣ 로드 후 요소 참조
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

    // 3️⃣ 세션 확인 요청 (쿠키 포함 필수!)
    try {
      const sessionRes = await fetch("/api/auth/check_session.php", {
        method: "GET",
        credentials: "include" // ⚡ 세션 쿠키 유지 (Firefox, Chrome 둘 다)
      });
      const result = await sessionRes.json();

      if (result.logged_in) {
        // ✅ 로그인 상태
        if (loginLink) loginLink.style.display = "none";
        if (registerLink) registerLink.style.display = "none";
        if (logoutLink) logoutLink.style.display = "inline-block";

        console.log(`🔹 로그인됨: ${result.user.nickname} (${result.user.role})`);
      } else {
        // ❌ 비로그인 상태
        if (loginLink) loginLink.style.display = "inline-block";
        if (registerLink) registerLink.style.display = "inline-block";
        if (logoutLink) logoutLink.style.display = "none";
      }
    } catch (err) {
      console.error("세션 확인 실패:", err);
    }

    // 4️⃣ 로그아웃 이벤트
    if (logoutLink) {
      logoutLink.addEventListener("click", async (e) => {
        e.preventDefault();
        await fetch("/api/auth/logout.php", {
          method: "POST",
          credentials: "include" // ⚡ 세션 쿠키 포함
        });
        alert("로그아웃 되었습니다.");
        window.location.reload();
      });
    }

  } catch (err) {
    console.error("헤더 로드 실패:", err);
  }
});
