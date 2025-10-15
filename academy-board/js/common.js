document.addEventListener("DOMContentLoaded", async () => {
  const headerContainer = document.createElement("div");
  document.body.prepend(headerContainer);

  // 🔥 변수 선언을 try 블록 밖으로!
  let loginLink, registerLink, logoutLink, menuToggle, nav;

  try {
    // 1️⃣ header.html 불러오기
    const res = await fetch("header.html");
    const html = await res.text();
    headerContainer.innerHTML = html;

    // 2️⃣ 요소 참조
    loginLink = document.getElementById("login-link");
    registerLink = document.getElementById("register-link");
    logoutLink = document.getElementById("logout-link");
    menuToggle = document.getElementById("menu-toggle");
    nav = document.getElementById("main-nav");

    console.log("로그아웃 버튼:", logoutLink); // 디버깅용

    // 홈 버튼
    window.goHome = () => (window.location.href = "index.html");

    // 메뉴 토글
    if (menuToggle && nav) {
      menuToggle.addEventListener("click", () => {
        nav.classList.toggle("open");
      });
    }

    // 3️⃣ 세션 확인
    const sessionRes = await fetch("/api/auth/check_session.php", {
      method: "GET",
      credentials: "include",
      cache: "no-store"
    });
    const result = await sessionRes.json();

    // 로그인되지 않은 상태에서 게시판에 접근하면 로그인 페이지로 리다이렉트
    if (!result.logged_in && window.location.pathname !== '/login.html') {
      window.location.href = 'login.html';  // 로그인 페이지로 이동
    }

    // UI 업데이트 함수
    const updateUI = (isLoggedIn) => {
      const guestView = document.getElementById("guest-view");
      const userView = document.getElementById("user-view");

      if (isLoggedIn) {
        // ✅ 로그인 상태
        if (loginLink) loginLink.style.display = "none";
        if (registerLink) registerLink.style.display = "none";
        if (logoutLink) logoutLink.style.display = "inline-block";
        if (guestView) guestView.style.display = "none";
        if (userView) userView.style.display = "block";
      } else {
        // ❌ 비로그인 상태
        if (loginLink) loginLink.style.display = "inline-block";
        if (registerLink) registerLink.style.display = "inline-block";
        if (logoutLink) logoutLink.style.display = "none";
        if (guestView) guestView.style.display = "block";
        if (userView) userView.style.display = "none";
      }
    };

    // 초기 UI 설정
    updateUI(result.logged_in);

    if (result.logged_in) {
      console.log(`🔹 로그인됨: ${result.user.nickname} (${result.user.role})`);
    }

    // 4️⃣ 로그아웃 이벤트 (try 블록 안으로 이동!)
    if (logoutLink) {
      logoutLink.addEventListener("click", async (e) => {
        e.preventDefault();

        try {
          const res = await fetch("/api/auth/logout.php", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" }
          });

          if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
          }

          const result = await res.json();

          if (result.success) {
            alert("로그아웃 되었습니다.");

            // UI 즉시 업데이트
            updateUI(false);

            // 메인 페이지로 이동
            setTimeout(() => {
              window.location.href = "index.html";
            }, 300);
          } else {
            alert("로그아웃 중 오류가 발생했습니다.");
          }
        } catch (err) {
          console.error("로그아웃 실패:", err);
          alert("로그아웃 처리에 문제가 발생했습니다.");
        }
      });
    }

  } catch (err) {
    console.error("헤더 로딩 또는 세션 확인 실패:", err);
  }
});
