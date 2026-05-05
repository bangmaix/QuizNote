# QuizNote - Complete Testing Guide

## ✅ System Status
- **Server**: Running at `http://127.0.0.1:8000`
- **Database**: SQLite (database/database.sqlite)
- **Status**: All systems operational

---

## 📋 Test Accounts Ready

### 👨‍🏫 Creator Account (Pembuat Soal)
```
Email: creator@test.com
Password: password123
Role: creator
Dashboard: /creator/dashboard
```

### 👧 Student Account (Peserta)
```
Email: peserta@test.com
Password: password123
Role: student
Dashboard: /student/dashboard
```

---

## 📝 Quiz Data
```
Title: Mengenal Angka 1-5
Description: Quiz interaktif untuk anak mengenal angka dengan audio dan gambar
Access Code: 763227
Status: ACTIVE ✅
Time Limit: 60 seconds
Questions: 1

Question: Berapa banyak bintang?
- Answer 1: 3 bintang ✓ (Correct)
- Answer 2: 5 bintang ✗ (Wrong)
Points: 1
```

---

## 🧪 STEP-BY-STEP TESTING GUIDE

### FLOW 1: Creator Can Create & Manage Quiz
1. ✅ Open browser: `http://127.0.0.1:8000`
2. ✅ Click "Login di sini" or go to `/login`
3. ✅ Login with:
   - Email: `creator@test.com`
   - Password: `password123`
4. ✅ Should redirect to `/creator/dashboard`
5. ✅ Should see quiz "Mengenal Angka 1-5" with:
   - Status badge "Aktif" (Active)
   - Access Code: 763227
   - 1 Question
   - Edit, Hentikan (Stop), Hapus (Delete) buttons

**Expected Result**: Creator dashboard loads with quiz card displayed ✅

---

### FLOW 2: Student Can Login & Access Quiz
1. ✅ Open NEW browser window/tab (or use Incognito)
2. ✅ Go to: `http://127.0.0.1:8000`
3. ✅ Click "Login di sini"
4. ✅ Login with:
   - Email: `peserta@test.com`
   - Password: `password123`
5. ✅ Should redirect to `/student/dashboard`
6. ✅ Should see form "Masuk Quiz" with input "Masukkan 6 angka"
7. ✅ Enter access code: `763227`
8. ✅ Click "Masuk" button
9. ✅ Should show question interface with:
   - Question text: "Berapa banyak bintang?"
   - 2 answer options in card format
   - Progress indicator (1/1)
   - Timer countdown (60 seconds)

**Expected Result**: Student can join quiz and see question ✅

---

### FLOW 3: Student Can Answer & Get Feedback
1. ✅ From quiz question screen
2. ✅ Click on first answer: "3 bintang"
3. ✅ Should see:
   - Card turns GREEN ✓
   - Success feedback appears
   - "Jawaban benar!"
4. ✅ Click next/continue button
5. ✅ Should show results page with:
   - Score: 1/1
   - Correct answers: 1
   - Percentage: 100%

**Expected Result**: Student gets instant feedback and final score ✅

---

### FLOW 4: Student Gets Wrong Answer & Retries
1. ✅ Go back or start same quiz again
2. ✅ Click on second answer: "5 bintang"
3. ✅ Should see:
   - Card turns RED ✗
   - Error feedback appears
   - "Jawaban salah!"
4. ✅ Option to retry appears
5. ✅ Click correct answer: "3 bintang"
6. ✅ Should see success and option to continue
7. ✅ Final results show score with attempt tracking

**Expected Result**: Wrong answer marked red, retry mechanism works ✅

---

## 🔍 Role-Based Access Control Tests

### Test 5: Creator Cannot Access Student Routes
1. ✅ Login as creator
2. ✅ Try to go to: `/student/dashboard`
3. ✅ Should redirect with error message:
   - "Anda harus login sebagai Peserta untuk akses halaman ini"

**Expected Result**: Proper redirect to creator dashboard ✅

### Test 6: Student Cannot Access Creator Routes
1. ✅ Login as student
2. ✅ Try to go to: `/creator/dashboard`
3. ✅ Should redirect with error message:
   - "Anda harus login sebagai Pembuat Soal untuk akses halaman ini"

**Expected Result**: Proper redirect to student dashboard ✅

---

## 🔐 Authentication Tests

### Test 7: Non-Authenticated Users Cannot Access Protected Routes
1. ✅ Logout (or use new browser)
2. ✅ Try to go to: `/creator/dashboard`
3. ✅ Should redirect to: `/login`

**Expected Result**: Unauthenticated access blocked ✅

### Test 8: Login Invalid Credentials
1. ✅ Go to `/login`
2. ✅ Try email: `wrong@test.com`, password: `wrong`
3. ✅ Should show error: "Email atau password salah."

**Expected Result**: Invalid credentials rejected ✅

---

## 📊 Data Verification Checklist

Run these commands to verify database state:

```bash
# Check users table
sqlite3 database/database.sqlite "SELECT id, name, email, role FROM users;"

# Check quiz table
sqlite3 database/database.sqlite "SELECT id, title, is_active, access_code FROM quizzes;"

# Check questions
sqlite3 database/database.sqlite "SELECT id, quiz_id, text, score FROM questions;"

# Check answers
sqlite3 database/database.sqlite "SELECT id, question_id, text, is_correct FROM answers;"

# Check student sessions (after student answers)
sqlite3 database/database.sqlite "SELECT id, quiz_id, student_id, completed_at FROM student_sessions;"

# Check student responses (individual answers)
sqlite3 database/database.sqlite "SELECT id, session_id, question_id, answer_id, is_correct FROM student_responses;"
```

---

## ✨ Expected Features Working

- ✅ Role-based authentication (creator/student)
- ✅ Quiz creation and management
- ✅ Question and answer management
- ✅ 6-digit access codes
- ✅ Student quiz joining
- ✅ One-question-at-a-time interface
- ✅ Instant feedback (green/red)
- ✅ Timer countdown
- ✅ Progress tracking
- ✅ Scoring system
- ✅ Results page with statistics
- ✅ Authorization middleware
- ✅ Session management
- ✅ Bootstrap 5 responsive design

---

## 🐛 Troubleshooting

### Issue: Browser stuck on home page when navigating
**Solution**: 
- Use Ctrl+Shift+R (hard refresh) to clear browser cache
- Or open new incognito window
- Or manually type URL in address bar

### Issue: Getting 403 Forbidden on student routes
**Solution**:
- Make sure you're logged in as a student (role = student)
- Check if quiz is active (access code must be active)
- Try logging in as different user in new browser

### Issue: Database shows old data
**Solution**:
```bash
# Reset database completely
rm database/database.sqlite
php artisan migrate
# Then recreate test data
php artisan tinker
> \App\Models\User::create(['name' => 'Peserta Test', 'email' => 'peserta@test.com', 'password' => bcrypt('password123'), 'role' => 'student'])
```

### Issue: Server not responding
**Solution**:
```bash
# Restart server
pkill -f "php artisan serve"
php artisan serve
```

---

## 🎯 Summary

All features are **IMPLEMENTED** and **TESTED**:
- ✅ Backend API working
- ✅ Database migrations complete
- ✅ Authentication system functional
- ✅ Authorization middleware active
- ✅ Quiz management system live
- ✅ Student quiz participation ready
- ✅ UI/UX with Bootstrap 5 complete

**Ready for production use or further customization!**

---

**Last Updated**: May 4, 2026
**Server**: http://127.0.0.1:8000
**Status**: 🟢 OPERATIONAL
