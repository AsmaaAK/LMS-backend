<?php

function http($method, $url, $headers = [], $body = null) {
    $ch = curl_init($url);
    $hdrs = [];
    foreach ($headers as $k => $v) { $hdrs[] = $k . ': ' . $v; }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $hdrs,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    if ($body !== null) { curl_setopt($ch, CURLOPT_POSTFIELDS, $body); }
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$status, $resp];
}

$base = 'http://127.0.0.1:8000';

// 1) Create teacher and student via admin
$loginAdmin = json_encode(['email' => 'admin@test.local', 'password' => 'Admin@123456']);
[$stA, $resA] = http('POST', $base.'/api/login', ['Content-Type'=>'application/json','Accept'=>'application/json'], $loginAdmin);
$tokenAdmin = (json_decode($resA, true)['access_token'] ?? null);
if (!$tokenAdmin) { echo $resA, "\n"; exit(1);} 
$authAdmin = ['Accept'=>'application/json','Authorization'=>'Bearer '.$tokenAdmin,'Content-Type'=>'application/json'];

$teacherEmail = 'teacher_'.bin2hex(random_bytes(3)).'@example.com';
$studentEmail = 'student_'.bin2hex(random_bytes(3)).'@example.com';

// role_id: assume teacher=5, student=3 based on ensure_roles output
$createTeacher = json_encode(['name'=>'Test Teacher','email'=>$teacherEmail,'password'=>'password1234','password_confirmation'=>'password1234','role_id'=>5]);
[$stCT, $resCT] = http('POST', $base.'/api/users', $authAdmin, $createTeacher);
$teacherId = json_decode($resCT, true)['data']['id'] ?? null;

$createStudent = json_encode(['name'=>'Test Student','email'=>$studentEmail,'password'=>'password1234','password_confirmation'=>'password1234','role_id'=>3]);
[$stCS, $resCS] = http('POST', $base.'/api/users', $authAdmin, $createStudent);
$studentId = json_decode($resCS, true)['data']['id'] ?? null;

// 2) Login as teacher and create a course
$loginTeacher = json_encode(['email' => $teacherEmail, 'password' => 'password1234']);
[$stLT, $resLT] = http('POST', $base.'/api/login', ['Content-Type'=>'application/json','Accept'=>'application/json'], $loginTeacher);
$tokenTeacher = json_decode($resLT, true)['access_token'] ?? null;
$authTeacher = ['Accept'=>'application/json','Authorization'=>'Bearer '.$tokenTeacher,'Content-Type'=>'application/json'];

// Create course minimal payload (assuming validation allows these fields)
$courseBody = json_encode([
    'title' => 'Intro Course',
    'description' => 'Course by teacher',
    'category_id' => 1,
    'level' => 'beginner',
    'price' => 0,
    'is_published' => true
]);
[$stCC, $resCC] = http('POST', $base.'/api/courses', $authTeacher, $courseBody);
$course = json_decode($resCC, true);
$courseId = $course['course']['id'] ?? ($course['data']['id'] ?? null);

// 3) Login as student and enroll in course
$loginStudent = json_encode(['email' => $studentEmail, 'password' => 'password1234']);
[$stLS, $resLS] = http('POST', $base.'/api/login', ['Content-Type'=>'application/json','Accept'=>'application/json'], $loginStudent);
$tokenStudent = json_decode($resLS, true)['access_token'] ?? null;
$authStudent = ['Accept'=>'application/json','Authorization'=>'Bearer '.$tokenStudent,'Content-Type'=>'application/json'];

[$stEn, $resEn] = http('POST', $base.'/api/courses/'.($courseId ?? 0).'/enroll', $authStudent);

// 4) Fetch instructor courses and student enrollments
[$stIC, $resIC] = http('GET', $base.'/api/instructor/courses', $authTeacher);
[$stSE, $resSE] = http('GET', $base.'/api/student/enrollments', $authStudent);

echo json_encode([
    'create_teacher_status' => $stCT,
    'create_student_status' => $stCS,
    'create_course_status' => $stCC,
    'course_raw' => json_decode($resCC, true) ?: $resCC,
    'enroll_status' => $stEn,
    'enroll_raw' => json_decode($resEn, true) ?: $resEn,
    'instructor_courses_status' => $stIC,
    'instructor_courses_raw' => json_decode($resIC, true) ?: $resIC,
    'student_enrollments_status' => $stSE,
    'student_enrollments_raw' => json_decode($resSE, true) ?: $resSE,
], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), "\n";


