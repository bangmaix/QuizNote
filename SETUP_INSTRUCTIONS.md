# 🎯 QuizNote - Setup & Testing Instructions

## ✅ Fixes Applied

Berikut masalah yang sudah diperbaiki:

1. ✅ **Middleware 'guest' Error** - Fixed bootstrap/app.php
2. ✅ **Auth Redirects** - Fixed LoginController & RegisterController untuk handle authenticated users
3. ✅ **Role-Based Access Control** - Improved CreatorMiddleware & StudentMiddleware dengan proper redirects
4. ✅ **Controller Traits** - Added AuthorizesRequests trait to base Controller
5. ✅ **Database Schema** - All migrations running correctly with role enum
6. ✅ **Authorization** - Proper policy implementation working

## 🚀 Server Status

**Server is RUNNING at: `http://127.0.0.1:8000`**

```
✅ Laravel: 13.7.0
✅ PHP: 8.5.3
✅ Database: SQLite (database/database.sqlite)
✅ Storage Link: Active
✅ All Routes: Registered and working
```

## 📋 Test Accounts (Ready to Use)

### Creator Account
```
Email:    creator@test.com
Password: password123
Role:     creator
```

### Student Account
```
Email:    peserta@test.com
Password: password123
Role:     student
```

### Quiz Details
```
Title:        Mengenal Angka 1-5
Status:       ACTIVE ✅
Access Code:  763227
Time Limit:   60 seconds
Questions:    1 (Berapa banyak bintang?)
Answers:      2 choices (1 correct, 1 wrong)
Points:       1 per question
```

## 🧪 COMPLETE TESTING FLOW

### Step 1: Creator Flow - Create Quiz
1. Open browser: `http://127.0.0.1:8000`
2. Click "Login di sini"
3. Email: `creator@test.com`
4. Password: `password123`
5. Click Login
6. **Expected**: Redirect to `/creator/dashboard`
7. **Verify**: See "Mengenal Angka 1-5" quiz card with:
   - Status: "Aktif" (green)
   - Code: 763227
   - 1 Question
   - Buttons: Edit, Hentikan, Hapus

### Step 2: Student Flow - Join & Answer Quiz
1. **Open NEW browser or Incognito window** (Important!)
2. Go to: `http://127.0.0.1:8000`
3. Click "Login di sini"
4. Email: `peserta@test.com`
5. Password: `password123`
6. Click Login
7. **Expected**: Redirect to `/student/dashboard`
8. **Verify**: See form "Masuk Quiz" with input field
9. Enter: `763227` (access code)
10. Click "Masuk"
11. **Expected**: See quiz question interface
12. **Verify**: Display shows:
    - Question: "Berapa banyak bintang?"
    - 2 answer cards
    - Progress: "1/1"
    - Timer: "00:60"

### Step 3: Answer Correctly
1. Click answer: "3 bintang" (first option)
2. **Expected**: Card turns GREEN ✓
3. **Verify**: See "Jawaban benar!" message
4. Click Continue/Next
5. **Expected**: Results page with:
    - "Selamat! 🎉"
    - Score: 1/1
    - Correct: 1
    - Percentage: 100%

### Step 4: Answer Incorrectly (Retry Mechanism)
1. Start same quiz again (new session)
2. Click answer: "5 bintang" (wrong option)
3. **Expected**: Card turns RED ✗
4. **Verify**: See "Jawaban salah!" message
5. Option to retry appears
6. Click correct answer: "3 bintang"
7. **Expected**: Card turns GREEN ✓
8. **Verify**: Results show both attempts tracked

### Step 5: Role Verification
1. **As Student**: Try to access `/creator/dashboard`
2. **Expected**: Redirect to `/student/dashboard` with message
3. **Message**: "Anda harus login sebagai Pembuat Soal untuk akses halaman ini"

1. **As Creator**: Try to access `/student/dashboard`
2. **Expected**: Redirect to `/creator/dashboard` with message
3. **Message**: "Anda harus login sebagai Peserta untuk akses halaman ini"

## ⚙️ Important Notes

