# BSTU International — Flutter Mobile Application Guide

This document explains the mobile application architecture, configuration, localization, and development workflow for Bukhara State Technical University (BSTU) International.

---

## 🚀 Quick Start & How to Run

1. **Prerequisites**: Ensure you have Flutter SDK installed (version `>=3.4.0 <4.0.0`) and target platforms set up (Android Studio / Xcode).
2. **Install Dependencies**:

   ```bash
   cd apps/mobile
   flutter pub get
   ```

3. **Run in Development**:

   ```bash
   flutter run
   ```

4. **Build Production Bundle**:
   - **Android APK**: `flutter build apk`
   - **iOS App**: `flutter build ios`

---

## 🌐 API Base URL Configuration

The mobile app connects to the same Laravel API as the web frontend. The API URL is dynamic and configurable:

### ⚙️ Default Base URLs

- **Android Emulator**: `http://10.0.2.2:8000/api/v1` (routes loopback traffic directly to the host machine's `localhost`).
- **Local Web Test**: `http://127.0.0.1:8000/api/v1`
- **Real Device / Same Wi-Fi**: `http://YOUR_PC_LOCAL_IP:8000/api/v1` (replace `YOUR_PC_LOCAL_IP` with your computer's local IP address like `192.168.1.100`).

### 🛠️ Changing the API Base URL at Compile Time

You can override the base URL by using Flutter's `--dart-define` option:

```bash
flutter run --dart-define=API_BASE_URL=http://YOUR_PC_LOCAL_IP:8000/api/v1
```

---

## 🔒 Authentication Flow

- **Secure Storage**: Tokens and authenticated user profiles are saved securely using the `flutter_secure_storage` package (`SecureStore` class under `lib/core/storage/secure_store.dart`).
- **Endpoint Actions**:
  - **Register**: `POST /api/v1/auth/register`
  - **Login**: `POST /api/v1/auth/login`
  - **Logout**: `POST /api/v1/auth/logout`
- **Protected Routes**:
  All routes under `/student/*` (like dashboard, contracts, support, etc.) are protected. If the user is unauthenticated (`state.isAuthenticated == false`), the router (`AppRouter.onGenerateRoute` under `lib/core/routing/app_router.dart`) automatically redirects them to the `LoginScreen`.

---

## 🌍 Localization & RTL Support

### 📜 Dynamic Translation Key System

Instead of packaging local JSON translation files, the Flutter app fetches all content directly from the database through the Laravel API:

- **Locales API**: `GET /api/v1/locales`; active language codes, names, and directions are database-managed through `/apanel/locales`.
- **Translations API**: `GET /api/v1/translations?locale={locale}`.
- **Missing Translation Handling**:
  The `AppState` class (`lib/shared/app_state.dart`) provides the global translation utility:

  ```dart
  String text = state.t('nav.home');
  ```

  It resolves dot-notation strings from the loaded translation dictionary. Mobile code should not hide missing translations behind hardcoded display fallback strings.

### 📐 Right-to-Left (RTL) Layout

- The app supports RTL/LTR rendering based on the selected locale direction.
- Direction should come from the active locale record instead of a fixed language-code list.
- The root of the widget tree in `app.dart` wraps the `MaterialApp` in a `Directionality` widget:

  ```dart
  Directionality(
    textDirection: state.isRtl ? TextDirection.rtl : TextDirection.ltr,
    child: MaterialApp(...)
  )
  ```

  This forces all material layouts, alignment markers, grids, text boxes, and list items to mirror automatically when Arabic is selected.

---

## 🎓 Student Application Flow

1. **Authentication**: Student logs in or registers.
2. **Dashboard Overview**: Displays application status, billing contracts, latest payment requests, and missing documents alert.
3. **Application Draft**: Student opens the application form (`/student/application`), selects a program from the dynamic list fetched via API, and creates a draft (`status: 'draft'`).
4. **Document Upload**:
   - Opens the documents checklist screen (`/student/documents`).
   - Uses `file_picker` to select and upload scans for the 4 required types (`passport`, `photo`, `education_certificate`, `transcript`).
   - The app makes a multipart upload request to `POST /api/v1/applications/{id}/documents` with the file.
5. **Submission**: Once files are attached, the student clicks submit, calling `POST /api/v1/applications/{id}/submit` to lock the application and alert the admissions board.
6. **Tracking & Contracts**: The student tracks their status in real-time. Once accepted, tuition contract links and payment requests appear on their dashboard automatically.

---

## Implemented Mobile Screens

The current Flutter app includes public list/detail screens for faculties, departments, programs, news, announcements, services, and contact, plus protected student screens for dashboard, profile, application, application status, documents, notifications, contracts, payments, and support.

The route definitions live in `lib/core/routing/app_router.dart`, API access lives in `lib/core/network/api_client.dart`, and shared state/localization lives in `lib/shared/app_state.dart`.

## Verification Note

Run this when Flutter is installed:

```bash
cd apps/mobile
flutter analyze
```

On machines where the Flutter SDK is not available on `PATH`, the code can be inspected but static analysis/build cannot be verified until Flutter is installed.
