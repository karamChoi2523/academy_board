document.addEventListener("DOMContentLoaded", async () => {
  // 1️⃣ 헤더를 불러올 컨테이너 확보
  let headerContainer = document.getElementById("header-container");
  if (!headerContainer) {
    headerContainer = document.createElement("div");
    headerContainer.id = "header-container";
    document.body.prepend(headerContainer);
  }

  try {
    // 2️⃣ header.html 불러오기
    const res = await fetch("header.html", { cache: "no-store" });
    const html = await res.text();
    headerContainer.innerHTML = html;

    // 3️⃣ 요소 참조
    const loginLink = document.getElementById("login-link");
    const registerLink = document.getElementById("register-link");
    const logoutLink = document.getElementById("logout-link");
    const menuToggle = document.getElementById("menu-toggle");
    const nav = document.getElementById("main-nav");

    // 4️⃣ 세션 확인
    const sessionRes = await fetch("/api/auth/check_session.php", {
      method: "GET",
      credentials: "include", // 세션 쿠키 포함
      cache: "no-store"
    });
    const result = await sessionRes.json();

    // 5️⃣ 로그인 상태에 따른 UI 업데이트 함수
    const updateUI = (isLoggedIn) => {
      if (isLoggedIn) {
        if (loginLink) loginLink.style.display = "none";
        if (registerLink) registerLink.style.display = "none";
        if (logoutLink) logoutLink.style.display = "inline-block";
      } else {
        if (loginLink) loginLink.style.display = "inline-block";
        if (registerLink) registerLink.style.display = "inline-block";
        if (logoutLink) logoutLink.style.display = "none";
      }
    };

    // 6️⃣ 로그인 상태 반영
    if (result.logged_in) {
      sessionStorage.setItem("user_id", result.user.id);
      sessionStorage.setItem("isLoggedIn", "true");
    } else {
      sessionStorage.removeItem("user_id");
      sessionStorage.removeItem("isLoggedIn");
    }

    updateUI(result.logged_in);

    // 7️⃣ 로그아웃 이벤트
    if (logoutLink) {
      logoutLink.addEventListener("click", async (e) => {
        e.preventDefault();

        try {
          const res = await fetch("/api/auth/logout.php", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" }
          });

          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const result = await res.json();

          if (result.success) {
            alert("로그아웃 되었습니다.");

            // UI 업데이트 및 세션 초기화
            updateUI(false);
            sessionStorage.clear();

            // 메인 페이지로 이동
            window.location.href = "index.html";
          } else {
            alert("로그아웃 중 오류가 발생했습니다.");
          }
        } catch (err) {
          console.error("로그아웃 실패:", err);
          alert("로그아웃 처리에 문제가 발생했습니다.");
        }
      });
    }

    // 8️⃣ 햄버거 메뉴 토글 기능
    if (menuToggle && nav) {
      menuToggle.addEventListener("click", () => {
        nav.classList.toggle("open");
      });
    }

    // 9️⃣ 페이지 이동 함수
    window.goTo = function (page) {
      window.location.href = page;
    };

  } catch (err) {
    console.error("헤더 로딩 또는 세션 확인 실패:", err);
  }
});
