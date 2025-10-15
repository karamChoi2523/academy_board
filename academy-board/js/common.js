document.addEventListener("DOMContentLoaded", async () => {
  const headerContainer = document.createElement("div");
  document.body.prepend(headerContainer);

  try {
    // 1️⃣ header.html 불러오기
    const res = await fetch("header.html");
    const html = await res.text();
    headerContainer.innerHTML = html; // header.html을 DOM에 삽입

    // 2️⃣ header.html이 로드된 후에 요소 참조
    const loginLink = document.getElementById("login-link");
    const registerLink = document.getElementById("register-link");
    const logoutLink = document.getElementById("logout-link"); // 이 부분을 여기서 참조
    const menuToggle = document.getElementById("menu-toggle");
    const nav = document.getElementById("main-nav");

    console.log(logoutLink); // 제대로 참조되는지 확인

    // 홈 버튼
    window.goHome = () => (window.location.href = "index.html");

    // 메뉴 토글
    if (menuToggle && nav) {
      menuToggle.addEventListener("click", () => {
        nav.classList.toggle("open");
      });
    }

    // 3️⃣ 세션 확인 요청 (쿠키 포함 필수!)
    const sessionRes = await fetch("/api/auth/check_session.php", {
      method: "GET",
      credentials: "include", // 세션 쿠키 포함
      cache: "no-store" // 캐시 사용 안 함
    });
    const result = await sessionRes.json();

    if (result.logged_in) {
      // ✅ 로그인 상태일 때
      if (loginLink) loginLink.style.display = "none";
      if (registerLink) registerLink.style.display = "none";
      if (logoutLink) logoutLink.style.display = "inline-block";

      // 로그인 후 `user-view` 표시, `guest-view` 숨기기
      document.getElementById("guest-view").style.display = "none";
      document.getElementById("user-view").style.display = "block";

      console.log(`🔹 로그인됨: ${result.user.nickname} (${result.user.role})`);
    } else {
      // ❌ 비로그인 상태일 때
      if (loginLink) loginLink.style.display = "inline-block";
      if (registerLink) registerLink.style.display = "inline-block";
      if (logoutLink) logoutLink.style.display = "none";

      // 비로그인 상태에서 `user-view` 숨기고 `guest-view` 보이게 설정
      document.getElementById("guest-view").style.display = "block";
      document.getElementById("user-view").style.display = "none";
    }
  } catch (err) {
    console.error("세션 확인 실패:", err);
  }

  // 4️⃣ 로그아웃 이벤트
  if (logoutLink) {
    logoutLink.addEventListener("click", async (e) => {
      e.preventDefault();

      try {
        // 로그아웃 요청
        const res = await fetch("/api/auth/logout.php", {
          method: "POST",
          credentials: "include" // 세션 쿠키 포함
        });

        const result = await res.json();

        if (result.success) {
          alert("로그아웃 되었습니다.");

          // 로그아웃 후 세션 확인 요청
          const sessionRes = await fetch("/api/auth/check_session.php", {
            method: "GET",
            credentials: "include", // 세션 쿠키 포함
            cache: "no-store"
          });

          const sessionResult = await sessionRes.json();

          if (!sessionResult.logged_in) {
            // 로그아웃 상태에서 `guest-view` 보이고 `user-view` 숨기기
            document.getElementById("guest-view").style.display = "block";
            document.getElementById("user-view").style.display = "none";

            // 로그인, 회원가입 버튼 보이게 하고 로그아웃 버튼 숨기기
            if (loginLink) loginLink.style.display = "inline-block";
            if (registerLink) registerLink.style.display = "inline-block";
            if (logoutLink) logoutLink.style.display = "none";
          }
        } else {
          alert("로그아웃 중 오류가 발생했습니다.");
        }
      } catch (err) {
        console.error("로그아웃 실패:", err);
        alert("로그아웃 처리에 문제가 발생했습니다.");
      }
    });
  }
});