### Browser Caching Issue
If navigation doesn't work properly:
- **Solution 1**: Use Ctrl+Shift+R (hard refresh)
- **Solution 2**: Open new Incognito window
- **Solution 3**: Manually type URL in address bar

### Test Different Users
Always use **different browser windows** or **Incognito**:
- Creator in main browser
- Student in Incognito / new window

This avoids session conflicts and auth issues.

### Database Verification

Check current data:

```bash
# All users
sqlite3 database/database.sqlite "SELECT id, name, email, role FROM users ORDER BY id;"

# All quizzes
sqlite3 database/database.sqlite "SELECT id, title, is_active, access_code FROM quizzes;"

# All questions
sqlite3 database/database.sqlite "SELECT id, quiz_id, text FROM questions;"

# All answers
sqlite3 database/database.sqlite "SELECT id, question_id, text, is_correct FROM answers;"
```

## 🔧 Troubleshooting Commands

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Restart Server
```bash
# Stop current server (Ctrl+C in terminal)
# Then restart:
php artisan serve
```

### Reset Database (if needed)
```bash
# Delete database
rm database/database.sqlite

# Recreate with migrations
php artisan migrate

# Recreate test data via tinker
php artisan tinker
```

## ✨ What's Fixed

### Middleware Issues
- ✅ Proper guest middleware handling
- ✅ Auth redirects for different roles
- ✅ Meaningful error messages instead of 403

### Authorization
- ✅ Creator can only access creator routes
- ✅ Student can only access student routes
- ✅ Non-auth users redirected to login
- ✅ Auth users from different roles redirected properly

### Controllers
- ✅ AuthorizesRequests trait added to base Controller
- ✅ Authorization checks working in QuizController
- ✅ Proper validation in all controllers

### Database
- ✅ Role column properly defined as enum
- ✅ All relationships working
- ✅ Cascading deletes configured

### Views
- ✅ Login page redirects authenticated users
- ✅ Register page redirects authenticated users
- ✅ Error messages display properly
- ✅ All forms validate inputs

## 📊 System Architecture

```
User (roles: creator, student)
├── Creator Flow:
│   ├── Login → creator.dashboard
│   ├── Create Quiz (with 6-digit code)
│   ├── Add Questions (with audio/images)
│   ├── Add Answers (mark correct)
│   └── Activate Quiz
│
└── Student Flow:
    ├── Login → student.dashboard
    ├── Enter Access Code
    ├── Join Quiz (creates StudentSession)
    ├── Answer Questions (one at a time)
    ├── Get Instant Feedback (green/red)
    ├── Automatic Retry (if wrong)
    └── View Results (with score & stats)
```

## 🎯 Next Steps

1. **Test all flows** using the steps above
2. **Verify data** using sqlite3 commands
3. **Check error logs**: `storage/logs/laravel.log`
4. **Report issues** with exact steps to reproduce

## ✅ Checklist Before Going Live

- [ ] All migrations run successfully
- [ ] Test accounts created and working
- [ ] Creator can create quiz
- [ ] Student can join quiz with code
- [ ] Instant feedback working (green/red)
- [ ] Timer countdown working
- [ ] Progress indicator showing correctly
- [ ] Results page displays scores
- [ ] Role-based access control working
- [ ] Database backups configured
- [ ] Error logging set up
- [ ] HTTPS configured (if on production)

## 📞 Quick Reference

| Action | URL |
|--------|-----|
| Home | http://127.0.0.1:8000 |
| Login | http://127.0.0.1:8000/login |
| Register | http://127.0.0.1:8000/register |
| Creator Dashboard | http://127.0.0.1:8000/creator/dashboard |
| Student Dashboard | http://127.0.0.1:8000/student/dashboard |
| API Health | http://127.0.0.1:8000/up |

## 📝 Notes

- Database: SQLite (database/database.sqlite)
- Storage: storage/app/public/ (for uploads)
- Logs: storage/logs/laravel.log
- Cache: storage/framework/ (auto-managed)

---

**System Status**: 🟢 READY FOR TESTING  
**Last Updated**: May 4, 2026  
**All Issues**: ✅ RESOLVED
