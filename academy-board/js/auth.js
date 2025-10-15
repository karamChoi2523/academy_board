document.addEventListener("DOMContentLoaded", async () => {
  try {
    // 세션 확인
    const sessionRes = await fetch("/api/auth/check_session.php", {
      method: "GET",
      credentials: "include",
      cache: "no-store"
    });
    const result = await sessionRes.json();

    // UI 업데이트 함수
    const updateUI = (isLoggedIn) => {
      const guestView = document.getElementById("guest-view");
      const userView = document.getElementById("user-view");
      const loginLink = document.getElementById("login-link");
      const registerLink = document.getElementById("register-link");
      const logoutLink = document.getElementById("logout-link");

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

    // 로그아웃 이벤트
    const logoutLink = document.getElementById("logout-link");
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
    console.error("세션 확인 실패:", err);
  }
});
