document.addEventListener("DOMContentLoaded", async () => {
  const headerContainer = document.createElement("div");
  document.body.prepend(headerContainer);

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

    // 3️⃣ 세션 확인
    const sessionRes = await fetch("/api/auth/check_session.php", {
      method: "GET",
      credentials: "include",  // 세션 쿠키 포함
      cache: "no-store"
    });
    const result = await sessionRes.json();

    // 로그인 상태 확인 후 UI 업데이트
    const updateUI = (isLoggedIn) => {
      if (isLoggedIn) {
        // 로그인 상태
        if (loginLink) loginLink.style.display = "none";
        if (registerLink) registerLink.style.display = "none";
        if (logoutLink) logoutLink.style.display = "inline-block";
      } else {
        // 비로그인 상태
        if (loginLink) loginLink.style.display = "inline-block";
        if (registerLink) registerLink.style.display = "inline-block";
        if (logoutLink) logoutLink.style.display = "none";
      }
    };

    // 로그인 상태를 sessionStorage에 저장
    if (result.logged_in) {
      sessionStorage.setItem('user_id', result.user.id);
      sessionStorage.setItem('isLoggedIn', 'true');
      //sessionStorage.setItem('user', JSON.stringify(result.user)); // 유저 정보 저장
    } else {
      sessionStorage.removeItem('isLoggedIn');
      sessionStorage.removeItem('user_id');
    }

    // UI 설정
    updateUI(result.logged_in);

    // 로그인되지 않으면 로그인 페이지로 리다이렉트
    console.log("pathname", window.location.pathname);

    // 4️⃣ 로그아웃 이벤트
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

            // 세션 및 로컬스토리지 초기화
            sessionStorage.removeItem('isLoggedIn');
            sessionStorage.removeItem('user_id');

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
