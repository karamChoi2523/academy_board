# 🏫 academy_board

**PHP + PostgreSQL 기반의 교사·학생용 게시판 시스템**

회원 가입 시 역할(teacher / student)에 따라 다른 기능을 이용할 수 있으며, 게시물 작성, 수정, 삭제 등 기본적인 CRUD 기능과 프로필 이미지 업로드 기능을 지원합니다.

---

## 📌 프로젝트 개요

| 항목 | 내용 |
|------|------|
| **프로젝트명** | academy_board |
| **개발 기간** | 2025.10.14 ~ 2025.10.17 |
| **개발자** | 1인 (기획·프론트엔드·백엔드·DB 전부 직접 구현) |
| **목적** | 학원 또는 학교 환경에서 학생과 교사가 함께 사용할 수 있는 간단한 게시판 시스템 구축 |
| **라이선스** | MIT |

---

## 🎯 주요 기능

### 👤 회원 관리
- **회원가입 / 로그인 / 로그아웃**: 간단한 인증 시스템
- **역할 기반 접근 제어**: teacher(교사), student(학생) 두 가지 역할
- **프로필 이미지 관리**: 가입 후 이미지 업로드 및 수정 가능
- **회원 정보 수정 및 탈퇴**: 개인정보 관리 기능

### 📝 게시판 기능
- **게시판 타입별 구분**: `notice`(공지), `question`(질문), `assignment`(과제) 등
- **CRUD 기능**: 게시글 작성, 조회, 수정, 삭제
- **파일 관리**: 첨부파일 업로드 및 다운로드
- **메타정보 표시**: 작성자, 작성일, 카테고리 표시
- **댓글 기능**: 게시글별 댓글 작성 및 삭제

### 🔐 세션 및 인증
- **PHP 세션 기반 로그인 유지**: 자동 세션 관리
- **역할 기반 UI 제어**: 예를 들어 학생은 공지 게시판에 글 작성 불가능
- **권한 검증**: API 요청 시 역할 및 권한 확인

---

## ⚙️ 기술 스택

| 구분 | 사용 기술 |
|------|------------|
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla) |
| **Backend** | PHP 8.x |
| **Database** | PostgreSQL 12+ |
| **Web Server** | Nginx + PHP-FPM |
| **OS** | Ubuntu Linux |
| **Version Control** | Git / GitHub |

---

## 🧱 디렉터리 구조

```
academy_board/
├── api/
│   ├── auth/
│   │   ├── register.php          # 회원가입 처리
│   │   ├── login.php             # 로그인 처리
│   │   ├── logout.php            # 로그아웃 처리
│   │   ├── check_session.php     # 세션 확인 (현재 사용자 정보)
│   │   └── delete-account.php    # 회원 탈퇴 처리
│   ├── post/
│   │   ├── create.php            # 게시글 작성 처리
│   │   ├── read.php              # 게시글 상세 조회
│   │   ├── update.php            # 게시글 수정 처리
│   │   ├── delete.php            # 게시글 삭제 처리
│   │   └── list.php              # 게시글 목록 조회
│   └── config/
│       └── database.php          # PostgreSQL 연결 설정
├── uploads/                      # 프로필 이미지, 첨부파일 저장 경로
│   ├── profiles/                 # 프로필 이미지 저장
│   └── attachments/              # 게시글 첨부파일 저장
├── js/
│   ├── board.js                  # 게시판 관련 스크립트
│   ├── auth.js                   # 인증 관련 스크립트
│   └── utils.js                  # 공통 유틸 함수
├── css/
│   └── style.css                 # 통합 스타일시트
├── index.html                    # 메인 페이지
├── header.html                   # 헤더 (공통 네비게이션)
├── login.html                    # 로그인 페이지
├── register.html                 # 회원가입 페이지
├── forgot-password.html          # 비밀번호 찾기 페이지
├── board.html                    # 게시판 목록 페이지
├── board-detail.html             # 게시글 상세 페이지
├── create.html                   # 게시글 작성 페이지
├── update.html                   # 게시글 수정 페이지
├── profile.html                  # 프로필 조회 페이지
└── profile-edit.html             # 프로필 수정 페이지
```

---

## 📖 사용 방법

### 회원가입
1. 메인 페이지에서 "회원가입" 클릭
2. 닉네임, 이메일, 비밀번호 입력
3. 역할 선택 (teacher / student)
4. 프로필 이미지 업로드 (선택사항)

### 게시글 작성
1. 로그인 후 게시판 선택
2. 선택한 게시판에서 작성 버튼 클릭
3. 제목, 내용, 카테고리 선택
4. 필요시 파일 첨부
5. 작성 완료

### 게시글 조회 및 관리
- 게시판 목록에서 원하는 글 클릭
- 작성자는 수정/삭제 가능
- 모든 사용자는 댓글 작성 가능

---
